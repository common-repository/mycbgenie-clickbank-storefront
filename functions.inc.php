<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


function wooCommerce_load_check()
{

	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
		
		} else { 
		
		$warning='<h3>This plugin needs <a href=plugin-install.php?tab=plugin-information&plugin=woocommerce> <img  src='.plugins_url( 'images/woocommerce-logo.png', __FILE__ ).' width=190 height=45></a> to be activated to work with.</h3>';
		wp_die(__($warning),'Plugin dependency check', array( 'back_link' => true ) );
	}
}

function mycbgenie_sf_category_thumbnails(){


	if (is_woocommerce() && (is_product_category() || is_shop())   ) {

		if (is_shop()) {
			$term = get_terms( 'product_cat'); // get current term
		}else{
			$term = get_term_by( 'slug', get_query_var( 'product_cat' ),  'product_cat'  ); // get current term
		}
		
		if ($term){

			//	$children = get_term_children($term->term_id, ('product_cat')); // get children
			//var_dump($children);
			
			$parent = get_term($term->parent, ('product_cat') );
			
			//if($parent->term_id!="") {
				$children = get_terms( 'product_cat',   array( 'parent' => $term->term_id,  ));
				$sub_related ="sub"	;
				if (is_shop()) {
				
					$sub_related ="storefront";
				}
				
				$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );

				if ( $shop_page_url ) { 

						$related= "<div id='mcg_back_to_parent' style='margin:10px;' align=right> Back to root category : <a href='" . $shop_page_url . "'>Shop</a></div>"  ;				
				}
				

			//}

			if(($parent->term_id!="") && (sizeof($children)==0)) {			
				$sub_related ="related"		;
				$children = get_terms( 'product_cat',   array( 'parent' => $parent->term_id,  ));
				$related= "<div id='mcg_back_to_parent' style='margin:10px;' align=right> Back to parent category : <a href='" . get_term_link( $parent->term_id ) . "'>". $parent->name ."</a></div>"  ;
				
			}
			
			$related= '<div align=right id="mcg_thumb_collapse" ><span style="font-size:11px; color:#808080;">Hide<img  style="border:0px solid red; vertical-align:middle; float:right; margin:3px; padding-left:5px; margin-right:20px; margin-bottom:10px;" src="'.plugins_url('images/collapse.png', __FILE__ ).'"></span></div>';
					
			//var_dump($children);
			$size = 'small';
			
			if ( ! is_wp_error( $terms ) ) {
			
				$cnt_child = sizeof($children);
				if ($cnt_child >0 ) {
		
						
				
				?>
					<style>
					 #mcg_image{
					 cursor: pointer; cursor: hand; 
					 }
					</style>
				<?php 
				
				echo '
					<!--<div style="clear:both;"></div>-->
					<div id="mcg_div_thumb" style="width:100%; margin-left:1px; margin-right:1px; margin-top:3px; 
								margin-bottom:15px; border:0px solid #fafafa; overflow:auto; padding:0px;
								padding-bottom:0px; text-align:center; border-radius:5px; "   >
					<p class="mcg_image" style="max-width:100%; border-radius:3px; float:right; 
					border:1px solid #'.get_option( 'mycbgenie_sf_bg_thumbnails','EBEBEB' ).'; padding:5px; padding-left:8px; margin-top:0px; padding-bottom:5px; ">
					<a href="" onClick="return false;" style="font-size:11px; color:#'.get_option('mycbgenie_sf_text_color_thumbnails','000000').';">View: '.$sub_related.' categories </a>
					<img  style="border:0px solid red; vertical-align:middle; float:right; margin:3px; padding-left:5px;" src="'.plugins_url('images/expand.png', __FILE__ ).'">
					</p>
					
				'; 
				?>
					
					
					
					<style>
					 #mcg_thumb li{
 					 display: inline-block;
					 cursor: pointer; cursor: hand; 
					 }
					</style>


					

					
					<ul id="mcg_thumb" style="clear:right; margin:0px; padding:0px; margin-bottom:20px;">
				<?php	
				
						
				foreach ( $children as $child ) {
				
				
				 if ($child->term_id != $term->term_id) {
				 
					$cnt_temp = $cnt_temp+1;
					
					//if ($cnt_temp % 2==0) 	$rand_left_right="right";
					//else $rand_left_right="right";
					
					$mcg_thumbnail_id = get_woocommerce_term_meta($child->term_id, 'thumbnail_id', true);
					$mcg_image = wp_get_attachment_image_src($mcg_thumbnail_id, $size, false);

					echo '
						
								<li>
								<a href="' . get_term_link( $child ) . '">
								<img style="width:140px; height:auto; padding:5px;" src="'.$mcg_image[0].'">
								<p style="font-size:12px; color:#'.get_option('mycbgenie_sf_text_color_thumbnails','000000').';">'.$child->name.'</p>
								</a>
								</li>
								
					';
				 }//avoid term catid  if ($child->term_id != $term->term_id) {
				}//end of foreach children
				
				echo '
					</ul>
					'.$related.'
					</div>
				';
				} //if sizeof children
//******************************************************************************************************************
			/*	else { // if no child but parent
				
					$parent = get_term($term->parent, ('product_cat') );
					
					if(($parent->term_id!="") && (sizeof($children)==0)) {
						
						//$parent->term_id
					} // of parent is there and size of children zero
				}// else if has no child but paren
				*/
//******************************************************************************************************************				
			} // wp_error


		//	echo "<div style='margin:5px; margin-top:20px; margin-bottom:20px;'>I am from</div>";
					
		} // end of if $term



	} // end of is_woocommerc
}

function mycbgenie_wp_trim_all_excerpt($text) {

	// Creates an excerpt if needed; and shortens the manual excerpt as well
	global $post;
   $raw_excerpt = $text;
   if ( '' == $text ) {
      $text = get_the_content('');
      $text = strip_shortcodes( $text );
      $text = apply_filters('the_content', $text);
      $text = str_replace(']]>', ']]&gt;', $text);
   }

	$text = strip_tags($text);
	$excerpt_length = apply_filters('excerpt_length', 20);
	$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
	$text = ucfirst(wp_trim_words( $text, $excerpt_length, $excerpt_more )); 
	
	return apply_filters('wp_trim_excerpt', $text, $raw_excerpt); 
}

