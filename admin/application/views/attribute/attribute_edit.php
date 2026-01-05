<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
        <li ><a href="<?= base_url('attribute') ?>">Attribute</a></li>
        <li class="active"><a>Edit Attribute</a></li>
    </ul>
    <div class="main-inner min-height-480">
    <form id="attributeForm" method="POST" action="<?php echo base_url('AttributeController/submitAttribute'); ?>">
            <input type="hidden" name="attribute_id" value="<?= $attribute->id ?>">
            <input type="hidden" name="type" value="1">
            <div class="variant-common-block variant-list">
                <h1 class="head-name pad-bottom-20">Attribute</h1>
                    <div class="form-group row">
                        <label for="attribute_name" class="col-sm-2 col-form-label font-500">Attribute Name</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control" name="attribute_name" id="attribute_name" value="<?= $attribute->attr_name ?>" onkeypress= 'return /^[a-zA-Z\s]+$/i.test(event.key)' maxlength = "30" required >
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Attribute Code</label>
                        <div class="col-sm-3">
                        <input type="hidden" name="attribute_code" value="<?= $attribute->attr_code ?>">
                          <input type="text" class="form-control" name="attribute_code" id="attribute_code" value="<?= $attribute->attr_code ?>" required disabled>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Attribute Description</label>
                        <div class="col-sm-3">
                            <textarea class="form-control" name="attribute_description" id="attribute_description"   maxlength= "250"><?= $attribute->attr_description ?></textarea>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Attribute Properties</label>
                        <div class="col-sm-3">
                            <?php $seValue = $attribute->attr_properties ?>
                            <input type="hidden" name="attribute_properties" value="<?= $seValue ?>">
                            <select class="form-control" name="attribute_properties" id="attribute_properties" required disabled>
                                <option value="">Select Attribute Properties</option>
                                <option value="1" <?php if($seValue == 1) echo "selected";?>>Text Field</option>
                                <option value="2" <?php if($seValue == 2) echo "selected";?>>Text Area</option>
                                <option value="3" <?php if($seValue == 3) echo "selected";?>>Date</option>
                                <option value="4" <?php if($seValue == 4) echo "selected";?>>Yes/No</option>
                                <option value="5" <?php if($seValue == 5) echo "selected";?>>Dropdown</option>
                                <option value="6" <?php if($seValue == 6) echo "selected";?>>Multiselect</option>
                            </select>
                        </div>
                  </div><!-- form-group -->
                  <?php if($seValue == 5 || $seValue == 6) {?>
                  <div class="form-group row" id="slectvalue">
                    <label for="" class="col-sm-2 col-form-label font-500">Attribute Values</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" data-role="tagsinput" name="tagsValues" id="tagsValues" required>
                        <div class="tagsinputvalues">
                        <input type="text" class="form-control" data-role="tagsinput" name="tagsnewValues" id="tagsnewValues" value="<?= $attributevalues ?>" required>
                      </div>
                    </div>
                  </div>
              <?php } ?>
                  <div class="form-group row">
                        <label for="" class="col-sm-2 col-form-label font-500">Status</label>
                        <div class="col-sm-3">
                            <?php $status = $attribute->status; ?>
                            <input type="radio" name="status" value="1" <?php if($status == 1){echo "checked";} ?>>
                          <label>Active</label><br>
                            <input type="radio" name="status" value="0" <?php if($status == 0){echo "checked";} ?>>
                            <label>In Active</label>
                        </div>
                  </div><!-- form-group -->
                  <div class="download-discard-small pos-ab-bottom">
                  <button class="white-btn" type="button"data-dismiss="modal" onclick="window.location.href='<?= base_url('attribute') ?>';">Discard</button> 
                        <button type="submit" class="download-btn">Save</button>
                  </div><!-- download-discard-small -->
            </div><!-- -common-block -->
        </form>
    </div><!-- add new tab -->
</div>
  </div>
</main>
<script src="<?php echo SKIN_JS; ?>attribute.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
