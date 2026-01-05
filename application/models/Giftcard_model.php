<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Giftcard_model extends CI_Model
{
    public function get_values($only_active = true)
    {
        $this->db->select('*')->from('gift_card_values');
        if ($only_active) $this->db->where('active', 1);
        return $this->db->order_by('amount','ASC')->get()->result();
    }

    public function get_value($id)
    {
        return $this->db->get_where('gift_card_values', ['id' => $id])->row();
    }

    public function create_order($data)
    {
        $this->db->insert('gift_card_orders', $data);
        return $this->db->insert_id();
    }

    public function get_order($order_id)
    {
        return $this->db->get_where('gift_card_orders', ['id'=>$order_id])->row();
    }

    public function get_order_by_number($order_number)
    {
        return $this->db->get_where('gift_card_orders', ['order_number'=>$order_number])->row();
    }

    public function mark_order_paid($order_id, $payment_ref)
    {
        $this->db->where('id', $order_id)->update('gift_card_orders', [
            'status' => 1,
            'payment_ref' => $payment_ref,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function issue_giftcard($order_id)
    {
        $order = $this->get_order($order_id);
        if (!$order || $order->status != 1) return false;

        // generate unique code
        $code = $this->generate_unique_code();

        $insert = [
            'code' => $code,
            'order_id' => $order->id,
            'initial_value' => $order->amount,
            'balance' => $order->amount,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('gift_cards', $insert);
        $gift_card_id = $this->db->insert_id();

        if ($gift_card_id) {
            // Automatically add transaction for the gift card credit
            $this->add_transaction($order->user_id, $gift_card_id, 'credit', $order->amount, 1);
            return (object) array_merge($insert, ['id' => $gift_card_id]);
        }

        return false;
    }

    private function generate_unique_code($length = 10)
    {
        do {
            $code = 'GC' . strtoupper(substr(bin2hex(random_bytes(6)),0,$length));
            $exists = $this->db->get_where('gift_cards', ['code' => $code])->row();
        } while ($exists);
        return $code;
    }

    // Add transaction
    public function add_transaction($user_id, $gift_card_id, $type, $amount, $status = 1)
    {
        $this->db->insert('gift_card_transactions', [
            'user_id' => $user_id,
            'gift_card_id' => $gift_card_id,
            'type' => $type, // credit or debit
            'amount' => $amount,
            'status' => $status, // 1=completed
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

   public function get_user_balance($user_id)
{
    $this->db->select("SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as balance", false);
    $this->db->where('user_id', $user_id);
    
    if ($this->db->field_exists('status', 'gift_card_transactions')) {
        $this->db->where('status', 1); // only completed transactions
    }
    
    $query = $this->db->get('gift_card_transactions');
    
    return $query->row()->balance ?? 0;
}

    public function get_user_transactions($user_id)
    {
        $this->db->where('user_id', $user_id);
        if ($this->db->field_exists('status', 'gift_card_transactions')) {
            $this->db->where('status', 1);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('gift_card_transactions')->result();
    }

        // ✅ Get gift card details
        public function getGiftCardDetails($code)
        {
            $query = $this->db->get_where('gift_cards', ['code' => $code, 'status' => 1]);
            return $query->row_array();
        }

        // ✅ Get cart by session ID
    public function getCartBySession($session_id)
    {
        $query = $this->db->get_where('sales_quote', ['session_id' => $session_id]);
        return $query->row_array();
    }

    // ✅ Update gift card balance
    public function updateGiftCardBalance($giftcard_id, $new_balance)
    {
        $data = [
            'balance'    => $new_balance,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $giftcard_id);
        return $this->db->update('gift_cards', $data);
    }

    // ✅ Update cart total
    public function updateCartTotalAfterGiftCard($quote_id, $new_total, $gift_code, $used_amount)
    {
        $data = [
            'grand_total'     => $new_total,
            'voucher_code'    => $gift_code,
            'voucher_amount'  => $used_amount,
            'updated_at'      => date('Y-m-d H:i:s')
        ];
        $this->db->where('quote_id', $quote_id);
        return $this->db->update('sales_quote', $data);
    }

    // ✅ Add transaction record
    public function addGiftCardTransaction($user_id, $giftcard_id, $type, $amount, $order_id = null, $note = '')
    {
        $data = [
            'user_id'      => $user_id,
            'gift_card_id'  => $giftcard_id,
            'type'         => $type, // debit or credit
            'amount'       => $amount,
            'order_id'     => $order_id,
            'note'         => $note,
            'status'       => 1,
            'created_at'   => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('gift_card_transactions', $data);

          /*if(empty($giftcard_id) || !in_array($type, ['debit','credit']) || $amount <= 0){
                log_message('error', 'Gift card transaction invalid data: '.json_encode($data));
                return false;
            }

            $insert = $this->db->insert('gift_card_transactions', $data);

            if(!$insert){
                log_message('error', 'Gift card transaction insert failed: '.$this->db->last_query());
            }

        return $insert;*/
    }

    // ✅ MAIN FUNCTION — Apply gift card to cart
    /*public function applyGiftCardToCart($gift_code, $session_id, $user_id)
    {
        $gift = $this->getGiftCardDetails($gift_code);
        if (!$gift) {
            return ['status' => 'error', 'message' => 'Invalid or inactive gift card'];
        }

        if ($gift['balance'] <= 0) {
            return ['status' => 'error', 'message' => 'Gift card has no balance left'];
        }

        $cart = $this->getCartBySession($session_id);
        if (!$cart) {
            return ['status' => 'error', 'message' => 'Cart not found'];
        }

        $cart_total = $cart['grand_total'];

        // Calculate deduction
        if ($gift['balance'] >= $cart_total) {
            $used_amount = $cart_total;
            $remaining_balance = $gift['balance'] - $cart_total;
            $new_cart_total = 0;
        } else {
            $used_amount = $gift['balance'];
            $remaining_balance = 0;
            $new_cart_total = $cart_total - $gift['balance'];
        }

        // Update both records
        $this->updateCartTotalAfterGiftCard($cart['quote_id'], $new_cart_total, $gift_code, $used_amount);
        $this->updateGiftCardBalance($gift['id'], $remaining_balance);
        $this->addGiftCardTransaction($user_id, $gift['id'], 'debit', $used_amount, $cart['quote_id'], 'Used on cart checkout');

        return [
            'status'             => 'success',
            'message'            => 'Gift card applied successfully',
            'used_amount'        => $used_amount,
            'remaining_balance'  => $remaining_balance,
            'new_cart_total'     => $new_cart_total
        ];
    }*/

    public function applyGiftCardToCart($gift_code, $session_id, $user_id)
    {
        $gift = $this->getGiftCardDetails($gift_code);
        if (!$gift) return ['status'=>'error','message'=>'Invalid or inactive gift card'];

         if ($gift['balance'] <= 0) {
            return ['status' => 'error', 'message' => 'Gift card has been fully used'];
        }

        $cart = $this->getCartBySession($session_id);
        if (!$cart) return ['status'=>'error','message'=>'Cart not found'];

        $cart_total = $cart['grand_total'];

        // Calculate discount
        $used_amount = min($cart_total, $gift['balance']);
        $new_cart_total = $cart_total - $used_amount;

        // Update cart only
        $this->updateCartTotalAfterGiftCard($cart['quote_id'], $new_cart_total, $gift_code, $used_amount);

        return [
            'status' => 'success',
            'message' => 'Gift card applied successfully',
            'used_amount' => $used_amount,
            'new_cart_total' => $new_cart_total
        ];
    }


    /*public function removeGiftCardFromCart($gift_code, $session_id, $user_id)
    {
        $gift = $this->getGiftCardDetails($gift_code);
        if (!$gift) {
            return ['status' => 'error', 'message' => 'Invalid gift card'];
        }

        $cart = $this->getCartBySession($session_id);
        if (!$cart) {
            return ['status' => 'error', 'message' => 'Cart not found'];
        }

        // Get the amount used on this cart
        $used_amount = $this->getGiftCardUsageOnCart($gift['id'], $cart['quote_id']);

        // Restore gift card balance
        $new_balance = $gift['balance'] + $used_amount;
        $this->updateGiftCardBalance($gift['id'], $new_balance);

        // Remove gift card deduction from cart
        $this->updateCartTotalAfterGiftCard($cart['quote_id'], $cart['grand_total'] + $used_amount, '', 0);

        // Remove gift card transaction record
        $this->removeGiftCardTransaction($user_id, $gift['id'], $cart['quote_id']);

        return [
            'status'            => 'success',
            'message'           => 'Gift card removed successfully',
            'restored_balance'  => $new_balance,
            'new_cart_total'    => $cart['grand_total'] + $used_amount
        ];
    }*/

    public function removeGiftCardFromCart($gift_code, $session_id, $user_id)
    {
        $cart = $this->getCartBySession($session_id);
        if (!$cart) return ['status'=>'error','message'=>'Cart not found'];

        $used_amount = $this->db->select('voucher_amount')
                                ->where('voucher_code', $gift_code)
                                ->where('quote_id', $cart['quote_id'])
                                ->get('sales_quote')
                                ->row()
                                ->voucher_amount ?? 0;

        // Reset cart totals
        $this->updateCartTotalAfterGiftCard($cart['quote_id'], $cart['grand_total'] + $used_amount, '', 0);

        return [
            'status' => 'success',
            'message' => 'Gift card removed from cart',
            'new_cart_total' => $cart['grand_total'] + $used_amount
        ];
    }


public function getGiftCardUsageOnCart($giftcard_id, $quote_id)
    {
        // Get the gift card code first
        $gift_code = $this->getGiftCardCodeById($giftcard_id);
        if (!$gift_code) return 0;

        // Check how much of this gift card was applied to this cart
        $this->db->select('voucher_amount');
        $this->db->where('voucher_code', $gift_code);
        $this->db->where('quote_id', $quote_id);
        $query = $this->db->get('sales_quote');

        if ($query && $query->num_rows() > 0) {
            return (float) $query->row()->voucher_amount;
        } else {
            return 0;
        }
    }

// ✅ Helper function to get gift card code by ID
public function getGiftCardCodeById($giftcard_id)
{
    $this->db->select('code');
    $this->db->where('id', $giftcard_id);
    $query = $this->db->get('gift_cards');

    if ($query && $query->num_rows() > 0) {
        return $query->row()->code;
    } else {
        return null;
    }
}

public function getGiftCardIdByCode($giftcard_code)
{
    $this->db->select('id');
    $this->db->where('code', $giftcard_code);
    $query = $this->db->get('gift_cards');

    if ($query && $query->num_rows() > 0) {
        return $query->row()->id;
    } else {
        return null;
    }
}

public function removeGiftCardTransaction($user_id, $giftcard_id, $quote_id)
{
    // Remove the debit record for this gift card and cart
    $this->db->where('user_id', $user_id);
    $this->db->where('giftcard_id', $giftcard_id);
    $this->db->where('order_id', $quote_id);
    $this->db->where('type', 'debit'); // only debit transactions applied to cart
    return $this->db->delete('gift_card_transactions');
}

public function deductGiftCardOnOrder($gift_card_id, $user_id, $order_id, $used_amount)
{
    $gift = $this->db->get_where('gift_cards', ['id'=>$gift_card_id])->row_array();
    if (!$gift) return false;

    if ($gift['balance'] < $used_amount) return false; // insufficient balance

    $new_balance = $gift['balance'] - $used_amount;

    // Deduct balance
    $this->db->where('id', $gift_card_id)->update('gift_cards', ['balance'=>$new_balance]);

    // Add transaction
    $this->addGiftCardTransaction($user_id, $gift_card_id, 'debit', $used_amount, $order_id, 'Used on order checkout');

    return true;
}
// Get all gift cards used by the user (via transactions)
public function get_user_giftcards($user_id)
{
    $this->db->select('gc.*');
    $this->db->from('gift_cards gc');
    $this->db->join('gift_card_transactions gct', 'gc.id = gct.gift_card_id');
    $this->db->where('gct.user_id', $user_id);
    $this->db->group_by('gc.id'); // avoid duplicates
    return $this->db->get()->result();
}


}
