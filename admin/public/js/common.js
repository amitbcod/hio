
function gotoLocation(url)
{
	window.location.href=url;
}

function calculate_percentage(percent, total) {
    return ((percent/ 100) * total).toFixed(2);
}




$(document).ready(function() {

	$('.valid-alphanum-old').bind('keypress', function (event) {
        var regex = new RegExp("^[a-zA-Z0-9]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

	$('.valid-alphanum').keypress(function (e) {
		var regex = new RegExp("^[a-zA-Z0-9]+$");
		var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
		if (regex.test(str)) {
			return true;
		}

		e.preventDefault();
		return false;
	});



	$(".sis-text-editor").each(function () {
            let id = $(this).attr('id');
            CKEDITOR.replace(id);
    });

	$(".mini-text-editor").each(function () {
            let id = $(this).attr('id');
			CKEDITOR.replace(id, {
				//height: 40,
				removePlugins:'elementspath,save,font',
				toolbarLocation:'bottom',
				resize_enabled: false,
				startupFocus:'start',
				removeButtons : 'Source,Save,NewPage,Preview,Print,-,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,-,Undo,Redo,Find,Replace,-,SelectAll,-,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,-,RemoveFormat,-,Outdent,Indent,-,Blockquote,CreateDiv,-,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,-,BidiLtr,BidiRtl,Language,Link,Unlink,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,Font,FontSize,TextColor,BGColor,Maximize,ShowBlocks,About,Strikethrough'
			});

	});


	  const $valueSpan = $('.valueSpan');
	  const $value = $('#slider11');
	  $valueSpan.html($value.val());
	  $value.on('input change', () => {
		$valueSpan.html($value.val());
	  });

		$('#dropdownMenuLink').click(function(){
			$.ajax({
				type:"POST",
				url: BASE_URL+"NotificationController/updateCount",
				success:function(resopnse){
					$('#notification-count').css('display','none');
				}
			});
		});

		jQuery('.dropdown-menu.keep-open').on('click', function (e) {
			e.stopPropagation();
		});
	});

	$(document).ready(function() {
	  const $valueSpan = $('.valueSpan2');
	  const $value = $('#slider12');
	  $valueSpan.html($value.val());
	  $value.on('input change', () => {
		$valueSpan.html($value.val());
	  });
	});

	$(document).ready(function(){
	$(".filter-section").hide();
	  $(".filter button").click(function(){
		$(".filter-section").toggle();
	  });
	  $(".close-arrow").click(function(){
		$(".filter-section").hide();
	  });
	});


	function isNumberKey(evt)
      {
         var charCode = (evt.which) ? evt.which : event.keyCode

		 if(charCode == 13){
			 return false;
		 }else  if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)){
            return false;
		 }

         return true;
      }

function updateNotificationUnreadlFlag(id){
	
	$.ajax({
        type:"POST",
        url: BASE_URL+"NotificationController/updateNotificationUnreadlFlag",
		dataType: 'json',
        data:'id='+id,
        success:function(data){
			console.log(id);
			if (data.flag == 1) {
				$("#notification_"+id).removeClass("unread");
				$("#notification"+id).removeClass("unread");
			}
        }
    });
}

function updateOrderStatus(id,status){
	$("#accept-btn").prop('disabled', true);
	$("#decline-btn").prop('disabled', true);

	var tax_status = $("#tax_status").val();

	$('#ajax-spinner').show();

	if(status == 1){
		msg = 'You want to accept this order request?'
	}else{
		msg = 'You want to decline this order request?'
	}
	swal({
		title: "Are you sure ??",
		text: msg,
		icon: "warning",
		buttons: true,
		className: 'swal-height',
		dangerMode: true,
	},function(isConfirm)
	{if(isConfirm){

		$("#accept-btn").prop('disabled', true);
		$("#decline-btn").prop('disabled', true);


		$.ajax({
			type:"POST",
			url: BASE_URL+"NotificationController/updateOrderStatus",
			dataType: 'json',
			data:'id='+id+'&status='+status+'&tax_status='+tax_status,
			success:function(data){

				$('#ajax-spinner').hide();

				console.log(id);
				console.log(data.flag);
				//console.log(status);
				if (data.flag == 1) {
					if(status == 1){
						status_msg = '<span class="order-confirmed">Accepted</span>';
					}else{
						status_msg = '<span class="order-rejected">Declined</span>';
					}
					//console.log(status_msg);
					$("#notification-btn_"+id).html(status_msg);
					$("#notification-btn"+id).html(status_msg);
					$("#applied-notification-detail_"+id).html(status_msg);



				}
			}
		});
	}else {
		swal({
				//icon: "warning",
				text: "Nothing has been changed",
				buttons: false,
			})
			window.location.reload();
	}
	});

}
function checkPinBaseOnCountry(postal_code,country_code){
	 switch (country_code){

		case "GB":
		postalcode_regex = /^(GIR[ ]?0AA|((AB|AL|B|BA|BB|BD|BH|BL|BN|BR|BS|BT|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}))|BFPO[ ]?\d{1,4})$/i;
		break;

		case "JE":
		postalcode_regex = /JE\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}$/i;
		break;

		case "GG":
		postalcode_regex = /GY\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}$/i;
		break;

		case "IM":
		postalcode_regex = /IM\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}$/i;
		break;

		case "US":
		postalcode_regex = /^\d{5}([\-]?\d{4})?$/i;
		break;

		case "CA":
		postalcode_regex = /[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJ-NPRSTV-Z][ ]?\d[ABCEGHJ-NPRSTV-Z]\d$/i;
		break;

		case "DE":
		postalcode_regex = /^\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b$/;
		break;

		case "JP":
		postalcode_regex = /^\d{3}-\d{4}$/;
		break;

		case "FR":
		postalcode_regex = /^\d{2}[ ]?\d{3}$/;
		break;

		case "AU":
		postalcode_regex = /^\d{4}$/;
		break;

		case "IT":
		postalcode_regex = /^\d{5}$/;
		break;

		case "CH":
		postalcode_regex = /^\d{4}$/;
		break;

		case "AT":
		postalcode_regex = /^\d{4}$/;
		break;

		case "ES":
		postalcode_regex = /^\d{5}$/;
		break;

		case "NL":
		postalcode_regex = /^\d{4}[ ]?[A-Z]{2}$/i;
		break;

		case "BE":
		postalcode_regex = /^\d{4}$/;
		break;

		case "DK":
		postalcode_regex = /^\d{4}$/;
		break;

		case "SE":
		postalcode_regex = /^\d{3}[ ]?\d{2}$/;
		break;

		case "NO":
		postalcode_regex = /^\d{4}$/;
		break;

		case "BR":
		postalcode_regex = /^\d{5}-?\d{3}$/;
		break;

		case "PT":
		postalcode_regex = /^\d{4}([\-]\d{3})?$/;
		break;

		case "FI":
		postalcode_regex = /^\d{5}$/;
		break;

		case "AX":
		postalcode_regex = /^22\d{3}$/;
		break;

		case "KR":
		postalcode_regex = /^\d{3}[\-]\d{3}$/;
		break;

		case "CN":
		postalcode_regex = /^\d{6}$/;
		break;

		case "TW":
		postalcode_regex = /^\d{3}(\d{2})?$/;
		break;

		case "SG":
		postalcode_regex = /^\d{6}$/;
		break;

		case "DZ":
		postalcode_regex = /^\d{5}$/;
		break;

		case "AD":
		postalcode_regex = /AD\d{3}$/;
		break;

		case "AR":
		postalcode_regex = /([A-HJ-NP-Z])?\d{4}([A-Z]{3})?$/i;
		break;

		case "AM":
		postalcode_regex = /^(37)?\d{4}$/;
		break;

		case "AZ":
		postalcode_regex = /^\d{4}$/;
		break;

		case "BH":
		postalcode_regex = /^((1[0-2]|[2-9])\d{2})?$/;
		break;

		case "BD":
		postalcode_regex = /^\d{4}$/;
		break;

		case "BB":
		postalcode_regex = /^(BB\d{5})?$/i;
		break;

		case "BY":
		postalcode_regex = /^\d{6}$/;
		break;

		case "BM":
		postalcode_regex = /[A-Z]{2}[ ]?[A-Z0-9]{2}$/i;
		break;

		case "BA":
		postalcode_regex = /^\d{5}$/;
		break;

		case "IO":
		postalcode_regex = /BBND 1ZZ$/i;
		break;

		case "BN":
		postalcode_regex = /[A-Z]{2}[ ]?\d{4}$/i;
		break;

		case "BG":
		postalcode_regex = /^\d{4}$/;
		break;

		case "KH":
		postalcode_regex = /^\d{5}$/;
		break;

		case "CV":
		postalcode_regex = /^\d{4}$/;
		break;

		case "CL":
		postalcode_regex = /^\d{7}$/;
		break;

		case "CR":
		postalcode_regex = /^\d{4,5}|\d{3}-\d{4}$/;
		break;

		case "HR":
		postalcode_regex = /^\d{5}$/;
		break;

		case "CY":
		postalcode_regex = /^\d{4}$/;
		break;

		case "CZ":
		postalcode_regex = /^\d{3}[ ]?\d{2}$/;
		break;

		case "DO":
		postalcode_regex = /^\d{5}$/;
		break;

		case "EC":
		postalcode_regex = /^([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?$/i;
		break;

		case "EG":
		postalcode_regex = /^\d{5}$/;
		break;

		case "EE":
		postalcode_regex = /^\d{5}$/;
		break;

		case "FO":
		postalcode_regex = /^\d{3}$/;
		break;

		case "GE":
		postalcode_regex = /^\d{4}$/;
		break;

		case "GR":
		postalcode_regex = /^\d{3}[ ]?\d{2}$/;
		break;

		case "GL":
		postalcode_regex = /39\d{2}$/;
		break;

		case "GT":
		postalcode_regex = /^\d{5}$/;
		break;

		case "HT":
		postalcode_regex = /^\d{4}$/;
		break;

		case "HN":
		postalcode_regex = /^\d{5}$/;
		break;

		case "HU":
		postalcode_regex = /^\d{4}$/;
		break;

		case "IS":
		postalcode_regex = /^\d{3}$/;
		break;

		case "ID":
		postalcode_regex = /^\d{5}$/;
		break;

		case "IN":
		postalcode_regex = /^\d{3}\s?\d{3}$/;
		break;

		case "IL":
		postalcode_regex = /^\d{7}$/;
		break;

		case "JO":
		postalcode_regex = /^\d{5}$/;
		break;

		case "KZ":
		postalcode_regex = /^\d{6}$/;
		break;

		case "KE":
		postalcode_regex = /^\d{5}$/;
		break;

		case "KW":
		postalcode_regex = /^\d{5}$/;
		break;

		case "LA":
		postalcode_regex = /^\d{5}$/;
		break;

		case "LV":
		postalcode_regex = /^\d{4}$/;
		break;

		case "LB":
		postalcode_regex = /^(\d{4}([ ]?\d{4})?)?$/;
		break;

		case "LI":
		postalcode_regex = /(948[5-9])|(949[0-7])$/;
		break;

		case "IE":
		postalcode_regex = /(?:^[AC-FHKNPRTV-Y][0-9]{2}|D6W)[ -]?[0-9AC-FHKNPRTV-Y]{4}$/i;
		break;

		case "LT":
		postalcode_regex = /^\d{5}$/;
		break;

		case "LU":
		postalcode_regex = /^\d{4}$/;
		break;

		case "MK":
		postalcode_regex = /^\d{4}$/;
		break;

		case "MY":
		postalcode_regex = /^\d{5}$/;
		break;

		case "MV":
		postalcode_regex = /^\d{5}$/;
		break;

		case "MT":
		postalcode_regex = /[A-Z]{3}[ ]?\d{2,4}$/i;
		break;

		case "MU":
		postalcode_regex = /^(\d{3}[A-Z]{2}\d{3})?$/i;
		break;

		case "MX":
		postalcode_regex = /^\d{5}$/;
		break;

		case "MD":
		postalcode_regex = /^\d{4}$/;
		break;

		case "MC":
		postalcode_regex = /980\d{2}$/;
		break;

		case "MA":
		postalcode_regex = /^\d{5}$/;
		break;

		case "NP":
		postalcode_regex = /^\d{5}$/;
		break;

		case "NZ":
		postalcode_regex = /^\d{4}$/;
		break;

		case "NI":
		postalcode_regex = /^((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?$/;
		break;

		case "NG":
		postalcode_regex = /^(\d{6})?$/;
		break;

		case "OM":
		postalcode_regex = /(PC )?\d{3}$/;
		break;

		case "PK":
		postalcode_regex = /^\d{5}$/;
		break;

		case "PY":
		postalcode_regex = /^\d{4}$/;
		break;

		case "PH":
		postalcode_regex = /^\d{4}$/;
		break;

		case "PL":
		postalcode_regex = /^\d{2}-\d{3}$/;
		break;

		case "PR":
		postalcode_regex = /^00[679]\d{2}([ \-]\d{4})?$/;
		break;

		case "RO":
		postalcode_regex = /^\d{6}$/;
		break;

		case "RU":
		postalcode_regex = /^\d{6}$/;
		break;

		case "SM":
		postalcode_regex = /4789\d$/;
		break;

		case "SA":
		postalcode_regex = /^\d{5}$/;
		break;

		case "SN":
		postalcode_regex = /^\d{5}$/;
		break;

		case "SK":
		postalcode_regex = /^\d{3}[ ]?\d{2}$/;
		break;

		case "SI":
		postalcode_regex = /^\d{4}$/;
		break;

		case "ZA":
		postalcode_regex = /^\d{4}$/;
		break;

		case "LK":
		postalcode_regex = /^\d{5}$/;
		break;

		case "TJ":
		postalcode_regex = /^\d{6}$/;
		break;

		case "TH":
		postalcode_regex = /^\d{5}$/;
		break;

		case "TN":
		postalcode_regex = /^\d{4}$/;
		break;

		case "TR":
		postalcode_regex = /^\d{5}$/;
		break;

		case "TM":
		postalcode_regex = /^\d{6}$/;
		break;

		case "UA":
		postalcode_regex = /^\d{5}$/;
		break;

		case "UY":
		postalcode_regex = /^\d{5}$/;
		break;

		case "UZ":
		postalcode_regex = /^\d{6}$/;
		break;

		case "VA":
		postalcode_regex = /00120$/;
		break;

		case "VE":
		postalcode_regex = /^\d{4}$/;
		break;

		case "ZM":
		postalcode_regex = /^\d{5}$/;
		break;

		case "AS":
		postalcode_regex = /96799$/;
		break;

		case "CC":
		postalcode_regex = /6799$/;
		break;

		case "CK":
		postalcode_regex = /^\d{4}$/;
		break;

		case "RS":
		postalcode_regex = /^\d{6}$/;
		break;

		case "ME":
		postalcode_regex = /8\d{4}$/;
		break;

		case "CS":
		postalcode_regex = /^\d{5}$/;
		break;

		case "YU":
		postalcode_regex = /^\d{5}$/;
		break;

		case "CX":
		postalcode_regex = /6798$/;
		break;

		case "ET":
		postalcode_regex = /^\d{4}$/;
		break;

		case "FK":
		postalcode_regex = /FIQQ 1ZZ$/i;
		break;

		case "NF":
		postalcode_regex = /2899$/;
		break;

		case "FM":
		postalcode_regex = /^(9694[1-4])([ \-]\d{4})?$/;
		break;

		case "GF":
		postalcode_regex = /9[78]3\d{2}$/;
		break;

		case "GN":
		postalcode_regex = /^\d{3}$/;
		break;

		case "GP":
		postalcode_regex = /9[78][01]\d{2}$/;
		break;

		case "GS":
		postalcode_regex = /SIQQ 1ZZ$/i;
		break;

		case "GU":
		postalcode_regex = /^969[123]\d([ \-]\d{4})?$/;
		break;

		case "GW":
		postalcode_regex = /^\d{4}$/;
		break;

		case "HM":
		postalcode_regex = /^\d{4}$/;
		break;

		case "IQ":
		postalcode_regex = /^\d{5}$/;
		break;

		case "KG":
		postalcode_regex = /^\d{6}$/;
		break;

		case "LR":
		postalcode_regex = /^\d{4}$/;
		break;

		case "LS":
		postalcode_regex = /^\d{3}$/;
		break;

		case "MG":
		postalcode_regex = /^\d{3}$/;
		break;

		case "MH":
		postalcode_regex = /969[67]\d([ \-]\d{4})?$/;
		break;

		case "MN":
		postalcode_regex = /^\d{6}$/;
		break;

		case "MP":
		postalcode_regex = /9695[012]([ \-]\d{4})?$/;
		break;

		case "MQ":
		postalcode_regex = /9[78]2\d{2}$/;
		break;

		case "NC":
		postalcode_regex = /988\d{2}$/;
		break;

		case "NE":
		postalcode_regex = /^\d{4}$/;
		break;

		case "VI":
		postalcode_regex = /^008(([0-4]\d)|(5[01]))([ \-]\d{4})?$/;
		break;

		case "PF":
		postalcode_regex = /987\d{2}$/;
		break;

		case "PG":
		postalcode_regex = /^\d{3}$/;
		break;

		case "PM":
		postalcode_regex = /9[78]5\d{2}$/;
		break;

		case "PN":
		postalcode_regex = /PCRN 1ZZ$/i;
		break;

		case "PW":
		postalcode_regex = /96940$/;
		break;

		case "RE":
		postalcode_regex = /9[78]4\d{2}$/;
		break;

		case "SH":
		postalcode_regex = /(ASCN|STHL) 1ZZ$/i;
		break;

		case "SJ":
		postalcode_regex = /^\d{4}$/;
		break;

		case "SO":
		postalcode_regex = /^\d{5}$/;
		break;

		case "SZ":
		postalcode_regex = /[HLMS]\d{3}$/i;
		break;

		case "TC":
		postalcode_regex = /TKCA 1ZZ$/i;
		break;

		case "WF":
		postalcode_regex = /986\d{2}$/;
		break;

		case "XK":
		postalcode_regex = /^\d{5}$/;
		break;

		case "YT":
		postalcode_regex = /976\d{2}$/;
		break;

		default:
		postalcode_regex = /^\d{4}$/;
		break;

	 }
	 return postalcode_regex.test(postal_code);
}
function CheckAddressValMax(country,address1,address2){
	if(country == 'IN'){
		$(address1).attr( "maxlength",'150');
		$(address2).attr( "maxlength",'150');
	}
	else{
		$(address1).attr( "maxlength",'35');
		$(address2).attr( "maxlength",'35');
	}
}
