$(document).ready(function () {

  $("#ckbCheckAllVariants").click(function () {

    $('input[name="ckb_Variant[]"]').prop("checked", $(this).prop("checked"));

  });



  $("#type_apply").on("click", function (e) {

    e.preventDefault();

    // console.log('hiii');

    var formData = new FormData($("#product-frm-edit")[0]);

    $.ajax({

      type: "POST",

      url: BASE_URL + "Sellerproduct/update_inv_type",

      dataType: "json",

      data: formData,

      processData: false,

      contentType: false,



      success: function (response) {

        // console.log(response);

        if (response.status == 200) {

          swal(

            {

              title: "",

              icon: "success",

              text: response.message,

              buttons: true,

            },

            function () {

              location.reload();

            }

          );

        } else {

          // console.log(response.message);

          swal(

            {

              title: "",

              html: true,

              icon: "error",

              text: response.message,

              buttons: true,

            },

            function () {

              location.reload();

            }

          );

        }

      },

    });

  });



  $("#product-frm-edit").validate({

    ignore: [],

    debug: false,

    rules: {

      product_name: {

        required: true,

      },

      product_code: {

        required: true,

        remote: {

          url: BASE_URL + "sellerproduct/checkproductcode",

          type: "POST",

          data: {

            flag: "edit",

            pid: $("#pid").val(),

            product_code: function () {

              return $("#product_code").val();

            },

          },

        },

      },



      description: {

        required: function () {

          CKEDITOR.instances.description.updateElement();

        },

        ckrequired: true,

      },

      highlights: {

        required: function () {

          CKEDITOR.instances.highlights.updateElement();

        },

        ckrequired: true,

      },

      lang_description: {
        required: function()
        {
          CKEDITOR.instances.lang_description.updateElement();
				},
				ckrequired:true
      },

			lang_highlights: {
        required: function()
        {
          CKEDITOR.instances.lang_highlights.updateElement();
				},
				ckrequired:true
      },

      category: {

        required: true,

      },

      sub_category: {

        required: true,

      },

      "gallery_image[]": {

        required: function (element) {

          if ($(".uploadPreview").length > 0) {

            return false;

          } else {

            return true;

          }

        },

      },

      product_type: {

        required: true,

      },

      sku: {

        required: function (element) {

          if ($("#product_type").val() == "simple") {

            return true;

          } else {

            return false;

          }

        },

        validatesku: function (element) {

          if ($("#product_type").val() == "simple") {

            return true;

          } else {

            return false;

          }

        },

        remote: {

          url: BASE_URL + "sellerproduct/checkskuexist",

          type: "POST",

          data: {

            flag: "edit",

            pid: $("#pid").val(),

            product_type: $("#product_type").val(),

            sku: function () {

              return $("#sku").val();

            },

          },

        },

      },

      cost_price: {

        required: function (element) {

          if ($("#product_type").val() == "simple") {

            return true;

          } else {

            return false;

          }

        },

      },

      price: {

        required: function (element) {

          if ($("#product_type").val() == "simple") {

            return true;

          } else {

            return false;

          }

        },

      },

      stock_qty: {

        required: function (element) {

          if ($("#product_type").val() == "simple") {

            return true;

          } else {

            return false;

          }

        },

        remote: {

          url: BASE_URL + "sellerproduct/checkstockqty",

          type: "POST",

          data: {

            flag: "edit",

            pid: $("#pid").val(),

            product_type: $("#product_type").val(),

            stock_qty: function () {

              return $("#stock_qty").val();

            },

          },

        },

      },

      barcode: {

        remote: {

          url: BASE_URL + "sellerproduct/checkbarcodeexist",

          type: "POST",

          data: {

            flag: "edit",

            pid: $("#pid").val(),

            product_type: $("#product_type").val(),

            barcode: function () {

              return $("#barcode").val();

            },

          },

        },

      },

    },

    messages: {

      product_code: {

        //required: "Please enter valid product code.",

        remote: "Product code already in use.",

      },

      stock_qty: {

        //required: "Please enter valid sku.",

        remote:

          "Please enter number greater than " +

          $("#stock_qty").data("ordered_qty"),

      },

      sku: {

        //required: "Please enter valid sku.",

        remote: "Sku already in use.",

      },

      barcode: {

        //required: "Please enter valid sku.",

        remote: "Barcode already in use.",

      },

      //  inputPassword: { "minlength": "Please enter 8 or more characters." },

    },

    submitHandler: function (form) {

      // $('#save_product').attr('disabled',true);



      var form_obj = $("#product-frm-edit")[0];

      var totalfiles = document.getElementById("gallery_image").files.length;

      var formData = new FormData(form_obj);

      $.each($("input[type='file']")[0].files, function (i, file) {

        formData.append("gallery_image[]", file);

      });



      $.ajax({

        url: form.action,

        type: "ajax",

        method: form.method,

        dataType: "json",

        enctype: "multipart/form-data",

        processData: false, // Important!

        contentType: false,

        cache: false,

        data: formData,

        beforeSend: function () {

          // $('#ajax-spinner').show();

        },

        success: function (response) {

          // $('#save_product').attr('disabled',false);

          //console.log(response);return false;

          $("#ajax-spinner").hide();

          if (response.status == 200) {

            swal("Success", response.message, "success");

            setTimeout(function () {

              windaow.location.href = BASE_URL + "seller/warehouse";

              loction.reload();

            }, 1000);

          } else {

            swal("Error", response.message, "error");

            return false;

          }

        },

      });

    },

  });



  $(".number-input").each(function () {

    $(this).rules("add", {

      required: true,

      number: true,

      messages: {

        required: "Field is required",

      },

    });

  });



  $(".required-field").each(function () {

    $(this).rules("add", {

      required: true,

      messages: {

        required: "Field is required",

      },

    });

  });



  jQuery.validator.addMethod(

    "ckrequired",

    function (value, element, params) {

      var idname = jQuery(element).attr("id");

      var messageLength = jQuery.trim(CKEDITOR.instances[idname].getData());

      return !params || messageLength.length !== 0;

    },

    "This field is required"

  );



  $.validator.addMethod(

    "regex_pcname",

    function (value, element, regexp) {

      var re = new RegExp(regexp);

      return this.optional(element) || re.test(value);

    },

    "Only allowed numbers(0-9), letters(a-z A-Z), underscore(_) and hyphen(-)."

  );



  $("#product_code").rules("add", { regex_pcname: "^[a-zA-Z0-9-_]+$" });



  $.validator.addMethod(

    "validatesku",

    function (value, element) {

      //test user value with the regex

      var regex_spe = "^[a-zA-Z0-9-_:/ ]+$";

      var re_new = new RegExp(regex_spe);

      return this.optional(element) || re_new.test(value);

    },

    "Letters, numbers, slash(/), dash(-), space and colon(:) only please"

  );



  $(".valid-sku").each(function () {

    $(this).rules("add", {

      validatesku: true,

    });

  });

});



