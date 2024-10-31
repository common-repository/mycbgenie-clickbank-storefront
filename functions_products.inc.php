<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once(ABSPATH .'wp-includes/pluggable.php'); 



// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
 

 
				/**
				 * Create a new table class that will extend the WP_List_Table
				 */
				class mycbgenie_products_Wp_List_Table extends WP_List_Table
				{
				
				protected  $sql_for_count;
				
				
				/**
					 * Prepare the items for the table to process
					 *
					 * @return Void
					 */

					 

					 
					public function prepare_items()
					{
					



						$currentPage="";
						$search_str ="";
						
						$columns 	= $this->get_columns();	
						$hidden 	= $this->get_hidden_columns();
						$sortable 	= $this->get_sortable_columns();
						
						$this->_column_headers = array(
							$columns,
							$hidden,
							$sortable
						);

						/** Process bulk action */
					    $this->process_bulk_action();
						
						//getting screen option values from top area
						$perPage = get_user_meta(get_current_user_id(), 'mycbgenie_screen_option_products_per_page', true);
						
						
						//if ( $perPage == " " ) { perPage=10; }
						if ( empty ( $perPage) || $perPage < 1 ) {
							$perPage=10;
						}
						
						$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] -1) * $perPage) : 0;
								
										
						$this->items = $this->table_data( $perPage, $currentPage, $paged ,$search_str  );
								
						$currentPage = $this->get_pagenum();
				 		$totalItems = $this->record_count( $search_str  ); 
				
						$this->set_pagination_args( array(
							'total_items' => $totalItems,
							'per_page'    => $perPage
							
						) );
	

					}
				 
					/**
					 * Override the parent columns method. Defines the columns to use in your listing table
					 *
					 * @return Array
					 */
					public function get_columns()
					{
						$columns = array(
						//	'cb'      		=> '<input type="checkbox" />',
							'image'			=>  'Thumnbnail',
							'id'          	=>  'Name',
							'mycbgenie_id'  =>  'MyCBGenie ID',
							'featured'		=>	'Featured',
							'enabled'		=>	'Visible',
							'title_a'      	=>  'Title',
							'description'	=>  'Description',
							'price'        	=>  'Price',
							'post_status'		=>	'Status',
							//'category'    	=>  'Category',
							'rank'		    =>  'Rank',
							'gravity'	    =>  'Gravity',
							'last_sync'	  	=>  'Last Sync',							
							'custom_edit'	=>	'Custom <br>Edited'
							//'tags'      	=> 'Tags'
						);
				 
						return $columns;
					}
				 
				 
				 	/*	public function search_box( $text, $input_id )
					{
						return ;
					}
					*/	
					
					/**
					 * Define which columns are hidden
					 *
					 * @return Array
					 */
					public function get_hidden_columns()
					{
						return array('mycbgenie_id','title_a','description'
						);
					}
				 
					/**
					 * Define the sortable columns
					 *
					 * @return Array
					 */
					public function get_sortable_columns()
					{
						//return array('title' => array('title', false));
						return array('price' => array('price', false), 
									 //'title_a' => array('title_a', false) ,
									 'featured'		=> array('featured',false),
									 'enabled'		=> array('enabled', false),
									 'last_sync'		=> array('last_sync', false),
									 //'custom_edit'		=> array('custom_edit', false),

									 'rank'		=>	array('rank',false),
									 'gravity'  =>  array('gravity',false)
									 );
					}
				 
					/**
					 * Get the table data
					 *
					 * @return Array
					 
					 
					 */

					
					private function table_data($per_page = 10, $page_number = 1, $paged, $search_str)
					{

						$orderby="last_sync";
						$order="";
						
					  	if ( ! empty( $_REQUEST['orderby'] ) ) 
						{
							$orderby = esc_sql( $_REQUEST['orderby'] );
							$order 	 = esc_sql( $_REQUEST['order']	 )  ;
						  }

						 if ($orderby== "price") $orderby		=	"_price";
						 if ($orderby== "rank") $orderby		=	"_mycbgenie_rank";
						 if ($orderby== "gravity") $orderby		=	"_mycbgenie_gravity";
						 if ($orderby== "enabled") $orderby		=	"_visibility";
						 if ($orderby== "featured") $orderby	=	"_featured";
						 if ($orderby== "last_sync") $orderby	=	"_mycbgenie_last_sync";
	 					 if ($orderby== "custom_edit") $orderby	=	"_mycbgenie_custom_edited";


					
						if ( $orderby == "_price" ||  $orderby == "_mycbgenie_rank" || $orderby == "_mycbgenie_gravity" ) {
							$num_orderby="meta_value_num";
							}
						else{	
							$num_orderby=$orderby;
						}


						$args = array(
						
							 'meta_query' => array (

						  					array (
												'key' => '_mycbgenie_managed_by',
												'value' => 'mycbgenie',
												 'compare' => '='
											 )),
							 'posts_per_page' => $per_page,
							 'offset'		=>  $paged,
							 'post_type' 	=> 'product',
							 'meta_key' 	=> $orderby,
							 'orderby' 	=> $num_orderby,
							 'order'		=> $order,
						  );	
						  
						  

						$args=mycbgenie_product_data_arguments($args);
		

						$new_query = new WP_Query( $args );
						
						//echo $new_query->request;
						$this->sql_for_count	=	$new_query->request;
	
	
						while ( $new_query->have_posts() ) : $new_query->the_post();
						 	
							
							$get_id=get_the_ID();


							/*
							//////////////////////////////Getting category//////////////////////////////////
							$terms = get_terms( 'product_cat' );

							foreach ( $terms as $term ) {
									// The $term is an object, so we don't need to specify the $taxonomy.
									$term_link = get_term_link( $term );
								   
									// If there was an error, continue to the next term.
									if ( is_wp_error( $term_link ) ) {
										continue;
										$mycbgenie_cats='';
									}
									else {
										$tax_name = 'product_cat';
										$mycbgenie_cats = '';
										$terms = get_the_terms( $get_id, $tax_name );
									
										if ( !empty( $terms ) ) {
											$out = array();
											foreach ( $terms as $term )
												$out[] = '<a href="' .get_term_link($term->slug, $tax_name) .'">'.$term->name.'</a>';
												$mycbgenie_cats = join( ', ', $out );
										}
									}
							}
							//////////////////////////////End of getting category//////////////////////////////////
							*/
							
							
							
							/////////////////////////////checking for custom edited/////////////////////////////////
							
							if (in_array(   get_post_meta($get_id,"_mycbgenie_id",true), 
											get_option('mycbgenie_custom_edited_products'),true
								))
							{
								$custom_edit="Yes";
								}
							else
								{
								$custom_edit="No";
								}	
							
							/////////////////////////////end if checking for custom edited/////////////////////////////////
							

							
							 $data[] = array(
									'id'          => $get_id,
									'image'		  => '',
									'mycbgenie_id'  => get_post_meta($get_id,"_mycbgenie_id",true),
									'featured'		=> get_post_meta($get_id, "_featured", true),
									'enabled'		=>	get_post_meta($get_id, "_visibility", true),
									'custom_edit'	=>	$custom_edit,
									'title_a'       => get_the_title(),
									'description' => get_the_excerpt(),
									'post_status'	=> get_post_status($get_id) ,
									'price'    	  => number_format((float)get_post_meta($get_id, "_price", true), 2, '.', ''),
									//'category'    => $mycbgenie_cats,
									'rank'		  => get_post_meta($get_id, "_mycbgenie_rank", true),
									'gravity'	  => get_post_meta($get_id, "_mycbgenie_gravity", true),
									'last_sync'	  => get_post_meta($get_id, "_mycbgenie_last_sync", true)

									);
		
						endwhile;
						
						
						return $data;
					
					}
				 
				 


				 
				 public  function record_count( $search_str ) {
				 
					 global $wpdb;
					 
					 $sql=$this->sql_for_count;
					 $sql_after_from=substr($sql,strpos($sql,"FROM"));
					 
					 $sql_remove_orderby=substr($sql_after_from,0,strpos($sql_after_from,"GROUP BY"));
	   
					 $sql="SELECT count(*)  ". $sql_remove_orderby ;
					 $result = $wpdb->get_var( $sql );
					 			//echo 	$sql_after_from;

					 return $result;
				
				}
				
				
				
				 
					/**
					 * Define what data to show on each column of the table
					 *
					 * @param  Array $item        Data
					 * @param  String $column_name - Current column name
					 *
					 * @return Mixed
					 */
					public function column_default( $item, $column_name )
					{
						switch( $column_name ) {
							case 'cb':	
							case 'id':
							case 'image' :
							case 'featured' :
							case 'enabled' :
							case 'custom_edit' :	
							case 'mycbgenie_id' :
							case 'title_a':
							case 'description':
							case 'price':
							case 'post_status'	:
						//	case 'category':
							case 'rank' :
							case 'gravity' :
							case 'last_sync' :
							
							
								return $item[ $column_name ];
				 
							default:
								return print_r( $item, true ) ;
						}
					}
				 
				
				
				/**
				 * Render the bulk edit checkbox
				 *
				 * @param array $item
				 *
				 * @return string
				 */
				function column_cb( $item ) {
				  return sprintf(
					'<input type="checkbox" name="bulk-list[]" value="%s" />', $item['id'].','.$item['mycbgenie_id']
				  );
				}


				/**
				 * Returns an associative array containing the bulk action
				 *
				 * @return array
				 */
				public function get_bulk_actions() {
				  $actions = array(
					'bulk-enable' => 'Visible',
					'bulk-disable' => 'Hidden',
					'bulk-featured' => 'Featured',
					'bulk-reverse-featured' => 'Not Featured',
					
				  );
				
				  return $actions;
				}
				
				
				
				//////////////////////////////filitering for categories/////////////////////////
				public function extra_tablenav( $which ){
				
				    // global $mycbgenie_filter_cat_id;
   
					 if ( 'top' != $which )
						  return;
   					?>
				
					<div class="alignleft actions">
					<form  method="GET" action=""> 
					<?php
					
					foreach ($_GET as $key => $value) { 
					// don't include few query strings
					if( 's' !== $key 
							&&  'mycbgenie_filter_cat_id' !== $key 
							&&  'filter_category_action' !== $key 
							&&  'filter_custom_edit_action' !== $key 
							&&  'product_character' !== $key ) {
							
						echo("<input type='hidden' name='$key' value='$value' />");
						echo("<input type='hidden' name='paged' value='1' />"); }
					}
					
					//get an array of imported terms (main & sub) categories /////
					$include_main_array= get_option('mycbgenie_imported_main_terms') ;
					
					$tmp_push=array();
					foreach ( $include_main_array as $include_main_arr) {
					//echo $include_main_arr['slug'].'<br>';
					array_push($tmp_push, $include_main_arr['slug']);
					}
					$include_main_array=$tmp_push;
					
					
					
					//var_dump( $include_main_array);
					$include_sub_array = get_option('mycbgenie_imported_sub_terms') ;
					$tmp_push=array();
					foreach ( $include_sub_array as $include_sub_arr) {
					//echo $include_main_arr['slug'].'<br>';
					array_push($tmp_push, $include_sub_arr['slug']);
					}
					$include_sub_array=$tmp_push;
					//var_dump($include_main_array);
					
					$include_array= array_merge($include_main_array, $include_sub_array);

					$include_array_final=array();
					
					
					foreach ($include_array as $include_arr){
						$theCatId =  get_term_by( 'slug', $include_arr, 'product_cat' );
						
						array_push($include_array_final,$theCatId->term_id);
					}
					//$a[]='82717';
					//$a[]='82599';
					$mycbgenie_filter_cat_id_in="";
					if(isset($_REQUEST['mycbgenie_filter_cat_id'])){
						$mycbgenie_filter_cat_id_in=$_REQUEST['mycbgenie_filter_cat_id'];
					}
					$include_array_final=implode(",",$include_array_final);
					$dropdown_options = array(
						'selected' => $mycbgenie_filter_cat_id_in,
						'name' => 'mycbgenie_filter_cat_id',
					    'taxonomy' => 'product_cat',
					    'include' 	=>	$include_array_final, //we show only mycbgenie categories
						'show_option_all' => __( 'All categories' ),
						'hide_empty' => false,
					   'hierarchical' => 1,
					   'show_count' => 1,
					    'orderby' => 'name'
						
				   	);
					
				
   
              		echo '<label class="screen-reader-text" for="mycbgenie_filter_cat_id">' . __( 'Filter by category' ) . '</label>';
              	 	wp_dropdown_categories( $dropdown_options );
					submit_button( __( 'Filter' ), 'button', 'filter_category_action', false, 
								array( 'mycbgenie_filter_cat_id' => 'post-query-submit' ) );
               		?>
					</form>
         			</div>
					
					<div class="alignleft actions">
					<form  method="GET" action="">
					<?php
						foreach ($_GET as $key => $value) { 
						// don't include few query strings
						if( 's' !== $key 
							&&  'mycbgenie_filter_cat_id' !== $key 
							&&  'filter_category_action' !== $key 
							&&  'filter_custom_edit_action' !== $key ) {
						echo("<input type='hidden' name='$key' value='$value' />");
						echo("<input type='hidden' name='paged' value='1' />"); }
						}
						
						
						
					if(isset($_REQUEST['product_character'])){
						$selected=$_REQUEST['product_character'];
					}
					
						//$selected = $_REQUEST['product_character'];
					?>
					
					    <select name="product_character">
						<option value="" > - </option>
						<option value="Custom_Edited" <?php if ( $selected == "Custom_Edited" ) echo 'selected="selected"'; ?>
							 >Custom Edited</option>
						<option value="Hidden" <?php if ( $selected == "Hidden" ) echo 'selected="selected"'; ?>>
							  Hidden</option>
						<option value="Featured" <?php if ( $selected == "Featured" ) echo 'selected="selected"'; ?>
							 >Featured</option>
					<!--	<option value="Active" <?php if ( $selected == "Active" ) echo 'selected="selected"'; ?>
							 >Active in Clickbank Marketplace</option> 
						<option value="Inactive" <?php if ( $selected == "Inactive" ) echo 'selected="selected"'; ?>
							 >Inactive - Clickbank Removed</option>-->
						</select>
						
					 <?php 
					 
					 echo '<label class="screen-reader-text" for="mycbgenie_filter_custom_edit_id">' . __( 'Filter by custom edit' ) . '</label>';
					 
					 submit_button( __( 'Filter' ), 'button', 'filter_custom_edit_action', false, array( 'product_character' => 'post-query-submit' ) );   ?>
					 </form>
         			</div>
					<?php		     
				}
			
			
			
			
	
			
			
			
				///////////////DISPLAY NAV//////////////////////
				protected function display_tablenav( $which ) {
				
				?>
				<form action="" method="get">
				<?php 
				$this->search_box( __( 'Search' ), 'example' ); 
				foreach ($_GET as $key => $value) { 
					// don't include the search query & category filters
					
				if( 's' !== $key 
							&&  'mycbgenie_filter_cat_id' !== $key 
							&&  'filter_category_action' !== $key 
							&&  'filter_custom_edit_action' !== $key 
							&&  'product_character' !== $key ) {							
											
						echo("<input type='hidden' name='$key' value='$value' />");
						echo("<input type='hidden' name='paged' value='1' />"); 
						}
				}
				
				?>
				</form> 
				<?php
			
			
					if ( 'top' == $which )
							wp_nonce_field( 'bulk-' . $this->_args['plural'] );
				?>
					<div class="tablenav <?php echo esc_attr( $which ); ?>">
				 
						
						
							<!--
							<div class="alignleft actions bulkactions">
							<?php //$this->bulk_actions( $which ); ?>
						
							</div> -->
							
				<?php
						$this->extra_tablenav( $which );
						$this->pagination( $which );
				?>
				 
						<br class="clear" />
					</div>
				<?php
				}
							
						
				
			
			
			
			
			
			
			//////////////////////BULK ACTIONS PROCESSINg///////////////////////////////////////////

				public function process_bulk_action() {

				  // If the delete bulk action for enable is triggered
				  if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-enable' )
					   || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-enable' )
				  ) {
				  
				 		$product_ids = esc_sql( $_REQUEST['bulk-list'] );
						if (is_array($product_ids)){
						foreach ( $product_ids as $id ) {
							mycbgenie_enable_product($id);
						} }//for each
				
					}//bulk action for enabled
					
					
					// If the delete bulk action for featured is triggered
				  if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-featured' )
					   || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-featured' )
				  ) {
						$product_ids = esc_sql( $_REQUEST['bulk-list'] );
						if (is_array($product_ids)){
							foreach ( $product_ids as $id ) {
							mycbgenie_featured_product($id);
							} 
						} //for each

					}//bulk action for featured
					
					
					
					
					// If the delete bulk action for disable is triggered
				  if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-reverse-featured' )
					   || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-reverse-featured' )
				  ) {
						$product_ids = esc_sql( $_REQUEST['bulk-list'] );
						if (is_array($product_ids)){
							foreach ( $product_ids as $id ) {
							mycbgenie_reverse_featured_product($id);
						} } //for each
						    
				 }//bulk action for reverse featured
					
					
					
					
					// If the delete bulk action for disable is triggered
				  if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-disable' )
					   || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-disable' )
				  ) {
						$product_ids = esc_sql( $_REQUEST['bulk-list'] );
						if (is_array($product_ids)){
						foreach ( $product_ids as $id ) {
							mycbgenie_disable_product($id);
						}} //for each
						    
							

					}//bulk action for enabled

				
				}
				
				//////////end of BULK ACTION/////////////////////////////////////////
				
				
				


				
				function column_post_status($item){
				
					if ($item['post_status']=='private' ) {	
		
						$delete_nonce = wp_create_nonce( 'sp_delete_product' );
		
						$status=sprintf('<div align=center>
						 Removed from Clickbank<br><a href="?page=%s&action=%s&id=%s&mycbgenie_id=%s&_deletenonce=%s">
						<img alt="Inactive" title="Inactive - Clickbank removed. Click to delete permanently" 
						width=25 height=25 src="'.plugins_url( 'images/removed.png', __FILE__ ).' "></a> </div>',
						esc_attr($_REQUEST['page']),'delete_pr',$item['id'], $item['mycbgenie_id'],
						$delete_nonce  );
							
					}		
						
					else 						
						{
						$status=sprintf('<div align=center><img  alt="Active" title="Active" 
						width=18 height=18 src="'.plugins_url( 'images/clean.png', __FILE__ ).' "></div>'); 
					}
	
							return sprintf('%1$s %2$s', $status, $this->row_actions($actions) );
				}	
				
				

				function column_image($item){
				
					$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($item['id']), 'thumbnail' );
					$url = $thumb['0'];
					
					$image='<img style="margin-top:5px;" width=50 height=50 src="'.$url.'">'	;
					
					if ($item['post_status']<>'private' ) {	
							
							$actions = array(
							'edit'      => 	sprintf('<a href="?page=%s&action_edit=%s&id=%s&mycbgenie_id=%s">
										'.  'Upload' .'</a>',
											esc_attr($_REQUEST['page']),'edit', $item['id'], $item['mycbgenie_id']));
											
							//$image='';				
	
								
					}	
					
					else {
					
								$actions = array(
							'edit'      => 	sprintf('<a href="?page=%s&action_edit=%s&id=%s&mycbgenie_id=%s"></a>',
											esc_attr($_REQUEST['page']),'edit', $item['id'], $item['mycbgenie_id']));	
					}	
					return sprintf('%1$s %2$s', $image, $this->row_actions($actions) );
				}
				
				
				
				function column_price($item){
				
					if ($item['post_status']<>'private' ) {
						$price	= get_woocommerce_currency_symbol().$item['price'];
					}
					return sprintf('%1$s %2$s', $price, $this->row_actions($actions) );
				}
				
		
		

				function column_custom_edit($item){
					if ($item['post_status']<>'private' ) {	
						if ($item['custom_edit']=="Yes") {
					
						 $custom_edit=sprintf('<img  width=18 height=18 src="'.plugins_url
						( 'images/clean.png', __FILE__ ).' ">'); }
						else
						{$custom_edit	= '-'; }
					}		
					return sprintf('%1$s %2$s', $custom_edit, $this->row_actions($actions) );
				}
				
				
				
				
				function column_enabled($item){
				
				$search_req="";	
				$page_req="";	
		
				if ($item['post_status']<>'private' ) {	
				$enabled= $item['enabled'];
				
				if(isset($_REQUEST['search'])){
							$search_req=$_REQUEST['search'];
				}
				if(isset($_REQUEST['page'])){
							$page_req=$_REQUEST['page'];
				}		
						
					if ($item['enabled']=="visible") {
					
						$hide_nonce = wp_create_nonce( 'sp_hide_product' );
						
						$enabled= '<div align=center>'.
						 sprintf('<a title="Click to disable this product"
						  href="?page=%s&action=%s&id=%s&mycbgenie_id=%s&search=%s&_hidenonce=%s">
						 <img  width=18 height=18 src="'.plugins_url
						( 'images/visible_green.gif', __FILE__ ).' "></a>', 
						esc_attr($page_req),'disable', $item['id'], $item['mycbgenie_id'], esc_attr($search_req),
						$hide_nonce).'</div>';
					}
					else{
						$visible_nonce = wp_create_nonce( 'sp_visible_product' );
						
						$enabled= '<div align=center>'.
						 sprintf('<a title="Click to enable this product" 
						 href="?page=%s&action=%s&id=%s&mycbgenie_id=%s&search=%s&_visiblenonce=%s">
						 <img  width=15 height=15 src="'.plugins_url
						( 'images/visible_red.png', __FILE__ ).' "></a>', 
						esc_attr($page_req) ,'enable',$item['id'], $item['mycbgenie_id'],  esc_attr($search_req),
						$visible_nonce).'</div>';
						
					}
					
				}
						
					return sprintf('%1$s %2$s', $enabled, $this->row_actions($actions) );
				}
				

				function column_featured($item){
				$page_req="";
				$search_req="";
				
				if ($item['post_status']<>'private' ) {
				
						if(isset($_REQUEST['page'])){
							$page_req=$_REQUEST['page'];
						}
						
						if(isset($_REQUEST['search'])){
							$search_req=$_REQUEST['search'];
						}
						
					if ($item['featured']=="no") {
					
						$feature_nonce = wp_create_nonce( 'sp_feature_product' );
						
						
				
							$featured= '<div align=center>'.
							sprintf('<a title="Click to feature this product"
									href="?page=%s&action=%s&id=%s&mycbgenie_id=%s&_featurenonce=%s&search=%s">
									<img  width=18 height=18 src="'.plugins_url
									('images/fav_white.png', __FILE__ ).' "></a>',esc_attr($page_req) ,'feature',
									$item['id'], $item['mycbgenie_id'], $feature_nonce, esc_attr($search_req) ).'</div>';
					}

					else{
						
						$unfeature_nonce = wp_create_nonce( 'sp_unfeature_product' );
						
							 $featured= '<div align=center>'.
							 sprintf('<a title="Click to unfeature this product"
						 			href="?page=%s&action=%s&id=%s&mycbgenie_id=%s&_unfeaturenonce=%s&search=%s">
						 			<img  width=18 height=18 src="'.plugins_url
									('images/fav_red.png', __FILE__ ).' "></a>',esc_attr($page_req),'unfeature',
									$item['id'], $item['mycbgenie_id'], $unfeature_nonce, esc_attr($search_req) ).'</div>';
					}
				}	
					return sprintf('%1$s %2$s', $featured, $this->row_actions($actions) );
				}
				




				function column_id($item){
				//echo 'status'.$item['post_status'];
				if ($item['post_status']<>'private' ) {
					$id='<a class="row-title" target=_blank href="' . get_permalink($item['id']) . '">' . 
						$item['title_a'] . '</a><br>ID : '.$item['id'];
					//$refurl=$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
					
				  	$actions = array(
							'edit'      => 	sprintf('<a href="?page=%s&action_edit=%s&id=%s&mycbgenie_id=%s">Edit</a>',
											esc_attr($_REQUEST['page']),'edit', $item['id'], $item['mycbgenie_id']),
							'sync'      => 	sprintf('<a href="?page=%s&action_edit=%s&id=%s&mycbgenie_id=%s">Sync</a>',
											esc_attr($_REQUEST['page']),'sync',$item['id'], $item['mycbgenie_id']),
							);
				}
				else{
				
				$id='<a target=_blank href="' . get_permalink($item['id']) . '">' . 
						$item['title_a'] . '</a><br>ID : '.$item['id'];
						
						$actions = array(
							'edit'      => 	sprintf('<a href="">=>Inactive</a>',
											esc_attr($_REQUEST['page']),'edit', $item['id'], $item['mycbgenie_id']));
				
				}// post sttaus end			
				  	return sprintf('%1$s %2$s', $id, $this->row_actions($actions) );
			}
			
			

}//class
					

		
		
	//////////////////////////////////////////////////END OF MAIN CLASS //////////////////////////////////////////////////////////////////
	



			
				//function to delete the clickbank removed (inactive) products
		
				if( isset( $_GET['action'] ) && ($_GET['action'])== "delete_pr" ) {
				
				$nonce_hide = esc_attr( $_REQUEST['_deletenonce'] );

				if ( ! wp_verify_nonce( $nonce_hide, 'sp_delete_product' ) ) {
					die( 'Go get a life script kiddies.' );
				}
				else // else nonce
				{
						//wp_delete_post($_GET['id']);
						mycbgenie_delete_all_entries($_GET['id'],'temp');
		
						wp_redirect ($_SERVER['HTTP_REFERER']);
						exit;
						
						
				}
				}//end of function
			
				// function to disable the product
				if( isset( $_GET['action'] ) && ($_GET['action'])== "disable" ) {
				
				$nonce_hide = esc_attr( $_REQUEST['_hidenonce'] );

				if ( ! wp_verify_nonce( $nonce_hide, 'sp_hide_product' ) ) {
					die( 'Go get a life script kiddies.' );
				}
				else // else nonce
				{
				
					$disabled_products_array=array();
					$mycbgenie_id = $_GET['mycbgenie_id'];
					$post_id = $_GET['id'];
					update_post_meta( $post_id, '_visibility', 'hidden' );
					
					//checking if exisits in option, if not add to it.
					$disabled_products_array=get_option('mycbgenie_disabled_products');
					if (in_array($mycbgenie_id, $disabled_products_array,true)){}
					else {
					

						array_push($disabled_products_array,$mycbgenie_id);
					
						//$disabled_product_array.push($mycbgenie_id);
						update_option('mycbgenie_disabled_products', $disabled_products_array);
					}
					
						//wp_redirect( esc_url( add_query_arg() ) );
						wp_redirect ($_SERVER['HTTP_REFERER']);
						exit;
				}//nonce
				}
				
				
				
						
	
			



				
				// function to enable the product
				if( isset( $_GET['action'] ) && ($_GET['action'])== "enable" ) {
				
				$nonce_visible = esc_attr( $_REQUEST['_visiblenonce'] );

				if ( ! wp_verify_nonce( $nonce_visible, 'sp_visible_product' ) ) {
					die( 'Go get a life script kiddies...' );
				}
				else // else nonce
				{
				
					$disabled_products_array=array();
					$mycbgenie_id = $_GET['mycbgenie_id'];
					$post_id = $_GET['id'];
					update_post_meta( $post_id, '_visibility', 'visible' );
					//var_dump( get_option('mycbgenie_disabled_products'));
					$disabled_products_array = get_option('mycbgenie_disabled_products');
					


					if (in_array($mycbgenie_id, $disabled_products_array,true)){

						update_option('mycbgenie_disabled_products', mycbgenie_remove_array_item($disabled_products_array,$mycbgenie_id));
					}
						
					//wp_redirect( esc_url( add_query_arg() ) );
					wp_redirect ($_SERVER['HTTP_REFERER']);
					exit;
				}//nonce		
				}






				// function to feature the product
				if( isset( $_GET['action'] ) && ($_GET['action'])== "feature" ) {
				

				
				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_featurenonce'] );
	
				if ( ! wp_verify_nonce( $nonce, 'sp_feature_product' ) ) {
					die( 'Go get a life script kiddies' );
				}
				else // else nonce
				{
			
					$featured_products_array=array();
					$mycbgenie_id = $_GET['mycbgenie_id'];
					$post_id = $_GET['id'];
					update_post_meta( $post_id, '_featured', 'yes' );
					
					//checking if exisits in option, if not add to it.
					$featured_products_array=get_option('mycbgenie_featured_products');
					if (in_array($mycbgenie_id, $featured_products_array,true)){}
					else {
					

						array_push($featured_products_array,$mycbgenie_id);
					
						//$disabled_product_array.push($mycbgenie_id);
						update_option('mycbgenie_featured_products', $featured_products_array);
						//echo esc_url( add_query_arg() );
						
						wp_redirect ($_SERVER['HTTP_REFERER']);
						//wp_redirect( esc_url( add_query_arg() ) );
						exit;
					}
				}//nonce
				}
				
				
				
				// function to disable the featuring this product
				if( isset( $_GET['action'] ) && ($_GET['action'])== "unfeature" ) {

				$nonce = esc_attr( $_REQUEST['_unfeaturenonce'] );
	
				if ( ! wp_verify_nonce( $nonce, 'sp_unfeature_product' ) ) {
					die( 'Go get a life script kiddies' );
				}
				else // else nonce
				{
					$featured_products_array=array();
					$mycbgenie_id = $_GET['mycbgenie_id'];
					$post_id = $_GET['id'];
					update_post_meta( $post_id, '_featured', 'no' );
					//var_dump( get_option('mycbgenie_disabled_products'));
					$featured_products_array = get_option('mycbgenie_featured_products');


					if (in_array($mycbgenie_id, $featured_products_array, true)){

					update_option('mycbgenie_featured_products', mycbgenie_remove_array_item($featured_products_array,$mycbgenie_id));
					}
						//echo esc_url( add_query_arg() );
						wp_redirect ($_SERVER['HTTP_REFERER']);
						//wp_redirect( esc_url( add_query_arg() ) );
						exit;
						
				}//nonce

				}
				






