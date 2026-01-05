<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <style type="text/css">
    .iti--allow-dropdown input,
    .iti--allow-dropdown input[type=text],
    .iti--allow-dropdown input[type=tel],
    .iti--separate-dial-code input,
    .iti--separate-dial-code input[type=text],
    .iti--separate-dial-code input[type=tel] {
      padding-left: 52px;
      margin-left: 0;
    }
    .iti { width: 100%; }
    label.error { margin-bottom: 0; text-align: center; }
  </style>
</head>

<div class="row register-hide">
  <input type="hidden" name="display_page" id="display_page" value="<?= $display_page; ?>">

  <div class="col-sm-6">
    <div class="form-group <?= ($display_page == 'checkout') ? 'first' : '' ?>">
      <label class="sr-only" for="first_name"><?= lang('first_name'); ?></label>
      <input class="form-control validate-char" type="text" name="first_name" id="first_name" placeholder="<?= lang('first_name'); ?>" required>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="form-group <?= ($display_page == 'checkout') ? 'second' : '' ?>">
      <label class="sr-only" for="last_name"><?= lang('last_name'); ?></label>
      <input class="form-control validate-char" type="text" name="last_name" id="last_name" placeholder="<?= lang('last_name'); ?>" required>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="form-group <?= ($display_page == 'checkout') ? 'second' : '' ?>">
      <label class="sr-only" for="mobile_no"><?= lang('mobile_number'); ?></label>
      <input class="form-control" type="tel" name="mobile_no" id="mobile_no" placeholder="<?= lang('mobile_number'); ?>" maxlength="15" required>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="form-group <?= ($display_page == 'checkout') ? 'full' : '' ?>">
      <label class="sr-only" for="email_id"><?= lang('email'); ?></label>
      <input class="form-control" type="text" name="email" id="email_id" placeholder="<?= lang('email'); ?>" required>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="form-group <?= ($display_page == 'checkout') ? 'first' : '' ?>">
      <label class="sr-only" for="password"><?= lang('password'); ?></label>
      <input class="form-control" type="password" id="password" name="password" placeholder="<?= lang('password'); ?>" required>
      <span class="eye-password toggle-password"></span>
    </div>
  </div>

  <div class="col-sm-6">
    <div class="form-group <?= ($display_page == 'checkout') ? 'second' : '' ?>">
      <label class="sr-only" for="conf_password"><?= lang('confirm_password'); ?></label>
      <input class="form-control" type="password" id="<?= ($display_page == 'checkout') ? 'reg_password' : 'conf_password' ?>" name="conf_password" placeholder="<?= lang('confirm_password'); ?>" required>
      <span class="eye-password toggle-Confpassword"></span>
    </div>
  </div>

  <div class="checkbox">
    <label>
      <input type="checkbox" name="remember" id="remember" <?= isset($_COOKIE["login_email"]) ? 'checked' : '' ?>> 
      <?= lang('accept_terms'); ?> 
      <span class="required">
        <a href="/page/terms-conditions" target="_blank"><?= lang('terms_conditions'); ?></a>
      </span>
    </label>
  </div>

  <div class="col-sm-6">
    <input class="btn btn-primary d-none" type="submit" name="signup-btn" id="signup-btn" value="<?= lang('create_account'); ?>">
    <div class="form-input">
      <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY_V2; ?>"></div>
    </div>
    <button class="btn btn-primary" type="button" id="continue_btn_reg" onClick="CustomerOptSignup(); return false;">
      <?= lang('signup'); ?>
    </button>
  </div>
</div>


  <script src="https://www.google.com/recaptcha/api.js?<?php time(); ?>" async defer></script>

<script>
  const input = document.querySelector("#mobile_no");
  const iti = window.intlTelInput(input, {
    initialCountry: "In",
    separateDialCode: true,
    placeholderNumberType: "MOBILE",
    autoPlaceholder: "polite",
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
  });
</script>
<!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Load Bootstrap (if used) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Load your custom scripts -->
<script src="/path/to/your-script.js"></script>

<script>
  $(document).ready(function () {
	  $(document).on('click', '.toggle-password', function() {
    var input = $(this).siblings('input'); // Get the input field related to this toggle button

    // Toggle the type attribute of the input
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        $(this).removeClass('eye-password').addClass('eye-text'); // Change icon to "eye-text"
    } else {
        input.attr('type', 'password');
        $(this).removeClass('eye-text').addClass('eye-password'); // Change icon to "eye-password"
    }
});

$(document).on('click', '.toggle-Confpassword', function() {
    var input = $(this).siblings('input'); // Get the input field related to this toggle button

    // Toggle the type attribute of the input
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        $(this).removeClass('eye-password').addClass('eye-text'); // Change icon to "eye-text"
    } else {
        input.attr('type', 'password');
        $(this).removeClass('eye-text').addClass('eye-password'); // Change icon to "eye-password"
    }
});

	
		
	});
</script>



