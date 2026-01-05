$(document).ready(function(){
  $("#launch_date").datepicker({
        autoclose: true,
        todayHighlight: true,
		startDate: new Date(2015, 0, 1),
		format:'dd-mm-yyyy',
  });

   $(".sis-datepicker").datepicker({
        autoclose: true,
        todayHighlight: true,
		//startDate: new Date(),
		format:'dd-mm-yyyy',
  });



  /*
  $("#gallery_image").change(function (e) {
	   e.preventDefault();
		  if(this.disabled) {

			swal("Error", "File upload not supported!", "error");
			return false;

		  }

		  var F = this.files;
		  if (F && F[0]) {
			for (var i = 0; i < F.length; i++) {
			  readMediaFiles(F[i]);
			}
		  }
	});
	*/





  $('#child_category').tagsinput({
      allowDuplicates: false,
        itemValue: 'id',  // this will be used to set id of tag
        itemText: 'label' // this will be used to set text of tag
    });


	$("#product_code").keyup(function(event) {
		if ($('#product_type').val() == 'simple') {
			var stt = $(this).val();
			$("#sku").val(stt);
		}
	});





});


function calculate_webshop_price(round_flag='',random_id=''){
	if($('#product_type').val() == 'simple'){
		var price = $('#price').val();


		var tax_percent = $('#tax_percent').val();
		/*
		if((tax_percent=='') ||  (tax_percent= undefined) || (tax_percent==0)){
			tax_percent=0;
		}
		*/


		var new_ws_price=price;
		//new_ws_price=parseFloat(new_ws_price);
		if(price>0 && tax_percent>0){
			var tax_amount=calculate_percentage(tax_percent,price);
			 new_ws_price = parseFloat(price) + parseFloat(tax_amount);
		}else{
			new_ws_price=parseFloat(price);
		}

		if(round_flag == 1){
			$("#webshop_price").val(Math.round(new_ws_price).toFixed(2));
		}else{
			$("#webshop_price").val(new_ws_price.toFixed(2));
		}
	}else{

		if(random_id!=''){
			var price = $('#variant_price_'+random_id).val();
			var tax_percent = $('#variant_tax_percent_'+random_id).val();

			/*
			if((tax_percent=='') ||  (tax_percent= undefined) || (tax_percent==0)){
				tax_percent=0;
			}
			*/


			var new_ws_price=price;

			//new_ws_price=parseFloat(new_ws_price);
			if(price>0 && tax_percent>0){
				var tax_amount=calculate_percentage(tax_percent,price);
				 new_ws_price = parseFloat(price) + parseFloat(tax_amount);
			}else{
				new_ws_price=parseFloat(price);
			}

			if(round_flag == 1){
				$("#variant_webshop_price_"+random_id).val(Math.round(new_ws_price).toFixed(2));
			}else{
				$("#variant_webshop_price_"+random_id).val(new_ws_price.toFixed(2));
			}
		}

	}

}

function preview_images()
{
 var total_file=document.getElementById("gallery_image").files.length;
 for(var i=0;i<total_file;i++)
 {

  var uniqid=Date.now()
  $('#uploadPreview').append('<span class="single-img radio" id="media-file-'+uniqid+'"><a href="javascript:void(0);" onclick="removeMediaFile('+uniqid+')" class="rm-media">X</a><img src="' + URL.createObjectURL(event.target.files[i]) + '"  class="thumb"><label><input type="radio" name="default_image"  value="'+event.target.files[i].name+'"   id="media-selection-'+uniqid+'">&nbsp;<span class="checkmark"></span></label></span>');

	var default_image= $('input[name=default_image]:checked').val();

	var def_checked='';
	if(default_image=='' || default_image==undefined){
		if(i==0){
			//def_checked='"checked"';
			$('#media-selection-'+uniqid).attr('checked',true);

		}
	}


 }
}





function GetSellerSubCategoryAjax(parent_id)
{

	if(parent_id!='')
	{
		if(parent_id=='new'){
			OpenCategoryCreatePopup();
		}else{
			$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/getsubcategory",
				data: {parent_id:parent_id},
				async:false,
				complete: function () {
				},
				beforeSend: function(){
					// $('#ajax-spinner').show();
				},
				success: function(response) {
					if(response!='error'){
					// $('#ajax-spinner').hide();
						$('#SubCategoryOptions').html(response);

					}else{
						$('#SubCategoryOptions').html('<option value="">Select Sub Category</option>');
					}
				}
			});

			$('#bulk_cat_save').removeAttr('disabled');
		}

	}
	else{
		// $('#ajax-spinner').hide();
		$('#SubCategoryOptions').html('<option value="">Select Sub Category</option>');
		return false;
	}
}


function SelectAttributeAndVariants(category_id,flag){

	if($('#product_code').val()!=''){

		 if(flag=='edit_variant'){
			var pid=$('#pid').val();
			OpenVariantsListbyCategory(category_id,flag,pid);
		}else{

			if(flag=='import'){
				if(category_id!='' && category_id=='new'){
					//OpenSubCategoryCreatePopup();
					var root_category_id=$('#category').val();
					if(root_category_id>0){
						OpenEditCategory(root_category_id);
					}
				}
			}else{
				if(category_id!='' && category_id>0){
					if($('#attr_tbody').length>0){

					}else{
						LoadAttributebyCategory(category_id,flag);
					}
				OpenVariantsListbyCategory(category_id,flag);
				LoadTagsByCategory(category_id,flag);
				$('#add_attr_bottom').removeClass('d-none');
				}else if(category_id!='' && category_id=='new'){
					//OpenSubCategoryCreatePopup();
					var root_category_id=$('#category').val();
					if(root_category_id>0){
						OpenEditCategory(root_category_id);
					}
					$('#attribute_list').html('');
					$('#add_attr_bottom').addClass('d-none');
				}else{
					$('#attribute_list').html('');
					$('#add_attr_bottom').addClass('d-none');
				}

			}

		}

	}else{
		alert('test');
		$('#sub_category').val('');
		swal("Error", "Please enter product code.", "error");
		return false;
	}
}


