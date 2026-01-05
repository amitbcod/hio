<?php $this->load->view('common/fbc-user/header'); ?>         
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <div class="tab-content">
    <div id="menu-tab" class="tab-pane fade in active common-tab-section min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        
        
      </div><!-- d-flex -->
		  <!-- form -->

    		<div class="parent-menu-list" id="category_menu_list" style="">
         	<div class="row">
   <div class="col-md-6">
      <h1 class="head-name">Variants Translation</h1>
   </div>
   <div class="col-md-6 add-flag-translation">
      <?php foreach ($languagesListing as $key => $value) {

         ?>      
         <img title="<?php echo $value['name']; ?>" src="<?php echo SKIN_IMG.$value['code'].'-flag.png' ?>">
       <?php } ?>
   </div>
</div>
<?php if(isset($VariantsBySeller) && !empty($VariantsBySeller)) {?>
<form method="POST" id="categoryMenuForm" action="">
   <input type="hidden" name="block_id" value="">
   <div class="parent-menu-list-inner">
      <ul>
         <?php foreach($VariantsBySeller as $main_cat) {
            $id = $main_cat['id'];
            ?> 
         <li>
            <div class="row">
               <div class="col-md-6">
                  <label class="checkbox">
                     <p><?php echo $main_cat['attr_name'];?></p>
                  </label>
               </div>
               <div class="col-md-6 edit-add-translation">
                  <?php foreach ($languagesListing as $key => $value) { 
                     $code = $value['code'];
                     $countVariant = $this->Multi_Languages_Model->getSingleDataByID('multi_lang_eav_attributes',
                        array('attr_id'=>$id,'lang_code'=>$code),'id');
                     if(isset($countVariant))
                     {
                      ?>
                  <a class="edit-cat fa fa-edit"  title="<?php echo $value['name']; ?>" onclick="OpenEditVariants(<?php echo $main_cat['id']; ?>,'<?php echo $value['code']; ?>');"></a> 
                  <?php } else {   ?>
                  <a class="add-cat fa fa-plus"  title="<?php echo $value['name']; ?>" onclick="OpenEditVariants(<?php echo $main_cat['id']; ?>,'<?php echo $value['code']; ?>');"></a>
                  <?php } } ?> 
               </div>
            </div>
         </li>
         <?php } ?>
      </ul>

      <div class="download-discard-small">
         <!-- <button class="download-btn menu-save-btn" type="submit">Save</button> -->
      </div>
      <!-- download-discard-small -->
   </div>
   <!-- select-attributes -->
</form>
<?php }else{ 
   echo "Not Available";
   } ?>
        </div>
        </div>
			
      </div>
        <!--end form-->
    </div>
	</div>
</main>

<script src="<?php echo SKIN_JS; ?>Multi_Languages_setting.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>         


