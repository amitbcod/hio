function sort_by(page, ajaxType = "", lastpagenum = "") {
  var $_GET = {};
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

  window.prevUrl = window.location.href;

  $("html, body").animate(
    {
      scrollTop: 0,
    },
    "slow"
  );

  var page = page ? page : 0;
  var p1 = $("#href_" + page).text();
  if (p1 == "" && lastpagenum != "") {
    p1 = lastpagenum;
  }
  var variantAttrIdArr = new Array();
  var variantAttrValArr = new Array();

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
  var search_term = $_GET["s"];

  if (p1 != "") {
    setGetParameter("page", p1);
  } else {
    removeParam("page");
  }

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
  console.log(search_term);
  $.ajax({
    type: "POST",
    dataType: "html",
    url: BASE_URL + "ProductsController/sort_by/" + page,
    data: {
      sort_val: sortValue,
      cat_Id: catId,
      variantId: variantAttrIdArr,
      variantVal: variantAttrValArr,
      attributeArr: attributeIdArr,
      search_terms: search_term,
      show_limit: showlimit,
      page: p1,
    },
    beforeSend: function () {
      $("#ajax-spinner").show();
      $("#product-list-section").hide();
    },
    success: function (response) {
      $("#ajax-spinner").hide();
      $("#product-list-section").show();
      $("#product-list-section").html(response);
    },
  });
}
