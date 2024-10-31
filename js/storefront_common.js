jQuery(document).ready(function(){

    jQuery("#mcg_thumb").hide();
    jQuery("#mcg_back_to_parent").hide();
    jQuery("#mcg_thumb_collapse").hide();
	
   jQuery('#mcg_thumb_collapse').click(function(){
	   jQuery(".mcg_image").trigger("click");
   });
   
    
    

	//jQuery(".show_hide").show();
	
	jQuery('.mcg_image').click(function(){
			



		var cs_pluginURL = mycbgenie_sf_url.pluginsUrl+'images/';
	
		
 		
		
		//jQuery('#mcg_div_thumb').css('background-color', '#'+mycbgenie_sf_url.show_thumb_color);

			jQuery(this).html(function(i,html) {
									   
				if (html.indexOf('collapse.png') != -1 ){
					//jQuery('#mcg_div_thumb').css('background-color', '#'+mycbgenie_sf_url.show_thumb_color);
					jQuery("#mcg_back_to_parent").hide("fast");
					jQuery("#mcg_thumb_collapse").hide();
					
				}
				else{
					jQuery("#mcg_back_to_parent").show("fast");
					jQuery("#mcg_thumb_collapse").show("fast");
				}
				
				if (html.indexOf('View') != -1 ){
					html = html.replace('View','Hide');
					//alert('ss');
					jQuery('#mcg_div_thumb').css('background-color', '#'+mycbgenie_sf_url.show_thumb_color);					
					//jQuery('.mcg_image').css('background-color', '#'+mycbgenie_sf_url.show_thumb_color);		
					jQuery("#mcg_thumb").fadeIn(1000);
				}
				else{
					html = html.replace('Hide','View');

					
//					jQuery('#mcg_div_thumb').css('background-color', '#'+mycbgenie_sf_url.show_thumb_color);					

					jQuery("#mcg_thumb").fadeOut(700);
					jQuery('#mcg_div_thumb').css('background-color', 'transparent');					

					//jQuery('.mcg_image').css('background-color', '#'+mycbgenie_sf_url.show_thumb_color);
				}
				//else{
	
				//}
			   //    html = html.replace('Show','Hide');
			  //  } else {
			   //    html = html.replace('Hide','Show');
			  //  }
					return html;
				}).find('img').attr('src',function(i,src){
				return (src.indexOf('expand.png') != -1)? cs_pluginURL+'collapse.png' 	: cs_pluginURL+'expand.png';
			});
	
 });






});