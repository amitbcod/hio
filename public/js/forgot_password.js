console.log("loaded");
$(document).ready(function () {
  $.validator.addMethod(
    "validateEmail",
    function (value, element) {
      return (
        this.optional(element) ||
        value.match(
          /^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i
        )
      );
    },
    "Please enter valid email address."
  );

  $("#forgot-password-form").validate({
    ignore: ":hidden",
    //ignore: ".ignore",
    rules: {
      email: {
        required: true,
        //email: true,
        validateEmail: true,
      },
    },
    beforeSend: function () {
      $("#ajax-spinner").show();
    },
    submitHandler: function (form) {
      var fd = new FormData($("#forgot-password-form")[0]);

      $.ajax({
        url: form.action,
        type: "ajax",
        method: form.method,
        dataType: "json",
        data: fd,
        processData: false,
        contentType: false,
        success: function (response) {
          console.log(response);
          $("#ajax-spinner").hide();
          if (response.flag == 1) {
            swal({
              title: "",
              icon: "success",
              text: response.msg,
              buttons: false,
            });

            setTimeout(function () {
              window.location.href = response.redirect;
            }, 1000);
          } else {
            swal({
              title: "",
              icon: "error",
              text: response.msg,
              buttons: false,
            });

            setTimeout(function () {
              //window.location.href = response.redirect;
            }, 1000);
          }
        },
      });
    },
  });
});
