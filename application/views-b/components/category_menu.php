<?php if ($search_flag == 1) { ?>
<div class="filter-inner-section category-filter">
    <h3 data-toggle="collapse" data-target="#filterCategory" class="collapsed"
        aria-expanded="<?php echo (isset($feature_prod) && $feature_prod != '') ? true : false; ?>">
        <?php echo (isset($feature_prod) && $feature_prod != '') ? 'Shop By Categories' : 'Category'; ?><span
            class="icon-arrow_drop_down"></span></h3>
    <ul class="collapse <?php echo (isset($feature_prod) && $feature_prod != '') ? 'show' : ''; ?>" id="filterCategory">
        <?php foreach ($navCatData as $main_cat) { ?>
        <li>
            <img src="<?php echo CATEGORY_IMAGE . '/' . $main_cat->cat_image; ?>"> <a
                href="<?php echo BASE_URL ?>category<?php echo '/' . $main_cat->slug ?>"><?php echo $main_cat->menu_name . ' (' . $main_cat->product_count . ')'; ?></a>
            <?php if (isset($main_cat->menu_level_1)) { ?>
            <button class="accordion"></button>
            <ul class="panel">
                <?php foreach ($main_cat->menu_level_1 as $cat_level1) { ?>
                <li><img src="<?php echo CATEGORY_IMAGE . '/' . $cat_level1->cat_image; ?>"> <a
                        href="<?php echo BASE_URL ?>category<?php echo '/' . $main_cat->slug . '/' . $cat_level1->slug ?>"><?php echo $cat_level1->menu_name; ?></a>
                    <?php echo (isset($cat_level1->menu_level_2) ? '<button class="accordion"></button>' : '') ?>
                    <?php if (isset($cat_level1->menu_level_2)) { ?>
                    <ul class="panel">
                        <?php foreach ($cat_level1->menu_level_2 as $cat_level2) { ?>
                        <li><img src="<?php echo CATEGORY_IMAGE . '/' . $cat_level2->cat_image; ?>"> <a
                                href="<?php echo BASE_URL ?>category<?php echo '/' . $main_cat->slug . '/' . $cat_level1->slug . '/' . $cat_level2->slug ?>"><?php echo $cat_level2->menu_name; ?></a>
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
</div><!-- filter-inner-section -->
<?php } else { ?>

<div class="filter-inner-section category-filter">
    <h3 data-toggle="collapse" data-target="#filterCategory" class="collapsed" aria-expanded="false"> Category <span
            class="icon-arrow_drop_down"></span></h3>
    <ul class="collapse" id="filterCategory">

        <?php foreach ($navCatData as $main_cat) { ?>
        <li><a class="<?php echo ($cat_obj->slug == $main_cat->slug) ? 'active' : ''; ?>"
                href="<?php echo BASE_URL ?>category<?php echo '/' . $main_cat->slug ?>">
                <?php echo ((isset($main_cat->lang_menu_name) && $main_cat->lang_menu_name != '') ? $main_cat->lang_menu_name . ' (' . $main_cat->product_count . ')' : $main_cat->menu_name . ' (' . $main_cat->product_count . ')'); ?></a>

            <?php if (isset($main_cat->menu_level_1)) { ?>
            <button class="accordion"></button>
            <ul class="panel">
                <?php foreach ($main_cat->menu_level_1 as $menu_level_1) { ?>

                <li><a class="<?php echo ($cat_obj->slug == $menu_level_1->slug) ? 'active' : ''; ?>"
                        href="<?php echo BASE_URL ?>category<?php echo '/' . $main_cat->slug . '/' . $menu_level_1->slug ?>">
                        <?php
                                        echo ((isset($menu_level_1->lang_menu_name) && $menu_level_1->lang_menu_name != '') ? $menu_level_1->lang_menu_name : $menu_level_1->menu_name);
                                        ?></a>
                    <?php echo (isset($menu_level_1->menu_level_2) ? '<button class="accordion"></button>' : '') ?>

                    <?php if (isset($menu_level_1->menu_level_2)) { ?>
                    <ul class="panel">
                        <?php foreach ($menu_level_1->menu_level_2 as $menu_level_2) { ?>
                        <li><a class="<?php echo ($cat_obj->slug == $menu_level_2->slug) ? 'active' : ''; ?>"
                                href="<?php echo BASE_URL ?>category<?php echo '/' . $main_cat->slug . '/' . $menu_level_1->slug . '/' . $menu_level_2->slug ?>">
                                <?php echo ((isset($menu_level_2->lang_menu_name) && $menu_level_2->lang_menu_name != '') ? $menu_level_2->lang_menu_name : $menu_level_2->menu_name);
                                                        ?></a></li>
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
</div><!-- filter-inner-section -->
<?php } ?>