<script type="text/javascript">
  jQuery.validator.addMethod("alphabets", function(value, element) {
    return this.optional(element) || /^[a-zA-Z ]*$/.test(value);
  }, "Please enter Alphabets only");

  jQuery.validator.addMethod("alphanumeric", function(value, element) {
    return this.optional(element) || /^[a-zA-Z0-9. ]*$/.test(value);
  }, "Please enter Alphabets only");

  jQuery.validator.addMethod("eml", function(value, element) {
    return this.optional(element) || /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/.test(value);
  }, "Please enter Alphabets only");

  // create a custom phone number rule called 'intlTelNumber'
  jQuery.validator.addMethod("intlTelNumber", function(value, element) {
    return this.optional(element) || $(element).intlTelInput("isValidNumber");
  }, "Please enter a valid International Phone Number");

  function CustomerOptSignup() {
    var form = $("#signup-form");
    form.validate({
      rules: {
        first_name: {
          required: true,
          alphabets: true
        },
        last_name: {
          required: true,
          alphabets: true
        },
        email: {
          required: true,
          eml: true
        },
        mobile_no: {
          required: true,
          //intlTelNumber: true
        },
        password: {
          minlength: 8,
          mypassword: true
        },
        conf_password: {
          equalTo: '#password'
        },
        agree_chk: "required",
      },
      messages: {
        first_name: {
          required: "Please enter your first name",
          alphabets: "Please enter Alphabets only"
        },
        last_name: {
          required: "Please enter your last name",
          alphabets: "Please enter Alphabets only"
        },
        email_id: {
          required: "Email id is required.",
          eml: "Please enter a valid email id."
        },
        mobile_no: {
          required: "Valid mobile required",
        },
        password: "Password is required",
        conf_password: "Passwords do not match. Please check.",
        agree_chk: "You have to agree to our terms of use.",
      },
      errorElement: "em",
      errorPlacement: function(error, element) {
        // Add the `invalid-feedback` class to the error element
        error.addClass("invalid-feedback");
        if (element.prop("type") === "checkbox") {
          //error.insertAfter(element.next("br.agrbr"));
          //error.insertBefore(element.parent("div"));
          error.insertAfter($(element).parents('div.chkbx'));
        } else if (element.prop("type") === "tel") {
          error.insertAfter(element.parents("div.iti"));
        } else {
          error.insertAfter(element.next(".pmd-textfield-focused"));
        }
      },
      highlight: function(element, errorClass, validClass) {
        $(element).addClass("is-invalid").removeClass("is-valid");
      },
      unhighlight: function(element, errorClass, validClass) {
        $(element).addClass("is-valid").removeClass("is-invalid");
      },

    });
    var mobile_no = $('#mobile_no').val();
    var email = $('#email_id').val();
    var last_name = $('#last_name').val();
    var first_name = $('#first_name').val();
    var password = $('#password').val();
    var conf_password = $('#conf_passwords').val();
    var agree_chk_error = $('#agree_chk').val();
    var g_recaptcha_response = $('#g-recaptcha-response').val();
    var display_page = $('#display_page').val();
    var flag = true;

    if (form.valid() === true) {
      $.ajax({
        url: BASE_URL + "CustomerController/register",
        type: "POST",
        data: {
          mobile_no: mobile_no,
          email: email,
          first_name: first_name,
          last_name: last_name,
          password: password,
          agree_chk_error: agree_chk_error,
          g_recaptcha_response: g_recaptcha_response,
          display_page: display_page,
        },
        success: function(response) {
          var obj = JSON.parse(response);
          if (obj.flag == 1) {
            swal({
              title: "",
              icon: "success",
              text: obj.msg,
              buttons: false,
            });
            setTimeout(function() {
              window.location = obj.redirect;
            }, 1000);
          } else {
            grecaptcha.reset();
            swal({
              title: "",
              icon: "error",
              text: obj.msg,
              buttons: false,
            });
          }

        }
      });


    }
    // if (flag && first_name !== '' /*&& password*/ !== '' && mobile_no !== '' && last_name !== '' && $(
    //         'input[type=checkbox]').is(':checked')) {
    //     $.ajax({
    //         url: BASE_URL + "CustomerController/customer_signup_otp",
    //         type: "POST",
    //         data: {
    //             mobile_no: mobile_no,
    //             email: email,
    //         },
    //         success: function(response) {
    //             var obj = JSON.parse(response);
    //             if (obj.flag == 1) {
    //                 $('.register-hide').addClass('d-none');
    //                 $('.otp_verification').removeClass('d-none');
    //                 $('.reg_continue-btn').addClass('d-none');
    //                 $('.blue-btn').removeClass('d-none');
    //                 $('.sent-otp').html('OTP is : ' + obj.data);
    //             } else {
    //                 swal({
    //                     title: "",
    //                     icon: "error",
    //                     text: obj.msg,
    //                     buttons: false,
    //                 });
    //             }

    //         }
    //     });
    // } else {
    //     return false;
    // }
  }
</script>