<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');

// Function to import main categories
function mycbgenie_insert_main_category_term()

{

		global $wpdb;

		global $mycbgenie_import_mode;
		$mycbgenie_import_mode="yes";

 		$url = 'https://cbproads.com/xmlfeed/woocommerce/zip/main_category.asp';
		
		$json = json_decode(mycbgenie_sf_file_get_contents_curl($url,0,null,null));
		
		$main_cat_array=array();

    	foreach ($json as $key=>$value) {
	
			foreach ($value as $key => $val) { 
			
			if ($key=='hName') { $main_cat= $val;}
			if ($key=='hCode') { $mycbgenie_cat_id= $val;}
			if ($key=='thumbnail') { $thumb_name= $val;}

			}
			

	
	   		$cid = wp_insert_term(
      			$main_cat, //."d",
     		 	'product_cat', // the taxonomy
    		  	array(
      		 	 	'description'=> $main_cat,
       			 	'slug' => $main_cat,
					'parent'=> $parent_term_id,
      			)
    		);     // end of wp_insert_term
			
			if ( ! is_wp_error( $cid ) )
			{

				 $cat_id	=	$cid['term_id'];
				 $term = get_term( $cid['term_id'],'product_cat' );
				 $slug = $term->slug;
			
				//pushing the imported categoreies to an array. Useful to identify imported categories.
				//array_push($main_cat_array,$slug);
			
					$temp_array= array(
				
					'slug'	=>	$slug,
					'term_id'	=>	$cid['term_id'],
					'parent_term_id'	=>	$parent_term_id,
					'mycbgenie_sub_cat_id'	=>	NULL,
					'mycbgenie_main_cat_id'	=> $mycbgenie_cat_id
				
					);
			}
			else
			{
			
     				// Trouble in Paradise:
    					// echo $main_cat.'-'.$cid->get_error_message();
			 	if (strpos($cid->get_error_message(), 'provided already exists with this parent' )) {

					$term_exists=get_term_by('slug', sanitize_title($main_cat), 'product_cat');
					
					//$term_exists= get_term_by('name', htmlspecialchars($main_cat), 'product_cat');
					
	//	var_dump($term_exists);		
	
						 $cat_id	=	$term_exists->term_id;
	
						$temp_array= array(
					
						'slug'	=>	$term_exists->slug,
						'term_id'	=>	$term_exists->term_id,
						'parent_term_id'	=>	$term_exists->parent,
						'mycbgenie_sub_cat_id'	=>	NULL,
						'mycbgenie_main_cat_id'	=> $mycbgenie_cat_id
					
						);
				 } //if strpos
			}

			
			//inserting post to create thumbnials for each terms
			 $sql="SELECT * FROM ". $wpdb->prefix."posts 
				 	WHERE ( post_type='mcg_thumbnail' and post_content='".sanitize_title($main_cat)."') ";
				
			 $result = $wpdb->get_results( $sql );

	 		$count_posts	=	intval(	count($result)	);

	 		if ($count_posts >0 ) {
			
					$post_id	=	$result[0]->ID;

			}else{
					$post = array(
							 'post_author' => 1,
							 'post_content' => sanitize_title($main_cat),
							 'post_excerpt'=> sanitize_title($main_cat),
							 'post_status' => "publish",
							 'post_title' => ($main_cat),
							 'post_parent' => '',
							 'post_type' => "mcg_thumbnail",
						
							 );
					 
										  //Create post
					$post_id = wp_insert_post( $post, $wp_error );
							if(  is_wp_error( $post_id ) ) {
		
								$error_message= "creating thumbnail error for term". $main_cat. ",". $post_id->get_error_message();
								$error->add( 'InsertError', $error_message ); }
								
					//update post meta
					update_post_meta( $post_id, '_mycbgenie_managed_by', 'mycbgenie' );

			} //end of if ($count_posts >0 ) {
			
				
			$uploads = wp_upload_dir();
			$thumb_url	=	$uploads['basedir'].'/mycbgenie/media/thumbnails_extract/'.$thumb_name;
			$thumb_id = mycbgenie_fetch_media($thumb_url, sanitize_title($main_cat) ,$post_id);
			update_woocommerce_term_meta( $cat_id, 'thumbnail_id', absint( $thumb_id ) );
	
			//end of creating thumbnail
			
			
			//pusing to options
			array_push($main_cat_array,$temp_array);

   		 } // end of foreach
		 
//var_dump($main_cat_array);
	
		//setting imported categories array to an option.
		update_option('mycbgenie_imported_main_terms',$main_cat_array);
		 $mycbgenie_import_mode="no";
}



 
 
// Function to import sub categories
function mycbgenie_insert_sub_category_term()

{

	global $wpdb;
	
	global $mycbgenie_import_mode;
		$mycbgenie_import_mode="yes";

		
	$url = 'https://cbproads.com/xmlfeed/woocommerce/zip/categories.asp';
	
	//$url = 'C:\wamp\www\wp\wp-content\plugins\mycbgenie-clickbank-storefront\zip\categories.asp';

  	$json = json_decode(mycbgenie_sf_file_get_contents_curl($url,0,null,null));

	$sub_cat_array=array();


	
	
    foreach ($json as $key=>$value) {

		$main_cat="";
		
		foreach ($value as $key => $val) { 
		
		
			if ($key=='hCode') {	$mycbgenie_main_cat= $val;}
			if ($key=='thumbnail') {	$thumb_name= $val;}
		
			if ($key=='hName') {  $main_cat= $val; 
			
		
			$parent_term = term_exists( $main_cat, 'product_cat' );
	
			if ($parent_term !== 0 && $parent_term !== null) {
  
    				$parent_term_id = $parent_term['term_id']; // get numeric term id
			}
			else
				{ 
				//echo $main_cat."<h1> not found </h1>";
				}
		
			}
		
			if ($key=='sName') { $sub_cat= $val; } //print $val."<hr>";}
			
			
			if ($key=='sCode') { $mycbgenie_cat_id= $val;}
			
				
				
		} //end of inner for loop
	
if ($sub_cat=='Soccer')	{
//echo $main_cat.'-'.$sub_cat."<br>";
//echo $parent_term_id."<br>";

}		
   		$cid=wp_insert_term(
	
      	$sub_cat,
      	'product_cat', // the taxonomy
      	array(
        	'description'=> $main_cat.'-'.$sub_cat,
        	'slug' => $main_cat.'-'.$sub_cat,
			'parent'=> $parent_term_id,
      		)
		);
	
	
		
	  
			if ( ! is_wp_error( $cid ) )
			{

				 $cat_id	=	$cid['term_id'];
				 $pr		=	$parent_term_id;

				 $term = get_term( $cid['term_id'],'product_cat' );
				 $slug = $term->slug;
				 
				 $temp_array= array(
					
						'slug'	=>	$slug,
						'term_id'	=>	$cid['term_id'],
						'"parent_term_id'	=>	$parent_term_id,
						'mycbgenie_sub_cat_id'	=>	$mycbgenie_cat_id,
						'mycbgenie_main_cat_id'	=> $mycbgenie_main_cat
					
				 );
			
			}
			else
			{
     				// Term already exists:
    				//echo $cid->get_error_message();
				if (strpos($cid->get_error_message(), 'provided already exists with this parent' )) {
					$term_exists=get_term_by('slug', sanitize_title($main_cat.'-'.$sub_cat), 'product_cat');
					//$term_exists= get_term_by('name', htmlspecialchars($sub_cat), 'product_cat');

							
					//var_dump($term_exists);
					$cat_id	=	$term_exists->term_id;
					 $pr		=	$term_exists->parent;

							 
					$temp_array= array(
					
						'slug'	=>	$term_exists->slug,
						'term_id'	=>	$term_exists->term_id,
						'parent_term_id'	=>	$term_exists->parent,
						'mycbgenie_sub_cat_id'	=>	$mycbgenie_cat_id,
						'mycbgenie_main_cat_id'	=>  $mycbgenie_main_cat
					
					);
				}	
			}

			//inserting post to create thumbnials for each terms
	
			$sql="SELECT * FROM ". $wpdb->prefix."posts 
				 	WHERE ( post_type='mcg_thumbnail' and post_content='".sanitize_title($main_cat.'-'.$sub_cat)."') ";
				
			 $result = $wpdb->get_results( $sql );

	 		$count_posts	=	intval(	count($result)	);

	 		if ($count_posts >0 ) {
			
					$post_id	=	$result[0]->ID;

			}else{
				
					$post = array(
					 'post_author' => 1,
					 'post_content' => sanitize_title($main_cat.'-'.$sub_cat),
					 'post_excerpt'=> sanitize_title($main_cat.'-'.$sub_cat),
					 'post_status' => "publish",
					 'post_title' => ($main_cat.'-'.$sub_cat),
					 'post_parent' => '',
					 'post_type' => "mcg_thumbnail",
				
					 );
			 
					     		  //Create post
   					$post_id = wp_insert_post( $post, $wp_error );
			  		if(  is_wp_error( $post_id ) ) {

						$error_message= "creating thumbnail error for sub term". $main_cat.'-'.$sub_cat. ",". $post_id->get_error_message();
						$error->add( 'InsertError', $error_message ); }
		
					//update post meta
					update_post_meta( $post_id, '_mycbgenie_managed_by', 'mycbgenie' );

			} //end of count
			$uploads = wp_upload_dir();
			$thumb_url	=	$uploads['basedir'].'/mycbgenie/media/thumbnails_extract/'.$thumb_name;
			$thumb_id = mycbgenie_fetch_media($thumb_url, sanitize_title($main_cat.'-'.$sub_cat) ,$post_id);

			update_woocommerce_term_meta( $cat_id, 'thumbnail_id', absint( $thumb_id ) );

			//end of creating thumbnail
			
			
			
		//adding slug as elements to array

				
			array_push($sub_cat_array , $temp_array);
				

    }  // end of main foreach
	
	//print_r ($sub_cat_array);
	update_option('mycbgenie_imported_sub_terms',$sub_cat_array);
	//update_option('mycbgenie_imported_sub_term_ids', $sub_cat_id_array);
	 $mycbgenie_import_mode="no";
}		//end of function