function IsConfirmRemoveProduct(product_id) {

  swal(

    {

      title: "Are you sure?",

      text: "You won't be able to revert this!",

      type: "warning",

      showCancelButton: true,

      confirmButtonColor: "#3085d6",

      cancelButtonColor: "#d33",

      confirmButtonText: "Yes, delete it!",

      cancelButtonText: "Cancel",

      closeOnConfirm: false,

      closeOnCancel: false,

    },

    function (isConfirm) {

      if (isConfirm) {

        DeleteProdudct(product_id);

      } else {

        swal.close();

      }

    }

  );

}



function DeleteProdudct(product_id) {

  if (product_id != "") {

    $.ajax({

      type: "POST",

      dataType: "html",

      url: BASE_URL + "sellerproduct/deleteProduct",

      data: { product_id: product_id },

      async: false,

      complete: function () {},

      beforeSend: function () {

        $("#ajax-spinner").show();

      },

      success: function (response) {

        $("#ajax-spinner").hide();

        if (response != "error") {

          swal("Deleted!", "Your phas been deleted.", "success");

          window.location.href = BASE_URL + "seller/warehouse";

        } else {

          swal("Error", "Something went wrong", "error");

        }

      },

    });

  } else {

    $("#ajax-spinner").hide();



    return false;

  }

}



function OpenEditProduct(product_id, code) {

  console.log();

  console.log(code);

  if (product_id != "" && code != "") {

    $.ajax({

      type: "POST",

      dataType: "html",

      url: BASE_URL + "Sellerproduct/openeditproductpopup",

      data: { product_id: product_id, code: code },

      //  async:false,

      complete: function () {},

      beforeSend: function () {

        // $('#ajax-spinner').show();

      },

      success: function (response) {

        $("#FBCUserCommonModal").modal();

        $("#modal-content").html(response);

      },

    });

  } else {

    return false;

  }

}



function SaveExistingProduct() {

  var product_name_lang = $("#product_name_lang").val();



  var description_lang = CKEDITOR.instances["description_lang"].getData();

  var highlights_lang = CKEDITOR.instances["highlights_lang"].getData();



  var meta_keyword_lang = $("#meta_keyword_lang").val();

  var meta_title_lang = $("#meta_title_lang").val();

  var meta_description_lang = $("#meta_description_lang").val();

  var hidden_product_id = $("#hidden_product_id").val();

  var code = $("#code").val();

  if (product_name_lang == "") {

    swal("Error", "Please enter product name", "error");

    return false;

  } else if (description_lang === "") {

    swal("Error", "Please enter product description", "error");

    return false;

  } else if (highlights_lang === "") {

    swal("Error", "Please enter product highlights", "error");

    return false;

  } else {

    $.ajax({

      type: "POST",

      dataType: "json",

      url: BASE_URL + "Sellerproduct/saveProductTranslate",

      data: {

        meta_description_lang: meta_description_lang,

        hidden_product_id: hidden_product_id,

        code: code,

        product_name_lang: product_name_lang,

        description_lang: description_lang,

        highlights_lang: highlights_lang,

        meta_keyword_lang: meta_keyword_lang,

        meta_title_lang: meta_title_lang,

      },

      success: function (response) {

        console.log(response);

        if (response.flag == 1) {

          console.log(response);

          swal({

            title: "",

            icon: "success",

            text: response.msg,

            buttons: false,

          });

          setTimeout(function () {

            location.reload();

          }, 1000);

        } else {

          swal({

            title: "",

            icon: "error",

            text: response.msg,

            buttons: false,

          });

          setTimeout(function () {

            location.reload();

          }, 1000);

        }

      },

    });

  }

}



