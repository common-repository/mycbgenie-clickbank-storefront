<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


function mycbgenie_cron_job_process(){




global $wpdb;

	 $sql="SELECT status_import,mycbgenie_import_id,start_time,products_completed FROM mycbgenie_fresh_import_products_master 
				 	order by start_time desc";
				
	 $result = $wpdb->get_results( $sql );
	 $master_count=	intval(	count($result)	);
	 
	 if ($master_count==0){
	 	//have not yet imported. Exit
		 return;
		 exit;
	 }
	 


	//check if FRESH Instatalttion is running and yet to complete. If value return ==0 means FRESH INSTALL ins running
	 $sql="SELECT * FROM mycbgenie_fresh_import_products_master 
				 	WHERE ( status_import = 'success' OR status_modified = 'success') order by start_time desc";
			
					
	 $result = $wpdb->get_results( $sql );
	 $master_count=	intval(	count($result)	);
	 
	 if ($master_count==0){
	 	//have not yet imported succesffully. Exit
		 return;
		 exit;
	 }

	
	
	//check if any SYNC process is running.. If value return >0 means SYNC is running
	$sql	="	SELECT * FROM mycbgenie_manual_sync_products_master 
	 			WHERE ( status_sync <> 'success' or status_sync is NULL) order by start_time desc  ";

				
	$result = $wpdb->get_results( $sql );
	
	$sync_master_count	=	intval(	count($result)	);
	
	if ($sync_master_count>0){
	 	//have not completed SYNC. Exit
		 return;
		 exit;
	 }
	 	
	 
	 //check to get last saved option for screenshot allowed from fresh_import_master
	$sql	="	SELECT screenshot_allowed,end_time FROM mycbgenie_fresh_import_products_master 
	 			 order by end_time desc  limit 1 ";

	$result = $wpdb->get_results( $sql );
	
	$date1 = ($result[0]->end_time);
	$ss_allowed1 = $result[0]->screenshot_allowed;

 
	 //check to get last saved option for screenshot allowed from sync_master
	$sql	="	SELECT screenshot_allowed,end_time FROM mycbgenie_manual_sync_products_master 
	 			order by end_time desc  limit 1";
	
	$result = $wpdb->get_results( $sql );
	$date2 = ($result[0]->end_time);
	$ss_allowed2 = $result[0]->screenshot_allowed;
	
	if ($date1 > $date2) {
						$screenshot_allowed = $ss_allowed1;
	}
	else{
						$screenshot_allowed = $ss_allowed2;
	}
	
	if ( is_null($screenshot_allowed) || ($screenshot_allowed === NULL)  ) $screenshot_allowed="yes";
	//echo 'dd';
	//var_dump( $screenshot_allowed);
	//exit;
	
	
	
	$sql	="	SELECT * FROM mycbgenie_cron_sync_master 
	 			WHERE ( status_cron <> 'success' or status_cron is NULL) order by start_time desc  ";
		
	 $result = $wpdb->get_results( $sql );
	
	 $cron_master_count	=	intval(	count($result)	);

	 if  ($cron_master_count==0) {


	 		$throttle_speed	= get_option('mycbgenie_cron_per_batch_no_products',25);
			
			if (empty($throttle_speed))	{	$throttle_speed=10;}

			$skip_file_import	=	"no";
			$json_change_detected=	"yes";
			$process_type	=	"cron";

	  		$cron_id=time();
		 	 		

	
			$json_object		=	json_decode(mycbgenie_sideload_remote_JSON($throttle_speed,$skip_file_import,$json_change_detected,$process_type),true);
			$total_steps		=	$json_object['total_steps'];
			$total_products		=	$json_object['total'];
			$destination_path	=	$json_object['destination_path'];
			$products_processed	=	0;
			

	$wpdb->query( 'START TRANSACTION;' );
	$wpdb->query( 'SET autocommit = 0;' );
		
			$wpdb->query( 
				$wpdb->prepare( 
				"	
				INSERT INTO mycbgenie_cron_sync_master (mycbgenie_cron_id,start_time,total_products,total_steps,json_path,throttle_speed,steps_completed) 
				VALUES ('%s','".date("Y-m-d H:i:s")."',%d,%d,'%s',%d,-1)"
				,
				$cron_id,$total_products,$total_steps,$destination_path,$throttle_speed
				)
			);
			
			//Downloading the thumbnails for terms as a zip from remote location
			mycbgenie_sideload_remote_JSON_thumbnail_download_unzip();
			//mycbgenie_insert_main_category_term();
			//mycbgenie_insert_sub_category_term();	
			
	$wpdb->query( 'COMMIT;' );
	$wpdb->query( 'SET autocommit = 1;' );	
	$wpdb->flush(); 
				
			echo 'pre step has executed';
			return;
			exit;
	 }
	 else {
	
 
	 	 	$cron_id			=	$result[0]->mycbgenie_cron_id;
		 	$products_processed	=	$result[0]->products_completed;
			$destination_path	=	$result[0]->json_path;
			$total_products		=	$result[0]->total_products;
			$steps_completed	=	$result[0]->steps_completed;
			$total_steps		=	$result[0]->total_steps;

			$pre_step			=	$steps_completed;
			$step				=	$steps_completed+1;
			$throttle_speed		=	$result[0]->throttle_speed;
			
			

	 }

	 $destination_path	=	$destination_path.'/json_output_'.$step.'.txt';
	 	 
	 if ($pre_step < 0) {	 	$step=1;	}
	
	
	 if ($step==$total_steps) {
	 
			$final_step			=	'final_step';
			$modulus			=	$total_products % $throttle_speed;

	}
	
	echo $step.' has executed';

	 
	//update woocommerce image dimensions
	mycbgenie_update_woocommerce_image_dimensions();
			
	 //call the sync process
	 $error_message	=	mycbgenie_sync_products	($destination_path,$final_step,$cron_id,$throttle_speed,
								$modulus,$step,$products_processed,'cron-job',$screenshot_allowed);

	//reverse woocommerce image dimensions
	mycbgenie_reverse_woocommerce_image_dimensions();
	
	//update term count
	if ($step==$total_steps) {
	 	mycbgenie_after_import_update_term_count_action_function();
	}
	 
}


