<?php $this->load->view('common/header'); ?>

<div class="signin-full">

  <div class="container">

    <div class="col-md-12">

      <div class="row">

        <div class="grey-bg-user signin-section forgot-password-section">

          <div class="sign-in-inner new-toped">

            <h3><?php echo $this->lang->line('forgot_password_title'); ?></h3>

            <h5><?php echo $this->lang->line('forgot_password_subtitle'); ?></h5>



            <form id="forgot-password-form" method="POST" action="<?php echo BASE_URL;?>merchants/forgot-password">

              <div class="forgotpassword-form" style="margin-bottom: 20px;">

                <div class="form-box" style="margin-bottom: 10px;">

                  <input class="form-control" type="text" name="email" id="email"

                         placeholder="<?php echo $this->lang->line('forgot_password_placeholder'); ?>">

                </div>

                <div class="signin-btn">

                  <input type="submit" class="black-btn blue-btn btn btn-primary"

                         name="forgot-password-btn" id="forgot-password-btn"

                         value="<?php echo $this->lang->line('forgot_password_button'); ?>">

                </div>

              </div>

            </form>



          </div>

        </div>

      </div>

    </div>

  </div>

</div>

<?php $this->load->view('common/footer'); ?>

<script src="<?php echo SKIN_JS ?>forgot_password.js?v=<?php echo CSSJS_VERSION; ?>"></script>

</body>

</html>

