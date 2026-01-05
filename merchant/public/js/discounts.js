$(document).ready(function() {
totalListCount = $('#totalListCount').val();
var i = 1;
for(i;i<=totalListCount;i++){
    $('#productList_'+i).DataTable( {
        language:{
            paginate:{
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>'
            },
            search: "",
            searchPlaceholder: "Search"
        },
        //scrollY: '300px',
        paging: false,
        searching: true,
        info: false,
        pageLength: 6,
        lengthChange: false,
    } );
}

	// $('#discountTableList').DataTable({
	// 	aaSorting: [],
	// 	order : [],
	// 	language:{
	// 		infoFiltered: "",
    //   		search: '',
    //   		searchPlaceholder: "Search",
	// 		paginate:{
	// 			next: '<i class="fas fa-angle-right"></i>',
    //     		previous: '<i class="fas fa-angle-left"></i>'
	// 		}
	// 	},
	// });

	$("#discountTableList").dataTable().fnDestroy();
    //datatables
	var discount_type = $('#discount_type').val();


    table = $('#discountTableList').DataTable({

        "scrollCollapse": true,
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        //"scrollX":true,
        "bLengthChange": true, //thought this line could hide the LengthMenu
        "bInfo": false,
        "stateSave": true,
        "order": [], //Initial no order.
        "iDisplayLength": 100,
        "pageLength": 100,
        "searchDelay": 100,
        "lengthMenu": [
            [25, 100, 200, 300, 500, -1],
            [25, 100, 200, 300, 500, "All"]
        ],
        // Load data for the table's content from an Ajax source

        "ajax": {
            "url": BASE_URL + "WebshopController/couponCodeAjaxLoading",
            "type": "POST",
            "data": function(B) {  B.discount_type = discount_type; }
        },
        "search": {
            "caseInsensitive": false
        },
        "fnDrawCallback": function(oSettings) {

            if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {

                $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                // $(oSettings.nTableWrapper).find('.dataTables_length').hide();

            } else {
                $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
                // $(oSettings.nTableWrapper).find('.dataTables_length').show();
            }

        },

        "language": {
            "infoFiltered": "",
            "search": '',
            "searchPlaceholder": "Search",
            "paginate": {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }

        },
        'columnDefs': [{
            "targets": [],
            "orderable": false
        }],
        "initComplete": function() {

        }

    });






	$('#discountTableCatList').DataTable({
		aaSorting: [],
		language:{
			infoFiltered: "",
      		search: '',
      		searchPlaceholder: "Search",
			paginate:{
				next: '<i class="fas fa-angle-right"></i>',
        		previous: '<i class="fas fa-angle-left"></i>'
			}
		},
        pageLength: -1,
        bLengthChange: false,
        bPaginate:false,
	});

	$("#apply_percent").on('change',function(){
		if($(this).val() == 'by_fixed'){
			$(".disc_lbl").text('Amount');
            $("#discount_amnt").val('');
            $(".cp_type").text('Voucher');
		}else{
			$(".disc_lbl").text('Discount %');
            $("#discount_amnt").val('');
            $(".cp_type").text('Discount');
		}
	});

    $("#cp_conditions").on('change',function(){
        if($(this).val() == 'discount_on_mincartval'){
            if($("#apply_percent").val() == 'by_fixed'){
                $(".cp_type").text('Voucher');
            }
            $(".condition-left-sec-min-cart-val").show();
            $(".condition-left-sec-xy").hide();
            $(".condition-left-sec-free-sample").hide();
        }else if($(this).val() == 'buyx_getyfree'){
            $(".cp_type").text('Discount');
            $(".condition-left-sec-min-cart-val").hide();
            $(".condition-left-sec-xy").show();
            $(".condition-left-sec-free-sample").hide();
        }else{
            $(".cp_type").text('Discount');
            $(".condition-left-sec-min-cart-val").hide();
            $(".condition-left-sec-xy").hide();
            $(".condition-left-sec-free-sample").show();
        }
    });

    $('input[name=cpradio]').change(function(){
        if($(this).is(':checked'))
        {
            if($(this).val() == 1){
                $('#coupon_code').prop('disabled', true);
                $('#prefix').prop('disabled', false);
            }else{
                $('#coupon_code').prop('disabled', false);
                $('#prefix').prop('disabled', true);
            }
        }
    })

    $('.readonly:radio').click(function(){
        return false;
    });

    var en_dt = $('#start_date, #end_date').attr('data-datepicker');

    if(en_dt == 'readonly'){
        $('#start_date, #end_date').attr('readonly',true).datepicker("destroy");
    }else{
       $("#start_date, #end_date").datepicker({
            autoclose: true,
            todayHighlight: true,
            startDate: new Date(),
            format:'dd-mm-yyyy',
        });
    }

	$(".add-more").click(function(e){
        e.preventDefault();
        var html = $(".after-add-more").first().clone();
        $(html).find(".change").after("<a class='btn btn-danger remove'>Remove</a>");
        $(".after-add-more").last().after(html);
    });

    $("body").on("click",".remove",function(){
        $(this).parents(".after-add-more").remove();
    });

	$("#discount-frm-add").validate({
        ignore: ':hidden',
       	rules: {
        	discount_name: {
                required: true,
            },
            start_date: {
                required: true,
            },
            end_date: {
                required: true,
            },
            coupon_code: {
                required: true,
                remote: {
                    url: BASE_URL+"WebshopController/checkCouponCode",
                    type: "POST",
					data: {
					  flag:$('#current_page').val(),
                      cid:$('#c_id').val(),
					  coupon_code: function() {
						return $( "#coupon_code" ).val();
					  }
					}
                 }
            },
            discount_amnt: {
                required: true,
            },
            "checked_cat[]": {
				minlength: 1,
                required: true,
            },
        },
		 messages: {
			coupon_code: {
				remote: "Coupon code already in use."
			}
		},
		beforeSend: function(){
			$('#ajax-spinner').show();
		},
        submitHandler: function(form) {
			var formData = new FormData($('#discount-frm-add')[0]);

            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
				processData: false,
				contentType: false,
				cache: false,
                success: function(response) {
					console.log(response);
                    $('#ajax-spinner').hide();
					if (response.flag == 1) {
                        if(response.discountType != 'product'){
                            swal({ title: "",text: response.msg, button: false, icon: 'success' })
                        }
                        setTimeout(function() {
								window.location.href=response.redirect;
                        }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
	                    return false;
                    }
                }
            });
        }
    });

$("#productListForm").validate({
    ignore: ':hidden',
    //ignore: ".ignore",
    beforeSend: function(){
        $('#ajax-spinner').show();
    },
    submitHandler: function(form) {
        var formData = new FormData($('#productListForm')[0]);
        $.ajax({
            url: form.action,
            type: 'ajax',
            method: form.method,
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                console.log(response);
                $('#ajax-spinner').hide();
                if(response.flag == 1) {
                    swal({ title: "",text: response.msg, button: false, icon: 'success' })
                    setTimeout(function() {
                        window.location.href=response.redirect;
                    }, 1000);
                }else{
                    swal({ title: "",text: response.msg, button: false, icon: 'error' })
                    return false;
                }
            }
        });
    }
});

