<?php 
$min_price_rng = 0;
$max_price_rng = 0;
$variant_val_arry = array();
$attr_val_arry = array();

if (isset($_GET['variantVal'])) {
    $variant_val_arry = explode(",", $_GET['variantVal']);
}

if (isset($_GET['attribute'])) {
    $attr_val_arry = explode(",", $_GET['attribute']);
}

$catqalog_filter = $catalogFilter->productCatalogFilter;?>

<div class="filter-inner-section price-filter" style="display: none;">
    <h3 data-toggle="collapse" data-target="#price-range-block" class="collapsed" aria-expanded="false"><?=lang('price_in')?> <?php echo CURRENCY_TYPE;  ?><span class="icon-arrow_drop_down"></span></h3>
    <?php
        $min_price_rng = (isset($catqalog_filter->price_range->min_price_range) && $catqalog_filter->price_range->min_price_range>0)?$catqalog_filter->price_range->min_price_range:0;
        $max_price_rng =(isset($catqalog_filter->price_range->max_price_range)  && $catqalog_filter->price_range->max_price_range>0)?$catqalog_filter->price_range->max_price_range:0;
    ?>
    <div class="price-range-block collapse" id="price-range-block">
        <div id="slider-range" class="price-filter-range" name="rangeInput"></div>
        <div class="price-result">
        <input type="number" id="min_price" name="min_price" class="price-range-field range-left" value="<?php echo $min_price_rng;?>"/>
        <input type="number" id="max_price" name="max_price" class="price-range-field range-right" value="<?php echo $max_price_rng;?>"/>
        </div>
    </div><!-- price-range-block -->
</div><!-- filter-inner-section -->



<?php if (isset($catqalog_filter->variant_listing) && !empty($catqalog_filter->variant_listing)) {
    foreach ($catqalog_filter->variant_listing as $key => $variant) {
        $string = str_replace(' ', '', $key); 
        $attCodeKeyData=explode('__', $key);   
        if(isset($attCodeKeyData) && !empty($attCodeKeyData)){
            $key=ucfirst($attCodeKeyData[0]);
            $keyName=$attCodeKeyData[1];
        } 
?>
    <div class="filter-inner-section <?php echo(($key == 'Color')?'color-filter':''); ?>">
        <h3 data-toggle="collapse" data-target="#filterBy<?php echo $string?>" class="collapsed" aria-expanded="false"><?php echo $keyName; ?><span class="icon-arrow_drop_down"></span></h3>
        <ul class="collapse" id="filterBy<?php echo $string?>">
            <?php 
            if($key == 'Color' && $color_access=='yes'){ 
                if(isset($variant)){
                    $variant_id_value=$variant[0]->variant_id;
                }else{
                    $variant_id_value='';
                } 

                $current_cat_id = (isset($current_category_id) ? $current_category_id : '');   
                $base_color_data=(new CatalogFiltersBaseColor($current_cat_id,$variant_id_value,$filter_type,$search_term))->render();
                if(isset($base_color_data) && !empty($base_color_data)){

                    foreach($base_color_data as $colorkey => $colorvalue){
                            $base_attr_value=$colorvalue->base_attr_value;
                            $check_varient_id=$colorvalue->check_varient_id;
                            $color_name =$colorvalue->color_name;
                            $attr_value =$colorvalue->OptionIds;
                            $color_name =$colorvalue->color_name;
                
                $attr_value_arr=array();
                if(!empty($attr_value) && isset($attr_value)){

                   $attr_value_arr=explode(',', $attr_value);
                }
                if(count($attr_value_arr) >0 && count($variant_val_arry) > 0 ){ ?>       	
                    <li>
                        <label class="container-checkbox">
                            <input <?php echo(array_intersect($attr_value_arr, $variant_val_arry)?'checked':'')?> type="checkbox" name="variant_chk_base[]" value="<?php echo $attr_value;?>" class="chk-variant"> <?php echo $color_name; ?>
                            <span class="checkmark <?php echo strtolower($colorvalue->color_name) ?>" style="<?php echo(($key == 'Color')?'background:'.strtolower($colorvalue->square_color):''); ?>"></span>
                        </label>
                    </li>

                    <input <?php echo(array_intersect($attr_value_arr, $variant_val_arry)?'checked':'')?> type="hidden" id="variant_attr_<?php echo $base_attr_value;?>" value="<?php echo $variant_id_value;?>">
        <?php  }else{ ?>
                    <li>
                        <label class="container-checkbox">
                            <input type="checkbox" name="variant_chk_base[]" value="<?php echo $attr_value;?>" class="chk-variant"> <?php echo $color_name; ?>
                            <span class="checkmark <?php echo strtolower($color_name) ?>" style="<?php echo(($key == 'Color')?'background:'.strtolower($colorvalue->square_color):''); ?>"></span>
                        </label>
                    </li>

                    <input type="hidden" id="variant_attr_<?php echo $base_attr_value;?>" value="<?php echo $variant_id_value;?>">
        <?php  }
            }
        ?>
        <input type="hidden" id="base_color_variant_id_value" value="<?=$variant_id_value?>">
        <?php }
        }else{ 
            foreach ($variant as $varnt) { ?>
                <li><label class="container-checkbox"><input <?php echo(in_array($varnt->attr_value, $variant_val_arry)?'checked':'')?> type="checkbox" name="variant_chk[]" value="<?php echo $varnt->attr_value;?>" class="chk-variant"> <?php echo $varnt->attr_options_name; ?><span class="checkmark <?php echo strtolower($varnt->attr_options_name) ?>" style="<?php echo(($key == 'Color')?'background:'.strtolower($varnt->attr_options_name):''); ?>"></span></label></li>
                <input <?php echo(in_array($varnt->attr_value, $variant_val_arry)?'checked':'')?> type="hidden" id="variant_attr_<?php echo $varnt->attr_value;?>" value="<?php echo $varnt->variant_id;?>">
            <?php 
                   } 
                }
            ?>
           
        </ul>
    </div><!-- filter-inner-section -->
<?php
    }
} ?>

<?php if (isset($catqalog_filter->attribute_listing) && !empty($catqalog_filter->attribute_listing)) {
    foreach ($catqalog_filter->attribute_listing as $key1 => $attribute) {
    $string1 = str_replace(' ', '', $key1); 
    $attCodeKeyData=explode('__', $key1);   
    if(isset($attCodeKeyData) && !empty($attCodeKeyData)){
            $keyName=$attCodeKeyData[1];
    }
?>
    <div class="filter-inner-section">
        <h3 data-toggle="collapse" data-target="#filterBy<?php echo $string1?>" class="collapsed" aria-expanded="false"><?php echo $keyName; ?><span class="icon-arrow_drop_down"></span></h3>
        <ul class="collapse" id="filterBy<?php echo $string1?>">
        <?php foreach ($attribute as $varnt) {?>
            <li><label class="container-checkbox"><input <?php echo(in_array($varnt->attr_value, $attr_val_arry)?'checked':'') ?> type="checkbox" name="attribute_chk[]" value="<?php echo $varnt->attr_value;?>" class="chk-variant"> <?php echo $varnt->attr_options_name; ?><span class="checkmark <?php echo strtolower($varnt->attr_options_name) ?>" style=""></span></label></li>
            <input <?php echo(in_array($varnt->attr_value, $attr_val_arry)?'checked':'') ?> type="hidden" id="attribute_attr_<?php echo $varnt->attr_value;?>" value="<?php echo $varnt->variant_id;?>">
            <?php } ?>
        </ul>
    </div><!-- filter-inner-section -->
<?php }
    }  ?>