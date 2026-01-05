$(document).ready(function () {

  var restricted_access = $("#restricted_access").val();

  // console.log(restricted_access);

  if (restricted_access == "yes") {

    $(".sigin-member").addClass("d-none");

    $(".guestTnc").addClass("hide");

    $("#login-block").removeClass("d-none");

    $("#checkmethod-tab").hide();

  }



  var billing_state_div = ".b_state_div";

  var billing_dp_state_div = ".b_state_dp_div";

  var billing_state_dp = "#b_state_dp";

  var billing_state = "#billing_state";



  CheckState(

    $("#billing_country").val(),

    billing_state_div,

    billing_dp_state_div,

    billing_state_dp,

    billing_state

  );



  $("#billing_country").change(function () {

    CheckState(

      $("#billing_country").val(),

      billing_state_div,

      billing_dp_state_div,

      billing_state_dp,

      billing_state

    );

    var pincode = $("#billing_pincode").val();

    var country = $("#billing_country").val();

    changeCountryBasedOnPostalCode(pincode, country, "#billing_country");

  });



  var shipping_state_div = ".s_state_div";

  var shipping_dp_state_div = ".s_state_dp_div";

  var shipping_state_dp = "#s_state_dp";

  var shipping_state = "#shipping_state";



  CheckState(

    $("#shipping_country").val(),

    shipping_state_div,

    shipping_dp_state_div,

    shipping_state_dp,

    shipping_state

  );



  $("#shipping_country").change(function () {

    CheckState(

      $("#shipping_country").val(),

      shipping_state_div,

      shipping_dp_state_div,

      shipping_state_dp,

      shipping_state

    );

    var pincode = $("#shipping_pincode").val();

    var country = $("#shipping_country").val();

    changeCountryBasedOnPostalCode(pincode, country, "#shipping_country");

  });



  CheckAddressValMax(

    $("#billing_country").val(),

    "#billing_address",

    "#billing_address_1"

  );



  $("#billing_country").change(function () {

    CheckAddressValMax(

      $("#billing_country").val(),

      "#billing_address",

      "#billing_address_1"

    );

  });



  CheckAddressValMax(

    $("#shipping_country").val(),

    "#shipping_address",

    "#shipping_address_1"

  );



  $("#shipping_country").change(function () {

    CheckAddressValMax(

      $("#shipping_country").val(),

      "#shipping_address",

      "#shipping_address_1"

    );

  });



  $("#order-frm").validate({

    ignore: ":hidden",

    rules: {

      address_options: {

        required: true,

      },

      billing_address_options: {

        required: true,

      },

      billing_state: {

        required: true,

      },



      // billing_first_name: {

      //   required: function () {

      //     if (

      //       $("input[name='billing_address_options']:checked").val() == "new"

      //     ) {

      //       return true;

      //     } else {

      //       return false;

      //     }

      //   },

      //   lettersonly: true,

      // },

      // billing_last_name: {

      //   required: function () {

      //     if (

      //       $("input[name='billing_address_options']:checked").val() == "new"

      //     ) {

      //       return true;

      //     } else {

      //       return false;

      //     }

      //   },

      //   lettersonly: true,

      // },



      billing_address: {

        required: function () {

          if (

            $("input[name='billing_address_options']:checked").val() == "new"

          ) {

            return true;

          } else {

            return false;

          }

        },

        ValMaxCharCheck_billing: true,

      },



      billing_address_1: {

        required: function () {

          if (

            $("input[name='billing_address_options']:checked").val() == "new"

          ) {

            return true;

          } else {

            return false;

          }

        },

        ValMaxCharCheck_billing: true,

      },

      billing_city: {

        required: function () {

          if (

            $("input[name='billing_address_options']:checked").val() == "new"

          ) {

            return true;

          } else {

            return false;

          }

        },

        // alphaSpace: true,

      },

      /*

        billing_state: {

          required: function(){

            if($("input[name='billing_address_options']:checked").val()=='new'){

              return true;

            }else{

              return false;

            }

          },

        },	*/



      billing_pincode: {

        required: function () {

          if (

            $("input[name='billing_address_options']:checked").val() == "new"

          ) {

            return true;

          } else {

            return false;

          }

        },

        Pin_noCheck: true,

        CheckPinCodeBill: true,

      },

      billing_country: {

        required: function () {

          if (

            $("input[name='billing_address_options']:checked").val() == "new"

          ) {

            return true;

          } else {

            return false;

          }

        },

      },

      billing_email_id: {

        required: function () {

          if (

            $("input[name='billing_address_options']:checked").val() == "new"

          ) {

            return true;

          } else {

            return false;

          }

        },

        email: true,

      },

      billing_mobile_no: {

        required: function () {

          if (

            $("input[name='billing_address_options']:checked").val() == "new"

          ) {

            return true;

          } else {

            return false;

          }

        },

        validate_phone: true,

      },



      // shipping_first_name: {

      //   required: function () {

      //     if ($("input[name='address_options']:checked").val() == "new") {

      //       return true;

      //     } else {

      //       return false;

      //     }

      //   },

      //   lettersonly: true,

      // },

      // shipping_last_name: {

      //   required: function () {

      //     if ($("input[name='address_options']:checked").val() == "new") {

      //       return true;

      //     } else {

      //       return false;

      //     }

      //   },

      //   lettersonly: true,

      // },



      shipping_address: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

        ValMaxCharCheck_shipping: true,

      },

      shipping_address_1: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

        ValMaxCharCheck_shipping: true,

      },

      shipping_city: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

        alphaSpace: true,

      },

      s_state_dp: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

      },



      shipping_state: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

      },



      shipping_pincode: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

        Pin_noCheck: true,

        // Pin_noCheckShipping_Country: true,

        CheckPinCodeShip: true,

      },

      shipping_country: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

      },

      shipping_mobile_no: {

        required: function () {

          if ($("input[name='address_options']:checked").val() == "new") {

            return true;

          } else {

            return false;

          }

        },

        validate_phone: true,

      },

      payment_method: {

        required: true,

      },

    },

    errorPlacement: function (label, element) {

      if (element.attr("name") == "payment_method") {

        label.appendTo("#check_pay_error");

      } else {

        label.insertAfter(element);

      }

    },

    errorClass: "error text-danger",

    messages: {

      //password : {"minlength":"Please enter 6 or more characters."},

    },

  });



