<style>
/* Make the +/– a solid clickable target and keep it above any <a> overlay */
.category-tree .toggle{
  display:inline-block;
  min-width:18px;
  text-align:center;
  border:none;
  border-radius:3px;
  line-height:16px;
  font-weight:600;
  cursor:pointer;
  user-select:none;
  margin-right:6px;
  position:relative;
  z-index:2;
color: #444d5c;
}
/* keep links inline so they don't cover the toggle */

</style>


<?php $this->load->view('common/header'); ?>
<!-- Intro Section -->
<div class="daily-deal-intro">
    <h1><?php echo $this->lang->line('daily_watch_space'); ?></h1>
    <table class="daily-deal-table">
        <tbody>
            <tr>
                <td class="deal-img">
                    <img src="<?php echo base_url('public/images/daily-deals-417x306.jpg'); ?>" alt="24 Hours Daily Deal">
                </td>
                <td class="deal-text">
                    <p><?php echo $this->lang->line('daily_para1'); ?></p>
                    <p><?php echo $this->lang->line('daily_para2'); ?></p>
                    <p><span class="important"><strong><?php echo $this->lang->line('daily_important'); ?></strong></span> <?php echo $this->lang->line('daily_para3'); ?></p>
                    <p><strong><?php echo $this->lang->line('daily_para4'); ?></strong></p>
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

            $html .= '<a href="'.site_url('daily-deals/category/'.$cat->id).'"><i class="fa fa-angle-right"></i> '.$cat->cat_name.'</a>';

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
                        <div class="block-title"><strong><span><?php echo $this->lang->line('browse_by'); ?></span></strong></div>
                        <div class="block-content">
                            <dl id="narrow-by-list2">
                                <dt><?php echo $this->lang->line('categories'); ?></dt>
                                <dd class="categorycontainer tree-div category-tree" id="tree-div">
                                    <ul class="level-0 vshop-left-cat-filter root-category root-category-wrapper">
                                        <li class="tree-node">
                                            <a href="<?php echo site_url('daily-deals'); ?>" 
                                               class="<?php echo empty($active_category) ? 'active' : ''; ?>">
                                               <i class="fa fa-angle-right"></i> <?php echo $this->lang->line('all_categories'); ?>
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
                    <h2 class="page-title"><?php echo $this->lang->line('daily_deals'); ?></h2>
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
                                            <p class="deal-ends mb-2"><?php echo $this->lang->line('deal_ends'); ?> <?php echo date("d M Y, H:i", $p->daily_deal_ends_at); ?></p>
                                            <a href="<?php echo site_url('product-detail/'.$p->url_key); ?>" class="btn btn-sm btn-primary"><?php echo $this->lang->line('view_product'); ?></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info"><?php echo $this->lang->line('no_deals'); ?></div>
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

    // Collapse only direct child lists under each node
    $('.category-tree .tree-node > ul').hide();

    // Auto-open the path to the active category, if present
    var $active = $('.category-tree a.active');
    if ($active.length) {
      $active.parents('ul').show();
      $active.parents('li.tree-node')
             .children('.toggle')
             .text('-')
             .attr('aria-expanded', true);
    }

    // Use event delegation so it works regardless of when DOM is built
    $(d).on('click', '.category-tree .toggle', function (e) {
      e.preventDefault();
      e.stopPropagation();

      var $li = $(this).closest('li.tree-node');

      // Prefer a direct child <ul>, fall back to first level child if markup varies
      var $child = $li.children('ul').first();
      if (!$child.length) $child = $li.find('> ul').first();

      if ($child.length) {
        var isVisible = $child.is(':visible'); // check BEFORE toggle
        $child.slideToggle(150);
        // flip sign based on new state
        $(this).text(isVisible ? '>' : '>')
               .attr('aria-expanded', !isVisible);
      }
    });
  }
  boot();
})(window, document);
</script>


<?php $this->load->view('common/footer'); ?>