function mycbgenie_product_data_arguments($args){
			
					if (isset($_REQUEST['mycbgenie_filter_cat_id']) && intval($_REQUEST['mycbgenie_filter_cat_id'])<>0 ){
					
						  $args[tax_query]= array(
       							 array(
           						 'taxonomy' => 'product_cat',
          						 'terms'    => $_REQUEST['mycbgenie_filter_cat_id'],
        							),
    							);
						  			
							}
							
							
							
	
							
					
					elseif (isset($_REQUEST['filter_custom_edit_action']) && isset($_REQUEST['product_character']) ){
					
					
					
								array_shift($args);
								if ($_REQUEST['product_character'] === "Inactive"){
									$args['post_status']	=	'private';
									$args[meta_query]= 	array (
												array (
													'key' => '_mycbgenie_managed_by',
													'value' => 'mycbgenie',
													 'compare' => '='
											 	));
								}
								elseif ($_REQUEST['product_character'] === "Active"){
								
									$args['post_status']	=	'publish';
									$args[meta_query]= 	array (
												array (
													'key' => '_mycbgenie_managed_by',
													'value' => 'mycbgenie',
													 'compare' => '='
											 	));

								}
								elseif ($_REQUEST['product_character'] === "Custom_Edited"){
							
									$args[meta_query]=  array (
	
					  					 array (
												'key' => '_mycbgenie_custom_edited',
												'value' => 'Yes',
												 'compare' => '='
											 ),
											 
						  					array (
												'key' => '_mycbgenie_managed_by',
												'value' => 'mycbgenie',
												 'compare' => '='
											 ));
								}			 
								elseif ( $_REQUEST['product_character'] === "Featured" ) 	{
								
									$args[meta_query]=  array (
	
					  					 array (
												'key' => '_featured',
												'value' => 'Yes',
												 'compare' => '='
											 ),
											 
						  					array (
												'key' => '_mycbgenie_managed_by',
												'value' => 'mycbgenie',
												 'compare' => '='
											 ));
								}
								elseif ( $_REQUEST['product_character'] === "Hidden" ) {
								
										$args[meta_query]=  array (
	
					  					 array (
												'key' => '_visibility',
												'value' => 'hidden',
												 'compare' => '='
											 ),
											 
						  					array (
												'key' => '_mycbgenie_managed_by',
												'value' => 'mycbgenie',
												 'compare' => '='
											 ));	
								} 

					}
					
					elseif (isset($_REQUEST['s']) ){

						$args[s]=($_REQUEST['s']);
	
					}		
					
				//var_dump($args);
				return $args;
				
	
	
	

			
}







