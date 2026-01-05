<?php
	$shopcode = SHOPCODE;
    $shop_id = SHOP_ID;
    $data['webshop_details'] = CommonRepository::get_webshop_details($shopcode, $shop_id);
    if (isset($webshop_details) && $webshop_details->is_success=='true') {
        $SiteMetaTitle = ($webshop_details->FbcWebShopDetails->meta_title!='')?$webshop_details->FbcWebShopDetails->meta_title:$FinalPageTitle;
        $SiteMetaKey = ($webshop_details->FbcWebShopDetails->meta_keywords!='')?$webshop_details->FbcWebShopDetails->meta_keywords:$FinalPageTitle;
        $SiteMetaDesc = ($webshop_details->FbcWebShopDetails->meta_description!='')?$webshop_details->FbcWebShopDetails->meta_description:$FinalPageTitle;
    }else{
        $SiteMetaTitle = $FinalPageTitle;
        $SiteMetaKey = $FinalPageTitle;
        $SiteMetaDesc = $FinalPageTitle;
    }
?>
<title><?php echo (isset($PageMetaTitle) && $PageMetaTitle!='')? $PageMetaTitle:$SiteMetaTitle; ?></title>
<meta name="title" content="<?php echo (isset($PageMetaTitle) && $PageMetaTitle!='')? $PageMetaTitle:$SiteMetaTitle; ?>" />
<meta name="keywords" content="<?php echo (isset($PageMetaKey) && $PageMetaKey!='')? strip_tags($PageMetaKey):strip_tags($SiteMetaKey); ?>" />
<meta name="description" content="<?php echo (isset($PageMetaDesc) && $PageMetaDesc!='')? strip_tags($PageMetaDesc):strip_tags($SiteMetaDesc); ?>" />
<?php if ($this->router->fetch_class() != 'SearchController' && $this->router->fetch_method() != 'searchResultPage') { ?>
	<meta property="og:title" content="<?php echo (isset($PageMetaTitle) && $PageMetaTitle!='')? $PageMetaTitle:$SiteMetaTitle; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo site_url($this->uri->uri_string()); ?>" />
<?php if ($this->router->fetch_class() == 'ProductsController' && $this->router->fetch_method() == 'productDetails') {
  $og_product_image = ((isset($ProductData->base_image) && $ProductData->base_image!='') ? PRODUCT_MEDIUM_IMG.$ProductData->base_image : PRODUCT_DEFAULT_IMG);
 ?><meta property="og:image" content="<?php echo $og_product_image; ?>" />
<?php } ?>
<meta property="og:description" content="<?php echo (isset($PageMetaDesc) && $PageMetaDesc!='')? strip_tags($PageMetaDesc):strip_tags($SiteMetaDesc); ?>" />
<meta name="twitter:card" content="summary"/>
<meta name="twitter:title" content="<?php echo (isset($PageMetaTitle) && $PageMetaTitle!='')? $PageMetaTitle:$SiteMetaTitle; ?>"/>
<meta name="twitter:description" content="<?php echo (isset($PageMetaDesc) && $PageMetaDesc!='')? strip_tags($PageMetaDesc):strip_tags($SiteMetaDesc); ?>"/>
<?php if ($this->router->fetch_class() == 'ProductsController' && $this->router->fetch_method() == 'productDetails') { ?>
	<meta name="twitter:image" content="<?php echo $og_product_image; ?>"/>
<?php } ?>
		<link rel="canonical" href="<?php echo site_url($this->uri->uri_string()); ?>" />
<?php } ?>
