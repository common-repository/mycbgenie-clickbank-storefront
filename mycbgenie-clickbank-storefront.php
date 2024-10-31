<?php
/*
* Plugin Name: MyCBGenie Clickbank Storefront Plugin
* Plugin URI: http://mycbgenie.com
* Description: Not just an another Clickbank Storefront plugin! Designed to excel with the world's favorite eCommerce plugin - WooCommerce !
* Version: 1.7
* Author: MyCBGenie.com
* Author URI: http://www.mycbgenie.com
*/

if (!defined('MYCBGENIE_ACTIVE_VERSION'))
    define('MYCBGENIE_ACTIVE_VERSION', '1.7');





if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	


require_once 'functions.inc.php'; // Defines all functions
require_once 'functions_gen_settings.inc.php';
require_once 'functions_fresh_install_import.inc.php';
require_once 'functions_manual_sync.inc.php';
require_once 'functions_cron.inc.php';
require_once 'functions_reviews.inc.php';
require_once 'functions_products.inc.php';
require_once 'functions_category_exclude.inc.php';
require_once 'img/img.inc.php';
require_once 'redirect.inc.php';
require_once 'special_searches.inc.php'; 


	
	
//this applies to only for Orchid theme product decription
add_action('orchid_store_shop_loop_item_desc','mycbgenie_store_template_loop_product_desc',10);
function mycbgenie_store_template_loop_product_desc(){

    echo '<p style="font-size:0.9em;  text-align:left; color:#808080;  overflow: hidden;    text-overflow: ellipsis;   display: -webkit-box;   -webkit-line-clamp: 3; /* number of lines to show */           line-clamp: 3;    -webkit-box-orient: vertical;">' 
    .  ucwords(strtolower(wp_kses_post( get_the_excerpt() ))) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  
}
//end of orchid proeduct description


function mycbgenie_plugin_setup_menu(){


        add_menu_page( 'MyCBGenie', 'My CB Genie', 'manage_options', 'mycbgenie_main_menu', 'mycbgenie_dashboard',
		plugin_dir_url( __FILE__ ) .'images/fav_small.ico',25);//mycbgenie_dashboard


		add_submenu_page( 'mycbgenie_main_menu',    'Settings',    'Settings', 'manage_options', 'mycbgenie_main_menu','mycbgenie_dashboard');


    	//add_submenu_page( 'mycbgenie_main_menu',    'Import',    'Fresh Import / SYNC', 'manage_options', 'mycbgenie_main_menu_import','test57');				
 		add_submenu_page( 'mycbgenie_main_menu',    'Import',    'Import / SYNC Products', 'manage_options', 'mycbgenie_main_menu_import','mycbgenie_cb_fresh_import');
		//mycbgenie_reverse_woocommerce_image_dimensions //mycbgenie_cb_fresh_import
		
		
		
		add_submenu_page( 'mycbgenie_main_menu',    'Add Products',    'Add Products', 'manage_options', 'mycbgenie_main_menu_add_products','mycbgenie_add_products');
				
				//mycbgenie_add_products


				



	
	

	
	//	$hook=add_submenu_page( 'mycbgenie_main_menu',    'Products',    'Products', 'manage_options', 'mycbgenie_custom_products','test56');
//		$hook=add_submenu_page( 'mycbgenie_main_menu',    'Products',    'Products', 'manage_options', 'mycbgenie_custom_products','mycbgenie_insert_sub_category_term');
	//	$hook=add_submenu_page( 'mycbgenie_main_menu',    'Products',    'Products', 'manage_options', 'mycbgenie_custom_products','mycbgenie_add_product_attributes');
//		$hook=add_submenu_page( 'mycbgenie_main_menu',    'Products',    'Products', 'manage_options', 'mycbgenie_custom_products','mycbgenie_uninstall');
	$hook=add_submenu_page( 'mycbgenie_main_menu',    'Products',    'Manage Products', 'manage_options', 'mycbgenie_custom_products','mycbgenie_display_products');

	//products screen add options
	add_action( "load-$hook", 'mycbgenie_products_screen_add_options' );
	
	

	
	
			//add_submenu_page( 'mycbgenie_main_menu',    'SYNC',    'SYNC Stats', 'manage_options', 'mycbgenie_cron','mycbgenie_cron_stats');
	//		add_submenu_page( 'mycbgenie_main_menu',    'SYNC',    'SYNC Stats', 'manage_options', 'mycbgenie_cron','mycbgenie_add_product_attribute_taxonomy');
	//mycbgenie_cb_fresh_import
}

