$(document).ready(function () {
  window.prevUrl = window.location.href;
  var $_GET = {};
  if (attr_options_name == "English ") {
    // alert('hi');
    $("#language_dropdown").hide();
  } else {
    // alert('h2');
    $("#language_dropdown").show();
  }
  if (attr_options_name == "English ") {
    // alert('hi');
    $("#language_heading").hide();
  } else {
    // alert('h2');
    $("#language_heading").show();
  }

  if (language_attr_options_name == "English ") {
    // alert('hi');
    $("#language_dropdown_new").hide();
  } else {
    // alert('h2');
    $("#language_dropdown_new").show();
  }
  if (document.location.toString().indexOf("?") !== -1) {
    var query = document.location
      .toString()
      // get the query string
      .replace(/^.*?\?/, "")
      // and remove any existing hash string (thanks, @vrijdenker)
      .replace(/#.*$/, "")
      .split("&");

    for (var i = 0, l = query.length; i < l; i++) {
      var aux = decodeURIComponent(query[i]).split("=");
      $_GET[aux[0]] = aux[1];
    }
  }
  $("#sort-by,#show-limit,#language").change(function () {
    window.prevUrl = window.location.href;
    // console.log("In product.js");
    if (THEMENAME == "theme2") {
      $("html, body").animate(
        { scrollTop: $(".breadcrum").offset().top },
        "slow"
      );
    } else if (THEMENAME == "theme_zumbawear") {
      $("html, body").animate(
        { scrollTop: $(".product-list-section").offset().top },
        "slow"
      );
    } else {
      $("html, body").animate({ scrollTop: 0 }, "slow");
    }

    // var sortValue = $(this).val();
    var sortValue = $("#sort-by option:selected").val();
    var language = $("#language option:selected").val();

    var catId = $("#category-id").val();
    var current_viewmode = $("#current_viewmode").val();
    var showlimit = $("#show-limit option:selected").val();
    var search_term = $("#search-term").val();
    var pageSortType = $("#page_sort_type").val();

    var chkArray = new Array();
    $('input[name="subscription[]"]:checked').each(function () {
      chkArray.push($(this).val());
    });

    var priceArray = new Array();
    var minimum_range = $("#min_price").val();
    var maximum_range = $("#max_price").val();
    priceArray.push(minimum_range);
    priceArray.push(maximum_range);

    var variantAttrIdArr = new Array();
    var variantAttrValArr = new Array();

    $('input[name="variant_chk_base[]"]:checked').each(function () {
      var attr_v = $(this).val();
      var attr_v1 = attr_v.replace(/'/g, "");
      var reg = attr_v.split(/[ ,]+/);
      //console.log(reg.length);
      var varnt_id = $("#base_color_variant_id_value").val();
      reg.forEach((element) => {
        variantAttrValArr.push(element);
        variantAttrIdArr.push(varnt_id);
      });
    });

    $('input[name="variant_chk[]"]:checked').each(function () {
      var attr_v = $(this).val();
      var varnt_id = $("#variant_attr_" + attr_v).val();
      variantAttrIdArr.push(varnt_id);
      variantAttrValArr.push(attr_v);
    });

    var attributeIdArr = new Array();
    $('input[name="attribute_chk[]"]:checked').each(function () {
      var attr_id = $(this).val();
      attributeIdArr.push(attr_id);
    });

    var LanguageArr = new Array();
    LanguageArr.push(language);

    if (language != "") {
      attributeIdArr.push(language);
    }

    if (sortValue != "") {
      setGetParameter("page", 1);

      if (showlimit != "") {
        setGetParameter("limit", showlimit);
      } else {
        removeParam("limit");
      }

      if (LanguageArr != "" && LanguageArr.length > 0) {
        setGetParameter("language", LanguageArr.toString());
      } else {
        removeParam("language");
      }

      if (sortValue != "") {
        setGetParameter("sort", sortValue);
      } else {
        removeParam("sort");
      }

      if (current_viewmode != "") {
        setGetParameter("viewmode", current_viewmode);
      } else {
        removeParam("viewmode");
      }

      if (chkArray != "" && chkArray.length > 0) {
        setGetParameter("subscription", chkArray.toString());
      } else {
        removeParam("subscription");
      }

      if (variantAttrIdArr != "" && variantAttrIdArr.length > 0) {
        setGetParameter("variantId", variantAttrIdArr.toString());
      } else {
        removeParam("variantId");
      }

      if (variantAttrValArr != "" && variantAttrValArr.length > 0) {
        setGetParameter("variantVal", variantAttrValArr.toString());
      } else {
        removeParam("variantVal");
      }

      if (attributeIdArr != "" && attributeIdArr.length > 0) {
        setGetParameter("attribute", attributeIdArr.toString());
      } else {
        removeParam("attribute");
      }
      if (typeof $_GET["s"] !== "undefined") {
        // console.log("Search term", $_GET["s"]);
        var search_term = $_GET["s"];
      }

      $.ajax({
        type: "POST",
        url: BASE_URL + "ProductsController/sort_by",
        dataType: "html",
        data: {
          sort_val: sortValue,
          cat_Id: catId,
          subscription: chkArray,
          price_range: priceArray,
          variantId: variantAttrIdArr,
          variantVal: variantAttrValArr,
          attributeArr: attributeIdArr,
          current_viewmode: current_viewmode,
          search_terms: search_term,
          show_limit: showlimit,
          page_sort_type: pageSortType,
          language_arr: LanguageArr,
        },
        beforeSend: function () {
          $("#ajax-spinner").show();
        },
        success: function (response) {
          $("#ajax-spinner").hide();
          //$("#processing").hide();
          $(".product-list-section").html(response);
        },
      });
    }
  });

  $(".grid-view").click(function () {
    $(".product-grid-listing-view").show(10);
    setGetParameter("viewmode", "grid-view");
    $(".product-listing-view").hide();
    $("#current_viewmode").val("grid-view");
    $(".grid-view").addClass("active");
    $(".list-view").removeClass("active");
  });

  $(".list-view").click(function () {
    $(".product-listing-view").show(10);
    setGetParameter("viewmode", "list-view");
    $(".product-grid-listing-view").hide();
    $("#current_viewmode").val("list-view");
    $(".list-view").addClass("active");
    $(".grid-view").removeClass("active");
  });

  //accordion category js start
  $(".panel").hide();
  $(".accordion").click(function () {
    $(this).next().slideToggle();
  });
  var acc = document.getElementsByClassName("accordion");
  var i;

  for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function () {
      this.classList.toggle("active");
      var panel = this.nextElementSibling;
    });
  }
  //accordion category js close

  $(".chk-gender").click(function () {
    if (THEMENAME == "theme2") {
      $("html, body").animate(
        { scrollTop: $(".breadcrum").offset().top },
        "slow"
      );
    } else if (THEMENAME == "theme_zumbawear") {
      $("html, body").animate(
        { scrollTop: $(".product-list-section").offset().top },
        "slow"
      );
    } else {
      $("html, body").animate({ scrollTop: 0 }, "slow");
    }

    var current_viewmode = $("#current_viewmode").val();

    var chkArray = new Array();
    $('input[name="subscription[]"]:checked').each(function () {
      chkArray.push($(this).val());
    });

    var priceArray = new Array();
    var minimum_range = $("#min_price").val();
    var maximum_range = $("#max_price").val();
    priceArray.push(minimum_range);
    priceArray.push(maximum_range);

    var variantAttrIdArr = new Array();
    var variantAttrValArr = new Array();

    $('input[name="variant_chk_base[]"]:checked').each(function () {
      var attr_v = $(this).val();
      var attr_v1 = attr_v.replace(/'/g, "");
      var reg = attr_v.split(/[ ,]+/);
      //console.log(reg.length);
      var varnt_id = $("#base_color_variant_id_value").val();
      reg.forEach((element) => {
        variantAttrValArr.push(element);
        variantAttrIdArr.push(varnt_id);
      });
    });

    $('input[name="variant_chk[]"]:checked').each(function () {
      var attr_v = $(this).val();
      var varnt_id = $("#variant_attr_" + attr_v).val();
      variantAttrIdArr.push(varnt_id);
      variantAttrValArr.push(attr_v);
    });

    var attributeIdArr = new Array();
    $('input[name="attribute_chk[]"]:checked').each(function () {
      var attr_id = $(this).val();
      attributeIdArr.push(attr_id);
    });

    var sortValue = $("#sort-by option:selected").val();
    var showlimit = $("#show-limit option:selected").val();
    var catId = $("#category-id").val();
    var search_term = $("#search-term").val();
    var pageSortType = $("#page_sort_type").val();

    window.prevUrl = window.location.href;

    setGetParameter("page", 1);

    if (showlimit != "") {
      setGetParameter("limit", showlimit);
    } else {
      removeParam("limit");
    }

    if (sortValue != "") {
      setGetParameter("sort", sortValue);
    } else {
      removeParam("sort");
    }

    if (current_viewmode != "") {
      setGetParameter("viewmode", current_viewmode);
    } else {
      removeParam("viewmode");
    }

    if (chkArray != "" && chkArray.length > 0) {
      setGetParameter("subscription", chkArray.toString());
    } else {
      removeParam("subscription");
    }

    if (variantAttrIdArr != "" && variantAttrIdArr.length > 0) {
      setGetParameter("variantId", variantAttrIdArr.toString());
    } else {
      removeParam("variantId");
    }

    if (variantAttrValArr != "" && variantAttrValArr.length > 0) {
      setGetParameter("variantVal", variantAttrValArr.toString());
    } else {
      removeParam("variantVal");
    }

    if (attributeIdArr != "" && attributeIdArr.length > 0) {
      setGetParameter("attribute", attributeIdArr.toString());
    } else {
      removeParam("attribute");
    }

    $.ajax({
      type: "POST",
      url: BASE_URL + "ProductsController/sort_by",
      dataType: "html",
      data: {
        sort_val: sortValue,
        cat_Id: catId,
        subscription: chkArray,
        price_range: priceArray,
        variantId: variantAttrIdArr,
        variantVal: variantAttrValArr,
        attributeArr: attributeIdArr,
        current_viewmode: current_viewmode,
        show_limit: showlimit,
        search_terms: search_term,
        page_sort_type: pageSortType,
      },
      beforeSend: function () {
        $("#ajax-spinner").show();
      },
      success: function (response) {
        $("#ajax-spinner").hide();
        // $("#processing").hide();
        $(".product-list-section").html(response);
      },
    });
  });

  $("#review-rating-form").validate({
    ignore: ":hidden",
    rules: {
      rating: {
        required: true,
      },
      review_content: {
        required: true,
      },
    },
    // messages: {
    // rating: { "minlength": "Please enter 8 or more characters." },
    // },
    beforeSend: function () {
      $("#ajax-spinner").show();
    },
    submitHandler: function (form) {
      var prod_id = $("#product_id").val();
      var review_data = new FormData($("#review-rating-form")[0]);
      review_data.append("product_id", prod_id);
      $.ajax({
        url: form.action,
        type: "ajax",
        method: form.method,
        dataType: "json",
        data: review_data,
        processData: false,
        contentType: false,
        success: function (response) {
          $("#ajax-spinner").hide();
          if (response.flag == 1) {
            swal({
              title: "",
              icon: "success",
              text: response.msg,
              buttons: false,
              timer: 2000,
            });

            $("#review-rating-form")[0].reset();

            $(".review-container-main").html(response.reviewData);
            // setTimeout(function() {
            // window.location.href = response.redirect;

            // }, 1000);
          } else if (response.flag == 2) {
            swal({
              title: "",
              icon: "error",
              text: response.msg,
              buttons: false,
              timer: 2000,
            });
          } else {
            swal({
              title: "",
              icon: "error",
              text: response.msg,
              buttons: false,
              timer: 2000,
            });

            setTimeout(function () {
              window.location.href = response.redirect;
            }, 1000);
          }
        },
      });
    },
  });

  $(".chk-variant").click(function () {
    if (THEMENAME == "theme2") {
      $("html, body").animate(
        { scrollTop: $(".breadcrum").offset().top },
        "slow"
      );
    } else if (THEMENAME == "theme_zumbawear") {
      $("html, body").animate(
        { scrollTop: $(".product-list-section").offset().top },
        "slow"
      );
    } else {
      $("html, body").animate({ scrollTop: 0 }, "slow");
    }

    var variantAttrIdArr = new Array();
    var variantAttrValArr = new Array();

    $('input[name="variant_chk_base[]"]:checked').each(function () {
      var attr_v = $(this).val();
      var attr_v1 = attr_v.replace(/'/g, "");
      var reg = attr_v.split(/[ ,]+/);
      //console.log(reg.length);
      var varnt_id = $("#base_color_variant_id_value").val();
      reg.forEach((element) => {
        variantAttrValArr.push(element);
        variantAttrIdArr.push(varnt_id);
      });
    });

    $('input[name="variant_chk[]"]:checked').each(function () {
      var attr_v = $(this).val();
      var varnt_id = $("#variant_attr_" + attr_v).val();
      variantAttrIdArr.push(varnt_id);
      variantAttrValArr.push(attr_v);
    });

    var attributeIdArr = new Array();
    $('input[name="attribute_chk[]"]:checked').each(function () {
      var attr_id = $(this).val();
      attributeIdArr.push(attr_id);
    });

    var chkArray = new Array();
    $('input[name="subscription[]"]:checked').each(function () {
      chkArray.push($(this).val());
    });

    var priceArray = new Array();
    var minimum_range = $("#min_price").val();
    var maximum_range = $("#max_price").val();
    priceArray.push(minimum_range);
    priceArray.push(maximum_range);

    var sortValue = $("#sort-by option:selected").val();
    var showlimit = $("#show-limit option:selected").val();
    var search_term = $("#search-term").val();
    var pageSortType = $("#page_sort_type").val();
    var catId = $("#category-id").val();
    var current_viewmode = $("#current_viewmode").val();

    window.prevUrl = window.location.href;

    setGetParameter("page", 1);

    if (showlimit != "") {
      setGetParameter("limit", showlimit);
    } else {
      removeParam("limit");
    }

    if (sortValue != "") {
      setGetParameter("sort", sortValue);
    } else {
      removeParam("sort");
    }

    if (current_viewmode != "") {
      setGetParameter("viewmode", current_viewmode);
    } else {
      removeParam("viewmode");
    }

    if (chkArray != "" && chkArray.length > 0) {
      setGetParameter("subscription", chkArray.toString());
    } else {
      removeParam("subscription");
    }

    if (variantAttrIdArr != "" && variantAttrIdArr.length > 0) {
      setGetParameter("variantId", variantAttrIdArr.toString());
    } else {
      removeParam("variantId");
    }

    if (variantAttrValArr != "" && variantAttrValArr.length > 0) {
      setGetParameter("variantVal", variantAttrValArr.toString());
    } else {
      removeParam("variantVal");
    }

    if (attributeIdArr != "" && attributeIdArr.length > 0) {
      setGetParameter("attribute", attributeIdArr.toString());
    } else {
      removeParam("attribute");
    }

    $.ajax({
      type: "POST",
      url: BASE_URL + "ProductsController/sort_by",
      dataType: "html",
      data: {
        sort_val: sortValue,
        cat_Id: catId,
        subscription: chkArray,
        price_range: priceArray,
        variantId: variantAttrIdArr,
        variantVal: variantAttrValArr,
        attributeArr: attributeIdArr,
        current_viewmode: current_viewmode,
        show_limit: showlimit,
        search_terms: search_term,
        page_sort_type: pageSortType,
      },
      beforeSend: function () {
        $("#ajax-spinner").show();
      },
      success: function (response) {
        $("#ajax-spinner").hide();
        $(".product-list-section").html(response);
      },
    });
  });
});

function setGetParameter(paramName, paramValue) {
  var url = window.location.href;
  var hash = location.hash;
  url = url.replace(hash, "");
  if (url.indexOf(paramName + "=") >= 0) {
    var prefix = url.substring(0, url.indexOf(paramName + "="));
    var suffix = url.substring(url.indexOf(paramName + "="));
    suffix = suffix.substring(suffix.indexOf("=") + 1);
    suffix =
      suffix.indexOf("&") >= 0 ? suffix.substring(suffix.indexOf("&")) : "";
    url = prefix + paramName + "=" + paramValue + suffix;
  } else {
    if (url.indexOf("?") < 0) url += "?" + paramName + "=" + paramValue;
    else url += "&" + paramName + "=" + paramValue;
  }
  //window.location.href = url + hash;
  window.history.replaceState(null, null, url + hash);
}

function getUrlParameter(name, url) {
  if (!url) {
    url = window.location.href;
  }
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
  var results = regex.exec(url);
  return results === null
    ? ""
    : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function removeParam(parameter) {
  var url = document.location.href;
  var urlparts = url.split("?");

  if (urlparts.length >= 2) {
    var urlBase = urlparts.shift();
    var queryString = urlparts.join("?");

    var prefix = encodeURIComponent(parameter) + "=";
    var pars = queryString.split(/[&;]/g);
    for (var i = pars.length; i-- > 0; )
      if (pars[i].lastIndexOf(prefix, 0) !== -1) pars.splice(i, 1);
    url = urlBase + "?" + pars.join("&");
    window.history.pushState("", document.title, url); // added this line to push the new url directly to url bar .
  }
  return url;
}

function loadMoreReview(offset) {
  //var offset = $(this).attr('id');
  var prodId = $("#product_id").val();
  $(".show_more").hide();
  $(".loding").show();
  $.ajax({
    type: "POST",
    url: BASE_URL + "ProductReviewController/review_loadmore",
    dataType: "html",
    data: { offset: offset, product_id: prodId },
    success: function (response) {
      $(".show_more_main_" + offset).remove();
      $(".reviewlist-section").append(response);
    },
  });
}

function addToWishlist(prod_id) {
  if (prod_id != "") {
    $.ajax({
      type: "POST",
      dataType: "json",
      url: BASE_URL + "WishlistController/addToWishList",
      data: { product_id: prod_id },
      beforeSend: function () {
        $("#ajax-spinner").show();
      },
      success: function (response) {
        $("#ajax-spinner").hide();
        if (response.flag == 1) {
          if (THEMENAME == "theme2") {
            $(".heart_wishlist_img_" + prod_id).addClass("active");
          } else if (THEMENAME == "theme_zumbawear") {
            $(".heart_wishlist_img_" + prod_id).addClass("active");
            $(".heart_wishlist_img_" + prod_id)
              .find("i")
              .toggleClass("far fa");
          } else {
            $("#heart_wishlist_img_" + prod_id).prop(
              "src",
              TEMP_SKIN_IMG + "/heart-wishlist-active.png"
            );
          }
        } else if (response.flag == 2) {
          swal({
            title: "",
            icon: "error",
            text: response.msg,
            buttons: false,
            //timer:3000
          });

          setTimeout(function () {
            window.location.href = response.redirect;
          }, 1000);
        } else {
          swal({
            title: "",
            icon: "info",
            text: response.msg,
            buttons: false,
            //timer:3000
          });
        }
      },
    });
  }
}

function getMediaVariant(attr_option_value, attr_id) {
  var media_variant_id = $("#media_variant_id").val();
  var product_id = $("#product_id").val();
  if (media_variant_id == attr_id && media_variant_id != 0) {
    $.ajax({
      url: BASE_URL + "ProductsController/getMediaVariantProduct",
      type: "POST",
      dataType: "json",
      data: {
        product_id: product_id,
        media_variant_id: media_variant_id,
        attr_option_value: attr_option_value,
      },
      success: function (response) {
        $("#product-image-section").html(response.mediaGallery);
      },
    });
  }
}