$.validator.addMethod(
    "CheckPinCodeBill",
    function (value, element) {
        var country = $("#billing_country").val();
        return checkPinBaseOnCountry(value, country);
    },
    "Invalid Pin code"
);

$.validator.addMethod(
    "CheckPinCodeShip",
    function (value, element) {
        var country = $("#shipping_country").val();
        return checkPinBaseOnCountry(value, country);
    },
    "Invalid Pin code"
);



  $.validator.addMethod(

    "Pin_noCheck",

    function (value, element) {

      var country = $("#billing_country").val();

      result = changeCountryBasedOnPostalCode(

        value,

        country,

        "#billing_country"

      );

      return true;

    },

    "Invalid Pin code"

  );



  $.validator.addMethod(

    "Pin_noCheckShipping_Country",

    function (value, element) {

      var country = $("#shipping_country").val();

      changeCountryBasedOnPostalCode(value, country, "#shipping_country");

      return true;

    },

    "pin"

  );

  $.validator.addMethod(

    "noSpace",

    function (value, element) {

      return value.indexOf(" ") < 0 && value != "";

    },

    "No space please and don't leave it empty"

  );



  $.extend($.validator.messages, {

    lettersonly: "Alphabetic characters only and no spcae please",

  });



  // $.validator.addMethod(
  //   "alphaSpace",
  //   function(value, element) {
  //     value = $.trim(value);
  //     // Allow only letters, spaces, hyphens, apostrophes
  //     return this.optional(element) || /^[\p{L}\s\-']+$/u.test(value);
  //   },
  //   "Please enter a valid city name."
  // );

  // Add validation to city dropdown
  // $("#billing_city").rules("add", {
  //     required: true,
  //     alphaSpace: true
  // });

  // Trigger validation on change
  $("#billing_city").on("change", function () {
      $(this).valid();
  });






  var address_validation_mssg = "";



  var address_mssg = function () {

    return address_validation_mssg;

  };

  $.validator.addMethod(

    "ValMaxCharCheck_billing",

    function (value, element) {

      var country = $("#billing_country").val();

      if (

        (country == "IN" && value.length > 150) ||

        (country == "" &&

          (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4) &&

          value.length > 150)

      ) {

        address_validation_mssg = "Please enter no more than 150 characters.";

        return false;

      } else if (

        country == "" &&

        (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4)

      ) {

        return true;

      } else if (country != "IN" && value.length > 35) {

        address_validation_mssg = "Please enter no more than 35 characters.";

        return false;

      } else return true;

    },

    address_mssg

  );



  $.validator.addMethod(

    "ValMaxCharCheck_shipping",

    function (value, element) {

      var country = $("#shipping_country").val();

      if (

        (country == "IN" && value.length > 150) ||

        (country == "" &&

          (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4) &&

          value.length > 150)

      ) {

        address_validation_mssg = "Please enter no more than 150 characters.";

        return false;

      } else if (

        country == "" &&

        (SHOP_FLAG == 2 || SHOP_FLAG == 1 || SHOP_FLAG == 4)

      ) {

        return true;

      } else if (country != "IN" && value.length > 35) {

        address_validation_mssg = "Please enter no more than 35 characters.";

        return false;

      } else return true;

    },

    address_mssg

  );



  $.validator.addMethod(

    "validate_phone",

    function (phone_number, element) {

      phone_number = phone_number.replace(/\s+/g, "");

      return (

        this.optional(element) ||

        (phone_number.length <= 10 &&

          phone_number.match(

            /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/

          ))

      );

    },

    "Please specify a valid phone number."

  );



  $.validator.addMethod(

    "vat_noCheck",

    function (val, elem) {

      $("#vat_no-error").remove();

      if (val == "") {

        return true;

      }



      console.log(event.target.id);

      if (event.target.id == "billing-address-save") {

        if (

          event.type == "click" ||

          event.type == "submit" ||

          event.type == "blur"

        ) {

          $(".loaderDiv").show();



          valid = false;

          $(".common_vat_no").val(val);

          $("#vat_no-error").remove();

          $("#vat_no-success").remove();

          $("#vatFlag").val("0");

          $.ajax({

            url: BASE_URL + "CheckoutController/checkvatAlreadyExits",

            type: "ajax",

            method: "POST",

            dataType: "json",

            data: { vat_no: val },

            async: false,

            beforeSend: function () {

              $(".loaderDiv").show();

            },

            success: function (response) {

              if (response.flag == 0) {

                var vat_no = val;

                var final_res = response.result;



                if (final_res.response_type == 0) {

                  var url = BASE_URL + "index-vat.php";

                  $("#vat_no-error").remove();

                  $("#vat_no-success").remove();

                  $.ajax({

                    url: url,

                    type: "ajax",

                    method: "POST",

                    dataType: "json",

                    data: { vat_no: vat_no },

                    async: false,

                    success: function (response) {

                      var address_id = $("#address_id").val();

                      if (response.flag == 0) {

                        if (response.validitiy == "Valid") {

                          $(".common_vat_no").val(vat_no);

                          $(".consulation_no").val(response.Identifier);

                          $(".res_company_name").val(response.Company_name);

                          $(".res_company_address").val(

                            response.Company_address

                          );



                          addingVatLog(val, response, 1, address_id);

                          $("#vatFlag").val("1");

                          valid = true;



                          var billing_address_options = $(

                            "input[name='billing_address_options']:checked"

                          ).val();



                          if (billing_address_options == "new") {

                            $(

                              '<label id="vat_no-success" class="success-msg" for="vat_no"> ' +

                                vat_no +

                                " is valid. </label>"

                            ).insertAfter(".loaderDiv");

                            $(".loaderDiv").hide();

                          } else {

                            swal("Success", val + " is valid.", "success");

                            //$( '<label id="vat_no-success" class="success-msg" for="vat_no"> '+ val + ' is valid. </label>' ).insertBefore( "#order-frm" );

                          }

                        } else {

                          addingVatLog(val, response, 2, address_id);

                          valid = true;



                          var billing_address_options = $(

                            "input[name='billing_address_options']:checked"

                          ).val();



                          if (billing_address_options == "new") {

                            $(

                              '<label id="vat_no-error" class="error-msg" for="vat_no"> ' +

                                vat_no +

                                " is not valid. Vat will be charged.</label>"

                            ).insertAfter(".loaderDiv");

                          } else {

                            swal(

                              "Error",

                              val + " is not valid. Vat will be charged",

                              "warning"

                            );

                            //$( '<label id="vat_no-error" class="error-msg" for="vat_no"> '+ vat_no + ' is not valid. Vat will be charged.</label>' ).insertBefore( "#order-frm" );

                          }

                        }

                      } else {

                        addingVatLog(val, response, 0, address_id);

                        valid = true;



                        var billing_address_options = $(

                          "input[name='billing_address_options']:checked"

                        ).val();



                        if (billing_address_options == "new") {

                          $(

                            '<label id="vat_no-error" class="error-msg" for="vat_no"> ' +

                              response.msg +

                              ". Vat will be charged.</label>"

                          ).insertAfter(".loaderDiv");



                          $(".loaderDiv").hide();

                        } else {

                          //$( '<label id="vat_no-error" class="error-msg" for="vat_no"> '+ response.msg + '. Vat will be charged.</label>' ).insertBefore( "#order-frm" );

                          swal(

                            "Error",

                            response.msg + ". Vat will be charged",

                            "warning"

                          );

                        }

                      }

                    },

                  });

                } else if (final_res.response_type == 2) {

                  var billing_address_options = $(

                    "input[name='billing_address_options']:checked"

                  ).val();

                  valid = true;



                  if (billing_address_options == "new") {

                    $(

                      '<label id="vat_no-error" class="error-msg" for="vat_no"> ' +

                        vat_no +

                        " is not valid. Vat will be charged.</label>"

                    ).insertAfter(".loaderDiv");



                    $(".loaderDiv").hide();

                  } else {

                    //$( '<label id="vat_no-error" class="error-msg" for="vat_no"> '+ vat_no + ' is not valid. Vat will be charged.</label>' ).insertBefore( "#order-frm" );

                    swal(

                      "Error",

                      vat_no + " is not valid. Vat will be charged",

                      "warning"

                    );

                  }

                } else if (final_res.response_type == 1) {

                  $(".common_vat_no").val(vat_no);

                  $(".consulation_no").val(final_res.consulation_no);

                  $(".res_company_name").val(final_res.company_name);

                  $(".res_company_address").val(final_res.company_address);

                  $("#vatFlag").val("1");

                  valid = true;



                  var billing_address_options = $(

                    "input[name='billing_address_options']:checked"

                  ).val();



                  if (billing_address_options == "new") {

                    $.validator.messages.vat_noCheck = "";

                    $(

                      '<label id="vat_no-success" class="success-msg" for="vat_no"> ' +

                        vat_no +

                        " is valid. </label>"

                    ).insertAfter(".loaderDiv");

                    //valid = true;

                    $(".loaderDiv").hide();

                  } else {

                    //$( '<label id="vat_no-success" class="success-msg" for="vat_no"> '+ vat_no + ' is valid. </label>' ).insertBefore( "#order-frm" );



                    swal("Success", val + " is valid.", "success");

                  }

                }



                return valid;

              } else {

                valid = false;

                $.validator.messages.vat_noCheck = "";

                $("#vat_no-error").remove();

                $("#vat_no-success").remove();

                var url = BASE_URL + "index-vat.php";

                var vat_no = val;

                $.ajax({

                  url: url,

                  type: "ajax",

                  method: "POST",

                  dataType: "json",

                  data: { vat_no: vat_no },

                  async: false,

                  success: function (response) {

                    var address_id = $("#address_id").val();

                    if (response.flag == 0) {

                      if (response.validitiy == "Valid") {

                        $(".common_vat_no").val(vat_no);



                        $(".consulation_no").val(response.Identifier);

                        $(".res_company_name").val(response.Company_name);

                        $(".res_company_address").val(response.Company_address);

                        $("#vatFlag").val("1");

                        addingVatLog(val, response, 1, address_id);



                        var billing_address_options = $(

                          "input[name='billing_address_options']:checked"

                        ).val();



                        if (billing_address_options == "new") {

                          $.validator.messages.vat_noCheck = "";

                          $(

                            '<label id="vat_no-success" class="success-msg" for="vat_no"> ' +

                              vat_no +

                              " is valid. </label>"

                          ).insertAfter(".loaderDiv");

                          valid = true;

                          $(".loaderDiv").hide();

                        } else {

                          swal("Success", val + " is valid.", "success");

                          //$( '<label id="vat_no-success" class="success-msg" for="vat_no"> '+ vat_no + ' is valid. </label>' ).insertBefore( "#order-frm" );

                          valid = true;

                        }

                      } else {

                        addingVatLog(val, response, 2, address_id);



                        var billing_address_options = $(

                          "input[name='billing_address_options']:checked"

                        ).val();



                        if (billing_address_options == "new") {

                          $(

                            '<label id="vat_no-error" class="error-msg" for="vat_no"> ' +

                              vat_no +

                              " is not valid. Vat will be charged.</label>"

                          ).insertAfter(".loaderDiv");

                          valid = true;

                          $(".loaderDiv").hide();

                        } else {

                          //$( '<label id="vat_no-error" class="error-msg" for="vat_no"> '+ vat_no + ' is not valid. Vat will be charged.</label>' ).insertBefore( "#order-frm" );

                          swal(

                            "Error",

                            val + " is not valid. Vat will be charged",

                            "warning"

                          );

                          valid = true;

                        }

                      }

                    } else {

                      addingVatLog(val, response, 0, address_id);



                      var billing_address_options = $(

                        "input[name='billing_address_options']:checked"

                      ).val();



                      if (billing_address_options == "new") {

                        $(

                          '<label id="vat_no-error" class="error-msg" for="vat_no"> ' +

                            response.msg +

                            ". Vat will be charged.</label>"

                        ).insertAfter(".loaderDiv");

                        valid = true;

                        $(".loaderDiv").hide();

                      } else {

                        swal(

                          "Error",

                          response.msg + ". Vat will be charged",

                          "warning"

                        );

                        //$( '<label id="vat_no-error" class="error-msg" for="vat_no"> '+ vat_no + ' is not valid. Vat will be charged.</label>' ).insertBefore( "#order-frm" );

                        valid = true;

                      }

                    }

                  },

                });



                return valid;

              }

            },

          });



          return valid;

        }

      } else {

        return true;

      }

    },

    $.validator.messages.vat_noCheck

  );



  var form = $("#order-frm");

  $("#billing-address-save").click(function (event) {

    if ($("input[name='select_checkout_method']:checked").val() == "guest") {

    }



    if (form.valid() === true) {

      SaveQuoteAddress(1);



      var same_as_billing = $("input[name='same_as_billing']:checked").val();



      if (same_as_billing == 1) {

        var temp_bill_country = $("#temp_bill_country").val();

        if (temp_bill_country != "") {

          var temp_bill_country = $("#temp_bill_country").val();

        } else {

          var temp_bill_country = $("#billing_country").val();

        }



        var billing_address_options = $(

          "input[name='billing_address_options']:checked"

        ).val();

        $("#temp_bill_country").val(

          $('input[name="billing_address_options"]:checked').data("country")

        );



        var billing_country = $("#billing_country").val(); // "MU"
        var allowed_ship_country = $("#allowed_ship_country").val(); // e.g., "MU,US,CA"

        if (allowed_ship_country != "") {

          if (allowed_ship_country.includes(",")) {

            var res_country = allowed_ship_country.split(",");

          } else {

            var res_country = allowed_ship_country;

          }



          if (billing_address_options == "new") {

            var checkCountryExist = res_country.includes(billing_country);

            if (checkCountryExist == false) {

              swal(

                "Error",

                "Shipping does not allowed to this country, Please select proper country.",

                "error"

              );

              return false;

            }

          } else {

            var flag_allowed = CheckBillingShippingCountry(

              billing_address_options

            );



            if (flag_allowed == false) {

              return false;

            }

          }

          //console.log('shipping_exist------'+checkCountryExist);

        }



        if (billing_address_options == "new") {

          console.log("");



          $(

            "input[name=address_options][value=" + billing_address_options + "]"

          ).attr("checked", "checked");

          SameAsBillingAddress("new");

        } else {

          $(

            "input[name=address_options][value=" + billing_address_options + "]"

          ).attr("checked", "checked");

        }



        $("#bill-add-tab").attr("disabled", false);

        $("#collapseTwo").removeClass("show");

        $("#collapseTwo").removeClass("in");



        $("#address-save").click();

      } else {

        if ($(".single-payment").length > 0) {

          $("#check_pay_error").html("");

        } else {

          $("#check_pay_error").html("No payment method available.");

          $("#check_pay_error").addClass("error");

          $("#payment-save").attr("disabled", true);

          $("#place_order").attr("disabled", true);

        }



        $("#bill-add-tab").attr("disabled", false);

        $("#payment-tab").attr("disabled", true);



        $("#collapseTwo").removeClass("show");

        $("#collapseTwo").removeClass("in");

        $("#collapseTwoNew").addClass("show");

      }

    } else {

      $("#payment-tab").attr("disabled", true);

      $("#place_order").attr("disabled", true);

      $("#collapseTwo").addClass("show");

      $("#collapseTwoNew").removeClass("show");

      $("#collapseThree").removeClass("show");

      return false;

    }

  });



  var form = $("#order-frm");



  $("#address-save").click(function (event) {

    if ($("input[name='select_checkout_method']:checked").val() == "guest") {

    }



    if (form.valid() === true) {

      SaveQuoteAddress(2);



      RefreshOrderTotals();

      //RefreshOrderSidebar();

      if (SHOP_FLAG == 2 || SHOP_FLAG == 4) {

        RefreshPaymentMethods();

      }



      if ($(".single-payment").length > 0) {

        $("#check_pay_error").html("");

      } else {

        $("#check_pay_error").html("No payment method available.");

        $("#check_pay_error").addClass("error");

        $("#payment-save").attr("disabled", true);

        $("#place_order").attr("disabled", true);

      }



      $("#ship-add-tab").attr("disabled", false);

      $("#payment-tab").attr("disabled", false);



      $("#collapseTwoNew").removeClass("show");

      $("#collapseThree").addClass("show");

    } else {

      $("#payment-tab").attr("disabled", true);

      $("#place_order").attr("disabled", true);

      $("#collapseTwoNew").addClass("show");

      $("#collapseThree").removeClass("show");

      return false;

    }

  });



  $("#payment-save").click(function (event) {

    if (

      form.valid() === true &&

      $("input[type='radio'][name='payment_method']").is(":checked")

    ) {

      SaveQuotePaymentMethod();

      RefreshOrderSidebar(); //cod

      RefreshOrderTotals();

      $("#payment_warning").css("display", "hide");

      $("#overview-tab").attr("disabled", false);

      $("#collapseThree").removeClass("show");

      $("#collapseFour").addClass("show");



      $("#place_order").attr("disabled", false);



      //$('#overview-tab').click();

    } else {

      $("#payment_warning").css("display", "block");

      $("#overview-tab").attr("disabled", true);

      $("#place_order").attr("disabled", true);

      $("#collapseThree").addClass("show");

      $("#collapseFour").removeClass("show");

      return false;

    }

  });

});



