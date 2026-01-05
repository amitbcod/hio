<div class="row add-new-btn-webshop">
<form id="top_menu_form" action="" method="post">
<button type="submit" class="purple-btn">Update Position</button>
<input type="hidden" name="blockID" value="<?php echo $blockID; ?>">
<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
   <button class="white-btn" onclick="window.location.href='<?= base_url('webshop/add-custom-menu/'.$blockID) ?>';"> + Add New</button>
<?php } ?>
</div>
<div class="table-responsive text-center">
   <table class="table table-bordered table-style" id="customMenuTabless">

      <thead>
         <tr>
            <th>Menu</th>
            <th>Positions <span></span></th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
         <?php if(isset($customMenu) && $customMenu!='' ) { ?>
         <?php foreach ($customMenu as $block ) {
            $id = $block['id'];
            ?>
         <tr>
            <td><?php echo $block['menu_name']; ?></td>
			<td class="pos-td">
			<input type="number" name="position_<?php echo $block['id'];  ?>" value="<?php echo $block['position']; ?>"
			 class="form-control pos">
		</td>
            <td>
               <a class="link-purple" href="<?= base_url('webshop/edit-custom-menu/'.$block['id']) ?>">View</a>
             <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
                / <a class="link-purple deleteBlock" data-toggle="modal" data-target="#deleteModal" data-id="<?= $block['id'] ?>">Delete</a>
             <?php } ?>
            </td>
         </tr>
         <?php
            if(isset($block['menu_level_1']) && $block['menu_level_1']!='') {
                  foreach($block['menu_level_1'] as $menu_level1) { ?>
         <tr>
            <td>&nbsp;&nbsp;<?php echo '-'.$menu_level1['menu_name']; ?></td>
			<td class="pos-td">
			<input type="number" name="position_<?php echo $menu_level1['id'];  ?>" value="<?php echo $menu_level1['position']; ?>"
			 class="form-control pos">

		</td>


            <td>
               <a class="link-purple" href="<?= base_url('webshop/edit-custom-menu/'.$menu_level1['id']) ?>">View</a>
               <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
                / <a class="link-purple deleteBlock" data-toggle="modal" data-target="#deleteModal" data-id="<?= $menu_level1['id'] ?>">Delete</a>
             <?php } ?>
            </td>
            <?php $shopData = $this->CommonModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);
               if(isset($shopData) && $shopData->multi_lang_flag == 1) {  ?>
            <td>
               <?php
                  foreach ($languagesListing as $key => $value) {
                    $code = $value['code'];
                    $count = $this->WebshopModel->countCustomMenu($menu_level1['id'], $code);
                    if ($count>0) {
                     ?>
               <a class="edit-cat fa fa-edit"  onclick="OpenEditMenu(<?php echo $menu_level1['id']; ?>,'<?php echo $value['code']; ?>');"></a>
               <?php } else { ?>
               <a class="edit-cat fa fa-plus"  onclick="OpenEditMenu(<?php echo $menu_level1['id']; ?>,'<?php echo $value['code']; ?>');"></a>
               <?php }} ?>
            </td>
            <?php } ?>
         </tr>
         <?php
            if(isset($menu_level1['menu_level_2']) && $menu_level1['menu_level_2']!='') {
              foreach($menu_level1['menu_level_2'] as $menu_level2) { ?>
         <tr>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?php  echo '--'.$menu_level2['menu_name']; ?></td>
			<td class="pos-td">
			<input type="number" name="position_<?php echo $menu_level1['id'];  ?>" value="<?php echo $menu_level2['position']; ?>"
			 class="form-control pos">
		</td></td>

            <td>
               <a class="link-purple" href="<?= base_url('webshop/edit-custom-menu/'.$menu_level2['id']) ?>">View</a>
               <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
                / <a class="link-purple deleteBlock" data-toggle="modal" data-target="#deleteModal" data-id="<?= $menu_level2['id'] ?>">Delete</a>
             <?php } ?>
            </td>
            <?php $shopData = $this->CommonModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);
               if(isset($shopData) && $shopData->multi_lang_flag == 1) {  ?>
            <td>
               <?php
                  foreach ($languagesListing as $key => $value) {
                    $code = $value['code'];
                    $count = $this->WebshopModel->countCustomMenu($menu_level2['id'], $code);
                    if ($count>0) {
                     ?>
               <a class="edit-cat fa fa-edit"  onclick="OpenEditMenu(<?php echo $menu_level2['id']; ?>,'<?php echo $value['code']; ?>');"></a>
               <?php } else { ?>
               <a class="edit-cat fa fa-plus"  onclick="OpenEditMenu(<?php echo $menu_level2['id']; ?>,'<?php echo $value['code']; ?>');"></a>
               <?php }} ?>
            </td>
            <?php } ?>
         </tr>
         <?php
            if(isset($menu_level2['menu_level_3']) && $menu_level2['menu_level_3']!='') {
              foreach($menu_level2['menu_level_3'] as $menu_level_3) {
				?>
         <tr>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php  echo  '---'.$menu_level_3['menu_name']; ?></td>
            <td class="pos-td">
			<input type="number" name="position_<?php echo $menu_level1['id'];  ?>" value="<?php echo $menu_level_3['position']; ?>"
			 class="form-control pos">
		</td></td>
            <td>
               <a class="link-purple" href="<?= base_url('webshop/edit-custom-menu/'.$menu_level_3['id']) ?>">View</a>
             <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/website_configuration/write',$this->session->userdata('userPermission'))){ ?>
                / <a class="link-purple deleteBlock" data-toggle="modal" data-target="#deleteModal" data-id="<?= $menu_level_3['id'] ?>">Delete</a>
             <?php } ?>
            </td>
            <?php $shopData = $this->CommonModel->getShopData($_SESSION['ShopOwnerId'],$_SESSION['ShopID']);
               if(isset($shopData) && $shopData->multi_lang_flag == 1) {  ?>
            <td>
               <?php
                  foreach ($languagesListing as $key => $value) {
                    $code = $value['code'];
                    $count = $this->WebshopModel->countCustomMenu($menu_level_3['id'], $code);
                    if ($count>0) {
                     ?>
               <a class="edit-cat fa fa-edit"  onclick="OpenEditMenu(<?php echo $menu_level_3['id']; ?>,'<?php echo $value['code']; ?>');"></a>
               <?php } else { ?>
               <a class="edit-cat fa fa-plus"  onclick="OpenEditMenu(<?php echo $menu_level_3['id']; ?>,'<?php echo $value['code']; ?>');"></a>
               <?php }} ?>
            </td>
            <?php } ?>
         </tr>
         <?php } } ?>
         <?php } } ?>
         <?php } } ?>
         <?php }  } else { ?>
         <tr class="odd">
            <td valign="top" colspan="7" class="dataTables_empty">No data available in table</td>
         </tr>
         <?php } ?>
      </tbody>
	  </form>
	</table>
</div>