function mycbgenie_wp_load_js(){

	
	
	//wp_localize_script('mycbgenie_sf_show_thumbnails', 'mycbgenie_url', array(
		 //   'pluginsUrl' => plugin_dir_url(__FILE__)
	//));
	

	wp_enqueue_style( 'progressbar', plugins_url('css/mycbgenie.css?v7', __FILE__) );
		
	wp_enqueue_script('mycbgenie_js_common', plugin_dir_url(__FILE__).'js/storefront_common.js', array('jquery'));
	
	wp_localize_script('mycbgenie_js_common', 'mycbgenie_sf_url', array(
    'pluginsUrl' => plugin_dir_url(__FILE__),
	'show_thumb_color'	=>		get_option( 'mycbgenie_sf_bg_thumbnails','EBEBEB' ),
	));
}

function mycbgenie_load_js(){


	//add_submenu_page( 'mycbgenie_main_menu',    'Reviews',    'Import Products Reviews', 'manage_options', 'mycbgenie_product_review_import','mycbgenie_product_review_import_fn');
	
	//style sheet for progress bar
	
	wp_enqueue_style( 'progressbar', plugins_url('css/progress_bar.css', __FILE__) );

	//media gallery upload form
   // wp_enqueue_script('media-upload');
   // wp_enqueue_script('thickbox');
   // wp_enqueue_script('upload_media_widget', plugin_dir_url(__FILE__) . 'js/media_upload.js', array('jquery'));
	//wp_enqueue_script('mycbgenie_js_media_upload', 		plugin_dir_url(__FILE__).'js/media_upload.js', array('jquery'));
	
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('mycbgenie_js_media_upload', plugin_dir_url(__FILE__).'js/media_upload.js', array('jquery','media-upload','thickbox'));
	//wp_enqueue_script('my-upload');	
   wp_enqueue_style('thickbox');
		
		
	//ajax operations 
	wp_enqueue_script('mycbgenie_js_import', 				plugin_dir_url(__FILE__).'js/ajax-clickbank-import.js', array('jquery'));
	wp_enqueue_script('mycbgenie_js_manual_sync',   		plugin_dir_url(__FILE__).'js/ajax-manual-sync.js', array('jquery'));
	wp_enqueue_script('mycbgenie_js_single_product_sync',   plugin_dir_url(__FILE__).'js/ajax-single-product-sync.js', array('jquery'));



	wp_enqueue_script('mycbgenie_js_category_exclude', 	plugin_dir_url(__FILE__).'js/ajax-category_exclude.js', array('jquery'));
	wp_enqueue_script('mycbgenie_js_general_settings', 	plugin_dir_url(__FILE__).'js/ajax-general_settings.js?id=4', array('jquery'));
		
	wp_localize_script('mycbgenie_js_import','mycbgenie_vars', array(
	'global_mycbgenie_nonce' => wp_create_nonce('local_mycbgenie_nonce'),
	'ajaxadminurl' => admin_url('admin-ajax.php')
	));
	
	
	wp_localize_script('mycbgenie_js_single_product_sync','mycbgenie_single_sync_vars', array(
	'mycbgenie_single_sync_nonce' => wp_create_nonce('local_mycbgenie_single_sync_nonce'),
	'ajaxadminurl' => admin_url('admin-ajax.php')
	));
	
	
	wp_localize_script('mycbgenie_js_manual_sync','mycbgenie_manual_sync_vars', array(
	'mycbgenie_manual_sync_nonce' => wp_create_nonce('local_mycbgenie_manual_sync_nonce'),
	'ajaxadminurl' => admin_url('admin-ajax.php')
	));
	
	/*wp_localize_script('mycbgenie_js_manual_sync_delete','mycbgenie_manual_sync_delete_vars', array(
	'mycbgenie_manual_sync_delete_nonce' => wp_create_nonce('local_mycbgenie_manual_sync_delete_nonce'),
	'ajaxadminurl' => admin_url('admin-ajax.php')
	));	
	*/
	
	wp_localize_script('mycbgenie_js_category_exclude','mycbgenie_cat_exclude_vars', array(
	'category_exclude_mycbgenie_nonce' => wp_create_nonce('local_cat_exclude_mycbgenie_nonce'),
	'ajaxadminurl' => admin_url('admin-ajax.php')
	));
	
	wp_localize_script('mycbgenie_js_general_settings','mycbgenie_general_settings_vars', array(
	'gen_setting_mycbgenie_nonce' => wp_create_nonce('local_gen_setting_mycbgenie_nonce'),
	'ajaxadminurl' => admin_url('admin-ajax.php')
	));
	


	//wp_localize_script('mycbgenie_js_media_upload','mycbgenie_media_upload_vars', array(
	//'media_upload_mycbgenie_nonce' => wp_create_nonce('local_media_upload_mycbgenie_nonce'),
	//'ajaxadminurl' => admin_url('admin-ajax.php')
	//));
	
	
}