function SetCheckoutMethod(value) {

  $("#checkout_method").val(value);



  if (value == "guest") {

    $(".guestTnc").removeClass("hide");

    $(".sigin-member").addClass("d-none");

    $("#checkmethod-tab").show();



    SetCheckoutMethodAPI(value);

  } else if (value == "login") {

    $(".guestTnc").addClass("hide");

    $(".sigin-member").addClass("d-none");

    $("#login-block").removeClass("d-none");

    $("#checkmethod-tab").hide();

  } else if (value == "register") {

    $(".guestTnc").addClass("hide");

    $(".sigin-member").addClass("d-none");

    $("#register-block").removeClass("d-none");

    $("#checkmethod-tab").hide();

  } else {

    return false;

  }

}



function ProceedBilling() {

  if ($("#agree_chk_guest").prop("checked") == true) {

    $("#agree_chk-error-guest").html("");

    $("#order-frm").validate();

    $("#bill-add-tab").removeClass("d-none");

    $("#checkout-register").addClass("d-none");

    $("#bill-add-tab").attr("disabled", false);

    $("#bill-add-tab").click();

    $(".custom-address-billing").removeClass("d-none");

    $("#billing_address_options").val("new");

  } else {

    $("#agree_chk-error-guest").html("This field is required");

    return false;

  }

}



