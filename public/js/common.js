$(document).ready(function () {

  $(".validate-number").keydown(function (event) {

    if (event.shiftKey == true) {

      event.preventDefault();

    }



    if (

      (event.keyCode >= 48 && event.keyCode <= 57) ||

      (event.keyCode >= 96 && event.keyCode <= 105) ||

      event.keyCode == 8 ||

      event.keyCode == 9 ||

      event.keyCode == 37 ||

      event.keyCode == 39 ||

      event.keyCode == 46 ||

      event.keyCode == 190

    ) {

    } else {

      event.preventDefault();

    }



    if ($(this).val().indexOf(".") !== -1 && event.keyCode == 190)

      event.preventDefault();

  });



  $(".validate-char").on("keypress", function (key) {

    //alert(111111)

    if (

      (key.charCode < 97 || key.charCode > 122) &&

      (key.charCode < 65 || key.charCode > 90) &&

      key.charCode != 45 &&

      key.charCode != 32 &&

      key.charCode != 0

    ) {

      return false;

    }

  });



  $.validator.addMethod(

    "validateEmail",

    function (value, element) {

      return (

        this.optional(element) ||

        value.match(/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i)

      );

    },

    "Please enter valid email address."

  );



  //=============== Start Search ===========================



  $(".submit-search").on("click", function () {

    var name = $("#search").val();

    if (name == "") {

      return false;

    } else {

      $(".site-block-top-search").submit();

    }

  });

  $(".submit-search-M").on("click", function (e) {

    e.preventDefault();

    var name = $("#search_M").val();

    console.log("Name", name);

    if (name == "") {

      return false;

    } else {

      $(".site-block-top-search-M").submit();

    }

  });

  var $stateSelect = $("#b_state_dp");
  var $citySelect  = $("#billing_city");

  // Cache original city options (excluding the placeholder)
  var originalCities = [];
  $citySelect.find("option").each(function () {
    var $o = $(this);
    var val = $o.attr('value');
    if (!val) return; // skip placeholder (value == "")
    originalCities.push({
      id: val,
      name: $o.text().trim(),
      state: String($o.data('state') ?? $o.attr('data-state') ?? "")
    });
  });

  // Populate cities for a given stateId (string)
 

  /* $(".site-block-top-search").on("keypress", function (e) {

    console.log("keypress", $("#search").val());

    if (e.which === 13) {

      var name = $("#search").val();

      if (name == "") {

        return false;

      } else {

        $(".site-block-top-search").submit();

      }

    }

  });*/



  $("#search").keyup(function () {

    var name = $("#search").val();

    if (name.length >= 2) {

      $("#search").addClass("search-loader");

      $.ajax({

        type: "POST",

        url: BASE_URL + "SearchController/getSearchSuggestion",

        dataType: "html",

        data: { search_key: name },

        success: function (response) {

          $("#livesearch").html(response);

          $("#search").removeClass("search-loader");

        },

      });

    } else {

      $("#livesearch").html("");

    }

  });



  $("#search_M").keyup(function () {

    var name = $("#search_M").val();

    if (name.length >= 2) {

      $("#search_M").addClass("search-loader");

      $.ajax({

        type: "POST",

        url: BASE_URL + "SearchController/getSearchSuggestion",

        dataType: "html",

        data: { search_key: name },

        success: function (response) {

          $("#livesearch_M").html(response);

          $("#search_M").removeClass("search-loader");

        },

      });

    } else {

      $("#livesearch_M").html("");

    }

  });



  $(document).on("click", "#search_icon", function () {

    var serarch_input = $("#search");

    var length = serarch_input.val().length;

    serarch_input[0].focus();

    serarch_input[0].setSelectionRange(length, length);

  });



  $(document).on("click", "#search_icon", function () {

    var serarch_input = $("#search");

    var length = serarch_input.val().length;

    serarch_input[0].focus();

    serarch_input[0].setSelectionRange(length, length);

  });

  //==================== End Search =======================



  $(".select-language .form-control").change(function (event) {

    var language_id = $(this).val();

    $.ajax({

      url: BASE_URL + "HomeController/updateCurrentLanguage",

      type: "ajax",

      method: "POST",

      dataType: "json",

      data: { language_id: language_id },

      success: function (response) {

        if (response.flag == 1) {

          location.reload();

        }

      },

    });

  });



  $(".select-currency .form-control").change(function (event) {

    var currency_id = $(this).val();

    $.ajax({

      url: BASE_URL + "HomeController/updateCurrentCurrency",

      type: "ajax",

      method: "POST",

      dataType: "json",

      data: { currency_id: currency_id },

      success: function (response) {

        if (response.flag == 1) {

          location.reload();

        }

      },

    });

  });



  $("#newsletter-subscribe-form").validate({

    ignore: ":hidden",

    rules: {

      email_subscribe: {

        required: true,

        email: true,

        validateEmail: true,

      },

    },

    errorElement: "div",

    errorLabelContainer: ".email-subscribe-error",

    beforeSend: function () {

      $("#newsletter-loader").addClass("search-loader");

    },

    submitHandler: function (form) {

      var newsData = new FormData($("#newsletter-subscribe-form")[0]);

      $("#newsletter-loader").addClass("search-loader");

      $.ajax({

        url: form.action,

        type: "ajax",

        method: form.method,

        dataType: "json",

        data: newsData,

        processData: false,

        contentType: false,

        success: function (response) {

          $("#newsletter-loader").removeClass("search-loader");



          $("#ajax-spinner").hide();

          if (response.flag == 1) {

            $("#newsletter-subscribe-form")[0].reset();



            $("#subscribe_result").fadeIn().html(response.msg);

            setTimeout(function () {

              $("#subscribe_result").fadeOut("slow");

            }, 2000);

          } else {

            $("#subscribe_result").fadeIn().html(response.msg);

            setTimeout(function () {

              $("#subscribe_result").fadeOut("slow");

            }, 2000);

          }

        },

      });

    },

  });



  /*=================================== Start Cart Price Section =======================================*/



  $(document).on("submit", "#form-coupon", function (event) {

    event.preventDefault();

    if ($(this).valid()) {

      var formData = new FormData($("#form-coupon")[0]);

      $.ajax({

        url: BASE_URL + "CartController/ApplyCouponCode",

        type: "ajax",

        method: "POST",

        dataType: "json",

        data: formData,

        processData: false,

        contentType: false,

        cache: false,

        success: function (response) {

          $("#ajax-spinner").hide();

          if (response.flag == 1) {

            swal("Success", response.msg, "success").then(() => {

              $("#li-voucher-code").hide();

              location.reload();

            });

          } else {

            swal("Oops...", response.msg, "error").then(() => {

              $("#coupon_code").focus();

            });

            return false;

          }

        },

      });

    }

  });



  $("#form-coupon").validate({

    ignore: ":hidden",

  });



  $(document).on("submit", "#form-voucher", function (event) {

    event.preventDefault();

    if ($(this).valid()) {

      var formData = new FormData($("#form-voucher")[0]);

      $.ajax({

        url: BASE_URL + "CartController/ApplyCouponCode",

        type: "ajax",

        method: "POST",

        dataType: "json",

        data: formData,

        processData: false,

        contentType: false,

        cache: false,

        success: function (response) {

          $("#ajax-spinner").hide();

          if (response.flag == 1) {

            swal("Success", response.msg, "success").then(() => {

              $("#li-discount-code").hide();

              location.reload();

            });

          } else {

            swal("Oops...", response.msg, "error").then(() => {

              $("#voucher_code").focus();

            });

            return false;

          }

        },

      });

    }

  });



  $("#form-voucher").validate({

    ignore: ":hidden",

  });



  /*=================================== End Cart Price Section ========================================*/

});



