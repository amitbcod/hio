<div class="row">
   <div class="col-md-6">
      <h1 class="head-name">Category Translation</h1>
   </div>
   <div class="col-md-6 add-flag-translation">
      <?php foreach ($languagesListing as $key => $value) {

         ?>
         <img title="<?php echo $value['name']; ?>" src="<?php echo SKIN_IMG.$value['code'].'-flag.png' ?>">
       <?php } ?>
   </div>
</div>
<?php if(isset($browse_category) && !empty($browse_category)) {?>
<form method="POST" id="categoryMenuForm" action="">
   <input type="hidden" name="block_id" value="">
   <div class="parent-menu-list-inner">
      <ul>
         <?php foreach($browse_category as $main_cat) {
            $id = $main_cat['id'];
            ?>
         <li>
            <div class="row">
               <div class="col-md-6">
                  <label class="checkbox">
                     <p><?php echo $main_cat['cat_name'];?></p>
                  </label>
               </div>
               <div class="col-md-6 edit-add-translation">
                  <?php foreach ($languagesListing as $key => $value) {
                     $code = $value['code'];
                     $count = $this->Multi_Languages_Model->CountMultiLangCategory($id, $code);
                     if($count > 0)
                     {
                      ?>
                  <a class="edit-cat fa fa-edit"  title="<?php echo $value['name']; ?>" onclick="OpenEditCategory(<?php echo $main_cat['id']; ?>,'<?php echo $value['code']; ?>');"></a>
                  <?php } else {   ?>
                  <a class="add-cat fa fa-plus"  title="<?php echo $value['name']; ?>" onclick="OpenEditCategory(<?php echo $main_cat['id']; ?>,'<?php echo $value['code']; ?>');"></a>
                  <?php } } ?>
               </div>
            </div>
            <?php if(isset($main_cat['cat_level_1'])) { ?>
            <ul>
               <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>
               <li>
                  <div class="row">
                     <div class="col-md-6">
                        <label class="checkbox">
                           <p><?php echo $cat_level1['cat_name']; ?> </p>
                        </label>
                     </div>
                     <div class="col-md-6 edit-add-translation">
                        <?php foreach ($languagesListing as $key => $value) {
                           $code = $value['code'];
                           $id=$cat_level1['id'];
                           $count = $this->Multi_Languages_Model->CountMultiLangCategory($id, $code);
                           if($count > 0)
                           {
                            ?>
                        <a class="edit-cat fa fa-edit" title="<?php echo $value['name']; ?>" onclick="OpenEditCategory(<?php echo $cat_level1['id']; ?>,'<?php echo $value['code']; ?>');"></a>
                        <?php } else {   ?>
                        <a class="add-cat fa fa-plus"  title="<?php echo $value['name']; ?>" onclick="OpenEditCategory(<?php echo $cat_level1['id']; ?>,'<?php echo $value['code']; ?>');"></a>
                        <?php } } ?>
                     </div>
                  </div>
                  <?php if(isset($cat_level1['cat_level_2'])) { ?>
                  <ul>
                     <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>
                     <li>
                        <div class="row">
                           <div class="col-md-6 ">
                              <label class="checkbox">
                                 <p><?php echo $cat_level2['cat_name']; ?> </p>
                              </label>
                           </div>
                           <div class="col-md-6 edit-add-translation">
                              <?php foreach ($languagesListing as $key => $value) {
                                 $code = $value['code'];
                                 $id=$cat_level2['id'];
                                 $count = $this->Multi_Languages_Model->CountMultiLangCategory($id, $code);
                                 if($count > 0)
                                 {
                                  ?>
                              <a class="edit-cat fa fa-edit" title="<?php echo $value['name']; ?>" onclick="OpenEditCategory(<?php echo $cat_level2['id']; ?>,'<?php echo $value['code']; ?>');"></a>
                              <?php } else {   ?>
                              <a class="add-cat fa fa-plus"  title="<?php echo $value['name']; ?>" onclick="OpenEditCategory(<?php echo $cat_level2['id']; ?>,'<?php echo $value['code']; ?>');"></a>
                              <?php } } ?>
                           </div>
                        </div>
                     </li>
                     <?php } ?>
                  </ul>
                  <?php } ?>
               </li>
               <?php } ?>
            </ul>
            <?php } ?>
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