//bulk action singlecall
function mycbgenie_enable_product($product_id)
	{
		$post_id=explode(",",$product_id);
		$mycbgenie_id=$post_id[1];
		
		//echo $post_id[0] . "<br>";
		update_post_meta( $post_id[0], '_visibility', 'visible' );
		
		//var_dump( get_option('mycbgenie_disabled_products'));
		$disabled_products_array = get_option('mycbgenie_disabled_products');

		//if already exisis, remove from options.
		if (in_array($mycbgenie_id, $disabled_products_array, true)){
		 update_option('mycbgenie_disabled_products', mycbgenie_remove_array_item($disabled_products_array,$mycbgenie_id));
		}
		
	}
				
//bulk action singlecall			
function mycbgenie_disable_product($product_id)
{
	$post_id=explode(",",$product_id);
	$mycbgenie_id=$post_id[1];

	update_post_meta( $post_id[0], '_visibility', 'hidden' );

	//checking if exisits in option, if not add to it.
	$disabled_products_array = get_option('mycbgenie_disabled_products');
	if (in_array($mycbgenie_id, $disabled_products_array, true)){}
	else {

		array_push($disabled_products_array,$mycbgenie_id);
	
		//$disabled_product_array.push($mycbgenie_id); Adding to array.
		update_option('mycbgenie_disabled_products', $disabled_products_array);
	}
}



