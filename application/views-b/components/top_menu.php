<?php
if ($this->session->userdata('LoginID')) {
  $_sis_session_id = $this->session->userdata('LoginToken');
  $this->session->set_userdata('sis_session_id', $_sis_session_id);
} else {
  if ($this->session->userdata('sis_session_id')) {
    $_sis_session_id = $this->session->userdata('sis_session_id');
  } else {
    $_sis_session_id = generateToken('50');
    $this->session->set_userdata('sis_session_id', $_sis_session_id);
  }
}
$first_segment = $this->uri->segment(1);
$search_term = '';
?>

<ul class="site-menu js-clone-nav d-none d-md-block">
    <?php foreach ($navCatData as $nav) { ?>
    <?php if ($menuType=='category_menu') {?>
    <li class="<?php echo (isset($nav->menu_level_1) && !empty($nav->menu_level_1))?'has-children':''; ?>">
        <a href="<?= linkUrl('category/'.$nav->slug) ?>">
            <?php echo ((isset($nav->lang_menu_name) && $nav->lang_menu_name !='') ? $nav->lang_menu_name : $nav->menu_name);
            ?></a>
        <?php if (isset($nav->menu_level_1) && !empty($nav->menu_level_1)) { ?>
        <ul class="dropdown">
            <?php foreach ($nav->menu_level_1 as $subNav) { ?>
            <li
                class="<?php echo (isset($subNav->menu_level_2) && !empty($subNav->menu_level_2))?'has-children':''; ?>">
                <a href="<?= linkUrl('category/'.$nav->slug.'/'.$subNav->slug) ?>">
                    <?php echo ((isset($subNav->lang_menu_name) && $subNav->lang_menu_name !='') ? $subNav->lang_menu_name : $subNav->menu_name); ?></a>
                <?php if (isset($subNav->menu_level_2) && !empty($subNav->menu_level_2)) { ?>
                <ul class="dropdown">
                    <?php foreach ($subNav->menu_level_2 as $navLevel_2) { ?>
                    <li><a href="<?= linkUrl('category/'.$nav->slug.'/'.$subNav->slug.'/'.$navLevel_2->slug) ?>">
                            <?php echo ((isset($navLevel_2->lang_menu_name) && $navLevel_2->lang_menu_name !='') ? $navLevel_2->lang_menu_name : $navLevel_2->menu_name); ?></a>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </li>
    <?php } else { ?>
    <li class="<?php echo (isset($nav->menu_level_1) && !empty($nav->menu_level_1))?'has-children':''; ?>">
        <?php if ($nav->menu_type==1) {
                $slug = $nav->slug;
            } elseif ($nav->menu_type==2) {
                $slug = '/page/'.$nav->slug;
            } else {
                $slug = '/category/'.$nav->slug;
            } ?>

        <a href="<?= linkUrl($slug) ?>">
            <?php echo ((isset($nav->lang_menu_name) && $nav->lang_menu_name !='') ? $nav->lang_menu_name : $nav->menu_name);
            ?></a>
        <?php if (isset($nav->menu_level_1) && !empty($nav->menu_level_1)) { ?>
        <ul class="dropdown">
            <?php foreach ($nav->menu_level_1 as $subNav) {
                if ($subNav->menu_type==1) {
                    $subSlug = $subNav->slug;
                } elseif ($subNav->menu_type==2) {
                    $subSlug = '/page/'.$subNav->slug;
                } else {
                    $subSlug = '/category/'.$nav->slug.'/'.$subNav->slug;
                } ?>

            <li
                class="<?php echo (isset($subNav->menu_level_2) && !empty($subNav->menu_level_2))?'has-children':''; ?>">
                <a href="<?= linkUrl($subSlug) ?>">
                    <?php echo ((isset($subNav->lang_menu_name) && $subNav->lang_menu_name !='') ? $subNav->lang_menu_name : $subNav->menu_name); ?></a>

                <?php if (isset($subNav->menu_level_2) && !empty($subNav->menu_level_2)) { ?>
                <ul class="dropdown">
                    <?php foreach ($subNav->menu_level_2 as $navLevel_2) {
                        if ($navLevel_2->menu_type==1) {
                            $lvl2Slug = $navLevel_2->slug;
                        } elseif ($navLevel_2->menu_type==2) {
                            $lvl2Slug = '/page/'.$navLevel_2->slug;
                        } else {
                            $lvl2Slug = '/category/'.$nav->slug.'/'.$subNav->slug.'/'.$navLevel_2->slug;
                        } ?>
                    <li
                        class="<?php echo (isset($navLevel_2->menu_level_3) && !empty($navLevel_2->menu_level_3))?'has-children':''; ?>">
                        <a href="<?= linkurl($lvl2Slug) ?>">
                            <?php echo ((isset($navLevel_2->lang_menu_name) && $navLevel_2->lang_menu_name !='') ? $navLevel_2->lang_menu_name : $navLevel_2->menu_name); ?></a>

                        <?php if (isset($navLevel_2->menu_level_3) && !empty($navLevel_2->menu_level_3)) { ?>
                        <ul class="dropdown">
                            <?php foreach ($navLevel_2->menu_level_3 as $navLevel_3) {
                                    if ($navLevel_3->menu_type==1) {
                                        $lvl3Slug = $navLevel_3->slug;
                                    } elseif ($navLevel_3->menu_type==2) {
                                        $lvl3Slug = '/page/'.$navLevel_3->slug;
                                    } else {
                                        $lvl3Slug = '/category/'.$nav->slug.'/'.$subNav->slug.'/'.$navLevel_2->slug.'/'.$navLevel_3->slug;
                                    } ?>
                            <li><a href="<?= linkurl($lvl3Slug) ?>">
                                    <?php echo ((isset($navLevel_3->lang_menu_name) && $navLevel_3->lang_menu_name !='') ? $navLevel_3->lang_menu_name : $navLevel_3->menu_name); ?></a>
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
        <?php } ?>
    </li>
    <?php } } ?>
</ul>