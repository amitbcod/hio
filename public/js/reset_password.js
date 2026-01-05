console.log("loaded");
$(document).ready(function () {
  $.validator.addMethod(
    "mypassword",
    function (value, element) {
      return (
        this.optional(element) ||
        (value.match(/[a-zA-Z]/) &&
          value.match(/[0-9]/) &&
          value.match(/[!@#$%^&*():;?_~+=]/))
      );
    },
    "Password must contain at least one alphabetic, one numeric and one special character."
  );

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

  $("#reset-password-form").validate({
    ignore: ":hidden",
    //ignore: ".ignore",
    rules: {
      password: {
        required: true,
        minlength: 8,
        mypassword: true,
      },
      conf_password: {
        equalTo: "#password",
      },
    },
    messages: {
      password: { minlength: "Please enter 8 or more characters." },
      conf_password: "Confirm Password does not match.",
    },
    beforeSend: function () {
      $("#ajax-spinner").show();
    },
    submitHandler: function (form) {
      var fd = new FormData($("#reset-password-form")[0]);

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
              // window.location.href = response.redirect;
            }, 1000);
          }
        },
      });
    },
  });
});
