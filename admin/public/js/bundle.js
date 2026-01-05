$('#sku').autocomplete({
    minLength: 3,
    source: function(request, response) {
        $.getJSON(BASE_URL+"BundleProductsController/getProductBundleChildSku", {
            term: request.term
        }, function(data) {
            var array = data.error ? [] : $.map(data, function(m) {
                return {
                    label: m.name+" - "+m.sku,
                    value: m.sku,
                    id: m.id,
                    parent_id: m.parent_id,
                };
            });
            console.log(array)
            response(array);
         });
    },
    select: function (event, ui) {
        $('#sku').val(ui.item.value); // save selected id to hidden input
        return false;
    },
    focus: function( event, ui ) {
        $('#sku').val(ui.item.label);
        return false;
    },
    change: function( event, ui ) {
        $( "#sku" ).val( ui.item? ui.item.value : "" );
    }


});



$(document).on("keyup", "#sku_config", function(e) {
    $('#config-data').html('');
    $('#barcode-error-config').html('');
    // alert("Hi");
})

function calculate_bundle_webshop_price(round_flag='',random_id=''){
    // SELLING PRICE change
    if($('#product_type_'+random_id).val() == 'simple'){
        var price = $('#price_'+random_id).val();
        var tax_percent = $('#tax_percent_'+random_id).val();
        var new_ws_price=price;
        if(price>0 && tax_percent>0){
            var tax_amount=calculate_percentage(tax_percent,price);
             new_ws_price = parseFloat(price) + parseFloat(tax_amount);
        }else{
            new_ws_price=parseFloat(price);
        }
        if(round_flag == 1){
            $("#webshop_price_"+random_id).val(Math.round(new_ws_price).toFixed(2));
        }else{
            $("#webshop_price_"+random_id).val(new_ws_price.toFixed(2));
        }
    }else{
        if(random_id!=''){
            var price = $('#price_'+random_id).val();
            var tax_percent = $('#tax_percent_'+random_id).val();
            var new_ws_price=price;
            if(price>0 && tax_percent>0){
                var tax_amount=calculate_percentage(tax_percent,price);
                 new_ws_price = parseFloat(price) + parseFloat(tax_amount);
            }else{
                new_ws_price=parseFloat(price);
            }
            if(round_flag == 1){
                $("#webshop_price_"+random_id).val(Math.round(new_ws_price).toFixed(2));
            }else{
                $("#webshop_price_"+random_id).val(new_ws_price.toFixed(2));
            }
        }
    }
    calculate_bundle_webshop_selling_price(round_flag);
    calculate_bundle_tax_amount(round_flag,random_id);
    calculate_bundle_webshop_price_qty(random_id,round_flag);
    calculate_bundle_selling_price_qty(random_id,round_flag);
}

function calculate_bundle_webshop_selling_price(round_flag=''){
         var new_ws_price=$('.bundleWebshopPrice').val();

        var bundle_webshop_price = 0;
        $(".bundleWebshopPrice").each(function(){
            var get_textbox_value = $(this).val();
            if ($.isNumeric(get_textbox_value)) {
                bundle_webshop_price += parseFloat(get_textbox_value);
            }
        });


        if(round_flag == 1){
            $("#bundle_webshop_price").val(Math.round(bundle_webshop_price).toFixed(2));
        }else{
            $("#bundle_webshop_price").val(bundle_webshop_price.toFixed(2));
        }
    //calculate_bundle_tax_amount(round_flag);
}

function calculate_bundle_webshop_price_qty(random_id,round_flag){
    if(random_id!=''){
        var rowQty = $('#stock_qty_'+random_id).val();
        var rowPrice = $('#webshop_price_'+random_id).val();
        var totalQtyTotal = parseFloat(rowQty) * parseFloat(rowPrice);
        $('#webshop_qty_total_'+random_id).val(totalQtyTotal.toFixed(2));
    }
    //calculate_bundle_selling_price(round_flag);
    //calculate_bundle_tax_amount(round_flag,random_id);
    calculate_bundle_webshop_selling_price(round_flag);
    // calculate_bundle_selling_price_qty(random_id,round_flag);
}

function remove(removed,round_flag){
    removed.remove();
    calculate_bundle_webshop_selling_price(round_flag);
    calculate_bundle_selling_price(round_flag);
    calculate_bundle_tax_amount(round_flag);
}

function removeEdit(removed,round_flag,bundleId){
    if(bundleId){
        removedBundleProductItemRow(bundleId);
    }
    removed.remove();
    calculate_bundle_webshop_selling_price(round_flag);
    calculate_bundle_selling_price(round_flag);
    calculate_bundle_tax_amount(round_flag);
}

function removedBundleProductItemRow(bundleId){
    $.ajax({
        type: "POST",
        dataType: "html",
        url: BASE_URL+"sellerproduct/removedBundleProductItem",
        data: {bundleId:bundleId},
        complete: function () {
        },
        beforeSend: function(){
            // $('#ajax-spinner').show();
        },
        success: function(response) {
            if(response!='error'){
                //$('#category-tree').html(response);
            }else{
                //return false;
            }
        }
    });
}

function calculate_bundle_selling_price(round_flag=''){
        var new_ws_price=$('.bundleWebshopPrice').val();
        var bundle_webshop_price = 0;
        $(".bundleSellingPrice").each(function(){
            var get_textbox_value = $(this).val();
            if ($.isNumeric(get_textbox_value)) {
                bundle_webshop_price += parseFloat(get_textbox_value);
            }
        });

        if(round_flag == 1){
            $("#bundle_price").val(Math.round(bundle_webshop_price).toFixed(2));
        }else{
            $("#bundle_price").val(bundle_webshop_price.toFixed(2));
        }
}

function calculate_bundle_selling_price_qty(random_id='',round_flag=''){
    // DEFAULT QTY change
    if(random_id!=''){
        var rowQty = $('#stock_qty_'+random_id).val();
        var rowPrice = $('#price_'+random_id).val();
        var totalQtyTotal = parseFloat(rowQty) * parseFloat(rowPrice);
        $('#qty_total_'+random_id).val(totalQtyTotal);
    }

    calculate_bundle_selling_price(round_flag);
    calculate_bundle_tax_amount(round_flag,random_id);
    calculate_bundle_webshop_price_qty(random_id,round_flag);

}

// calculate tax pencentage
function calculate_bundle_tax_amount(round_flag='',random_id=''){
    if(random_id!=''){
        var price = $('#price_'+random_id).val();
        var tax_percent = $('#tax_percent_'+random_id).val();
        var tax_amount=calculate_percentage(tax_percent,price);
        var rowQty = $('#stock_qty_'+random_id).val();
        $('#bundle_tax_amount_'+random_id).val(tax_amount);
        var row_tax_amount= parseFloat(tax_amount) * parseFloat(rowQty);
        $('#tax_amount_'+random_id).val(row_tax_amount);
    }

    var bundle_tax_amount = 0;
    $(".row_tax_amount").each(function(){
        var get_textbox_value_tax = $(this).val();
        if ($.isNumeric(get_textbox_value_tax)) {
            bundle_tax_amount += parseFloat(get_textbox_value_tax);
        }
    });

    if(round_flag == 1){
        $("#tax_amount").val(Math.round(bundle_tax_amount).toFixed(2));
    }else{
        $("#tax_amount").val(bundle_tax_amount.toFixed(2));
    }

}
