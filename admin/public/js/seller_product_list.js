var save_method; // for save method string

var table;

var a = $(window).height(); // screen height

var b = 250;

var pageHeight = a - b;

if (pageHeight < 200) {

  pageHeight = 400;

}



$(document).ready(function () {

  FilterProductDataTable();



  // Remove accented character from search input as well

  $("#DataTables_Table_WProducts input[type=search]").keyup(function () {

    var table = $("#DataTables_Table_WProducts").DataTable();

    table.search($.fn.DataTable.ext.type.search.html(this.value)).draw();

  });



  $("#from_date").datepicker({

    format: "dd-mm-yyyy",

    minDate: 0,

    autoclose: true,

    todayHighlight: true,

    onSelect: function (selected) {

      var dt = new Date(selected);

      dt.setDate(dt.getDate() + 1);

      $("#to_date").datepicker("option", "minDate", dt);

    },

  });



  $("#to_date").datepicker({

    autoclose: true,

    todayHighlight: true,

    format: "dd-mm-yyyy",

  });
  // ✅ Select/Deselect all checkboxes
  $(document).on('change', '.select-all', function () {
      let isChecked = $(this).is(':checked');
      $(this).closest('table').find('.product-checkbox').prop('checked', isChecked);
  });
  
  // ✅ If any single checkbox is unchecked → uncheck master
  $(document).on('change', '.product-checkbox', function () {
      let $table = $(this).closest('table');
      let allChecked = $table.find('.product-checkbox').length === $table.find('.product-checkbox:checked').length;
      $table.find('.select-all').prop('checked', allChecked);
  });
  
  
  $(document).on('click', '.approve-btn, .reject-btn', function () {
    let newStatus = $(this).hasClass('approve-btn') ? 'approve' : 'reject';
    let updates = [];

    $('#DataTables_Table_WProducts tbody tr').each(function () {
      let $row = $(this);
      let $checkbox = $row.find('.product-checkbox');

      if ($checkbox.is(':checked')) {
          let productId = $checkbox.val();

          // update UI
          $row.find('.status-cell').text(newStatus);

          updates.push({
              id: productId,
              status: newStatus
          });
      }
    });

    if (updates.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Products Selected',
            text: 'Please select at least one product.'
        });
        return;
    }

    $.ajax({
        url: BASE_URL + "sellerproduct/updateStatuses",
        type: "POST",
        data: { updates: updates },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Status updated successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // ✅ Reload current page
                location.reload();
            });
          } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed!',
                text: 'Failed to update statuses!'
            });
          }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Something went wrong!'
            });
        }
    });
  });


});



function FilterProductDataTable() {

  $(".filter-section").hide(); // shorthand for css("display","none")



  // Get checked supplier and image filters

  var schecked = $("input[name='supplier_filter[]']:checked").map(function () { return this.value; }).get();

  var ichecked = $("input[name='image_filter[]']:checked").map(function () { return this.value; }).get();



  var price = $("#slider11").val() || 0;

  var inventory = $("#slider12").val() || 0;

  var supplier = schecked.join(",");

  var image_filter = ichecked.join(",");

  var from_date = $("#from_date").val() || "";

  var to_date = $("#to_date").val() || "";



  // Capture approval_status from URL if present

  var urlParams = new URLSearchParams(window.location.search);

  var approval_status = urlParams.get("approval_status") || "";



  // Destroy previous table if exists

  if ($.fn.DataTable.isDataTable("#DataTables_Table_WProducts")) {

    $("#DataTables_Table_WProducts").DataTable().clear().destroy();

  }



  // Initialize DataTable

  table = $("#DataTables_Table_WProducts").DataTable({

    scrollCollapse: true,

    processing: true,

    serverSide: true,

    deferRender: true,

    buttons: ["copy", "csv", "excelHtml5", "pdf", "print"],
    columnDefs: [
        { orderable: false, targets: [0, -1] }  // Disable sorting on the first column (checkbox)
    ],

    lengthChange: true,

    info: true,

    stateSave: true,

    order: [[6, "desc"]], // order by last updated

    pageLength: 100,

    searchDelay: 500,

    lengthMenu: [

      [25, 50, 100, 200, 500, -1],

      [25, 50, 100, 200, 500, "All"],

    ],



    ajax: {

      url: BASE_URL + "sellerproduct/loadproductsajax",

      type: "POST",

      data: function (d) {

        d.price = price;

        d.inventory = inventory;

        d.supplier = supplier;

        d.from_date = from_date;

        d.to_date = to_date;

        d.image_filter = image_filter;



        // Send approval_status only if present

        if (approval_status !== "") {

          d.approval_status = approval_status;

        }

      },

      error: function (xhr, error, thrown) {

        console.error("DataTables Ajax Error:", xhr.responseText); // debug server response

      },

    },



    search: { caseInsensitive: false },



    fnDrawCallback: function (oSettings) {},



    language: {

      infoFiltered: "",

      search: "",

      searchPlaceholder: "Search",

      paginate: {

        next: '<i class="fas fa-angle-right"></i>',

        previous: '<i class="fas fa-angle-left"></i>',

      },

    },

    initComplete: function () {},

  });

}

