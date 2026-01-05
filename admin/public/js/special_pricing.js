$(document).ready(function () {
  $("#ckbCheckAllSP").click(function () {
    $('input[name="chk_sp[]"]').prop("checked", $(this).prop("checked"));
  });

  $("#deleteall").click(function () {
    if ($("input[type=checkbox]:checked").length == 0) {
      $("#deleteALLModal").modal("hide");
      swal({
        title: "",
        text: "Please Select items to delete.",
        icon: "error",
      });
      return false;
    }
  });

  $("#product_id").change(function () {
    var product_id = $(this).val();
    // console.log(product_id);

    $.ajax({
      type: "POST",
      url: BASE_URL + "WebshopController/get_webshop_price",
      data: { product_id: product_id },
      datatype: "json",
      success: function (response) {
        var obj = $.parseJSON(response);
        // console.log(obj.webshop_price.webshop_price);
        $("#webshop_price").val(obj.webshop_price.webshop_price);
      },
    });
  });

  $("#special_price").change(function () {
    if (parseFloat($("#special_price").val()) > $("#webshop_price").val()) {
      swal(
        {
          title: "",
          icon: "error",
          text: "Special Price Cannot be Greater webshop price",
          buttons: false,
        },
        function () {
          location.reload();
        }
      );
    }
  });

  $("#to_date").change(function () {
    if ($("#to_date").val() <= $("#from_date").val()) {
      swal(
        {
          title: "",
          icon: "error",
          text: "To date should be Greater than From Date",
          buttons: false,
        },
        function () {
          location.reload();
        }
      );
    }
  });

});

$("#special_pricing_form").on("submit", function (e) {
  e.preventDefault();

  if ($(this).valid()) {
    var formData = new FormData($("#special_pricing_form")[0]);
    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,

      success: function (response) {
        // console.log(response);
        if (response.msg == "Success") {
          swal(
            {
              title: "",
              icon: "success",
              text: response.msg,
              buttons: false,
            },
            function () {
              window.location = response.redirect;
            }
          );
        } else {
          console.log(response.msg);
          swal(
            {
              title: "",
              html: true,
              icon: "error",
              text: response.msg,
              buttons: false,
            },
            function () {
              location.reload();
            }
          );
        }
      },
    });
  }
});

$(document).on("click", ".trash", function (e) {
  var id = $(this).attr("data-id");
  $("#row_id").val(id);
  console.log(id);
});

$(document).on("click", ".showall", function (e) {
  $("#showall").prop("disabled", true);
  $("#table_content").removeClass("d-none");
  $("#deleteall").removeClass("d-none");
  $("#hideall").removeClass("d-none");
  $("#showall").addClass("d-none");
  SpecialPricingDataTable();
});
$(document).on("click", ".hideall", function (e) {
  $("#showall").prop("disabled", false);
  $("#hideall").addClass("d-none");
  $("#table_content").addClass("d-none");
  $("#deleteall").addClass("d-none");
  $("#showall").removeClass("d-none");
  //SpecialPricingDataTable();
});

$("#deleteModalForRowForm").validate({
  ignore: ":hidden",
  //ignore: ".ignore",
  submitHandler: function (form) {
    var formData = new FormData($("#deleteModalForRowForm")[0]);
    $.ajax({
      url: form.action,
      type: "ajax",
      method: form.method,
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.flag == 1) {
          swal(
            { title: "", text: response.msg, button: false, icon: "success" },
            function () {
              location.reload();
            }
          );
        } else {
          swal({ title: "", text: response.msg, button: false, icon: "error" });
          return false;
        }
      },
    });
  },
});

function OpenBulkUploadPopup() {
  $.ajax({
    type: "POST",
    dataType: "html",
    url: BASE_URL + "WebshopController/openbulkuploadpopup",
    data: {},
    async: false,
    complete: function () {},
    beforeSend: function () {
      $("#ajax-spinner").show();
    },
    success: function (response) {
      $("#ajax-spinner").hide();
      if (response != "error") {
        $("#FBCUserCommonModal").modal();
        $("#modal-content").html(response);
      } else {
      }
    },
  });
}

function ImportSpecialPricing() {
  var upload_csv_file = $("#upload_csv_file").val();
  if (upload_csv_file == "") {
    swal("Error", "Please upload file", "error");
    return false;
  } else {
    var fd = new FormData();
    fd.append("upload_csv_file", $("#upload_csv_file")[0].files[0]); // since this is your file input

    $.ajax({
      url: BASE_URL + "WebshopController/import_special_pricing",
      type: "post",
      dataType: "html",
      contentType: false,
      cache: false,
      processData: false,
      data: fd,
      success: function (response) {
        //console.log(response);return false;

        var obj = JSON.parse(response);
        if (obj.status == 200) {
          $("#FBCUserCommonModal").modal("hide");
          swal("Success", obj.message, "success");
        } else {
          swal("Error", obj.message, "error");
          return false;
        }
      },
      error: function () {
        swal("Error", "Something went wrong", "error");
        return false;
      },
    });
  }
}