//$test->list_table_page();



function mycbgenie_show_tabs(){

		//getting & setting active tab
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general_tab_1';
		if(isset($_GET['tab'])) $active_tab = $_GET['tab'];
		?>
		<!--div for displaying result confirmation -->
		<div class="updated" id="ajax-result_gen_settings" style="display:none;" ></div>
		 

		<h2>Dashboard</h2>
		<h2 class="nav-tab-wrapper">
		
		<a href="?page=mycbgenie_main_menu&amp;tab=general_tab_1" class="nav-tab 
		<?php echo $active_tab == 'general_tab_1' ? 'nav-tab-active' : ''; ?>">
				<?php _e('Settings', 'sample'); ?></a>
				
		<a href="?page=mycbgenie_main_menu&amp;tab=categories_tab_2" class="nav-tab 
				<?php echo $active_tab == 'categories_tab_2' ? 'nav-tab-active' : ''; ?>"><?php _e('Categories', 'sample'); ?></a>
		
		<a href="?page=mycbgenie_main_menu&amp;tab=cron_tab_3" class="nav-tab 
				<?php echo $active_tab == 'sample_tab_3' ? 'nav-tab-active' : ''; ?>"><?php _e('Cron Job', 'sample'); ?></a>

		</h2>


		<?php if($active_tab == 'general_tab_1') { ?>
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div class="postbox">
			<h3><?php _e('General Settings', 'sample'); ?></h3>
				<div class="inside">
				<p><?php _e(mycbgenie_general_settings(), 'sample'); ?></p>
				</div>
			</div>
		</div>

		<?php } if($active_tab == 'categories_tab_2') { ?>
		<div id="poststuff" class="ui-sortable meta-box-sortables">
		<div class="postbox">
		<h3><?php _e('Imported Clickbank Categories', 'sample'); ?></h3>
		<div class="inside"><p>Please tick the categories that you do <strong>NOT</strong> wish to display on your storefront.</p>
		<p><?php _e(mycbgenie_exclude_categories(), 'sample'); ?></p>
		</div>
		
		</div>
		</div>
		<?php } if($active_tab == 'cron_tab_3') { ?>
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<div class="postbox" style=background:#e0e0e0 ; >
			<h3><?php _e('&nbsp;&nbsp;Automatic Update (Cron Job) Settings', 'sample'); ?></h3>
				<div class="inside">
				<p><?php _e(mycbgenie_cron_settings(), 'sample'); ?></p>
				</div>
			</div>
		</div>
		<?php } ?>
		
		
		<?php
}




