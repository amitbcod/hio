<div class="breadcrum-section">
	<div class="container">
		<div class="breadcrum">
			<ul>
				<li><a href="<?php echo BASE_URL;?>">HOME</a></li>
				<li><span class="icon icon-keyboard_arrow_right"></span></li>
				<?php if ($main_cat) { ?>
				<?php echo (isset($level1_cat) && $level1_cat!='') ? '<li><a href="'.base_url().'category/'.$main_cat.'">'.$main_cat_name.'</a></li>':'<li class="active">'.$main_cat_name.'</li>';?>
				<?php } if ($level1_cat) { ?>
				<li><span class="icon icon-keyboard_arrow_right"></span></li>
				<?php echo (isset($level2_cat) && $level2_cat!='') ? '<li><a href="'.base_url().'category/'.$main_cat.'/'.$level1_cat.'">'.$level1_cat_name.'</a></li>':'<li class="active">'.$level1_cat_name.'</li>';?>
				<?php } if ($level2_cat) { ?>
				<li><span class="icon icon-keyboard_arrow_right"></span></li>
				<li class="active"><?php echo $level2_cat_name; ?></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div><!-- breadcrum section -->