function LoadAttributebyCategory(category_id,flag){
	if(category_id!='')
	{
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/getattributesbycategory",
			data: {category_id:category_id},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				$('#ajax-spinner').show();
			},
			success: function(response) {
				$('#ajax-spinner').hide();
				if(response!='error'){
				//
					$('#attribute_list').html(response);
				}else{
					$('#attribute_list').html('');
				}
			}
		});
	}
	else{
		$('#ajax-spinner').hide();
		$('#attribute_list').html('');
		return false;
	}
}


function Addattributesingle(flag){
	/*
	var sub_category=$('#SubCategoryOptions').val();

	if(sub_category==''){
		swal('Error','Please select category and sub category ','error');
		return false;
	}else{
		if(sub_category!='' && sub_category>0){
			OpenAttributeListPopup(sub_category,flag);
		}
		else{
			OpenCreateAttributePopup(0,flag);
		}
	}

	*/


	OpenCreateAttributePopup(0,flag);
}


function LoadTagsByCategory(parent_id,flag){
	if(parent_id!='')
	{
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/getsubcategorytags",
			data: {parent_id:parent_id},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				if(response!='error'){
				// $('#ajax-spinner').hide();
					$("#child_category").tagsinput('removeAll');

					if(response!='[]'){
						//$('#child_category').val(response);

						var cat_arr = jQuery.parseJSON(response);

						$.each(cat_arr, function(index, value) {
							$('#child_category').tagsinput('add', value);

						});

					}else{
						$("#child_category").tagsinput('removeAll');

					}

				}else{
					$("#child_category").tagsinput('removeAll');

					$('#child_category').val('');
				}
			}
		});
	}
	else{
		// $('#ajax-spinner').hide();
		$('#child_category').val('');
		return false;
	}
}


function removeAttrRow(id){
	$('#attr_row_'+id).remove();

	if($('.at-row').length){

	}else{
		$('#added_attr').val('');
	}

	resetAttribute(id,'attributes');

}


function removeVariantRow(id,pid=''){
	$('#variant_row_'+id).remove();

	var current_page=$('#current_page').val();

	if($('.vr-row').length){

	}else{
		if(current_page=='add'){
		//$('#added_variant').val('');
		}
	}

	if(pid!='' && pid>0){
		var prev_list=$('#deleted_vs').val();
		if(prev_list!=''){
			var new_list=','+pid;
			new_list=prev_list+','+new_list;
			$('#deleted_vs').val(new_list);
		}else{
			var new_list=pid;

			$('#deleted_vs').val(new_list);
		}

	}

}


function OpenVariantsListbyCategory(category_id,flag,pid=''){
	if(category_id!='')
	{

	}else{
		category_id=0;
	}

		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/getvariantbycategory",
			data: {category_id:category_id,flag:flag,pid:pid},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {

				if(response!='error'){
				// $('#ajax-spinner').hide();
					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{
					$('#variant_list').html('');
				}

				$("#FBCUserSecondaryModal").modal('hide');
				 $("#modal-content-second").html('');
			}
		});

}


function OpenVariantsMaster(category_id,flag=''){
	if(category_id!='')
	{

		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openvariantmaster",
			data: {category_id:category_id,flag:flag},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {

				if(response!='error'){
				// $('#ajax-spinner').hide();
					$("#FBCUserSecondaryModal").modal();
					$("#modal-content-second").html(response);
				}else{
					//$('#variant_list').html('');
				}

			}
		});
	}
	else{
		// $('#ajax-spinner').hide();
		//$('#variant_list').html('');
		return false;
	}
}

function SaveProductType(){
	let simple_product = $('#simple_product').is(':checked');

	let Rounded_price_flag = $('#Rounded_price_flag').val();

	if(simple_product==1){
		$('#temp_add_var_single').addClass('d-none');
		$('#product_type').val('simple');
		$('#single_info_block').removeClass('d-none');
		let pro_code=$('#product_code').val();
		var uniqid=Date.now()
		var barcode_text=pro_code+uniqid;
		let single_product_tbl='<table class="table table-bordered table-style"><thead><tr><th>SKU </th><th>INVENTORY</th><th>COST PRICE </th><th>SELLING PRICE </th><th>TAX (%) </th><th>WEBSHOP PRICE </th><th>GIFT </th><th>SUB ISSUES </th></tr></thead><tbody><tr><td><input type="text" class="form-control input-sm "  name="sku" id="sku" value="'+pro_code+'"></td><td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);"  maxlength="10" name="stock_qty" id="stock_qty" placeholder="40000"></td><td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);" name="cost_price" id="cost_price" ></td>	<td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);" name="price" id="price" onblur="calculate_webshop_price('+Rounded_price_flag+');"></td>	<td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);" name="tax_percent" id="tax_percent"  onblur="calculate_webshop_price('+Rounded_price_flag+');"></td>	<td><input type="number" class="form-control input-sm" onkeypress="return isNumberKey(event);" readonly name="webshop_price" id="webshop_price" ></td><td><select name="sel_gifts" id="sel_gifts"><option value="">-- Select Gifts To--<option></select></td><td><input type="text" class="form-control input-sm" name="sub_issues" id="sub_issues" ></td></tr></tbody></table>';
		$('#single_info').html(single_product_tbl);
		$('#variant_info').html('');
		$('#variant_info_block').addClass('d-none');
	}else{
		$('#temp_add_var_single').removeClass('d-none');
		$('#product_type').val('configurable');

		$('#single_info').html('');
		$('#single_info_block').addClass('d-none');

		var pro_code=$('#product_code').val();
			var selected_variant_option=[];

			$('.variant-single:checked').each(function() {
				selected_variant_option.push(this.value);
			});

			if(selected_variant_option=='' || selected_variant_option=='[]'){
				swal("Error", "Please select at least one variant.", "error");
				return false;
			}
			else if(pro_code==''){
				swal("Error", "Please enter product code.", "error");
				return false;
			}
			else{


				LoadVariantCombination(pro_code,selected_variant_option);
			}
	}

	$.ajax({
		url: BASE_URL+"sellerproduct/addgifts",
		method: 'post',
		dataType: 'json',
		success: function(response){
			// Remove options
			$('#sel_gifts').find('option').not(':first').remove();

			// Add options
		  	$.each(response,function(index,data){
				$('#sel_gifts').append('<option value="'+data['id']+'">'+data['name']+'</option>');
		  	});
		}
	 });


	$('#FBCUserCommonModal').modal('hide');
}



