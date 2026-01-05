<?php if (is_array($addressList) && count($addressList) > 0) {?>
    <?php foreach ($addressList as $list) {?>
        <div class="col-md-6"> <!-- This div should be taken in the loop .col-md-6 here -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <p><b><?= $list->first_name.' '.$list->last_name?></b></p>
                    <div class="tool-box">
                        <?php if ($list->is_default == 0) {?>
                        <?php if ($restricted_access != 'yes') { ?>
                        <a href="javascript:void(0)" class="make-default" data-address-id="<?= $list->id ?>"><span class="default"><?=lang('make_default')?></span></a>
                        <?php  } ?>
                        <?php } else {?>
                        <span class="default"><?=lang('default')?></span>
                        <?php }?>
                        <?php if ($list->is_default == 1 && $restricted_access == 'yes') { ?>
                        <?php } else { ?>
                            <span class="address-action"> 
                                <a href="javascript:void();" onclick="openAddressPopup('edit',<?= $_SESSION['LoginID']?>,<?= $list->id ?>)"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                                <a href="javascript:void();"  class="remove-address" data-address-id="<?= $list->id ?>"><i class="fa fa-trash" aria-hidden="true"></i></a>
                            </span>
                        <?php } ?>
                      
                       
                    </div>
                </div>
                <div class="panel-body">
                    <p><b><?= $list->mobile_no ?></b></p>
                    <p><?= $list->address_line1 ?>,<br><?= $list->address_line2 ?> <br><?= $list->city.', '.$list->state.' - '.$list->pincode ?></p>
                </div>
            </div>                  
        </div><!-- .col-md-6 till here -->
        <!--<div class="col-sm-6 bg-grey-full">
            <div class="user-address-full">
                <span class="user-address-name"><?= $list->first_name.' '.$list->last_name?></span>
                <?php if ($list->is_default == 0) {?>
                <?php if ($restricted_access != 'yes') { ?>
                <a href="javascript:void(0)" class="make-default" data-address-id="<?= $list->id ?>"><span class="default"><?=lang('make_default')?></span></a>
                <?php  } ?>
                <?php } else {?>
                <span class="default"><?=lang('default')?></span>
                <?php }?>
                <?php if ($list->is_default == 1 && $restricted_access == 'yes') { ?>

                <?php } else { ?>
                    <span class="address-action"> <a class="edit-address" href="javascript:void(0)" onclick="openAddressPopup('edit',<?= $_SESSION['LoginID']?>,<?= $list->id ?>)"><i class="<?php echo (THEMENAME=='theme_zumbawear') ? 'fa fa-edit' : 'icon-edit'; ?>"></i> <?=lang('edit')?></a> <a href="javascript:void(0)" class="remove-address" data-address-id="<?= $list->id ?>"><i class="<?php echo (THEMENAME=='theme_zumbawear') ? 'fa fa-trash' : 'icon-delete'; ?>"></i> <?=lang('remove')?></a></span>
                <?php } ?>
                <?php if ($list->mobile_no != '') {?>
                <p class="mob-no"><?=lang('mo')?> <?= $list->mobile_no ?></p>
                <?php }?>
                <p class="full-address"><?= $list->address_line1 ?> <br><?= $list->city.', '.$list->state.' - '.$list->pincode ?></p>
                <?php if((int) $this->session->userdata('session_vat_flag') === 1): ?>
                <div class="company-details-addr-profile" >
                <p class="full-address"><?=lang('vat_no')?> <?= (($list->vat_no != '') ? $list->vat_no :'-') ?></p>
                <p class="full-address"><?=lang('company_name')?> <?= (($list->company_name != '') ? $list->company_name :'-') ?></p>
                </div>
                <?php endif; ?>
            </div><!-- user-address-name -->
       <!-- </div><!-- bg-grey-full -->
    <?php } ?>
<?php } ?>