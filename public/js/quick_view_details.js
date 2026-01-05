// quick view product-details
function QuickViewProdDetails(productslug, produrl) {
  $("#ajax-spinner").show();
  $.ajax({
    url: BASE_URL + "ProductsController/productQuickDetails",
    type: "POST",
    dataType: "html",
    data: {
      productslug: productslug,
      produrl: produrl,
    },
    success: function (response) {
      $("#ajax-spinner").hide();
      $("#myModal").modal();
      $("#myModal .modal-body").html(response);
      initImageZoom();
      getvariantdata();
      sd();
    },
  });
}

function initImageZoom() {
  $(".product-main-image").zoom({
    url: $(".product-main-image img").attr("data-BigImgSrc"),
  });
}

function sd() {
  if (jQuery(".fancybox-button").size() > 0) {
    jQuery(".fancybox-button").fancybox({
      groupAttr: "data-rel",
      prevEffect: "none",
      nextEffect: "none",
      closeBtn: true,
      helpers: {
        title: {
          type: "inside",
        },
      },
    });
  }
}

function getvariantdata() {
  var product_type = $("#product_type").val();
  if (product_type == "bundle") {
    $(".bundle_child_id").each(function (i) {
      var bundle_child_id = $(this).val();

      GetVariantProductForBundle(bundle_child_id);
    });
  } else {
    if (product_type == "configurable") {
      GetVariantProduct();

      return false;
    }
  }
}

function GetVariantProductForBundle(bundle_child_id) {
  $("#addtocart_error").html("");

  $("#add_to_cart").attr("disabled", true);

  var selected_variant = [];

  var count_variant = $(".single_variant_" + bundle_child_id).length;

  var variant_count = [];

  var product_id = $("#product_id_child_main_" + bundle_child_id).val();

  var className = ".single_variant_" + bundle_child_id;

  var value_variants = $(
    ".single_variant_" + bundle_child_id + ":checked"
  ).val();

  var array_attr = value_variants.split(",");

  if (array_attr.length > 0) {
    var attr_value = array_attr[0];

    var attr_id = array_attr[1];

    var default_qty = array_attr[2];

    selected_variant.push({
      attr_id,
      attr_value,
    });

    variant_count.push({
      attr_id,
      attr_value,
    });

    var total_selected_variant = variant_count.length;

    if (total_selected_variant != "") {
      $.ajax({
        url: BASE_URL + "ProductsController/getVariantProduct",

        type: "POST",

        dataType: "json",

        cache: false,

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        data: {
          product_id: product_id,

          total_variant: total_selected_variant,

          selected_variant: selected_variant,

          media_variant_id: "",
        },

        success: function (response) {
          if (response.status == 200) {
            const simple_product_id =
              response.ConfigSimpleDetails.conf_simple_pro_id;

            const simple_price = parseFloat(
              response.ConfigSimpleDetails.conf_simple_pro_pice
            ).toFixed(2);

            var simple_qty =
              response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;

            const simple_qty_status =
              response.ConfigSimpleDetails.conf_simple_pro_inventory.status;

            const qty_limit = response.qty_limit;

            if (response.mediaGallery != "") {
              $("#product-image-section").html(response.mediaGallery);
            }

            if (simple_qty_status === "instock" && simple_qty >= default_qty) {
              $("#conf_simple_pid_" + bundle_child_id).val(simple_product_id);

              $("#conf_simple_price_" + bundle_child_id).val(simple_price);

              $("#conf_simple_qty_" + bundle_child_id).val(simple_qty);

              var string = $("#bundle-child-ids-merge").val();

              var bundle_child_ids = string.split(",");

              var flag = 0;

              $.each(bundle_child_ids, function (key, value) {
                var id = $("#conf_simple_pid_" + value).val();

                if ($("#conf_simple_pid_" + value).val() == "") {
                  flag = 1;
                }
              });

              if (flag == 0) {
                $("#add_to_cart").attr("disabled", false);

                $("#addtocart_error").html("");
              } else {
                $("#add_to_cart").attr("disabled", true);

                $("#addtocart_error").html("Not all values selected");
              }
            } else {
              $("#quantity").val(1);

              $("#quantity").attr("disabled", true);

              $("#add_to_cart").attr("disabled", true);

              $("#addtocart_error").html("Product is out of stock.");

              return false;
            }
          } else {
            $("#conf_simple_pid_" + bundle_child_id).val("");

            $("#conf_simple_price_" + bundle_child_id).val("");

            $("#conf_simple_qty_" + bundle_child_id).val("");

            $("#add_to_cart").attr("disabled", true);

            $("#addtocart_error").html(response.message);

            return false;
          }
        },
      });
    } else {
      return false;
    }
  }
}

