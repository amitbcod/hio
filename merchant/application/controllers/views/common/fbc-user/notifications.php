<?php 
$LoginID	=	$this->session->userdata('LoginID');
$ShopID		=	$this->session->userdata('ShopID');

$notificationData=$this->NotificationModel->getMultiDataById('notifications',array('to_shop_id'=>$ShopID,'to_fbc_user_id'=>$LoginID,'visited_flag'=>0),'');
// echo "<pre>";print_r($notificationData);exit;
?>
<script>
$(document).ready(function(){

	$('#dropdown-notification').click(function(){
		 $.ajax({
			type:"POST",
			url: BASE_URL+"admin.php/user/notificationCountUpdate",
			success:function(resopnse){
				$('.noti-icon-badge').css('display','none');
				//console.log("abc");
				//location.reload();
			} 
		});
	});

});

function notificationUpdate(id){
	
	 $.ajax({
        type:"POST",
        url: BASE_URL+"admin.php/user/notificationUpdate",
        data:'id='+id,
        success:function(data){
			console.log(id);
            //location.reload();
        } 
    });
	
}
</script>