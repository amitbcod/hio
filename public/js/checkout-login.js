$(document).ready(function () {

  console.log("sssssssssssssss");

  if (CAPTCHA_CHECK_FLAG == "yes") {

    grecaptcha.ready(function () {

      grecaptcha

        .execute(GC_SITE_KEY_V3, { action: "checkoutLogin" })

        .then(function (e) {

          document.getElementById("g-recaptcha-response-login").value = e;

          document.getElementById("g-recaptcha-response").value = e;

        });

    });

  }



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



  $("#sigin-form").validate({

    ignore: ":hidden",

    rules: {

      email: {

        validateEmail: true,

      },

      login_password: {

        required: true,

      },

      hiddenRecaptcha: {

        required: function () {

          if (grecaptcha.getResponse() == "") {

            return true;

          } else {

            return false;

          }

        },

      },

    },

    beforeSend: function () {

      $("#ajax-spinner").show();

    },

    submitHandler: function (form) {

      var fd = new FormData($("#sigin-form")[0]);

      console.log("sssssssssssssss", fd);

      $.ajax({

        url: form.action,

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
            }).then(() => {
              window.location.href = response.redirect;
            });
          } else {

            if (CAPTCHA_CHECK_FLAG == "yes") {

              grecaptcha

                .execute(GC_SITE_KEY_V3, { action: "checkoutLogin" })

                .then(function (e) {

                  document.getElementById("g-recaptcha-response-login").value =

                    e;

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



            return false;

          }

        },

      });

    },

  });



  $("#signup-form").validate({

    ignore: ":hidden",

    //ignore: ".ignore",

    rules: {

      first_name: {

        required: true,

      },

      last_name: {

        required: true,

      },

      email: {

        validateEmail: true,

      },

      password: {

        required: true,

        minlength: 8,

        mypassword: true,

      },

      conf_password: {

        required: true,

        minlength: 8,

        equalTo: "#password",

      },

      agree_chk: {

        required: true,

      },

      hiddenRecaptcha: {

        required: function () {

          if (grecaptcha.getResponse() == "") {

            return true;

          } else {

            return false;

          }

        },

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

      //$('#signup-btn').attr('disabled',true);

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

          //$('#signup-btn').attr('disabled',false);

          $("#ajax-spinner").hide();

          if (response.flag == 1) {

            swal({

              title: "",

              icon: "success",

              text: response.msg,

              buttons: false,

            });

            //swal(response.msg);



            setTimeout(function () {

              window.location.href = response.redirect;

            }, 1000);

          } else {

            if (CAPTCHA_CHECK_FLAG == "yes") {

              grecaptcha

                .execute(GC_SITE_KEY_V3, { action: "checkoutLogin" })

                .then(function (e) {

                  document.getElementById("g-recaptcha-response-login").value =

                    e;

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



            return false;

          }

        },

      });

    },

  });

});