function ValidateEmail() {
  var email = document.getElementById("notified-email").value;

  var lblError = document.getElementById("lblError");

  lblError.innerHTML = "";

  var expr =
    /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

  if (!expr.test(email)) {
    lblError.innerHTML = "Invalid email address.";
  }
}

function openNotifiedPopup(product_id) {
  var email_notified = $("#notified-email").val();

  $("#lblError").html("");

  var expr =
    /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

  if (email_notified != "" && expr.test(email_notified)) {
    if (product_id != "" && email_notified != "") {
      $.ajax({
        type: "POST",

        dataType: "html",

        url: BASE_URL + "ProductsController/productNotified",

        data: {
          email: email_notified,
          product_id: product_id,
        },

        complete: function () {},

        beforeSend: function () {
          $("#notified-keep-inform").prop("disabled", true);
          $("#ajax-spinner").show();
        },

        success: function (response) {
          $("#ajax-spinner").hide();
          swal({
            title: "KEEP ME NOTIFIED",
            html: "<label>You have been successfully subscribed to updates of this product.</label>",
            icon: "success",
          });
          $("#notified-keep-inform").prop("disabled", false);
        },
      });
    } else {
      return false;
    }
  } else {
    $("#lblError").html("Please enter valid email.");

    return false;
  }
}

function Quickaddtocart() {
  $("#addtocart_error").html("");
  $("#add_to_cart").attr("disabled", true);
  var form_obj = $("#quick-product-frm")[0];
  var formData = new FormData(form_obj);
  $.ajax({
    url: BASE_URL + "CartController/addtocart",
    type: "ajax",
    method: "POST",
    dataType: "json",
    processData: false, // Important!
    contentType: false,
    cache: false,
    data: formData,
    beforeSend: function () {
      $("#ajax-spinner").show();
    },
    success: function (response) {
      $("#add_to_cart").attr("disabled", false);
      $("#ajax-spinner").hide();
      if (response.status == 200) {
        $("#addtocart-message")
          .html('<span class="success-msg">' + response.message + "</span>")
          .show();
        setTimeout(function () {
          $("#addtocart-message").hide();
        }, 3000);
        $.ajax({
          type: "POST",
          url: BASE_URL + "CartController/updateminicart",
          dataType: "html",
          data: {},
          success: function (response) {
            $("#mini-cart-main-container").html(response);
            $(".top-cart-content").css("display", "block");
            $("#myModal").modal("hide");
          },
        });

        return false;
      } else {
        $("#addtocart-message")
          .html('<span class="error-msg">' + response.message + "</span>")
          .show();
        setTimeout(function () {
          $("#addtocart-message").hide();
        }, 3000);
        return false;
      }
    },
  });
}

function numberWithCommas(number) {
  var parts = number.toString().split(".");

  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");

  return parts.join(".");
}