if (!function_exists('mycbgenie_woocommerce_product_excerpt'))    {
	
			function mycbgenie_woocommerce_product_excerpt() { 

		    	echo    "<span style='text-align:left; color:#808080; display:inline-block;  padding:10px;   '>".wp_trim_words( get_the_excerpt(), 12 )."</span>";          
			}   
}



function  mycbgenie_update_woocommerce_image_dimensions() {

	$catalog = get_option( 'shop_catalog_image_size');
	$single=	get_option( 'shop_single_image_size');
	$thumbnail=	get_option( 'shop_thumbnail_image_size');
	
	update_option( 'mycbgenie_shop_catalog_image_size', $catalog ); 
	update_option( 'mycbgenie_shop_single_image_size', $single ); 		// Single product image
	update_option( 'mycbgenie_shop_thumbnail_image_size', $thumbnail ); 
	
	
  	$catalog = array(
		'width' 	=> '450',	// px
		'height'	=> '450',	// px
		'crop'		=> 0 		// true
	);

	$single = array(
		'width' 	=> '450',	// px
		'height'	=> '450',	// px
		'crop'		=> 0		// true
	);

	$thumbnail = array(
		'width' 	=> '180',	// px
		'height'	=> '180',	// px
		'crop'		=> 0 		// false
	);

	// Image sizes
	update_option( 'shop_catalog_image_size', $catalog ); 		// Product category thumbs
	update_option( 'shop_single_image_size', $single ); 		// Single product image
	update_option( 'shop_thumbnail_image_size', $thumbnail ); 	// Image gallery thumbs
}


function  mycbgenie_reverse_woocommerce_image_dimensions() {

	$catalog = get_option( 'mycbgenie_shop_catalog_image_size');
	$single=	get_option( 'mycbgenie_shop_single_image_size');
	$thumbnail=	get_option( 'mycbgenie_shop_thumbnail_image_size');
	
	update_option( 'shop_catalog_image_size', $catalog ); 		// Product category thumbs
	update_option( 'shop_single_image_size', $single ); 		// Single product image
	update_option( 'shop_thumbnail_image_size', $thumbnail ); 	// Image gallery thumbs
	
	
}



function mycbgenie_array_options_init($option_name)
	{
	
		if (is_array(get_option( $option_name )))
		{
			if (sizeof(get_option( $option_name ))== 0 )
			{
				add_option( $option_name ,array());
			}
		}
		else {add_option( $option_name ,array());}
		
	}
	



function mycbgenie_version_update() {


		if (get_option('mycbgenie_version') !== MYCBGENIE_ACTIVE_VERSION ) {
			mycbgenie_create_update_database_tables("plugin_update");
			update_option('mycbgenie_version', MYCBGENIE_ACTIVE_VERSION);
		}

	
}


	

function mycbgenie_activate()
{

    if ( ! current_user_can( 'activate_plugins' ) )
       wp_die(__('You do not have sufficient permissions to activate plugins for this site.'));


	wooCommerce_load_check();

	//setting help tips to disable 
	add_user_meta(get_current_user_id(), 'mycbgenie_import_screen_dismiss_option', 'true', true);
	add_user_meta(get_current_user_id(), 'mycbgenie_product_screen_dismiss_option', 'true', true);


	//setting meta data to wp-options for category exclude preference.
	mycbgenie_array_options_init('mycbgenie_excluded_terms');
	//setting option to mark all imported terms
	mycbgenie_array_options_init('mycbgenie_imported_main_terms');
	mycbgenie_array_options_init('mycbgenie_imported_sub_terms');

	
	//setting option for storing imported products that have disabled by the user. and for featured products
	mycbgenie_array_options_init('mycbgenie_disabled_products');
	mycbgenie_array_options_init('mycbgenie_featured_products');
	
	//custom edited list
	mycbgenie_array_options_init('mycbgenie_custom_edited_products');

	//update_option('mycbgenie_version','1.3');
	
	mycbgenie_add_product_attribute_taxonomy();
	
	mycbgenie_create_update_database_tables("new_install");
	
	mycbgenie_sync_wordpress_installs();

}


function mycbgenie_create_update_database_tables($update_or_new_install){

	global $wpdb;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
  $charset_collate = $wpdb->get_charset_collate();
	

	
  $table_name = "mycbgenie_custom_edited_products";
  
//  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

		$sql = "CREATE TABLE mycbgenie_custom_edited_products (
		`mycbgenie_id` varchar(50) NOT NULL,
		`title` varchar(200) NOT NULL,
		`excerpt` varchar(300) NOT NULL,
		`descr` text,
		`price` decimal(7,3) NOT NULL,
		`thumbnail_id` varchar(300),
		`tags`	TEXT,
		PRIMARY KEY mycbgenie_id (mycbgenie_id)
		) ENGINE=InnoDB ";
	
	dbDelta( $sql );
//	}// no table exists


	$table_name = "mycbgenie_fresh_import_products_master";
 // 	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

		$sql = "CREATE TABLE mycbgenie_fresh_import_products_master (
		`mycbgenie_import_id` varchar(20) NOT NULL,
		`json_import_file_id` varchar(20) NOT NULL,
		`start_time` datetime,
		`products_completed` mediumint(10)	DEFAULT 0,
		`end_time`	datetime,
		`modified_time`	datetime,
		`status_import`	varchar(20),
		`status_modified`	varchar(20),
		`batch_interval` mediumint(5)	DEFAULT 0,
		`last_used_throttle`	mediumint(3),
		`screenshot_allowed`	varchar(3),		
		PRIMARY KEY mycbgenie_import_id (mycbgenie_import_id)
		) ENGINE=InnoDB ";
	
	dbDelta( $sql );
//	}// no table exists
	

	
	$table_name = "mycbgenie_fresh_import_product_details";
//  	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

		$sql = "CREATE TABLE mycbgenie_fresh_import_product_details (
		`mycbgenie_import_id` varchar(20) NOT NULL,
		`mycbgenie_id` varchar(50) NOT NULL,
		`insert_time` datetime,
		PRIMARY KEY mycbgenie_import_id (`mycbgenie_import_id`,`mycbgenie_id`)
		) ENGINE=InnoDB ";
	
	dbDelta( $sql );
