<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<ul class="nav nav-pills">
			<li class="active"><a href="<?= base_url('custom-variables') ?>">Custom Variables</a></li>
	</ul>
	<div class="main-inner min-height-480">
		<div class="tab-content">
		<div id="attribute" class="tab-pane fade in active show" style="opacity:1;">
		  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
				<h1 class="head-name">Custom Variables List </h1> 
				<div class="float-right">
					<button class="white-btn" data-toggle="modal" data-target="#Modal_Add"> +  Add New</button>
				</div>
			</div>
			
			<!-- form -->
			<div class="content-main form-dashboard">
				<form>

				  <div class="table-responsive text-center">
					<table class="table table-bordered table-style" name="Variable_table" id="Variabletable">
					  <thead>
						<tr>
						  <th>Variable Code</th>
						  <th>Variable Name</th>
						  <th>Variable Value</th>
						  <th class="no-sort">DETAILS</th>
						</tr>
					  </thead>
					  <tbody>
					<?php
						if(isset($CustomVariables) && !empty($CustomVariables))
						{
							foreach($CustomVariables as $var_key=>$var_val)
							{
					  
					?>
						<tr>
						  <td><?php echo $var_val['identifier']; ?></td>
						  <td><?php echo $var_val['name']; ?></td>
						  <td><?php echo $var_val['value']; ?></td>
						  <td><a class="link-purple" href="javascript:void(0);" onclick="editCustomVariable(<?php echo $var_val['id']; ?>)">View</a></td>
						</tr>
					<?php
							}
							
						}  
						
					?>
						
						
						
					  </tbody>
					</table>
				  </div>

				</form>
			</div>
			<!--end form-->
		</div>
	   <!-- main-inner -->


		</div>
	</div>	
</main>	


<!-- MODAL ADD -->
<div class="modal fade" id="Modal_Add" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="exampleModalLabel">Add New Custom Variables</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
	  </div>
	  <div class="modal-body">
		<form action="" method="POST" id="add-custvariable">
			<div class="form-group">
				<label>Variable Code:</label><br>
				<input type="text" class="form-control" name="VariableCode" id="VariableCode">
			</div>
			
			<div class="form-group">
				<label>Variable Name:</label><br>
				<input type="text" class="form-control" name="VariableName" id="VariableName">
			</div>
			
			<div class="form-group">
				<label>Variable Value:</label><br>
				<input type="text" class="form-control" name="VariableValue" id="VariableValue">
			</div>
	  </div>
	  <div class="modal-footer">
		<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">Close</button>
		<button type="submit" class="purple-btn">Save</button>
		</form>
	  </div>
	</div>
  </div>
</div>
<!--END MODAL ADD-->	

<!-- MODAL EDIT -->
<div class="modal fade" id="Modal_Edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="exampleModalLabel">Edit Custom Variables</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
	  </div>
	  <div class="modal-body">
	   <form action="" method="POST" id="edit-custvariable">
	   <input type="hidden" name="Id" id="Id" class="form-control" value="">
			<div class="form-group">
				<label>Variable Code:</label><br>
				<input type="text" class="form-control" name="VariableCode" id="VariableCodeEdit">
			</div>
		  
			<div class="form-group">
				<label>Variable Name:</label><br>
				<input type="text" class="form-control" name="VariableName" id="VariableNameEdit">
			</div>
			
			<div class="form-group">
				<label>Variable Value:</label><br>
				<input type="text" class="form-control" name="VariableValue" id="VariableValueEdit">
			</div>
	  </div>
	  <div class="modal-footer">
		<button type="button" data-dismiss="modal" aria-label="Close" class="white-btn">Close</button>
		<button type="submit" class="purple-btn">Update</button>
		</form>
	  </div>
	  
	</div>
  </div>
</div>
<!--END MODAL EDIT-->

<script src="<?php echo SKIN_JS; ?>customvariable.js"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>