function ProceedShipping() {

  $("#order-frm").validate();

  $("#ship-add-tab").removeClass("d-none");

  $("#checkout-register").addClass("d-none");

  $("#ship-add-tab").attr("disabled", false);

  $("#ship-add-tab").click();

  $(".custom-address-shipping").removeClass("d-none");

  $("#address_options").val("new");

}



function SetAddressId(value, address_country = "", shop_country = "") {

  if (value != "") {

    if (value == "new") {

      resetShippingForm();

      $(".custom-address-shipping").removeClass("d-none");

    } else {

      /* if(address_country!=shop_country){

        swal('Error','Country not matched with shop country, Please select proper country.','error');

        $('[name="address_options"]').prop('checked', false);

        return false;



      }else{

        $('.custom-address-shipping').addClass('d-none');

      } */



      var allowed_ship_country = $("#allowed_ship_country").val();

      if (allowed_ship_country != "") {

        if (allowed_ship_country.includes(",")) {

          var res_country = allowed_ship_country.split(",");

        } else {

          var res_country = allowed_ship_country;

        }



        var checkCountryExist = res_country.includes(address_country);

        if (checkCountryExist == false) {

          swal(

            "Error",

            "Shipping does not allowed to this country, Please select proper country.",

            "error"

          );

          $('[name="address_options"]').prop("checked", false);

          return false;

        } else {

          $(".custom-address-shipping").addClass("d-none");

        }

        //console.log('shipping_exist------'+checkCountryExist);

      }

    }

  } else {

    return false;

  }

}



