<?php
    if(isset($ProductData)){
        if((is_array($ProductData->specification) && count($ProductData->specification)>0) ||  (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions)>0)){
?>
            <div id="specifications" class="tab-pane fade in <?php echo empty($ProductData->description) ? 'active show' : ''; ?>">
                <div class="specifications-section">
                    <ul class="specifications-section-list">
                    <?php if (is_array($ProductData->specification) && count($ProductData->specification)>0) {
                        foreach ($ProductData->specification as $attr) { ?>
                        <li>
                            <span class="specifications-title">
                                <?php echo ((isset($attr->multi_attr_name) && $attr->multi_attr_name !='') ? $attr->multi_attr_name : $attr->attr_name); ?>
                            </span>
                            <span class="specifications-text">
                                <?php echo $attr->attr_value; ?>
                                </span>
                        </li>
                    <?php } }

                        if (is_array($ProductData->AttributesWithOptions) && count($ProductData->AttributesWithOptions)>0) {
                            foreach ($ProductData->AttributesWithOptions as $attr) { ?>
                        <li><span class="specifications-title"><?php echo $attr->attr_name; ?></span> <span class="specifications-text"><?php echo $attr->attr_value; ?></span></li>
                      <?php }
                        } ?>
                    </ul>
                </div><!-- specifications-section -->
            </div><!-- specifications -->
<?php
        }
    }
?>
