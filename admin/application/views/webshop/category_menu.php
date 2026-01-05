<h1 class="head-name">Menu List</h1>
<?php 
// echo "<pre>";print_r(value: $browse_category);die;
// phpinfo();
?>


<?php if(isset($browse_category) && !empty($browse_category)) {?>

  <form method="POST" id="categoryMenuForm" action="<?= base_url('WebshopController/saveCategoryMenu') ?>">
    <input type="hidden" name="block_id" value="<?= $blockID?>">
    <div class="parent-menu-list-inner">
      <ul>
        <li>
          <label class="checkbox">
            <input type="checkbox" id="ckbCheckAll"> Select All <span class="checked"></span>
          </label>
        </li>
        <?php foreach($browse_category as $main_cat) { ?>
        <li>
          <label class="checkbox">
            <input type="checkbox"  class="chk_cat_menu" <?php echo (isset($main_cat['category_id']) && $main_cat['category_id']==$main_cat['id']) ? 'checked' : ''; ?> name="chk_cat_menu[]" value="<?php echo $main_cat['id'] ?>" > <?php echo $main_cat['cat_name'];?><span class="checked"></span>
          </label>
          <input type="number" name="position_<?php echo $main_cat['id']; ?>" value="<?= isset($main_cat['position']) ? $main_cat['position'] : 0 ?>" class="cat-position form-control">
            <?php if(isset($main_cat['cat_level_1'])) { ?>
            <ul>
              <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>
                <li>
                  <label class="checkbox">
                    <input type="checkbox"  class="chk_cat_menu" <?php echo (isset($cat_level1['category_id']) && $cat_level1['category_id']==$cat_level1['id']) ? 'checked' : ''; ?> name="chk_cat_menu[]" value="<?php echo $cat_level1['id'] ?>" > <?php echo $cat_level1['cat_name']; ?>
                    <span class="checked"></span>
                  </label>
                  <input type="number" name="position_<?php echo $cat_level1['id']; ?>" value="<?= isset($cat_level1['position']) ? $cat_level1['position'] : 0 ?>" class="cat-position form-control">
                  <?php if(isset($cat_level1['cat_level_2'])) { ?>
                  <ul>
                    <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>
                      <li>
                        <label class="checkbox">
                          <input type="checkbox"  class="chk_cat_menu" <?php echo (isset($cat_level2['category_id']) && $cat_level2['category_id']==$cat_level2['id']) ? 'checked' : ''; ?> name="chk_cat_menu[]" value="<?php echo $cat_level2['id'] ?>" > <?php echo $cat_level2['cat_name']; ?>
                          <span class="checked"></span>
                        </label>
                        <input type="number" name="position_<?php echo $cat_level2['id']; ?>" value="<?= isset($cat_level2['position']) ? $cat_level2['position'] : 0 ?>" class="cat-position form-control">                  
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
      <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
        <div class="download-discard-small">
          <button class="download-btn menu-save-btn" type="submit">Save</button>
        </div><!-- download-discard-small -->
      <?php } ?>
    </div><!-- select-attributes -->
  </form>
<?php }else{
  echo "Not Available";
} ?>

<script>
  $(document).ready(function(){
      // Select/Deselect all
      $("#ckbCheckAll").on('change', function(){
          $(".chk_cat_menu").prop('checked', this.checked);

          if(this.checked){
              // set all cat-position inputs to 0 when select all
              $(".cat-position").val(0);
          }
      });

      // If any checkbox is unchecked, uncheck "Select All"
      $(".chk_cat_menu").on('change', function(){
          if(!this.checked){
              $("#ckbCheckAll").prop('checked', false);
          }
          // If all are checked, check "Select All"
          if($(".chk_cat_menu:checked").length === $(".chk_cat_menu").length){
              $("#ckbCheckAll").prop('checked', true);
          }
      });
  });
</script>