function mycbgenie_suspend_cron_jobs(){
	
	wp_clear_scheduled_hook ('mycronjob_mycbgenies');

	global $wpdb;
	$sql = "DELETE FROM mycbgenie_cron_sync_master WHERE ( status_cron <> 'success' or status_cron is NULL)";
	$wpdb->query($sql);
}

function mycbgenie_restart_cron_jobs(){


		$mycbgenie_cron_batch_frequency	=	get_option('mycbgenie_cron_batch_frequency',30);
	
		if ($mycbgenie_cron_batch_frequency==5){
			$cron_interval='every5minute'; }
		elseif ($mycbgenie_cron_batch_frequency==10){
			$cron_interval='every10minute'; }
		elseif ($mycbgenie_cron_batch_frequency==15){
			$cron_interval='every15minute'; }
		elseif ($mycbgenie_cron_batch_frequency==30){
			$cron_interval='every30minute'; }
		elseif ($mycbgenie_cron_batch_frequency==60){
			$cron_interval='everyhour'; }	
		

		if( !wp_next_scheduled( 'mycronjob_mycbgenies' ) ) {  
	  	 wp_schedule_event( time(), $cron_interval, 'mycronjob_mycbgenies' );  
		}

}

// add custom interval
function cron_add_minute( $schedules ) {
			// Adds once every minute to the existing schedules.
			$schedules['everyminute'] = array(
				'interval' => 60,
				'display' => __( 'Once Every Minute' )
			);
			return $schedules;
}

function cron_five_minute( $schedules ) {
			// Adds once every minute to the existing schedules.
			$schedules['every5minute'] = array(
				'interval' => 5*60,
				'display' => __( 'Once Every 5 Minutes' )
			);
			return $schedules;
}