//function that imports clickbank products 
function mycbgenie_import_products($url,$final_step,$import_id,$resume,$throttle,$modulus,$step,$already_processed,$batch_interval,$screenshot_allowed)
{

global $wpdb;



//wp_defer_term_counting( false );
$wpdb->query( 'START TRANSACTION;' );
$wpdb->query( 'SET autocommit = 0;' );



wp_suspend_cache_addition(true);
set_time_limit(0);
		

	
		$error = new WP_Error();
		
		$json = json_decode(file_get_contents($url,0,null,null));
		
		if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
		   
		}

		$temp_count=0;
		$product_master_table_minus=0;
		//$batch_interval=intval($batch_interval);

		
		$to_be_omitted_count=0;//=	intval(($already_processed )- (	($step-1)* $throttle	));
		
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
				if ($key=='physical') { $physical= $val;}
				if ($key=='rank') { $rank= $val;}
				if ($key=='gravity') { $gravity= $val;}
				if ($key=='keywords') { $keywords= $val;}
				if ($key=='maincat') { $maincat= $val;}
				if ($key=='subcat') { $subcat= $val;}
				if ($key=='descr')  { $descr= $val;}
				if ($key=='rating')  { $rating= $val;}
				if ($key=='last_image_updated')  { $last_image_updated	= $val;}


			}
			
			$image_name=$images;
			
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


			$temp_count = $temp_count+1;
	
			if ($resume=="delete" || $resume=="fresh"){
			
				$exists_count=0;
			}
			else{
						
						
				
				$to_be_omitted_count=intval(	intval($already_processed )- (	($step-1)* $throttle	)	);
				
				if ($to_be_omitted_count >= 0) {	$product_master_table_minus=$to_be_omitted_count;}	
				
				//if $to_be_omitted_count>0 means, already inserted,,,,, just ignore them
				if ($temp_count <= $to_be_omitted_count) {
						$exists_count=1;
				}	
				else{
						$exists_count=0;
				}
				
			}


			if ($exists_count==0) {
				

				//inserting to import table
				
				$wpdb->query( 
							$wpdb->prepare( 
							"	
								INSERT INTO mycbgenie_fresh_import_product_details (mycbgenie_import_id,mycbgenie_id,insert_time) 
								VALUES ('%s','%s','".date("Y-m-d H:i:s")."')
							",
							$import_id,	$mycbgenie_id
							)
				);
				if ($wpdb->last_error) {
							$error_message= "Post Title : ". $title. " Image URL : ".$images.
							" Error in  inserting to fresh_import_product_details TABLE '" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );									}

		$json_object_array	=	json_decode (mycbgenie_insert_new_product
						($mycbgenie_id,$title,$descr,$mdescr,$rank,$gravity,$price,$image_name,$images,$maincat,$subcat,$keywords,$import_id,$rating,$last_image_updated, $screenshot_allowed,$physical),true);

		$post_id		=	$json_object_array['post'];
		//$error_msg		=	$json_object.error_msg;
		
		 if($json_object_array['error_msg'] != '') {
						$error->add( 'InsertError', $json_object_array['error_msg'] );	}	

				
			}//if of exists_count		
    	}    // end of foreach
		
	
	
		if ( $modulus ==0) { $throttle_local= $throttle;	}
		else	{ $throttle_local= $modulus;	}
			 
	
			 
		$wpdb->query( 
				$wpdb->prepare( 
				"	
					UPDATE mycbgenie_fresh_import_products_master
					SET products_completed=products_completed-$product_master_table_minus+%d ,   
					batch_interval	= $batch_interval, last_used_throttle=%d, 
					screenshot_allowed = '%s' 
					WHERE mycbgenie_import_id='%s'	
					"
				,
				$throttle_local,$throttle,$screenshot_allowed,$import_id
				)
				
		);	if ($wpdb->last_error) {
		  						$error_message= "Import ID : ". $import_id. " Step : ".$step.
							" Error in updating  fresh_import_product_master TABLE '" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );	}
  						
	


		if(	$final_step=='final_step'){
	

				
				if(	$resume=='resume'){
					$wpdb->query( 
							$wpdb->prepare( 
							"	
								UPDATE mycbgenie_fresh_import_products_master
								SET status_modified='success' , modified_time='".date("Y-m-d H:i:s")."',
								screenshot_allowed = '%s' 
								WHERE mycbgenie_import_id='%s'	
								"
							,
							$screenshot_allowed,$import_id
							)
							
						);
						if ($wpdb->last_error) {
		  					$error_message= "Import ID : ". $import_id. " Step : ".$step.
							" Error in updating in FINAL STEP(resume) of fresh_import_product_master TABLE '" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );	}
  						
	
				}
				elseif(	$resume=='fresh' || $resume=='delete' ){
				
					$wpdb->query( 
							$wpdb->prepare( 
							"	
								UPDATE mycbgenie_fresh_import_products_master
								SET status_import='success' , end_time='".date("Y-m-d H:i:s")."', 
								screenshot_allowed = '%s' 
								WHERE mycbgenie_import_id='%s'	
								"
							,
							$screenshot_allowed,$import_id
							)
							
						);
						if ($wpdb->last_error) {
		  					$error_message= "Import ID : ". $import_id. " Step : ".$step.
							"Error in updating in FINAL STEP(fresh/delete) of fresh_import_product_master TABLE'" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );	}
  								
				}
				

				

				
		} //final step
	
	
		
	$error_message='';
	
	if ( 1 > count( $error->get_error_messages() ) ) {
	
		if(	$final_step=='final_step'){
	
			//reverse woocommerce image dimensions
		mycbgenie_reverse_woocommerce_image_dimensions();
		}
	}
	else{
		
		$wpdb->query( 'ROLLBACK;' );
		
 		 //reverse woocommerce image dimensions on error
		 mycbgenie_reverse_woocommerce_image_dimensions();

	
		 foreach ($error->get_error_messages() as $err){
	 		$error_message=$error_message.' -  '.$err;
		 }
		 
	 	echo $error_message;
	}
	//}
	
	
	

	
	$wpdb->query( 'COMMIT;' );
	$wpdb->query( 'SET autocommit = 1;' );	
	//$wpdb->query( 'UNLOCK TABLES;' ); 
	//wp_defer_term_counting( false );  
	
	$wpdb->flush();   
	

	
	

}


function mycbgenie_update_terms_and_tags($post_id,$tags,$maincat,$subcat)
{
		   // $taxonomies = get_taxonomies( '', 'names' );   
		  //foreach ($taxonomies as $taxonomy ) {  
			// echo '<p>'. $taxonomy. '</p>';  
		  // }  
			$returns=wp_set_object_terms( $post_id, explode(",",$tags), 'product_tag',false);
			
				if ( is_wp_error( $returns ) ) {
					return	"error_tag";
				}
										
			//Identifying the term ID of the main category
			$parent_term=term_exists(htmlentities($maincat), 'product_cat');
			$parent_term_id=(int)$parent_term['term_id'];
			 //Identifying the term ID of the sub category
			$term=term_exists(htmlentities($subcat), 'product_cat',$parent_term_id);
			$cats=wp_set_object_terms( $post_id,  (int)$term['term_id'] , 'product_cat' );
		
				if ( is_wp_error( $cats ) ) {
					return	"error_terms";
				}
			
}




