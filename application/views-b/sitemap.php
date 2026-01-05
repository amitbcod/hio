<?php echo'<?xml version="1.0" encoding="UTF-8" ?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    <url>
                <loc><?php echo base_url();?></loc>
                <priority>1.0</priority>
                <changefreq>daily</changefreq>
        </url>
        <!-- Sitemap -->
       <!-- Categories -->
       <?php foreach ($categorymenu as $category) {
    if ($category->slug !='independence-day-sale') { ?>
            
                <url>
                    <loc><?php echo base_url()."category/".$category->slug; ?></loc>
                    <priority>0.5</priority>
                    <changefreq>daily</changefreq>
                </url>
            
            <?php }
} ?>
          <!-- Products -->
          <?php  foreach ($products as $product) {  ?>
            
                <url>
                    <loc><?php echo base_url()."product-detail/".$product->url_key; ?></loc>
                    <priority>0.5</priority>
                    <changefreq>daily</changefreq>
                </url>
            
            <?php } ?>
           <!-- CMS Pages -->
           <?php    foreach ($cms_pages as $cms_page) {
    if ($cms_page->identifier !='howitstarted' && $cms_page->identifier != '404-not-found') {
        ?>
           
                <url>
                    <loc><?php echo base_url()."page/".$cms_page->identifier; ?></loc>
                    <priority>0.5</priority>
                    <changefreq>daily</changefreq>
                </url>
            
            <?php
    }
} ?>
           
                <url>
                    <loc><?php echo base_url()."contact-us"; ?></loc>
                    <priority>0.5</priority>
                    <changefreq>daily</changefreq>
                </url>
           

    

</urlset>
 