function resetShippingForm() {

  $("#shipping_first_name").val("");

  $("#shipping_last_name").val("");

  $("#shipping_address").val("");

  $("#shipping_address_1").val("");

  $("#shipping_mobile_no").val("");

  $("#shipping_country").val("");

  $("#shipping_pincode").val("");

  $("#shipping_state").val("");

  $("#s_state_dp").val("");

  $("#shipping_city").val("");

}



function SetBillingAddressId(value) {

  if (value != "") {

    if (value == "new") {

      resetBillingForm();

      $(".custom-address-billing").removeClass("d-none");

    } else {

      $(".custom-address-billing").addClass("d-none");

    }

  } else {

    return false;

  }

}



function resetBillingForm() {

  $("#billing_first_name").val("");

  $("#billing_last_name").val("");

  $("#billing_address").val("");

  $("#billing_address_1").val("");

  $("#billing_email_id").val("");

  $("#billing_mobile_no").val("");

  $("#billing_country").val("");

  $("#billing_pincode").val("");

  $("#billing_state").val("");

  $("#b_state_dp").val("");

  $("#billing_city").val("");

}



function SetCheckoutMethodAPI(value) {

  if (value == "guest") {

    $.ajax({

      url: BASE_URL + "CheckoutController/setCheckoutMethod",

      type: "POST",

      data: { checkout_method: value },

      method: "POST",

      success: function (response) {

        if (response == "success") {

        } else {

          return false;

        }

      },

    });

  } else {

    return false;

  }

}



