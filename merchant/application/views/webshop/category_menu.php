<h1 class="head-name">Menu List</h1>
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
          <input type="checkbox" <?php echo (isset($main_cat['category_id']) && $main_cat['category_id']==$main_cat['id']) ? 'checked' : ''; ?> name="chk_cat_menu[]" value="<?php echo $main_cat['id'] ?>" > <?php echo $main_cat['cat_name'];?><span class="checked"></span>
        </label>
		<input type="number" name="position_<?php echo $main_cat['id']; ?>" value="<?= isset($main_cat) ? $main_cat['position'] : '' ?>" class="cat-position form-control">
         <?php if(isset($main_cat['cat_level_1'])) { ?>
        <ul>
          <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>
          <li>
            <label class="checkbox">
              <input type="checkbox" <?php echo (isset($cat_level1['category_id']) && $cat_level1['category_id']==$cat_level1['id']) ? 'checked' : ''; ?> name="chk_cat_menu[]" value="<?php echo $cat_level1['id'] ?>" > <?php echo $cat_level1['cat_name']; ?>
              <span class="checked"></span>
            </label>
			<input type="number" name="position_<?php echo $cat_level1['id']; ?>" value="<?= isset($cat_level1) ? $cat_level1['position'] : '' ?>" class="cat-position form-control">

            <?php if(isset($cat_level1['cat_level_2'])) { ?>
            <ul>
              <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>
                <li>
                  <label class="checkbox">
                    <input type="checkbox" <?php echo (isset($cat_level2['category_id']) && $cat_level2['category_id']==$cat_level2['id']) ? 'checked' : ''; ?> name="chk_cat_menu[]" value="<?php echo $cat_level2['id'] ?>" > <?php echo $cat_level2['cat_name']; ?>
                    <span class="checked"></span>
                  </label>
				  <input type="number" name="position_<?php echo $cat_level2['id']; ?>" value="<?= isset($cat_level2) ? $cat_level2['position'] : '' ?>" class="cat-position form-control">
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
