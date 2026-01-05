<?php 

$allNavgte = array_column($navCatData, 'slug'); 

$currNav = $this->uri->segment(2);

$currNav1 = $this->uri->segment(3);

$currNav2 = $this->uri->segment(4);
$lang = $this->session->userdata('site_lang');
// echo "<pre>"; print_r($lang); die;
?>

    <?php

  

        ?>

<ul class="list-group margin-bottom-25 sidebar-menu">

    <?php foreach($navCatData as $main_cat){ 

        // Use lang_title if language is French and it exists
        $mainName = ($lang === 'french' && !empty($main_cat->lang_title)) ? $main_cat->lang_title : $main_cat->menu_name;

    ?> 

        <li class="list-group-item clearfix <?= ($currNav == $main_cat->slug) ? 'active' : ''; ?> <?= isset($main_cat->menu_level_1) ? 'dropdown' : ''; ?>">

            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>">
                <i class="fa fa-angle-right"></i>
                <?= $mainName; ?>
            </a>

            <?php if(isset($main_cat->menu_level_1)){ ?> 

                <ul class="dropdown-menu">

                    <?php foreach($main_cat->menu_level_1 as $cat_level1){ 

                        $level1Name = ($lang === 'french' && !empty($cat_level1->lang_title)) ? $cat_level1->lang_title : $cat_level1->menu_name;

                    ?>

                        <li class="list-group-item clearfix <?= ($currNav == $cat_level1->slug) ? 'active' : ''; ?> <?= isset($cat_level1->menu_level_2) ? 'dropdown' : ''; ?>">

                            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>/<?= $cat_level1->slug ?>">
                                <i class="fa fa-angle-right"></i>
                                <?= $level1Name; ?>
                            </a>

                            <?php if(isset($cat_level1->menu_level_2)){ ?>

                                <ul class="dropdown-menu">

                                    <?php foreach($cat_level1->menu_level_2 as $cat_level2){ 
                                        $level2Name = ($lang === 'french' && !empty($cat_level2->lang_title)) ? $cat_level2->lang_title : $cat_level2->menu_name;
                                    ?>
                                        <li class="<?= ($currNav == $cat_level2->slug) ? 'active' : ''; ?>">
                                            <a href="<?= BASE_URL ?>category/<?= $main_cat->slug ?>/<?= $cat_level1->slug ?>/<?= $cat_level2->slug ?>">
                                                <?= $level2Name; ?>
                                            </a>
                                        </li>
                                    <?php } ?>

                                </ul>

                            <?php } ?>

                        </li>

                    <?php } ?>

                </ul>

            <?php } ?>

        </li> 

    <?php } ?>

</ul>
