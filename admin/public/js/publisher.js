$("#publisherForm").validate({
  submitHandler: function (form) {
    var formData = new FormData($("#publisherForm")[0]);
    $.ajax({
      url: form.action,
      type: "ajax",
      method: form.method,
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response == "success") {
          swal(
            {
              title: "Success",
              text: "Merchant Add successfully!",
              type: "success",
            },
            function () {
              location.href = BASE_URL + "publishers";
            }
          );
        } else {
          swal({
            title: "",
            text: response.msg,
            button: false,
            icon: "success",
          });
          return false;
        }
      },
      error: function (response) {
        console.log(response.responseText);
        return false;
      },
    });
    return false;
  },
});

$(document).ready(function () {
  $("#update_publisher").click(function (e) {
    e.preventDefault();
    var formData = new FormData($("#EditpublisherForm")[0]);
    $.ajax({
      type: "POST",
      url: BASE_URL + "PublisherController/submitPublisher",
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function () {
        $("#ajax-spinner").show();
      },
      success: function (response) {
        window.setTimeout(function () {
          $("#ajax-spinner").hide();
        }, 1500);
        $("#update_publisher").attr("disabled", "disabled");
        if (response == "success") {
          swal(
            {
              title: "Success",
              text: "Merchant Update successfully!",
              type: "success",
            },
            function () {
              location.href = BASE_URL + "publishers";
            }
          );
          // swal('Success','Publisher Update successfully!','success');
          //     window.setTimeout( function() {
          //     window.location.href="/admin/publishers";
          //     }, 1500);
        } else {
          swal({
            title: "",
            text: response.msg,
            button: false,
            icon: "success",
          });
          return false;
        }
      },
    });
  });
});

$(document).ready(function () {
  $("#passwordCheck").click(function () {
    var disabled = $("#password").prop("disabled");
    if (disabled) {
      $("#password").prop("disabled", false); // if disabled, enable
      $("#password").val("");
    } else {
      $("#password").prop("disabled", true); // if enabled, disable
      $("#password").val("password");
    }
  });
});

function ConfirmPublisherDelete(id) {
  if (id != "") {
    swal(
      {
        title: "Are you sure? ",
        text: "",
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
          DeleteCategory(id);
        } else {
          swal.close();
        }
      }
    );
  }
}

function DeleteCategory(id) {
  if (id != "") {
    $.ajax({
      type: "POST",
      dataType: "html",
      url: BASE_URL + "PublisherController/deletePublisher/",
      data: { id: id },
      beforeSend: function () {
        $("#ajax-spinner").show();
      },
      success: function (response) {
        $("#ajax-spinner").hide();
        if (response == "success") {
          swal("Success", "Merchant deleted successfully!", "success");
          window.setTimeout(function () {
            window.location.reload();
          }, 1500);
        } else {
          return false;
        }
      },
    });
  } else {
    return false;
  }
}
