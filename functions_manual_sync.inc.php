<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



function mycbgenie_ajax_cb_manual_sync_function() {


		// verify the nonce as part of security measures
   		if ( !isset($_POST['mycbgenie_manual_sync_nonce']) || !wp_verify_nonce( $_POST['mycbgenie_manual_sync_nonce'], "local_mycbgenie_manual_sync_nonce")) {
      		exit("No naughty business please");
			
  	 	} 
		
	$current_throttle	= $_POST['throttle'];
		
	global $wpdb;


	$json_object	=	json_decode(mycbgenie_sideload_remote_JSON_file_id(),true);
	$import_file_id	=	$json_object['file_id'];


	 $sql="SELECT * FROM mycbgenie_fresh_import_products_master 
				 	WHERE ( status_import ='success' or status_modified ='success') order by start_time desc";
				
	 $result = $wpdb->get_results( $sql );
	 
	 $import_master_count	=	intval(	count($result)	);
	
	 $sql	="	SELECT * FROM mycbgenie_manual_sync_products_master 
	 			WHERE ( status_sync <> 'success' or status_sync is NULL) order by start_time desc  ";
				
	 $result = $wpdb->get_results( $sql );
	
	 $sync_master_count	=	intval(	count($result)	);
	 
	 if  ($sync_master_count==0) {
	 
	 //wp_die("error syn count". $sync_master_count . "sql ". $sql);
	 
	 		$sync_id=time();
			//$sync_id=9999900+rand(5,78);

			$skip_file_import=	"no";
			
			
				 $sql="SELECT * FROM mycbgenie_manual_sync_products_master
				 	WHERE ( status_sync ='success' )  order by start_time desc";
				
				 $result_temp = $wpdb->get_results( $sql );
				
				 if (count($result_temp)>0){
			
					$last_success_import_file	=	$result_temp[0]->json_import_file_id;	
					$last_used_throttle			=	$result_temp[0]->last_used_throttle;	
				 } 
			
			
			if( $last_success_import_file == $import_file_id &&  $current_throttle ==	$last_used_throttle ){
				$skip_file_import=	"yes";
			}

			
			$wpdb->query( 
							$wpdb->prepare( 
							"	
								INSERT INTO mycbgenie_manual_sync_products_master (mycbgenie_sync_id,json_import_file_id,last_used_throttle,start_time) 
								VALUES ('%s','%s',%d,'".date("Y-m-d H:i:s")."')"
							,
							$sync_id, $import_file_id, $current_throttle
							)
				);
	 }
	 else {
	 
	 
	 	  	$sql="SELECT (ID) FROM ". $wpdb->prefix."posts a INNER JOIN ". $wpdb->prefix."postmeta b
				ON ( a.ID = b.post_id ) WHERE 1=1 AND 
				b.meta_key = '_mycbgenie_sync_id' AND CAST(b.meta_value AS CHAR)  = '".$result[0]->mycbgenie_sync_id."' order by post_date DESC";
		
	
		
		
			$results = $wpdb->get_results( $sql );
			$products_processed =  count($results);
			$screenshot_allowed	= $result[0]->screenshot_allowed;


			$wpdb->query( 
				$wpdb->prepare( 
				"	
					UPDATE mycbgenie_manual_sync_products_master
					SET products_completed=%d 
					WHERE mycbgenie_sync_id='%s'	
					"
				,
				$products_processed,$result[0]->mycbgenie_sync_id
				)
				
			);	
		
		
			$import_existing_file_id		= 	$result[0]->json_import_file_id;
	 	 	$sync_id						=	$result[0]->mycbgenie_sync_id;
			$last_used_throttle				=	$result[0]->last_used_throttle;
		 	//$products_processed	=	$result[0]->products_completed;
			if 		($import_existing_file_id == $import_file_id && $current_throttle == $last_used_throttle  ){			
						$skip_file_import=	"yes";

			}elseif ($import_existing_file_id == $import_file_id && $current_throttle <> $last_used_throttle  ){			
						$skip_file_import=	"no";
			}else{						
					$skip_file_import=	"no"; 

					if ($import_existing_file_id== ' ' || empty ($import_existing_file_id) || is_null($import_existing_file_id)) {
					}
					else
					{
		
						// remote file ID is changed in the mean time after this process has started with another file ID.	
						//So, skipping and taking data only from  the local folder to which is already downloaded.		
						if($import_file_id	<> $import_existing_file_id){			
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

	 


	 if ($wpdb->last_error) {
							$err_status='error';
							$error_message=
							" Error ... '" . $wpdb->last_error;
							
							}
	 

	 $wpdb->flush();
	 
	
	echo json_encode( array( 
	
			 			'status'				=>	$err_status,
	 					'import_master_count'	=>	$import_master_count ,
						'error_message'			=>	$error_message,
						'products_processed'	=>	$products_processed,
						'sync_master_count'		=>	$sync_master_count,
						'throttle_changed'				=>	$throttle_changed,
						'throttle_old_value'				=>	$throttle_old_value,						
						'json_import_file_id'			=>	$import_file_id,
						'json_file_change_detected'		=>	$json_file_change_detected,
						'last_used_throttle'			=>	$last_used_throttle,
						'last_success_import_file'		=>	$last_success_import_file,
						'jsonimport_existing_file_id'	=>	$import_existing_file_id,
						'skip_file_import'				=>	$skip_file_import,
						'screenshot_allowed'			=>	$screenshot_allowed,
						'sync_id'						=>	$sync_id
						
					));


	
	die();


}

function mycbgenie_ajax_cb_manual_sync_delete_function(){

	// verify the nonce as part of security measures
	if ( !isset($_POST['mycbgenie_manual_sync_nonce']) || 
		!wp_verify_nonce( $_POST['mycbgenie_manual_sync_nonce'], "local_mycbgenie_manual_sync_nonce")) 
	{
		exit("No naughty business please");
		
	} 
	
	global $wpdb;
	
	$sync_id	=	$_POST['sync_id'];
	
	$wpdb->query( 
					$wpdb->prepare( 
						"	
							DELETE FROM mycbgenie_manual_sync_products_master
							WHERE mycbgenie_sync_id='%s'	
							"
						,
						$sync_id
						)
						
					);
					
	if ($wpdb->last_error) {
		  						$error_message= "Sync ID : ". $sync_id. " Step : ".$step.
								" Error in deleting  manual_sync_product_master TABLE '" . $wpdb->last_error;	
								$error->add( 'InsertError', $error_message );	
							echo json_encode( array(
								'status'			=> 	"Error"	 )); 			
	}
	echo json_encode( array(
							'status'			=> 	"OK"	 )); 	 

	die();
}

function mycbgenie_manual_sync_fetch_json_files(){

	// verify the nonce as part of security measures
	if ( !isset($_POST['mycbgenie_manual_sync_nonce']) || 
		!wp_verify_nonce( $_POST['mycbgenie_manual_sync_nonce'], "local_mycbgenie_manual_sync_nonce")) 
	{
		exit("No naughty business please");
		
	} 

		


	//import the JSON zip file from REMOTE server to local server and UNZIPP
	$json_file_change_detected		=	$_POST['json_file_change_detected'];
	$json_import_file_id			=	$_POST['json_import_file_id'];
	$skip_file_import				=	$_POST['skip_file_import'];
	$json_existing_import_file_id	=	$_POST['json_existing_import_file_id'];
			
	$throttle_speed	=	$_POST['throttle_speed'];
	$json_object	=	json_decode(mycbgenie_sideload_remote_JSON($throttle_speed,$skip_file_import,$json_file_change_detected,"manual"),true);
	$total_steps	=	$json_object['total_steps'];
	$total			=	$json_object['total'];
	$destination_path=	$json_object['destination_path'];



	echo json_encode( array(
							// 'sync_id' 				=> $sync_id,
							 'destination_path'		=> $destination_path,	
							// 'products_processed'	=> $products_processed,
							 'error_message'		=>	$error_message,
							 'total'				=>	$total,
							 'total_steps'			=> 	$total_steps	 )); 	 

	die();
}

function mycbgenie_manual_sync_process_action_function(){

	// verify the nonce as part of security measures
	if ( !isset($_POST['mycbgenie_manual_sync_nonce']) || 
		!wp_verify_nonce( $_POST['mycbgenie_manual_sync_nonce'], "local_mycbgenie_manual_sync_nonce")) 
	{
		exit("No naughty business please");
		
	} 
	
	$sync_id					=	$_POST['sync_id'];
	$throttle_speed				=	$_POST['throttle_speed'];
	$step     					= 	absint( $_POST['step'] );
	$total_steps				= 	$_POST['batches'];
	$total						=	$_POST['total'];
	$products_processed			=	absint($_POST['products_processed']) ;
	$resume_first_step			=	$_POST['resume_first_step'];
	$screenshot_allowed			=	$_POST['screenshot_allowed'];
	$destination_path			=	$_POST['destination_path'].'/json_output_'.$step.'.txt';

/*
$throttle_speed	=100;
$step=1;
$total_steps=95;
$total=9420;
$products_processed=0;
$sync_id='1446458639';
$destination_path='/home3/mycbgenie/public_html/wp-content/uploads/2015/11/json_output_'.$step.'.txt';
echo $destination_path;
*/

/*
	if ($step==1) {
	


		//add any missing product attributes
		//mycbgenie_add_product_attributes();

		//stop all cron jobs
		mycbgenie_suspend_cron_jobs();

		//Downloading the thumbnails for terms as a zip from remote location
		mycbgenie_sideload_remote_JSON_thumbnail_download_unzip();
		mycbgenie_insert_main_category_term();
		mycbgenie_insert_sub_category_term();	
	}
*/
	

	//This means that the first call is made from AJAX manual coding, not from by iterations inside AJAX loop recursively
	//We have set $resume_first_step=='Over' for all recusrisve calls from AJAX. to identify it.
	if (isset($resume_first_step) && ($resume_first_step=='Over')	){
		
				//$products_processed=0;
						//$products_processed_test1=124;

		//wp_die("dd".$products_processed);
		$resume_first_step="Over";

	}
	else{
		if (intval($products_processed)>0) 
			{
			$steps_completed	= 	intval($products_processed/	$throttle_speed);
			
				$step				=	$steps_completed +1;
				$destination_path			=	$_POST['destination_path'].'/json_output_'.$step.'.txt';
			}
		elseif(intval($products_processed)==0)	
			{	$steps_completed=0;	

			}
	}	
	
	if  ( $step <>1 ) {
		$products_processed=0;
	}
	
	if (isset($resume_first_step)) {
			$products_processed=0;
	}else{
			$products_processed=absint($_POST['products_processed']) ;
	}



//if ($step >=7){
//wp_die("error.. products_processed". $products_processed." omit ". $to_be_omitted_count." step ". $step);
//}



	if ($step==$total_steps) {


			$modulus	=	$total % $throttle_speed;

			$error_message	=	mycbgenie_sync_products	($destination_path,'final_step',$sync_id,
								$throttle_speed,$modulus,$step,$products_processed,'manual-sync', $screenshot_allowed);

			echo json_encode( array( 'step' 			=> 'done', 
									 'batches'			=> $total_steps,
									 'total'			=>  $total,
									 'step_final'		=> $step,
									 'diagonostics'		=> $error_message,
									 'resume_first_step'	=>	'Over',
									 'percentage' 	=> 'Over' ) ); }


	else {
	
	//wp_die("err".$step."\n syncid:". $sync_id." pp: ". $products_processed." Desti:". $destination_path);
	
			$error_message	=	mycbgenie_sync_products	($destination_path,$final_step,$sync_id,$throttle_speed,
								$modulus,$step,$products_processed,'manual-sync', $screenshot_allowed);

			$step += 1;
			echo json_encode( array( 'step' => $step, 
									'batches'	=> $total_steps,  
									'total'		=>  $total,
									'Diagonostics'	=> $error_message,
									'resume_first_step'	=>	'Over',
									'percentage' => intval( (($step-1)/$total_steps)*100) ) ); 
			
	}
		

	die();
}
/*
function test123(){
$url='http://www.abc.com';
$response = wp_safe_remote_get( $url, array(
	'method' => 'GET',
	'timeout' => 45,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'headers' => array(),
	'body' => array( 'username' => 'bob', 'password' => '1234xyz' ),
	'cookies' => array()
    )
);

if ( is_wp_error( $response ) ) {
   $error_message = $response->get_error_message();
   echo "Something went wrong: $error_message";
} else {
   echo 'Response:<pre>';
   print_r( $response );
   echo '</pre>';
}

}


function mycbgenie_manual_sync_products1(){

//$destination_path='/home3/mycbgenie/public_html/wp-content/uploads/2015/11/json_output_41.txt';
$destination_path='C:\wamp\www\mycbgenie_wp/wp-content/uploads/2016/06/json_output_3.txt';
$sync_id='1466449494';
$throttle_speed=25;
$modulus=0;
$step=6;
$products_processed=50;
mycbgenie_sync_products	($destination_path,$final_step,$sync_id,$throttle_speed,$modulus,$step,$products_processed,'manual-sync');
}

*/

function mycbgenie_sync_products	($destination_path,$final_step,$sync_id,$throttle_speed,$modulus,$step,$products_processed,$sync_type, $screenshot_allowed){


$url=$destination_path;

global $wpdb;


//wp_defer_term_counting( true );
$wpdb->query( 'START TRANSACTION;' );
//$wpdb->query( 'SET autocommit = 0;' );
wp_suspend_cache_addition(true);
set_time_limit(0);


		$mycbgenie_custom_edit_products_array=array();
		$mycbgenie_custom_edit_products_array=get_option('mycbgenie_custom_edited_products');
		


		$error = new WP_Error();





		$json = json_decode(file_get_contents($url,0,null,null));
		
		if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
		   
		}

		$temp_count=0;
		$product_master_table_minus=0;
		//$batch_interval=intval($batch_interval);


		
		
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
				if ($key=='physical') { $physical= $val;}
				if ($key=='gravity') { $gravity= $val;}
				if ($key=='keywords') { $keywords= $val;}
				if ($key=='maincat') { $maincat= $val;}
				if ($key=='subcat') { $subcat= $val;}
				if ($key=='descr')  { $descr= $val;}
				if ($key=='rating')  { $rating= $val;}
				if ($key=='last_image_updated')  { $last_image_updated= $val;}


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


		
 
 

	 							
	 							
	 							
	 							
	 	$result=0;		
	 	$thumbnail_id='';
	  	$sql="SELECT (ID) FROM ". $wpdb->prefix."posts a INNER JOIN ". $wpdb->prefix."postmeta b
				ON ( a.ID = b.post_id ) WHERE 1=1 AND 
				b.meta_key = '_mycbgenie_id' AND CAST(b.meta_value AS CHAR)  = '".$mycbgenie_id."' order by post_date DESC";
		
	
		
		
		$results = $wpdb->get_results( $sql );
		//var_dump($results).'<br>';
		
		$post_id	=	(	$results[0]->ID	);


		//updating the product whethet the product is reviewd or not based upon categories posts (_mycbgenie_id_code)
		$result=0;
	    $sql="SELECT (ID) FROM ". $wpdb->prefix."posts a INNER JOIN ". $wpdb->prefix."postmeta b
			    	ON ( a.ID = b.post_id ) WHERE 1=1 AND 
			    	b.meta_key = '_mycbgenie_id_code' AND CAST(b.meta_value AS CHAR)  = '".$mycbgenie_id."' order by post_date DESC";


		//$results = $wpdb->get_results( $sql );
		$result_rowcount = $wpdb->get_var($sql);

		
		if ( $result_rowcount==0){
		    
		    $mycbgenie_reviewed="no";
	 	    
		}else{ 
		    $mycbgenie_reviewed="yes";   
		    
		}
		update_post_meta( $post_id, '_mycbgenie_reviewed', $mycbgenie_reviewed );
	 	//end of reviewed check
	 							
	
	if (count($results)==0){
	
		//	echo 'test12'.$sql.$post_id;

	
		if($sync_type=='manual-sync') {

					$wpdb->query( 
					$wpdb->prepare( 
						"	
							UPDATE mycbgenie_manual_sync_products_master
							SET products_added=products_added+1 
							WHERE mycbgenie_sync_id='%s'	
							"
						,
						$sync_id
						)
						
					);			
							if ($wpdb->last_error) {
							$error_message= "Sync ID : ". $sync_id. " Step : ".$step.
							"Error in updating field products_added of manual_sync_product_master TABLE'" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );}
		}
		else{
		
					$wpdb->query( 
					$wpdb->prepare( 
						"	
							UPDATE mycbgenie_cron_sync_master
							SET products_added=products_added+1 
							WHERE mycbgenie_cron_id='%s'	
							"
						,
						$sync_id
						)
						
					);			
							if ($wpdb->last_error) {
							$error_message= "Cron ID : ". $sync_id. " Step : ".$step.
							"Error in updating field products_added of mycbgenie_cron_sync_master TABLE'" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );}
		
		}					
		
			
			
			
		$json_object_array	=	json_decode (mycbgenie_insert_new_product
						($mycbgenie_id,$title,$descr,$mdescr,$rank,$gravity,$price,$image_name,$images,$maincat,$subcat,$keywords,$sync_id,$rating,$last_image_updated, $screenshot_allowed,$physical),true);


			$post_id		=	$json_object_array['post'];
			//$error_msg		=	$json_object.error_msg;
		
		if($json_object_array['error_msg'] != '') {
						$error->add( 'InsertError', $json_object_array['error_msg'] );	
		}	
						
						
	}
	else{	 
	

	
				if ( in_array($mycbgenie_id, $mycbgenie_custom_edit_products_array, true) ) {
			
			 		$rows = $wpdb->get_results( $wpdb->prepare
					("SELECT mycbgenie_id,thumbnail_id FROM mycbgenie_custom_edited_products WHERE mycbgenie_id=%s LIMIT 1", $mycbgenie_id));

						if(empty($rows)){	
							$result=0;
						}
						else{
							$result=1;
			
							foreach ($rows as $row) { $thumbnail_id= $row->thumbnail_id;	}
						}
				}	
		
		 		if ($result==0){
			 
			 		$permalink = sanitize_title($title);

					$post_update = array(
					  'ID'           	=> $post_id,
					  'post_title'   	=> $title,
					  'post_content' 	=> $mdescr,
					  'post_excerpt'	=> $descr,
					  'post_name'		=> $permalink,
					  'post_status' 	=> "publish"
					);
			   }
	   		   else{
			 
					$post_update = array(
					  'ID'           	=> $post_id,
					//  'post_title'   	=> $title,
					  //'post_content' 	=> $mdescr,
					  //'post_excerpt'	=> $descr,
					  'post_status' 	=> "publish"
					);
			   }
		

				// Update the post into the database


  			   $post_id_tmp=wp_update_post( $post_update );
			    if(  is_wp_error( $post_id_tmp ) ) {
		
								$error_message= "Post Title : ". $title. " ID : ".$mycbgenie_id.
								" Error in wp_update_post...".$post_id->get_error_message();
								$error->add( 'InsertError', $error_message ); }
		
							  //setting rating
	
				mycbgenie_update_rating($post_id,$rating);
		
	

				if ($thumbnail_id	=='' || empty($thumbnail_id)) {
				
						$existing_image_last_updated_array	=	get_post_meta($post_id,'_mycbgenie_last_image_updated');
						$prev_screenshot_allowed			=	get_post_meta($post_id,'_mycbgenie_screenshot_allowed');
						
						if ($prev_screenshot_allowed ==NULL || empty($prev_screenshot_allowed) ) 	$prev_screenshot_allowed="yes";
						
						//$meta_image_name	=	get_post_meta($post_id,'_mycbgenie_image_url');
						

						
						$feed_image_last_updated = date_create($last_image_updated);
						$existing_image_last_updated = date_create($existing_image_last_updated_array[0]);
		
						//if ( 	($image_name != $meta_image_name[0])){
						
						
						$diff=date_diff($feed_image_last_updated, $existing_image_last_updated )	;
		
						if  ( 
								(	 $diff->format("%R%a") < 0	 ) 
								|| 
								(	
										($screenshot_allowed !== $prev_screenshot_allowed)
										&& 
										( $image_name === "blank.gif")
								)
							)
						{

									$msg="feed last updataed: " .$last_image_updated." <br>\n" ;				
									$msg.="existing last updataed: ". $existing_image_last_updated_array[0] ." <br>\n" ;						
									$msg.="image_name: ". $image_name ." <br>\n" ;						
									$msg.="post_id: ". $post_id ." <br>\n" ;		
									$msg.="_mycbgenie_image_url: ". $_mycbgenie_image_url ." <br>\n" ;	
									$msg.="images: ". $images ." <br>\n" ;	
									$msg.="mycbgenie_id: ". $mycbgenie_id ." <br>\n" ;		
																			
									//mail("go4buck@yahoo.com","My subject",$msg);	
		
		
							update_post_meta( $post_id, '_mycbgenie_image_url', $image_name );
							
							//update update date only if update in feed URL.
							if (	$diff->format("%R%a")  < 0	 )  
										update_post_meta( $post_id, '_mycbgenie_last_image_updated', $last_image_updated );
										
							update_post_meta( $post_id, '_mycbgenie_screenshot_allowed', $screenshot_allowed );							

		//$screenshot_allowed="yes";
						  	
							
							$image_error=  mycbgenie_fetch_product_images($images,$mycbgenie_id, $image_name, $post_id, $altimage , $screenshot_allowed , "sync");

							//$image_error=mycbgenie_update_thumbnail( $images,$post_id);
							if($image_error != '') {$error->add( 'InsertError', $image_error );	}
						}
		
				}	
				else {
					//skip image updatation as have already custim edited this image.
				}
		
		
		
				if ($result==0){	
				
					//update only if not listed in CUSTOM EDITED TABLE
					update_post_meta( $post_id, '_price', $price );		

					
				}
				
					

				$term_taxonomy_ids	=	mycbgenie_update_terms_and_tags	($post_id,$keywords,$maincat,$subcat);
				if ( is_wp_error( $term_taxonomy_ids ) ) {
									
							$error->add( 'InsertError', 'Error in updating product tags/terms' );
							// There was an error somewhere and the terms couldn't be set.
				}
				
	
				//set_post_thumbnail($custom_edit_id, $thumbid);
		  
				//wp_set_object_terms ($post_id, 'external', 'product_type');
		
				update_post_meta( $post_id,'_mycbgenie_rank', $rank);
				update_post_meta( $post_id,'_mycbgenie_gravity', $gravity);
		
				
		
				$target_url	=	"?action=mycbgenie_store_view&id=".$mycbgenie_id;
				update_post_meta( $post_id, '_product_url', $target_url );
			
							 
				update_post_meta( $post_id,'_mycbgenie_last_sync', date('m/d/Y h:i:s a', time()));
				update_post_meta( $post_id,'_mycbgenie_sync_id', $sync_id);
				if ($physical=="yes") {
					update_post_meta( $post_id,'_mycbgenie_physical_product', "yes");
				}else{
				    update_post_meta( $post_id,'_mycbgenie_physical_product', "no");
				}
                				
			    //setting featired products
			    if (($rank<=10) && ($gravity>40) ){
					wp_set_object_terms( $post_id, 'featured', 'product_visibility');
   			    }
				wp_set_object_terms ($post_id, 'external', 'product_type');

	}//end if of count=0


}    // end of foreach
			
			
	if	    ( $modulus ==0) { $throttle_local= $throttle_speed;	}
	else	{ $throttle_local= $modulus;	}
	


	$already_processed=$products_processed;
	
	if ($already_processed==0){
		$to_be_omitted_count=0;
	}
	if ($already_processed>0){
		$to_be_omitted_count=	intval(		($already_processed )- (	($step-1)* $throttle_speed	)			); 
	}
			 

	//echo "to be products_processed : " .$products_processed;			 
	//echo "to be omo : " .$to_be_omitted_count;



	if($sync_type=='manual-sync') {
			 
		$wpdb->query( 
				$wpdb->prepare( 
				"	
					UPDATE mycbgenie_manual_sync_products_master
					SET products_completed=products_completed-$to_be_omitted_count+%d,
					last_used_throttle=%d , screenshot_allowed = '%s' 
						WHERE mycbgenie_sync_id='%s'	
					"
				,
				$throttle_local,$throttle_speed,$screenshot_allowed,$sync_id
				)
				
		);	
		
				if ($wpdb->last_error) {
		  						$error_message= "Sync ID : ". $sync_id. " Step : ".$step.
								" Error in updating  manual_sync_product_master TABLE '" . $wpdb->last_error;	
								$error->add( 'InsertError', $error_message );		}
	}else{ //else of sync type
	
		$wpdb->query( 
				$wpdb->prepare( 
				"	
					UPDATE mycbgenie_cron_sync_master
					SET products_completed=products_completed-$to_be_omitted_count+%d, 
					last_used_throttle=%d, 
					    steps_completed=$step,last_batch_process_time='".date("Y-m-d H:i:s")."'  
					WHERE mycbgenie_cron_id='%s'	
					"
				,
				$throttle_local,$throttle_speed,$sync_id
				)
				
		);	
		
				if ($wpdb->last_error) {
		  						$error_message= "Cron ID : ". $sync_id. " Step : ".$step.
								" Error in updating  mycbgenie_cron_sync_master TABLE '" . $wpdb->last_error;	
								$error->add( 'InsertError', $error_message );		}
	
	}//end of sync type


		if(	$final_step=='final_step'){
		
		
			
			$sql="SELECT ". $wpdb->prefix."posts.ID FROM ".$wpdb->prefix."posts INNER JOIN ".$wpdb->prefix."postmeta ON 
			( ".$wpdb->prefix."posts.ID = ".$wpdb->prefix."postmeta.post_id ) INNER JOIN ".$wpdb->prefix."postmeta
			 AS mt1 ON ( ".$wpdb->prefix."posts.ID = mt1.post_id ) WHERE 1=1 AND ( 
			 ( ".$wpdb->prefix."postmeta.meta_key = '_mycbgenie_managed_by' 
			AND CAST(".$wpdb->prefix."postmeta.meta_value AS CHAR) = 'mycbgenie' ) AND 
			( mt1.meta_key = '_mycbgenie_sync_id' AND CAST(mt1.meta_value AS CHAR)
			 != '".$sync_id."' ) ) AND ".$wpdb->prefix."posts.post_type = 'product' AND 
			 (".$wpdb->prefix."posts.post_status = 'publish' OR ".$wpdb->prefix."posts.post_status = 
			 'future' OR ".$wpdb->prefix."posts.post_status = 'draft' OR ".$wpdb->prefix."posts.post_status 
			 = 'pending' OR ".$wpdb->prefix."posts.post_status = 'private') GROUP BY ".$wpdb->prefix."posts.ID
			  ORDER BY ".$wpdb->prefix."posts.post_date DESC";

		  
		  
			$result = $wpdb->get_results( $sql );
	
			$tmp_push=array();

			foreach ($result as $res){
			
								array_push($tmp_push, $res->ID);

			}
		
			$products_removed=count($tmp_push);
			

			
			if ($products_removed>0 ){
			

			
				mycbgenie_delete_all_entries(implode("**",$tmp_push),'temp');
			}
			//$sql = "UPDATE ".$wpdb->prefix."posts  SET post_status='private'  WHERE ID in (".implode(",",$tmp_push).")";

			
			//$wpdb->query( $sql);	
		
		if($sync_type=='manual-sync') {

					$wpdb->query( 
					$wpdb->prepare( 
						"	
							UPDATE mycbgenie_manual_sync_products_master
							SET status_sync='success' , end_time='".date("Y-m-d H:i:s")."',
							products_removed=$products_removed, screenshot_allowed = '%s' 
							WHERE mycbgenie_sync_id='%s'	
							"
						,
						$screenshot_allowed,$sync_id
						)
						
					);			
							if ($wpdb->last_error) {
							$error_message= "Sync ID : ". $sync_id. " Step : ".$step.
							"Error in updating in final status of manual_sync_product_master TABLE'" . $wpdb->last_error;	
							$error->add( 'InsertError', $error_message );}
							
							

		}
		else {	// synctype=manual
		
					$wpdb->query( 
					$wpdb->prepare( 
						"	
							UPDATE mycbgenie_cron_sync_master
							SET status_cron='success' ,last_batch_process_time='".date("Y-m-d H:i:s")."',
							 end_time='".date("Y-m-d H:i:s")."',products_removed=$products_removed 
							WHERE mycbgenie_cron_id='%s'	
							"
						,
						$sync_id
						)
						
					);	
		
		}
		

		
	} //final step
		

	$error_message='';
	
	if ( 1 > count( $error->get_error_messages() ) ) {
	
		//reverse woocommerce image dimensions only for sync_type='manual' // No need if sync_type='CRON'
		if(	$final_step=='final_step'){
		
			if($sync_type=='manual-sync') {
				mycbgenie_reverse_woocommerce_image_dimensions();
			}
		}
		if($sync_type=='cron-job') {
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
									

	//$wpdb->query( 'ROLLBACK;' );								
	$wpdb->query( 'COMMIT;' );
	//$wpdb->query( 'SET autocommit = 1;' );	
	
	
	//$wpdb->query( 'UNLOCK TABLES;' ); 
	//wp_defer_comment_counting( false );  
	
	$wpdb->flush();   
	

}
					
					

?>