function mycbgenie_insert_new_product($mycbgenie_id,$title,$descr,$mdescr,$rank,$gravity,$price,$images,$imageurl,$maincat,$subcat,$keywords,$sync_id,$rating,$last_image_updated, $screenshot_allowed,$physical){
/*
error_log("=============================================================================");
error_log('mycbgenie_id '. $mycbgenie_id);
error_log('title '. $title);
error_log('descr '. $descr);
error_log('mdescr '. $mdescr);
error_log('rank '. $rank);
error_log('gravity '. $gravity);
error_log('price '. $price);
error_log('images '. $images);
error_log('imageurl '. $imageurl);
error_log('maincat '. $maincat);
error_log('subcat '. $subcat);
error_log('sync_id '. $sync_id);
error_log('rating '. $rating);
error_log('last_image_updated '. $last_image_updated);
error_log('screenshot_allowed '. $screenshot_allowed);

error_log("=============================================================================");
*/
				$error = new WP_Error();

			 	$post = array(
					 'post_author' => 1,
					 'post_content' => $mdescr,
					 'post_excerpt'=> $descr,
					 'post_status' => "publish",
					 'post_title' => $title,
					 'post_parent' => '',
					 'post_type' => "product",
				
					 );

	 
    		  //Create post
   			  $post_id = wp_insert_post( $post, $wp_error );
			  if(  is_wp_error( $post_id ) ) {

						$error_message= "Post Title : ". $title. " ID : ".$mycbgenie_id.
						" Error in wp_insert_post...".$post_id->get_error_message();
						$error->add( 'InsertError', $error_message ); }


			 if (($rank<=8) && ($gravity>60) ){

					$feature_yes_no='yes';
				}else{
					$feature_yes_no='no';
				}
			  
			  //setting rating
			  mycbgenie_update_rating($post_id,$rating);

			  //Identifying the term ID of the main category
			  $parent_term=term_exists(htmlentities($maincat), 'product_cat');
			  $parent_term_id=(int)$parent_term['term_id'];
			  //Identifying the term ID of the sub category
			  $term=term_exists(htmlentities($subcat), 'product_cat',$parent_term_id);
			  wp_set_object_terms( $post_id,  (int)$term['term_id'] , 'product_cat' );
			  //setting product tags from keywords
			  wp_set_object_terms( $post_id, explode(",",$keywords), 'product_tag');
			  
			  //setting featired products
			  if (($rank<=10) && ($gravity>40) ){
					wp_set_object_terms( $post_id, 'featured', 'product_visibility');
   			  }
				
			  //Setting as EXTERNAL products
			  wp_set_object_terms ($post_id, 'external', 'product_type');
			  
			 $target_url	=	"?action=mycbgenie_store_view&id=".$mycbgenie_id;
			// Prepare and insert the custom post meta
			  global $wpdb;
			  $meta_keys = array();
			  $meta_keys['_mycbgenie_managed_by'] 	=  'mycbgenie';
			  $meta_keys['_mycbgenie_last_sync']  	=  date('m/d/Y h:i:s a', time());
			  $meta_keys['_mycbgenie_rank'] 		=  $rank;
			  $meta_keys['_mycbgenie_gravity'] 		=  $gravity;
			  $meta_keys['_mycbgenie_id'] 			=  $mycbgenie_id;
			  $meta_keys['_visibility'] 			=  'visible';
			  $meta_keys['_featured'] 				=  $feature_yes_no;
			  $meta_keys['_product_url'] 			=  $target_url;
			  $meta_keys['_price'] 					=  $price;
			  $meta_keys['_mycbgenie_image_url'] 	=  $images;
			  $meta_keys['_mycbgenie_screenshot_allowed'] 	=  $screenshot_allowed;	
			  if ($physical=="yes") {
				  $meta_keys['_mycbgenie_physical_product'] 	=  $physical;					 
			  }else{
				    $meta_keys['_mycbgenie_physical_product'] 	=  "no";	
			  }
			  $meta_keys['_mycbgenie_last_image_updated'] 	= 	$last_image_updated;
			  
			 // if ($sync_id != ''){
			  $meta_keys['_mycbgenie_sync_id'] 	=  $sync_id;
			  $meta_keys['_button_text'] 		=  'Read more';
			  $meta_keys['_stock_status'] 		=  'instock';
			  
			  
			 // _button_text

			  //}
									  
									  
			  $custom_fields = array();
			  $place_holders = array();
			  $query_string = "INSERT INTO ". $wpdb->prefix."postmeta ( post_id, meta_key, meta_value) VALUES ";
			  foreach($meta_keys as $key => $value) {
				 array_push($custom_fields, $post_id, $key, $value);
				 $place_holders[] = "('%d', '%s', '%s')";
			  }
			  $query_string .= implode(', ', $place_holders);

			  $wpdb->query( $wpdb->prepare("$query_string ", $custom_fields));
			  
			  if ($wpdb->last_error) {
					$error_message= "Post Title : ". $title. " ID : ".$mycbgenie_id.
									" Error in inserting post meta data '" . $wpdb->last_error;	
									$error->add( 'InsertError', $error_message );	}
			 
			  $wpdb->flush();
	
	
	
	          
			  
			  
			  //$thumb_url=  $images;
			  
			  
			  $tmp_error_message='';
	 		  //$tmp_error_message= mycbgenie_update_thumbnail( $imageurl,$post_id);
			

				
  			  $tmp_error_message=  mycbgenie_fetch_product_images($imageurl,$mycbgenie_id, $images, $post_id, $altimage , $screenshot_allowed , "import");
			  
			
			  
			  
			  if($tmp_error_message != '') {
			  
						$error->add( 'InsertError', $tmp_error_message );	}			
		
	
				if ( 1 > count( $error->get_error_messages() ) ) {}
				else{
			
					 foreach ($error->get_error_messages() as $err){
						$error_message=$error_message.' -  '.$err;
					 }
					 
					//echo $error_message;
				}

			 return json_encode( array( 
							'post' => 	 $post_id, 
							'error_msg'	=> $error_message
			));  
									

	

}

function mycbgenie_update_thumbnail($thumb_url,$post_id){


$error_message="";


if (substr_count($thumb_url,"cbproads.com/cbbanners/blank.gif")==0) {

				// Download file to temp location
				$tmp = download_url( $thumb_url );
				
	
				// Set variables for storage
				// fix file name for query strings
				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|bmp|BMP|png|PNG)/', $thumb_url, $matches);
				//$matches[0]=$tmp;
				$file_array['name'] = basename($matches[0]);
				$file_array['tmp_name'] = $tmp;
			
				// If error storing temporarily, unlink
				if ( is_wp_error( $tmp ) ) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
				if ($debug) { 
					$error_message= "Post Title : ". $title. " Image URL : ".$images.
									" Error in storing image temp file! <br />"; 
									$error->add( 'Image_Error', $error_message );	}
				}	
				//use media_handle_sideload to upload img:
				$thumbid = media_handle_sideload( $file_array, $post_id, $title );
			
				// If error storing permanently, unlink
				if ( is_wp_error($thumbid) ) {
				//	@unlink($file_array['tmp_name']);
				//	$error_message=" Post Title : ". $title. " Image URL : ".$thumb_url.
									//" Error in thumbnail media handle side load,".$thumbid->get_error_messages();
									//$error->add( 'Image_Error', $error_message );
				}
				
				else {
						
						set_post_thumbnail($post_id, $thumbid);

				}	
				
}	

		
	return  $error_message;  
	
		

}

function test11(){
$post_id=123;
$url = "http://wordpress.org/about/images/logos/wordpress-logo-stacked-rgb.png";
$desc = "The WordPress Logo";
$image = media_sideload_image($url, $post_id, $desc);

//mycbgenie_update_rating(33936,5);
}


function mycbgenie_update_rating($post_id,$rating){

global $wpdb;

	 $sql="SELECT comment_ID FROM ".$wpdb->prefix."comments 
				 	WHERE ( comment_post_ID=".$post_id." and comment_author_url='http://mycbgenie.com' and comment_author='admin')";
				
	 $result = $wpdb->get_results( $sql );
	 
	 $comment_count	=	intval(	count($result)	);


	//echo $sql;
	//echo $comment_count;


	//var_dump($comment_id_tmp);
	
	if ($comment_count==0){
	
		$comment_count=1;
	
		$time = current_time('mysql');
	
		$data = array(
		'comment_post_ID' => $post_id,
		'comment_author' => 'admin',
		'comment_author_email' => 'admin@mycbgenie.com',
		'comment_author_url' => 'http://mycbgenie.com',
		'comment_content' => 'Rated as per Clickbank Marketplace stats',
		'comment_type' => '',
		'comment_parent' => 0,
		'user_id' => 1,
		'comment_author_IP' => '127.0.0.1',
		'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
		'comment_date' => $time,
		'comment_approved' => 1,
		);
	
		$comment_id=wp_insert_comment($data);
	}
	else{
	
	
				$comment_id	=	$result[0]->comment_ID;
				$comment_count=$comment_count;
				//echo $comment_id;
	}

	update_comment_meta( $comment_id, 'rating', $rating );

	if ($rating==5) {
		$my_cb_rating="mycbgenie-5-star-rating";
		$rated="rated-5";
		}
	elseif ($rating==4) {	
		$my_cb_rating="mycbgenie-4-star-rating";
		$rated="rated-4";	
		}
	elseif ($rating==3) {	
		$my_cb_rating="mycbgenie-3-star-rating";
		$rated="rated-3";
		}
		
	elseif ($rating==2) {	
		$my_cb_rating="mycbgenie-2-star-rating";
		$rated="rated-2";	
		}
	elseif ($rating==1) {	
		$my_cb_rating="mycbgenie-1-star-rating";
		$rated="rated-1";	
		}
	
	//setting product attribute
	wp_set_object_terms( $post_id, $my_cb_rating, "pa_mycbgenie-star-rating", false);

	$product_attributes['pa_mycbgenie-star-rating'] = array(
        //Make sure the 'name' is same as you have the attribute
        'name' => 'pa_mycbgenie-star-rating',//htmlspecialchars(stripslashes('CB Rating')),//mycbgenie-star-rating
        'value' => $my_cb_rating,  //replace 'term name' with a valid term
        'position' => 1,
        'is_visible' => 1,
        'is_variation' => 0,
        'is_taxonomy' => 1
    );

	//Add as post meta, ensure to replace 123 with a valid product id
	update_post_meta($post_id, '_product_attributes', $product_attributes);


	if ($comment_count==0){	$comment_count=1; }
	$re = array(
		 "1" => 1,
		 "2" => 2,
		 "3" => 3,
		 "4" => 4,
		 "5" => 5,
	   );
//	wp_set_object_terms( $post_id, $rated , "product_visibility", false);
	
	update_post_meta( $post_id, '_wc_average_rating', $rating );
	//update_post_meta( $post_id, '_wc_review_count', $comment_count );
//	update_post_meta( $post_id, '_wc_rating_count', $re );


}