function cron_ten_minute( $schedules ) {
			// Adds once every minute to the existing schedules.
			$schedules['every10minute'] = array(
				'interval' => 10*60,
				'display' => __( 'Once Every 10 Minutes' )
			);
			return $schedules;
}

function cron_fifteen_minute( $schedules ) {
			// Adds once every minute to the existing schedules.
			$schedules['every15minute'] = array(
				'interval' => 15*60,
				'display' => __( 'Once Every 15 Minutes' )
			);
			return $schedules;
}

function cron_thirty_minute( $schedules ) {
			// Adds once every minute to the existing schedules.
			$schedules['every30minute'] = array(
				'interval' => 30*60,
				'display' => __( 'Once Every 30 Minutes' )
			);
			return $schedules;
}


function cron_sixty_minute( $schedules ) {
			// Adds once every minute to the existing schedules.
			$schedules['everyhour'] = array(
				'interval' => 60*60,
				'display' => __( 'Once Every Hour' )
			);
			return $schedules;
}
		
add_filter( 'cron_schedules', 'cron_add_minute' );
add_filter( 'cron_schedules', 'cron_five_minute' );
add_filter( 'cron_schedules', 'cron_ten_minute' );
add_filter( 'cron_schedules', 'cron_fifteen_minute' );
add_filter( 'cron_schedules', 'cron_thirty_minute' );
add_filter( 'cron_schedules', 'cron_sixty_minute' );

		
		
function mycbgenie_cron_settings(){

		if (!get_option('mycbgenie_cron_per_batch_no_products')){
			$per_batch_cron='25';
			}
		else{$per_batch_cron=get_option('mycbgenie_cron_per_batch_no_products');}


		if (!get_option('mycbgenie_cron_batch_frequency')){
			$batch_frequency_cron='30';
			}
		else{$batch_frequency_cron=get_option('mycbgenie_cron_batch_frequency');}

?>

	<form action="" method=post id="mycbgenie_form_settings_cron">
	<input type="hidden" name="mycbgenie_settings_cron" />
			<ul>
			
			<li><label for="perbatch"><strong>Execute </strong></label>
						<select name="mycbgenie_cron_per_batch_no_products" id="mycbgenie_cron_per_batch_no_products" />
							<option <?php if ($per_batch_cron == 5)  echo "selected=selected"  ?>  value="5">5</option>
							<option <?php if ($per_batch_cron == 10)  echo "selected=selected"  ?>  value="10">10</option>
							<option <?php if ($per_batch_cron == 25)  echo "selected=selected"  ?>  value="25">25</option>
							<option <?php if ($per_batch_cron == 50)  echo "selected=selected"  ?>   value="50">50</option>
							<option <?php if ($per_batch_cron == 100)  echo "selected=selected" ?>  value="100">100</option>
						</select> products in a batch  
						</li> 

						<div style="padding:7px; background:#FFFFCC; line-height:inherit;">
						Please select <span style="padding:3px; background:#FCCCCC;"><strong>10</strong></span> products, 
						if you are on a shared server.	A good server can handle up to 100 products in a batch!<br />
						
						There are many cheap servers that may throw errors if selected more than 10 products!</div>
							
						
						
			<br />
			<li><label for="batch_frequency"><strong>Execute each batch in every</strong>
			 <select name="mycbgenie_cron_batch_frequency" id="mycbgenie_cron_batch_frequency" />
			 				
							<option <?php if ($batch_frequency_cron == 60) echo "selected=selected"  ?> value="60">60</option>
							<option <?php if ($batch_frequency_cron == 30) echo "selected=selected"  ?> value="30">30</option>
							<option <?php if ($batch_frequency_cron == 15) echo "selected=selected"  ?> value="15">15</option>
							<option <?php if ($batch_frequency_cron == 10) echo "selected=selected"  ?> value="10">10</option>

						</select> minutes
						</li> 	
					</label>
					
					</ul>	

						<!--<span style="padding:5px; background:#FFFFCC;">
						Please select <strong>25</strong> or <strong>50</strong>, if you are on a shared server.
						</span>	-->
			</ul><!--
			<label><span style="padding:5px; background:#FFFFCC;">In order for the WordPress cron jobs to work effectively, please make sure you have at least one visitor to your website in the batch frequency interval, you have selected. In case you did not get any traffic in the selected batch interval time, the missing cron job batch is executed in the next cycle.</span>	</label><br />--><br />
			<input class="button-primary" id="submit-btn-ajax_gen_setting" type=submit  value="Save Changes" />

	</form>
												

<?php
}

