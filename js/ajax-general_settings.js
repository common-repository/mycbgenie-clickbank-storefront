jQuery(document).ready( function($){
	
	/*
	$("#myCbGenie_dialog").dialog({
    autoOpen: false,
    modal: true,
    height: 600,
	width:800,
    open: function(ev, ui){
			$('#myCbGenieIframe').show();
           $('#myCbGenieIframe').attr('src','plugin-install.php?tab=plugin-information&plugin=affiliate-ads-builder-for-clickbank-products');
          }
	});
	
	$('#myCbGenie_dialogBtn').click(function(){
		$('#myCbGenie_dialog').dialog('open');
	});
		*/
		
		
	$('#mycbgenie_ajax_gen_setting_form').submit(function(){
														  
		//alert ('dd'+$('#mycbgenie_account_no').val());
		//alert ('dd'+$('#mycbgenie_cb_track_id').val());
		
		

		$('#submit-btn-ajax_gen_setting').attr('disabled',true);
		
		$('#ajax-result_gen_settings').html('');
		

		
		if ($('#mycbgenie_show_price').prop('checked')){
			var show_price = 'Yes';
		}
		else{
			var show_price = 'No';
		}
		
		if ($('#mycbgenie_sf_show_descr').prop('checked')){
			var show_descr = 'Yes';
		}
		else{
			var show_descr = 'No';
		}


		if ($('#mycbgenie_sf_show_thumbnails').prop('checked')){
			var show_thumb = 'Yes';
		}
		else{
			var show_thumb = 'No';
		}
	
		var mycbgenie_sf_thumbnail_location=jQuery('[name="mycbgenie_sf_thumbnail_location"]:checked','#mycbgenie_ajax_gen_setting_form').val()	;			


$.ajax({
	   
		//console.log($('#mycbgenie_cb_image_quality').val());
		
		  type: 	"POST",
		  url: 		mycbgenie_general_settings_vars.ajaxadminurl,
		  data: 	{	
					action :  'mycbgenie_ajax_gen_settings_action',
					mycbgenie_account_no : $('#mycbgenie_account_no').val(),
					mycbgenie_show_price : show_price ,
					mycbgenie_cb_tracking_id : $('#mycbgenie_cb_track_id').val(),
					mycbgenie_product_per_page : $('#mycbgenie_perpage').val(),
					mycbgenie_sf_show_descr		: show_descr,
					mycbgenie_sf_show_thumbnails :  show_thumb,
					mycbgenie_sf_bg_thumbnails		:	$('#mycbgenie_sf_bg_thumbnails').val(),
					mycbgenie_sf_text_color_thumbnails		:	$('#mycbgenie_sf_text_color_thumbnails').val(),					
					mycbgenie_sf_thumbnail_location :  mycbgenie_sf_thumbnail_location,
			 		mycbgenie_cb_image_quality		:  $('#mycbgenie_cb_image_quality').val(),
					mycbgenie_gen_setting_nonce : mycbgenie_general_settings_vars.gen_setting_mycbgenie_nonce
					},
		  async: true,
		  success: 	function(response){
					//alert('sucess'+$('#mycbgenie_cb_image_quality').val());
		
					$('#ajax-result_gen_settings').show();
					$('#ajax-result_gen_settings').html(response);
					
					
					$('#submit-btn-ajax_gen_setting').attr('disabled',false);
		
		  } ,  
		  
		  error: function(MLHttpRequest, textStatus, errorThrown){  
						alert(errorThrown + textStatus);  
		   }
		   
		});

		

		
				return false;
	});
		
		
		
		
		
		
		
});
		
