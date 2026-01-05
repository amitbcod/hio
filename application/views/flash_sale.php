<?php $this->load->view('common/header'); ?>

<!-- Intro Section -->
<div class="daily-deal-intro">
    <h1><?= lang('flash_sale_title'); ?></h1>
    <table class="daily-deal-table">
        <tbody>
            <tr>
                <td class="deal-img">
                    <img src="<?php echo base_url('public/images/ym-flash-sale-web.png'); ?>" alt="<?= lang('flash_sale_alt'); ?>">
                </td>
                <td class="deal-text">
                    <p><strong><?= lang('flash_sale_intro1'); ?></strong></p>
                    <p><?= lang('flash_sale_intro2'); ?></p>
                    <p><?= lang('flash_sale_intro3'); ?></p>
                    <p><strong><?= lang('flash_sale_intro4'); ?></strong></p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php
function buildCategoryTree($categories, $parent_id = 0) {
    $html = '';
    $hasChild = false;

    foreach ($categories as $cat) {
        if ($cat->parent_id == $parent_id) {
            if (!$hasChild) {
                $html .= '<ul>';
                $hasChild = true;
            }

            $children = array_filter($categories, function($c) use ($cat) {
                return $c->parent_id == $cat->id;
            });
            $hasChildren = !empty($children);

            $html .= '<li class="tree-node'.($hasChildren ? ' dropdown' : '').'">';
            
            if ($hasChildren) {
                $html .= '<span class="toggle">></span>';
            }

            $html .= '<a href="'.site_url('flash-sale/category/'.$cat->id).'"><i class="fa fa-angle-right"></i> '.$cat->cat_name.'</a>';

            if ($hasChildren) {
                $html .= buildCategoryTree($categories, $cat->id);
            }

            $html .= '</li>';
        }
    }

    if ($hasChild) $html .= '</ul>';
    return $html;
}
?>

<main id="maincontent" class="page-main">
    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar: Category Filter -->
            <div class="col-md-3 order-md-1">
                <div class="sidebar sidebar-main mb-4">
                    <div class="block block-layered-nav">
                        <div class="block-title"><strong><span><?= lang('browse_by'); ?></span></strong></div>
                        <div class="block-content">
                            <dl id="narrow-by-list2">
                                <dt><?= lang('categories'); ?></dt>
                                <dd class="categorycontainer tree-div category-tree" id="tree-div">
                                    <ul class="level-0 vshop-left-cat-filter root-category root-category-wrapper">
                                        <li class="tree-node">
                                            <a href="<?php echo site_url('flash-sale'); ?>" 
                                               class="<?php echo empty($active_category) ? 'active' : ''; ?>">
                                               <i class="fa fa-angle-right"></i> <?= lang('all_categories'); ?>
                                            </a>
                                        </li>
                                        <?php if(!empty($categories)) echo buildCategoryTree($categories); ?>
                                    </ul>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content: Products -->
            <div class="col-md-9 order-md-2">
                <div class="page-title-wrapper mb-4">
                    <h2 class="page-title"><?= lang('flash_sale'); ?></h2>
                </div>

                <div class="category-products">
                    <div class="row">
                        <?php if(!empty($products)): ?>
                            <?php foreach($products as $p): ?>
                                <div class="col-md-4 col-sm-6 mb-4">
                                    <div class="product-item border p-2 h-100">
                                        <div class="product-image text-center mb-2">
                                            <?php 
                                            $imgPath = FCPATH.'uploads/products/thumb/'.$p->base_image;
                                            if(!empty($p->base_image) && file_exists($imgPath)): ?>
                                                <img class="product-image-photo img-fluid" 
                                                     src="<?php echo base_url('uploads/products/thumb/'.$p->base_image); ?>" 
                                                     alt="<?php echo $p->name; ?>">
                                            <?php else: ?>
                                                <img class="product-image-photo img-fluid" 
                                                     src="https://via.placeholder.com/300x300?text=No+Image" 
                                                     alt="No Image">
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-details text-center">
                                            <h3 class="product-name"><?php echo $p->name; ?></h3>
                                            <div class="price-box mb-2">
                                                <?php if(!empty($p->special_price)): ?>
                                                    <span class="special-price">MUR <?php echo $p->special_price; ?></span>
                                                    <span class="old-price text-muted"><s>MUR <?php echo $p->price; ?></s></span>
                                                <?php else: ?>
                                                    <span class="regular-price">MUR <?php echo $p->price; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="deal-ends mb-2"><?= lang('sale_ends'); ?>: <?php echo date("d M Y, H:i", $p->flash_sale_ends_at); ?></p>
                                            <a href="<?php echo site_url('product-detail/'.$p->url_key); ?>" class="btn btn-sm btn-danger"><?= lang('view_product'); ?></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info"><?= lang('no_flash_sale_items'); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pagination-wrapper mt-4">
                        <?php echo $pagination ?? ''; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
(function (w, d) {
  function boot() {
    if (!w.jQuery) { setTimeout(boot, 50); return; }
    var $ = w.jQuery;

    $('.category-tree .tree-node > ul').hide();

    var $active = $('.category-tree a.active');
    if ($active.length) {
      $active.parents('ul').show();
      $active.parents('li.tree-node')
             .children('.toggle')
             .text('-')
             .attr('aria-expanded', true);
    }

    $(d).on('click', '.category-tree .toggle', function (e) {
      e.preventDefault();
      e.stopPropagation();

      var $li = $(this).closest('li.tree-node');
      var $child = $li.children('ul').first();
      if (!$child.length) $child = $li.find('> ul').first();

      if ($child.length) {
        var isVisible = $child.is(':visible');
        $child.slideToggle(150);
        $(this).text(isVisible ? '>' : '>')
               .attr('aria-expanded', !isVisible);
      }
    });
  }
  boot();
})(window, document);
</script>

<?php $this->load->view('common/footer'); ?>