function RemoveCartItem(item_id) {

  if (item_id != "") {

    swal({

      title: "Are you sure?",

      text: "You want to remove this item.",

      icon: "warning",

      buttons: true,

      dangerMode: true,

    }).then((willDelete) => {

      if (willDelete) {

        $.ajax({

          url: BASE_URL + "CartController/removecartitem",

          type: "POST",

          dataType: "json",

          cache: false,

          headers: { "X-Requested-With": "XMLHttpRequest" },

          data: {

            item_id: item_id,

          },

          success: function (response) {

            if (response.status == 200) {

              swal("Success", response.message, "success");

              location.reload();

            } else {

              swal("Error", response.message, "error");

              return false;

            }

          },

        });

      } else {

        return false;

      }

    });

  } else {

    return false;

  }

}



function addToCart(el) {

  var product_id = $(el).attr("data-product-id");

  // var qty = $(el).attr('data-qty');

  // var qty = 1;

  $(el).attr("disabled", true);



  if (product_id != "") {

    $.ajax({

      type: "POST",

      dataType: "json",

      url: BASE_URL + "CartController/addtocart",

      data: { product_id: product_id, quantity: 1 },

      beforeSend: function () {

        $("#ajax-spinner").show();

      },

      success: function (response) {

        //console.log(response);

        $(el).attr("disabled", false);

        $("#ajax-spinner").hide();



        if (response.status == 200) {

          /*swal("Success", response.message, "success");

            setTimeout(function() {

              window.location.href=BASE_URL+"cart/";

                      }, 1000);*/

          $(".addtocart-message-" + product_id).html(

            '<span class="success-msg">Added to cart</span>'

          );

          $(".addtocart-message-" + product_id).show();

          setTimeout(function () {

            $(".addtocart-message-" + product_id).hide();

          }, 3000);



          $.ajax({

            type: "POST",

            url: BASE_URL + "CartController/updateminicart",

            dataType: "html",

            data: {},

            success: function (response) {

              $("#mini-cart-main-container").html(response);

            },

          });



          return false;

        } else {

          //swal("Error", response.message, "error");

          $(".addtocart-message-" + product_id).html(

            '<span class="error-msg">' + response.message + "</span>"

          );

          $(".addtocart-message-" + product_id).show();

          setTimeout(function () {

            $(".addtocart-message-" + product_id).hide();

          }, 3000);

          return false;

        }

      },

    });

  } else {

    return false;

  }

}



