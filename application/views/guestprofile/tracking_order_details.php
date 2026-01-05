<?php $this->load->view('common/header'); ?>



<div class="breadcrum-section">

  <div class="container">

		<div class="breadcrum">

			<ul>

				<li><a href="<?php echo base_url(); ?>">Home</a></li>

				

				<li><span class="icon icon-keyboard_arrow_right"> order-tracking</span></li>


			</ul>

		</div>

    </div>

 </div><!-- breadcrum section -->

<div class="d-none" id="sharepop">

	<div class="sharepopover" style="padding-bottom: 30px;">

		<ul class="social-share-promote" style="list-style: none">

			<li class="fb-btn">

				<a href="https://www.facebook.com/sharer/sharer.php?u="><i class="icon-facebook" aria-hidden="true"></i></a>

			</li>

			<li class="tw-btn">

				<a href="https://www.twitter.com/share?url="><i class="icon-twitter" aria-hidden="true"></i></a>

			</li>

			<li class="lnk-btn">

				<a href="https://www.linkedin.com/shareArticle?mini=true&url="><i class="icon-linkedin" aria-hidden="true"></i></a>

			</li>

			<li class="wa-btn">

                <?php if(is_firefox()): ?>

				    <a href="whatsapp://send?text="><i class="icon-whatsapp" aria-hidden="true"></i></a>

                <?php else: ?>

                    <a href="https://wa.me/?text="><i class="icon-whatsapp" aria-hidden="true"></i></a>

                <?php endif; ?>



			</li>

			<li id="cp-btn">

				<a><i class="icon-copy copy-link" aria-hidden="true"></i></a>

			</li>

		</ul>

	</div>

</div>


<!-- tracking container part -->

<div id="track-order">
    <h1>Track Your Order</h1>
    <div class="left-trackorder">
        <span class="track-text">
            To track your order, please enter your Order Number in the field provided below and click on the Submit button.
        </span>
        <form method="POST" name="tracking-deaitls" id="tracking-deaitls" action="">
            <label>Order Number : </label>
            <input type="text" name="order_tracking_no" class="trackorder-txtbox" id="order_tracking_no" required>
            <button  type="submit" name="trackingbtn" id="trackingbtn" class="track-order-btn" >Submit</button>
            <table name="customerdata" id="customerdata" style="display:none">
            <tr>
                <th>Order #</th>
                <th>Receipient Name	</th>
                <th>Order Value (Rs.)</th>
                <!-- <th>Status</th> -->
            </tr>
            <tbody id="tackdata">
            </tbody>
            </table>
            <table name="productdata"  id="productdata" style="display:none">
            <tr>
                <th>Magazine Name</th>
                <th>Quantity Order</th>
                <th>Cover Price (Rs.)	</th>
                <th>Offer Price (Rs.)</th>
                <th>Discount (Rs.)</th>
                <th>Gift</th>
            </tr>
            <tbody id="productdetaildata">
            </tbody>
            </table>
        </form>
    </div>
</div>





<script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>

 <script src="<?php echo SKIN_JS; ?>jquery.exzoom.js"></script>

<link href="<?php echo SKIN_CSS; ?>jquery.exzoom.css" rel="stylesheet" type="text/css"/>

<?php $this->load->view('common/footer'); ?>

<script src="<?php echo SKIN_JS ?>product.js?v=<?php echo CSSJS_VERSION; ?>"></script>

<script src="<?php echo SKIN_JS ?>social_share.js"></script>

<script>
    // $(document).ready(function(){
        $("#tracking-deaitls").submit(function(e) {
            
        e.preventDefault();   
    var formData = $("#order_tracking_no").val();
    //alert(formData);
    $.ajax({
        type:"POST",
        url:BASE_URL+"MyGuestOrdersController/show_guest_tracking_details",
        dataType:"json",
        data:{formData:formData},
        // processData: false,
        // contentType: false,
        beforeSend: function(){
            // $('#ajax-spinner').show();
             $("#tackdata").html('');
             $("#productdetaildata").html('');
        },
        success:function(response){
            console.log(response.data);
            if(response.flag == 1){
                $("#customerdata").show();
                //console.log(response.data.tableData.productdata);
                var html="";
                html="<tr><td>"+response.data.tableData.order_customer_data.increment_id+"</td><td>"+response.data.tableData.order_customer_data.customer_firstname+"</td><td>"+response.data.tableData.order_customer_data.base_grand_total+"</td></tr>";
                $("#tackdata").append(html);
                $("#productdata").show();
                //var product_type = json[i]["product_type"];

                var productdata="";
                for(var i= 0;  i < response.data.tableData.productdata.length ; i++){
                    //var product_name = response.data.tableData.productdata[i]["product_name"];
                    productdata="<tr><td>"+response.data.tableData.productdata[i]['product_name']+"</td><td>"+response.data.tableData.productdata[i]['qty_ordered']+"</td><td>"+response.data.tableData.productdata[i]['cover_price']+"</td><td>"+response.data.tableData.productdata[i]['total_price']+"</td><td>"+response.data.tableData.productdata[i]['total_discount_amount']+"</td><td>"+response.data.tableData.productdata[i]['gift_name']+"</td></tr>";

                    $("#productdetaildata").append(productdata);
                }
                
                //<tr><td>"+firstname+"</td><td>"+lastname+"</td><td>"+address+"</td></tr>";
               
            }else if(response.flag == 2){
                $("#customerdata").hide();
                $("#productdata").hide();
                swal('error','No Order Found','error');

            }else{
                swal('error','Enter Order Number','error');
            }
        }
      });
    });
//});
</script>

</body>

</html>