$("#discountDeleteForm").validate({
    ignore: ':hidden',
    //ignore: ".ignore",
    submitHandler: function(form) {
        var formData = new FormData($('#discountDeleteForm')[0]);
        $.ajax({
            url: form.action,
            type: 'ajax',
            method: form.method,
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.flag == 1) {
                    swal({ title: "",text: response.msg, button: false, icon: 'success' })
                    setTimeout(function() {window.location.href = response.redirect}, 1000);
                } else {
                    swal({ title: "",text: response.msg, button: false, icon: 'error' })
                    return false;
                }
            }
        });
    }
});

$("#coupon-code-frm-add").validate({
    ignore: ':hidden',
    rules: {
        discount_name: {
            required: true,
        },
        start_date: {
            required: true,
        },
        end_date: {
            required: true,
        },
        coupon_code: {
            required: true,
            remote: {
                url: BASE_URL+"WebshopController/checkCouponCode",
                type: "POST",
                data: {
                  flag:$('#current_page').val(),
                  cid:$('#c_id').val(),
                  coupon_code: function() {
                    return $( "#coupon_code" ).val();
                  }
                }
             }
        },

    },
     messages: {
        coupon_code: {
            remote: "Coupon code already in use."
        }
    },
    beforeSend: function(){
        $('#ajax-spinner').show();
    },
    submitHandler: function(form) {
         for (instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
        var formData = new FormData($('#coupon-code-frm-add')[0]);

        $.ajax({
            url: form.action,
            type: 'ajax',
            method: form.method,
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function() {
                      $("#save_coupon").prop('disabled', true); // disable button
                    },
            success: function(response) {
                console.log(response);
                  $("#save_coupon").prop('disabled', false); // enable button
                $('#ajax-spinner').hide();
                if (response.flag == 1) {
                    swal({ title: "",text: response.msg, button: false, icon: 'success' })
                    setTimeout(function() {
                            window.location.href=response.redirect;
                    }, 1000);
                } else {
                    swal({ title: "",text: response.msg, button: false, icon: 'error' })
                    return false;
                }
            }
        });
    }
});

	// $.validator.addMethod(
	// 		"regex_pcname",
	// 		function(value, element, regexp) {
	// 			var re = new RegExp(regexp);
	// 			return this.optional(element) || re.test(value);
	// 		},
	// 		"Only allowed numbers(0-9), letters(a-z A-Z), underscore(_) and hyphen(-)."
	// );

	// $("#coupon_code").rules("add", { regex_pcname: "^[a-zA-Z0-9-_]+$" });


});

