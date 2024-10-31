
jQuery(document).ready( function($){
	
	
	// General Settings & Category Exclude Process
	$('#mycbgenie_form_settings_exclude_terms_ajax').submit(function(){
	$('#ajax-result_settings_exclude_category').html('');
	$('#submit-btn-category-exclude').attr('disabled',true);
	
	var tax_input_arr=[];
	

	
			$('input[name="tax_input[]"]').each(function(){
				if ($(this).attr('checked') ) {
				tax_input_arr.push(	$(this).attr('value'));	
					
				}

			});
		
			//alert (tax_input_arr[0]);
	
	$.ajax({

	  type: 	"POST",
	  url: 		mycbgenie_cat_exclude_vars.ajaxadminurl,
	  data: 	{
		  		tax_input: tax_input_arr,
				action :  'mycbgenie_ajax_exclude_category',
				mycbgenie_cat_exclude_nonce : mycbgenie_cat_exclude_vars.category_exclude_mycbgenie_nonce
				},
	  async: true,
	  success: 	function(response){
    
			$('#ajax-result_settings_exclude_category').show();
			$('#ajax-result_settings_exclude_category').html(response);
			$('#submit-btn-category-exclude').attr('disabled',false);

	  		} ,  
	  
	  error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown + textStatus);  
	   		}
	   
	});
		return false;
	});
		
		
		
		
		
		
		
		
	
});
		