if (isset($_POST['mycbgenie_settings_cron'])){

	


	//check if any SYNC process is running.. If value return >0 means SYNC is running
	$sql	="	SELECT * FROM mycbgenie_manual_sync_products_master 
	 			WHERE ( status_sync <> 'success' or status_sync is NULL) order by start_time desc  ";
				
	$result = $wpdb->get_results( $sql );
	
	$sync_master_count	=	intval(	count($result)	);
	
	//check if FRESH Instatalttion is running and yet to complete. If value return ==0 means FRESH INSTALL ins running
	 $sql="SELECT * FROM mycbgenie_fresh_import_products_master 
				 	WHERE ( status_import = 'success' OR status_modified = 'success') order by start_time desc";
				
				
	$result = $wpdb->get_results( $sql );
	 
	 
	$fresh_master_count	=	intval(	count($result)	);

	if( $sync_master_count >0 || $fresh_master_count == 0  ){
	
		echo '<div align=center style="background:lightyellow; padding:5px; font-size:18px; font-famiy:arial;">Please be patient while the Import/Sync process is completed. You can change this setting only after IMPORT or SYNC process is completed.';
		
		if( $sync_master_count >0 ){
				echo '<br><br>If you are NOT running MANUAL SYNC process on another window at the moment, please click on MANUAL SYNC button on the IMPORT page, to resume from the last interrupted point. You need to complete the current scheduled MANUAL SYNC process before you configure the CRON job. <br><br>After this action, you may come back to this settings page to configure CRON job.';
		}
		else
		{
		echo '<br><br>You may come back to this settings page to configure CRON job once the import is finished.';
		}
		echo '<br></div>';
		exit;
		
	}else{
	
		//suspend and restsrt the cron only if NO import or SYNC is running in background/other window.

		mycbgenie_suspend_cron_jobs();

		update_option('mycbgenie_cron_per_batch_no_products',$_POST['mycbgenie_cron_per_batch_no_products']);
		update_option('mycbgenie_cron_batch_frequency',$_POST['mycbgenie_cron_batch_frequency']);
	
		mycbgenie_restart_cron_jobs();
	}

}


function mycbgenie_convert_seconds_to_time($tot_seconds){

					$tot_mins= absint($tot_seconds /60);
					$tot_hours= absint($tot_mins / 60);
					$tot_days=	absint($tot_hours / 24);
					
					if ($tot_days>0){
					
							$status_string=$tot_days.' day(s) '.absint($tot_hours % 24). ' hours' ;
								//'. absint($tot_mins % 60).' minute(s) ' . absInt($tot_seconds % 60) . ' seconds';
					}
					elseif ($tot_hours>0 ) {
							
							$status_string=$tot_hours . ' hours '. absint($tot_mins % 60).' mins ' ;
								//. absInt($tot_seconds % 60) . ' seconds';
						}
					elseif ($tot_mins>0 ) {

							$status_string=$tot_mins. ' mins '. absInt($tot_seconds % 60) . ' secs';
							}
					elseif ($tot_seconds>0 ) {
							
							$status_string=$tot_seconds . ' secs';
							
					}
				return 		$status_string;

}