//bulk action singlecall	featured product		
function mycbgenie_featured_product($product_id)
{
	$post_id=explode(",",$product_id);
	$mycbgenie_id=$post_id[1];
	
	//update_post_meta( $post_id[0], '_featured', 'yes' );
	update_post_meta( $post_id[0], 'featured', 'product_visibility' );
	

	//checking if exisits in option, if not add to it.
	$featured_products_array = get_option('mycbgenie_featured_products');
	if (in_array($mycbgenie_id, $featured_products_array, true)){}
	else {
	

		array_push($featured_products_array,$mycbgenie_id);
	
		update_option('mycbgenie_featured_products', $featured_products_array);
	}
	
}




//bulk action singlecall for reverse featured			
function mycbgenie_reverse_featured_product($product_id)
{
	$post_id=explode(",",$product_id);
	$mycbgenie_id=$post_id[1];
	
	//echo $post_id[0] . "<br>";
	//update_post_meta( $post_id[0], '_featured', 'no' );
	update_post_meta( $post_id[0], '', 'product_visibility' );
	


	//checking if exisits in option, if not add to it.
	$reverse_featured_products_array = get_option('mycbgenie_featured_products');
		//if already exisis, remove from options.
	if (in_array($mycbgenie_id, $reverse_featured_products_array,true)){
	 update_option('mycbgenie_featured_products', mycbgenie_remove_array_item($reverse_featured_products_array,$mycbgenie_id));
	}
	
}

	
//fucntion to remove elements from array
function mycbgenie_remove_array_item( $array, $item ) {
	$index = array_search($item, $array);
	if ( $index !== false ) {
		unset( $array[$index] );
	}
	return $array;
}
			
			
			