function readMediaFiles(file,elem='') {

  var reader = new FileReader();
  var image  = new Image();

  reader.readAsDataURL(file);
  reader.onload = function(_file) {
	  _file.preventDefault();

	var uniqid=Date.now();
    image.src = _file.target.result; // url.createObjectURL(file);
    image.onload = function() {
      var w = this.width,
          h = this.height,
          t = file.type, // ext only: // file.type.split('/')[1],
          n = file.name,
          s = ~~(file.size/1024) +'KB';
      $('#uploadPreview').append('<span class="single-img radio" id="media-file-'+uniqid+'"><a href="javascript:void(0);" onclick="removeMediaFile('+uniqid+')" class="rm-media">X</a><img src="' + this.src + '"  class="thumb"><label><input type="radio" id="media-selection-'+uniqid+'" name="default_image" value="'+file.name+'">&nbsp;<span class="checkmark"></span></label></span>');
    };

	var default_image= $('input[name=default_image]:checked').val();

	var def_checked='';

	if(default_image=='' || default_image==undefined){
			//def_checked='"checked"';
			$('#media-selection-'+uniqid).attr('checked',true);
			$('#media-selection-'+uniqid).prop('checked',true);
	}


    image.onerror= function() {
		swal("Error", 'Invalid file type: '+ file.type, "error");
			return false;

     // alert('Invalid file type: '+ file.type);
    };
  };

}


function removeMediaFile(id){
	$('#media-file-'+id).remove();

	var current_page=$('#current_page').val();
	if(current_page=='edit'){

		var prev_list=$('#deleted_md').val();
		if(prev_list!=''){
			var new_list=','+id;
			new_list=prev_list+','+new_list;
			$('#deleted_md').val(new_list);
		}else{
			var new_list=id;

			$('#deleted_md').val(new_list);
		}

	}
}





function OpenCategoryCreatePopup()
{

	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"sellerproduct/openaddcategorypopup",
		data: {},
		async:false,
		complete: function () {
		},
		beforeSend: function(){
			// $('#ajax-spinner').show();
		},
		success: function(response) {
			$("#FBCUserCommonModal").modal();
			 $("#modal-content").html(response);
		}
	});

}


function SaveRootCategory(){
	var rc_description=$('#rc_description').val();
	var rc_name=$('#rc_name').val();


	var level_one_category = [];
	$('input[name^="level_one_category"]').each(function() {
		level_one_category.push(this.value);
	});


	if(rc_name==''){
		swal("Error", "Please enter category name", "error");

		return false;
	}
	/*
	else if(level_one_category=='' || level_one_category=='[]'){
		swal("Error", "Please enter at least one sub category name", "error");
		return false;
	}*/

	else{
			$('#rc-error').html('');
			$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/saverootcategory",
			data: {rc_name:rc_name,rc_description:rc_description,sub_cat:level_one_category},

			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {

				if(response!='error'){
					if(response=='error2'){
						swal("Error", 'Category already exist.', "success");
					}else{

						$('#FBCUserCommonModal').modal('hide');
						var current_page=$('#current_page').val();
						if(current_page=='add' || current_page=='edit'){
							RefreshCategoryTree();
						}else{

							RefreshMainCategory();
							swal("Success", 'Category added successfully.', "success");
							OpenEditCategory(response);

						}
					}

				}
			}
		});
	}
}

function RefreshMainCategory(category_id){

	if(category_id!=''){
		$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/refreshrootcategory",
				data: {},

				complete: function () {
				},
				beforeSend: function(){
					// $('#ajax-spinner').show();
				},
				success: function(response) {
					if(response!='error'){
						$('#category').html(response);
						$('#category').val(category_id);

					}else{
						$('#category').html('');
					}
				}
		});
	}else{
		return false;
	}
}


function OpenSubCategoryCreatePopup()
{

	$.ajax({
		type: "POST",
		dataType: "html",
		url: BASE_URL+"sellerproduct/openaddsubcategorypopup",
		data: {},
		async:false,
		complete: function () {
		},
		beforeSend: function(){
			// $('#ajax-spinner').show();
		},
		success: function(response) {
			$("#FBCUserCommonModal").modal();
			 $("#modal-content").html(response);
		}
	});

}

function OpenAttributeListPopup(category_id,flag='',type=''){
	if(category_id!=''){
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openattributelistpopup",
			data: {category_id:category_id,flag:flag,type:type},
			//async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#FBCUserSecondaryModal").modal();
				 $("#modal-content-second").html(response);
			}
		});
	}else{
		return false;
	}
}

