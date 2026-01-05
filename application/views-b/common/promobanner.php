
<?php 

  $lang_code= ((!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0) ? $this->session->userdata('lcode') : '');

//$post_arr = array('country_code'=>$country_code, 'lang_code'=>$lang_code);
$post_arr = array();
$show_banner = HomeDetailsRepository::get_promo_text_banners($post_arr);
if(isset($show_banner->message->banner_text) && $show_banner->message->banner_text !== ''){ ?>
    <div style="padding: 0.5rem; font-weight: bold; text-align: center; background-color:<?php echo $show_banner->message->background_color ?>; color: <?php echo $show_banner->message->text_color; ?>">
    <?php  echo ((isset($show_banner->message->lang_banner_text) && $show_banner->message->lang_banner_text !='') ? $show_banner->message->lang_banner_text :  $show_banner->message->banner_text); ?>
    </div>
<?php } ?>