//Adding screen opitons

function mycbgenie_products_screen_add_options() {

  global $mycbgenie_List_Table_products;
  //global products_per_page;
  $option = 'per_page';
  $args = array(
         'label' => 'Products Per Page (max: 30)',
         'default' => 10,
         'option' => 'mycbgenie_screen_option_products_per_page'
         );
  add_screen_option( $option, $args );
  



  $mycbgenie_List_Table_products = new mycbgenie_products_Wp_List_Table(45);
}




//screen setting store values
function mycbgenie_set_screen_option($status, $option, $value) {
 
 	

	$value= ('mycbgenie_screen_option_products_per_page' == $option) && ( intval($value) <=30 ) ?  $value : 30;
    return  $value;
 
    return $status;
}






//ignore the product page admin notice and hide for ever
if ( isset($_GET['mycbgenie_nag_ignore3']) && '0' == $_GET['mycbgenie_nag_ignore3'] ) {
	
	add_user_meta(get_current_user_id(), 'mycbgenie_product_screen_dismiss_option', 'true', true);
	
	wp_redirect ($_SERVER['HTTP_REFERER']);
	exit;
						

}
	
	
	
//show the product page help admin notice again
if ( isset($_GET['mycbgenie_nag_show3']) && '0' == $_GET['mycbgenie_nag_show3'] ) {
	

	if ( ! delete_user_meta(get_current_user_id(),'mycbgenie_product_screen_dismiss_option')) {
 	 echo "Ooops! Error while deleting this information! for user id :".get_current_user_id();
	}
	
	wp_redirect ($_SERVER['HTTP_REFERER']);
	exit;

}

if ( isset($_POST['mycbgenie_product_edit_back'])  ){


	//redirect to where before
	$redirect_url=$_POST['ref_url'];
	//echo $redirect_url;
	//exit;

	wp_redirect(($redirect_url));
}



function mycbgenie_single_sync_process_action_function() {

   	if ( !isset($_POST['mycbgenie_single_sync_nonce']) || !wp_verify_nonce( $_POST['mycbgenie_single_sync_nonce'], "local_mycbgenie_single_sync_nonce")) {
      		exit("No naughty business please");
			
  	 } 
	
			//$keywords="ddd,sddd,ll,ff,yasar";
			//$custom_edit_id=69508;
			//$term=mycbgenie_update_terms_and_tags($custom_edit_id,$keywords,$maincat,$subcat);
			
			$custom_edit_id					=	$_POST['id'];
			$custom_edit_mycbgenie_id		=	$_POST['mycbgenie_id'];
					
			mycbgenie_single_sync_ajax($custom_edit_id,$custom_edit_mycbgenie_id);
			
									 
	die();
}


function mycbgenie_custom_edit_sync_remote_server ($mycbgenie_id,$title,
												$excerpt,$mdescr,$thumbnail,
												$product_tags,$price){


	$remote_url = 'http://mycbgenie.com/php/sync_wordpress_installs/custom_edit.php';
	$url=	get_site_url();
	
	
	$response = wp_remote_post( $remote_url, array(
		//'method' => 'POST',
		//'timeout' => 45,
		//'redirection' => 5,
		//'httpversion' => '1.0',
		//'blocking' => true,
		//'headers' => array(),
		'body' => array( 	'mycbgenie_id' 	=>  $mycbgenie_id, 
							'endata' 		=> 	md5($url) ,
							'title'			=>	$title,
							'descr'			=>	$excerpt,
							'm_descr'		=>	$mdescr,
							'image_url'		=>	$thumbnail,
							'tags'			=>	$product_tags,
							'price'			=>	$price,
							'url'			=>	$url,
							'account_id'	=>	get_option('mycbgenie_account_no')
							)
		//'cookies' => array()
		)
	);

	if ( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
	  	//echo "Something went wrong: $error_message";
	} else {
	   //echo 'Response:<pre>';
	   //print_r( $response );
 	// echo '</pre>';
	}

}


//function mycbgenie_single_sync_ajax1(){
//mycbgenie_single_sync_ajax('97792','106.194.135.216.156.177.158.179');
//}


