<div class="sigin-form">
    <div class="form-box <?php echo ($display_page =='checkout')?'first' :'' ?> emailmobile">
        <input class="form-control" type="text" name="emailmobile" id="emailmobile" placeholder="Email or mobile phone number">
    </div><!-- form-box -->
    
    <div class="form-box <?php echo ($display_page =='checkout')?'second' :'' ?> password d-none">
        <input class="form-control" type="password" id="password" name="password" placeholder="Password">
    </div><!-- form-box -->

    <div class="form-box <?php echo ($display_page =='checkout')?'second' :'' ?> d-none otp_verification">
        <input class="form-control" type="number" id="<?php echo ($display_page =='checkout')?'reg_password' :'conf_password' ?>" name="otp_verification" id="otp_verification" placeholder="Enter Otp">
        <p class="login-otp"></p>   
    </div><!-- form-box -->
    <p class="forgot-password d-none"><a href="<?php echo BASE_URL?>customer/forgot-password">Forgot Password</a></p>
    <!-- Google reCAPTCHA box -->
    <input type="hidden" name="g-recaptcha-response" id="<?php echo ($display_page =='checkout')?'g-recaptcha-response-login' :'g-recaptcha-response' ?>" class="<?php echo ($display_page =='checkout')?'g-recaptcha-response' :'' ?>">

    <?php if(isset($GLOBALS['captcha_check_flag_g']) && $GLOBALS['captcha_check_flag_g']=='no'){ ?>
        <div class="col-sm-12">
            <input type="text" name="nickname" id="nickname" value="" style="display: none;"></label>
        </div>
    <?php }  ?>

    <div class="signin-btn">
    <?php if($display_page =='login') { ?>
        <input type="button" class="btn btn-black continue-btn" onclick="customerLoginOtpEmail();" name="signin-btn" id="signin-continue-btn" value="Continue">
        <input type="submit" class="btn btn-blue d-none" name="signin-btn" id="signin-btn" value="Login">
    <?php } ?>  
    <?php if($display_page =='checkout') { ?>
        <input type="button" class="btn btn-black continue-btn" onclick="customerLoginOtpEmailCheckout();" name="signin-btn" id="signin-continue-btn" value="Continue">
        <input class="btn btn-blue d-none" type="submit" class="black-btn btn-blue" name="signin-btn" id="signin-btn"  value="Login & Continue"> 
    <?php } ?> 
    </div><!-- signin-btn -->

</div><!-- sigin-form -->

<script type="text/javascript">
    
</script>


<script type="text/javascript">
// Checkout Page
 function customerLoginOtpEmailCheckout() {
       var emailmobile = $('#emailmobile').val(); 
        $.ajax({
                url: BASE_URL+"CheckoutController/customerLoginOtpEmail",
                type: "POST",
                data: { emailmobile:emailmobile },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if(obj.flag==1){
                        $('.emailmobile').addClass('d-none');
                        $('.password').removeClass('d-none');
                        $('.continue-btn').addClass('d-none');
                        $('.btn-blue').removeClass('d-none');
                        $('.login-otp').html('Login OTP is :'+ obj.data);
                    }else if(obj.flag==2){
                        $('.emailmobile').addClass('d-none');
                        $('.otp_verification').removeClass('d-none');
                        $('.continue-btn').addClass('d-none');
                        $('.btn-blue').removeClass('d-none');
                        $('.login-otp').html('OTP is : ' + obj.data);
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
    }



    function customerLoginOtpEmail() {
       var emailmobile = $('#emailmobile').val(); 
        $.ajax({
                url: BASE_URL+"CustomerController/customerLoginOtpEmail",
                type: "POST",
                data: { emailmobile:emailmobile },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if(obj.flag==1){
                        $('.emailmobile').addClass('d-none');
                        $('.password').removeClass('d-none');
                        $('.continue-btn').addClass('d-none');
                        $('.btn-blue').removeClass('d-none');
                        $('.forgot-password').removeClass('d-none');
                        
                    }else if(obj.flag==2){
                        $('.emailmobile').addClass('d-none');
                        $('.otp_verification').removeClass('d-none');
                        $('.continue-btn').addClass('d-none');
                        $('.btn-blue').removeClass('d-none');
                        $('.login-otp').html('OTP is : ' + obj.data);

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
    }
</script>