function ChangeVatSession(country_code) {

  $.ajax({

    url: BASE_URL + "CheckoutController/CheckoutSetVatSession",

    type: "POST",

    dataType: "json",

    data: { shipping_country: country_code },

    success: function (response) {

      //console.log(response);

      if (response.flag == 0 && response.msg == "Success") {

        RefreshCheckoutListing();

      }

    },

  });

}



function RefreshCheckoutListing() {

  $.ajax({

    url: BASE_URL + "CheckoutController/CheckoutListing",

    type: "POST",

    async: false,

    dataType: "html",

    data: {},

    success: function (response) {

      //console.log(response);

      if (response != "error") {

        $(".cart-left-box-block").html(response);

      } else {

        return false;

      }

    },

  });

}



function SaveQuoteAddress(address_type) {

  if (address_type == 1) {

    if ($("#hidden_company_name").val() == "") {

      company_name = $("#company_name").val();

    } else {

      company_name = $("#hidden_company_name").val();

    }



    vat_no = $("#vat_no").val();

    consulation_no = $("#consulation_no").val();

    res_company_name = $("#res_company_name").val();

    res_company_address = $("#res_company_address").val();



    vat_vies_valid_flag = $("#vatFlag").val();



    var billing_address_options = $(

      "input[name='billing_address_options']:checked"

    ).val();



    var billing_first_name = $("#billing_first_name").val();

    var billing_last_name = $("#billing_last_name").val();

    var billing_address = $("#billing_address").val();

    var billing_address_1 = $("#billing_address_1").val();

    var billing_email_id = $("#billing_email_id").val();

    var billing_mobile_no = $("#billing_mobile_no").val();

    var billing_country = $("#billing_country").val();

    var billing_state = "";

    if (billing_country == "MU") {
      // Get the selected state's ID instead of name
      billing_state = $("#b_state_dp option:selected").text().trim(); 
    } else {
      billing_state = $("#billing_state").val();
    }

    var billing_pincode = $("#billing_pincode").val();

    // var billing_state=$('#billing_state').val();

    var billing_city = $("#billing_city").val();

    var save_in_address_book = $(

      "input[name='billing_save_in_address_book']:checked"

    ).val();



    $.ajax({

      url: BASE_URL + "CheckoutController/saveQuoteAddress",

      type: "POST",

      dataType: "json",

      data: {

        billing_first_name: billing_first_name,

        billing_last_name: billing_last_name,

        billing_address: billing_address,

        billing_address_1: billing_address_1,

        billing_email_id: billing_email_id,

        billing_mobile_no: billing_mobile_no,

        billing_country: billing_country,

        billing_pincode: billing_pincode,

        billing_state: billing_state,

        billing_city: billing_city,

        save_in_address_book: save_in_address_book,

        billing_address_options: billing_address_options,

        address_type: address_type,

        company_name: company_name,

        vat_no: vat_no,

        consulation_no: consulation_no,

        res_company_name: res_company_name,

        res_company_address: res_company_address,

        vat_vies_valid_flag: vat_vies_valid_flag,

      },



      success: function (response) {

        if (response.flag == 1) {

          $("#hidden_email_id").val(response.email_id);

          $("#hidden_mobile_no").val(response.mobile_no);

          return true;

        } else {

          //swal('Error',response.msg,'error');

          //	return false;

        }

      },

    });

  } else if (address_type == 2) {

    var address_options = $("input[name='address_options']:checked").val();



    var same_as_billing = $("input[name='same_as_billing']:checked").val();

    var shipping_first_name = $("#shipping_first_name").val();

    var shipping_last_name = $("#shipping_last_name").val();

    var shipping_address = $("#shipping_address").val();

    var shipping_address_1 = $("#shipping_address_1").val();

    var shipping_mobile_no = $("#shipping_mobile_no").val();

    var shipping_country = $("#shipping_country").val();

    var shipping_pincode = $("#shipping_pincode").val();



    if (same_as_billing == 1) {

      var shipping_company_name = $("#company_name").val();

    } else {

      var shipping_company_name = $("#Shippingcompany_name").val();

    }

    

    var shipping_state = "";

    // if (shipping_country == "IN") {

    //   var shipping_state = $("#s_state_dp").val();

    // } else {

    //   var shipping_state = $("#shipping_state").val();

    // }

    if (shipping_country == "MU") {
      // Get the selected state's ID instead of name
      shipping_state = $("#b_state_dp option:selected").text().trim(); 
    } else {
      shipping_state = $("#shipping_state").val();
    }
    console.log(shipping_state);
    // var shipping_state=$('#shipping_state').val();

    var shipping_city = $("#shipping_city").val();

    var save_in_address_book = $(

      "input[name='save_in_address_book']:checked"

    ).val();



    $.ajax({

      url: BASE_URL + "CheckoutController/saveQuoteAddress",

      type: "POST",

      dataType: "json",

      data: {

        shipping_first_name: shipping_first_name,

        shipping_last_name: shipping_last_name,

        shipping_address: shipping_address,

        shipping_address_1: shipping_address_1,

        shipping_mobile_no: shipping_mobile_no,

        shipping_country: shipping_country,

        shipping_pincode: shipping_pincode,

        shipping_state: shipping_state,

        shipping_city: shipping_city,

        save_in_address_book: save_in_address_book,

        address_options: address_options,

        address_type: address_type,

        same_as_billing: same_as_billing,

        company_name: shipping_company_name,

      },



      success: function (response) {

        if (response.flag == 1) {

          $("#hidden_email_id").val(response.email_id);

          $("#hidden_mobile_no").val(response.mobile_no);



          return true;

        } else {

          //swal('Error',response.msg,'error');

          //return false;

        }

      },

    });

  }

}