function mycbgenie_single_sync_ajax($custom_edit_id,$custom_edit_mycbgenie_id){

					//update woocommerce image dimensions
					mycbgenie_update_woocommerce_image_dimensions();

					global $wpdb;
				
					$wpdb->query( 'START TRANSACTION;' );
					$wpdb->query( 'SET autocommit = 0;' );
				
		
				
					$url="https://cbproads.com/xmlfeed/woocommerce/zip/sync.asp?id=".$custom_edit_mycbgenie_id;
					$json = json_decode(mycbgenie_sf_file_get_contents_curl($url,0,null,null));
					
					
					if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
		   				
						echo json_encode( 
								array( 'status' => 'error', 
								'error_message'=>'Error in accessing details of this product from our REMOTE server. There is a chance of this product might be got deleted from Clickbank Marketplace! REMOTE Sever URL : '.$url));
						exit;
					}
		
	
					foreach ($json as $key=>$value) {
					
						
					
						$title="";
						$linkurl="";
						$mdescr="";
						$images="";
						$altimage="";
						$totalp="";
						$ids="";
						$affiliate="";
						$price="";
						$niche="";
						$category="";
						$rank="";
						$gravity="";
						$keywords="";
						
						
						foreach ($value as $key => $val) { 
				
							if ($key=='title') { $title= $val;				}
							if ($key=='linkurl') { $linkurl= $val;}
							if ($key=='mdescr') { $mdescr= $val;}
							if ($key=='images') { $images= $val;}
							if ($key=='altimage') { $altimage= $val;}
							if ($key=='totalp') { $totalp= $val;}
							if ($key=='ids') 	{ $mycbgenie_id= $val;}
							if ($key=='affiliate') { $affiliate= $val;}
							if ($key=='price') { $price= $val;}
							if ($key=='niche') { $niche= $val;}
							if ($key=='category') { $category= $val;}
							if ($key=='rank') { $rank= $val;}
							if ($key=='gravity') { $gravity= $val;}
							if ($key=='keywords') { $keywords= $val;}
							if ($key=='maincat') { $maincat= $val;}
							if ($key=='subcat') { $subcat= $val;}
							if ($key=='descr')  { $descr= $val;}
							if ($key=='rating')  { $rating= $val;}

						}
						
						$image_name =	$images;

						
						if ($images=="blank.gif")
							{	
								if ($altimage=='no')
								{
								$images="cbproads.com/cbbanners/blank.gif";
								}
								else
								{
								$images="cbproads.com/cbbanners_mycbgenie/".$altimage;	
								}
							}
						else
							{
								$images="cbproads.com/cbbanners_mycbgenie/".$images;	
							}
							
					}//for each		
					
	
						
					$permalink = sanitize_title($title);
					
					$mycbgenie_custom_edit_post = array(
      						'ID'    		 => ($custom_edit_id),
     						'post_title'   	 => $title,
							'post_excerpt'   => $descr,
      						'post_content'   => $mdescr,
							'post_name'		 =>	$permalink,
							'post_status'	 => "publish"
 		 			);

					
				
					// Update the post into the database
  					$post_id_tmp =wp_update_post( $mycbgenie_custom_edit_post  );
					
								  //setting rating
			 		 mycbgenie_update_rating($post_id_tmp,$rating);
					 
					  $screenshot_allowed ="yes";
					  $temp=  mycbgenie_fetch_product_images($images,$custom_edit_id, $image_name, $post_id_tmp, $altimage , $screenshot_allowed , "sync");

					//$temp=mycbgenie_update_thumbnail($images,$custom_edit_id);
					

								
					if ($post_id_tmp==0) {
					
						echo json_encode( 
								array( 'status' => 'error', 
								'error_message'=>'Error in updating while in SYNC :'));
								$wpdb->query( 'ROLLBACK;' );
						exit;
						
					
					}

					update_post_meta( $custom_edit_id, '_mycbgenie_image_url', $image_name );

					update_post_meta( $custom_edit_id, '_price', $price );
					update_post_meta( $custom_edit_id,'_mycbgenie_rank', $rank);
					update_post_meta( $custom_edit_id,'_mycbgenie_gravity', $gravity);

					update_post_meta( $custom_edit_id, '_mycbgenie_custom_edited', 'No');
					update_post_meta( $custom_edit_id,'_mycbgenie_last_sync', date('m/d/Y h:i:s a', time()));

					 wp_set_object_terms ($custom_edit_id, 'external', 'product_type');
			
									
					$term=mycbgenie_update_terms_and_tags($custom_edit_id,$keywords,$maincat,$subcat);
						
					if ($term=="error_tag") {

							echo json_encode( 
								array( 'status' => 'error', 
								'error_message'=>'Error in updating product tags while in SYNC :'));
								$wpdb->query( 'ROLLBACK;' );
						exit;
						}
					elseif ($term=="error_terms") {
					
							echo json_encode( 
								array( 'status' => 'error', 
								'error_message'=>'Error in updating product terms while in SYNC :'));
								$wpdb->query( 'ROLLBACK;' );
						exit;
					
					}
	
					//remove this product as custom edited in OPTIONS if already there
					$mycbgenie_custom_edit_products_array	=	array();
					$mycbgenie_custom_edit_products_array	=	get_option('mycbgenie_custom_edited_products');
	
					if ( in_array($custom_edit_mycbgenie_id, $mycbgenie_custom_edit_products_array, true) ) {
						$mycbgenie_custom_edit_products_array	=
									mycbgenie_remove_array_item(
									$mycbgenie_custom_edit_products_array,$custom_edit_mycbgenie_id);
									update_option	('mycbgenie_custom_edited_products', $mycbgenie_custom_edit_products_array);

					}// end if



				
					
					$wpdb->query( 
						$wpdb->prepare ("	
								DELETE FROM mycbgenie_custom_edited_products
								WHERE mycbgenie_id='%s'	
								"
							,
							$custom_edit_mycbgenie_id
							)
					);	
				
					if ($wpdb->last_error) {
										
							$wpdb->query( 'ROLLBACK;' );
							echo json_encode( 
								array( 'status' => 'error', 
								'error_message'=>' Error in deleting TABLE mycbgenie_custom_edited_products' . $wpdb->last_error	));
								
										return;
										
										}
					

					
							$wpdb->query( 'COMMIT;' );
							$wpdb->query( 'SET autocommit = 1;' );	
							
							//reverse woocommerce image dimensions
							mycbgenie_reverse_woocommerce_image_dimensions();

				echo json_encode( array(
				
							'title' 			=> $title, 
							'descr'			=> $descr,
							'mdescr'			=>  $mdescr,
							'price'		=> $price,
							'keywords'	=>	$keywords,
							'rank'		=>	$rank,
							'gravity'	=>	$gravity
							
							) ); 
								
}



	
//This is the main function that display list table
function mycbgenie_display_products()
{


	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}


echo '</pre><div class="wrap" style="margin-top: 35px;">'; 


	function get_post_excerpt_by_id( $post_id ) {
		global $post;
		$post = get_post( $post_id );
		setup_postdata( $post );
		$the_excerpt = get_the_excerpt();
		wp_reset_postdata();
		return $the_excerpt;
	}

	
	function get_post_content_by_id( $post_id ) {
		global $post;
		$post = get_post( $post_id );
		setup_postdata( $post );
		$the_content = get_the_content();
		wp_reset_postdata();
		return $the_content;
	}
	
	
