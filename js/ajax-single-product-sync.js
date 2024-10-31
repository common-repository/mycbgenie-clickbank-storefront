
jQuery(document).ready( function($){
	

	// General Settings & Category Exclude Process
	$('#mycbgenie-ajax-single-product-sync-form').submit(function(){
	
		
				$('#submit-btn-single-sync-now').attr('disabled',true);
				  $("#single_sync_output_div").fadeOut(700);

				$.ajax({

				  type: 	"POST",
				  url: 		mycbgenie_single_sync_vars.ajaxadminurl,
				  data: 	{
							id	: $('#sync_id').val()	,
							mycbgenie_id	:	$('#mycbgenie_sync_id').val()	,
							action :  'mycbgenie_single_sync_process_action',
							mycbgenie_single_sync_nonce : mycbgenie_single_sync_vars.mycbgenie_single_sync_nonce
							},
				 dataType: "json",
				 success: 	function(response){
						
						if (response.status	==	'error'){
							
							alert(response.error_message);
							console.log(response.error_message);
							return false;
						}
						else {
							
							$("#single_sync_title_output").hide();
							$('#single_sync_descr_output').hide();		
							$('#single_sync_mdescr_output').hide();					
							$('#single_sync_price_output').hide();					
							$('#single_sync_keywords_output').hide();					
							$('#single_sync_gravity_output').hide();					
							$('#single_sync_rank_output').hide();					



							//alert('SYNC \n\n success');
							console.log(response);

							$('#submit-btn-single-sync-now').attr('disabled',false);
							//$('#single_sync_output_div').show();
							$('#single_sync_title_output').text(response.title);
							$('#single_sync_descr_output').text(response.descr);					
							$('#single_sync_mdescr_output').text(response.mdescr);					

							$('#single_sync_price_output').text(response.price);
							
							$('#single_sync_keywords_output').text(response.keywords);					
							$('#single_sync_rank_output').text(response.rank);					
							$('#single_sync_gravity_output').text(response.gravity);					


				  $("#single_sync_output_div").fadeIn(500);
				  $("#single_sync_title_output").fadeIn(2000);
				  $("#single_sync_descr_output").fadeIn(3000);
				  $("#single_sync_mdescr_output").fadeIn(4000);
				  $("#single_sync_price_output").fadeIn(5000);
				  $("#single_sync_keywords_output").fadeIn(6000);
				  $("#single_sync_rank_output").fadeIn(7000);
				  $("#single_sync_gravity_output").fadeIn(8000);

  


						}// end if  (response.error=='error'){

					
						}   //ssuccess
				  
				 
				  }).fail(function(xhr, textStatus, errorThrown) {
							if ( window.console && window.console.log ) {
						
							alert('Error! '+   xhr.responseText + textStatus + errorThrown  );
							return false;
				  			}
					});//ajax
			
			
			
		return false;
	});
		
		
		
		
		
		
		
		
	
});
		