function _GetVariantProduct() {
  $("#addtocart_error").html("");
  $("#add_to_cart").prop("disabled", true);
  var selected_variant = [];
  var variant_count = [];
  var product_id = $("#product_id").val();

  var media_variant_id = $("#media_variant_id").val();

  var value_variants = $(".single_variant:checked").val();

  var array_attr = value_variants.split(",");

  if (array_attr.length > 0) {
    var attr_value = array_attr[0];
    var attr_id = array_attr[1];
    selected_variant.push({
      attr_id,
      attr_value,
    });
    variant_count.push({
      attr_id,
      attr_value,
    });

    var total_selected_variant = variant_count.length;

    if (total_selected_variant != "") {
      $.ajax({
        url: BASE_URL + "ProductsController/getVariantProduct",
        type: "POST",
        dataType: "json",
        cache: false,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        data: {
          product_id: product_id,
          total_variant: total_selected_variant,
          selected_variant: selected_variant,
          media_variant_id: media_variant_id,
          //   isPrelaunch:isPrelaunch
        },

        success: function (response) {
          if (response.status == 200) {
            const simple_product_id =
              response.ConfigSimpleDetails.conf_simple_pro_id;

            const simple_price = parseFloat(
              response.ConfigSimpleDetails.conf_simple_pro_pice
            ).toFixed(2);

            // const simple_qty =
            //   response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;
            var simple_qty =
              response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;

            const simple_qty_status =
              response.ConfigSimpleDetails.conf_simple_pro_inventory.status;

            const special_price = response.ConfigSimpleDetails.special_price;

            const display_original =
              response.ConfigSimpleDetails.display_original;

            // const qty_limit = response.qty_limit;
            var qty_limit = response.qty_limit;

            if (response.mediaGallery != "") {
              $("#product-image-section").html(response.mediaGallery);
            }

            if (simple_qty_status === "instock") {
              $("#add_to_cart").prop("disabled", false);

              $("#conf_simple_pid").val(simple_product_id);

              $("#conf_simple_price").val(simple_price);

              $("#conf_simple_qty").val(simple_qty);

              if (special_price > 0) {
                if (display_original > 0 && display_original == 1) {
                  if (parseInt(special_price) < parseInt(simple_price)) {
                    var percent =
                      ((special_price - simple_price) / simple_price) * 100;

                    var percent_ceil = Math.round(percent);

                    var final_percentage = percent_ceil.toFixed(0);

                    var addition_percent_html = "";

                    if (final_percentage != 0.0) {
                      var addition_percent_html =
                        '<span class="price save-discount">(' +
                        final_percentage +
                        "%)</span>";
                    }

                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                    // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);

                    // 	special_price_convert = converted_price(special_price,currency_conversion_rate);

                    // 	$('.product-price-detail').html('<span class="discounted-price special-price" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span> <span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>'+addition_percent_html+'');

                    // }else{

                    $(".product-price-detail").html(
                      '<span class="discounted-price special-price" id="product_price">' +
                        CURRENCY_TYPE +
                        "&nbsp;" +
                        numberWithCommas(simple_price) +
                        '</span> <span class="special-price" id="discounted_price">' +
                        CURRENCY_TYPE +
                        "&nbsp;" +
                        numberWithCommas(special_price) +
                        "</span>" +
                        addition_percent_html +
                        ""
                    );

                    // }
                  } else {
                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                    // 	special_price_convert = converted_price(special_price,currency_conversion_rate);

                    // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');

                    // }else{

                    $(".product-price-detail").html(
                      '<span class="special-price" id="discounted_price">' +
                        CURRENCY_TYPE +
                        "&nbsp;" +
                        numberWithCommas(special_price) +
                        "</span>"
                    );

                    // }
                  }
                } else {
                  // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                  // 	special_price_convert = converted_price(special_price,currency_conversion_rate);

                  // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');

                  // }else{

                  $(".product-price-detail").html(
                    '<span class="special-price" id="discounted_price">' +
                      CURRENCY_TYPE +
                      "&nbsp;" +
                      numberWithCommas(special_price) +
                      "</span>"
                  );

                  // }
                }
              } else {
                // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);

                // 	$('.product-price-detail').html('<span class="special-price here" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span>');

                // }else{

                $(".product-price-detail").html(
                  '<span class="special-price" id="product_price">' +
                    CURRENCY_TYPE +
                    "&nbsp;" +
                    numberWithCommas(simple_price) +
                    "</span>"
                );

                // }
              }

              var j = 0;

              var qty_html_refresh = "";

              if (simple_qty > 0) {
                if (simple_qty > qty_limit) {
                  simple_qty = qty_limit;
                }

                for (j = 1; j <= simple_qty; j++) {
                  qty_html_refresh +=
                    '<option value="' + j + '">' + j + "</option>";
                }

                $("#quantity").attr("disabled", false);
              }

              $("#quantity").html(qty_html_refresh);
            } else {
              $("#quantity").html("");

              $("#quantity").attr("disabled", true);

              $("#add_to_cart").prop("disabled", true);

              $("#addtocart_error").html("Product is out of stock.");

              //swal('Error','Product is out of stock.','error');

              return false;
            }
          } else {
            $("#add_to_cart").prop("disabled", true);

            $("#addtocart_error").html(response.message);

            //swal('Error',response.message,'error');

            return false;
          }
        },
      });
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function GetVariantProduct() {
  $("#addtocart_error").html("");
  $("#add_to_cart").prop("disabled", true);
  var selected_variant = [];
  var variant_count = [];
  var product_id = $("#product_id").val();

  var media_variant_id = $("#media_variant_id").val();

  // var value_variants = $(".single_variant:checked").val();
  var value_variants = $('input[name="variant_options_data"]:checked').val();
  var conf_simple_id = value_variants;

  // console.log("VariantsOptionData: ",$('input[name="variant_options_data"]:checked').val());
  // return;

  var array_attr = value_variants.split(",");
  var conf_simple_id = array_attr[0];
  var product_id = array_attr[1];

  if (array_attr.length > 0) {
    var attr_value = array_attr[0];
    var attr_id = array_attr[1];
    selected_variant.push({
      attr_id,
      attr_value,
    });
    variant_count.push({
      attr_id,
      attr_value,
    });

    var total_selected_variant = variant_count.length;

    if (conf_simple_id != "") {
      $.ajax({
        url: BASE_URL + "ProductsController/getVariantProductNew",
        type: "POST",
        dataType: "json",
        cache: false,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        data: {
          parent_id: product_id,
          product_id: conf_simple_id,
          total_variant: total_selected_variant,
          selected_variant: selected_variant,
          media_variant_id: media_variant_id,
          //   isPrelaunch:isPrelaunch
        },

        success: function (response) {
          if (response.status == 200) {
            // alert(response.qty_limit);
            const simple_product_id =
              response.ConfigSimpleDetails.conf_simple_pro_id;

            const simple_price = parseFloat(
              response.ConfigSimpleDetails.conf_simple_pro_pice
            ).toFixed(2);

            const simple_qty =
              response.ConfigSimpleDetails.conf_simple_pro_inventory.qty;

            const simple_qty_status =
              response.ConfigSimpleDetails.conf_simple_pro_inventory.status;

            const special_price = response.ConfigSimpleDetails.special_price;

            const display_original =
              response.ConfigSimpleDetails.display_original;

            const qty_limit = response.qty_limit;

            if (response.mediaGallery != "") {
              $("#product-image-section").html(response.mediaGallery);
            }

            if (simple_qty_status === "instock") {
              $("#add_to_cart").prop("disabled", false);

              $("#conf_simple_pid").val(simple_product_id);

              $("#conf_simple_price").val(simple_price);

              $("#conf_simple_qty").val(simple_qty);

              if (special_price > 0) {
                if (display_original > 0 && display_original == 1) {
                  if (parseInt(special_price) < parseInt(simple_price)) {
                    var percent =
                      ((special_price - simple_price) / simple_price) * 100;

                    var percent_ceil = Math.round(percent);

                    var final_percentage = percent_ceil.toFixed(0);

                    var addition_percent_html = "";

                    if (final_percentage != 0.0) {
                      var addition_percent_html =
                        '<span class="price save-discount">(' +
                        final_percentage +
                        "%)</span>";
                    }

                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                    // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);

                    // 	special_price_convert = converted_price(special_price,currency_conversion_rate);

                    // 	$('.product-price-detail').html('<span class="discounted-price special-price" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span> <span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>'+addition_percent_html+'');

                    // }else{

                    $(".product-price-detail").html(
                      '<span class="discounted-price special-price" id="product_price">' +
                        CURRENCY_TYPE +
                        "&nbsp;" +
                        numberWithCommas(simple_price) +
                        '</span> <span class="special-price" id="discounted_price">' +
                        CURRENCY_TYPE +
                        "&nbsp;" +
                        numberWithCommas(special_price) +
                        "</span>" +
                        addition_percent_html +
                        ""
                    );

                    // }
                  } else {
                    // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                    // 	special_price_convert = converted_price(special_price,currency_conversion_rate);

                    // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');

                    // }else{

                    $(".product-price-detail").html(
                      '<span class="special-price" id="discounted_price">' +
                        CURRENCY_TYPE +
                        "&nbsp;" +
                        numberWithCommas(special_price) +
                        "</span>"
                    );

                    // }
                  }
                } else {
                  // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                  // 	special_price_convert = converted_price(special_price,currency_conversion_rate);

                  // 	$('.product-price-detail').html('<span class="special-price" id="discounted_price">'+currency_symbol +'&nbsp;'+special_price_convert+'</span>');

                  // }else{

                  $(".product-price-detail").html(
                    '<span class="special-price" id="discounted_price">' +
                      CURRENCY_TYPE +
                      "&nbsp;" +
                      numberWithCommas(special_price) +
                      "</span>"
                  );

                  // }
                }
              } else {
                // if (currency_code_session !== '' && default_currency_flag  !== '1'){

                // 	simple_price_convert = converted_price(simple_price,currency_conversion_rate);

                // 	$('.product-price-detail').html('<span class="special-price here" id="product_price">'+currency_symbol +'&nbsp;'+simple_price_convert+'</span>');

                // }else{

                $(".product-price-detail").html(
                  '<span class="special-price" id="product_price">' +
                    CURRENCY_TYPE +
                    "&nbsp;" +
                    numberWithCommas(simple_price) +
                    "</span>"
                );

                // }
              }

              var j = 0;

              var qty_html_refresh = "";

              if (simple_qty > 0) {
                if (simple_qty > qty_limit) {
                  simple_qty = qty_limit;
                }

                for (j = 1; j <= simple_qty; j++) {
                  qty_html_refresh +=
                    '<option value="' + j + '">' + j + "</option>";
                }

                $("#quantity").attr("disabled", false);
              }

              $("#quantity").html(qty_html_refresh);
            } else {
              $("#quantity").html("");

              $("#quantity").attr("disabled", true);

              $("#add_to_cart").prop("disabled", true);

              $("#addtocart_error").html("Product is out of stock.");

              //swal('Error','Product is out of stock.','error');

              return false;
            }
          } else {
            $("#add_to_cart").prop("disabled", true);

            $("#addtocart_error").html(response.message);

            //swal('Error',response.message,'error');

            return false;
          }
        },
      });
    } else {
      return false;
    }
  } else {
    return false;
  }
}

$(document).click(function (e) {
  // Check for left button
  if (e.button == 0) {
    // if ($(".top-cart-content").css("display") == "block") {
    //   $(".top-cart-content").css("display", "none");
    // } else {
    //   $(".top-cart-content").css("display", "block");
    // }
    // console.log("clicked");
  }
});