function openProductListPopup(prod_type)
{
    if(prod_type!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openProductListPopup",
            data: {product_type:prod_type},
            //async:false,
            complete: function () {
            },
            beforeSend: function(){
              $('#ajax-spinner').show();
            },
            success: function(response) {
                console.log(response);
                $('#ajax-spinner').hide();
                $("#FBCUserCommonModal").modal();
                $("#modal-content").html(response);
            }
        });
    }else{
        return false;
    }
}

function selectedProductList(prod_type)
{
    if(prod_type!=''){
        var chked_val = $('input[name=optradio]:checked').val();

        if(prod_type =='buy-x'){
            $(".buy_x").val(chked_val);
        }else if(prod_type =='get-y'){
            $(".get_y").val(chked_val);
        }else{
            return false;
        }
    }else{
        return false;
    }
}

function getProductCheckUncheck(product_id,category_id)
{
    if(product_id != '')
    {
        $('#checkedProduct_'+category_id+'_'+product_id).change(function(){
            console.log(category_id);
            if($(this).is(':checked'))
            {
                $(this).closest("tr").addClass('current-row');

                $( ".main-checkbox" ).each(function( i ) {
                    if(i==0){

                    }else{
                        $('.pid-'+category_id+'_'+product_id).prop('disabled', false);
                    }
                  });

                $('.trp-row').each(function( i ) {
                    if($(this).hasClass('current-row')){

                    }else{
                        $(this).find('.chk-line-'+product_id).prop('disabled',true);
                    }
                });
            }else{
                $(this).closest("tr").removeClass('current-row');
                $('.chk-line-'+product_id).prop('disabled',false);
            }
        })
    }
}

function DiscountCheckRelatedCat(elem,category_id,parent_cat_id,level){
    if($(elem).is(':checked')){
        if(level==0){
            if($('.b2b-pc-'+category_id).length>0){
                $('.b2b-pc-'+category_id).prop('checked',true);
                $('.b2b-pc-'+category_id).prop('disabled',true);

            }
        }else {

            if($('.b2b-c-'+parent_cat_id).length>0){
                $('.b2b-c-'+parent_cat_id).prop('checked',true);
                $('.b2b-c-'+parent_cat_id).prop('disabled',true);

            }
        }
    }else{

        if(level==0){
            if($('.b2b-pc-'+category_id).length>0){
                $('.b2b-pc-'+category_id).prop('checked',false);
                $('.b2b-pc-'+category_id).prop('disabled',false);

            }
        }else {

            if($('.b2b-c-'+parent_cat_id).length>0){
                if($('.b2b-pc-'+parent_cat_id).is(':checked')) {
                    $('.b2b-c-'+parent_cat_id).prop('checked',true);
                    $('.b2b-c-'+parent_cat_id).prop('disabled',true);
                }else{
                    $('.b2b-c-'+parent_cat_id).prop('checked',false);
                    $('.b2b-c-'+parent_cat_id).prop('disabled',false);
                }


            }
        }

    }
}