function OpenMediaProduct(product_id) {

  if (product_id != "") {

    $.ajax({

      type: "POST",

      dataType: "html",

      url: BASE_URL + "Sellerproduct/openproductmediapopup",

      data: { product_id: product_id },

      complete: function () {},

      beforeSend: function () {},

      success: function (response) {

        $("#FBCUserCommonModal").modal();

        $("#modal-content").html(response);

      },

    });

  } else {

    return false;

  }

}



function OpenMediaVariantProduct(product_id) {

  if (product_id != "") {

    var variant_id = $("input[name='product_variant']:checked").val();

    $.ajax({

      type: "POST",

      dataType: "html",

      url: BASE_URL + "Sellerproduct/openproductmediavariantpopup",

      data: { product_id: product_id, variant_id: variant_id },

      complete: function () {},

      beforeSend: function () {},

      success: function (response) {

        $("#FBCUserCommonModal").modal();

        $("#modal-content").html(response);

      },

    });

  } else {

    return false;

  }

}



function SaveProductMediaVariant() {

  var variant_id = $("input[name='product_variant']:checked").val();

  var product_id = $("#hidden_product_id").val();

  $.ajax({

    type: "POST",

    dataType: variant_id > 0 ? "html" : "json",

    url: BASE_URL + "Sellerproduct/saveProductMediaVariant",

    data: { variant_id: variant_id, product_id: product_id },

    success: function (response) {

      if (response.status == 400) {

        swal({

          title: "",

          icon: "success",

          text: response.message,

          buttons: false,

        });

        setTimeout(function () {

          location.reload();

        }, 1000);

      } else if (response.status == 600) {

        swal({

          title: "",

          icon: "error",

          text: response.message,

          buttons: false,

        });

        setTimeout(function () {

          location.reload();

        }, 1000);

      } else {

        $("#FBCUserSecondaryModal").modal();

        $("#modal-content-second").html(response);

        $("#FBCUserCommonModal").modal("hide");

      }

    },

  });

}



function SaveProductMediaByAttr() {



  var formData = new FormData($("#product-Media")[0]);



  $.ajax({

    type: "POST",

    dataType: "json",

    url: BASE_URL + "Sellerproduct/saveProductMediaAttrValue",

    data: formData,

    processData: false,

    contentType: false,

    cache: false,

    success: function (response) {

      if (response.status == 200) {

        swal({

          title: "",

          icon: "success",

          text: response.message,

          buttons: false,

        });

        setTimeout(function () {

          location.reload();

        }, 1000);

      } else {

        swal({

          title: "",

          icon: "error",

          text: response.message,

          buttons: false,

        });

        setTimeout(function () {

          location.reload();

        }, 1000);

      }

    },

  });

}



$(document).ready(function () {

  $("#product_publication").change(function (e) {

    e.preventDefault();



    var pubId = $("#product_publication").val();



    var check = $('input[type=radio][name="type-of-commission"]:checked').val();



    if (check == 0) {

      $.ajax({

        url: BASE_URL + "Sellerproduct/get_publisher_data",

        method: "POST",

        dataType: "JSON",

        data: {

          id: pubId,

        },

        success: function (resp) {

          $("#pub_com_percentage").val(resp.data);

        },

      });

    } else {

      $("#pub_com_percentage").val("");

    }

  });



  $("input[type=radio][name=type-of-commission]").change(function () {

    if (this.value == 0) {

      $("#pub_com_percentage").attr("readonly", true);



      var pubId = $("#product_publication").val();



      $.ajax({

        url: BASE_URL + "Sellerproduct/get_publisher_data",

        method: "POST",

        dataType: "JSON",

        data: {

          id: pubId,

        },

        success: function (resp) {

          $("#pub_com_percentage").val(resp.data);

        },

        error: function (resp) {},

      });

    }

    if (this.value == 1) {

      $("#pub_com_percentage").attr("readonly", false);



      var pubId = $("#product_publication").val();



      $.ajax({

        url: BASE_URL + "Sellerproduct/get_product_commission_data",

        method: "POST",

        dataType: "JSON",

        data: {

          id: pubId,

        },

        success: function (resp) {

          $("#pub_com_percentage").attr("placeholder", "0.00");

          $("#pub_com_percentage").val("");

        },

        error: function (resp) {},

      });

    }

  });

});

