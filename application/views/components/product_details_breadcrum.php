<?php
    if(isset($categoryList->is_success) && $categoryList->is_success == 'true'){ 
        if(isset($categoryList)){
            if($categoryList){
                if($type=='main'){ 
                $MainCategory=$categoryList->CategoryDetails;
                ?>
                    <li>
                        <a href="<?php echo base_url() ?>category/<?php echo $MainCategory->slug; ?>">
                            <?php
                            if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
                                $lang_code=$this->session->userdata('lcode');   
                                $categoryArr_main = array('categoryslug'=>$MainCategory->slug,'lang_code'=>$lang_code); 
                                $category_main = ProductRepository::get_category_details(SHOPCODE, SHOP_ID, $categoryArr_main);                         
                                if($category_main->is_success=='true'){
                                    if(isset($category_main->CategoryDetails->lang_cat_name) && $category_main->CategoryDetails->lang_cat_name !=''){
                                        echo $category_main->CategoryDetails->lang_cat_name;
                                    }else{
                                        echo $MainCategory->cat_name;
                                    }
                                }else{
                                    echo $MainCategory->cat_name;
                                }
                            }else{
                                echo $MainCategory->cat_name;
                            }
                            ?>
                        </a>
                    </li>
                <?php }elseif($type=='sub'){
                    $MainCategory=$categoryList->CategoryDetails;
                    $SubCategory=$categoryListSub->CategoryDetails;
                ?>
                <li><a href="<?php echo base_url() ?>category/<?php echo $MainCategory->slug; ?>/<?php echo $SubCategory->slug; ?>">
                    <?php 
                    if(!empty($this->session->userdata('lcode')) && $this->session->userdata('lis_default_language')==0){
                        $cat_slug =  $MainCategory->slug.'/'.$SubCategory->slug;
                        $lang_code=$this->session->userdata('lcode');
                        $category_2 = array('categoryslug'=>$cat_slug,'lang_code'=>$lang_code); 
                        $category_2 = ProductRepository::get_category_details(SHOPCODE, SHOP_ID, $category_2);              
                        if($category_2->is_success=='true'){
                            if(isset($category_2->CategoryDetails->lang_cat_name) && $category_2->CategoryDetails->lang_cat_name !=''){
                                echo $category_2->CategoryDetails->lang_cat_name;
                            }else{
                                echo $SubCategory->cat_name;
                            }
                        }else{
                            echo $SubCategory->cat_name;
                        }
                    }else{
                        echo $SubCategory->cat_name;
                    }
                    ?></a></li>
        <?php   }
            }
        }
    }
?>