//	}// no table exists
	
	
	$table_name = "mycbgenie_manual_sync_products_master";
//  	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

		$sql = "CREATE TABLE mycbgenie_manual_sync_products_master (
		`mycbgenie_sync_id` varchar(20) NOT NULL,
		`json_import_file_id` varchar(20) NOT NULL,
		`start_time` datetime,
		`products_completed` mediumint(10)	DEFAULT 0,
		`products_added` mediumint(10)	DEFAULT 0,
		`products_removed` mediumint(10)	DEFAULT 0,
		`end_time`	datetime,
		`status_sync`	varchar(20),
		`last_used_throttle`	mediumint(3),
		`screenshot_allowed`	varchar(3),
		PRIMARY KEY mycbgenie_sync_id (mycbgenie_sync_id)
		) ENGINE=InnoDB ";
	
	dbDelta( $sql );
//	}// no table exists
	
	
	$table_name = "mycbgenie_cron_sync_master";
//  	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

		$sql = "CREATE TABLE mycbgenie_cron_sync_master (
		`mycbgenie_cron_id` varchar(20) NOT NULL,
		`json_path`	varchar(200) NOT NULL,
		`start_time` datetime,
		`products_completed` mediumint(10)	DEFAULT 0,
		`total_products` mediumint(10)	DEFAULT 0,
		`steps_completed` mediumint(5)	DEFAULT 0,
		`total_steps` mediumint(5)	DEFAULT 0,
		`throttle_speed` mediumint(5)	DEFAULT 0,
		`end_time`	datetime,
		`last_batch_process_time`	datetime,
		`status_cron`	varchar(20),
		`products_added` mediumint(10)	DEFAULT 0,
		`products_removed` mediumint(10)	DEFAULT 0,
		`last_used_throttle` mediumint(3),
		PRIMARY KEY mycbgenie_cron_id (mycbgenie_cron_id)
		) ENGINE=InnoDB ";
	
	dbDelta( $sql );
//	}// no table exists

}


function mycbgenie_de_activate()
{

    if ( ! current_user_can( 'activate_plugins' ) )
        wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));
		
	delete_option( 'mycbgenie_product_image_quality');
	delete_option( 'mycbgenie_review_menu_check');
	delete_option( 'mycbgenie_menu_check');
	
	
	mycbgenie_sync_wordpress_uninstall_or_deactivate();

}




function mycbgenie_uninstall()


{

    if ( ! current_user_can( 'activate_plugins' ) )
        wp_die(__('You do not have sufficient permissions to  uninstall plugins for this site.'));
	
	//clear cron jobs	
	mycbgenie_suspend_cron_jobs();
	
	//delete product attribute master
	mycbgenie_delete_product_attribute_taxonomy();
	
	//clear all installtion entries
	mycbgenie_delete_all_entries('ALL','permanent');	

	//delete all options
	delete_option( 'mycbgenie_excluded_terms' );
	delete_option( 'mycbgenie_account_no' );
	delete_option( 'mycbgenie_products_per_page' );
	delete_option( 'mycbgenie_show_price' );
	delete_option( 'mycbgenie_sf_show_descr' );
	delete_option( 'mycbgenie_sf_show_thumbnails' );
	delete_option( 'mycbgenie_sf_bg_thumbnails' );	
	delete_option( 'mycbgenie_sf_thumbnail_location' );	
	delete_option( 'mycbgenie_cb_tracking_id' );
	delete_option( 'mycbgenie_imported_main_terms' );
	delete_option( 'mycbgenie_imported_sub_terms' );
	delete_option( 'mycbgenie_disabled_products' );
	delete_option( 'mycbgenie_featured_products' );
	delete_option( 'mycbgenie_custom_edited_products' );
	delete_option( 'mycbgenie_cron_per_batch_no_products' );
	delete_option( 'mycbgenie_cron_batch_frequency' );
	delete_option( 'mycbgenie_version');
	delete_option( 'mycbgenie_product_image_quality');
	delete_option( 'mycbgenie_review_menu_check');
	delete_option( 'mycbgenie_menu_check');

	
	



	
	//DROP TABLES CREATED;	
	global $wpdb;
	$table_name = "mycbgenie_custom_edited_products";
	$sql = "DROP TABLE " . $table_name . ";";
	$wpdb->query($sql);	
	
	$table_name = "mycbgenie_fresh_import_products_master";
	$sql = "DROP TABLE " . $table_name . ";";
	$wpdb->query($sql);	
	
	$table_name = "mycbgenie_fresh_import_product_details";
	$sql = "DROP TABLE " . $table_name . ";";
	$wpdb->query($sql);	


	$table_name = "mycbgenie_manual_sync_products_master";
	$sql = "DROP TABLE " . $table_name . ";";
	$wpdb->query($sql);
	
	$table_name = "mycbgenie_cron_sync_master";
	$sql = "DROP TABLE " . $table_name . ";";
	$wpdb->query($sql);
	
	mycbgenie_sync_wordpress_uninstall_or_deactivate();
	
	delete_user_meta(get_current_user_id(),'mycbgenie_product_screen_dismiss_option');
	delete_user_meta(get_current_user_id(),'mycbgenie_import_screen_dismiss_option');
	delete_user_meta(get_current_user_id(),'mycbgenie_screen_option_products_per_page');
	
	//delete import direceotry
	 WP_Filesystem();
	 $destination = wp_upload_dir();
	 $zip_file= $destination['basedir'].'/mycbgenie/';
	 if (file_exists($zip_file)) { rmdir ($zip_file); }
	
}

function mycbgenie_sync_wordpress_uninstall_or_deactivate(){

	$remote_url = 'https://mycbgenie.com/php/sync_wordpress_installs/de-activation.php';
	$url=	get_site_url();
	
	
	$response = wp_remote_post( $remote_url, array(
		//'method' => 'POST',
		//'timeout' => 45,
		//'redirection' => 5,
		//'httpversion' => '1.0',
		//'blocking' => true,
		//'headers' => array(),
		'body' => array( 'url' =>  $url, 'endata' => md5($url) )
		//'cookies' => array()
		)
	);

	if ( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
	  	//echo "Something went wrong: $error_message";
	} else {
	  // echo 'Response:<pre>';
	//   print_r( $response );
 	 // echo '</pre>';
	}

}

