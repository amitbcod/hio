<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
      <li class="active"><a data-toggle="pill" href="#giftMaster">Gift Master</a></li>
   </ul>
   <div class="main-inner min-height-480">
    <div class="tab-content">
        <div id="variants" class="tab-pane fade in active " style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
               <h1 class="head-name">Gift Master List </h1>
               <a class="purple-btn" title="Add" onclick="showAddGiftMasterPopup('<?php  echo ''; ?>','<?php  echo ''; ?>');">Add</a>
            </div>
        <!-- form -->
        <div class="content-main form-dashboard">
               <div class="table-responsive text-center">
                  <table  class="table table-bordered table-style" id="datatableGiftMaster">
                  <thead>
                        <tr>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>DETAILS </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($getGiftMaster as $giftmasters ) {?>
                        <tr> 
                        <td><?php echo $giftmasters['id']; ?></td>                       
                        <td><?php echo $giftmasters['name']; ?></td>
                        <td>
                        <a class="link-purple" title="View" onclick="showAddGiftMasterPopup('<?php  echo $giftmasters['name']; ?>','<?php  echo $giftmasters['id']; ?>');">View</a>
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
<script src="<?php echo SKIN_JS; ?>gift_master.js"></script>
<script type="text/javascript">
    $(document).ready( function () {
        $("#datatableGiftMaster").dataTable({
            "language": {
            "infoFiltered": "",
            "search": '',
            "searchPlaceholder": "Search",
            "paginate": {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        });
    });
</script>