function OpenEditCategory(category_id)
{
	if(category_id!=''){
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openeditcategorypopup",
			data: {category_id:category_id},
		//	async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#FBCUserCommonModal").modal();
				 $("#modal-content").html(response);
			}
		});
	}else{
		return false;
	}

}


function CreateSubCategoryRow(){

	var uniqid=Date.now();

	var sc_row='<div class="form-group row sub-cat"  id="scr_'+uniqid+'"><label for="" class="col-sm-3 col-form-label font-500">Category Name</label><div class="col-sm-4"><input type="text" class="form-control" value="" name="level_one_category[]" ><input type="hidden" class="form-control" value="" name="hidden_sub_cat[]" ></div><a href="javascript:void(0);" class="edit-purple link-purple" onclick="RemoveSubCatRow('+uniqid+');"> Remove </a></div>';

	$('#sub-cat-container').append(sc_row);

}


function RemoveSubCatRow(id){
	$('#scr_'+id).remove();
}

function EditSubCatRow(category_id){
	if(category_id!=''){
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/opensubcategoryeditpopup",
			data: {category_id:category_id},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#FBCUserCommonModal").modal();
				 $("#modal-content").html(response);
				 $("#FBCUserSecondaryModal").modal('hide');
				 $("#modal-content-second").html('');
			}
		});
	}else{
		return false;
	}
}


function OpenCreateAttributePopup(category_id,flag=''){

	//if(category_id!=''){
	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openaddattributepopup",
			data: {category_id:category_id,flag:flag},
			//async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#FBCUserSecondaryModal").modal();
				 $("#modal-content-second").html(response);
			}
		});
		/*

	}else{
			swal('Error','Please select category and sub category ','error');
		return false;
	}
	*/
}

function SaveAttributeForCategory(category_id,type){
	if(category_id!=''){
			var select_attr_main=[];

			$('.select_attr_main:checked').each(function() {
				select_attr_main.push(this.value);
			});

			if(select_attr_main=='' || select_attr_main=='[]'){
				swal("Error", "Please select at least one attribute.", "error");
				return false;
			}else{

				BackToEditSubCategory(category_id,select_attr_main,type);
				$("#FBCUserSecondaryModal").modal('hide');
				 $("#modal-content-second").html('');

			}
		}
		else{
		return false;
	}
}


function SaveVariantForCategory(category_id,type){
	if(category_id!=''){
			var select_attr_main=[];

			$('.select_attr_main:checked').each(function() {
				select_attr_main.push(this.value);
			});

			if(select_attr_main=='' || select_attr_main=='[]'){
				swal("Error", "Please select at least one attribute.", "error");
				return false;
			}else{

				BackToEditSubCategory(category_id,select_attr_main,type);
				$("#FBCUserSecondaryModal").modal('hide');
				 $("#modal-content-second").html('');

			}
		}
		else{
		return false;
	}
}


function BackToEditSubCategory(category_id,select_attr,type){
	if(category_id!=''){
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/refreshtempsubcatattr",
			data: {category_id:category_id,select_attr:select_attr,type:type},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				if(response!='error'){
					if(type=='attributes'){
						$('#sc_attr_list').html(response);
					}else if(type=='variants'){
						$('#sc_variant_list').html(response);
					}
					else{

					}
				}else{
					//$('#sc_attr_list').html('');
				}

			}
		});
	}else{
		return false;
	}
}

function SaveSubCategory(){
	var select_attr=[];
	var sub_cat_attr=[];
	var sub_cat_variant=[];



	var hidden_sc_id=$('#hidden_sc_id').val();
	var sub_cat_name=$('#sub_cat_name').val();
	var sub_child_ids=$('#sub_child_ids').val();


	$(".sub_cat_attr:checked").each(function(){
        sub_cat_attr.push($(this).val());
    });


	$(".sub_cat_variant:checked").each(function(){
        sub_cat_variant.push($(this).val());
    });

	if(sub_cat_name==''){
		swal("Error", "Please enter category name.", "error");
		return false;
	}
	/*
	else if(sub_child_ids==''){
		swal("Error", "Please select at least one catalogue.", "error");
		return false;
	}
	*/

	else{

		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/savesubcategorynew",
			data: {category_id:hidden_sc_id,sub_cat_name:sub_cat_name,sub_cat_attr:sub_cat_attr,sub_cat_variant:sub_cat_variant,sub_child_ids:sub_child_ids},
			//async:false,
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					swal("Success", response.message, "success");

					$('#FBCUserCommonModal').modal('hide');
					var current_page=$('#current_page').val();
					if(current_page=='add' || current_page=='edit'){
						RefreshCategoryTree();
					}else{
						if(obj.root_category_id!=''){
							RefreshMainCategory();

							$('#category').val(obj.root_category_id);
							GetSellerSubCategoryAjax(obj.root_category_id);
							$("#child_category").tagsinput('removeAll');
							OpenEditCategory(obj.root_category_id);

						}
					}
				} else {

					swal("Error", obj.message, "error");
					return false;
				}
			}
		});

	}
}