function mycbgenie_remote_update_fresh_install($status){

	$remote_url = 'http://mycbgenie.com/php/sync_wordpress_installs/fresh_install.php';
	$url_tmp=	get_site_url();
	

	if ($status=='started') {
	
			$args	=	 array( 
						'url' =>  $url_tmp, 
						'endata' => md5($url_tmp) 
				);
	}
	elseif ($status=='finished')  {
	
		$args	=	 array( 
						'url' =>  $url_tmp, 
						'endata' => md5($url_tmp),
						'fresh_install_end_date'	=> $status
				);
	}
	
	
	$response = wp_remote_post( $remote_url, array(
		//'method' => 'POST',
		//'timeout' => 45,
		//'redirection' => 5,
		//'httpversion' => '1.0',
		//'blocking' => true,
		//'headers' => array(),
		'body' => $args
		//'cookies' => array()
		)
	);

	if ( is_wp_error( $response ) ) {
		 //  $error_message = $response->get_error_message();
	  	//echo "Something went wrong: $error_message";
	} else {
	   //echo 'Response:<pre>';
	  // print_r( $response );
 	 //echo '</pre>';
	}

}

function mycbgenie_ajax_cb_import_resume_delete_function(){

	 if ( !isset($_POST['mycbgenie_nonce']) || !wp_verify_nonce( $_POST['mycbgenie_nonce'], "local_mycbgenie_nonce")) {
		 exit("No naughty business please");
	   } 

	global $wpdb;
//	wp_defer_term_counting( false );
	
	$delete_or_resume	= 	$_POST['delete_or_resume'];
	$remote_file_id		=	$_POST['remote_file_id'];
	$current_throttle	=	$_POST['throttle_speed'];



			
	
	if ($delete_or_resume=="delete" || $delete_or_resume=="fresh"){
	
		$process_type ="fresh";
		$skip_file_import	=	"no";

		//$wpdb->query("TRUNCATE TABLE `mycbgenie_fresh_import_products_master`");
		$wpdb->query("TRUNCATE TABLE `mycbgenie_fresh_import_product_details`");
		$wpdb->query("TRUNCATE TABLE `mycbgenie_manual_sync_products_master`");
		$wpdb->query("TRUNCATE TABLE `mycbgenie_cron_sync_master`");


		//delete previous import setttings
		
		update_option( 'mycbgenie_imported_main_terms' ,array());
		update_option( 'mycbgenie_imported_sub_terms' ,array());
		update_option( 'mycbgenie_disabled_products' ,array());
		update_option( 'mycbgenie_featured_products' ,array());
		update_option( 'mycbgenie_excluded_terms' ,array());
		
		
		$sql="SELECT * FROM mycbgenie_fresh_import_products_master
			WHERE ( status_import ='success' or  status_modified ='success' )  order by start_time desc";
		
		
		 $result_temp = $wpdb->get_results( $sql );
		 
		// wp_die(count($result_temp)."ddd");
		
		 if (count($result_temp)>0){
	
			$last_success_import_file	=	$result_temp[0]->json_import_file_id;	
			$last_used_throttle			=	$result_temp[0]->last_used_throttle;	
		  
		 
			 if( $last_success_import_file == $remote_file_id &&  $current_throttle ==	$last_used_throttle ){
					$skip_file_import=	"yes";
			}
		}
//wp_die(count($result)." Count".$import_existing_file_id.">".$skip_file_import.">".$last_used_throttle."-PP".$last_success_import_file);				 
		$import_id=time();
		
		$wpdb->query( 
							$wpdb->prepare( 
							"DELETE FROM mycbgenie_fresh_import_products_master WHERE ( status_import is NULL and  status_modified  is NULL ) ",""
											)
				);
				
		$wpdb->query( 
							$wpdb->prepare( 
							"	
								INSERT INTO mycbgenie_fresh_import_products_master (mycbgenie_import_id,start_time,json_import_file_id) 
								VALUES ('%s','".date("Y-m-d H:i:s")."','%s')"
							,
							$import_id,$remote_file_id
							)
				);
				
				if ($wpdb->last_error) {
							$error_message=
							" Error in  inserting a fresh entry to fresh_import_product_details TABLE '" . $wpdb->last_error;	}
				
										

	}
	elseif ($delete_or_resume=="resume")	{
	
		$process_type ="resume";


	
		 $batch_interval	= intval($_POST['batch_interval']);
		 
		 sleep($batch_interval);

		 	
		 $sql="SELECT status_import,mycbgenie_import_id,start_time,products_completed,last_used_throttle,json_import_file_id FROM mycbgenie_fresh_import_products_master 
				 	WHERE ( status_import  IS NULL  AND status_modified  IS NULL ) order by start_time desc";
				
	 	 $result = $wpdb->get_results( $sql );
	
		 $import_id				=		(	$result[0]->mycbgenie_import_id	);
		 $products_processed	=	$result[0]->products_completed;
		 
 		$import_existing_file_id		= 	$result[0]->json_import_file_id;
		$existing_import_id				=	$result[0]->mycbgenie_import_id;
		$last_used_throttle				=	$result[0]->last_used_throttle;
	

		 	 
		 if ($wpdb->last_error) {
							$error_message=
							" Error in  retrieving import id of already imported id '" . $wpdb->last_error;	}
							
							
							
		if 		($import_existing_file_id == $remote_file_id && $current_throttle == $last_used_throttle  ){			
					$skip_file_import=	"yes";


		}elseif ($import_existing_file_id == $remote_file_id && $current_throttle <> $last_used_throttle  ){			
					$skip_file_import=	"no";

					
		}else{		
						
				$skip_file_import=	"no"; 

				if ($import_existing_file_id== ' ' || empty ($import_existing_file_id) || is_null($import_existing_file_id)) {
				}
				else
				{
	
					// remote file ID is changed in the mean time after this process has started with another file ID.	
					//So, skipping and taking data only from  the local folder to which is already downloaded.		
					//(Must remember that we are resuming of a previous import interuupted todaty or even days before.
					//SO its natural the current remote file might have changed in this time.
					if($remote_file_id	<> $import_existing_file_id){			
						$skip_file_import=	"yes"; 
						$json_file_change_detected="yes";
						if ($current_throttle <> $last_used_throttle) {
							$throttle_changed="yes";
							$throttle_old_value=$last_used_throttle;
						}
					}
									
				}
		}

	}
				
	 $wpdb->flush();
	 
 

	// wp_die($skip_file_import."-".$remote_file_id."-".$json_file_change_detected."-PP".$products_processed);
//wp_die($import_existing_file_id.">".$remote_file_id.">".$current_throttle."-".$last_used_throttle."-PP".$skip_file_import.">".$json_file_change_detected);
	
	//import the JSON zip file from REMOTE server to local server and UNZIPP
	$throttle_speed=$_POST['throttle_speed'];
	
	$json_object	=	json_decode(mycbgenie_sideload_remote_JSON($throttle_speed,$skip_file_import,$json_file_change_detected,$process_type),true);
	$total_steps	=	$json_object['total_steps'];
	$total			=	$json_object['total'];
	$destination_path=	$json_object['destination_path'];

	$wpdb->query( 
				$wpdb->prepare( 
				"	
					UPDATE mycbgenie_fresh_import_products_master
					SET last_used_throttle=%d  
					WHERE mycbgenie_import_id='%s'	
					"
				,
				$throttle_local,$throttle,$import_id
				)
				
		);	
		
				if ($wpdb->last_error) {
		  						$error_message= 
							" Error in updating  fresh_import_product_master TABLE  with last_used_throttle'" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );	}

	 
	 
	echo json_encode( array( 'import_id' 					=>	$import_id,
							 'destination_path'				=> 	$destination_path,	
							 'products_processed'			=>	$products_processed,
							 'error_message'				=>	$error_message,
							 'total'						=>	$total,
							 'skip_file_import'				=>	$skip_file_import,							 
							 'json_file_change_detected'	=>	$json_file_change_detected,
							 'last_used_throttle'			=>	$last_used_throttle,
							 'json_import_file_id'			=>	$import_file_id,
							 'last_success_import_file'		=>	$last_success_import_file,
							 'total_steps'			=> $total_steps	 )); 	 
	die();
}

function mycbgenie_cb_import_products_check_already_exists(){

	 if ( !isset($_POST['mycbgenie_nonce']) || !wp_verify_nonce( $_POST['mycbgenie_nonce'], "local_mycbgenie_nonce")) {
		 exit("No naughty business please");
	   } 
	   

	global $wpdb;


	$json_object	=	json_decode(mycbgenie_sideload_remote_JSON_file_id(),true);
	$import_file_id	=	$json_object['file_id'];


	 $sql="SELECT status_import,status_modified,mycbgenie_import_id,start_time,products_completed,batch_interval,last_used_throttle,json_import_file_id,screenshot_allowed FROM mycbgenie_fresh_import_products_master order by start_time desc";
				
	 $result = $wpdb->get_results( $sql );
	 
	 if ($wpdb->last_error) {
							$err_status='error';
							$error_message=
							" Error ... '" . $wpdb->last_error;	}
	 

	 $wpdb->flush();
	 
	 		 

	 
	 echo json_encode( array( 
	 					'status'					=>	$err_status,
						'error_message'				=>	$error_message,
	 					'already_imported' 			=>	count($result),	
	 					'modified_status'			=>	$result[0]->status_modified,  
						'import_status'				=> 	$result[0]->status_import,		
						'products_processed'		=>	$result[0]->products_completed,
						'batch_interval'			=>	$result[0]->batch_interval,
						'existing_import_file_id'	=>	$result[0]->json_import_file_id,	
						'remote_import_file_id'		=>	$import_file_id,	
						'screenshot_allowed'		=>	$result[0]->screenshot_allowed,
						'last_used_throttle'		=>	$result[0]->last_used_throttle
						));

	die();
}



