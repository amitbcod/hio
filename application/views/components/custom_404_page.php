<div class="container">
    <div class="row margin-bottom-40">
        <div class="col-md-10 col-md-offset-1">
            <div class="content-page shadow">
                <div class="errWrap">
                    <h1>404</h1>
                    <p class="error404">Oops! We can't find that page.</p>
                    <p class="error404_msg">Why not check our magazines from top categories?</p>
                    <ul class="errmagslist">
                    <?php $navCatData = array_chunk($navCatData,12);?>
                    <?php foreach($navCatData as $navCat){ ?>
                        <?php foreach($navCat as $nav){?>
                    <?php 
                        $nvName = '';
                        if(isset($nav->lang_menu_name) && $nav->lang_menu_name !=''){
                            $nvName = strtolower($nav->lang_menu_name);
                            $nvName = ucwords($nvName);
                        }else{
                            $nvName = strtolower(ucwords($nav->menu_name));
                            $nvName = ucwords($nvName);
                        }
                    ?>
                    <li><a href="<?= linkUrl('category/'.$nav->slug) ?>"><?php echo $nvName; ?></a></li>
                    <?php } ?>
                    <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>