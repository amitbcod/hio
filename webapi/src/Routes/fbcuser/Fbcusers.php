<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->post('/fbc-users/register', function (Request $request, Response $response){
	
	// $message = $request->getParsedBody()['email'];
	
	$posted_data = $request->getParsedBody();
	// extract($posted_data);
	print_R($posted_data);
	$error='';
	
	
	
	if(empty($email) || empty($password) || empty($org_shop_name)){		
		$error='Please enter all mandatory / compulsory fields.';		
	}else if( !preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email)){	
		$error='Please enter a valid Email address.';		
	}else{
		
		
		$fbc_obj=new DbFbcuser();	
		$IsEmailExists = $fbc_obj->FbcUserDetailByEmail($email);
	
		if($IsEmailExists!==false)
		{	
			$error='User already registered with this email address';			
		}
		else {
			
			//$HashPassword = password_hash($password, PASSWORD_DEFAULT);	
			$HashPassword = md5($password);				

			$fbc_user_id=$fbc_obj->insert_fbc_user($email,$HashPassword);
			
			if(isset($fbc_user_id) && is_numeric($fbc_user_id) && $fbc_user_id>0){
				
				$shop_id=$fbc_obj->insert_fbc_user_shop($fbc_user_id,$org_shop_name);
				
				if(isset($shop_id) && is_numeric($shop_id) && $shop_id>0){
					
					$identifier=$fbc_obj->seo_friendly_url($org_shop_name);
					
					$fbc_obj->UpdateShopIdForFbcUser($fbc_user_id,$shop_id,$identifier);
					
				}else{
					$shop_id='';
				}
				
				$error='';
				$success='User created successfully';
				
			}else{
				$fbc_user_id='';
				$shop_id='';
				$error='Something went wrong!';
			}
		}
		
	}

	if($error != '' ){
		$message['statusCode'] = '500';
   		$message['is_success'] = 'false';
		$message['message'] = $error;      
		exit(json_encode($message));
   	}else{
		$message['statusCode'] = '200';
   		$message['is_success'] = 'true';
		$message['message'] = $success;
		$message['userdetails'] = array('fbc_user_id'=>$fbc_user_id,'shop_id'=>$shop_id);
		exit(json_encode($message));
   	}
	
	
});