function SaveExistingCategory(){

	var rc_description=$('#rc_description').val();
	var rc_name=$('#rc_name').val();
	var hidden_rc_id=$('#hidden_rc_id').val();




	var level_one_category = [];
	$('input[name^="level_one_category"]').each(function() {
		level_one_category.push(this.value);
	});

	var hidden_sub_cat = [];
	$('input[name^="hidden_sub_cat"]').each(function() {
		hidden_sub_cat.push(this.value);
	});


	if(rc_name==''){
		swal("Error", "Please enter category name", "error");

		return false;
	}
	/*
	else if(level_one_category=='' || level_one_category=='[]'){
		swal("Error", "Please enter at least one sub category name", "error");
		return false;
	}
	*/
	else{
			$('#rc-error').html('');
			$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/updaterootcategory",
			data: {rc_name:rc_name,rc_description:rc_description,sub_cat:level_one_category,category_id:hidden_rc_id,hidden_sub_cat:hidden_sub_cat},

			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {

				if(response!='error'){


					$('#FBCUserCommonModal').modal('hide');
					var current_page=$('#current_page').val();
					if(current_page=='add' || current_page=='edit'){
						RefreshCategoryTree();
					}else{
						RefreshMainCategory();
						swal("Success", 'Category updated successfully.', "success");

						$('#category').val(hidden_rc_id);
						GetSellerSubCategoryAjax(hidden_rc_id);
					}

				} else {

					swal("Error",'Something went wrong.', "error");
					return false;
				}
			}
		});
	}


}

function CreateSingleAttributeHTML(attr_id){

	if(attr_id!=''){
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/loadsingleattribute",
			data: {attr_id:attr_id},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				if(response!='error'){
				//swal("Success", 'Attribute created successfully.', "success");
				 $("#attr_tbody").append(response);

				}else{
					swal("Error", 'Something went wrong.', "error");
					return false;
				}
			}
		});
	}else{
		return false;
	}
}

function LoadExtraAttribute(category_id){
	var select_attr_main=[];

			$('.select_attr_main:checked').each(function() {
				select_attr_main.push(this.value);
			});

			//alert(select_attr_main);

			if(select_attr_main=='' || select_attr_main=='[]'){
				swal("Error", "Please select at least one attribute.", "error");
				return false;
			}else{

				$('.select_attr_main:checked').each(function() {
					var atid=this.value;
					if($('#attr_row_'+atid).length){
					//	$('#attr_row_'+atid).remove();
					//	CreateSingleAttributeHTML(this.value);
					}else{
						CreateSingleAttributeHTML(this.value);
					}
				});


				$('#added_attr').val(select_attr_main);

				$("#FBCUserSecondaryModal").modal('hide');
				 $("#modal-content-second").html('');

			}
}




function EditAttribute(attr_id,category_id){
	if(attr_id!=''){
		var current_page=$('#current_page').val();
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openeditattributepopup",
			data: {attr_id:attr_id,category_id:category_id},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				if(response!='error'){
					$("#FBCUserSecondaryModal").modal('show');
					$("#modal-content-second").html(response);

				}else{
					swal("Error", 'Something went wrong.', "error");
					return false;
				}
			}
		});
	}else{
		return false;
	}
}



function EditVariant(attr_id,category_id){
	if(attr_id!=''){
		var current_page=$('#current_page').val();
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openeditvariantpopup",
			data: {attr_id:attr_id,category_id:category_id,flag:current_page},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				if(response!='error'){
				$("#FBCUserSecondaryModal").modal('show');
				 $("#modal-content-second").html(response);

				}else{
					swal("Error", 'Something went wrong.', "error");
					return false;
				}
			}
		});
	}else{
		return false;
	}
}


function OpenCreateVariantPopup(category_id){

	if(category_id!=''){
	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openaddvariantpopup",
			data: {category_id:category_id},
			//async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#FBCUserSecondaryModal").modal();
				 $("#modal-content-second").html(response);
			}
		});
		}else{
		return false;
	}
}

function LoadVariantCombination(pro_code,variant_options)
{
	if(pro_code!='' && variant_options!='[]'){
	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/loadvariants",
			data: {pro_code:pro_code,variant_options:variant_options},
			//async:false,
			complete: function () {
			},
			beforeSend: function(){
				$('#ajax-spinner').show();
			},
			success: function(response) {
				$('#ajax-spinner').hide();
				if(response!='error'){
					$('#variant_info_block').removeClass('d-none');
					 $("#variant_info").html(response);
				}else{
					$("#variant_info").html('');
					$('#variant_info_block').addClass('d-none');

				}
			}
		});
		}else{
		return false;
	}
}



function AddAdditionalVariant(flag){
	var sub_category=$('#SubCategoryOptions').val();

	if(sub_category!='' && sub_category!='add_new' && sub_category>0){
		console.log(22);
		SelectAttributeAndVariants(sub_category,flag);
	}
	else{
		console.log(111111111);
		SelectAttributeAndVariants(0,flag);
	}
}

function Addvariantsinglerow(){
	var added_variant=$('#added_variant').val();
	var pro_code=$('#product_code').val();
	var variant_products_count=$('#variant_products_count').val();
	var get_own_products_count=$('#get_own_products_count').val();
	if(added_variant!='' && pro_code!=''){

		var current_page=$('#current_page').val();
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/addsinglevariant",
			data: {pro_code:pro_code,variant_options:added_variant,flag:current_page,variant_products_count:variant_products_count,get_own_products_count:get_own_products_count},
			//async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				if(response!='error'){

					 $("#variant_tbody").append(response);
				}else{

					swal("Error", 'Something went wrong.', "error");
					return false;
				}
			}
		});


	}else{
		return false;
	}


}

function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}



function resetAttribute(value,type='') {

if(type=='attributes'){
	  var list=$('#added_attr').val();

 }else if(type=='variants'){
	  var list=$('#added_variant').val();

 }else {
	 var list='';
 }

 var new_list='';

  list = list.split(',');
  list.splice(list.indexOf(value), 1);
  new_list= list.join(',');


 if(type=='attributes'){
	 $('#added_attr').val(new_list);
	 return true;
 }else if(type=='variants'){
	 $('#added_variant').val(new_list);
	 return true;
 }else {
	 return list;
 }
}


