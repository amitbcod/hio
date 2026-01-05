<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">

		<li><a href="<?= base_url('webshop/newsletter-subscriber') ?>">Newsletter Subscriber </a></li>
    <li class="active"><a href="<?= base_url('webshop/edit-newsletter-subscriber-text') ?>">Edit Newsletter Subscriber Text </a></li>
	</ul>

    <div class="profile-details busniess-details editnewsletter">
        <div class="row">
            <form id="editnewslettertexteditform" method="POST" action="<?= base_url('WebshopController/submit_newsletter_text') ?>">
              
                <div class="col-md-12">
                    <h2>Edit Newsletter Subscriber Text</h2>
                    <div class="row">
               <?php 
                     if(!empty($news_letter_info))
                    	{
						 foreach ($news_letter_info as  $value) { ?>
                         <input type="hidden" name="row_id" value="<?= $value['id']; ?>">
                        
                       <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                            
                            <div class="profile-inside-box">
                                <label for="title">Title</label>
                                <div>
                                    <input class="form-control" type="text"  name="title" id="email" value="<?php echo $value['newsletter_title']; ?>" placeholder="Enter title ">
                                </div>
                                
                            </div><!-- profile-inside-box -->
                            
                        </div><!-- col-sm-6 -->
						<div class="clear"></div>
                       	<div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="news_letter_message" id="news_letter_message" value="" placeholder="Newsletter Message"><?php echo $value['newsletter_message']; ?></textarea>
                            </div><!-- profile-inside-box -->
                         </div>
                         
                        <?php   }	}else
                        {  ?>
                        <div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                           
                           <div class="profile-inside-box">
                                <label for="title">Title</label>
                                <div>
                                    <input class="form-control" type="text"  name="title" id="title" value="" placeholder="Enter Title ">
                                </div>
                                
                            </div><!-- profile-inside-box -->
                        </div><!-- col-sm-6 -->


						<div class="clear"></div>
                       
                       	<div class="col-sm-6 profile-details-inner role-in-company webshop-details-sec" >
                        	 <label class="">Message</label>
                        	 <div class="profile-inside-box">                                
                             <textarea class="form-control" name="news_letter_message" id="news_letter_message" placeholder="News Letter Message"></textarea>
                            </div><!-- profile-inside-box -->
                         </div>
                         
                       <?php } ?>	
                    </div>
                <?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/newsletter_subscriber/write',$this->session->userdata('userPermission'))){ ?>
                    <div class="save-discard-btn">
                        <input type="submit" name="save_newsletter_edit" value="Save" class="purple-btn">
                    </div>
                <?php } ?>
                </div><!-- row -->
            </form>
        </div><!-- profile-details-block -->
    </div>
</main>
<script src="<?php echo SKIN_JS; ?>news_letter.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>