function mycbgenie_ajax_cb_import_process_function() {


		// verify the nonce as part of security measures
   		if ( !isset($_POST['mycbgenie_nonce']) || !wp_verify_nonce( $_POST['mycbgenie_nonce'], "local_mycbgenie_nonce")) {
      		//exit("No naughty business please");
			
  	 	} 
	

		$step     					= 	absint( $_POST['step'] );
		$delete_or_resume     		=  	$_POST['delete_or_resume'] ;
		$import_id					=	$_POST['import_id'] ;
		$throttle_speed				=	$_POST['throttle_speed'] ;
		$products_processed			=	absint($_POST['products_processed']) ;
		$resume_first_step			=	$_POST['resume_first_step'];
		$batch_interval				=	absint($_POST['batch_interval']);
		$screenshot_allowed			=	$_POST['screenshot_allowed'];
		

		if (isset($resume_first_step) && ($resume_first_step=='Over')	){
		
		}
		else{
				if ($delete_or_resume=="resume"){
					
					if(intval($products_processed)==0)
						{	$steps_completed=0;	}
					else{
						$steps_completed	= intval($products_processed/	$throttle_speed);
							$step=	$steps_completed +1;	
					}
					
					//$diagonostics=$diagonostics."Step : ".$step;
		
				}
		}
		
//	echo 'step:'.$step;	

		if ($step==1 || $delete_or_resume=="resume") {
		
			//update woocommerce image dimensions
			//mycbgenie_update_woocommerce_image_dimensions();
			//update woo image dimensions
			mycbgenie_update_woocommerce_image_dimensions();


			/*if (get_option('mycbgenie_premium_store')){
					mycbgenie_mega_menu_category_import();
					mycbgenie_review_menu_import();
			}*/
		}
		
		 $total_steps				= 	$_POST['batches'];
		  $total					=	$_POST['total'];
		 $destination_path			=	$_POST['destination_path'].'/json_output_'.$step.'.txt';



		if ($step==$total_steps) {
				
			$modulus	=	$total % $throttle_speed;
			$error_message=mycbgenie_import_products($destination_path,'final_step',$import_id,$delete_or_resume,$throttle_speed,$modulus,$step,$products_processed, $batch_interval,$screenshot_allowed);
					
			echo json_encode( array( 'step' 			=> 'done', 
									 'batches'			=> $total_steps,
									 'total'			=>  $total,
									 'step_final'		=> $step,
									 'diagonostics'		=> $error_message,
									 'resume_first_step'	=>	'Over',
									 'percentage' 	=> 'Over' ) ); 
									 
			
		}
		
		else {
		

			$error_message	=	
				mycbgenie_import_products
				($destination_path,'not_final_step',$import_id,$delete_or_resume,$throttle_speed,0,$step,$products_processed, $batch_interval, $screenshot_allowed);
			
		
			$step += 1;
			echo json_encode( array( 'step' => $step, 
									'batches'	=> $total_steps,  
									'total'		=>  $total,
									'Diagonostics'	=> $error_message,
									'resume_first_step'	=>	'Over',
									'percentage' => intval( (($step-1)/$total_steps)*100) ) ); 
			
		}
		
		

		
		//sleep(1);
		die();
}



