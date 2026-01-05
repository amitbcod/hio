<?php $this->load->view('common/header'); ?>

<div class="review-form">
<h3>Review Your Order</h3>
<form method="post" action="<?= base_url('review/save/'.$order_id) ?>">
    <?php foreach($items as $item): ?>
        <div class="product-review">

            <div class="star-rating">
                <?php for($i=5; $i>=1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>-<?= $item->product_id ?>" 
                           name="ratings[<?= $item->product_id ?>]" 
                           value="<?= $i ?>" />
                    <label for="star<?= $i ?>-<?= $item->product_id ?>">★</label>
                <?php endfor; ?>
            </div>

            <textarea name="comments[<?= $item->product_id ?>]" placeholder="Your feedback"></textarea>
        </div>
    <?php endforeach; ?>
    <button type="submit">Submit</button>
</form>
</div>

 <?php $this->load->view('common/footer'); ?>

<style>

.review-form {
    border:1px solid #eee;
    padding: 30px 30px;
    max-width: 430px;
    margin: 60px auto;
    text-align: center;
    font: 14px "Open Sans", sans-serif;
    background:#f8f8f8;
}

.review-form h3 {
    font-size: 20px;
    font-family: Roboto;
    font-weight: 500;
    color: #222;
}

.review-form textarea {
    width: 100%;
    height: 80px;
    padding: 7px;
     font: 14px "Open Sans", sans-serif;
}

.star-rating {
    direction: rtl; /* so highest star comes first */
    display: inline-flex;
    display: block;
    margin-bottom: 15px;
    margin-top: -6px;
}
.star-rating input {
    display: none;
}
.star-rating label {
    font-size: 31px;
    color: #ccc;
    cursor: pointer;
    padding: 0 0px;
}
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: gold;
}

.review-form button[type="submit"] {
       background: #0f5cd0;
    color: #fff;
    font-size: 14px !important;
    font-weight: 400;
    padding: 12px 0 !important;
    margin-top: 20px !important;
    max-width: 130px;
    width: 100%;
    border: 0 !IMPORTANT;
    cursor: pointer;
    line-height: 10px;
}
</style>
