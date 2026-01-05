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

  $("#signup-form").validate({
    rules: {
      otp_verification: { required: true },
      email: {
        validateEmail: true,
        required: true,
      },
      password: {
        minlength: 8,
        mypassword: true,
      },
      conf_password: { equalTo: "#password" },
      agree_chk: "required",
    },
    messages: {
      password: { minlength: "Please enter 8 or more characters." },
      conf_password: "Confirm Password does not match.",
    },
    errorElement: "em",
    errorPlacement: function (error, element) {
      // Add the `invalid-feedback` class to the error element
      error.addClass("invalid-feedback");
      if (element.prop("type") === "checkbox") {
        //error.insertAfter(element.next("br.agrbr"));
        //error.insertBefore(element.parent("div"));
        error.insertAfter($(element).parents("div.chkbx"));
      } else if (element.prop("type") === "tel") {
        error.insertAfter(element.parents("div.iti"));
      } else {
        error.insertAfter(element.next(".pmd-textfield-focused"));
      }
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    },
    submitHandler: function (form) {
      // $("#ajax-spinner").show();

      // alert('ffff');exit;

      var fd = new FormData($("#signup-form")[0]);
      console.log(fd);
      $.ajax({
        url: form.action,
        type: "ajax",
        method: form.method,
        dataType: "json",
        data: fd,
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
            });

            setTimeout(function () {
              window.location.href = response.redirect;
            }, 1000);
          } else {
            if (CAPTCHA_CHECK_FLAG == "yes") {
              grecaptcha
                .execute(GC_SITE_KEY_V3, { action: "register" })
                .then(function (e) {
                  document.getElementById("g-recaptcha-response").value = e;
                });
            }
            grecaptcha.reset();
            swal({
              title: "",
              icon: "error",
              text: response.msg,
              buttons: false,
            });
          }
        },
      });
    },
  });
});
