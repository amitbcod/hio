
   
    <div class="main-inner ">
        <form id="attributeForm" method="POST">
            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
			  <input type="hidden" name="attribute_id" id="attribute_id" value="">
			
            <div class="variant-common-block variant-list">
                <h1 class="head-name pad-bottom-20">Attribute</h1>
                    <div class="form-group row">
                        <label for="attribute_name" class="col-sm-3 col-form-label font-500">Attribute Name <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_name" id="attribute_name" >
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500">Attribute Code <span class="required">*</span></label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control" name="attribute_code" id="attribute_code" >
                        </div>
                  </div><!-- form-group -->

                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500">Attribute Description</label>
                        <div class="col-sm-7">
                            <textarea class="form-control" name="attribute_description" id="attribute_description"></textarea>
                        </div>
                  </div><!-- form-group -->
                  <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label font-500">Attribute Properties <span class="required">*</span></label>
                        <div class="col-sm-4">
                            <select class="form-control" name="attribute_properties" id="attribute_properties" >
                                <option value="">Select Attribute Properties</option>
                                <option value="1">Text Field</option>
                                <option value="2">Text Area</option>
                                <option value="3">Date</option>
                                <option value="4">Yes/No</option>
                                <option value="5">Dropdown</option>
                                <option value="6">Multiselect</option>

                            </select>
                        </div>
                  </div><!-- form-group -->
                  
                  <div class="form-group row" style="display: none;" id="slectvalue">
                    <label for="" class="col-sm-3 col-form-label font-500">Attribute Values <span class="required">*</span></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" data-role="tagsinput" name="tagsValues" id="tagsValues" >
                    </div>
                  </div>
				  
                <div class=" switch-onoff">
					  <div class="form-group row">
						<label for="" class="col-sm-3 col-form-label font-500">Display On Frontend</label>
						 <div class="col-sm-4">
							<label class="checkbox">
								<input type="checkbox" name="display_on_frontend" value="1" autocomplete="off"  > 
								<span class="checked"></span>
								</label>
							</div>
					</div>	
					
					 <div class="form-group row">
						<label for="" class="col-sm-3 col-form-label font-500">Filterable With Result</label>
												 <div class="col-sm-4">
							<label class="checkbox">
								<input type="checkbox" name="filterable_with_results" value="1" autocomplete="off"  > 
								<span class="checked"></span>
								</label>
							</div>
					</div>	
				</div><!-- bs-example -->


                  <div class="download-discard-small">
                        <button class="white-btn" type="button" onclick="OpenAttributeList('add_attr');" >Discard</button>
                        <button type="submit" class="download-btn"  id="attr_save_btn">Save</button>
                  </div><!-- download-discard-small OpenAttributeListPopup('<?php echo $category_id; ?>'); -->
            </div><!-- -common-block -->
        </form>

    </div><!-- add new tab -->
	<script type="text/javascript" src="<?php echo SKIN_JS; ?>attribute.js"></script>
<script>

$(document).ready(function(){
// Destroy all previous bootstrap tags inputs (optional)
      
		
		
		$( "#attribute_properties" ).change(function() {
			  if(this.value=='5' || this.value=='6'){
				  $('#slectvalue').show();
			  }else{
				   $('#slectvalue').hide();
				   $( "#tagsinput" ).val('');
				   $('input[data-role="tagsinput"]').tagsinput('destroy');
					
					
			  }
		});
		
		/*
		$("#attributeForm").validate({
	    ignore: ":hidden",
	    rules: {
	        attribute_name: {required: true,},
	        attribute_code: {required: true,},
	        attribute_properties: {required: true,},
			tagsValues: {
                required: {
                    depends: function(element) {
                        if ($('#attribute_properties option:selected').text() == 'Dropdown') {
                            return true;
                        }else if($('#attribute_properties option:selected').text() == 'Multiselect') {
                        	return true;
                        }else {
                            return false;
                        }
                    }
                },
            },
	    },
	    submitHandler: function(form) {
	        $.ajax({
	            url: BASE_URL+"sellerproduct/saveattribute",
	            method: 'POST',
                type: 'ajax',
                dataType: 'json',
                data: $(form).serialize(),
	            success: function(response) {
	                if (response.status == 200) {
						
	                   swal("Success", response.message, "success");
	                   
	                    if(response.category_id==0){
							CreateSingleAttributeHTML(response.attr_id);
						}else{
							OpenAttributeListPopup(response.category_id);
						}
	                }else{
	                    swal("Error", response.message, "error");
	                    return false;
	                }
	            },
	            error: function (response) {
	                console.log(response.responseText);
	                return false;
	            }
	        });
	        return false;
	    }
	});
	
	*/
  
});
</script>