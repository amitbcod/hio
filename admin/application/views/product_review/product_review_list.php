
    <table id="DataTables_Table_Project_Review_list" class="table table-bordered table-style">
        <thead>
            <tr>
                <th data-orderable="false">
                    <input type="checkbox" id="ckbCheckAllSP"> Select All
                </th>
                <th>Product Name </th>
                <th>Customer Name </th>
                <th>Customer Email </th>
                <th>Rating Given </th>
                <th>Review Given </th>
                <th>Review Date </th>
                <th>View </th>
                <th>Action</th>
                <th>Delete </th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

<!-- //model\\ -->

    <div class="modal fade show" tabindex="-1" id="review_details" role="dialog">
        <div class="modal-dialog change-pass-modal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="head-name">Product Review</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pro-review-popup" id="pro-review-popup">

                </div>
            </div>
        </div>
    </div>