function gotoLocation(url) {

  window.location.href = url;

}



function isNumberKey(evt) {

  var charCode = evt.which ? evt.which : event.keyCode;

  if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))

    return false;

  return true;

}



function isCharKey(evt) {

  var charCode = evt.which ? evt.which : event.keyCode;

  if (

    (charCode < 97 || charCode > 122) &&

    (charCode < 65 || charCode > 90) &&

    charCode != 45 &&

    charCode != 32 &&

    charCode != 0

  )

    return false;

  return true;

}

function removeDiscount(coupon_code, coupon_type) {

  if (coupon_code != "") {

    $.ajax({

      url: BASE_URL + "CartController/removeCouponCode",

      type: "POST",

      dataType: "json",

      cache: false,

      headers: { "X-Requested-With": "XMLHttpRequest" },

      data: { coupon_code: coupon_code, coupon_type: coupon_type },

      success: function (response) {

        if (response.flag == 1) {

          swal("Success", response.msg, "success").then(() => {

            location.reload();

          });

        } else {

          swal("Error", response.msg, "error");

          return false;

        }

      },

    });

  } else {

    return false;

  }

}



function RemoveWishlistItem(wishlist_id) {

  if (wishlist_id != "") {

    $.ajax({

      url: BASE_URL + "WishlistController/removeWishlistItem",

      type: "POST",

      dataType: "json",

      cache: false,

      headers: { "X-Requested-With": "XMLHttpRequest" },

      data: {

        wishlist_id: wishlist_id,

      },

      success: function (response) {

        console.log(response);

        if (response.flag == 1) {

          swal("Success", response.msg, "success");

          location.reload();

        } else {

          swal("Error", response.msg, "error");

          return false;

        }

      },

    });

  } else {

    return false;

  }

}



$("#newsletter-subscribe-form").validate({

  ignore: ":hidden",

  rules: {

    email_subscribe: {

      required: true,

      email: true,

      validateEmail: true,

    },

  },

  errorElement: "div",

  errorLabelContainer: ".email-subscribe-error",

  beforeSend: function () {

    $("#ajax-spinner").show();

  },

  submitHandler: function (form) {

    var newsData = new FormData($("#newsletter-subscribe-form")[0]);

    $.ajax({

      url: form.action,

      type: "ajax",

      method: form.method,

      dataType: "json",

      data: newsData,

      processData: false,

      contentType: false,

      success: function (response) {

        $("#ajax-spinner").hide();

        if (response.flag == 1) {

          $("#newsletter-subscribe-form")[0].reset();



          $("#subscribe_result").fadeIn().html(response.msg);

          setTimeout(function () {

            $("#subscribe_result").fadeOut("slow");

          }, 2000);

        } else {

          $("#subscribe_result").fadeIn().html(response.msg);

          setTimeout(function () {

            $("#subscribe_result").fadeOut("slow");

          }, 2000);

        }

      },

    });

  },

});

