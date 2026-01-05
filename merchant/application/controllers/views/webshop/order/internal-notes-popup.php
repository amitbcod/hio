<div class="modal-header">
	<h4 class="head-name">Internal Notes</h4>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	  <span aria-hidden="true">×</span>
	</button>
	
	</div>
	<div class="modal-body">
		<div class="message-box-popup">
					<!-- <label>Internal Notes</label> -->
	<textarea class="form-control" id="internal_notes" name="internal_notes"  placeholder="Internal Notes"><?=$OrderItemData->internal_notes?></textarea>
				</div>
	</div>
<div class="modal-footer">
 <button class="purple-btn" type="button" id="conf-notes-btn" onclick="ConfirmNotes(<?php echo $order_id; ?>);">Confirm</button>
 <button type="button" class="purple-btn" data-dismiss="modal">Close</button>
</div>