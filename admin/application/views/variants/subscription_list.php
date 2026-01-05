<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
      <li class="active"><a data-toggle="pill" href="#variants">Variants </a></li>
   </ul>
   <div class="main-inner min-height-480">
    <div class="tab-content">
        <div id="variants" class="tab-pane fade in active " style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
               <h1 class="head-name">Variants List </h1>
               <a href="<?php echo base_url()?>variants/add-variants"> <button class="purple-btn">Create New</button></a>
            </div>
        <!-- form -->
        <div class="content-main form-dashboard">
               <div class="table-responsive text-center">
                  <table  class="table table-bordered table-style" id="datatableattribute">
                  <thead>
                        <tr>
                            <th>Subscription Name</th>
                            <th>Subscription Time Limit </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($getAttribute as $attribute ) {?>
                        <tr>                        
                            <td><?php echo $attribute['attr_options_name']; ?></td>
                            <td>
                            <select class = "sub_time" name="sub_time" id="sub_time_<?php echo $attribute['id']; ?>" onchange="savetime('<?php echo $attribute['id']?>','<?php echo $attribute['attr_options_name']?>')">
                            <?php
                                $eav_option_detail = $this->EavAttributesModel->get_subscription_time_details($attribute['id']);
                                // print_R($eav_option_detail);
                                // (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+1 months' ? 'selected'  : '' );
                            ?>
                                <option value="">No time</option>
                                <option value="+1 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+1 months' ? 'selected'  : '' ) ?>>1 months</option>
                                <option value="+2 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+2 months' ? 'selected'  : '' ) ?>>2 months</option>
                                <option value="+3 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+3 months' ? 'selected'  : '' ) ?>>3 months</option>
                                <option value="+4 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+4 months' ? 'selected'  : '' ) ?>>4 months</option>
                                <option value="+5 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+5 months' ? 'selected'  : '' ) ?>>5 months</option>
                                <option value="+6 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+6 months' ? 'selected'  : '' ) ?>>6 months</option>
                                <option value="+7 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+7 months' ? 'selected'  : '' ) ?>>7 months</option>
                                <option value="+8 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+8 months' ? 'selected'  : '' ) ?>>8 months</option>
                                <option value="+9 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+9 months' ? 'selected'  : '' ) ?>>9 months</option>
                                <option value="+10 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+10 months' ? 'selected'  : '' ) ?>>10 months</option>
                                <option value="+11 months" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+11 months' ? 'selected'  : '' ) ?>>11 months</option>
                                <option value="+1 year" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+1 year' ? 'selected'  : '' ) ?>>1 year</option>
                                <option value="+2 year" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+2 year' ? 'selected'  : '' ) ?>>2 year</option>
                                <option value="+3 year" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+3 year' ? 'selected'  : '' ) ?>>3 year</option>
                                <option value="+4 year" <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+4 year' ? 'selected'  : '' ) ?>>4 year</option>
                                <option value="+5 year"  <?php echo (isset($eav_option_detail) && $eav_option_detail && $eav_option_detail['sub_time']!= '' && $eav_option_detail['sub_time'] == '+5 year' ? 'selected'  : '' ) ?>>5 year</option>
                                
                            </select>
                            </td>
                        </tr>
                        <?php  }?>
                    </tbody>
                </table>
            </div>
        </div>
        <!--end form-->
    </div>
    </div><!-- add new tab -->
</div>
  </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script type="text/javascript">
    $(document).ready( function () {
        $("#datatableattribute").dataTable({
            "language": {
            "infoFiltered": "",
            "search": '',
            
            "searchPlaceholder": "Search",
            "paginate": {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        stateSave: true,
        });
    });

    function savetime(eav_option_id,eav_option_name){
        var sub_time  = $("#sub_time_"+eav_option_id).val();
       
        $.ajax({
			type:"POST",
			url: BASE_URL+"VariantsController/submitSubscribtionTime",
			dataType: 'json',
			data:{'sub_time': sub_time, 'eav_option_id': eav_option_id, 'eav_option_name': eav_option_name},
			success:function(data){

				$('#ajax-spinner').hide();
				if (data.flag == 1) {
                    swal({
                            title: "Success", 
                            icon: "success",
                            text: data.msg,
                            buttons: true,
                        })
                        //  window.location.reload();
					}else{
                        swal({
                            title: "Warning", 
                            icon: "warning",
                            text: "Something went wrong!",
                            buttons: true,
                        })
                         window.location.reload();
					}
					//console.log(status_msg);


				
			}
		}); 
    }
</script>