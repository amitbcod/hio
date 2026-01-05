<ul class="breadcrumb">

            <li><a href="<?php echo base_url(); ?>">Home</a></li>

            <?php if(isset($main_cat) && !empty($main_cat)){ ?> 

                <li><a href="<?php echo $main_cat; ?>"><?php echo $main_cat_name; ?></a></li>

            <?php } ?>

            <?php if(isset($level1_cat) && !empty($level1_cat)){ ?> 

                <li><a href="<?php echo base_url().'/category/'.$main_cat.'/'.$level1_cat; ?>"><?php echo $level1_cat_name; ?></a></li>

            <?php } ?>

            <?php if(isset($level2_cat) && !empty($level2_cat)){ ?> 

                <li><a href="<?php echo base_url().'/category/'.$main_cat.'/'.$level1_cat.'/'.$level2_cat; ?>"><?php echo $level2_cat_name; ?></a></li>

            <?php } ?>

        </ul>