$(document).ready(function () {

  $(".category_tab").on("click", function () {

    var position = $(this).data("position");

    var cat_id = $(this).data("cat_id");



    // alert('#tab'+position);

    $.ajax({

      type: "POST",

      dataType: "html",

      url: BASE_URL + "HomeController/homepage_cateory_block_product_data_ajax",

      data: { categoryid: cat_id },

      beforeSend: function () {

        $("#ajax-spinner").show();

        // $('#product-list-section').hide();

      },

      success: function (response) {

        $("#ajax-spinner").hide();

        $("#tab-cat-produt-list-" + position).html(response);

      },

    });

  });



  $(".category_tab_block_2").on("click", function () {

    var position = $(this).data("position");

    var cat_id = $(this).data("cat_id");



    // alert('#tab'+position);

    $.ajax({

      type: "POST",

      dataType: "html",

      url: BASE_URL + "HomeController/homepage_cateory_block_product_data_ajax",

      data: { categoryid: cat_id },

      beforeSend: function () {

        $("#ajax-spinner").show();

        // $('#product-list-section').hide();

      },

      success: function (response) {

        $("#ajax-spinner").hide();

        $("#tab-cat-produt-list-block2-" + position).html(response);

      },

    });

  });



  $("#gift-form").validate({

    ignore: ":hidden",

    beforeSend: function () {

      $("#ajax-spinner").show();

    },

    submitHandler: function (form) {

      var formData = new FormData($("#gift-form")[0]);

      $.ajax({

        url: BASE_URL + "HomeController/CheckValidityCouponCode",

        type: "ajax",

        method: form.method,

        dataType: "json",

        data: formData,

        processData: false,

        contentType: false,

        cache: false,

        success: function (response) {

          if (response.flag == 1) {

            $("#coupon-message").html("");

            $("#coupon-message").html(

              '<span class="success-msg">' + response.msg + "</span>"

            );

          } else {

            $("#coupon-message").html("");

            $("#coupon-message").html(

              '<span class="error-msg">' + response.msg + "</span>"

            );

            return false;

          }

        },

      });

    },

  });

});




// function checkPinBaseOnCountry(postal_code, country_code) {

//   // console.log(country_code);

//   switch (country_code) {
//     case "MU":

//         postalcode_regex = /^(\d{3}[A-Z]{2}\d{3})?$/i;

//         break;

//     case "GB":

//       postalcode_regex =

//         /^(GIR[ ]?0AA|((AB|AL|B|BA|BB|BD|BH|BL|BN|BR|BS|BT|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}))|BFPO[ ]?\d{1,4})$/i;

//       break;



//     case "JE":

//       postalcode_regex = /JE\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}$/i;

//       break;



//     case "GG":

//       postalcode_regex = /GY\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}$/i;

//       break;



//     case "IM":

//       postalcode_regex = /IM\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}$/i;

//       break;



//     case "US":

//       postalcode_regex = /^\d{5}([\-]?\d{4})?$/i;

//       break;



//     case "CA":

//       postalcode_regex =

//         /[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJ-NPRSTV-Z][ ]?\d[ABCEGHJ-NPRSTV-Z]\d$/i;

//       break;



//     case "DE":

//       postalcode_regex =

//         /^\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b$/;

//       break;



//     case "JP":

//       postalcode_regex = /^\d{3}-\d{4}$/;

//       break;



//     case "FR":

//       postalcode_regex = /^\d{2}[ ]?\d{3}$/;

//       break;



//     case "AU":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "IT":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "CH":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "AT":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "ES":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "NL":

//       postalcode_regex = /^\d{4}[ ]?[A-Z]{2}$/i;

//       break;



//     case "BE":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "DK":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "SE":

//       postalcode_regex = /^\d{3}[ ]?\d{2}$/;

//       break;



//     case "NO":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "BR":

//       postalcode_regex = /^\d{5}-?\d{3}$/;