function mycbgenie_sync_wordpress_installs(){


	$remote_url = 'https://mycbgenie.com/php/sync_wordpress_installs/activation.php';
	$url=	get_site_url();
	
	
	$response = wp_remote_post( $remote_url, array(
		//'method' => 'POST',
		//'timeout' => 45,
		//'redirection' => 5,
		//'httpversion' => '1.0',
		//'blocking' => true,
		//'headers' => array(),
		'body' => array( 'url' =>  $url, 'endata' => md5($url) )
		//'cookies' => array()
		)
	);

	if ( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
	  	//echo "Something went wrong: $error_message";
	} else {
	 //  echo 'Response:<pre>';
	 //  print_r( $response );
 	 // echo '</pre>';
	}

}


function mycbgenie_delete_all_entries($delete_all,$permanent_temp)
{


global $wpdb;



		$wpdb->query(	"DROP TABLE IF EXISTS poststodelete" );
		$sql="CREATE TABLE poststodelete (ID bigint(20) unsigned NOT NULL,PRIMARY KEY(ID))"	;
		$wpdb->query( $sql );
			
									
		if ($delete_all==='ALL') {

			$wpdb->query(	"INSERT INTO poststodelete
							   (	SELECT ID FROM ". $wpdb->prefix."posts a 
 									INNER JOIN	". $wpdb->prefix."postmeta AS mt1 ON a.id=mt1.post_id
									WHERE a.post_type IN  ('product','mcg_thumbnail') 
									AND mt1.meta_key = '_mycbgenie_managed_by' )"
							);
					
		
		//	
							   
							
							
											if ($wpdb->last_error) {
								return 
								" Error in  INSERT INTO poststodelete (first entries) '" . $wpdb->last_error;	
							
								}		
		}
		elseif (substr_count($delete_all,"**")>0){
		
			//$post_id=$delete_all;
			
			$tmp_array=array();
			
			$tmp_array=explode("**",$delete_all);
			
			
			foreach($tmp_array as $tmp_arr){
	
				$wpdb->query(	"INSERT INTO poststodelete values (".$tmp_arr.")"	);
			}
		
		}
		else {
		
				$post_id=$delete_all;

				$wpdb->query(	"INSERT INTO poststodelete values (".$post_id.")"	);

		
		
		}
		//$wpdb->query(	"INSERT INTO poststodelete values(16714)");
	


	
			//revisions & attachments
			$wpdb->query(	"INSERT INTO poststodelete
							   (	SELECT a.ID FROM ". $wpdb->prefix."posts a 
							   		INNER JOIN poststodelete b on a.post_parent=b.id)
 									"		);
						
								if ($wpdb->last_error) {
			
								return 
								" Error in  INSERT INTO poststodelete table ( revisions and attachments)" . $wpdb->last_error;	
							
								}	
			
	
			
			//comment meta and comments
			$wpdb->query(	"
								DELETE a, b
								FROM ". $wpdb->prefix."commentmeta a
								inner join ". $wpdb->prefix."comments b on a.comment_id=b.comment_id
								inner join poststodelete c on b.comment_post_ID=c.id"	);
						
								if ($wpdb->last_error) {
			
								return 
								" Error in  deleting ( comment meta and comments)" . $wpdb->last_error;	
							
								}	
		
								
			//wp_termsmeta
			$wpdb->query(	"	DELETE mt FROM ". $wpdb->prefix."termmeta mt 
								INNER JOIN ". $wpdb->prefix."terms tr ON tr.term_id=mt.term_id
								INNER JOIN ". $wpdb->prefix."term_taxonomy tm  ON tm.term_id=tr.term_id 								
								INNER JOIN ". $wpdb->prefix."term_relationships a ON a.term_taxonomy_id=tm.term_taxonomy_id
								INNER JOIN poststodelete c ON	c.id=a.object_id"
								);
						
								if ($wpdb->last_error) {
								return 
								" Error in  deleting ( wp_termsmeta)" . $wpdb->last_error;	
							
								}
											
			//wp_terms_taxonomy & terms			
			if ($permanent_temp == "temp") {
			
						$sql=	"DELETE tt, tr 
									FROM ". $wpdb->prefix."term_taxonomy tt 
									INNER JOIN ". $wpdb->prefix."term_relationships a ON a.term_taxonomy_id=tt.term_taxonomy_id
									INNER JOIN poststodelete c ON	c.id=a.object_id
									INNER JOIN ". $wpdb->prefix."terms tr ON tr.term_id=tt.term_id 
									WHERE tt.taxonomy<>'pa_mycbgenie-star-rating' and tt.taxonomy<>'product_cat' and tt.taxonomy<>'product_type'  ";
			}
			else{
						$sql=	"DELETE tt, tr 
									FROM ". $wpdb->prefix."term_taxonomy tt 
									INNER JOIN ". $wpdb->prefix."term_relationships a ON a.term_taxonomy_id=tt.term_taxonomy_id
									INNER JOIN poststodelete c ON	c.id=a.object_id
									INNER JOIN ". $wpdb->prefix."terms tr ON tr.term_id=tt.term_id 
									WHERE tt.taxonomy in ('pa_mycbgenie-star-rating','product_tag') "
									;

					$wpdb->query(	$sql);
							
					if ($wpdb->last_error) {return 	" Error in  deleting ( wp_terms_taxonomy & terms)" . $wpdb->last_error;	}
				
			}
										
				

					
								
								
								
			//wp_terms_relationship
			$wpdb->query(	"	DELETE a
								FROM ". $wpdb->prefix."term_relationships a
								inner join poststodelete b on a.object_id=b.id"	);
						
								if ($wpdb->last_error) {
			
								return 
								" Error in  deleting ( wp_terms_relationship)" . $wpdb->last_error;	
							
								}
								
			//wp_postmeta
			$wpdb->query(	"	DELETE a
								FROM ". $wpdb->prefix."postmeta a
								inner join poststodelete b on a.post_id=b.id"	);
						
								if ($wpdb->last_error) {
			
								return 
								" Error in  deleting ( wp_postmeta)" . $wpdb->last_error;	
							
								}
								
			//wp_posts
			$wpdb->query(	"	DELETE a
								FROM ". $wpdb->prefix."posts a
								inner join poststodelete b on a.id=b.id"	);
						
								if ($wpdb->last_error) {
			
								return 
								" Error in  deleting ( wp_posts)" . $wpdb->last_error;	
							
								}
								
			//update taxonomy count
			$wpdb->query(	"UPDATE ". $wpdb->prefix."term_taxonomy t 
								set count=(select count(*) from ". $wpdb->prefix."term_relationships 
								where term_taxonomy_id=t.term_taxonomy_id)"	);
						
								if ($wpdb->last_error) {
			
								return 
								" Error in  updating taxonomy count" . $wpdb->last_error;	
							
								}
								
			//� delete temporary table
			$wpdb->query(	" DROP TABLE IF EXISTS poststodelete"	);
						
								if ($wpdb->last_error) {
			
								return 
								" Error in  droping temp table..." . $wpdb->last_error;	
							
								}
			if ($permanent_temp == "temp") {}
			else	{


					//deleting mycbgenie defined categories		
					$mycbgenie_imported_main_arr	= get_option('mycbgenie_imported_main_terms');
					$tmp_push=array();
					//var_dump(($mycbgenie_imported_main_arr));
		
					foreach ( $mycbgenie_imported_main_arr as $mycbgenie_imported_array) {
					//echo $include_main_arr['slug'].'<br>';
						array_push($tmp_push, $mycbgenie_imported_array['slug']);
					}
					
					$mycbgenie_imported_arr	= get_option('mycbgenie_imported_sub_terms');
			
					foreach ( $mycbgenie_imported_arr as $mycbgenie_imported_array) {
						//echo $include_main_arr['slug'].'<br>';
						array_push($tmp_push, $mycbgenie_imported_array['slug']);
					}
					//var_dump(($mycbgenie_imported_arr));
					//return;			
					
					//echo count($tmp_push);
		
					//print_r(array_values($tmp_push));
						//		echo "---------------------------------------<hr>";
						//print_r($tmp_push);

		
					if (count($tmp_push)>0){
						$sql = " DELETE  a,b from ". $wpdb->prefix."terms 	a
								 INNER JOIN ". $wpdb->prefix."term_taxonomy b ON ( a.term_id = b.term_id )
								 WHERE a.slug IN (".implode(', ', array_fill(0, count($tmp_push), '%s')).")			";
				
						// Call $wpdb->prepare passing the values of the array as separate arguments
						$query = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $tmp_push));
						$wpdb->query($query);
												 
						 if ($wpdb->last_error) {
									return " Error in  deleting terms'" . $wpdb->last_error;	
									}
					}
			}//temp or permaanr
			
//echo $wpdb->rows_affected . ' rows affected';
//echo $query;
//echo "<hr>".$sql;


}


function mycbgenie_delete_product_attribute_taxonomy(){

global $wpdb;

			$wpdb->query(	"
								DELETE 
								FROM ". $wpdb->prefix."woocommerce_attribute_taxonomies 
								WHERE attribute_name='mycbgenie-star-rating'"	
						);
}

function mycbgenie_add_product_attribute_taxonomy(){


//mycbgenie_delete_all_entries('ALL','temp');
//return;




global $wpdb;

			$sql="SELECT count(*)  FROM ".$wpdb->prefix ."woocommerce_attribute_taxonomies where attribute_name='mycbgenie-star-rating'" ;
			$result = absint($wpdb->get_var( $sql ));
			
			
if($result==0) {	
		    $insert = $wpdb->insert(
            $wpdb->prefix . 'woocommerce_attribute_taxonomies',
            array(
                'attribute_label'   => 'CB Rating',
                'attribute_name'    => 'mycbgenie-star-rating',
                'attribute_type'    => 'select',
                'attribute_orderby' => 'order_by',
                'attribute_public'  => 1
            ),
            array( '%s', '%s', '%s', '%s', '%d' )
        );

        if ( is_wp_error( $insert ) ) {
            throw new WC_API_Exception( 'woocommerce_api_cannot_create_product_attribute', $insert->get_error_message(), 400 );
        }

        // Clear transients
        delete_transient( 'wc_attribute_taxonomies' );
} //end of result==0



}

function mycbgenie_add_product_attributes()
{
//mycbgenie_insert_main_category_term();
//mycbgenie_insert_sub_category_term();
//return;
//mycbgenie_activate();


global $wpdb;
	
		$ids=wp_insert_term(
		  ' &nbsp;&#10026; &#10026; &#10026; &#10026; &#10026; &nbsp;', // the term 
		  'pa_mycbgenie-star-rating', // the taxonomy
		  array(
			'description'=> '5 star rating',
			'slug' => 'mycbgenie-5-star-rating',
			'parent'=> 0
		  )
		);
	    if ( is_wp_error( $ids ) ) {
					//echo 'error';
		}
				
				
				
				
		$ids=	wp_insert_term(
		  ' &nbsp;&#10026; &#10026; &#10026; &#10026; &nbsp;', // the term 
		  'pa_mycbgenie-star-rating', // the taxonomy
		  array(
			'description'=> '4 star rating',
			'slug' => 'mycbgenie-4-star-rating',
			'parent'=> 0
		  )
		);
	    if ( is_wp_error( $ids ) ) {
					//echo 'error';
		}
		
		
		
				
			$ids=	wp_insert_term(
		  ' &nbsp;&#10026; &#10026; &#10026; &nbsp;', // the term 
		  'pa_mycbgenie-star-rating', // the taxonomy
		  array(
			'description'=> '3 star rating',
			'slug' => 'mycbgenie-3-star-rating',
			'parent'=> 0
		  )
		);
	    if ( is_wp_error( $ids ) ) {
				//	echo 'error';
		}
		
		
				
		
		$ids=	wp_insert_term(
		  ' &nbsp;&#10026; &#10026; &nbsp;', // the term 
		  'pa_mycbgenie-star-rating', // the taxonomy
		  array(
			'description'=> '2 star rating',
			'slug' => 'mycbgenie-2-star-rating',
			'parent'=> 0
		  )
		);
	    if ( is_wp_error( $ids ) ) {
					//echo 'error';
		}
		
		
				
		
		$ids=	wp_insert_term(
		  ' &nbsp;&#10026; &nbsp;', // the term 
		  'pa_mycbgenie-star-rating', // the taxonomy
		  array(
			'description'=> '1 star rating',
			'slug' => 'mycbgenie-1-star-rating',
			'parent'=> 0
		  )
		);
	    if ( is_wp_error( $ids ) ) {
					//echo 'error';
		}
		
		

}


function mycbgenie_sf_file_get_contents_curl($url) {
			
				$ch = curl_init();
			
				curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
			
				$data = curl_exec($ch);
				curl_close($ch);
			
				return $data;
}
			
			




function mycbgenie_fetch_media($file_url, $slug, $post_id) {
			
				require_once(ABSPATH . 'wp-load.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				global $wpdb;
			
				if(!$post_id) {
				//	return false;
				}
			
				$artDir = 'wp-content/uploads/mycbgenie/media/thumbnails/';
		
				//rename the file... alternatively, you could explode on "/" and keep the original file name
				$ext = array_pop(explode(".", $file_url));
				$new_filename = $slug.".".$ext; //if your post has multiple files, you may need to add a random number to the file name to prevent overwrites

	
			
				if (@fclose(@fopen($file_url, "r"))) { //make sure the file actually exists
					copy($file_url, ABSPATH.$artDir.$new_filename);

				$siteurl = get_option('siteurl');
					$file_info = getimagesize(ABSPATH.$artDir.$new_filename);
					
					$uploads = wp_upload_dir();
					$save_path = $uploads['basedir'].'/mycbgenie/media/thumbnails/'.$new_filename;
					
					$sql="SELECT * FROM ". $wpdb->prefix."posts 
				 	WHERE ( post_type='attachment' and post_name='".sanitize_title_with_dashes(str_replace("_", "-", $new_filename))."' and  post_parent=".$post_id."  ) ";
		
					 $result = $wpdb->get_results( $sql );

	 				$count_posts	=	intval(	count($result)	);

					if ($count_posts >0 ) {
					
							$attach_id	=	$result[0]->ID;
							update_attached_file( $attach_id, $save_path );
					}else{
							//create an array of attachment data to insert into wp_posts table
							$artdata = array();
							$artdata = array(
								'post_author' => 1, 
								'post_date' => current_time('mysql'),
								'post_date_gmt' => current_time('mysql'),
								'post_title' => $new_filename, 
								'post_status' => 'inherit',
								'comment_status' => 'closed',
								'ping_status' => 'closed',
								'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $new_filename)),
								'post_modified' => current_time('mysql'),
								'post_modified_gmt' => current_time('mysql'),
								'post_parent' => $post_id,
								'post_type' => 'attachment',
								'guid' => $siteurl.'/'.$artDir.$new_filename,
								'post_mime_type' => $file_info['mime'],
								'post_excerpt' => '',
								'post_content' => ''
							);
			
						//insert the database record
						$attach_id = wp_insert_attachment( $artdata, $save_path, $post_id );
				}// end of count(posts)>0
									
	

				return $attach_id;
					/*
							//generate metadata and thumbnails
							if ($attach_data = wp_generate_attachment_metadata( $attach_id, $save_path)) {
								wp_update_attachment_metadata($attach_id, $attach_data);
							}
					
							//optional make it the featured image of the post it's attached to
							//$rows_affected = $wpdb->insert($wpdb->prefix.'postmeta', array('post_id' => $post_id, 'meta_key' => '_thumbnail_id', 'meta_value' => $attach_id));
					*/
				}
				else {
					return false;
				}
			
				return true;
}



function mycbgenie_fetch_product_images($file_url, $mycbgenie_id, $file_name, $post_id, $altimage, $screenshot_allowed, $import_or_sync) {


	$error_message="";


	
	if ($screenshot_allowed=="yes" ) {
	
	
	
	}else { //$screenshot_allowed=="no"
			//wp_die($file_url."<br>".$post_id."<br>sync:".$import_or_sync."<br>".$file_name);
			
			if ($file_name=="blank.gif")  { // return, no need to generate thumbnails. Delete thumbnails if SYNC is being processed.
			
				if ($import_or_sync == "sync") {
				

				
						$post_delete=get_post_meta($post_id,'_thumbnail_id',true);
						if ($post_delete) wp_delete_attachment( $post_delete , true);
				}
				
	//wp_die($file_url."<br>".$post_id."<br>".$import_or_sync);
				
				
				return;
			}
	}
	
	if (substr_count($file_url,"cbproads.com/cbbanners/blank.gif")==0) {}
	else { return; }



	
		
		
					
				$mycbgenie_id	=  sanitize_title(str_replace(".", "-", $mycbgenie_id));
				global $wpdb;
					
				$artDir = 'wp-content/uploads/mycbgenie/media/product_images/';
	
				//rename the file... alternatively, you could explode on "/" and keep the original file name
				$ext = array_pop(explode(".", $file_url));
				$new_filename = $mycbgenie_id.".".$ext; //if your post has multiple files, you may need to add a random number to the file name to prevent overwrites


						
				$ch = curl_init($file_url);
				$fp = fopen(ABSPATH.$artDir.$new_filename, 'wb');
				//use this line in some servers espectially in WordPress.com
				//$fp = fopen($_SERVER['DOCUMENT_ROOT'].'/'.$artDir.$new_filename, 'wb');
				
				//kindly comment htis line if any issue. Behave differemtly on different servcers
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
				
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);//for ssl
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);//for ssl
				$resp=curl_exec($ch);
				curl_close($ch);
				fclose($fp);
				
			
			
				$siteurl = get_option('siteurl');
				
			
			
				$file_info = getimagesize(ABSPATH.$artDir.$new_filename);
				//use this line in some servers espectially in WordPress.com
				//$file_info = getimagesize($_SERVER['DOCUMENT_ROOT'].'/'.$artDir.$new_filename);
					
					$uploads = wp_upload_dir();
					$save_path = $uploads['basedir'].'/mycbgenie/media/product_images/'.$new_filename;
					
					$sql="SELECT * FROM ". $wpdb->prefix."posts 
				 	WHERE ( post_type='attachment' and post_name='".$mycbgenie_id."' and  post_parent=".$post_id."  ) ";
		
	 $result = $wpdb->get_results( $sql );

	 				$count_posts	=	intval(	count($result)	);

					if ($count_posts >0 ) {
					
							$attach_id	=	$result[0]->ID;
							update_attached_file( $attach_id, $save_path );
					}else{
							//create an array of attachment data to insert into wp_posts table
							$artdata = array();
							$artdata = array(
								'post_author' => 1, 
								'post_date' => current_time('mysql'),
								'post_date_gmt' => current_time('mysql'),
								'post_title' => $file_name, 
								'post_status' => 'inherit',
								'comment_status' => 'closed',
								'ping_status' => 'closed',
								'post_name' => $mycbgenie_id,
								'post_modified' => current_time('mysql'),
								'post_modified_gmt' => current_time('mysql'),
								'post_parent' => $post_id,
								'post_type' => 'attachment',
								'guid' => $siteurl.'/'.$artDir.$new_filename,
								'post_mime_type' => $file_info['mime'],
								'post_excerpt' => '',
								'post_content' => ''
							);
			
						//insert the database record
						$attach_id = wp_insert_attachment( $artdata, $save_path, $post_id );
				}// end of count(posts)>0
									

						set_post_thumbnail( $post_id, $attach_id );
	
						//generate metadata and thumbnails
						if ($attach_data = wp_generate_attachment_metadata( $attach_id, $save_path)) {
							wp_update_attachment_metadata($attach_id, $attach_data);
						}
	
		//}
				

			
				
}



function mycbgenie_jpeg_quality_control( $metadata, $attachment_id ) 
{



    $file = get_attached_file( $attachment_id );
    $type = get_post_mime_type( $attachment_id );

    // Target jpeg images
    if( in_array( $type, array( 'image/jpg', 'image/jpeg', 'image/png', 'image/gif' ) ) )
    {
	

        // Check for a valid image editor
        $editor = wp_get_image_editor( $file );
        if( ! is_wp_error( $editor ) )
        {

            // Set the new image quality
            $result = $editor->set_quality( 45 );
					 // $editor->resize( 135, 135, true );
		    $editor->resize( 300, 300, true );
		    // Re-save the original image file
            if ( ! is_wp_error( $result ) ) {
                $editor->save( $file );  
				
 			}
			
        }
		
	
		
		
    }   
    return $metadata;
}




function mycbgenie_sideload_remote_JSON_thumbnail_download_unzip(){



    WP_Filesystem();
    $destination = wp_upload_dir();
	
	$artDir = $destination['basedir'].'/mycbgenie/';

   if(!file_exists($artDir)) {
					mkdir($artDir);
	}
	
	$artDir = $destination['basedir'].'/mycbgenie/media/';
   
   if(!file_exists($artDir)) {
					mkdir($artDir);
	}
	

	
	$artDir = $destination['basedir'].'/mycbgenie/media/thumbnails_extract/';
   
   if(!file_exists($artDir)) {
					mkdir($artDir);
	}		
		
	$zip_file	=	$destination['path'].'/thumbnails.zip';
	$dest_dir	=	$destination['basedir'].'/mycbgenie/media/thumbnails_extract/';
	$url		=	"https://cbproads.com/xmlfeed/woocommerce/zip/category_thumbnails/thumbnails.zip";

   
		if (file_exists($zip_file)) {		 unlink($zip_file);	}
		

		//echo $process_type;
		//var_dump($url); 

		// download file to temp dir
		$temp_file = download_url( $url, 300 );

		if (!is_wp_error( $temp_file )) {

		
				// array based on $_FILE as seen in PHP file uploads
				$file = array(
					'name' => basename($url), // ex: wp-header-logo.png
					//'type' => 'image/png',
					'tmp_name' => $temp_file,
					'error' => 0,
					'size' => filesize($temp_file),
				);

				$overrides = array(
					'test_form' => false,
					// setting this to false lets WordPress allow empty files, not recommended
					'test_size' => true,
					'test_upload' => true, 
				);

				// move the temporary file into the uploads directory
				$results = wp_handle_sideload( $file, $overrides );

				if (!empty($results['error'])) {
						
					// insert any error handling here
						$error_message ="Error in wp_handle_sideload, Error : ".   $results['error'];   
			 			 return json_encode (array  ('error_message'	=> $error_message));
				} else {
			
					$filename = $results['file']; // full path to the file
					$local_url = $results['url']; // URL to the file in the uploads dir
					$type = $results['type']; // MIME type of the file
					
				}
		}
		else{
						wp_die("Please check your ISP connection /speed. \n\n".$temp_file->get_error_message()."\n\n");		   						
						$error_message ="Error in downloading file from ". $url." , Error : ".   $temp_file->get_error_message();   
			 			 return json_encode (array  ('error_message'	=> $error_message));
		}

		//	wp_die($skip_file_import.">B".$json_file_change_detected.">".$throttle_speed.">".$process_type.">".$zip_file.">".$dest_dir);		   			
		$unzipfile = unzip_file(  $zip_file , $dest_dir) ;
					


					//wp_die("dd");
					//$unzipfile = unzip_file(  $zip_file , 'C:\wamp\www\mycbgenie_wp/wp-content/uploads/');

		   if ( $unzipfile ) {
			// echo 'Successfully unzipped the file!';       
		   } else {
			 // echo 'There was an error unzipping the file.';
			  $error_message ="Error in unzipping the thumbnail file ".   $zip_file;   
			  return json_encode (array  ('error_message'	=> $error_message));
		   }
		   
		   //echo $destination;
			

}



if (!function_exists('get_post_id_by_meta_key_and_value')) {
 function get_post_id_by_meta_key_and_value($key, $value) {
   global $wpdb;
   $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."'");
   if (is_array($meta) && !empty($meta) && isset($meta[0])) {
      $meta = $meta[0];
      }   
   if (is_object($meta)) {
      return $meta->post_id;
      }
   else {
      return false;
      }
   }
}

function mycbgenie_customizing_woocommerce_description( $content ) {

    // Only for single product pages (woocommerce)
    if ( is_product() ) {
		
		
		$tt=get_post_meta( get_the_ID(), '_mycbgenie_id', true );
		
//		$read_more_link="<div  style='margin-top:30px; margin-bottom:30px; margin-left:40%; margin-right:auto;'>  <button><a href='".get_post_meta( get_the_ID(), '_product_url', true )."' style='font-weight:bold;color:white;' target='_blank'> Buy this product now</a></button> </div>";
		$read_more_link="<div  style='margin-top:30px; margin-bottom:30px; margin-left:40%; margin-right:auto;'>  <button><a href='".str_replace("store","review",get_post_meta( get_the_ID(), '_product_url', true ))."' style='font-weight:bold;color:white;' target='_blank'> Buy this product now</a></button> </div>";
		
		//$tt=get_post_id_from_meta( '_mycbgenie_id',$tt );
		$postID = get_post_id_by_meta_key_and_value('_mycbgenie_id_code', $tt);

		$content_post = get_post($postID);
		$content_post = $content_post->post_content;

        // Inserting the custom content at the end
        if ($postID>0) {
			$content = $content_post.$read_more_link;
			
 		}
    }
    return $content;
}

function mycbgenie_change_product_tab_title_on_review_yes_no($title){
    if ( is_product() ) {
		$tt=get_post_meta( get_the_ID(), '_mycbgenie_id', true );
		//$tt=get_post_id_from_meta( '_mycbgenie_id',$tt );
		$postID = get_post_id_by_meta_key_and_value('_mycbgenie_id_code', $tt);
        if ($postID>0) {
			return 'REVIEW';
 		}else{return  $title;}
    }
}


function mycbgenie_change_product_tab_main_title($tabs){
    if ( is_product() ) {
		$tt=get_post_meta( get_the_ID(), '_mycbgenie_id', true );
		//$tt=get_post_id_from_meta( '_mycbgenie_id',$tt );
		$postID = get_post_id_by_meta_key_and_value('_mycbgenie_id_code', $tt);
        if ($postID>0) {
			$tabs['description']['title'] = 'REVIEW BY ADMIN';
			$tabs['reviews']['title'] = 'OTHER REVIEWS (1)';
			
 		}
		$tabs['additional_information']['title'] = 'Rating';
		return $tabs;
    }
}



function mycbgenie_header_files()
{


	wooCommerce_load_check();
?><!--background: //#E6E6E6-->

	<style type="text/css">
	div.inline { float:left;  }
	.clearBoth { clear:both; }
	</style>
	
	
<div align=center class="wrap" style="border:0px ridge #3877A1;  height:auto; margin-right:0px; border-radius:5px; padding:0px;  color:#333333;


">


	<div align="left" class="inline"  style="float:left; margin:0%; width:99%; border:1px solid #dcdcdc;  padding:5px; border-radius:5px; padding-left:10px;">
		<a href="http://mycbgenie.com" target=_blank>
		<img style='margin-right:0px; margin-top:10px;' width=201 height=70 src='<?php echo plugin_dir_url( __FILE__ )  ?>/images/MyCBGenie_logo.png' ></a>
		<div style="float:right; margin-right:10px; vertical-align:bottom; ">&nbsp;<br /><br /><br />Storefront Plugin Version : <?php echo get_option('mycbgenie_version');?></div>
	</div>
	
	
	
	<!--	<div class="wrap" style=" height:auto; margin-top:0px; margin-right:0%; 
								background-color: #3877A1 ;  border-radius:7px; padding:20px; 
								text-align:center; font-size:21px; font-weight:bold; 
								
								line-height:190%;
								font-size: 15px; text-align:right;
								
color: #ffffff; text-shadow: rgb(30,30,30) 0px 1px 1px; 
								

background: linear-gradient(308deg, #FFFFFF 25%, #3877A1 52%);
background: -moz-linear-gradient(308deg, #FFFFFF 25%, #3877A1 52%);
background: -webkit-linear-gradient(308deg, #FFFFFF 25%, #3877A1 52%);
background: -o-linear-gradient(308deg, #FFFFFF 25%, #3877A1 52%); 

border:1px solid #3877A1;
"> 

				<i>The <q>FIRST &#38; ONLY</q> WordPress Plugin Ever Built To Have The Products From <br>
				The 
					
					<strong>ClickBank&reg;</strong> - The World's Highest Paying Affiliate Products Network, 
				
				
				Integrated <br>In To The World's Most Popular e-Commerce Storefront Platform -
			
				WooCommerce!
				</i>
					
		</div>
	-->
	
</div>


<br /><br />&nbsp;<br class="clearBoth" />

<?php

}


function mycbgenie_add_products(){

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	echo '<div class="wrap" style="margin-top: 35px;">';
		mycbgenie_header_files();
		//mycbgenie_show_tabs();
	?>
	<h1>Adding Custom Products</h1>
	<div><br />
		As you might be knowing, <strong>MyCbGenie</strong> is exclusively for importing and maintaining the products from <strong>ClickBank's Marketplace</strong>. In case, you wish to feature your own products along with these <strong>Clickbank products</strong> to your storefront, you can add them directly from your <strong>WoooCommerce</strong> screen. <strong>WooCommerce</strong> is an excellent Wordpress plugin which will allow you to turn any WordPress site in to an online shopping cart at no extra cost. <br /><br />You may please add your own <strong>affiliate products/shopping cart</strong> 
		products from the link :  
				<div style="padding-left:2em;">		<strong><u>WooCommerce</u></strong> -> <strong><u>Products</u></strong> - > 
				<a href="post-new.php?post_type=product" target="_blank">Add Product</a> screen. 
				</div>
		
	
	</div>
	<?php
	echo '</div>';
	
	
}




  // Add settings link on plugin page
function mycbgenie_settings_link($links)


{
	$settings_link =  '<a href="options-general.php?page=mycbgenie_main_menu">
					Settings</a>';
    array_unshift($links, $settings_link);
	
	
	    $settings_link =  '<img src="'. plugin_dir_url( __FILE__ ).'/images/fav.jpg" style="height:40px; width:auto;"><a href="admin.php?page=mycbgenie_main_menu_import">
					Import Products</a>';
    array_unshift($links, $settings_link);
	
	
    
    return $links;
}

?>
