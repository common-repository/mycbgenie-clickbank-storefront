<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}





function mycbgenie_general_settings()
{

		$show_price ="";
		$show_thumbnails ="";
		$mycbgenie_sf_show_descr ="";
		$thumbnails_location ="";
		
		//Setting the default value 33333 if option is not set.
		if (!get_option('mycbgenie_account_no')){
			$account_no='33333';
			}
		else{$account_no=get_option('mycbgenie_account_no');}
		
		
		if (!get_option('mycbgenie_cb_tracking_id')){
			$mycbgenie_cb_tracking_id='';
			}
		else{$mycbgenie_cb_tracking_id=get_option('mycbgenie_cb_tracking_id');}
		
		
		if (!get_option('mycbgenie_show_price')){ }
			
		else{$show_price=get_option('mycbgenie_show_price');
				if ( $show_price=="Yes")	{ $show_price="checked";}
				else				    {$show_price="";}
			}

		if (!get_option('mycbgenie_sf_show_descr')){ }
			
		else{$mycbgenie_sf_show_descr=get_option('mycbgenie_sf_show_descr');
				if ( $mycbgenie_sf_show_descr=="Yes")	{ $mycbgenie_sf_show_descr="checked"; }
				else				    {$mycbgenie_sf_show_descr="";}
			}


		if (!get_option('mycbgenie_sf_show_thumbnails')){ }
			
		else{$show_thumbnails=get_option('mycbgenie_sf_show_thumbnails');
				if ( $show_thumbnails=="Yes")	{ $show_thumbnails="checked"; }
				else				    {$show_thumbnails="";}
			}
			

		if (!get_option('mycbgenie_sf_thumbnail_location')){ }
			
		else{$thumbnails_location=get_option('mycbgenie_sf_thumbnail_location');
				//if ( $thumbnails_location=="Yes")	{ $thumbnails_location="checked"; }
				//else				    {$thumbnails_location="";}
			}
			
			
							
		if (!get_option('mycbgenie_products_per_page')){
			$prods_per_page=12;
			}
		else{$prods_per_page=get_option('mycbgenie_products_per_page');}



		if (!get_option('mycbgenie_sf_bg_thumbnails')){
			$mycbgenie_sf_bg_thumbnails="EBEBEB";
			}
		else{$mycbgenie_sf_bg_thumbnails=get_option('mycbgenie_sf_bg_thumbnails');}		
		

		if (!get_option('mycbgenie_sf_text_color_thumbnails')){
			$mycbgenie_sf_text_color_thumbnails="000000";
			}
		else{$mycbgenie_sf_text_color_thumbnails=get_option('mycbgenie_sf_text_color_thumbnails');}		
		
		
		
		
	//	mycbgenie_sf_bg_thumbnails
		
		?>
		<style>
			#mycbgenie_ajax_gen_setting_form li { background:#e5e5e5; padding:7px; border:1px solid #dddddd; width:63%;}
		</style>
		
					<form method="POST" action="" id="mycbgenie_ajax_gen_setting_form">
					<ul>
						<li><label for="accno">MyCBGenie Account #: </label>
						<input size=5 id="mycbgenie_account_no" name="mycbgenie_account_no" value="<?php echo $account_no ?>" />
						<span> <a href="http://mycbgenie.com" target=_blank>It's FREE! Click here to get one</a></span></li>    
							 
						<li>
						<input type=checkbox <?php echo $show_price?> name="mycbgenie_show_price" id="mycbgenie_show_price" />
						<label for="showprice">Show <strong>Price</strong> </label>
						</li>

						<li>
						<input type=checkbox <?php echo $show_thumbnails?> name="mycbgenie_sf_show_thumbnails" id="mycbgenie_sf_show_thumbnails" />
						<label for="showdescr">Show <strong>Category Thumbnails</strong> in Category/Sub category </label>
						<div style="padding:7px; background:#d3d3d3; margin:9px; margin-left:21px; border-radius:5px; width:72%; max-width:100%;" >
							<span>  <input checked type="radio" value="header" name="mycbgenie_sf_thumbnail_location"/>On Header Area<br />
									<input <?php if ($thumbnails_location=="breadcrumb") echo "checked" ?> type="radio" value="breadcrumb"  
												name="mycbgenie_sf_thumbnail_location"/>On Breadcrumb Area<br />
												
									<input  <?php if ($thumbnails_location=="shop_loop") echo "checked" ?> type="radio" value="shop_loop"  
												name="mycbgenie_sf_thumbnail_location"/>Just Before Products
							</span>	
							<!--<br />
							<span style="margin-left:21px;"> <font color="#0066FF">[ Your theme may OR not support all of the above areas.</span>
							<br><span style="margin-left:21px;">Select the best area that looks good on your theme. ]</font></span>-->
							
																						
						</div>
						<span style="margin-left:21px;">Background Color: #</span>
							<input size=6 name="mycbgenie_sf_bg_thumbnails" id="mycbgenie_sf_bg_thumbnails" value="<?php echo $mycbgenie_sf_bg_thumbnails; ?>" />
						<br />
						<span style="margin-left:21px;">Text Color: #</span>
							<input size=6 name="mycbgenie_sf_text_color_thumbnails" id="mycbgenie_sf_text_color_thumbnails" value="<?php echo $mycbgenie_sf_text_color_thumbnails; ?>" />
						</li> 
												
						<li>
						<input type=checkbox <?php echo $mycbgenie_sf_show_descr?> name="mycbgenie_sf_show_descr" id="mycbgenie_sf_show_descr" />
						<label for="showdescr">Show <strong>Product Description</strong> in Category/Sub category Pages </label>
						<span></span>
						</li> 	


						<?php 
						
							if (!defined('MYCBGENIE_ADS_ACTIVE_VERSION')) {
						?>
						
						<li>
						<input type=checkbox  name="test" id="test" />
						<label for="test">Show Relevant <strong>Clickbank Ads</strong> On Top & Bottom Of All Products </label>
						<span> <font color="#0066FF">[ Requires MyCBGenie Ads Plugin - 
						<a href="plugin-install.php?tab=plugin-information&plugin=affiliate-ads-builder-for-clickbank-products&TB_iframe=true&width=753&height=494" target="_blank">
						Install Now</a> ]</font></span>
						<span> 
						<!--
						<div id="myCbGenie_dialog">
						 <iframe id="myCbGenieIframe" style="height: 480px; width:780px; display:none;" src=""></iframe>
						</div>
						<button id="myCbGenie_dialogBtn">Install MyCBGenie Ads Plugin</button>
						-->
						
						</span>
						</li>
						<?php	}		?>
								

						
						
												
						<li><label for="perpage">Products Per Page: # </label>
						<input size=2 value="<?php echo $prods_per_page ?>" name="mycbgenie_products_perpage" id="mycbgenie_perpage" />
						<span> <font color="#0066FF">[ This setting can be overwritten by your theme's setting ]</font></span></li> 
						

					
						
						 
						<li><label for="tracking">Clickbank Tracking ID: </label>
						<input size=12 value="<?php echo $mycbgenie_cb_tracking_id ?>" 
										name="mycbgenie_cb_tracking_id" id="mycbgenie_cb_track_id" />
						<span> (Optional) </span></li> 
						
						
						<li><label for="tracking">Product Image Quality: </label>
						<select name="mycbgenie_cb_image_quality" id="mycbgenie_cb_image_quality" />
						<option value="default" <?php echo (get_option('mycbgenie_product_image_quality')=='default' ?  "selected":"") ?>         >Default</option>
						<option value="medium" <?php echo (get_option('mycbgenie_product_image_quality')=='medium' ?  "selected":"") ?>         >Medium</option>
						<option value="high" <?php echo (get_option('mycbgenie_product_image_quality')=='high' ?  "selected":"") ?>         >High (consume more bandwidth & loads with less speed)</option>
						<option value="demo" >/</option>
						</select>
						</li> 
						
						
					</ul>
					<input class="button-primary" id="submit-btn-ajax_gen_setting" type=submit  value="Save Changes" />
					</form>
					
			<?php
}




function mycbgenie_ajax_gen_settings_function()
{

	if ( !isset($_POST['mycbgenie_gen_setting_nonce']) || !wp_verify_nonce( $_POST['mycbgenie_gen_setting_nonce'], "local_gen_setting_mycbgenie_nonce")) {

   		  exit("No naughty business please");
	} 
	
	//if (    isset($POST['mycbgenie_account_id'])     ?  $d= "dd"; : $d= "no";
		$account_no 						= 	$_POST['mycbgenie_account_no'];
		$prods_p_page						= 	$_POST['mycbgenie_product_per_page'];
		$show_price 						=	$_POST['mycbgenie_show_price'];
		$mycbgenie_sf_show_descr 			=	$_POST['mycbgenie_sf_show_descr'];		
		$mycbgenie_sf_show_thumbnails 		=	$_POST['mycbgenie_sf_show_thumbnails'];				
		$mycbgenie_sf_bg_thumbnails			=	$_POST['mycbgenie_sf_bg_thumbnails'];	
		$mycbgenie_sf_text_color_thumbnails	=	$_POST['mycbgenie_sf_text_color_thumbnails'];			
		$mycbgenie_sf_thumbnail_location 	=	$_POST['mycbgenie_sf_thumbnail_location'];						
		$mycbgenie_cb_tracking_id			=	$_POST['mycbgenie_cb_tracking_id'];
		$mycbgenie_image_quality			=	$_POST['mycbgenie_cb_image_quality'];


		//echo 'ddd'.$mycbgenie_sf_show_thumbnails.$mycbgenie_cb_tracking_id.$mycbgenie_sf_show_descr;
		
		
		if (!is_numeric($account_no)  && isset($account_no) 	){ exit("MyCBGenie Account# must be a numeric value.");}
		if (!is_numeric($prods_p_page) && isset($prods_p_page)	){ exit("Products Per Page must be a numeric value.");}
		
		//$account_no=mysql_real_escape_string($account_no);
		$account_no=floor(htmlspecialchars (($account_no)));
		$prods_p_page=floor(htmlspecialchars (($prods_p_page)));
		$mycbgenie_cb_tracking_id=htmlspecialchars (($mycbgenie_cb_tracking_id));

		update_option('mycbgenie_account_no',$account_no);
		update_option('mycbgenie_products_per_page',$prods_p_page);
		update_option('mycbgenie_show_price',$show_price);
		update_option('mycbgenie_sf_show_descr',$mycbgenie_sf_show_descr);	
		update_option('mycbgenie_sf_show_thumbnails',$mycbgenie_sf_show_thumbnails);	
		update_option('mycbgenie_sf_text_color_thumbnails',$mycbgenie_sf_text_color_thumbnails);			
		update_option('mycbgenie_sf_bg_thumbnails',$mycbgenie_sf_bg_thumbnails);			
		update_option('mycbgenie_sf_thumbnail_location',$mycbgenie_sf_thumbnail_location);			
		update_option('mycbgenie_cb_tracking_id',$mycbgenie_cb_tracking_id);
		update_option('mycbgenie_product_image_quality',$mycbgenie_image_quality);
		
		echo "<b><div><br>Updated Successfully.</b></div><br>";

	
		die();
}


function mycbgenie_remove_price_and_description_from_loop(){

   
	global $post;
    $managed_by = get_post_meta( $post->ID , '_mycbgenie_managed_by', true );
	

		
			//We are making sure that we only remove the prices of the mycbgenie imported products.
		   if( $managed_by == 'mycbgenie' ){
				
				if (get_option('mycbgenie_show_price')=="No" ) {
					remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				}

				
				if (get_option('mycbgenie_sf_show_descr')=="Yes"){

						add_action( 'woocommerce_after_shop_loop_item_title', 'mycbgenie_woocommerce_product_excerpt', 35, 2);   
				}
				
			}
			else {
						remove_action( 'woocommerce_after_shop_loop_item_title', 'mycbgenie_woocommerce_product_excerpt', 35, 2);   

				 		add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
			}
}



function mycbgenie_remove_price_from_single(){

    global $post;
    $managed_by = get_post_meta( $post->ID, '_mycbgenie_managed_by', true );
	
	//We are making sure that we only remove the price of the mycbgenie imported products.
    if( $managed_by == 'mycbgenie' ){
	
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' );
    }
}





function mycbgenie_show_cbpro_images_on_demo_remove_hook() {
           
        if (!get_option("mycbgenie_product_image_quality")) {

		}else if (get_option('mycbgenie_product_image_quality')=="default") {
            
          
        }else{
			
			if (function_exists('orchid_store_template_loop_product_thumbnail')) { 
			//if ($_SESSION['cs_theme_chosen']=='orchid') {
				remove_action( 'orchid_store_product_thumbnail', 'orchid_store_template_loop_product_thumbnail' );
			}else{
           		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			}
        }
}




function mycbgenie_show_cbpro_images_on_demo(){
    


    if (!function_exists('orchid_store_template_loop_product_thumbnail')) {
    //if ($_SESSION['cs_theme_chosen']=='orchid') {
       
        if (!get_option("mycbgenie_product_image_quality")) {

		}else if (get_option('mycbgenie_product_image_quality')=="default") {
            
          
        }else{
            add_action( 'woocommerce_before_shop_loop_item_title', 'mycbgenie_change_product_thumbnail_on_demo_with_cbpro', 100 );
        }
        
    }else{  //Orchid pro
           
        if (!get_option("mycbgenie_product_image_quality")) {
			
		}else if (get_option('mycbgenie_product_image_quality')=="default") {
            
          
        }else{
               add_action( 'orchid_store_product_thumbnail', 'mycbgenie_change_product_thumbnail_on_demo_with_cbpro',10 );   
        }
    }
}


function mycbgenie_change_product_thumbnail_on_demo_with_cbpro(){
    

		    if (get_post_meta( get_the_ID(), '_mycbgenie_managed_by', true )==='mycbgenie') {

                if (get_option('mycbgenie_product_image_quality')=="medium") {
                    
                    $post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );
                    if(!empty($post_thumbnail_id)) {
                       $mycbgenie_full_img_url =  wp_get_attachment_image_src( $post_thumbnail_id, 'medium' );    
                    }
                     $mycbgenie_img_url = $mycbgenie_full_img_url[0];
                     $mycbgenie_img_url_tag="medium";
                
                    
                }else if (get_option('mycbgenie_product_image_quality')=="high") {
                    
                    
                    
                    $post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );
                    if(!empty($post_thumbnail_id)) {
                       $mycbgenie_full_img_url =  wp_get_attachment_image_src( $post_thumbnail_id, 'full' );    
                    }
                     $mycbgenie_img_url = $mycbgenie_full_img_url[0];
                     $mycbgenie_img_url_tag="full";
                
                    
                }else if (get_option('mycbgenie_product_image_quality')=="demo"){
                    
                     $mycbgenie_img_url="https://cbproads.com/clickbankstorefront/v5/send_binary.asp?pc=&show_border=No&fl=".get_post_meta( get_the_ID(), '_mycbgenie_image_url', true )."&w=300&h=366";
                     $mycbgenie_img_url_tag="cbpro";
                }
                else
                {
                    echo "<p style='color:red; padding:5%;'>Please select an option for <i style='color:black;'>image output quality</i> in the settings link of the plugin: <span style='color:black;'>MyCBGenie</span></p>";
                }
                
                //echo $mycbgenie_img_url_tag.'cbggp'.$mycbgenie_img_url.get_post_meta( get_the_ID(), '_mycbgenie_reviewed', true );
              
                    if(get_post_meta( get_the_ID(), '_mycbgenie_reviewed', true )==='yes'){
                  
                                    /*
                                    //angled show
                                    $mycbgenie_review_badge = "<div style='overflow:hidden; background:#35A885; position: absolute;  color:white; inset: 0 auto auto 0;  transform-origin: 100% 0;  transform: translate(-29.3%) rotate(-45deg);  box-shadow: 0 0 0 999px #35A885;  clip-path: inset(0 -100%); font-size:13px; padding-top:2px; padding-bottom:2px;'>Top Seller</div>";
                                    //$mycbgenie_hide_overlow="overflow:hidden;";
                                    */
                                     $mycbgenie_review_badge = "<div style='  --f: 10px; /* control the folded part*/  --r: 15px; /* control the ribbon shape */  --t: 10px; /* the top offset */  position: absolute;  inset: var(--t) calc(-1*var(--f)) auto auto;  padding: 0 10px var(--f) calc(10px + var(--r));  clip-path:     polygon(0 0,100% 0,100% calc(100% - var(--f)),calc(100% - var(--f)) 100%,      calc(100% - var(--f)) calc(100% - var(--f)),0 calc(100% - var(--f)),      var(--r) calc(50% - var(--f)/2));  background: #D15454;  box-shadow: 0 calc(-1*var(--f)) 0 inset #0005; color:white; font-size:13px; padding-top:4px; padding-bottom:14px;'>Reviewed</div>";
                                     //$mycbgenie_hide_overlow="";
                                      $mycbgenie_top_rated_left_ribbon="yes";
									  $mycbgenie_ribbon_type="Reviewed";
                                  
                            
                    }else if(get_post_meta( get_the_ID(), '_featured', true )==="yes"){	
							            $mycbgenie_review_badge = "<div style='  --f: 10px; /* control the folded part*/  --r: 15px; /* control the ribbon shape */  --t: 10px; /* the top offset */  position: absolute;  inset: var(--t) calc(-1*var(--f)) auto auto;  padding: 0 10px var(--f) calc(10px + var(--r));  clip-path:     polygon(0 0,100% 0,100% calc(100% - var(--f)),calc(100% - var(--f)) 100%,      calc(100% - var(--f)) calc(100% - var(--f)),0 calc(100% - var(--f)),      var(--r) calc(50% - var(--f)/2));  background: #FFD300;  box-shadow: 0 calc(-1*var(--f)) 0 inset #0005; color:black; font-size:13px; padding-top:4px; padding-bottom:14px;'>Top Seller</div>";
                                     //$mycbgenie_hide_overlow="";
                                      $mycbgenie_top_rated_left_ribbon="yes";
									   $mycbgenie_ribbon_type="Featured";
								//echo get_post_meta( get_the_ID(), '_featured', true );		
					}else{$mycbgenie_ribbon_type="";}
				
				

                    $mycbgenie_permalink=get_the_permalink();
                  
                     if ( $mycbgenie_top_rated_left_ribbon=="") {
                         
                            $mycbgenie_img_url="<div style='width:100%;  text-align:center; position:relative;' > ".$mycbgenie_review_badge.
                                         "<a  target='_blank' href='".$mycbgenie_permalink."'>".
                                         "<img style='margin-left:auto !important; margin-right:auto !important;' class='img-responsive'".
                                            " src='".$mycbgenie_img_url."'".
                                            ">".
                                         "</a>".
                                     "</div>";
                

                                                                                 
                    }else if ( $mycbgenie_top_rated_left_ribbon=="yes") {   
						 
                               		if ($mycbgenie_ribbon_type=="Reviewed") {
										$mycbgenie_ribbon_color="#ff6251";
										$mycbgenie_ribbon_tcolor="fff";
										$mycbgenie_ribbon_label="Reviewed";										
									}else if ($mycbgenie_ribbon_type=="Featured") {
										$mycbgenie_ribbon_color="#61B136";
										$mycbgenie_ribbon_tcolor="000";
										$mycbgenie_ribbon_label="Top Seller";
									}                                                                 
                                                    
                                	
                                    $mycbgenie_img_url='<div class="mycbgenie_ribbon_container left" data-ribbon="'.$mycbgenie_ribbon_label.'" style="--d:10px;--c:'.$mycbgenie_ribbon_color.';--f:13px">'.
                                            '<a  target="_blank" href="'.$mycbgenie_permalink.'">'.
                                                '<img style="margin-left:auto !important; margin-right:auto !important;" class="img-responsive"'.
                                                ' src="'.$mycbgenie_img_url.'" '.
                                            '>'.
                                            '</a>'.
                                    '</div>';
                    }

                  if (!function_exists('orchid_store_template_loop_product_thumbnail')){
                      echo $mycbgenie_img_url;
                  }else{
                      echo $mycbgenie_img_url;
                  }
		    }else{
				 woocommerce_template_loop_product_thumbnail();
			}
}


?>