//       break;



//     case "PT":

//       postalcode_regex = /^\d{4}([\-]\d{3})?$/;

//       break;



//     case "FI":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "AX":

//       postalcode_regex = /^22\d{3}$/;

//       break;



//     case "KR":

//       postalcode_regex = /^\d{3}[\-]\d{3}$/;

//       break;



//     case "CN":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "TW":

//       postalcode_regex = /^\d{3}(\d{2})?$/;

//       break;



//     case "SG":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "DZ":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "AD":

//       postalcode_regex = /AD\d{3}$/;

//       break;



//     case "AR":

//       postalcode_regex = /([A-HJ-NP-Z])?\d{4}([A-Z]{3})?$/i;

//       break;



//     case "AM":

//       postalcode_regex = /^(37)?\d{4}$/;

//       break;



//     case "AZ":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "BH":

//       postalcode_regex = /^((1[0-2]|[2-9])\d{2})?$/;

//       break;



//     case "BD":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "BB":

//       postalcode_regex = /^(BB\d{5})?$/i;

//       break;



//     case "BY":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "BM":

//       postalcode_regex = /[A-Z]{2}[ ]?[A-Z0-9]{2}$/i;

//       break;



//     case "BA":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "IO":

//       postalcode_regex = /BBND 1ZZ$/i;

//       break;



//     case "BN":

//       postalcode_regex = /[A-Z]{2}[ ]?\d{4}$/i;

//       break;



//     case "BG":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "KH":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "CV":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "CL":

//       postalcode_regex = /^\d{7}$/;

//       break;



//     case "CR":

//       postalcode_regex = /^\d{4,5}|\d{3}-\d{4}$/;

//       break;



//     case "HR":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "CY":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "CZ":

//       postalcode_regex = /^\d{3}[ ]?\d{2}$/;

//       break;



//     case "DO":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "EC":

//       postalcode_regex = /^([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?$/i;

//       break;



//     case "EG":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "EE":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "FO":

//       postalcode_regex = /^\d{3}$/;

//       break;



//     case "GE":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "GR":

//       postalcode_regex = /^\d{3}[ ]?\d{2}$/;

//       break;



//     case "GL":

//       postalcode_regex = /39\d{2}$/;

//       break;



//     case "GT":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "HT":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "HN":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "HU":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "IS":

//       postalcode_regex = /^\d{3}$/;

//       break;



//     case "ID":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "IN":

//       postalcode_regex = /^\d{3}\s?\d{3}$/;

//       break;



//     case "IL":

//       postalcode_regex = /^\d{7}$/;

//       break;



//     case "JO":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "KZ":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "KE":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "KW":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "LA":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "LV":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "LB":

//       postalcode_regex = /^(\d{4}([ ]?\d{4})?)?$/;

//       break;



//     case "LI":

//       postalcode_regex = /(948[5-9])|(949[0-7])$/;

//       break;



//     case "IE":

//       postalcode_regex =

//         /(?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W)[ -]?[0-9AC-FHKNPRTV-Y]{4}$/i;

//       break;



//     case "LT":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "LU":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "MK":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "MY":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "MV":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "MT":

//       postalcode_regex = /[A-Z]{3}[ ]?\d{2,4}$/i;

//       break;



//     case "MU":

//       postalcode_regex = /^(\d{3}[A-Z]{2}\d{3})?$/i;

//       break;



//     case "MX":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "MD":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "MC":

//       postalcode_regex = /980\d{2}$/;

//       break;



//     case "MA":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "NP":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "NZ":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "NI":

//       postalcode_regex = /^((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?$/;

//       break;



//     case "NG":

//       postalcode_regex = /^(\d{6})?$/;

//       break;



//     case "OM":

//       postalcode_regex = /(PC )?\d{3}$/;

//       break;



//     case "PK":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "PY":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "PH":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "PL":

//       postalcode_regex = /^\d{2}-\d{3}$/;

//       break;



//     case "PR":

//       postalcode_regex = /^00[679]\d{2}([ \-]\d{4})?$/;

//       break;



//     case "RO":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "RU":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "SM":

//       postalcode_regex = /4789\d$/;

//       break;



//     case "SA":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "SN":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "SK":

//       postalcode_regex = /^\d{3}[ ]?\d{2}$/;

