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
        // $data: user_id, value_id, amount, receiver_name, receiver_email, message, order_number
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
        return $this->db->insert_id() ? ((object) array_merge($insert, ['id' => $this->db->insert_id()])) : false;
    }

    private function generate_unique_code($length = 10)
    {
        do {
            $code = 'GC' . strtoupper(substr(bin2hex(random_bytes(6)),0,$length));
            $exists = $this->db->get_where('gift_cards', ['code' => $code])->row();
        } while ($exists);
        return $code;
    }
}
