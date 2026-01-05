<?php

//upload.php

if(isset($_FILES['upload']['name']))
{
 $file = $_FILES['upload']['tmp_name'];
 $file_name = $_FILES['upload']['name'];
 $file_name_array = explode(".", $file_name);
 $extension = end($file_name_array);
 $new_image_name = rand() . '.' . $extension;
 chmod('cmsPagesImage', 0777);
 $allowed_extension = array("jpg", "gif", "png");
 if(in_array($extension, $allowed_extension))
 {
     
    if(isset($_GET['shopname']) && $_GET['shopname'] != NULL) {
        
        // if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/indiamags/uploads/cmsPagesImage') {
        //     mkdir($_SERVER['DOCUMENT_ROOT'].'/cmsPagesImage/'.$_GET['shopname'], 0777, true);
        // }

        move_uploaded_file($file, $_SERVER['DOCUMENT_ROOT'].'/indiamags/uploads/cmsPagesImage'.'/'. $new_image_name);
        $function_number = $_GET['CKEditorFuncNum'];
        $url = 'http://'.$_SERVER["HTTP_HOST"].'/indiamags/uploads/cmsPagesImage'.'/'.$new_image_name;

    }else{

        // if (!file_exists( $_SERVER['DOCUMENT_ROOT'].'/cmsPagesImage/')) {
        //     mkdir( $_SERVER['DOCUMENT_ROOT'].'/cmsPagesImage/', 0777, true);
        // }

        move_uploaded_file($file, $_SERVER['DOCUMENT_ROOT'].'/indiamags/uploads/cmsPagesImage'.'/'. $new_image_name);
        $function_number = $_GET['CKEditorFuncNum'];
        $url = 'http://'.$_SERVER["HTTP_HOST"].'/indiamags/uploads/cmsPagesImage'.'/'.$new_image_name;


    }

  $message = '';
  echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($function_number, '$url', '$message');</script>";
 }
}

?>