//       break;



//     case "SI":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "ZA":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "LK":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "TJ":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "TH":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "TN":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "TR":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "TM":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "UA":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "UY":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "UZ":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "VA":

//       postalcode_regex = /00120$/;

//       break;



//     case "VE":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "ZM":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "AS":

//       postalcode_regex = /96799$/;

//       break;



//     case "CC":

//       postalcode_regex = /6799$/;

//       break;



//     case "CK":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "RS":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "ME":

//       postalcode_regex = /8\d{4}$/;

//       break;



//     case "CS":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "YU":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "CX":

//       postalcode_regex = /6798$/;

//       break;



//     case "ET":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "FK":

//       postalcode_regex = /FIQQ 1ZZ$/i;

//       break;



//     case "NF":

//       postalcode_regex = /2899$/;

//       break;



//     case "FM":

//       postalcode_regex = /^(9694[1-4])([ \-]\d{4})?$/;

//       break;



//     case "GF":

//       postalcode_regex = /9[78]3\d{2}$/;

//       break;



//     case "GN":

//       postalcode_regex = /^\d{3}$/;

//       break;



//     case "GP":

//       postalcode_regex = /9[78][01]\d{2}$/;

//       break;



//     case "GS":

//       postalcode_regex = /SIQQ 1ZZ$/i;

//       break;



//     case "GU":

//       postalcode_regex = /^969[123]\d([ \-]\d{4})?$/;

//       break;



//     case "GW":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "HM":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "IQ":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "KG":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "LR":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "LS":

//       postalcode_regex = /^\d{3}$/;

//       break;



//     case "MG":

//       postalcode_regex = /^\d{3}$/;

//       break;



//     case "MH":

//       postalcode_regex = /969[67]\d([ \-]\d{4})?$/;

//       break;



//     case "MN":

//       postalcode_regex = /^\d{6}$/;

//       break;



//     case "MP":

//       postalcode_regex = /9695[012]([ \-]\d{4})?$/;

//       break;



//     case "MQ":

//       postalcode_regex = /9[78]2\d{2}$/;

//       break;



//     case "NC":

//       postalcode_regex = /988\d{2}$/;

//       break;



//     case "NE":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "VI":

//       postalcode_regex = /^008(([0-4]\d)|(5[01]))([ \-]\d{4})?$/;

//       break;



//     case "PF":

//       postalcode_regex = /987\d{2}$/;

//       break;



//     case "PG":

//       postalcode_regex = /^\d{3}$/;

//       break;



//     case "PM":

//       postalcode_regex = /9[78]5\d{2}$/;

//       break;



//     case "PN":

//       postalcode_regex = /PCRN 1ZZ$/i;

//       break;



//     case "PW":

//       postalcode_regex = /96940$/;

//       break;



//     case "RE":

//       postalcode_regex = /9[78]4\d{2}$/;

//       break;



//     case "SH":

//       postalcode_regex = /(ASCN|STHL) 1ZZ$/i;

//       break;



//     case "SJ":

//       postalcode_regex = /^\d{4}$/;

//       break;



//     case "SO":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "SZ":

//       postalcode_regex = /[HLMS]\d{3}$/i;

//       break;



//     case "TC":

//       postalcode_regex = /TKCA 1ZZ$/i;

//       break;



//     case "WF":

//       postalcode_regex = /986\d{2}$/;

//       break;



//     case "XK":

//       postalcode_regex = /^\d{5}$/;

//       break;



//     case "YT":

//       postalcode_regex = /976\d{2}$/;

//       break;



//     default:

//       if (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4) {

//         postalcode_regex = /^\d{6}$/;

//       } else {

//         postalcode_regex = /^\d{4}$/;

//       }

//       break;

//   }

//   return postalcode_regex.test(postal_code);

// }

function checkPinBaseOnCountry(postal_code, country_code) {
    postal_code = postal_code.trim(); // remove whitespace

    let postalcode_regex;

    switch (country_code) {
        case "MU":
            postalcode_regex = /^\d{5}$/; // 5-digit postal codes
            break;

        case "FR":
            postalcode_regex = /^97.*$/; // example for France overseas
            break;

        // Add other countries as needed
        default:
            postalcode_regex = /.*/; // accept any input if no rule
            break;
    }

    return postalcode_regex.test(postal_code);
}


