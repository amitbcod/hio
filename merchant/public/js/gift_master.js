function showAddGiftMasterPopup(name,gift_id){
    var buttonName = "";
    if (gift_id == ''){
        buttonName = "Add";
    }
    else{
        buttonName = "Update";
    }
    swal({
        title: "Add Gift Master",
        text: "Enter name",
        type: "input",
        showCancelButton: true,
        confirmButtonText: buttonName,
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        animation: "slide-from-top",
        inputPlaceholder: 'Enter here',
        inputValue:name
    }, function(inputValue) {
        if (inputValue === false) return false;
        if (inputValue === "") {
            swal.showInputError("Please enter name");
            return false;
        }
        addGiftMaster(inputValue,gift_id);
    });
}

function addGiftMaster(name,gift_id){
    $.ajax({
            type: "POST",
            dataType: "html",
            url: BASE_URL+"GiftMasterController/add_edit_GiftMaster/",
            data: {name:name,giftMasterId:gift_id},				
            beforeSend: function () { 
                $('#ajax-spinner').show();
            },			
            success: function(response) {
                $('#ajax-spinner').hide();
                if(response=='success'){
                    swal('Success','Gift Master added successfully!','success');
              window.setTimeout( function() {
              window.location.reload();
              }, 1500);
                }else{
                    return false;
                }
            }
        });
}