function CheckCSVData() {
  var upload_csv_file = $("#upload_csv_file").val();
  if (upload_csv_file == "") {
    swal("Error", "Please upload file", "error");
    return false;
  } else {
    var fd = new FormData();
    fd.append("upload_csv_file", $("#upload_csv_file")[0].files[0]); // since this is your file input

    $.ajax({
      url: BASE_URL + "WebshopController/checkcsvdata",
      type: "post",
      dataType: "html",
      contentType: false,
      cache: false,
      processData: false,
      data: fd,
      success: function (response) {
        var obj = JSON.parse(response);
        if (obj.status == 200) {
          swal("Success", obj.message, "success");
          $("#bulk_upload").removeClass("d-none");
          $("#check_data").addClass("d-none");
        } else {
          swal("Error", obj.message, "error");
          $("#bulk_upload").addClass("d-none");
          $("#check_data").removeClass("d-none");
          $("#csv_error").html(response.message);
          return false;
        }
      },
      error: function () {
        swal("Error", "Something went wrong", "error");
        return false;
      },
    });
  }
}

$(document).on("change", "#upload_csv_file", function () {
  //Do something
  $("#check_data").removeClass("d-none");
  $("#bulk_upload").addClass("d-none");
  $("#check_data").removeAttr("disabled");
});

function OpenBulkSelectCategory(type = "") {
  if (type != "") {
    // console.log(type);
    $.ajax({
      type: "POST",
      dataType: "html",
      url: BASE_URL + "WebshopController/openbulkselectcategory",
      data: { type: type },
      async: false,
      complete: function () {},
      beforeSend: function () {
        $("#ajax-spinner").show();
      },
      success: function (response) {
        $("#ajax-spinner").hide();
        if (response != "error") {
          $("#FBCUserCommonModal").modal();
          $("#modal-content").html(response);
        } else {
        }
      },
    });
  } else {
    return false;
  }
}

function DownloadProductCSV() {
  window.location.href = BASE_URL + "WebshopController/downloadproductcsv";
  $("#FBCUserCommonModal").modal("hide");
}

function DownloadAllProductCSV() {
  window.location.href = BASE_URL + "WebshopController/download_all_ProductCSV";
  $("#FBCUserCommonModal").modal("hide");
}

function OpenBulkDeletePopup(e) {
  e.preventDefault();
  var formData = new FormData($("#sp_listing_Form")[0]);
  $.ajax({
    type: "POST",
    dataType: "json",
    url: BASE_URL + "WebshopController/delete_all_special_pricing",
    data: formData,
    processData: false,
    contentType: false,
    async: false,
    complete: function () {
      $("#ajax-spinner").hide();
    },
    beforeSend: function () {
      $("#ajax-spinner").show();
    },
    success: function (response) {
      if (response.flag == 1) {
        swal(
          { title: "", text: response.msg, button: false, icon: "success" },
          function () {
            location.reload();
          }
        );
      } else {
        $("#deleteALLModal").modal("hide");
        swal({ title: "", text: response.msg, button: false, icon: "error" });
        return false;
      }
    },
  });
}

function SpecialPricingDataTable() {
  $("#Datatable_special_pricing").dataTable().fnDestroy();
  //datatables
  table = $("#Datatable_special_pricing").DataTable({
    scrollCollapse: true,
    serverSide: true,
    processing: true,
    order: [],
    info: false,
    iDisplayLength: 200,
    pageLength: 200,
    bLengthChange: true,
    searchDelay: 2000,
    lengthMenu: [
      [200, 400, 600, -1],
      [200, 400, 600, "All"],
    ],

    language: {
      paginate: {
        next: '<i class="fas fa-angle-right"></i>',
        previous: '<i class="fas fa-angle-left"></i>',
      },
      search: "",
      searchPlaceholder: "Search",
      infoFiltered: "",
    },
    columnDefs: [
      {
        targets: [0, 3, 5],
        orderable: false,
      },
    ],

    // Load data for the table's content from an Ajax source
    ajax: {
      url: BASE_URL + "WebshopController/loadspecialpricingajax",
      type: "POST",
      data: {},
    },
    search: {
      caseInsensitive: false,
    },
    initComplete: function () {},
  });
}