function SaveQuotePaymentMethod() {

  var payment_id = $("input[name='payment_method']:checked").val();

  var payment_type = $("#payment_type_" + payment_id).val();



  $.ajax({

    url: BASE_URL + "CheckoutController/saveQuotePaymentMethod",

    type: "POST",

    dataType: "json",

    data: { payment_id: payment_id, payment_type: payment_type },



    success: function (response) {

      if (response.flag == 1) {

        return true;

      } else {

        swal("Error", response.msg, "error");

        return false;

      }

    },

  });

}



function RefreshOrderTotals() {

  $.ajax({

    url: BASE_URL + "CheckoutController/refreshOrderTotals",

    type: "POST",

    dataType: "html",

    async: false,

    data: {},

    success: function (response) {

      if (response != "error") {

        RefreshOrderSidebar(); //cod

        $("#shopping-total").html(response);

      } else {

        return false;

      }

    },

  });

}



function RefreshOrderSidebar() {

  $.ajax({

    url: BASE_URL + "CheckoutController/refreshSidebar",

    type: "POST",

    async: false,

    dataType: "html",

    data: {},

    success: function (response) {

      if (response != "error") {

        $("#checkout-sidebar").html(response);

      } else {

        return false;

      }

    },

  });

}



function RefreshPaymentMethods() {

  $(

    '<span class="spinner-border spinner-border-sm" id="payment-loader" role="status" aria-hidden="true">Please Wait...</span>'

  ).insertBefore("#payment-save");

  $("#payment-save").addClass("disabled_payment_continue");

  $("#payment-save").prop("disabled", true);



  $.ajax({

    url: BASE_URL + "CheckoutController/refreshPaymentMethods",

    type: "POST",

    dataType: "html",



    data: {},

    success: function (response) {

      if (response != "error") {

        $("#payment-loader").remove();

        $("#payment-save").removeClass("disabled_payment_continue");

        $("#payment-save").prop("disabled", false);

        $("#checkout-payment").html(response);

      } else {

        return false;

      }

    },

  });

}



function SameAsBillingAddress(address_type) {

  if (address_type == "new") {

    SetAddressId("new");



    var shipping_state_div = ".s_state_div";

    var shipping_dp_state_div = ".s_state_dp_div";

    var shipping_state_dp = "#s_state_dp";

    var shipping_state = "#shipping_state";

    var billing_state_dp = "#b_state_dp";

    var billing_state = "#billing_state";



    CheckState(

      $("#billing_country").val(),

      shipping_state_div,

      shipping_dp_state_div,

      shipping_state_dp,

      shipping_state,

      1,

      billing_state_dp,

      billing_state

    );



    $("#shipping_first_name").val($("#billing_first_name").val());

    $("#shipping_last_name").val($("#billing_last_name").val());

    $("#shipping_address").val($("#billing_address").val());

    $("#shipping_address_1").val($("#billing_address_1").val());

    $("#shipping_mobile_no").val($("#billing_mobile_no").val());

    $("#shipping_country").val($("#billing_country").val()).change();

    $("#shipping_pincode").val($("#billing_pincode").val());

    $("#Shippingcompany_name").val($("#company_name").val());

    //$('#shipping_state').val($('#billing_state').val());

    $("#shipping_city").val($("#billing_city").val());

  } else {

    $("#shipping_first_name").val("");

    $("#shipping_last_name").val("");

    $("#shipping_address").val("");

    $("#shipping_address_1").val("");

    $("#shipping_mobile_no").val("");

    $("#shipping_country").val("");

    $("#shipping_pincode").val("");

    $("#shipping_state").val("");

    $("#shipping_city").val("");

  }

}



function SetBillingCountry(value, country, vat_no, company_name) {

  $("#temp_bill_country").val(country);

  $(".common_vat_no").val(vat_no);

  $("#hidden_company_name").val(company_name);

}



