<div class="pull-left">
    <h1>CATEGORY:
        <?php
        $displayCategory = '';

        if (isset($main_cat) && !empty($main_cat)) {
            $displayCategory = $main_cat_name;
        }
        if (isset($level1_cat) && !empty($level1_cat)) {
            $displayCategory .= ' / ' . $level1_cat_name;
        }
        if (isset($level2_cat) && !empty($level2_cat)) {
            $displayCategory .= ' / ' . $level2_cat_name;
        }

        // Check for specific categories to append (Subscription)
        $subscriptionCategories = ['Childrens Magazines', 'INTERNATIONAL MAGAZINES'];
        foreach ($subscriptionCategories as $subscriptionCategory) {
            if (strpos($displayCategory, $subscriptionCategory) !== false) {
                // Adjust capitalization and append "Subscription"
                $displayCategory = ucwords(strtolower($subscriptionCategory)) . ' Subscription';
                break;
            }
        }


        echo $displayCategory;
        ?>
    </h1>
</div>
