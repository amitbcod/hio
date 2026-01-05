<?php
$navCatData = $navCatData ?? [];
$menuType = $menuType ?? 'category_menu';
$search_term = $search_term ?? '';
$currNav = $this->uri->segment(2) ?? '';

// Ensure slugs array works even if empty
$allNavgte = !empty($navCatData) ? array_map(fn($nav) => $nav->slug ?? '', $navCatData) : [];
$patnership = ['corporate-offers', 'institutions', 'libraries', 'publisher-partnership'];
?>

<div class="menu-search">
    <div class="search-box">
        <form action="<?= linkUrl('searchresult') ?>" method="GET" class="site-block-top-search" autocomplete="off">
            <div class="input-group">
             <input type="text" id="search" name="s" 
       placeholder="<?= $this->lang->line('search_placeholder'); ?>" 
       class="form-control" value="<?= urldecode($search_term); ?>">

                <span class="input-group-btn">
                    <button class="btn btn-primary submit-search" type="submit"><i class="fa fa-search search-btn"></i></button>
                </span>
            </div>
            <div id="livesearch"></div>
        </form>
    </div>
</div>

<ul class="bottom-section-menu">

    <?php if (!empty($navCatData) && $menuType === 'category_menu') : ?>
        <li class="dropdown dropdown-megamenu <?= in_array($currNav, $allNavgte) ? 'active' : ''; ?>">
            <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:;">
    <?= $this->lang->line('categories'); ?>
</a>

            <ul class="dropdown-menu">
                <li>
                    <?php 
                        // echo "<pre>"; print_r($navCatData); die;
                        $lang = $this->session->userdata('site_lang'); // or whatever key you use for language
                    ?>
                    <div class="header-navigation-content">
                        <div class="row">
                            <?php foreach (array_chunk($navCatData, 12) as $navCat) : ?>
                                <div class="col-md-3 header-navigation-col">
                                    <ul>
                                        <?php foreach ($navCat as $nav) :
                                            // ✅ Choose name based on language
                                            if ($lang === 'french' && !empty($nav->lang_title)) {
                                                $nvName = $nav->lang_title;
                                            } else {
                                                $nvName = $nav->menu_name;
                                            }

                                            $nvName = ucwords(strtolower($nvName));
                                        ?>
                                            <li class="<?= ($currNav === $nav->slug) ? 'active' : ''; ?>">
                                                <a href="<?= linkUrl('category/' . $nav->slug) ?>">
                                                    <?= $nvName ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </li>
            </ul>
        </li>
    <?php endif; ?>

    <!-- Static items -->
<li class="menu-item-new <?= ($currNav === 'newarrival-products') ? 'active' : ''; ?>">
    <a href="<?= linkUrl('https://ymstore.whuso.in/newarrival-products') ?>">
        <?= $this->lang->line('new_arrivals'); ?>
    </a>
</li>
<li class="menu-item-new <?= ($currNav === 'trending-products') ? 'active' : ''; ?>">
    <a href="<?= linkUrl('https://ymstore.whuso.in/trending-products') ?>">
        <?= $this->lang->line('trending_products'); ?>
    </a>
</li>
<li class="menu-item-new <?= ($currNav === 'daily-deals') ? 'active' : ''; ?>">
    <a href="<?= linkUrl('https://ymstore.whuso.in/daily-deals') ?>">
        <?= $this->lang->line('daily_deals'); ?>
    </a>
</li>
<li class="menu-item-new <?= ($currNav === 'flash-sale') ? 'active' : ''; ?>">
    <a href="<?= linkUrl('https://ymstore.whuso.in/flash-sale/category/34') ?>">
        <?= $this->lang->line('flash_sales'); ?>
    </a>
</li>
<li class="menu-item-new <?= ($currNav === 'blog') ? 'active' : ''; ?>">
    <a href="<?= linkUrl('#') ?>">
        <?= $this->lang->line('blog'); ?>
    </a>
</li>

</ul>
