jQuery(document).ready(function() {
								
				//alert(mycbgenie_media_upload_vars.ajaxadminurl);
				
						
	
					
					
					
jQuery('#upload_image_link').click(function() {
											  
											
	 formfield = jQuery('#upload_image').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
 

	
 return false;
});
 
window.send_to_editor = function(html) {


	 		$html
			imgurl = jQuery('img',html).attr('src');
		//	alert(html+imgurl);
			 	//alert( jQuery( 'img', html ).attr( 'src' ));
			//jQuery('#upload_image').val(imgurl);
 
  			var class_string    = jQuery( 'img', html ).attr( 'class' );
            var image_url       = jQuery( 'img', html ).attr( 'src' );
            var classes         = class_string.split( /\s+/ );
            var image_id        = 0;

            for ( var i = 0; i < classes.length; i++ ) {
                var source = classes[i].match(/wp-image-([0-9]+)/);
                if ( source && source.length > 1 ) {
                    image_id = parseInt( source[1] );
                }
            }
			
			//jQuery('#mycbgenie_edit_image').attr("src",imgurl);
			//jQuery('#mycbgenie_attachment_id').val(image_id);

            alert(image_id); // <---- THE IMAGE ID
			
			
 	tb_remove();
 
 
}//window.send
 
});