function CheckBillingShippingCountry(billing_address_options = "") {

  console.log("here");

  var flag = true;

  var billing_country = $("#temp_bill_country").val();

  var allowed_ship_country = $("#allowed_ship_country").val();

  if (allowed_ship_country != "") {

    if (allowed_ship_country.includes(",")) {

      var res_country = allowed_ship_country.split(",");

    } else {

      var res_country = allowed_ship_country;

    }



    var checkCountryExist = res_country.includes(billing_country);

    if (checkCountryExist == false) {

      swal(

        "Error",

        "Shipping does not allowed to this country, Please select proper country.",

        "error"

      );

      flag = false;

    } else {

      flag = true;

    }

    //console.log('shipping_exist------'+checkCountryExist);

  }



  return flag;

}



/*start cod otp */

function otpVerifiedSuccess(order_id) {

  $.ajax({

    url: BASE_URL + "CheckoutController/otpVerifiedSuccess",

    type: "POST",

    dataType: "json",

    data: { order_id: order_id },

    success: function (response) {},

  });

}



function ValidOTP() {

  $("#valid-otp-btn").attr("disabled", "disabled");

  document.getElementById("error-msg").className = "error-red";

  var otp_password = document.getElementById("otp_password").value; //alert(otp_password);

  var order_id = document.getElementById("order_id").value; //alert(order_id);

  var phone_no = document.getElementById("phone_no").value;



  var numericExpression = /^[0-9]+$/;



  if (otp_password == "") {

    //jQuery("#Loader").hide();

    document.getElementById("otp_password").value = "";

    document.getElementById("otp_password").style.border =

      "1px solid #eb340a !important";

    document.getElementById("otp_password").style.background =

      "#faebe7 !important";

    document.getElementById("error-msg").innerHTML =

      "This is a required field.";

    $("#valid-otp-btn").removeAttr("disabled");

    return false;

  } else if (!otp_password.match(numericExpression)) {

    jQuery("#Loader").hide();

    document.getElementById("otp_password").value = "";

    document.getElementById("otp_password").style.border =

      "1px solid #eb340a !important";

    document.getElementById("otp_password").style.background =

      "#faebe7 !important";

    document.getElementById("error-msg").innerHTML =

      "Please enter numbers only.";

    $("#valid-otp-btn").removeAttr("disabled");

    return false;

  } else if (otp_password) {

    // else if(otp_generate == otp_password)

    document.getElementById("error-msg").innerHTML = "";

    document.getElementById("success-msg").innerHTML = "";

    $.ajax({

      url: BASE_URL + "CheckoutController/otpVerifiedMethod",

      type: "POST",

      dataType: "json",

      data: {

        order_id: order_id,

        otp_password: otp_password,

        phone_no: phone_no,

      },

      success: function (response) {

        if (response.flag == 1) {

          swal("Success", response.msg, "success");

          otpVerifiedSuccess(order_id);

          $("#thankyouImg").removeClass("hide");

          $(".thanky").removeClass("hide");

          $(".thankyou-order-msg").removeClass("hide");

          $(".thankyou-email-msg").removeClass("hide");

          $(".continue-shop-btn").removeClass("hide");

          $(".cod").addClass("hide");

          $("#processImg").addClass("hide");

          $(".otp-div").addClass("hide");

          return true;

        } else {

          $("#valid-otp-btn").removeAttr("disabled");

          swal("Error", response.msg, "error");

          return false;

        }

      },

    });

  } else {

    //jQuery("#Loader").hide();

    document.getElementById("otp_password").value = "";

    document.getElementById("otp_password").style.border =

      "1px solid #eb340a !important";

    document.getElementById("otp_password").style.background =

      "#faebe7 !important";

    document.getElementById("error-msg").innerHTML =

      "Please enter valid OTP password.";

    $("#valid-otp-btn").removeAttr("disabled");

    return false;

  }

}



/*generate otp*/

//code for Regenerate OTP.

function OTP_regenerate() {

  document.getElementById("error-msg").className = "error-red";

  var order_id = document.getElementById("order_id").value;

  var phone_no = document.getElementById("phone_no").value;



  if (order_id && phone_no) {

    $.ajax({

      url: BASE_URL + "CheckoutController/otpRegenerateMethod",

      type: "POST",

      dataType: "json",

      data: { order_id: order_id, phone_no: phone_no },

      success: function (response) {

        if (response.flag == 1) {

          swal("Success", response.msg, "success");

          // otpVerifiedSuccess(order_id);

          return true;

        } else {

          swal("Error", response.msg, "error");

          return false;

        }

      },

    });

  }

}

/*end cod otp */



function addingVatLog(request, response, response_type, address_id = "") {

  var quote_id = $("#quote_id").val();



  var url = BASE_URL + "CheckoutController/addVatLogging";

  $.ajax({

    url: url,

    type: "ajax",

    method: "POST",

    dataType: "json",

    data: {

      request: request,

      response: response,

      response_type: response_type,

      address_id: address_id,

      type: 2,

      quote_id: quote_id,

    },

    success: function (response) {

      //console.log(response);

    },

  });

}



function openAddressPopup(flag, customer_id, address_id = "") {

  if (flag != "" && customer_id != "") {

    $.ajax({

      type: "POST",

      dataType: "html",

      url: BASE_URL + "CheckoutController/openAddressPopup",

      data: { flag: flag, customer_id: customer_id, address_id: address_id },

      //async:false,

      complete: function () {},

      beforeSend: function () {

        // $('#ajax-spinner').show();

      },

      success: function (response) {

        $("#WebShopCommonModal").modal();

        $("#modal-content").html(response);

      },

    });

  } else {

    return false;

  }

}

