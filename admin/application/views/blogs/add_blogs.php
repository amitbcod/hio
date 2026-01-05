<?php $this->load->view('common/fbc-user/header'); ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.17.2/adapters/jquery.js"></script>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">

  <div class="tab-content">
    <div id="catalogue-discounts-details-tab" class="tab-pane fade in active common-tab-section  min-height-480" style="opacity:1;">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="head-name pad-bt-20"><?php echo $pageTitle; ?></h1>
      </div><!-- d-flex -->

		  <!-- form -->
      <form name="coupon-code-frm-add" id="blogs-add" method="POST" action="">
  			<div class="customize-add-section">
  				<div class="row">
    				<div class="left-form-sec coupon-code-select-product-list">
                        <?php //echo "<pre>";print_r($get_blogs_details); die(); ?>
                        <input type="hidden" name="blog_id" id="blog_id" value="<?php echo isset($get_blogs['id']) ? $get_blogs['id'] : '' ?>" />
    					<div class="col-sm-6 customize-add-inner-sec">
    						<label>Blog Title <span class="required">*</span></label>
    						<input class="form-control" type="text" name="blog_title" id="blog_title" value="<?php echo isset($get_blogs['title']) ? $get_blogs['title'] : '' ?>" placeholder="Enter Blog Title" maxlength = "50"  require>
    					</div>

                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Blog Description <span class="required">*</span></label>
                            <textarea class="form-control" id="blog_description"  name="blog_description" maxlength="5"><?php echo (isset($get_blogs['description']) && $get_blogs['description']!='')?$get_blogs['description']:'';  ?></textarea>
    					</div>

                        <div class="col-sm-6 customize-add-inner-sec">
    						<label>Status</label>
    						<div class="radio">
                                <label><input type="radio" name="status" <?php echo (isset($get_blogs['status']) && $get_blogs['status']=='1')?'checked':''; ?> checked value="1">Enabled <span class="checkmark"></span></label>
                            </div><!-- radio -->
                            <div class="radio">
                                <label><input type="radio" name="status"  <?php echo (isset($get_blogs['status']) && $get_blogs['status']=='0')?'checked':''; ?> value="0">Disabled <span class="checkmark"></span></label>
                            </div>
    					</div>
                    </div>
                </div>
                <div class="download-discard-small mar-top">
                      <button type="button" class="download-btn" id="add_subblogs">Add New Sub-blog</button>
                </div>
            </div>

            <!-- Sub Blogs Edit - Start -->
            <?php if(is_array($get_blogs_details)) {

                $bannersCount = count($get_blogs_details);

                for($i=0; $i<count($get_blogs_details); $i++) { ?>
                <div id="Sub-Title <?= $i+1; ?>">
                    <div class="customize-add-section">
                        <input type="hidden" name="editblogCount" value="<?= count($get_blogs_details); ?>">
                        <h1 class="head-name pad-bt-20">Sub-Title <?= $i+1; ?> </h1>
                        <button type="button" class="delete-btn float-right deleteBlock" data-toggle="modal" data-target="#deleteModal" data-id="<?= $get_blogs_details[$i]['id']; ?>"><i class="fas fa-trash-alt"></i> Delete</button>
                        <div class="row">
                            <div class="left-form-sec coupon-code-select-product-list">
                                <input type="hidden" name="sub_blog_id[]" id="sub_blog_id" value="<?php echo isset($get_blogs_details[$i]['id']) ? $get_blogs_details[$i]['id'] : '' ?>" />
                                <div class="col-sm-6 customize-add-inner-sec">
                                    <label>Blog Title <span class="required">*</span></label>
                                    <input class="form-control" type="text" name="sub_blog_title[]" id="sub_blog_title<?= $i+1; ?>" value="<?php echo isset($get_blogs_details[$i]['title']) ? $get_blogs_details[$i]['title'] : '' ?>" placeholder="Enter Blog Title" maxlength = "50" require >
                                </div>
                                <div class="col-sm-6 customize-add-inner-sec">
                                    <label>Blog Description <span class="required">*</span></label>
                                    <textarea class="form-control" name="sub_blog_description[]" id="sub_blog_description<?= $i+1; ?>" placeholder="Description Area" require maxlength="250"><?= isset($get_blogs_details[$i]['description']) ? $get_blogs_details[$i]['description'] : "" ?></textarea>
                                </div>
                                <div class="col-sm-6 customize-add-inner-sec">
                                    <label>Assign Product</label>
                                    <select class="productList" name="productList[]" id="productList<?= $i+1; ?>">
                                        <?php foreach($get_products_details as $prod_id):?>
                                            <option value="<?php echo $prod_id['id']?>" <?php echo ($get_blogs_details[$i]['product_id'] == $prod_id['id']) ? 'selected' : '' ?>><?php echo $prod_id['text']?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                                <div class="col-sm-6 customize-add-inner-sec">
                                    <label>Display Subscription detail?</label>
                                    <div class="radio">
                                        <label><input type="radio" name="subscription_det<?= $i+1; ?>[]" <?php echo (isset($get_blogs_details[$i]['display_subscription_details']) && $get_blogs_details[$i]['display_subscription_details']=='1')?'checked':''; ?> value="1">Yes <span class="checkmark"></span></label>
                                    </div>
                                    <div class="radio">
                                        <label><input type="radio" name="subscription_det<?= $i+1; ?>[]" <?php echo (isset($get_blogs_details[$i]['display_subscription_details']) && $get_blogs_details[$i]['display_subscription_details']=='0')?'checked':''; ?> value="0">No <span class="checkmark"></span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <script>
                        $('textarea[id*="sub_blog_description"]')
		                .each(function (index) {
			                var id = $(this).attr("id");

			                var editorInstance = CKEDITOR.replace(id, {
                                on: {
                                    instanceReady: function (evt) {
                                        evt.editor.document.on("keyup", function () {
                                            document.getElementById(id).value =
                                                evt.editor.getData();
                                        });

                                        evt.editor.document.on("paste", function () {
                                            document.getElementById(id).value =
                                                evt.editor.getData();
                                        });
                                    },
                                },
			                });
		            });
                    </script>
                </div>
                    <?php }
                    } ?>
                <!-- Sub Blogs Edit - End -->

                <!-- Sub Blogs Add - Start -->
                <div id="blogAddDive">
                </div>
                <!-- Sub Blogs Add - End -->

            <div class="download-discard-small mar-top">
                      <button class="download-btn" id="save_subblogs" type="submit">Save</button>
                </div>
        </form>
        <script type="text/javascript">
            $(function () {
                CKEDITOR.replace('blog_description', {
                extraPlugins :'justify',
                extraAllowedContent : "span(*)",
                    allowedContent: true,
                });
                CKEDITOR.dtd.$removeEmpty.span = 0;
                CKEDITOR.dtd.$removeEmpty.i = 0;
            });
        </script>
        <script type="text/javascript">

            var bCont = <?php echo $bannersCount; ?>;
            var int = bCont+1;

            $("#add_subblogs").on("click", (e) => {
                let appendDiv =
                    '<div id="Sub-Title '+int+'">'+
                    '<div class="customize-add-section">'+
                        '<input type="hidden" name="blogCount" value="'+int+'">'+
                        '<h1 class="head-name pad-bt-20">Sub-Title '+int+' </h1>'+
                        '<button type="button" class="delete-btn float-right removeBlock" data-toggle="modal" data-target="#removeModal" data-id="'+int+'"><i class="fas fa-trash-alt"></i> Remove</button>'+
                        '<div class="row">'+
                            '<div class="left-form-sec coupon-code-select-product-list">'+
                                '<div class="col-sm-6 customize-add-inner-sec">'+
                                    '<label>Blog Title <span class="required">*</span></label>'+
                                    '<input class="form-control" type="text" name="sub_blog_title[]" id="sub_blog_title'+int+'" value="<?php echo isset($get_blogs_details['title']) ? $get_blogs_details['title'] : '' ?>" placeholder="Enter Blog Title" require onkeypress="return /^[a-zA-Z\s]+$/i.test(event.key)">'+
                                '</div>'+
                                '<div class="col-sm-6 customize-add-inner-sec">'+
                                    '<label>Blog Description <span class="required">*</span></label>'+
                                    '<textarea class="form-control" name="sub_blog_description[]" id="sub_blog_description'+int+'" placeholder="Description Area" require onkeypress="return /[0-9a-zA-Z]/i.test(event.key)"><?= isset($get_blogs_details["descr"]) ? $get_blogs_details["descr"] : "" ?></textarea>'+
                                '</div>'+
                                '<div class="col-sm-6 customize-add-inner-sec">'+
                                    '<label>Assign Product</label>'+
                                    '<select class="productList" name="productList[]" id="productList'+int+'"><option value="">- Search Products -</option></select>'+
                                '</div>'+
                                '<div class="col-sm-6 customize-add-inner-sec">'+
                                    '<label>Display Subscription detail?</label>'+
                                    '<div class="radio">'+
                                        '<label><input type="radio" name="subscription_det'+int+'[]" <?php echo (isset($get_blogs_details['status']) && $get_blogs_details['status']=='1')?'checked':''; ?> value="1">Yes <span class="checkmark"></span></label>'+
                                    '</div>'+
                                    '<div class="radio">'+
                                        '<label><input type="radio" name="subscription_det'+int+'[]" <?php echo (isset($get_blogs_details['status']) && $get_blogs_details['status']=='0')?'checked':''; ?> checked value="0">No <span class="checkmark"></span></label>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '</div>'+
                    '</div>';
                    $("#blogAddDive").append(appendDiv);

                      $('textarea[id*="sub_blog_description"]')
		                .each(function (index) {
			                var id = $(this).attr("id");

			                var editorInstance = CKEDITOR.replace(id, {
                                on: {
                                    instanceReady: function (evt) {
                                        evt.editor.document.on("keyup", function () {
                                            document.getElementById(id).value =
                                                evt.editor.getData();
                                        });

                                        evt.editor.document.on("paste", function () {
                                            document.getElementById(id).value =
                                                evt.editor.getData();
                                        });
                                    },
                                },
			                });
		            });

                      int++;
            });

            $(document).ready(function () {
                $('select[id*="productList"]').select2('data', {id: '123', text: 'res_data.primary_email'});
            })

            $(document).on("click", "select[id*='productList']", function () {
                $('select[id*="productList"]').select2({
                    ajax: {
                        url: BASE_URL+"BlogController/get_products_details",
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                        return {
                            searchTerm: params.term // search term
                        };
                        },
                        processResults: function (response) {
                        return {
                            results: response
                        };
                        },
                        cache: true
                    }
                });
            });
        </script>
    </div>
    <?php $i++; ?>
</div>
</main>
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <form id="blockDeleteForm" method="POST" action="<?= base_url('BlogController/deleteBlog')?>">
            <input type="hidden" name="blockID" id="blockID">
            <div class="modal-header">
               <h1 class="head-name">Are you sure? you want to Delete Blog!</h1>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-footer">
               <button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">No</button>
               <button type="submit" class="purple-btn">Delete</button>
            </div>
         </form>
      </div>
   </div>
</div>
<script src="<?php echo SKIN_JS; ?>blogs.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
