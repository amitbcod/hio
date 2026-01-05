$(document).ready(function() {
	$("input[type=file]").change(function() {
        $(this).siblings("input[type='hidden']").val($(this).val());
	});

    $(function() {
	  	$("#enableWebshop").on("click",function() {
	    	$(".webshop-details-sec").toggle(this.checked);
	  	});
	});

     $(function() {
        $("#test_mode").on("change",function() {

                     $(".ip_addresses_sec").toggle(this.checked);
                   //  var ips = $('#ip_addresses');
                   // // coup.toggle();
                   //  if (ips.prop('required')) {
                   //      ips.prop('required', false);
                   //  } else {
                   //      ips.prop('required', true);
                   //  }
                if($("#test_check").val()!=0 || $("#live_check").val()!=0 )
                 {

                    var checkBox = $("#websiteLive");
                    (checkBox.prop("checked")==true)?checkBox.prop("checked", false):checkBox.prop("checked", true);
                }
                 });
    });

      $(function() {
        $("#websiteLive").on("change",function() {

                 if($("#test_check").val()!=0 || $("#live_check").val()!=0 )
                 {

                     $(".ip_addresses_sec").show(this.checked);

                   var checkBox = $("#test_mode");
                
                    (checkBox.prop("checked")==true)?checkBox.prop("checked", false):checkBox.prop("checked", true);
                 }
                 });
    });

     $('#bannerForm').find('textarea').each(function(index) {
        var id=$(this).attr('id');

           var editorInstance = CKEDITOR.replace(id,
                    {
                        on:
                       {
                           'instanceReady': function(evt) {
                               evt.editor.document.on('keyup', function() {
                                   document.getElementById(id).value = evt.editor.getData();

                               });

                              evt.editor.document.on('paste', function() {
                                  document.getElementById(id).value = evt.editor.getData();
                               });
                           }
                       }
                    });
            });


	$("#submitShopAccess").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            ownerName: { required: true, },
            weebshopName: { required: true, },
        },
        submitHandler: function(form) {
			var formData = new FormData($('#submitShopAccess')[0]);
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
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
	                    return false;
                    }
                }
            });
        }
    });

    $('.btn-slider').on('click', function() {
        var sliderId=  this.id;
        console.log(sliderId);
        $('.'+sliderId).show();
        $('.ct-sliderPop-container').addClass('open');
        $('.'+sliderId).addClass('flexslider');
        $('.'+sliderId+' .ct-sliderPop-container').addClass('slides');

        $('.'+sliderId).flexslider({
            selector: '.ct-sliderPop-container > .ct-sliderPop',
            slideshow: false,
            controlNav: false,
            controlsContainer: '.ct-sliderPop-container'
        });
    });

    $('.ct-sliderPop-close').on('click', function() {
        $('.sliderPop').hide();
        $('.ct-sliderPop-container').removeClass('open');
        $('.sliderPop').removeClass('flexslider');
        $('.sliderPop .ct-sliderPop-container').removeClass('slides');
    });

    $(document).on( "click", '.themeClass',function(e) {
        var id = $(this).attr("data-id");
        $("#themeID").val(id);
    });

    $("#themeForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#themeForm')[0]);
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
                        setTimeout(function() {window.location.reload();}, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

    $("#cmspageForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            pageTitle: { required: true, },
            pageIdentifier: { required: true, },
            pageContent: { required: true, },
        },
        submitHandler: function(form) {
            var formData = new FormData($('#cmspageForm')[0]);
            formData.append('pageContent', CKEDITOR.instances["pageContent"].getData());
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response);
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' })
                        setTimeout(function() { window.location.href = response.redirect }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

    $(document).on( "click", '.deleteClass',function(e) {
        var id = $(this).attr("data-id");
        $("#cmsPageID").val(id);
    });

    $("#cmsDeleteForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#cmsDeleteForm')[0]);
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

    $("#staticBlockForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            blockTitle: { required: true, },
            blockIdentifier: { required: true, },
            blockType: { required: true, },
        },
        submitHandler: function(form) {
            var formData = new FormData($('#staticBlockForm')[0]);
             if (CKEDITOR.instances['blockContent']){
               formData.append('blockContent', CKEDITOR.instances["blockContent"].getData());
            }
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
                        setTimeout(function() { window.location.href = response.redirect }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

    $(document).on( "click", '.deleteBlock',function(e) {
        var id = $(this).attr("data-id");
        $("#blockID").val(id);
    });

    $("#blockDeleteForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#blockDeleteForm')[0]);
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
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {window.location = response.redirect; })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

    $("#bannerForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            bannerType: {required: true,},
            //"customFil[]": "required",
        },
        /*messages: {
            "customFil[]": "This field is required.",
        },*/
        submitHandler: function(form) {

			var start_date_Arr = [];
			$(".start_date").each(function() {
				var dateValues =$(this).val();
				if(dateValues !=''){
					start_date_Arr.push(dateValues+'+');
				}else{
					start_date_Arr.push(0+'+');
				}
			});

			var end_date_Arr = [];
			$(".end_date").each(function() {
				var end_date_value =$(this).val();
				if(end_date_value !=''){
					end_date_Arr.push(end_date_value+'+');
				}else{
					end_date_Arr.push(0+'+');
				}
			});

			var arr = [];
			$(".type_ds").each(function() {
				var types_ids =$(this).val();
				arr.push(types_ids+'-');
			});

            var formData = new FormData($('#bannerForm')[0]);
			formData.append("types_ids", arr);
			formData.append("start_dateArr", start_date_Arr);
			formData.append("end_dateArr", end_date_Arr);

            // $('#bannerForm').find('textarea').each(function(index) {
            // //
            //        CKEDITOR.instances[$(this).attr('id')].updateElement();
            // //
            //  $(this).val(CKEDITOR.instances[$(this).attr('id')].getData());
            // });
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                      $("#save_banners").prop('disabled', true); // disable button
                    },
                success:function(response) {
                     console.log(response);
                     $("#save_banners").prop('disabled', false); // enable button
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {location.reload(); })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                },
                error:function(err,xhr){
                    console.log(err);
                    console.log(xhr);
                },
                complete:function(){
                    console.log('done');
                }
            });
        }
    });


    $("#homeblockForm").validate({
        // ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            //bannerType: {required: true,},
            //"customFil[]": "required",
        },
        /*messages: {
            "customFil[]": "This field is required.",
        },*/
        submitHandler: function(form) {
            var formData = new FormData($('#homeblockForm')[0]);

            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                      $("#save_homeblock").prop('disabled', true); // disable button
                    },
                success:function(response) {
                     console.log(response);
                     $("#save_homeblock").prop('disabled', false); // enable button
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {location.reload(); })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                },
                error:function(err,xhr){
                    console.log(err);
                    console.log(xhr);
                },
                complete:function(){
                    console.log('done');
                }
            });
        }
    });
