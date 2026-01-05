<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
  <ul class="nav nav-pills">
    <!-- <li><a href="<?= base_url('webshop/themes') ?>">Themes</a></li> -->
    <li><a href="<?= base_url('webshop/settings') ?>">Settings</a></li>
    <li><a href="<?= base_url('webshop/customize-pages') ?>">Customize Pages</a></li>
    <li class="active"><a href="<?= base_url('webshop/static-blocks') ?>">Static Blocks</a></li>
    <li><a href="<?= base_url('webshop/payment') ?>">Payments</a></li>
    <li><a href="<?= base_url('webshop/product-blocks') ?>">Product Blocks</a></li>
  </ul>

  <div class="tab-content">
    <div id="menu-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name"> &nbsp; <?php if(isset($sMenus)) { echo "Edit";}else{ echo "Add New";} ?></h1>
      </div><!-- d-flex -->

      <!-- form -->
      <form method="POST" id="customMenuForm" action="<?= base_url('WebshopController/saveCustomMenu') ?>">
        <input type="hidden" name="block_id" value="<?= isset($blockID) ? $blockID :''?>">
         <input type="hidden" name="menu_id" value="<?= isset($sMenus) ? $sMenus->id :''?>">
        <div class="content-main form-dashboard webshop-menu">
          <div class="row">
            <div class="col-lg-12">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label font-500">Menu Name</label>
                <input class="form-control col-sm-4" type="text" name="menu_name" id="menu_name" value="<?= isset($sMenus) ? $sMenus->menu_name : '' ?>">
              </div>
            </div>
            <div class="col-lg-12">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label font-500">Menu Type </label>
                <select class="form-control col-sm-4" onchange="showFieldByMenyType(this.value);" name="m_type" id="m_type" >
                  <option value="1" <?= isset($sMenus) && $sMenus->menu_type == 1 ? 'Selected' : '' ?> > Custom Links</option>
                  <option value="2" <?= isset($sMenus) && $sMenus->menu_type == 2 ? 'Selected' : '' ?> >Pages</option>
                  <option value="3" <?= isset($sMenus) && $sMenus->menu_type == 3 ? 'Selected' : '' ?> >Categories</option>
                </select>
              </div>
              <div class="col-sm-12">
                <!-- custom links -->
                <?php
                  $style = '';
                  if(isset($sMenus)) {
                    $style = ($sMenus->menu_custom_url!='') ? 'display:flex;':'display:none;';
                  }
                ?>
                <div class="custom-link form-group row" id="custom_link" style="<?= $style ?>">
                  <label class="col-sm-2 col-form-label font-500">Custom Link </label>
                  <input class="form-control col-sm-4" type="url" placeholder="http/https" name="m_cust_link" id="m_cust_link" onchange="checkURL(this)" value="<?= isset($sMenus) ? $sMenus->menu_custom_url : '' ?>">
                </div>
                <!-- pages  -->
                <div class="custom-link form-group row" id="page_field" style="<?= (isset($sMenus) && $sMenus->page_id!=0) ? 'display:flex' : 'display: none;' ?>">
                  <label class="col-sm-2 col-form-label font-500">Pages</label>
                  <select class="form-control col-sm-4" name="m_page" id="m_page">
                    <?php foreach($cmsPages as $pages){?>
                    <option value="<?= $pages->id ?>" <?= isset($sMenus) && $sMenus->page_id == $pages->id ? 'Selected' : '' ?> ><?= $pages->title; ?></option>
                    <?php } ?>
                  </select>
                </div>
                <!-- Categories  -->
                <div class="custom-link form-group row" id="category_field" style="<?= (isset($sMenus) && $sMenus->category_id!=0) ? 'display:flex;' : 'display: none;' ?>">
                  <label class="col-sm-2 col-form-label font-500">Categories</label>
                  <select class="form-control col-sm-4" name="m_category" id="m_category">
                    <?php foreach($browse_category as $main_cat) { ?>

                      <option value="<?= $main_cat['id']; ?>" <?= isset($sMenus) && $sMenus->category_id == $main_cat['id'] ? 'Selected' : '' ?> > <?= $main_cat['cat_name']; ?></option>

                        <?php foreach($main_cat['cat_level_1'] as $cat_level1) { ?>

                          <option value="<?= $cat_level1['id']; ?>" <?= isset($sMenus) && $sMenus->category_id == $cat_level1['id'] ? 'Selected' : '' ?> ><?= '-'.$cat_level1['cat_name']; ?></option>

                            <?php if(isset($cat_level1['cat_level_2'])) { ?>

                            <?php foreach($cat_level1['cat_level_2'] as $cat_level2) { ?>

                              <option value="<?= $cat_level2['id']; ?>" <?= isset($sMenus) && $sMenus->category_id == $cat_level2['id'] ? 'Selected' : '' ?> ><?= '--'.$cat_level2['cat_name']; ?></option>

                            <?php } } ?>

                        <?php } ?>

                    <?php } ?>
                  </select>
                </div>
                <!-- Parent menu  -->
                <?php //echo "<pre>";print_r($browse_menus);exit;?>
                <div class="custom-link form-group row">
                  <label class="col-sm-2 col-form-label font-500">Parent Menu</label>
                  <select class="form-control col-sm-4" name="m_menu" id="m_menu">
                    <option value="none"> None </option>
                    <?php if(isset($browse_menus) && $browse_menus!='') {?>
                      <?php foreach($browse_menus as $menu) { ?>
                      <option value="<?= $menu['id']; ?>" <?= isset($sMenus) && $sMenus->menu_parent_id == $menu['id'] ? 'Selected' : '' ?> ><?= $menu['menu_name']; ?></option>
                         <?php if(isset($menu['menu_level_1']) && $menu['menu_level_1']!='') { ?>

                          <?php foreach($menu['menu_level_1'] as $menu_level1) { ?>

                          <option value="<?= $menu['id']; ?>,<?= $menu_level1['id']; ?>" <?= isset($sMenus) && $sMenus->menu_parent_id == $menu_level1['id'] ? 'Selected' : '' ?>>
                            <?= '-'.$menu_level1['menu_name']; ?>
                          </option>

                            <?php if(isset($menu_level1['menu_level_2']) && $menu_level1['menu_level_2']!='') { ?>
                              <?php foreach($menu_level1['menu_level_2'] as $menu_level_2) { ?>
                                <option value="<?= $menu['id']; ?>,<?= $menu_level1['id']; ?>,<?= $menu_level_2['id']; ?>" <?= isset($sMenus) && $sMenus->menu_parent_id == $menu_level_2['id'] ? 'Selected' : '' ?>>
                                  <?= '--'.$menu_level_2['menu_name']; ?>
                                </option>

                              <?php } ?>
                             <?php } ?>
                          <?php } ?>
                        <?php } ?>
                      <?php } ?>
                    <?php } ?>
                  </select>
                </div>
              </div> <!--- col sm -12 -->
            </div>
			<div class="col-lg-12">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label font-500">Position</label>
                <input class="form-control col-sm-4" type="number" name="position" id="position" value="<?= isset($sMenus) ? $sMenus->position : '' ?>">
              </div>
            </div>
            <div class="col-lg-12">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label font-500">Status</label>
                <div class="radio row">
                  <div class="col-sm-4">
                    <label>
                      <input type="radio" name="cust_menu_status" <?= isset($sMenus) && $sMenus->status == 1 ? 'checked' : 'checked' ?> value="1">Enable
                      <span class="checkmark"></span>
                    </label>
                  </div>
                  <div class="col-sm-4">
                    <label><input type="radio" name="cust_menu_status" <?= isset($sMenus) && $sMenus->status == 0 ? 'checked' : '' ?> value="0">Disabled <span class="menu-status "></span> <span class="checkmark"></span></label>
                  </div>
                </div>
              </div><!-- form-group -->
            </div>
            <div class="col-sm-12">
            <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
              <div class="download-discard-small">
                <button class="download-btn" type="submit">Save</button>
              </div>
            <?php } ?>
            </div>
          </div>
        </div>
      </form>
    </div>

  </div>
</main>

<script src="<?php echo SKIN_JS; ?>webshop.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
