<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
      <li class="active"><a data-toggle="pill" href="#attribute">Attribute </a></li>
   </ul>
   <div class="main-inner min-height-480">
    <div class="tab-content">
        <div id="variants" class="tab-pane fade in active " style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
               <h1 class="head-name">Attribute List </h1>
               <a href="<?php echo base_url()?>attribute/add-attribute"> <button class="purple-btn">Create New</button></a>
            </div>
        <!-- form -->
        <div class="content-main form-dashboard">
               <div class="table-responsive text-center">
                  <table  class="table table-bordered table-style" id="datatableattribute">
                  <thead>
                        <tr>
                            <th>ATTRIBUTE </th>
                            <th>ATTRIBUTE CODE  </th>
                            <th>STATUS </th>
                            <th>DETAILS </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($getAttribute as $attribute ) {?>
                        <tr>                        
                        <td><?php echo $attribute['attr_name']; ?></td>
                        <td><?php echo $attribute['attr_code']; ?></td>
                            <td>
                                <?php if($attribute['status'] == 1){
                                    echo "Active";
                                }else{
                                    echo "In Active";
                                }?>    
                            </td>
                            <td>
                                <?php if($attribute['is_default'] != 1) {?>
                                <a class="link-purple" href="<?= base_url('AttributeController/editAttribute/').$attribute['id'] ?>">
                                View</a>
                            <?php }else{
                                echo "-";
                            } ?>
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
            "order": [], //Initial no order.

            "paginate": {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        });
    });
</script>