function mycbgenie_sideload_remote_JSON_file_id(){

			$url = 'https://cbproads.com/xmlfeed/woocommerce/zip/json/file_id.txt';

			//$url = $destination['path'].'/json_count.txt';
			$json = json_decode(mycbgenie_sf_file_get_contents_curl($url,0,null,null));
			
			
			foreach ($json as $key=>$value) {
				foreach ($value as $key => $val) { 
					if ($key=='file_id') { $file_id= $val;}
					}	
			}//for each
		//echo $file_id;
			return json_encode( array( 'file_id'	=> $file_id));
			
			
}
function mycbgenie_ajax_pre_import_activities_function(){

	global $wpdb;
	//wp_defer_term_counting( false );
	$wpdb->query( 'START TRANSACTION;' );
	$wpdb->query( 'SET autocommit = 0;' );

			$fresh=	$_POST['fresh'] ;
			$delete_or_resume=	$_POST['delete_or_resume'] ;
			
			
			$time_start = microtime(true); 		
			//stop all cron jobs
			mycbgenie_suspend_cron_jobs();
			
			// if delete or fresh install option, delete old entries
			if (($delete_or_resume=="delete") || ($delete_or_resume=="fresh") ){
				
					//update remote server about this fresh install
					mycbgenie_remote_update_fresh_install('started');
							
					$err_msg=mycbgenie_delete_all_entries('ALL','permanent');
							
					if ($err_msg){
						echo json_encode( 
						array( 'pre_status' => 'error', 
								'error_message'=>$err_msg));
						exit;
					}
			}
		
		
			if ($fresh=="fresh"){
					//Adding few product attributes
					mycbgenie_add_product_attributes();				
			}
		
			//Downloading the thumbnails for terms as a zip from remote location
			mycbgenie_sideload_remote_JSON_thumbnail_download_unzip();
			if ($fresh=="fresh"){
					mycbgenie_insert_main_category_term();
				mycbgenie_insert_sub_category_term();	
			}
			
						
			
			$time_end = microtime(true);
			$execution_time = ($time_end - $time_start);
	
	$wpdb->query( 'COMMIT;' );
	$wpdb->query( 'SET autocommit = 1;' );	
	
	$wpdb->flush(); 
		
	echo json_encode( array( 'pre_status'	=> "OK",
							'time_tk'	=>	$execution_time));
	die();
}


	function mycbgenie_review_menu_import(){
	    


	    if (get_option('mycbgenie_premium_store')){
             //echo  'premium.............OK';
        }else{
            return '';
        }
        $run_once = get_option('mycbgenie_review_menu_check');
        
        
         $menu_name = 'MyCBGenie Reviews Menu';
        //create the menu
        $menu_exists = wp_get_nav_menu_object($menu_name);
		
		if (    (!$run_once) ){ 

			if ($menu_exists) {
                        //wp_delete_nav_menu($menu_exists);
                       
			}else{  
            
            	    $category = get_term_by( 'slug', 'mycbgenie-product-reviews', 'category' );
	   
            	    $args = array(
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'parent' =>$category->term_id  //'cbpro-product-reviews'
                    );
                    $categories = get_categories($args);
                    
                    $menu_id = wp_create_nav_menu($menu_name);
            
                    
                    foreach ($categories as $cat) {
                        //echo $cat->name."<br>";
                        if (    stripos( $cat->name,"entertainment") >0 )  $icon_category="fa-film";
                        if (   $cat->name==="Business / Investing")  $icon_category="fa-briefcase";
                        if (  stripos( $cat->name,"food") >0 )  $icon_category="fa-cutlery";
                        if (  stripos( $cat->name,"-business") >0 )  $icon_category="fa-signal";
                        if ($cat->name==="Green Products")  $icon_category="fa-recycle";
                        if (  stripos( $cat->name,"fitness") >0 )  $icon_category="fa-heartbeat";
                        if ( stripos( $cat->name,"garden") >0 )  $icon_category="fa-home";
                        if ( stripos( $cat->name,"arenting") >0 )  $icon_category="fa-child";
                        if ( stripos( $cat->name,"elf-help") >0 ) $icon_category="fa-question";
                        if ( stripos( $cat->name,"belief") >0 )  $icon_category="fa-book";
                        
                         $top_menu= wp_update_nav_menu_item($menu_id, 0, array(
                                'menu-item-title' =>  __($cat->name),
                                'menu-item-classes' => '',
                                'menu-item-url' => get_category_link($cat->cat_ID) , 
                                'menu-item-status' => 'publish',
                                'menu-item-parent-id' => 0,
                                ));  
                        //echo $cat->name.'   '. $icon_category.'  '.$top_menu.'<br>';   
						
                         //this is applicable only if theme is Orchid
                         update_post_meta(  $top_menu, "menu-item-icon-field", $icon_category);
                         //end of oRchid pro theme     
            
                    }
                    

			}   //end of menu exists 
			update_option('mycbgenie_review_menu_check', true);
        }
            
		
        
    }
	
	
	if (function_exists('cs_cdata')) {}
    else{
        
        function  cs_cdata($data)
        {
            if (substr($data, 0, 9) === '<![CDATA[' && substr($data, -3) === ']]>') {
                $data = substr($data, 9, -3);
            }
            
            return $data;
        }
        
    }


	function mycbgenie_mega_menu_category_import(){
	    
	   
//	    $category_link = get_term_link("Health & Fitness-Mental Health", 'product_cat');
        if (get_option('mycbgenie_premium_store')){
             //echo  'premium.............OK';
        }else{
         //    echo  'premium.............OK';
            return '';
        }

        global $wp;
        $run_once = get_option('mycbgenie_menu_check');
        $curr_path=add_query_arg( $wp->query_vars, home_url( $wp->request ) );
        //$cat_page = cs_get_products_page('cs_category', '');
    
        
        if (    (!$run_once) ){         
            
                   	$empty_answer = 'Error in accessing XMl feed';
                   //give your menu a name
                    $menu_name = 'MyCBGenie Mega Menu for Categories';
                    //create the menu
                    $menu_exists = wp_get_nav_menu_object($menu_name);

                     if ($menu_exists) {
                         //wp_delete_nav_menu($menu_exists);
                       
                     }else{                     
                  
                         $menu_id = wp_create_nav_menu($menu_name);
                         
                   
                    
                                if ( is_wp_error($menu_id) ){
                            
                                     foreach ( $menu_id -> get_error_messages() as $error ) {
                                        echo 'Error is: '.$error;
                                     }      
                                }
                                
                        $url='https://cbproads.com/xmlfeed/wp/main/custom_categories_v5.asp';
                        $url=$url."?Dated-".date('Y-m-d');
                         
                		$rss = fetch_feed($url);
                		
                        if (is_wp_error($rss)) echo $empty_answer;
                        if (0 == $rss->get_item_quantity(400)) echo  $empty_answer;
                    
                        
                        $items = $rss->get_items(0, 400);
                        $cnt=0;

                 
                        foreach ($items as $item) {

                            //$cnt=$cnt+1;
                            $paths = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "mainhead");
                            $mainhead = htmlspecialchars(cs_cdata($paths[0]['data']));
                			
                			$paths = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "mainhead_name");
                            $mainhead_name = htmlspecialchars(cs_cdata($paths[0]['data']));
                                
                                   // echo $mainhead_name;
                                $top_menu= wp_update_nav_menu_item($menu_id, 0, array(
                                'menu-item-title' =>  __($mainhead_name),
                                'menu-item-classes' => '',
                                'menu-item-url' => '#' , 
                                'menu-item-status' => 'publish',
                                'menu-item-parent-id' => 0,
                                ));  
                                if ( is_wp_error($top_menu) ){
                            
                                     foreach ( $top_menu -> get_error_messages() as $error ) {
                                        echo 'Error is: '.$error;
                                     }      
                                }
                                
                                
                                //this is applicable only if theme is Orchid
                                $top_menu_label="";
                               if ($mainhead_name=='Wellness')  $top_menu_label="Hot";
                               if ($mainhead_name=='Make Money')  $top_menu_label="Popular";
                            
                                update_post_meta(  $top_menu, "menu-item-mega-menu-group-field", 1);
                               $the_post = array(      'ID'           => $top_menu,      'post_content' => $top_menu_label,  );
                                wp_update_post( $the_post );
                                //end of oRchid pro theme
                                
                                //getting second child details
            					$url2='https://cbproads.com/xmlfeed/wp/main/custom_categories_sub_v5.asp?test=test&main_cat_id='.$mainhead;
            					//echo "urlis.......................................................".$url2;
            					$rss2 = fetch_feed($url2);
            					if (is_wp_error($rss2)) return $empty_answer;
            					if (0 == $rss2->get_item_quantity(400)) return $empty_answer;
            					
            					$items_sc = $rss2->get_items(0, 400);
        
            					foreach ($items_sc as $item_sc) {
            					    //echo $url;
            					
            					    $paths = $item_sc->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "mccode");
                                    $mccode = htmlspecialchars(cs_cdata($paths[0]['data']));
                                    
                                    $paths = $item_sc->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "mcname");
                                    $mcname = htmlspecialchars(cs_cdata($paths[0]['data']));
                                    //$mcname=str_replace("&","%26",$mcname);
                                    //$surl=str_replace("?cs_category=","",$cat_page)."?cs_main_category=".$mccode."&cs_main_category_name=".urlencode($mcname);
                                 //   $surl = get_term_link($mainhead_name."-".$mcname, 'product_cat');
                                    $surl = get_term_link($mcname, 'product_cat');
                                        //echo "urlis.......................................................".$surl;
                                         $second_menu=wp_update_nav_menu_item($menu_id, 0, array(
                                        'menu-item-title' =>  __($mcname),
                                        'menu-item-classes' => '',
                                        'menu-item-url' => $surl, 
                                        'menu-item-status' => 'publish',
                                        'menu-item-parent-id' => $top_menu,
                                        ));  
                                        update_post_meta(  $second_menu, "menu-item-mega-menu-group-field", 1);
                                        echo $mcname."   - ".$second_menu."<br>";
                                        
                                     
                                                 //getting third child details
                            					$url3='https://cbproads.com/xmlfeed/wp/main/sub_categories.asp?test=test&main_cat_id='.$mccode;
                            					//echo "urlis.......................................................".$url3;
                            					$rss3 = fetch_feed($url3);
                            					if (is_wp_error($rss3)) return $empty_answer;
                            					if (0 == $rss3->get_item_quantity(400)) return $empty_answer;
                            					
                            					$items_tr = $rss3->get_items(0, 400);
                        
                            					foreach ($items_tr as $item_tr) {
                            					    //echo $url;
                            					
                            					    $paths = $item_tr->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "mainhead");
                                                    $mainheads = htmlspecialchars(cs_cdata($paths[0]['data']));
                                                    
                                                    $paths = $item_tr->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "subhead");
                                                    $sccode = htmlspecialchars(cs_cdata($paths[0]['data']));
                                                    
                                                    $paths = $item_tr->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "subhead_name");
                                                    $subhead_name = htmlspecialchars(cs_cdata($paths[0]['data']));
                                                    
                                                    //$turl=str_replace("?cs_category=","",$cat_page)."?cs_category=".$sccode."&cs_temp_main_category=".urlencode($mainheads);
                                                    $turl = get_term_link($mcname."-".$subhead_name, 'product_cat');
                                                   // echo "urlis.......................................................".$surl;
                                                         wp_update_nav_menu_item($menu_id, 0, array(
                                                        'menu-item-title' =>  __($subhead_name),
                                                        'menu-item-classes' => '',
                                                        'menu-item-url' => $turl, 
                                                        'menu-item-status' => 'publish',
                                                        'menu-item-parent-id' => $second_menu,
                                                        ));  
                                                    
                            					}// loop end of third level menu
                					
                					
            					} // loop end of second level menu
                            
                        } // loop end of top level menu
                     }    //if menu not exits 'mycbgenie categories'          
                
                    
                     update_option('mycbgenie_menu_check', true);
    
        }
        

	}
	

function mycbgenie_sideload_remote_JSON_download_unzip($throttle_speed,$process_type,$dest_dir){



   WP_Filesystem();
   $destination = wp_upload_dir();
   
   /* 	if ($process_type=="cron") {
   
   			//$zip_file	=	$destination['path'].'/cron_json.zip';
			$dest_dir	=	$destination['basedir'].'/mycbgenie/cron';
			//$url = 'http://clickbankproads.com/xmlfeed/woocommerce/zip/json/'.$throttle_speed.'/cron_json.zip';
			
  	    }elseif ($process_type=="manual"){
		
			//$zip_file	=	$destination['path'].'/manual_json.zip';
			$dest_dir	=	$destination['basedir'].'/mycbgenie/manual';
			//$url = 'http://clickbankproads.com/xmlfeed/woocommerce/zip/json/'.$throttle_speed.'/manual_json.zip';

		}else{
			//$zip_file	=	$destination['path'].'/fresh_json.zip';
			$dest_dir	=	$destination['basedir'].'/mycbgenie/fresh';
			//$url = 'http://clickbankproads.com/xmlfeed/woocommerce/zip/json/'.$throttle_speed.'/fresh_json.zip';

		}
		*/
		
		
		$zip_file	=	$destination['path'].'/json.zip';
		$url = 'https://cbproads.com/xmlfeed/woocommerce/zip/json/'.$throttle_speed.'/json.zip';



		
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
			  $error_message ="Error in unzipping the file ".   $zip_file;   
			  return json_encode (array  ('error_message'	=> $error_message));
		   }
		   
		   //echo $destination;
			

}

