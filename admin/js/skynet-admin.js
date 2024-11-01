(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(document).ready(function () {
    init_order_page();
    init_settings_page();
    init_auto_weight();
    
    if(window.location.href.includes("post-new.php") && window.location.href.includes("skynet_order_bulky="))
    {
        reprint_table();
    }
  });

  function init_order_page() {
    var adminpage = window.adminpage;
    var typenow = window.typenow;
    
    if (typenow !== "shop_order") {
      return;
    }

    if (adminpage !== "post-new-php" && adminpage !== "post-php") {
      return;
    }

    init_print_awb_meta_box();

    function init_print_awb_meta_box() {
      var $box = $("#skynet_printawb_meta_box");
    
      if (!$box.length) {
        return;
      }

      // print function
      $(document).on("click", "#btn_skynet_print_awb", function (e) {
        e.preventDefault();
		
		var weight	= document.getElementById("skynet_shipment_weight").value;
		var pieces	= document.getElementById("skynet_shipment_pieces").value;
		var type	= document.getElementById("skynet_shipment_type").value;
		var contents= document.getElementById("skynet_shipment_contents").value;
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;
		
		var error = "Please correct these errors:\n";

    if(length != 0 && width == 0 && height == 0)
    {
      error = error.concat(" - Width (kg): Please insert a width\n");
      error = error.concat(" - Height (kg): Please insert a height\n");
    }
    else if(length != 0 && width != 0 && height == 0)
    {
      error = error.concat(" - Height (kg): Please insert height\n");
    }
    else if(length != 0 && width == 0 && height != 0)
    {
      error = error.concat(" - Width (kg): Please insert width\n");
    }
    else if(length == 0 && width != 0 && height == 0)
    {
      error = error.concat(" - Length (kg): Please insert length\n");
      error = error.concat(" - Height (kg): Please insert height\n");
    }
    else if(length == 0 && width != 0 && height != 0)
    {
      error = error.concat(" - Length (kg): Please insert length\n");
    }
    else if(length == 0 && width == 0 && height != 0)
    {
      error = error.concat(" - Length (kg): Please insert length\n");
      error = error.concat(" - Width (kg): Please insert width\n");
    }
	
		if(weight)
		{
			if(parseFloat(weight) < 0.1)
				error = error.concat(" - Weight (kg): Must be greater than 0.1\n")
		}
		else
			error = error.concat(" - Weight (kg): Please insert a weight\n");
		
		if(pieces)
		{
			if(parseInt(pieces) < 1)
				error = error.concat(" - No. of Boxes: Must be greater than 0\n")
		}
		else
			error = error.concat(" - No. of Boxes: Please insert No. of Boxes\n");
		
		if(type == "")
			error = error.concat(" - Shipment Type: Please select Shipment Type\n")
		
		if(contents == "")
			error = error.concat(" - Contents: Please insert Contents details\n")
		
		if(error != "Please correct these errors:\n")
			alert(error);
		else
		{
			$box.block();

			var params = {
			  action: "skynet_print_awb_sticker",
			  order_id: woocommerce_admin_meta_boxes.post_id,
			  reprint: 0,
			};

			$.post(
			  woocommerce_admin_meta_boxes.ajax_url,
			  $.param(params) +
				"&" +
				$("#skynet_printawb_meta_box input, select").serialize() +
				"&" +
				$("#order_data input, select").serialize()
			)
			  .done(function (response) {
				if (response.success) {
				  const downloadLink = document.createElement("a");
				  const fileName = response.data.awbnumber + ".pdf";

				  downloadLink.href = response.data.pdfUrl;
				  downloadLink.target = "_blank";
				  downloadLink.download = fileName;
				  downloadLink.click();

				  $box.load(document.URL + " #skynet_printawb_meta_box > *");
				} else {
				  alert(response.data.message);
				}
			  })
			  .fail(function (data) {
				alert(
				  "Something went wrong, please try again later. \nError:" +
					data.statusText
				);
			  })
			  .always(function (data) {
				$box.unblock();
			  });
		}        
      });

      // reprint function
      $(document).on("click", "#btn_skynet_reprint_awb", function (e) {
        e.preventDefault();

        $box.block();

        $.post(woocommerce_admin_meta_boxes.ajax_url, {
          action: "skynet_print_awb_sticker",
          order_id: woocommerce_admin_meta_boxes.post_id,
          reprint: 1,
          "skynet-print-awb-meta-box-nonce": $(
            "#skynet-print-awb-meta-box-nonce"
          ).val(),
        })
          .done(function (response) {
            if (response.success) {
              const downloadLink = document.createElement("a");
              const fileName = response.data.awbnumber + ".pdf";

              downloadLink.href = response.data.pdfUrl;
              downloadLink.target = "_blank";
              downloadLink.download = fileName;
              downloadLink.click();
            } else {
              alert(response.data.message);
            }
          })
          .fail(function (data) {
            alert(
              "Something went wrong, please try again later. \nError:" +
                data.statusText
            );
          })
          .always(function (data) {
            $box.unblock();
          });
      });
    }
  }

  function init_settings_page() {
    var adminpage = window.adminpage;

    if (adminpage !== "woocommerce_page_wc-settings") {
      return;
    }

    var $form = $("#wpbody-content");

    $(document).on("click", "#skynet-default-store-address-link", function (e) {
      e.preventDefault();

      var confirmLoad = confirm(
        "This action will overwrite the existing fields. Are you sure?"
      );
      if (confirmLoad) {
        $form.block();
        $.post(ajaxurl, {
          action: "skynet_load_default_address",
          nonce: skynet_setting.load_address_nonce,
        })
          .done(function (response) {
            if (response.success) {
              $("#woocommerce_skynet_shipper_address1").val(
                response.data.address1
              );
              $("#woocommerce_skynet_shipper_address2").val(
                response.data.address2
              );
              $("#woocommerce_skynet_shipper_city").val(response.data.city);
              $("#woocommerce_skynet_shipper_state").val(response.data.state);
              $("#woocommerce_skynet_shipper_postcode").val(
                response.data.postcode
              );
              $("#woocommerce_skynet_shipper_country").val(
                response.data.country
              );
            } else {
              alert(response.data.message);
            }
          })
          .fail(function (data) {
            alert(
              "Something went wrong, please try again later. \nError:" +
                data.statusText
            );
          })
          .always(function (data) {
            $form.unblock();
          });
      }
    });
  }

  function reprint_table(){

    var tbody = $('#reprint tbody');

    var params = {
          action: "skynet_reprint_table",
          order_id: $('#order_id').val(),
          reprint: 0,
        };

    $.post(
          woocommerce_admin_meta_boxes.ajax_url,
          $.param(params) +
          "&" +
          $("#skynet-reprint-bulky-awb input").serialize() 
        )
          .done(function (response) {
          if (response.success) {
            var orders_id = [];
            $(response.data.awbnumber).each(function (a,b) {
              $(response.data.order).each(function (x,y) {
                $(response.data.product_name).each(function (c,d) {
                  if((b.AWBNumber == y.awbnumber) && (d.order_id == y.order_id))
                  {
                    const today = new Date(d.dated.date);
                    const yyyy = today.getFullYear();
                    let mm = today.getMonth() + 1; // Months start at 0!
                    let dd = today.getDate();

                    if (dd < 10) dd = '0' + dd;
                    if (mm < 10) mm = '0' + mm;

                    const formattedToday = dd + '/' + mm + '/' + yyyy;
                    orders_id.push(y.order_id);
                    var status = 'Pending';
                    if(b.Description != 'Data not found')
                    {
                      status = b.Description;
                    }
                    var tr = $('<tr></tr>')
                    tr.append('<td>' + (a+1) + '</td>')
                    tr.append('<td>' + b.AWBNumber + '</td>')
                    tr.append('<td> #' + y.order_id + '</td>')
                    tr.append('<td>' + d.item + '</td>')
                    tr.append('<td>' + d.receipt_name + '</td>')
                    tr.append('<td>' + status +'</td>')
                    tr.append('<td>' + formattedToday + '</td>')
                    tbody.append(tr);        
                  }
                });
              });
            });
              $("#order_id").val(orders_id);
          } else {
            alert(response.data.message);
          }
          }).fail(function (data) {
          alert(
            "Something went wrong, please try again later. \nError:" +
            data.statusText
          );
		      }).always(function (data) {
				    // $box.unblock();
            // $('#reprint').DataTable();
			    });


    $(document).on("click", "#btn_skynet_reprint_bulky_awb", function (e) {
        e.preventDefault();
        
        $('button[id="btn_skynet_reprint_bulky_awb"]').prop('disabled', true);
        
        var params = {
          action: "skynet_print_awb",
          order_id: $('#order_id').val(),
          reprint: 1,
        };

       $.post(
          woocommerce_admin_meta_boxes.ajax_url,
          $.param(params) +
          "&" +
          $("#skynet-reprint-bulky-awb input").serialize() 
        )
          .done(function (response) {
            
            if (response.success) {
              const downloadLink = document.createElement("a");
              const fileName = response.data.awbnumber + ".pdf";

              downloadLink.href = response.data.pdfUrl;
              downloadLink.target = "_blank";
              downloadLink.download = fileName;
              downloadLink.click();
            } else {
              alert(response.data.message);
            }
          })
          .fail(function (data) {
            alert(
              "Something went wrong, please try again later. \nError:" +
                data.statusText
            );
          })
          .always(function (data) {
             $('button[id="btn_skynet_reprint_bulky_awb"]').prop('disabled', false);
          });
      });

      var tester = '';
      $(document).on("keyup", ".volumetric", function (e) {
          e.preventDefault();
          tester = $(this);
          prop_volumetric_weight(tester);
      });

      $(document).on("change", ".volumetric", function (e) {
          e.preventDefault();
           tester = $(this);
          prop_volumetric_weight(tester);
      });
      
      function prop_volumetric_weight(tester)
      {
          var order_id = tester.data('get_order_id');
          var length = $('#skynet_shipment_length_'+order_id).val();
          var width = $('#skynet_shipment_width_'+order_id).val();
          var height = $('#skynet_shipment_height_'+order_id).val();

          var total = (length*width*height)/5000;
          var volumetric = $('#skynet_volumetric_weight_'+order_id).val(total);
          
      }

      $(document).on("click", "#btn_skynet_print_bulky_awb", function (e) {
          e.preventDefault();

          var $box = $("#skynet-print-bulky-awb");
          var order_id = document.getElementById("print_get_order_id").value;
          const myArray = order_id.split(",");

          var sum = 0;
          var error = "Please correct these errors:\n";

          for (var i = 0; i < myArray.length; i++) {
            
            sum = parseInt(myArray[i]);
            var weight = document.getElementById("skynet_shipment_weight_"+myArray[i]).value;
            var pieces = document.getElementById("skynet_shipment_pieces_"+myArray[i]).value;
            var type = document.getElementById("skynet_shipment_type_"+myArray[i]).value;
            var contents = document.getElementById("skynet_shipment_contents_"+myArray[i]).value;
            var length = document.getElementById("skynet_shipment_length_"+myArray[i]).value;
            var width = document.getElementById("skynet_shipment_width_"+myArray[i]).value;
            var height = document.getElementById("skynet_shipment_height_"+myArray[i]).value;
            var vol_weight = document.getElementById("skynet_volumetric_weight_"+myArray[i]).value;

            if(length != 0 && width == 0 && height == 0)
            {
              error = error.concat(" - Order ID   : "+myArray[i]+"\n");
              error = error.concat(" - Width (kg): Please insert a width\n");
              error = error.concat(" - Height (kg): Please insert a height\n");
            }
            else if(length != 0 && width != 0 && height == 0)
            {
              error = error.concat(" - Order ID   : "+myArray[i]+"\n");
              error = error.concat(" - Height (kg): Please insert height\n");
            }
            else if(length != 0 && width == 0 && height != 0)
            {
              error = error.concat(" - Order ID   : "+myArray[i]+"\n");
              error = error.concat(" - Width (kg): Please insert width\n");
            }
            else if(length == 0 && width != 0 && height == 0)
            {
              error = error.concat(" - Order ID   : "+myArray[i]+"\n");
              error = error.concat(" - Length (kg): Please insert length\n");
              error = error.concat(" - Height (kg): Please insert height\n");
            }
            else if(length == 0 && width != 0 && height != 0)
            {
              error = error.concat(" - Order ID   : "+myArray[i]+"\n");
              error = error.concat(" - Length (kg): Please insert length\n");
            }
            else if(length == 0 && width == 0 && height != 0)
            {
              error = error.concat(" - Order ID   : "+myArray[i]+"\n");
              error = error.concat(" - Length (kg): Please insert length\n");
              error = error.concat(" - Width (kg): Please insert width\n");
            }
          
            if(weight)
            {
              if(parseFloat(weight) < 0.1)
                error = error.concat(" - Weight (kg): Must be greater than 0.1\n")
            }
            else
              error = error.concat(" - Weight (kg): Please insert a weight\n");
            
            if(pieces)
            {
              if(parseInt(pieces) < 1)
                error = error.concat(" - No. of Boxes: Must be greater than 0\n")
            }
            else
              error = error.concat(" - No. of Boxes: Please insert No. of Boxes\n");
            
            if(type == "")
              error = error.concat(" - Shipment Type: Please select Shipment Type\n")
            
            if(contents == "")
              error = error.concat(" - Contents: Please insert Contents details\n")
            
          }
          
          if(error != "Please correct these errors:\n")
            alert(error);
          else
          {

            $box.block();

            var params = {
              action: "skynet_print_new_bulky_awb_sticker",
              order_id: woocommerce_admin_meta_boxes.post_id,
              reprint: 0,
            };

            $.post(
              woocommerce_admin_meta_boxes.ajax_url,
              $.param(params) +
              "&" +
              $("#skynet-print-bulky-awb input, select").serialize()
            )
              .done(function (response) {
                console.log(response);
                // alert(response);
              if (response.success) {
                const downloadLink = document.createElement("a");
                const fileName = response.data.awbnumber + ".pdf";

                downloadLink.href = response.data.pdfUrl;
                downloadLink.target = "_blank";
                downloadLink.download = fileName;
                downloadLink.click();

                $box.load(document.URL + " #skynet-print-bulky-awb > *");
                $("#skynet-reprint-bulky-awb").load(document.URL + " #skynet-reprint-bulky-awb > *");
                window.location.reload();
              } else {
                alert(response.data.message);
              }
              })
              .fail(function (data) {
              alert(
                "Something went wrong, please try again later. \nError:" +
                data.statusText
              );
              })
              .always(function (data) {
              $box.unblock();
              });
          }        
      });

      setInterval(function(){
          var print_disable = $('#print_get_order_id').val();
          var reprint_disable = $('#order_id').val();
          
          if(print_disable == ""){
            $('#skynet-print-bulky-awb').css("display", "none");
            
          }
          if(reprint_disable == ""){
            $('#skynet-reprint-bulky-awb').css("display", "none");
          }
      }, 3000);
      
  }
})(jQuery);

