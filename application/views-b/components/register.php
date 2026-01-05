<head>
    <link
     rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"
   />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
   <style type="text/css">
       .iti--allow-dropdown input, .iti--allow-dropdown input[type=text], .iti--allow-dropdown input[type=tel], .iti--separate-dial-code input, .iti--separate-dial-code input[type=text], .iti--separate-dial-code input[type=tel] {
    padding-right: 128px;
    padding-left: 52px;
    margin-left: 0;
}
label.error {
    margin-bottom: 0;
    text-align: center;
}
   </style>
</head>



<div class="sigin-form">
    <div class="register-hide">
        <div class="form-box <?php echo ($display_page =='checkout')?'first' :'' ?>">
        <input class="form-control validate-char" type="text" name="first_name" id="first_name" placeholder="First Name">
        <p class="first_name_error"></p>
    </div><!-- form-box -->

    <div class="form-box <?php echo ($display_page =='checkout')?'second' :'' ?>">
        <input class="form-control validate-char" type="text" name="last_name" id="last_name" placeholder="Last Name">
        <p class="last_name_error"></p>

    </div><!-- form-box -->

    <div class="form-box <?php echo ($display_page =='checkout')?'second' :'' ?>">
        <input class="form-control" type="tel" name="mobile_no" id="mobile_no" placeholder="Mobile number" onkeypress="return isNumberKey(event)" maxlength="15">
        <!-- <input class="form-control" type="tel" name="mobile_no" value="" placeholder="Mobile number" onkeypress="return isNumberKey(event)" maxlength="10"/> -->

        <p class="mobile_error"></p>
    </div><!-- form-box -->
    <div class="alert alert-info" style="display: none;"></div>


    <!--  <div class="row col-sm-12" >
        <div class="col-sm-5">
           <select class="form-control" name="phone_prefix" id="phone_prefix">
                <?php if(isset($countryPrifix) && !empty($countryPrifix)){ 
                    foreach ($countryPrifix as $key => $value) {  
                     ?>
                <option value="<?php echo $value->idd_code; ?>"><?php echo $value->country_code.' +'.$value->idd_code; ?></option>
            <?php } } ?>
            </select> 


        </div>
         <div class="col-sm-7">
             <div class="form-box <?php echo ($display_page =='checkout')?'full' :'' ?>">
            <input class="form-control" type="tel" name="mobile_no" id="mobile_no" placeholder="Mobile number">
        </div>
         </div>

         <div class="container">
         <form id="login" onsubmit="process(event)">
           <p>Enter your phone number:</p>
           <input id="phone" type="tel" name="phone" />
           <input type="submit" class="btn" value="Verify" />
         </form>
        </div> 
     </div> -->

    <div class="form-box <?php echo ($display_page =='checkout')?'full' :'' ?>">
        <input class="form-control" type="text" name="email" id="email_id" placeholder="Email (optional)">
    </div><!-- form-box -->

    

    <div class="form-box <?php echo ($display_page =='checkout')?'first' :'' ?>">
        <input class="form-control" type="password" id="password" name="password" placeholder="password">
        <p class="password_error"></p>
    </div><!-- form-box -->

    <div class="form-box <?php echo ($display_page =='checkout')?'second' :'' ?>">
        <input class="form-control" type="password" id="<?php echo ($display_page =='checkout')?'reg_password' :'conf_password' ?>" name="conf_password" placeholder="Confirm Password" id="conf_passwords">
        <p class="conf_password_error"></p>
    </div><!-- form-box -->

    <?php if($display_page =='checkout' || $display_page == 'register'){?>
        <p class="tw-flex tw-items-center">
        <input type="checkbox" id="agree_chk" name="agree_chk" value="1"> 
        <label for="agree_chk" class="tw-ml-2"> Agree to all Terms of Use* <a href="/page/termsofuse" target="_blank" class="tnc"><?=lang('tnc')?></a>
        </label>
        </p>
        <p class="agree_chk_error"></p>
    <?php } ?>
     </div>

     <div class="form-box <?php echo ($display_page =='checkout')?'second' :'' ?> d-none otp_verification">
        <input class="form-control" type="number" id="<?php echo ($display_page =='checkout')?'reg_password' :'conf_password' ?>" name="otp_verification" id="otp_verification" placeholder="Enter Otp">
        <p class="sent-otp"></p>
        
    </div><!-- form-box -->
    
        <div class="col-sm-12">
            <input type="text" name="nickname" id="nickname" value="" style="display: none;"></label>
        </div>

    <div class="signin-btn">
        <input class="black-btn blue-btn d-none" type="submit" name="signup-btn" id="signup-btn" value="Create an account">
        
        <input class="black-btn black-btn continue-btn" type="btn" id="continue_btn" value="Continue" onClick="CustomerOptSignup(); return false;">
    </div><!-- signin-btn -->
</div><!-- sigin-form -->
 <script>
   const input  = document.querySelector("#mobile_no");
   const iti = window.intlTelInput(input , {
    initialCountry: "In",
    separateDialCode: true,
    placeholderNumberType:"MOBILE",
    autoPlaceholder:"polite",
     utilsScript:
       "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
   });
</script>



<script type="text/javascript">
    function CustomerOptSignup(){
        var mobile_no=$('#mobile_no').val();
        var email=$('#email_id').val();
        var last_name=$('#last_name').val();
        var first_name=$('#first_name').val();
        var password=$('#password').val();
        var conf_password=$('#conf_passwords').val();
        var agree_chk_error = $('#agree_chk').val();
        var flag = true;

    
        if($('input[type=checkbox]').is(':checked')) {
            $(this).prop('checked',true);
            $('.agree_chk_error').html("");
        } else {
            $(this).prop('checked',false);
             $('.agree_chk_error').css("color", "red", 'top', '-27px');
            $('.agree_chk_error').html("This field required");
        }

        if(first_name ===''){
            $('.first_name_error').css("color", "red");
            $('.first_name_error').html("This field required");
            flag = false;
        }else{
            $('.first_name_error').html("");
            flag = true;
        }

        if(last_name ===''){
            $('.last_name_error').css("color", "red");
            $('.last_name_error').html("This field required");
            flag = false;
            
        }else{
            $('.last_name_error').html("");  
            flag = true;

        }
        if(mobile_no ===''){
            $('.mobile_error').css("color", "red");
            $('.mobile_error').html("This field required");
            flag = false;

        }else{
            $('.mobile_error').html("");
            flag = true;

        }
        if(password ===''){
            $('.password_error').css("color", "red");
            $('.password_error').html("This field required");
            flag = false;

        }else{
            $('.password_error').html("");
            flag = true;   
        }

        if(first_name !=='' && password !=='' && mobile_no !=='' && last_name !=='' && $('input[type=checkbox]').is(':checked')){
            $.ajax({
                url: BASE_URL+"CustomerController/customer_signup_otp",
                type: "POST",
                data: { mobile_no:mobile_no,email:email, },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if(obj.flag==1){
                        $('.register-hide').addClass('d-none');
                        $('.otp_verification').removeClass('d-none');
                        $('.continue-btn').addClass('d-none');
                        $('.blue-btn').removeClass('d-none');
                        $('.sent-otp').html('OTP is : ' + obj.data);
                    }else{
                        swal({
                            title: "",
                            icon: "error",
                            text: obj.msg,
                            buttons: false,
                        });
                    }
                  
                }
            });
        }else{
                return false;
            }
    }
</script>