function mycbgenie_sideload_remote_JSON($throttle_speed,$skip_file_import,$json_file_change_detected,$process_type){


	    WP_Filesystem();
	    $destination = wp_upload_dir();

	   //getting total JSON batch files count	   
   		if ($process_type=="cron") {
			   $url 		= 	$destination['basedir'].'/mycbgenie/cron/json_count.txt';
			   $dest_path	=	$destination['basedir'].'/mycbgenie/cron';
			   
   	    }elseif ($process_type=="manual"){

			   $url 		= 	$destination['basedir'].'/mycbgenie/manual/json_count.txt';
			   $dest_path	=	$destination['basedir'].'/mycbgenie/manual';
			   
		}else{
			   $url 		= 	$destination['basedir'].'/mycbgenie/fresh/json_count.txt';
			   $dest_path	=	$destination['basedir'].'/mycbgenie/fresh';
		}


		
		

	   if ($skip_file_import== "no")
	   {

			mycbgenie_sideload_remote_JSON_download_unzip($throttle_speed,$process_type,$dest_path);
	   }else{
	   
	   	
		
	   		if (!file_exists($url)) {			

				if (	$json_file_change_detected	<>	"yes"	){

					mycbgenie_sideload_remote_JSON_download_unzip($throttle_speed,$process_type,$dest_path);
					
					}
				else{
				

						if($process_type=="manual"){
						
							mycbgenie_manual_sync_delete_function();
							//restart cron jobs
							mycbgenie_restart_cron_jobs();
							wp_die("The JSON file which is imported on earlier SYNC process, has been deleted by the user manually from /UPLOADS directory. 
							\n\nCannot Continue... Please refresh and try again.\n\n\n");
	
						}elseif ($process_type=="resume"){ 
						
							//restart cron jobs
							//mycbgenie_restart_cron_jobs();
							
							wp_die("The JSON file which is imported on earlier import process, has been deleted by the user manually from /UPLOADS directory. 
							\n\nCannot Continue... \n\nPlease try again and go for a FRESH installation by choosing for DELETING existing entries option..\n\n\n");

						
	
						}elseif ($process_type=="fresh"){ 
						
							wp_die("fresh error");
						
							//restart cron jobs
							//mycbgenie_restart_cron_jobs();
							//wp_die("The JSON file which is imported on earlier import process, has been deleted by the user manually from /UPLOADS directory. 
							//\n\nCannot Continue... \n\nPlease try again and go for a FRESH installation by choosing for DELETING existing entries option..\n\n\n");

						}
					}
			}
	   }// skipimport="no"
	   
	   
   
   

		

		
		$json = json_decode(file_get_contents($url,0,null,null));
			

		foreach ($json as $key=>$value) {
			foreach ($value as $key => $val) { 
					if ($key=='batches') { $total_steps= $val;}
					if ($key=='count') { $total= $val;}
			}	
		}//for each
		

			
		return json_encode( array( 'destination_path'	=> $dest_path,
										'total_steps' =>  $total_steps, 
										'total'=> $total , 
										'error_message'	=> $error_message	));
			
}


function mycbgenie_manual_sync_delete_function(){


	global $wpdb;
	$wpdb->query( 
					$wpdb->prepare( 
						"	
							DELETE FROM mycbgenie_manual_sync_products_master
							WHERE status_sync	<> 'success'"
						)
						
					);
					
	if ($wpdb->last_error) {
		  						$error_message= 
								" Error in deleting  manual_sync_product_master TABLE (functions_fresh_install_import.inc.php)'" . $wpdb->last_error;	
								$error->add( 'InsertError', $error_message );	
								echo 	$error_message;		
	}

}


function mycbgenie_after_import_update_term_count_action_function(){

	$process_type	=	$_POST['process_type'];

	$time_start = microtime(true);
		
	$update_taxonomy = 'product_cat';
		$get_terms_args = array(
			'taxonomy' => $update_taxonomy,
			'fields' => 'ids',
			'hide_empty' => false,
		);

	$update_terms = get_terms($get_terms_args);
	if (wp_update_term_count_now($update_terms, $update_taxonomy)){
	
	    $time_end = microtime(true);
    	$time = $time_end - $time_start;
    
	
			echo json_encode( array(
							'status'			=> 	"OK",
							'time_taken'		=>	"{$time}"	 )); 	
		}
	else{
	    $time_end = microtime(true);
    	$time = $time_end - $time_start;
		wp_die("Error in updating final term count..Process Time: {$time}");
	}

//restart cron jobs 
 	if ($process_type=="manual" ||  $process_type=="fresh"){
								
						mycbgenie_restart_cron_jobs();
	}
 
 	//update remover server about finish of fresh install
  	if ($process_type=="fresh"){

			mycbgenie_remote_update_fresh_install('finished');
	}

	die();
}
	

function mycbgenie_cb_fresh_import(){

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

global $wpdb;

				require_once(ABSPATH . 'wp-load.php');
				require_once(ABSPATH . 'wp-admin/includes/image.php');

				//directory to import to	
				$artDir = 'wp-content/uploads/mycbgenie/';

				//if the directory doesn't exist, create it	
				if(!file_exists(ABSPATH.$artDir)) {
					mkdir(ABSPATH.$artDir);
				}
						
		
				//directory to import to	
				$artDir = 'wp-content/uploads/mycbgenie/media/';

				//if the directory doesn't exist, create it	
				if(!file_exists(ABSPATH.$artDir)) {
					mkdir(ABSPATH.$artDir);
				}
				
				
				$artDir = 'wp-content/uploads/mycbgenie/media/thumbnails/';

				//if the directory doesn't exist, create it	
				if(!file_exists(ABSPATH.$artDir)) {
					mkdir(ABSPATH.$artDir);
				}			
				

				$artDir = 'wp-content/uploads/mycbgenie/media/thumbnails_extract/';

				//if the directory doesn't exist, create it	
				if(!file_exists(ABSPATH.$artDir)) {
					mkdir(ABSPATH.$artDir);
				}			
				
						
				$artDir = 'wp-content/uploads/mycbgenie/media/product_images/';

				//if the directory doesn't exist, create it	
				if(!file_exists(ABSPATH.$artDir)) {
					mkdir(ABSPATH.$artDir);
				}	
	
	$sql="SELECT `table_schema`  FROM information_schema.tables WHERE `table_name` = 'mycbgenie_fresh_import_products_master'";
	
	$result = $wpdb->get_results( $sql );
	$table_schema= $result[0]->table_schema;
	
				

	$sql="SELECT table_name, ENGINE FROM information_schema.tables WHERE (table_name LIKE '".$wpdb->prefix."post%' OR
		table_name LIKE '".$wpdb->prefix."term%'  OR table_name LIKE '".$wpdb->prefix."comment%') and ENGINE<>'InnoDB' and `table_schema`='".$table_schema."'";
	
	$result = $wpdb->get_results( $sql );
	
	if (count($result)>0 ){

			 //`wp_74zdpf5kgj_posts` ENGINE = InnoDB 
			 
			 foreach ($result as $res){
			 
			 	$sql = "ALTER TABLE  " . $res->table_name . " ENGINE = InnoDB;";
				$wpdb->query($sql);

				}
	}
	
	
	$sql="SELECT table_name, ENGINE FROM information_schema.tables WHERE (table_name LIKE '".$wpdb->prefix."post%' OR
		table_name LIKE '".$wpdb->prefix."term%' OR table_name LIKE '".$wpdb->prefix."comment%') and ENGINE<>'InnoDB' and `table_schema`='".$table_schema."'";
	
	$result = $wpdb->get_results( $sql );
	
	if (count($result)>0 ){
		echo '<div align=center style="margin-top:50px; padding:10px; border:2px dotted red;">
		<h3>Cannot Continue...</h3>The <B>STORAGE ENGINE</B> of the following TABLES of your database is not supported by us.
		<p>You need to contact your hosting people to change the <strong>STORAGE ENGINE</strong> of the following
		 MySQL tables to <strong>InnoDB</strong>. <br>If you have the access to <strong>phpMyAdmin</strong> on your cpanel, you can have a try.</p>';
		
		echo '<div align=center style="margin-top:20px; MARGIN-BOTTOM:30PX;"><table class="sample">';
		echo '<tr><th>Table Name</th><th>Storage Engine</th></tr>';
		foreach ($result as $res){
		
			echo '<tr><td>'.$res->table_name.'</td><td>'.$res->ENGINE.'</td></tr>';
	
		}
		
		echo '</table></div>';
		echo '</div>';
		exit;

	}
 


	//ignore the product page admin notice and hide for ever
	if ( isset($_GET['mycbgenie_import_help_ignore3']) && '0' == $_GET['mycbgenie_import_help_ignore3'] ) {
		
		add_user_meta(get_current_user_id(), 'mycbgenie_import_screen_dismiss_option', 'true', true);
	
	}
		
		
		
	//show the help admin notice again
	if ( isset($_GET['mycbgenie_import_help_show3']) && '0' == $_GET['mycbgenie_import_help_show3'] ) {
		
		if ( ! delete_user_meta(get_current_user_id(),'mycbgenie_import_screen_dismiss_option')) {
		 echo "Ooops! Error while deleting this information! for user id :".get_current_user_id();
		}
	
	}


	
echo '<div class="wrap" style="margin-top: 35px; border:0px dotted; padding:0px">'; 
	echo mycbgenie_header_files();
	
	


	
	if ( ! get_user_meta(get_current_user_id(), 'mycbgenie_import_screen_dismiss_option') ) {
	
			echo '<div class="message" style="background:lightyellow; border:1px dashed; margin:10px; padding:10px; -webkit-border-radius: 5px; -moz-border-radius: 5px;">';
			
	 		printf(__(			
			'<h3 style="text-decoration:none;">Fresh Import Vs Manual SYNC </h3>
			
			
						
			<P>A <strong>fresh import</strong> is required only once just after you activate the plugin.
			The process of importing all the products can take up to 1-4 hours depending upon the
			speed of your server. Please do not close the browser until you finish the process.
			In case if you are on a <strong>shared server</strong>, we suggest you NOT
			to select more than 50 products in a batch as the throttle value. If any
			error occurs, you may resume the import process, after selecting a lower throttle value than before.
			During the import process, all of the product details along with it\'s cover
			images are downloaded in to your server. There are around 5K-8K products available for import at Clickbank\'s marketplace.</p>
			
			
			<p>As the Clickbank update it\'s database daily, it is suggested that you too update your imported database regularly.
			
			Thanks to CRON job! CRON job would take care of this job automatically for you 
			at regular 
			intervals set by you on the <a target=_blank href="admin.php?page=mycbgenie_main_menu&tab=cron_tab_3">settings</a> page. 
			 In case if wish to manually update 
			the database for any reason, you may <strong>SYNC</strong> the database manually using the button shown below. 
			
			</p>
			
			<p>
			 You can also SYNC only a particular product from the <a target=_blank href="admin.php?page=mycbgenie_custom_products">Manage Products</a> page.  </p>
			
			
			<div align=right style="margin-top:15px"><a href="%1$s"><< Hide Help</a></div>')
			,'?page=mycbgenie_main_menu_import&mycbgenie_import_help_ignore3=0');
			
			echo "</div><br><br>";

	
	}
	else
	{
			printf(__(' <div align=right style="margin:7px;">
				<img width=30 height=30 src="'.plugins_url('images/help-icon.png', __FILE__ ).'"> 
				<a style="text-decoration:none;" href="%1$s">
				[ Show Help ]</a></div>') , '?page=mycbgenie_main_menu_import&mycbgenie_import_help_show3=0');
		
	}
	
	echo "<div style= 'border:1px solid #E6E6E6; background: #E6E6E6; padding:0px; padding-top:12px;  -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;' >";

	


		global $wpdb;


		$sql="SELECT * FROM mycbgenie_fresh_import_products_master
			WHERE ( status_import is null and status_modified is null) or ( status_import !='success' and  status_modified !='success' )  order by start_time desc";
		
		 $result_temp = $wpdb->get_results( $sql );
		
		 if (count($result_temp)>0){

			$btn_value	="Resume / Start Again";
		}else {
			$btn_value	="Import Products";
		}
		
		
				$sql="SELECT * FROM mycbgenie_manual_sync_products_master
			WHERE ( status_sync !='success' or status_sync is null )  order by start_time desc";
		
		 $result_temp = $wpdb->get_results( $sql );
		
		 if (count($result_temp)>0){

			$btn_sync_value	="Resume SYNC";
		}else {
			$btn_sync_value	="Start SYNC";
		}
	?>	



	
	
	<div  >
			<div style="padding-top:3%; padding-bottom:5%; padding-left:5%; padding-right:3%;">

			<div class="inside" style="padding:0px; margin:0px; border:1px solid #E6E6E6; overflow:auto; background:#EEEEEE; border-radius:7px;">
			
					
					<p style="
												text-shadow: rgb(224, 224, 224) 1px 1px 0px;  color: rgb(97, 97, 97);
												font-size: 21px;
												
												padding-left:20px;
												margin-bottom:20px;">
												<?php _e('Start Import Products ', 'sample'); ?>
					</p>

					<form action="POST" action="" id="mycbgenie_form_id_ajax">
						

					
									<div style=" width:92%; margin-left:20px;">
									
									<table width=90% cellpadding="5" cellspacing="5" border="0" 
									style="border-collapse:collapse;">
									
									<tr ><td style=" background:#dcdcdc">
										Throttle
										</td><td >:</td><td >
										<select style="margin-right:2px; margin-left:10px;" name="mycbgenie_throttle" id="mycbgenie_throttle">
										
											<!--<option value="5" > 5 </option>-->
											<option value="10" selected="selected"> 10 </option>							
											<option value="25" > 25 </option>
											<option value="50" > 50	</option>	
											<!--<option value="100" > 100</option>	
											<option value="200" > 200</option>	-->
							
										</select><span style="margin-left:5px;"> products in a batch</span>
									</td>
									<td style="border:0px; background:#EEEEEE" rowspan="3" align="right" valign="bottom">
										<input class="button-primary" style="margin-left:40%;" id='submit-btn' type=submit  Value="<?php echo $btn_value?>">
									</td>
									
									</tr>
									
									<tr><td colspan=3 align=center></td>									</tr>
									
									
									<tr ><td style=" background:#dcdcdc">	
										Replace blank images with the <strong>'screenshots'</strong> of the product website
									</td><td>:</td><td>
										<div><input type="checkbox" style="	margin-left:10px;" name="screenshot_allowed_import" id="screenshot_allowed_import"  
										checked="checked" checked value="yes" /></div>
											
									</td></tr>

</table>
									
									</div>

								
									<div style="margin-left:20px; ">
										<div id="progressbar_id" class="container" style="display:none;"><div class="progressbar"></div></div>
										<img src="<?php echo (admin_url('/images/wpspin_light.gif'));?>" class="waiting" id="aad_loading"  style="display:none; margin-top:15px;">
									
									</div>
								
							
									<div id="product_import_time" style="margin-top:10px; margin-left:20px; margin-bottom:10px;"></div>
									<div id="product_import_status" style="margin-top:10px; margin-left:20px; margin-bottom:10px; color:#FF00FF;"></div>
									<div id="product_import_final_status" style="margin-top:10px; margin-left:20px; margin-bottom:10px;"></div>
									<div id="" style="margin-top:10px; margin-left:20px; margin-bottom:10px; margin-right:30px; color:#CC0000; float:left;">
										
										<?php 
											$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );

											if ( $shop_page_url ) { 
												echo '<br><font color=black><strong>Tip:</strong></font><br>You may monitor the status of the products that are being imported into your <strong>
													<font color=darkgreen>Woo Store\'s Shop</font></strong> URL: ';
												echo '<a href="'.$shop_page_url.'" target="_blank">'.$shop_page_url.'</a><br>&nbsp;';
											}
											else{
											echo '<br><font color=black><strong>Tip:</strong></font>To monitor the status of the products that are being imported to your
											      Woo Store, <br>you may please select your desired <strong>Shop Page</strong> URL, from the 
												  dropdwon select box listed
													on the page: <br><strong><font color=darkgreen>WooCommerce -> Settings -> Products -> ';
											echo '<a href="?page=wc-settings&tab=products&section=display" target=_blank>Display </a></font></strong>';
											}
										?>

										
									</div>									
									<div>&nbsp;</div>
							
					
					</form>
					
		
				
					
				
				</div>
		</div>

		<div  style=" width:90%; padding-top:1%; padding-bottom:5%; padding-left:5%; padding-right:5%;"	>	
				
					
				
				<div class="inside" style="padding:0px; margin:0px; border:1px solid #E6E6E6; overflow:auto; background:#EEEEEE; border-radius:7px;">

										<p style="
												text-shadow: rgb(224, 224, 224) 1px 1px 0px;  color: rgb(97, 97, 97);
												font-size: 18px;
												padding-left:20px;
												margin-bottom:20px;">
												<?php _e('Manual SYNC', 'sample'); ?>
										</p>		
					
								<form action="POST" action="" id="mycbgenie_form_manual_sync_ajax">
					
							
							
							
					
								<div style=" margin-left:20px; width:90%; ">
									<table width=90% cellpadding="5" cellspacing="5" border="0" 
									style="border-collapse:collapse;">
									
									<tr ><td style=" background:#dcdcdc"> Throttle  
									</td><td >:</td><td >
									
									<select style="  margin-right:2px; margin-left:10px;" name="mycbgenie_sync_throttle" 
									id="mycbgenie_sync_throttle">
									<option value="5" > 5 </option>
									<option value="10" > 10 </option>
									<option value="25" selected="selected"> 25 </option>
									<option value="50" > 50	</option>	
									<option value="100" > 100</option>	
									<option value="200" > 200</option>	
									</select><span style="margin-left:5px;"> products in a batch</span>
									</td>
									<td style="border:0px; background:#EEEEEE" rowspan="3" align="right" valign="bottom">
									<input class="button-primary" style="margin-left:42%;" id='sync-submit-btn' type=submit  Value="<?php echo $btn_sync_value ?>">
								 </td>
									
									</tr>
									
									<tr><td colspan=3 align=center></td>									</tr>
									
									
									<tr ><td style=" background:#dcdcdc">	
										Replace blank images with the <strong>'screenshots'</strong> of the product website
									</td><td>:</td><td>
										<div><input type="checkbox" style="	margin-left:10px;" name="screenshot_allowed_sync" id="screenshot_allowed_sync"  
										checked="checked" checked /></div>
										
									</td></tr>

									</table>
							</div>
							
								
								
			
								<div  style="margin-left:20px;" >
									<div id="sync_progressbar_id" class="container" style="display:none;"><div class="progressbar"></div></div>
									<img src="<?php echo (admin_url('/images/wpspin_light.gif'));?>" 
									class="waiting" id="sync_aad_loading"  style="display:none; margin-top:15px;">
								
								</div>
								
								
								<div id="manual_sync_time" style="margin-top:10px;  margin-left:20px; margin-bottom:10px;"></div>
								<div id="manual_sync_status" style="margin-top:10px;  margin-left:20px; margin-bottom:10px; color:#0066FF;"></div>
								<div id="manual_sync_final_status" style="margin-top:10px;  margin-left:20px; margin-bottom:10px;"></div>
								<div>&nbsp;</div>
								</form>
					</div>		
						
		</div>
		
	</div>
	<br class="clearBoth" />
	<!--<div id="poststuff" class="ui-sortable meta-box-sortables">
	<div class="postbox">
	<div>

				

	</div>	
	</div>
	</div>-->
								<div>&nbsp;</div>
								<div>&nbsp;</div>
								<div>&nbsp;</div>

	
	</div>
	
</div>
	
	
<?php } ?>