function mycbgenie_dashboard(){

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	echo '<div class="wrap" style="margin-top: 35px;">';
		mycbgenie_header_files();
		mycbgenie_show_tabs();
	echo '</div>';
}

	function create_posttype_mycbgenie_category_thumbnail() {
			register_post_type( 'mcg_thumbnail',
			// CPT Options
				array(
					'labels' => array(
						'name' => __( 'MyCBGenie thumbnail' ),
						'singular_name' => __( 'MyCBGeni' )
					),

					'exclude_from_search' => true,
					' publicly_queryable' => true,
					'rewrite' => array('slug' => 'mcg_thumbnail'),
				)

			 );

	}

	// Hooking up our function for custom post 'create_posttype_mycbgenie_category_thumbnail'
	add_action( 'init', 'create_posttype_mycbgenie_category_thumbnail' );
   add_action('init','mycbgenie_show_cbpro_images_on_demo');		
   add_action('after_setup_theme','mycbgenie_show_cbpro_images_on_demo_remove_hook');		
		
	//setting actions fors exclude terms
	mycbgenie_set_exclude_terms();
	
	//setting screen options
	add_filter('set-screen-option', 'mycbgenie_set_screen_option', 10, 3);
	
	//setting JPeg quality for imported images
	//add_filter( 'wp_generate_attachment_metadata','mycbgenie_jpeg_quality_control' , 10, 2 );

	//version change
	add_action('admin_init', 'mycbgenie_version_update');
//	add_action( 'upgrader_process_complete', 'mycbgenie_version_update', 9, 2 );


	//cron job
	add_action( 'mycronjob_mycbgenies',  'mycbgenie_cron_job_process' );
	add_filter('jpeg_quality', function($arg){return 100;});

  // Add menu to left side bar
	add_action('admin_menu', 'mycbgenie_plugin_setup_menu');

  // Add JS file for clickbank products import ajax.
	add_action('admin_enqueue_scripts','mycbgenie_load_js');
	
	add_action('wp_enqueue_scripts','mycbgenie_wp_load_js');


  // Add hook for Clickbank Products Import Ajax action
	add_action('wp_ajax_mycbgenie_ajax_cb_import_action',    						'mycbgenie_ajax_cb_import_process_function' );
	add_action('wp_ajax_mycbgenie_ajax_manual_sync_action',    						'mycbgenie_ajax_cb_manual_sync_function' );
	add_action('wp_ajax_mycbgenie_ajax_manual_sync_delete_action',    				'mycbgenie_ajax_cb_manual_sync_delete_function' );	

	add_action('wp_ajax_mycbgenie_ajax_cb_import_images_action',    				'mycbgenie_ajax_cb_import_images_process_function' );
	add_action('wp_ajax_mycbgenie_ajax_pre_import_activities',    					'mycbgenie_ajax_pre_import_activities_function' );
	add_action('wp_ajax_mycbgenie_ajax_cb_import_count_action',     				'mycbgenie_cb_import_products_count' );
	add_action('wp_ajax_mycbgenie_ajax_cb_import_check_already_exists_action',     	'mycbgenie_cb_import_products_check_already_exists' );
	add_action('wp_ajax_mycbgenie_ajax_cb_import_resume_delete_action',     		'mycbgenie_ajax_cb_import_resume_delete_function');
	
	add_action('wp_ajax_mycbgenie_manual_sync_fetch_json_files_action',    			'mycbgenie_manual_sync_fetch_json_files' );
	add_action('wp_ajax_mycbgenie_manual_sync_process_action',    					'mycbgenie_manual_sync_process_action_function' );
	add_action('wp_ajax_mycbgenie_single_sync_process_action',    					'mycbgenie_single_sync_process_action_function' );
	add_action('wp_ajax_mycbgenie_after_import_update_term_count_action',    		'mycbgenie_after_import_update_term_count_action_function' );


	add_action('wp_ajax_mycbgenie_ajax_exclude_category',    'mycbgenie_ajax_exclude_category_function' );
	add_action('wp_ajax_mycbgenie_ajax_gen_settings_action', 'mycbgenie_ajax_gen_settings_function' );
	//add_action('wp_ajax_mycbgenie_media_upload_action', 	 'mycbgenie_ajax_media_upload_function' );
	

	//add_filter('woocommerce_product_description_heading', '__return_null');
	add_filter('woocommerce_product_description_heading', 'mycbgenie_change_product_tab_title_on_review_yes_no');
	add_filter( 'woocommerce_product_additional_information_heading', function ( $heading ){return '';} );
	add_filter( 'the_content', 'mycbgenie_customizing_woocommerce_description' );
	add_filter( 'woocommerce_product_tabs', 'mycbgenie_change_product_tab_main_title', 98);
	
	
	if (	 (get_option('mycbgenie_premium_store')) || (get_option('cbproads_premium_store'))		) {
		 add_action('woocommerce_before_shop_loop','mycbgenie_special_search_header', 10 );
		 add_action('woocommerce_after_main_content','mycbgenie_special_search_content', 10 );
		 add_filter( 'woocommerce_get_breadcrumb', 'mycbgenie_remove_shop_crumb', 20, 2 );
		 add_filter( 'woocommerce_page_title', 'mycbgenie_change_woocommerce_category_page_title', 10, 1 );
	}



//remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 5 );

  // Add settings link on plugin page
  //use this plugin_basename( __FILE__ );
//  	add_filter('plugin_action_links_mycbgenie-clickbank-storefront/mycbgenie-clickbank-storefront.php',    'mycbgenie_settings_link');

  	$prefix = is_network_admin() ? 'network_admin_' : '';
	add_filter("{$prefix}plugin_action_links_mycbgenie-clickbank-storefront/mycbgenie-clickbank-storefront.php",    'mycbgenie_settings_link');
	
	//setting products per page display option
	//add_filter( 'loop_shop_per_page', create_function( '$cols', 'return '. get_option('mycbgenie_products_per_page').';' )   );
	//add_filter( 'loop_shop_per_page', function('$cols') use (get_option('mycbgenie_products_per_page')) { return get_option('mycbgenie_products_per_page');  } );
	add_filter( 'loop_shop_per_page', function($col) { return get_option('mycbgenie_products_per_page');  } );

	//if (get_option('mycbgenie_show_price')=="No" ) {
	
	add_action( 'woocommerce_before_shop_loop_item', 'mycbgenie_remove_price_and_description_from_loop' );
	add_action( 'woocommerce_before_single_product', 'mycbgenie_remove_price_from_single' );
	//}

	if (get_option('mycbgenie_sf_show_thumbnails')=="Yes" ) {

		if (get_option('mycbgenie_sf_thumbnail_location')=="breadcrumb" ) {
		
			add_action('woocommerce_archive_description','mycbgenie_sf_category_thumbnails', 10 );
			
		}elseif (get_option('mycbgenie_sf_thumbnail_location')=="shop_loop" ) {
		
			add_action('woocommerce_before_shop_loop','mycbgenie_sf_category_thumbnails', 10 );
			
		}else{
		
			add_action('woocommerce_before_main_content','mycbgenie_sf_category_thumbnails', 10 );
		}
		

	}
	



    


	
	//	add_filter('get_the_excerpt', 'mycbgenie_wp_trim_all_excerpt');

	
	
	register_activation_hook	(__FILE__, 'mycbgenie_activate');
	register_deactivation_hook	(__FILE__, 'mycbgenie_de_activate');
	register_uninstall_hook		(__FILE__, 'mycbgenie_uninstall');

?>