function changeCountryBasedOnPostalCode(pincode, country, country_classname) {

  if (pincode != "" && country != "") {

    switch (country) {

      case "FR":

        postalcode_regex = /^97.*$/;

        if (postalcode_regex.test(pincode) == true) {

          $(country_classname + ' option[value="RE"]')

            .prop("selected", true)

            .change();

        }

        break;



      default:

        postalcode_regex = /^[1,9]|[a,z].*$/;

        break;

    }

    return postalcode_regex.test(pincode);

  } else {

    return false;

  }

}



function CheckState(
  country,
  state_div,
  dp_state_div,
  state_dp,
  state,
  same_as_biling = "",
  billing_state_dp = "",
  billing_state = ""
) {
  if (country == "MU") {
    // Show state dropdown
    $(state_div).hide();
    $(dp_state_div).show();
    $(state_dp).attr("required", "true");
    $(state).removeAttr("required");
    if (same_as_biling != "") {
      $(state_dp).val($(billing_state_dp).val());
    }
    

    // Bind city fetch on state change
    $(state_dp).off("change").on("change", function () {
      var stateId = $(this).val();
      $.ajax({
        url: BASE_URL + "CheckoutController/getCities", 
        type: "POST",
        data: { state_id: stateId },
        dataType: "json",
        success: function (res) {
          if (res.status == "success") {
            $("#billing_city")
              .empty()
              .append('<option value="">Select City</option>');
            $.each(res.cities, function (i, city) {
              var cleanName = $.trim(city.city_name).replace(/[\r\n]+/g, ''); // remove newlines
              $("#billing_city").append(
                '<option value="' + city.id + '">' + cleanName + '</option>'
              );
            });
        }
        },
      });
    });

    $("#s_state_dp").on("change", function () {
      var stateId = $(this).val();
      $.ajax({
        url: BASE_URL + "CheckoutController/getCities", 
        type: "POST",
        data: { state_id: stateId },
        dataType: "json",
        success: function (res) {
          if (res.status == "success") {
            $("#shipping_city")
              .empty()
              .append('<option value="">Select City</option>');
            $.each(res.cities, function (i, city) {
              var cleanName = $.trim(city.city_name).replace(/[\r\n]+/g, ''); // remove newlines
              $("#shipping_city").append(
                '<option value="' + city.id + '">' + cleanName + '</option>'
              );
            });
          }
        },
      });
    });

  } else {
    // Default → free text state field
    $(state_div).show();
    $(dp_state_div).hide();
    $(state_dp).removeAttr("required");
    $(state).attr("required", "true");

    if (same_as_biling != "") {
      $(state).val($(billing_state).val());
    }
  }
}



function CheckAddressValMax(country, address1, address2) {

  if (country == "IN") {

    $(address1).attr("maxlength", "150");

    $(address2).attr("maxlength", "150");

  } else {

    if (country == "" && (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4)) {

      $(address1).attr("maxlength", "150");

      $(address2).attr("maxlength", "150");

    } else {

      $(address1).attr("maxlength", "35");

      $(address2).attr("maxlength", "35");

    }

  }

}



window.setCookie = function (cname, cvalue, exdays) {

  var d = new Date();

  d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);

  var expires = "expires=" + d.toUTCString();

  var expires = "expires=" + expires;

  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";

  $(".cookiesHide").hide();

};



window.getCookie = function (cname) {

  var name = cname + "=";

  var ca = document.cookie.split(";");

  for (var i = 0; i < ca.length; i++) {

    var c = ca[i];

    while (c.charAt(0) == " ") {

      c = c.substring(1);

    }

    if (c.indexOf(name) == 0) {

      return c.substring(name.length, c.length);

    }

  }

  return "";

};

$.validator.addMethod(

  "validateEmail",

  function (value, element) {

    return (

      this.optional(element) ||

      value.match(/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i)

    );

  },

  "Please enter valid email address."

);



$(document).ready(function () {

  $(".sidebar").append(

    "<span class='close-filter'><i class='fa fa-close'></i></span>"

  );

  $(".filter-op").click(function () {

    $(".sidebar").addClass("active");

  });

  $(".close-filter").click(function () {

    $(".sidebar").removeClass("active");

  });

});