function mycbgenie_cron_stats(){

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap" style="margin-top: 35px;">';
		mycbgenie_header_files();


?>


<?php

	echo '<div align=right><strong>Current Time : </strong>'. date('Y-m-d, h:i:s A', time()).'</div>';
	echo '<div style="background:#D4D4D4; border-radius:7px;  border:1px solid silver; 
			padding:20px; ">';
	echo '<div style=" border:0px solid black">';
	
	echo '<h3>Current Cron Job Stats</h3>';

	echo 	'<table class="sample" width=100%>';

global $wpdb;

	$timestamp = wp_next_scheduled( 'mycronjob_mycbgenies' );


	 $sql="SELECT *  FROM mycbgenie_manual_sync_products_master where status_sync is NULL or status_sync <> 'success' 	";
				
	 $result_sync = $wpdb->get_results( $sql );
	 
	 $sql="SELECT *  FROM mycbgenie_fresh_import_products_master	";
				
	 $result_fresh = $wpdb->get_results( $sql );



	
	if (	count($result_sync)==0	|| count($result_fresh)==0 ){
	
			$sql="SELECT *  FROM mycbgenie_cron_sync_master 
				 	order by start_time desc limit 0,1";
				
			$result = $wpdb->get_results( $sql );
			
			foreach ($result as $res){
	
			echo '<tr><th>Cron ID</th><td>'.$res->mycbgenie_cron_id.'</td></tr>';
			$tot_seconds=strtotime(date('Y-m-d H:i:s', time())) - strtotime( $res->start_time)  ;
	
			$status_string=mycbgenie_convert_seconds_to_time($tot_seconds);
	
			echo '<tr><th valign=top>Started</th><td>'.date('Y-m-d, h:i:s A',strtotime($res->start_time)).
				'<br><font color=maroon>   [ '.$status_string.' ago ]</font></td></tr>';
			
			echo '<tr><th>Status</th><td>';
			 if ($res->status_cron=='success') { echo ''; } else{ echo '<font color=green>On Schedule</font>';}
			echo '</td></tr>';
			echo '<tr><th>Total Products </th><td>'.$res->total_products.'</td></tr>';
			echo '<tr><th>Batch Size</th><td>'.$res->throttle_speed.' products</td></tr>';
	
			echo '<tr><th>Total Batches</th><td>'.$res->total_steps.'</td></tr>';
			
			
			if ($res->steps_completed<0){
				$steps_c="Yet to start";
				echo '<tr><th>Batches Synced</th><td>'.$steps_c.'</td></tr>';

			}else
			
			{ 
				$steps_c = $res->steps_completed;
				
				echo '<tr><th>Batches Synced</th><td>'.$steps_c.'</td></tr>';
				
				
				$tot_seconds=strtotime(date('Y-m-d H:i:s', time())) - strtotime( $res->last_batch_process_time)  ;
		
				$status_string=mycbgenie_convert_seconds_to_time($tot_seconds);
				
				echo '<tr><th valign=top>Last Batch Synced  </th><td><font color=blue>'
				.$status_string.' ago</font></td></tr>';
			
			
			}
			
			


			
			echo '<tr><th>Batch Frequency</th><td> in every '.get_option('mycbgenie_cron_batch_frequency',30).' minutes</td></tr>';
			$tot_seconds=strtotime(date('Y-m-d H:i:s', $timestamp)) - strtotime( (date('Y-m-d H:i:s', time())))  ;
			
			
			$status_string=mycbgenie_convert_seconds_to_time($tot_seconds);
	
			
				echo '<tr><th valign=top>Next Batch Due On</th><td>'.date('Y-m-d, h:i:s a', $timestamp).
				'<br><font color=maroon>   <span>[ '.$status_string.' left ]</span></font></td></tr>';
			
	
		   }
	
		
		}
	else{
		echo	'<tr><td>Cron Job will start after <br>
				<strong>FRESH IMPORT</strong> or <strong> MANUAL SYNC</strong><br> is finished.</td></tr>';
	}

	
		echo '</table></div>';
		

		$sql="SELECT *  FROM mycbgenie_cron_sync_master 
				 	order by start_time desc limit 1,7";
				
		$result = $wpdb->get_results( $sql );
		
		if (count($result)>0){
	
			echo 	'<div style="border:0px solid; margin-top:37px;">';
			echo 	'<h3>Last Few Completed CRON Jobs</h3>';
			echo	'<table width=100% class="sample">';
			
			
			echo '<tr><th>ID</th><th>Started</th><th>Ended</th><th>Result</th>
					<th>Products</th><th>Added</th><th>Removed</th><th>Batches</th><th>Batch Size</th>
					<th>Time Span</th></tr>';
	
	
			foreach ($result as $res){
			
					
					
					$status_string='';
					$tot_seconds=(strtotime($res->end_time)-strtotime($res->start_time));

					$status_string=mycbgenie_convert_seconds_to_time($tot_seconds);
							
								 					
			
						
					//echo '<br>';	
						
					echo '<tr><td valign=top>'.$res->mycbgenie_cron_id.'</td>';
					echo '<td valign=top>'.date('Y-m-d',strtotime($res->start_time)).
						 '<h5 style="margin:0px; font-weight:normal;">'.date('h:i:s A',strtotime($res->start_time)).'</h5></td>';
					echo '<td>'.date('Y-m-d',strtotime($res->end_time)).
						 '<h5 style="margin:0px; font-weight:normal;">'.date('h:i:s A',strtotime($res->end_time)).'</h5></td>';
					//echo '<td>'.$res->end_time.'</td>';
	
	
					echo '<td valign=top>';
						 if ($res->status_cron=='success') 
						 { echo '<font color=green>Success</font>'; } 
						 else{ echo '<font color=green>???</font>';}
					echo '</td>';
			
					echo '<td valign=top align=center>'.$res->total_products.'</td>';
					echo '<td valign=top align=center>'.$res->products_added.'</td>';

					echo '<td valign=top align=center>'.$res->products_removed.'</td>';
	
					echo '<td valign=top align=center>'.$res->total_steps.'</td>';
					echo '<td valign=top align=center>'.$res->throttle_speed.'</td>';
					echo '<td valign=top align=center>'.$status_string.'</td>';
	
	
			
			}
			
				echo '</table></div>';
			
		} //end if
		
		echo	'</div>';
		
		$sql="SELECT *  FROM mycbgenie_manual_sync_products_master 
						order by start_time desc limit 0,10";
					
		$result = $wpdb->get_results( $sql );
		
		if (count($result)>0){
		
			echo '<div style="background:#D4D4D4; border-radius:7px; margin-top:30px;
				padding:15px; padding-bottom:27px; height:100%; border:0px solid gray;">';

		
			echo 	'<div style=" border:0px solid; ">';
			echo 	'<h3>Last Few MANUAL SYNC Stats</h3>';
			echo	'<table width=100% class="sample">';
			
			
		
			
			echo '<tr><th>ID</th><th>Started</th><th>Ended</th><th>Result</th>
					<th>Products Synced</th><th>Products Added</th>					<th>Products Removed</th>

					<th>Time Span</th></tr>';
	
	
			foreach ($result as $res){
			
					$status_string='';
					$tot_seconds=(strtotime($res->end_time)-strtotime($res->start_time));
					$status_string=mycbgenie_convert_seconds_to_time($tot_seconds);

			
			
					echo '<tr><td>'.$res->mycbgenie_sync_id.'</td>';
					echo '<td>'.$res->start_time.'</td>';
					echo '<td>'.$res->end_time.'</td>';
	
	
					echo '<td>';
						 if ($res->status_sync=='success') 
						 { echo '<font color=green>Success</font>'; } 
						 else{ echo '<font color=green>???</font>';}
					echo '</td>';
			
					echo '<td align=center>'.$res->products_completed.'</td>';
					echo '<td align=center>'.$res->products_added.'</td>';

					echo '<td align=center>'.$res->products_removed.'</td>';

					echo '<td align=center>'.$status_string.'</td>';
	
	
			
			}
			
			echo '</table></div>';
		
		}
		
		echo '</div>';
}
	

	
?>