function AddVariantToForm(){

}



function OpenBulkSelectCategory(type=""){
	if(type!='')
	{
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openbulkselectcategory",
			data: {type:type},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
	}
	else{

		return false;
	}
}

function OpenBulkSelectCategoryUpdate(type=""){
	if(type!='')
	{
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/OpenBulkSelectCategoryUpdate",
			data: {type:type},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
	}
	else{

		return false;
	}
}

function OpenOnlineInventoryPopup(){
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/OpenOnlineInventoryPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function OpenBulkSelectCategoryUpdate_1(type=""){
	if(type!='')
	{
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/OpenBulkSelectCategoryUpdate_1",
			data: {type:type},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
	}
	else{

		return false;
	}
}

function skudbcompare(element,conf_simple_id=''){
	var value=$(element).val();
	if(value!=''){
		$('.sku-label').remove();

		var current_page=$('#current_page').val();

		if(current_page=='edit'){
			if($('#product_type').val()=='simple'){
				var pid=$('#pid').val();
			}else{
				var pid=conf_simple_id;
			}
		}else{
			var pid='';
		}

		var isSuccess='';
			var uniqid =Date.now();
		   $.ajax({
				url: BASE_URL+"sellerproduct/checkskuexist",
				type: "POST",
				data: {
				  flag:current_page,
				  product_type:$('#product_type').val(),
				  sku: value,
				  pid:pid
				},
				async: true,
				success: function(msg) {

					isSuccess = msg === "true" ? true : false;

					if(isSuccess==false){

						$(element).addClass('error text-danger wrong-sku');
						 $(element).parent('td').append('<label  id="id_ct'+uniqid+'-error" class="error sku-label"> Sku <b>'+value+'</b> is already in used.</label>');
					}else{
						$('.sku-label').remove();
						$(element).removeClass('error text-danger wrong-sku');
					}

				}
			});


	}else{
		return false;
	}
}



function BulkCategoryChange(category_id,flag){

	if(category_id!=''){

		//alert(category_id);

		if(category_id=='new'){
			var root_category_id=$('#category').val();
			OpenEditCategory(root_category_id);
		}else{

			$('#bulk_cat_save').removeAttr('disabled');

		}
	}else{
		$('#bulk_cat_save').removeAttr('disabled');
		$('#SubCategoryOptions').val('');
		swal("Error", "Please select proper sub category.", "error");
		return false;
	}
}

function SaveDownloadCSVCat(){
	var root_category_id=$('#category').val();
	var sub_category=$('#SubCategoryOptions').val();

	if(sub_category==''){
		sub_category='';
	}

	if(root_category_id!='' && sub_category!=''){


		OpenSubCatCSVAttributesSelect(root_category_id,sub_category);
	}else{
		swal('Error','Please select proper category & sub category','error');
		return false;
	}

}

function OpenSubCatCSVAttributesSelect(root_category_id,sub_category){

		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/bulkcategoryattrselect",
			data: {root_category_id:root_category_id,sub_category:sub_category},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{
					swal('Error','Something went wrong','error');
					return false;
				}

			}
		});

}

function DownloadProductCSV()
{
	var select_attr=[];
	var sub_cat_attr=[];
	var sub_cat_variant=[];

	var root_category_id=$('#root_category_id').val();
	var sub_category=$('#sub_category').val();

	$(".sub_cat_attr:checked").each(function(){
        sub_cat_attr.push($(this).val());
    });

	$(".sub_cat_variant:checked").each(function(){
        sub_cat_variant.push($(this).val());
    });

	if(root_category_id=='' || sub_category==''){
		swal("Error", "Please enter category name.", "error");
		return false;
	}else{

		var search_param='root_category_id='+root_category_id+'&sub_category='+sub_category;

		 if(sub_cat_attr.length>0)
		{
			search_param+='&attributes='+sub_cat_attr;
		}
		if(sub_cat_variant.length>0)
		{
			search_param+='&variants='+sub_cat_variant;
		}

		window.location.href=BASE_URL+'sellerproduct/downloadproductcsv?'+search_param;
		$("#FBCUserCommonModal").modal('hide');

	}
}

function DownloadProductCSVUpdate()
{
		window.location.href=BASE_URL+'sellerproduct/downloadproductcsvUpdate';
		$("#FBCUserCommonModal").modal('hide');
}

function DownloadOnlineInventoryCSVUpdate()
{
		window.location.href=BASE_URL+'sellerproduct/DownloadOnlineInventoryCSVUpdate';
		$("#FBCUserCommonModal").modal('hide');
}

function DownloadProductCSVUpdate_1()
{
	window.location.href=BASE_URL+'sellerproduct/downloadproductcsvUpdate_1';
	$("#FBCUserCommonModal").modal('hide');

}

function DownloadProductInventoryTypeCSV()
{
	window.location.href=BASE_URL+'sellerproduct/DownloadProductInventoryTypeCSV';
	$("#FBCUserCommonModal").modal('hide');
}

function DownloadAllProductCSV()
{
		window.location.href=BASE_URL+'sellerproduct/DownloadAllProductCSV';
		$("#FBCUserCommonModal").modal('hide');
}

function DownloadAllProductAttributesCSV()
{
		window.location.href=BASE_URL+'sellerproduct/DownloadAllProductAttributesCSV';
		$("#FBCUserCommonModal").modal('hide');
}

function DownloadProductAttributestemplate_to_import()
{
		window.location.href=BASE_URL+'sellerproduct/ProductAttributesCSV_import_template_download';
		$("#FBCUserCommonModal").modal('hide');
}