// Menu Section

    $("#ckbCheckAll").click(function () {
        $('input[name="chk_cat_menu[]"]').prop('checked', $(this).prop('checked'));
    });

    $('input[name="chk_cat_menu[]"]').change(function(){
        if (!$(this).prop("checked")){
           $("#ckbCheckAll").prop("checked",false);
           $(this).closest('label').parent().find(':checkbox').prop('checked', false);
        }else{
            var total = $('input[name="chk_cat_menu[]"]').length;
            var actives = $('input[name="chk_cat_menu[]"]:checked').length;

            $(this).closest('li').parent().parent().find(':checkbox').first(':checkbox').prop('checked', $(this).prop('checked'));
            $(this).closest('li').parent().parent().closest('ul').parent().find(':checkbox').first(':checkbox').prop('checked', $(this).prop('checked'));

            if(actives==total){
                $("#ckbCheckAll").prop("checked", $(this).prop('checked'));
            }else{
                $("#ckbCheckAll").prop("checked",false);
            }

        }

    });


    $("input[name=top_menu_selection]").click(function() {
        var cat_type = $(this).val();
        if(cat_type==1){
            $('#category_menu_list').show();
            $('#cust_menu_list').hide();
        }else if(cat_type==2){
            $('#category_menu_list').hide();
            $('#cust_menu_list').show();
        }

    });

   $(document).on( "click", '.menuClass',function(e) {
        var id = $(this).attr("data-id");
        $("#menuType").val(id);
    });

    $("#menuForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#menuForm')[0]);
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
                        setTimeout(function() {window.location.reload();}, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

    $("#categoryMenuForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            chk_cat_menu: {
                required: true,
                minlength: 1,
            }
        },
        submitHandler: function(form) {
            var formData = new FormData($('#categoryMenuForm')[0]);
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
                        swal({ title: "",text: response.msg, button: false, icon: 'success' },
                        function() {location.reload(); })
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

    $("#customMenuForm").validate({
        ignore: ':hidden',
        rules: {
            menu_name: {
                required: true,
            },
            m_type: {
                required: true,
            },
            m_cust_link: {
                required: true,
                url: true
            },
        },
        submitHandler: function(form) {
            var formData = new FormData($('#customMenuForm')[0]);
            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response);
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' })
                        setTimeout(function() {window.location.href = response.redirect }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

});

function showFieldByMenyType(id){
    if(id=='1')
    {
       $('#custom_link').show();
        $('#page_field').hide();
        $('#category_field').hide();
    }else if(id=='2'){
        $('#custom_link').hide();
        $('#page_field').show();
        $('#category_field').hide();
    }else if(id=='3'){
        $('#custom_link').hide();
        $('#page_field').hide();
        $('#category_field').show();
    }else{
        return false;
    }
}

function checkURL(url) {
    var string = url.value;

    if (!~string.indexOf("http")) {
        string = "http://" + string;
    }

    url.value = string;
    return url;
}

function previewImages(id)
{
    var file = $("#customFile_"+id).val();
    $("#customFile_"+id).siblings("input[type='hidden']").val(file);

    var total_file=document.getElementById("customFile_"+id).files.length;
    for(var i=0;i<total_file;i++)
    {
        var uniqid=Date.now()
        $('#uploadPreview_'+id).html('<img src="' + URL.createObjectURL(event.target.files[i]) + '" width="200">');
    }
}

function previewImagesMobile(id)
{
    var file = $("#customFileMobile_"+id).val();
    $("#customFileMobile_"+id).siblings("input[type='hidden']").val(file);

    var total_file=document.getElementById("customFileMobile_"+id).files.length;
    for(var i=0;i<total_file;i++)
    {
        var uniqid=Date.now()
        $('#uploadPreviewMobile_'+id).html('<img src="' + URL.createObjectURL(event.target.files[i]) + '" width="200">');
    }
}

function OpenEditMenu(id,code)
{
    console.log(id);
    console.log(code);
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openeditmenupopup",
            data: {id:id,code:code},
        //  async:false,
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

function SaveExistingMenu()
{
    var menu_name=$('#menu_name').val();
    console.log(menu_name);
    var hidden_menu_id=$('#hidden_menu_id').val();
    var code=$('#code').val();

    if(menu_name==''){
        swal("Error", "Please enter category name", "error");
        return false;
    }
    else{
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/saveMenuTranslate",
            data: {code:code,hidden_menu_id:hidden_menu_id,menu_name:menu_name},
            success: function(response) {
                console.log(response);
                if(response.flag == 1)
                    {
                        console.log(response)
                        swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }
                    else
                    {
                        swal({
                            title: "",
                            icon: "error",
                            text: response.msg,
                            buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }

            }
        });
    }
}

function OpenEditCms(cms_id,code)
{
    if(cms_id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openeditcmspopup",
            data: {cms_id:cms_id,code:code},
        //  async:false,
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

function SaveExistingCms()
{
    var hidden_cms_id=$('#hidden_cms_id').val();
    var code=$('#code').val();
    var pageTitle_lang=$('#pageTitle_lang').val();
    var metaTitle_lang=$('#metaTitle_lang').val();
    var metaKeyword_lang=$('#metaKeyword_lang').val();
    var metaDescription_lang=$('#metaDescription_lang').val();
    var pageContent_lang = CKEDITOR.instances['pageContent_lang'].getData();

    if(pageTitle_lang==''){
        swal("Error", "Please enter page title", "error");
        return false;
    }
   else if(pageContent_lang===""){
        swal("Error", "Please enter page content", "error");
        return false;
    }
    else{
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/saveCmsTranslate",
            data: {code:code,hidden_cms_id:hidden_cms_id,pageContent_lang:pageContent_lang,metaDescription_lang:metaDescription_lang,pageTitle_lang:pageTitle_lang,metaTitle_lang:metaTitle_lang,metaKeyword_lang:metaKeyword_lang},
            success: function(response) {
                console.log(response);
                if(response.flag == 1)
                    {
                        console.log(response)
                        swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }
                    else
                    {
                        swal({
                            title: "",
                            icon: "error",
                            text: response.msg,
                            buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }

            }
        });
    }
}

function OpenEditFooter(id,code)
{
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openeditfooter",
            data: {id:id,code:code},
        //  async:false,
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


function SaveFooter()
{
    var hidden_footer_id=$('#hidden_footer_id').val();
    var code=$('#code').val();
    var blockTitle_lang=$('#blockTitle_lang').val();
    var blockContent_lang = CKEDITOR.instances['blockContent_lang'].getData();

    if(blockTitle_lang==''){
        swal("Error", "Please enter Static Block title", "error");
        return false;
    }
   else if(blockContent_lang ===""){
        swal("Error", "Please enter Static Block content", "error");
        return false;
    }
    else{
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/saveFooterTranslate",
            data: {code:code,hidden_footer_id:hidden_footer_id,blockTitle_lang:blockTitle_lang,blockContent_lang:blockContent_lang},
            success: function(response) {
                console.log(response);
                if(response.flag == 1)
                    {
                        console.log(response)
                        swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }
                    else
                    {
                        swal({
                            title: "",
                            icon: "error",
                            text: response.msg,
                            buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }

            }
        });
    }
}

function OpenHomeBlock(id,code)
{
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openeHomeBlock",
            data: {id:id,code:code},
        //  async:false,
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


function SaveHomeBlock()
{

    var hidden_home_id=$('#hidden_home_id').val();
    var code=$('#code').val();
    var heading_lang=$('#heading_lang').val();
    var description_lang=$('#description_lang').val();

            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/saveHomeBlockTranslate",
            data: {code:code,hidden_home_id:hidden_home_id,heading_lang:heading_lang,description_lang:description_lang},
            success: function(response) {
                console.log(response);
                if(response.flag == 1)
                    {
                        console.log(response)
                        swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }
                    else
                    {
                        swal({
                            title: "",
                            icon: "error",
                            text: response.msg,
                            buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }

            }
        });
}

function OpenFooterBlock(id,code)
{
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openeFooterBlock",
            data: {id:id,code:code},
        //  async:false,
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

function SaveFooterBlock()
{

    var hidden_footer_id=$('#hidden_footer_id').val();
    var code=$('#code').val();
    var bannerHeading_lang=$('#bannerHeading_lang').val();

            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/saveFooterBlockTranslate",
            data: {code:code,hidden_footer_id:hidden_footer_id,bannerHeading_lang:bannerHeading_lang},
            success: function(response) {
                console.log(response);
                if(response.flag == 1)
                    {
                        console.log(response)
                        swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }
                    else
                    {
                        swal({
                            title: "",
                            icon: "error",
                            text: response.msg,
                            buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }

            }
        });
}

function OpenBanner(id,code)
{
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openeBanner",
            data: {id:id,code:code},
        //  async:false,
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


function SaveBanner()
{

    var hidden_banner_id=$('#hidden_banner_id').val();
    var code=$('#code').val();
    var banner_heading_lang=$('#banner_heading_lang').val();
    var buttonText_lang=$('#buttonText_lang').val();

    var desc_lang = CKEDITOR.instances['desc_lang'].getData();


            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/SaveBanners",
            data: {desc_lang:desc_lang,code:code,hidden_banner_id:hidden_banner_id,banner_heading_lang:banner_heading_lang,buttonText_lang:buttonText_lang},
            success: function(response) {
                console.log(response);
                if(response.flag == 1)
                    {
                        console.log(response)
                        swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }
                    else
                    {
                        swal({
                            title: "",
                            icon: "error",
                            text: response.msg,
                            buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }

            }
        });
}



 $("#promoBanner").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        rules: {
            banner_text: { required: true, },
            background_color: { required: true, },
            pageContent: { ckrequired:true, },
        },
        submitHandler: function(form) {
            var formData = new FormData($('#promoBanner')[0]);

            $.ajax({
                url: form.action,
                type: 'ajax',
                method: form.method,
                dataType: 'json',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response);
                    if (response.flag == 1) {
                        swal({ title: "",text: response.msg, button: false, icon: 'success' })
                        setTimeout(function() { window.location.href = response.redirect }, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

 $("#promoDeleteForm").validate({
        ignore: ':hidden',
        //ignore: ".ignore",
        submitHandler: function(form) {
            var formData = new FormData($('#promoDeleteForm')[0]);
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
                        setTimeout(function() {window.location.reload();}, 1000);
                    } else {
                        swal({ title: "",text: response.msg, button: false, icon: 'error' })
                        return false;
                    }
                }
            });
        }
    });

 $(document).on( "click", '.deletePromo',function(e) {
        var id = $(this).attr("data-id");
        $("#promoID").val(id);
    });

 function OpenEditPromoBanners(id,code)
{
    if(id!='' && code!=''){
        $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"WebshopController/openePromoBannersTrans",
            data: {id:id,code:code},
        //  async:false,
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

function savePromeBannersTrans()
{


    var promoId=$('#promoId').val();
    var code=$('#code').val();
    var banner_text=$('#banner_text').val();
    if(banner_text ==''){
        swal("Error", "Please enter Banner Text", "error");
        return false;
    }
    else{
            $.ajax({
            type: "POST",
            dataType: "json",
            url: BASE_URL+"WebshopController/savePromeBannersTrans",
            data: {code:code,promoId:promoId,banner_text:banner_text},
            success: function(response) {
                console.log(response);
                if(response.flag == 1)
                    {
                        console.log(response)
                        swal({
                        title: "",
                        icon: "success",
                        text: response.msg,
                        buttons: false,
                        })
                        setTimeout(function() {
                        location.reload();

                        }, 1000);
                    }
                    else
                    {

                    }

            }
        });
    }
}

$("#top_menu_form").submit(function(e) {
	e.preventDefault();
	var formData = new FormData($('#top_menu_form')[0]);
	$.ajax({
		url: BASE_URL+"WebshopController/saveTopMenuPosition",
		type: 'post',
		dataType: 'json',
		data: $("#top_menu_form").serialize(),
	    success: function(data) {
			if(data.flag==1){
				swal({
					title: "",
					icon: "success",
					text: data.msg,
					buttons: false,
					})
					setTimeout(function() {
					location.reload();

					}, 1000);
			}else{
				swal({ title: "",text: data.msg, button: false, icon: 'error' })
				return false;
			}
		}

});
});


// Menu Section End