function shipmentType(value)
{
	// document.getElementById("skynet_shipment_contents").value = value == "DOCUMENT" ? "Document" : "";
}

// function init_add_bulk_action() {
  
//   jQuery(document).on("click", "#bulk-action-selector-top", function () {

//     const value = [];
//     jQuery("#bulk-action-selector-top option").each(function()
//     {
//      value.push(jQuery(this).val());
//     });
//     if(value.indexOf('mark_download') == -1){
//       jQuery(this).append('<option value="mark_download">Download PDF</option>');
//     }

//   });
// }

function init_auto_weight(){

  jQuery("#skynet_shipment_length").on('keyup',function(){
    
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
  });

  jQuery("#skynet_shipment_width").on('keyup',function(){
    
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
  });

  jQuery("#skynet_shipment_height").on('keyup',function(){
    
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
  });

  jQuery("#skynet_shipment_length").on('change',function(){
    
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
  });

  jQuery("#skynet_shipment_width").on('change',function(){
    
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
  });

  jQuery("#skynet_shipment_height").on('change',function(){
    
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
  });

  jQuery("#skynet_shipment_volumn").on('click',function(){
    
    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
  });
  
  jQuery(".save-action").on('click',function(){
    var adminpage = window.adminpage;
    var typenow = window.typenow;
    
    if(adminpage == 'post-php' && typenow == 'shop_order')
    {
        // location.reload();
    }

  });

  jQuery(document).on('click', '.button-large', function (event) {
    
    var adminpage = window.adminpage;
    var typenow = window.typenow;
    
    if(adminpage == 'post-php' && typenow == 'shop_order')
    {
        // location.reload();
    }
    

  });

  jQuery("span[class='woocommerce-Price-amount amount']").change(function(){
    // jQuery(document).on('click', 'a[class="delete-order-item tips"]', function (event) {
    
    // var adminpage = window.adminpage;
    // var typenow = window.typenow;
    // // alert('masuk');
    // if(adminpage == 'post-php' && typenow == 'shop_order')
    // {
    //     location.reload();
    console.log("masuk");
    jQuery("#skynet_printawb_meta_box").load(location.href + " #skynet_printawb_meta_box");
    // }
    
  });

  
}

function calculate_weight(){

    var length = document.getElementById("skynet_shipment_length").value;
    var width= document.getElementById("skynet_shipment_width").value;
    var height= document.getElementById("skynet_shipment_height").value;
    var vol_weight= document.getElementById("skynet_shipment_volumn").value;

    var volumn = (length * width * height)/5000;

    document.getElementById("skynet_shipment_volumn").value = volumn;
}