function DownloadWeightLocationCSV()
{
		window.location.href=BASE_URL+'sellerproduct/DownloadWeightLocationCSV';
		$("#FBCUserCommonModal").modal('hide');
}


function OpenBulkUploadPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openbulkuploadpopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function OpenBulkUploadPopupUpdate(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openbulkuploadpopupupdate",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function OpenBulkUploadPopupUpdate_1(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openbulkuploadpopupupdate_1",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function ImportProducts(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/importproducts",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {
				//console.log(response);return false;

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					$('#FBCUserCommonModal').modal('hide');
					swal("Success", obj.message, "success");

				} else {

					swal("Error", obj.message, "error");
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function UpdateProducts(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/updateproducts",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {
				//console.log(response);return false;

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					$('#FBCUserCommonModal').modal('hide');
					swal("Success", obj.message, "success");

				} else {

					swal("Error", obj.message, "error");
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}


function UpdateProducts_1(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/updateproducts_1",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {
				//console.log(response);return false;

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					$('#FBCUserCommonModal').modal('hide');
					swal("Success", obj.message, "success");

				} else {

					swal("Error", obj.message, "error");
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function UpdateProductsInv_type(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/UpdateProductsInv_type",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {
				//console.log(response);return false;

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					$('#FBCUserCommonModal').modal('hide');
					swal("Success", obj.message, "success");

				} else {

					swal("Error", obj.message, "error");
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function UpdateProductsAttributes(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/UpdateProductsAttributes",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {
				//console.log(response);return false;

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					$('#FBCUserCommonModal').modal('hide');
					swal("Success", obj.message, "success");

				} else {

					swal("Error", obj.message, "error");
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function UpdateProductsWeightLocation(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/UpdateProductsWeightLocation",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {
				//console.log(response);return false;

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					$('#FBCUserCommonModal').modal('hide');
					swal("Success", obj.message, "success");

				} else {

					swal("Error", obj.message, "error");
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}



function CheckCSVData(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/checkcsvdata",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					swal("Success", obj.message, "success");
					$('#bulk_upload').removeClass('d-none');
					$('#check_data').addClass('d-none');


				} else {

					swal("Error", obj.message, "error");
					$('#bulk_upload').addClass('d-none');
					$('#check_data').removeClass('d-none');
					$('#csv_error').html(response.message);
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function CheckupdateCSVData(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/checkupdatecsvdata",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					swal("Success", obj.message, "success");
					$('#bulk_upload').removeClass('d-none');
					$('#check_data').addClass('d-none');


				} else {

					swal("Error", obj.message, "error");
					$('#bulk_upload').addClass('d-none');
					$('#check_data').removeClass('d-none');
					$('#csv_error').html(response.message);
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function CheckupdateCSVData_1(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/checkupdatecsvdata_1",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					swal("Success", obj.message, "success");
					$('#bulk_upload').removeClass('d-none');
					$('#check_data').addClass('d-none');


				} else {

					swal("Error", obj.message, "error");
					$('#bulk_upload').addClass('d-none');
					$('#check_data').removeClass('d-none');
					$('#csv_error').html(response.message);
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

$(document).on("change", "#upload_csv_file", function(){
     //Do something
	  $('#check_data').removeClass('d-none');
	   $('#bulk_upload').addClass('d-none');
	 $('#check_data').removeAttr('disabled');
});

function CheckupdateCSVData_Inv_type(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/CheckupdateCSVData_Inv_type",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					swal("Success", obj.message, "success");
					$('#bulk_upload').removeClass('d-none');
					$('#check_data').addClass('d-none');


				} else {

					swal("Error", obj.message, "error");
					$('#bulk_upload').addClass('d-none');
					$('#check_data').removeClass('d-none');
					$('#csv_error').html(response.message);
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function CheckupdateCSVAttributesData(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/CheckupdateCSVAttributesData",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					swal("Success", obj.message, "success");
					$('#bulk_upload').removeClass('d-none');
					$('#check_data').addClass('d-none');


				} else {

					swal("Error", obj.message, "error");
					$('#bulk_upload').addClass('d-none');
					$('#check_data').removeClass('d-none');
					$('#csv_error').html(response.message);
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}

function CheckupdateCSVWeightLocationData(){
	var upload_csv_file=$('#upload_csv_file').val();
	if(upload_csv_file==''){
		swal('Error','Please upload file','error');
		return false;
	}else{


		 var fd = new FormData();
		fd.append('upload_csv_file', $('#upload_csv_file')[0].files[0]); // since this is your file input

		$.ajax({
			url: BASE_URL+"sellerproduct/CheckupdateCSVWeightLocationData",
			type: "post",
			dataType: 'html',
			 contentType:false,
			  cache:false,
			  processData:false,
			data: fd,
			success: function(response) {

				var obj = JSON.parse(response);
				if(obj.status == 200) {

					swal("Success", obj.message, "success");
					$('#bulk_upload').removeClass('d-none');
					$('#check_data').addClass('d-none');


				} else {

					swal("Error", obj.message, "error");
					$('#bulk_upload').addClass('d-none');
					$('#check_data').removeClass('d-none');
					$('#csv_error').html(response.message);
					return false;

				}

			},
			error: function() {
				swal('Error','Something went wrong','error');
				return false;
			}
		});


	}
}


function barcodedbcompare(element,conf_simple_id=''){
	var value=$(element).val();
	if(value!=''){
		$('.barcode-label').remove();

		var current_page=$('#current_page').val();

		if(current_page=='edit'){
			if($('#product_type').val()=='simple'){
				var pid=$('#pid').val();
			}else{
				var pid=conf_simple_id;
			}
		}else{
			var pid='';
		}

		var isSuccess='';
			var uniqid =Date.now();
		   $.ajax({
				url: BASE_URL+"sellerproduct/checkbarcodeexist",
				type: "POST",
				data: {
				  flag:current_page,
				  product_type:$('#product_type').val(),
				  barcode: value,
				  pid:pid
				},
				async: true,
				success: function(msg) {

					isSuccess = msg === "true" ? true : false;

					if(isSuccess==false){

						$(element).addClass('error text-danger wrong-sku');
						 $(element).parent('td').append('<label  id="id_ct'+uniqid+'-error" class="error barcode-label"> Barcode <b>'+value+'</b> is already in used.</label>');
					}else{
						$('.sku-label').remove();
						$(element).removeClass('error text-danger wrong-sku');
					}

				}
			});


	}else{
		return false;
	}
}



function validatestockqty(element,pid){
	var stock_qty=$(element).val();
	if(stock_qty!=''){
		$('.stockqty-label').remove();
		var ordered_qty=$(element).data('ordered_qty');
		var current_page=$('#current_page').val();

		if(current_page=='edit'){

		}else{
			var pid='';
		}

		var isSuccess='';
			var uniqid =Date.now();
		   $.ajax({
				url: BASE_URL+"sellerproduct/checkstockqty",
				type: "POST",
				data: {
				  flag:current_page,
				  product_type:$('#product_type').val(),
				  stock_qty: stock_qty,
				  pid:pid
				},
				async: true,
				success: function(msg) {

					isSuccess = msg === "true" ? true : false;

					if(isSuccess==false){

						$(element).addClass('error text-danger wrong-sku');
						 $(element).parent('td').append('<label  id="id_ct'+uniqid+'-error" class="error stockqty-label"> Please enter number greater than '+ordered_qty+'</label>');
					}else{
						$('.stockqty-label').remove();
						$(element).removeClass('error text-danger wrong-sku');
					}

				}
			});


	}else{
		return false;
	}
}


function OpenVariantsList(flag,pid=''){

	var category_id='';
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/getvariantlist",
			data: {category_id:category_id,flag:flag,pid:pid},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {

				if(response!='error'){
				// $('#ajax-spinner').hide();
					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);


				}else{
					$('#variant_list').html('');

				}

				$("#FBCUserSecondaryModal").modal('hide');
				 $("#modal-content-second").html('');
			}
		});

}


function OpenAttributeList(flag='',type=''){

	var category_id='';
		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/getattributelist",
			data: {category_id:category_id,flag:flag,type:type},
			//async:false,
			complete: function () {
			},
			beforeSend: function(){
				// $('#ajax-spinner').show();
			},
			success: function(response) {
				$("#FBCUserSecondaryModal").modal();
				 $("#modal-content-second").html(response);
			}
		});

}


function RefreshCategoryTree(){

	var flag=$('#current_page').val();

	if(flag=='edit'){
		var pid=$('#pid').val();
	}else{
		var pid='';
	}

	if(flag!=''){
		$.ajax({
				type: "POST",
				dataType: "html",
				url: BASE_URL+"sellerproduct/refreshcategorytree",
				data: {flag:flag,pid:pid},
				complete: function () {
				},
				beforeSend: function(){
					// $('#ajax-spinner').show();
				},
				success: function(response) {
					if(response!='error'){
						$('#category-tree').html(response);

					}else{
						return false;
					}
				}
		});
	}else{
		return false;
	}
}

function OpenDownloadAll(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/openDownloadAll",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function OpenOutofStockModal(){

		$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/OpenOutofStockModal",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});

}

function BulkUploadInventoryTypesPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkUploadInventoryTypesPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function BulkInventoryTypesDownloadPopup()
{
	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkInventoryTypesDownloadPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function BulkUploadAttributesPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkUploadAttributesPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function BulkUploadweightLocationPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkUploadweightLocationPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function outofstockcheck(shop_id){
	if( shop_id != '')
	{
		$.ajax({
			type: "POST",
			dataType: "JSON",
			url: BASE_URL+"CronOutOfStockController/outofstockcheck/"+shop_id,
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				// console.log(response);
				 $('#ajax-spinner').hide();
				 // console.log(response);
					// $("#FBCUserCommonModal").modal();
					$("#FBCUserCommonModal").modal('hide');
					 if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: true, icon: 'success' },
                        function() {window.location = response.redirect; })
                    } else {
                        swal({ title: "",text: response.msg, button: true, icon: 'error' })
                        return false;
                    }

					//$("#modal-content").html(response);


			}
		});
	}else{
		return false;
	}

}

function BulkUploadAttributesPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkUploadAttributesPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function BulkUploadweightLocationPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkUploadweightLocationPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function BulkAttributesDownloadPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkAttributesDownloadPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

function BulkweightLoccationDownloadPopup(){

	$.ajax({
			type: "POST",
			dataType: "html",
			url: BASE_URL+"sellerproduct/BulkweightLoccationDownloadPopup",
			data: {},
			async:false,
			complete: function () {
			},
			beforeSend: function(){
				 $('#ajax-spinner').show();
			},
			success: function(response) {
				 $('#ajax-spinner').hide();
				if(response!='error'){

					$("#FBCUserCommonModal").modal();
					$("#modal-content").html(response);
				}else{

				}

			}
		});
}

$(document).ready(function(){
	$.ajax({
        url: BASE_URL+"sellerproduct/addgifts",
        method: 'post',
        dataType: 'json',
        success: function(response){
          // Add options
          $.each(response,function(index,data){
             $('#sel_gifts').append('<option value="'+data['id']+'">'+data['name']+'</option>');
          });
        }
     });
   });