if ( isset($_POST['mycbgenie_product_edit'])  ){

	global $wpdb;
		
	$nonce = esc_attr( $_POST['mycbgenie_product_edited_nonce'] );
	
	if ( ! wp_verify_nonce( $nonce, 'sp_mycbgenie_custom_edit' ) ) {
			die( 'Go get a life script kiddies' );
	}
				
	else
	{
		
		$custom_edit_id				=	 (esc_attr(sanitize_text_field(trim($_POST['woo_id']))));
		$custom_edit_mycbgenie_id	=	(esc_attr((trim($_POST['mycbgenie_id']))));
		$custom_edit_title			=	(esc_attr(wp_kses_post(sanitize_text_field(trim($_POST['mycbgenie_title'])))));
		$custom_edit_excerpt		=	(esc_attr(wp_kses_post(sanitize_text_field(trim($_POST['mycbgenie_excerpt'])))));
		$custom_edit_descr			=	 (esc_attr(wp_kses_post(sanitize_text_field(trim($_POST['mycbgenie_descr'])))));
		$custom_edit_product_tags	=	esc_attr(sanitize_text_field(trim($_POST['mycbgenie_keywords'])));
		$thumbid					=	$_POST['mycbgenie_attachment_id'];
		
		if (!is_numeric($_POST['mycbgenie_price'])) { die('Price must be numeric') ; }
		$custom_edit_price			=	(float)($_POST['mycbgenie_price']) ;	

			
		
		$permalink = sanitize_title($custom_edit_title);
		// Update post 
  		$mycbgenie_custom_edit_post = array(
      		'ID'             => $custom_edit_id,
     		'post_title'   	 => $custom_edit_title,
			'post_excerpt'   => $custom_edit_excerpt,
      		'post_content'   => $custom_edit_descr,
			'post_name'		 =>	$permalink
 		 );

		// Update the post into the database
  		$post_id_tmp =wp_update_post( $mycbgenie_custom_edit_post ,$wp_error );
		

		if ($wp_error) {
			wp_die('Error in updating!');
		}
		
		set_post_thumbnail($custom_edit_id, $thumbid);


  
  		$term_taxonomy_ids=wp_set_object_terms( $custom_edit_id, explode(",",$custom_edit_product_tags), 'product_tag',false);
		//$taxonomies = get_taxonomies( '', 'names' );   
		//  foreach ($taxonomies as $taxonomy ) {  
			// echo '<p>'. $taxonomy. '</p>';  
		  // } 
		  
		//echo	$custom_edit_product_tags.$custom_edit_id;
		//var_dump($term_taxonomy_ids);
		//exit;

		update_post_meta( $custom_edit_id, '_price', $custom_edit_price );
		
		update_post_meta( $custom_edit_id, '_mycbgenie_custom_edited', 'Yes');
		
		

		// SYNC with remote server of MYCBGENIE
		$thumbnail_tmp = wp_get_attachment_image_src( get_post_thumbnail_id($custom_edit_id), 'thumbnail' );
		


		mycbgenie_custom_edit_sync_remote_server($custom_edit_mycbgenie_id,$custom_edit_title,
												$custom_edit_excerpt,$custom_edit_descr,$thumbnail_tmp[0],
												$custom_edit_product_tags,$custom_edit_price);


		//acknowledge this product as custom edited in OPTIONS
		$mycbgenie_custom_edit_products_array=array();
		$mycbgenie_custom_edit_products_array=get_option('mycbgenie_custom_edited_products');
		
		if ( !in_array($custom_edit_mycbgenie_id, $mycbgenie_custom_edit_products_array, true) ) {
			array_push($mycbgenie_custom_edit_products_array,$custom_edit_mycbgenie_id);
			update_option('mycbgenie_custom_edited_products', $mycbgenie_custom_edit_products_array);
		}


     	/// data upation////////////////////////////////////////////////
		$table_name = "mycbgenie_custom_edited_products";
   		 $myrows = $wpdb->get_var( $wpdb->prepare
					("SELECT mycbgenie_id FROM $table_name WHERE mycbgenie_id=%s LIMIT 1", $custom_edit_mycbgenie_id));
		
		$data	=    array(
      	  	'mycbgenie_id'  => $custom_edit_mycbgenie_id,
			'title'    	 	=> $custom_edit_title,
			'excerpt'    	=> $custom_edit_excerpt,
			'descr'   		=> $custom_edit_descr,
			'price'			=> $custom_edit_price,
			'tags'			=>	$custom_edit_product_tags
   		 );
		 
		if ($thumbid	=='' || empty($thumbid)) {}
		else {
			$data['thumbnail_id']=$thumbid;
		}
		
	 
		 
		if(empty($myrows)){
        
       	 	$wpdb->insert( $table_name, $data);
		}
    	else {
        	$wpdb->update( $table_name, $data , array('mycbgenie_id' => $custom_edit_mycbgenie_id));
   		 }
     	/// end of data upation////////////////////////////////////////////////


		//redirect to where before
		$redirect_url=$_POST['ref_url'];
	
	?>
	<script>
	alert('Updated successfully');
	</script>
	<?php
		
	
	}//end of nonce checking

}

	
	
if ( isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="sync" ){

	$post_id	=	$_REQUEST['id'];
	?>
	<h2>SYNC</h2>
	<h5>Update with REMOTE server. 
	SYNCing a particular product will override any custom details that you have set for this product.
	</h5>
		
	<div id="single_sync_local_div"	style="border-radius:3px; background:#E8E8E8; float:left; width:40%; padding:20px; border:solid 1px silver;">
	
	<form action="" id="mycbgenie-ajax-single-product-sync-form"	method="post">
	
	<div align=right><input class="button-primary" id='submit-btn-single-sync-now' type=submit  value="SYNC now" />
	</div>
	
	<strong>Title	:</strong>	<p><i><?php echo get_the_title( $post_id ) ?>	</i></p>
		
	<strong>Short Desc	:</strong>	<p><i>	<?php echo trim(get_post_excerpt_by_id( $post_id )) ?></i></p>	
	
	<strong>Long Desc	:	</strong>	<p><i>		<?php echo trim(get_post_content_by_id( $post_id )) ?></i></p>	
		
	<strong>Price	:</strong>	$<?php echo get_post_meta($post_id, "_price",true); ?>	<br /><br />

		
	
	

		<input type="hidden"	id="mycbgenie_sync_id"	name="mycbgenie_sync_id" 
								value="<?php echo get_post_meta($post_id, "_mycbgenie_id",true);	?>"	/>
								
		<input type="hidden"	id="sync_id" name="sync_id" 		value="<?php echo $post_id	?>"	/>

		<input type="hidden" 	name="mycbgenie_product_sync"	value="yes"	/>
		<input type="hidden" 	name="ref_url" value="<?php echo $_SERVER['HTTP_REFERER'] ?>" />
		</form>

		
		
		<div style="width:100%; float:left; padding-top:20px;">
		<div align="left">
		<form action="" method=post>
		<input type="hidden" 	name="ref_url" value="<?php echo $_SERVER['HTTP_REFERER'] ?>" />
		<input type="hidden" 	name="mycbgenie_product_edit_back"	value="yes"	/>
	
		<input type="submit" class="button" value="Back" />
		</form>
		</div>
		</div>
	
	</div>
	
	
	
		<div id="single_sync_output_div"	style="display:none; float:right; width:48%; padding:0px; border:solid 0px grey;">
		<h4>SYNC Status : <font color=green>Success</font></h4>
		
		<strong>Title	:</strong>	<p><i><span style="color:#003300" id="single_sync_title_output"></span>	</i></p>
			
		<strong>Short Desc	:</strong>	<p><i>	<span style="color:#003300" id="single_sync_descr_output"></span></i></p>	
		
		<strong>Long Desc	:	</strong>	<p><i><span style="color:#003300" id="single_sync_mdescr_output"></span></i></p>	
			
		<strong>Price	:</strong>	$<span style="color:#003300" id="single_sync_price_output"></span><br /><br />
		
		<strong>Keywords	:</strong>	<p><i><span style="color:#003300" id="single_sync_keywords_output"></span></i></p>
		
		<strong>Rank	:</strong>	<span style="color:#003300" id="single_sync_rank_output"></span><br /><br />
	
		<strong>Gravity	:</strong>	<span style="color:#003300" id="single_sync_gravity_output"></span><br /><br />




	</div>
	

	<?php
	}

	// if condition to either to divert to EDIT page screen or full products lists.
