<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
   <?php  //$this->load->view('seller/products/breadcrums'); ?>
   <div class="tab-content">
      <div id="addnew" class="tab-pane fade active show">
         <form name="product-frm-detail" id="product-frm-detail" method="POST" action="<?php echo base_url() ?>" enctype="multipart/form-data">
            <input type="hidden" value="edit" id="current_page" name="current_page">
            <input type="hidden" value="<?php echo $ProductData->id; ?>" id="pid" name="pid">
            <div class="product-details-block read-only ">
               <!-- read-only -->
               <div class="row">
                  <div class="col-md-6">
                     <?php
                        $ParentCategory=$this->CommonModel->get_category_for_seller($shop_id);

                        $gender=$ProductData->gender;

                        if(isset($gender) && $gender!=''){
                        $gender_arr=explode(',',$gender);

                        }else{
                        $gender_arr=array();
                        }
                        ?>
                     <h2>Product Details</h2>
                     <div class="col-sm-12"><input type="text" class="form-control" name="product_name" value="<?php echo $ProductData->name; ?>" id="product_name" placeholder="Product Name *"></div>
                     <div class="col-sm-12"><input type="text" class="form-control" name="product_code" id="product_code"  value="<?php echo $ProductData->product_code; ?>" placeholder="Product Code *"></div>
                     <div class="row">
                        <label>Category</label>
                        <div class="col-sm-12" id="category-tree">
                           <?php require_once('category_checkbox.php'); ?>
                        </div>
                     </div>
                     <div class="col-sm-12 gender-box">
                        <label>Gender</label>
                        <div class="gender-box-inner">
                           <div class="col-sm-3 read-only"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Men"  <?php echo (count($gender_arr)>0 && in_array('Men',$gender_arr))?'checked':''; ?>><span class="checked"></span>Men</label></div>
                           <div class="col-sm-3 read-only"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Women" <?php echo (count($gender_arr)>0 && in_array('Women',$gender_arr))?'checked':''; ?>><span class="checked"></span>Women</label></div>
                           <div class="col-sm-3 read-only"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Children" <?php echo (count($gender_arr)>0 && in_array('Children',$gender_arr))?'checked':''; ?>><span class="checked"></span>Children</label></div>
                           <div class="col-sm-3 read-only"><label class="checkbox"><input type="checkbox" name="gender[]" class="form-control" value="Unisex"  <?php echo (count($gender_arr)>0 && in_array('Unisex',$gender_arr))?'checked':''; ?>><span class="checked"></span>Unisex</label></div>
                        </div>
                     </div>
                     <div class="col-sm-12 read-only">
                        <h2>Description <span class="required">*</span></h2>
                        <textarea class="form-control mini-text-editor" id="description" name="description"><?php echo (isset($ProductData->description) && $ProductData->description!='')?$ProductData->description:''; ?></textarea>
                     </div>
                  </div>
                  <!-- col-md-6 -->
                  <div class="col-md-6 read-only">
                     <h2>Product Highlights <span class="required">*</span></h2>
                     <div class="col-sm-12"><textarea class="form-control product-highlight-textarea mini-text-editor" id="highlights"  name="highlights" ><?php echo (isset($ProductData->highlights) && $ProductData->highlights!='')?$ProductData->highlights:''; ?></textarea></div>
                     <div class="col-sm-12">
                        <h2>Product Review Code</h2>
                        <input type="text" class="form-control" name="product_reviews_code" id="product_reviews_code" placeholder="Product Review Code"  value="<?php echo (isset($ProductData->product_reviews_code) && $ProductData->product_reviews_code!='')?$ProductData->product_reviews_code:''; ?>">
                     </div>
                     <div class="col-sm-12">
                        <h2>Launch Date</h2>
                        <input type="text" class="form-control" id="launch_date" name="launch_date" readonly placeholder="Launch Date" value="<?php echo (isset($ProductData->launch_date) && $ProductData->launch_date!='0')?date('d-m-Y',$ProductData->launch_date):''; ?>">
                     </div>
                  </div>
                  <!-- col-md-6 -->
                  <div class="col-md-6">
                     <h2>Product Media <span class="required">*</span></h2>
                     <div class="col-sm-12">
                        <div class="" id="media-block">
                           <div class="uploadPreview" id="uploadPreview">
                              <?php
                                 if(isset($ProductMedia) && count($ProductMedia)>0){
                                 	foreach($ProductMedia as $media){ ?>
                              <input type="hidden" value="<?php echo $media->id; ?>" name="media_ids[]" class='m-img'>
                              <span class="single-img radio" id="media-file-<?php echo $media->id; ?>">
                              <a href="javascript:void(0);" onclick="removeMediaFile(<?php echo $media->id; ?>)" class="rm-media d-none">X</a>
                              <img src="<?= get_s3_url('products/thumb/'.$media->image) ?>" class="thumb">
                              <label>
                              <input type="radio" name="default_image" <?php echo (isset($media->is_base_image) && $media->is_base_image==1)?'checked':''; ?> value="<?php echo (isset($media->is_base_image) && $media->is_base_image==1)?$media->image_title:''; ?>">&nbsp;<span class="checkmark"></span>
                              </label>
                              </span>
                              <?php
                                 }
                                 }
                                 ?>
                           </div>
                           <input type="hidden" name="deleted_md" id="deleted_md" value="">
                        </div>
                     </div>
                  </div>
                  <!-- col-md-6 -->
                  <div class="col-md-6">
                     <h2>Product Shipment</h2>
                     <div class="col-sm-12"><input type="text" class="form-control" id="estimate_delivery_time" name="estimate_delivery_time" placeholder="Estimate Delivery Time" value="<?php echo (isset($ProductData->estimate_delivery_time) && $ProductData->estimate_delivery_time!='')?$ProductData->estimate_delivery_time:''; ?>"></div>
                     <div class="col-sm-12"><input type="text" class="form-control" id="product_return_time"  name="product_return_time" placeholder="Product Return Time"  value="<?php echo (isset($ProductData->product_return_time) && $ProductData->product_return_time!='')?$ProductData->product_return_time:''; ?>"></div>
                     <h2 class="product-drop-shipment-head">Product Drop-Shipment</h2>
                     <div class="radio">
                        <label><input type="radio" name="product_drop_shipment" <?php echo (isset($ProductData->product_drop_shipment) && $ProductData->product_drop_shipment=='1')?'checked':''; ?> value="1">Allow <span class="checkmark"></span></label>
                     </div>
                     <!-- radio -->
                     <div class="radio">
                        <label><input type="radio" name="product_drop_shipment"  <?php echo (isset($ProductData->product_drop_shipment) && $ProductData->product_drop_shipment=='0')?'checked':''; ?> value="0">Deny <span class="checkmark"></span></label>
                     </div>
                     <!-- radio -->
                  </div>
                  <!-- col-md-6 -->
                  <div class="col-md-12 product-variant product-attributes " id="attribute_list_outer">
                     <h2>Product Attributes  <span class="product-variant-button " id="add_attr_bottom"></span></h2>
                     <div class="table-responsive text-center " id="attribute_list">
                        <table class="table table-bordered table-style">
                           <thead>
                              <tr>
                                 <th>Name</th>
                                 <th>Value</th>
                                 <th class="<?php echo ((isset($side_menu) && $side_menu=='product_view'))?'d-none':''; ?>">ACTION</th>
                              </tr>
                           </thead>
                           <tbody id="attr_tbody">
                              <?php include('load_ep_attributes.php'); ?>
                           </tbody>
                        </table>
                        <?php
                           $selected_attributes = (isset($selected_attributes) && count($selected_attributes)>0)?implode(',',$selected_attributes):''; ?>
                        <input type="hidden" name="added_attr" id="added_attr" value="<?php echo $selected_attributes; ?>">
                     </div>
                  </div>
                  <div class="col-md-12 product-variant "  id="<?php echo ($ProductData->product_type=='configurable')?'variant_info_block':'single_info_block'; ?>">
                     <h2>Product <?php echo ($ProductData->product_type=='simple')?'Stock':'Variants'; ?> <span class="required">*</span> </h2>
                     <div class="table-responsive text-center" id="<?php echo ($ProductData->product_type=='configurable')?'variant_info':'single_info'; ?>">
                        <?php if($ProductData->product_type=='configurable'){
                           //var_dump($VariantProducts);

                           				?>
                        <table class="table table-bordered table-style">
                           <thead>
                              <tr>
                                 <?php
                                    $sv_arr=array();
                                    if(isset($VariantMaster) && count($VariantMaster)>0){
                                    foreach($VariantMaster as $attr){
                                    	$sv_arr[]=$attr['attr_id'];

                                    ?>
                                 <th><?php echo $attr['attr_name']; ?> </th>
                                 <?php }
                                    } ?>
                                 <th>INVENTORY</th>
                                 <!-- <th>COST PRICE </th> -->
                                 <th>SELLING PRICE </th>
                                 <th>TAX (%) </th>
                                 <th>WEBSHOP PRICE </th>
                                 <th>SKU </th>
                                 <th>BARCODE </th>
                                 <th class="<?php echo ((isset($side_menu) && $side_menu=='product_view'))?'d-none':''; ?>">MEDIA STATUS</th>
                                 <th class="<?php echo ((isset($side_menu) && $side_menu=='product_view'))?'d-none':''; ?>">ACTION</th>
                              </tr>
                           </thead>
                           <tbody id="variant_tbody">
                              <?php include('load_ep_variants_detail_page.php'); ?>
                           </tbody>
                        </table>
                        <input type="hidden" name="added_variant" id="added_variant" value="<?php echo implode(',',$sv_arr); ?>">
                        <input type="hidden" name="deleted_vs" id="deleted_vs" value="">
                        <?php }else { ?>
                        <table class="table table-bordered table-style">
                           <thead>
                              <tr>
                                 <th>SKU </th>
                                 <th>BARCODE </th>
                                 <th>INVENTORY</th>
                                 <!-- <th>COST PRICE </th> -->
                                 <th>SELLING PRICE </th>
                                 <th>TAX (%) </th>
                                 <th>WEBSHOP PRICE </th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td><input type="text" class="form-control input-sm" readonly name="sku" id="sku" value="<?php echo $ProductData->sku; ?>"></td>
                                 <td><input type="text" class="form-control input-sm" readonly name="barcode" id="barcode" maxlength="48" value="<?php echo $ProductData->barcode; ?>"></td>
                                 <td><input type="text" class="form-control input-sm" readonly name="stock_qty" id="stock_qty"  value="<?php echo $ProductStock->qty; ?>"></td>
                                 <!--  <td><input type="text" class="form-control input-sm" readonly name="cost_price" id="cost_price"  value="<?php //echo $ProductData->cost_price; ?>"></td> -->
                                 <td><input type="text" class="form-control input-sm" readonly name="price" id="price"  value="<?php echo $ProductData->price; ?>"></td>
                                 <td><input type="text" class="form-control input-sm" readonly name="tax_percent" id="tax_percent"  value="<?php echo $ProductData->tax_percent; ?>"></td>
                                 <td><input type="text" class="form-control input-sm" readonly name="webshop_price" id="webshop_price"  value="<?php echo $ProductData->webshop_price; ?>"></td>
                              </tr>
                           </tbody>
                        </table>
                        <?php } ?>
                     </div>
                  </div>
                  <input type="hidden" class="" id="product_type" name="product_type"  value="<?php echo $ProductData->product_type; ?>">
                  <div class="save-discard-btn">
                     <!--button type="button" class="white-btn" onclick="gotoLocation('<?php //echo base_url() ?>/'); ">Discard</button-->
                  </div>
               </div>
               <!-- row -->
            </div>
            <!-- product-details-block -->
         </form>
      </div>
   </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