elseif ( isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="edit" ){

	$edit_id=$_REQUEST['id'];

	

	/**
	 * Get post excerpt by post ID.
	 *
	 * @return string
	 */
	
	
	

	
	$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($edit_id), 'thumbnail' );
	


	////getting product tags///
	$product_tags=(get_the_terms( $edit_id , 'product_tag' ));

	if (is_array($product_tags) ){
		foreach ($product_tags as $pr_tags){
			$product_tag_list =$product_tag_list.",". $pr_tags->name;
		}
		$product_tag_list=(substr($product_tag_list,1));
	}else	{	$product_tag_list=""; 
	}
	$product_tag_list=filter_var($product_tag_list, FILTER_SANITIZE_STRIPPED);

		
	//creating the nonce for security 
	$mycbgenie_custom_edit_nonce = wp_create_nonce( 'sp_mycbgenie_custom_edit' );
	
	
	
	
	echo mycbgenie_header_files().'<h1>Edit Product</h1>';
	
	
	




	?>
	
	<h3>My Custom Details:</h3>
	
	<div style="background:white; padding:20px; border:1px solid #dbdbdb; border-radius: 2px;">
	
		<div style="float:left">
		<span style="background:#CCCCCC; border-radius:3px; padding:7px;"> ID : <?php echo $edit_id?> </span> [
			<a  target=_blank href="<?php echo esc_url(get_permalink($edit_id)) ?>" title="View the product page">
			<img  width=25 height=25 src="<?php echo plugins_url('images/search.png', __FILE__ ) ?>" alt="View the product page"></a> |
					
			<a href="?action=mycbgenie_store_view&id=<?php echo $_GET['mycbgenie_id'] ?>" target="_blank" title="View the vendor website" > 
			<img  width=25 height=25 src="<?php echo plugins_url('images/search.png', __FILE__ ) ?>" alt="View the vendor website"></a>
			]
		</div>
			
			
			
	<div style="float:right">
		<form action="" method=post>
		<input type="hidden" name="ref_url" value="<?php echo isset($_POST['ref_url']) ? $_POST['ref_url'] : $_SERVER['HTTP_REFERER'] ?>" />
		<input type="hidden" 	name="mycbgenie_product_edit_back"	value="yes"	/>
	
		<input type="submit" class="button" value="Go Back" />
		</form>
	</div>
	
	<form action="" method=post>
	<table class="form-table">
	<tr>
	
		<td rowspan=6 valign="top" align="center">
		
			<div style="vertical-align:text-bottom">
			 <img id="mycbgenie_edit_image" width=100 height=100 style="margin-top:5px;" src="<?php echo $thumbnail['0'] ?>">
			<div align=center>
			<!--<img src="<?php echo plugins_url('images/upload.png', __FILE__ )?>" width=30 height=30 > -->
			<a href="<?php echo $edit_id ?>" id="upload_image_link" >  
			Change</a>
			</div>
			</div>
			<input type="hiddens" id="mycbgenie_attachment_id" name="mycbgenie_attachment_id" />
		</td>
	</tr>
	
	<tr><th scope="row">Price</th><td>
	$<input type="text" size="5" name="mycbgenie_price" value="<?php echo get_post_meta($edit_id, "_price",true); ?>" /></td>
	</tr>
	
	
	<tr><th scope="row">Title</th><td>
		<input type="text" size="42"  name="mycbgenie_title" value="<?php echo get_the_title( $edit_id ) ?>" /></td>
		
	</tr>
	<tr><th scope="row">Short Desc</th><td>
		<textarea rows="3" cols="60" name="mycbgenie_excerpt"><?php echo trim(get_post_excerpt_by_id( $edit_id )) ?></textarea></td>
	</tr>
	
	<tr><th scope="row">Long Desc</th><td>
		<textarea rows="5" cols="81" name="mycbgenie_descr"><?php echo trim(get_post_content_by_id( $edit_id )) ?></textarea></td>
	
	</tr>
	
	<?php 
	$my_terms = (wp_get_object_terms( $edit_id, 'product_tag' ));
	
	//echo implode($my_terms->name,",");
	$tmp_str='';
	foreach ($my_terms as $my_term){
		//echo $my_term->name.'d';
		if ($tmp_str==''){
			$tmp_str	=	$my_term->name;
		}
		else{
			$tmp_str	=	$tmp_str.','.$my_term->name;
			}
	}
	

	//var_dump($tmp_str); ?>
	<tr><th scope="row">Product Tags
			<h6>(seperated by commas)</h6></th><td>
		<textarea rows="5" cols="81" name="mycbgenie_keywords"><?php echo trim($tmp_str) ?></textarea></td>
	
	</tr>
	
		<tr><th scope="row"></th><td valign=bottom><?php submit_button() ?>		
		<input type="hidden"	name="mycbgenie_id" value="<?php echo get_post_meta($edit_id, "_mycbgenie_id",true);	?>"	/>
		<input type="hidden"	name="woo_id" 		value="<?php echo $edit_id	?>"	/>
	
		 
		<input type="hidden"	name="mycbgenie_product_edited_nonce" value="<?php echo $mycbgenie_custom_edit_nonce	?>"	/>
		<input type="hidden" 	name="mycbgenie_product_edit"	value="yes"	/>
		<input type="hidden" name="ref_url" value="<?php echo isset($_POST['ref_url']) ? $_POST['ref_url'] : $_SERVER['HTTP_REFERER']?>" />
		</form>
	<td>
	
	</td>
	</tr>
	<tr>
	<td>	</td></tr>
	</table>


	
	
	
	

	</div>

	<?php
	

}
else
		{

		global $mycbgenie_List_Table_products;
		echo mycbgenie_header_files().'<h2>Imported Clickbank Products</h2>';

		if ( ! get_user_meta(get_current_user_id(), 'mycbgenie_product_screen_dismiss_option') ) {

      	echo '<div class="updated">';
	 		printf(__(	
			
			'<h3>Imported Clickbank Products</h3><P>Below are the <strong>Clickbank products</strong> that are reviewed 
			by <strong>MyCBGenie</strong>
			 team and imported on to your WooCommerce store. 
			These imported 	products are set to SYNC with <strong>MyCBGenie\'s REMOTE</strong> server at regular 
			intervals set by you on the <a target=_blank href="admin.php?page=mycbgenie_main_menu&tab=cron_tab_3">settings</a> page. </p>
			
			
			
			<h4 style="margin-bottom:7px; margin-top:25px;">Edit</h4>
			
			<p>Though not necessary, you can edit the details of any product to override it\'s details. Once a product 
			is overridden by you, is exempted from subsequent SYNC processes in order to 
			preserve your custom edited detail preferences.</p>
			
			
			
			<h4 style="margin-bottom:7px; margin-top:25px;">SYNC</h4>
			
			<p>This option is to  
			  SYNC a particular product details with our REMOTE server. 
			 Remember, this action will cause to erase any <strong>custom edit</strong> 
			 details made against this product and will be reverted 
			 back to our default details. </p>
			
			<h4 style="margin-bottom:7px; margin-top:25px;">FEATURED PRODUCTS</h4>
			
			<p>Please click on the <img  width=18 height=18 src="'.plugins_url
									('images/fav_red.png', __FILE__ ).' "> symbol to feature any product in to the featured products list. 
									We have seen many storefront owners list the featured products on to their home page
			as a widget or slider. You can find many FREE third party plugins out there in the WordPress Plugin Directory.   
			You may check out for some providers in
			 <a href="plugin-install.php?tab=search&type=term&s=WooCommerce+featured+products" target=_blank>WordPress Plugin Directory</a></p>



			<h4 style="margin-bottom:7px; margin-top:25px;">ENABLE/HIDE PRODUCTS</h4>
			
			<p>Toggle between <img  width=18 height=18 src="'.plugins_url
									('images/visible_green.gif', __FILE__ ).' "> and 
								<img  width=14 height=14 src="'.plugins_url
									('images/visible_red.png', __FILE__ ).' ">
										symbol to hide/enable any product on the storefront.     </p>
			
						 			
			
			<div style="background:lightyellow; padding:10px; margin-top:25px; line-height:2%;">
			<i><strong>	Note: </strong> As we will not have any clue on the activities that you do from your 
			<strong>WooCommerce</strong> screens, 
			please <strong>DO NOT</strong> edit any of these imported  Clickbank products directly from the  
			<a target=_blank href="edit.php?post_type=product"><strong>WooCommerce Products</strong></a> page.
			Any update that you make through this <strong>WooCommerce Products</strong> page, on the ClickBank products that were imported by us, will be overwritten
			with our REMOTE server details in the next SYNC/CRON schedule.
			So it is suggested that you may EDIT any Clickbank Products using this screen only, in case if you wish to override the default details. 
			At the same time, MyCBGenie script will never overwrite/interfere on any other products created from 
			<strong>WooCommerce</strong> screen. </i></div> 
			
			<div align=right style="margin-top:15px"><a href="%1$s"><< Hide Help</a></div>')   , '?mycbgenie_nag_ignore3=0');
		  
		   echo "</p></div>";
		}
		else
		{
		printf(__(' <div align=right style="margin-bottom:10px;">
		<img width=30 height=30 src="'.plugins_url('images/help-icon.png', __FILE__ ).'"> 
		<a style="text-decoration:none;" href="%1$s">
		[ Show Help ]</a></div>') , '?mycbgenie_nag_show3=0');
		}
	

		?>
  		<div align=right style="margin:0px;">

		<!--<input type="search" id="search_id-search-input" name="s" value="tattoo" />
		<input
		 type="submit" id="search-submit" class="button" value="search"  /></p>
		-->
		
		<?php	
		//global $total_products;
		
		
		//$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] -1) * $perPage) : 0;
		//if (   (int)$paged == 0  ) {
			//$total_products=$mycbgenie_List_Table_products->record_count( esc_attr($_REQUEST['s']) );
		//}
			$mycbgenie_List_Table_products->prepare_items(esc_attr($_REQUEST['s'] ));
		?>
				
			<form>	
			
		<?php
			//$mycbgenie_List_Table_products->search_box( 'search', 'search_id' );
			//echo '';
			
  			$mycbgenie_List_Table_products->display(); 
	
  		echo '</div>'; 
		
} // end of action EDIT
		
		
		
		

}

?>