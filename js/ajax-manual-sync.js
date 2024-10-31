
jQuery(document).ready( function($){
								 
 	var throttle=$('#mycbgenie_sync_throttle').val();
	var start_time;
	var answer;
	var step;
	var total_batches;
	var resume_first_step;
	var	products_processed;
	var destination_path;
	var sync_id;
	var batch_interval;
	var	modulus;
	var resume_first_step;
	var screenshot_allowed='';
	var prev_screenshot_allowed='';
	var temp_screen_shot_allowed='';
	var temp_prev_screen_shot_allowed='';
	var answer_tick;



	
function setProgress(progress)
{			
	 	var progressBarWidth =progress*$(".container").width()/ 100;  
	 	$(".progressbar").width(progressBarWidth).html(progress + "%&nbsp;");
}

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function delete_entry_from_SYNC_master(sync_id)
{


$.ajax({	
		type: 'POST',
		url: mycbgenie_manual_sync_vars.ajaxadminurl,
		data: {
			action 	:  'mycbgenie_ajax_manual_sync_delete_action',
			sync_id		:	sync_id,
			mycbgenie_manual_sync_nonce : mycbgenie_manual_sync_vars.mycbgenie_manual_sync_nonce
		},
		dataType: "json",
		success: function( response ) {	

			if (response.status == "OK"){
				alert('Deleted! \n\nPlease click on "Start SYNC" again, once you have selected an appropriate throttle value.'  );
				$('#sync-submit-btn').attr('disabled',false);
				return false;
			}
			else{
				alert('Error on deleting MANUAL SYNC entry! Ajax Json did not return as OK!'  );
				return false;
			}

		}//ssuccess

		}).fail(function(xhr, textStatus, errorThrown) {
						if ( window.console && window.console.log ) {
							
							console.log( xhr.responseText + textStatus + errorThrown  );
							alert('Error on deleting MANUAL SYNC entry! '+ xhr.responseText + textStatus + errorThrown  );
							return false;
						}
			
}); //ajax 

return false;
}

$('body').on( 'submit', '#mycbgenie_form_manual_sync_ajax', function(e) {
														 


		if ($('#screenshot_allowed_sync').is(":checked"))
		{
  			// it is checked
			screenshot_allowed="yes";																
		}else {
			screenshot_allowed="no";																		
		}
		
			
		$('#progressbar_id').hide();
		$('#sync_progressbar_id').hide();
		$('#submit-btn').attr('disabled',true);

		$('#sync-submit-btn').attr('disabled',true);
		$('#manual_sync_time').html('');
		$('#manual_sync_status').html('');
		$('#manual_sync_final_status').html('');

		$.ajax({	
		type: 'POST',
		url: mycbgenie_manual_sync_vars.ajaxadminurl,
		data: {
			action :  'mycbgenie_ajax_manual_sync_action',
			throttle	:	$('#mycbgenie_sync_throttle').val(),
			mycbgenie_manual_sync_nonce : mycbgenie_manual_sync_vars.mycbgenie_manual_sync_nonce
		},
		dataType: "json",
		success: function( response ) {	


			if (response.status=='error'){
							console.log(response.error_message);
							alert(response.error_message);
							return false;
			}
			
			if (parseInt(response.import_master_count)==0){
				alert('You have not yet imported the products succesfully. ');
				$('#sync-submit-btn').attr('disabled',false);

				return false;
			}
			
			else {
				
				sync_id				=	response.sync_id;
				products_processed	=	response.products_processed;

				
			}
			
			skip_file_import 				= 	response.skip_file_import;
			json_import_file_id				=	response.json_import_file_id;
			json_existing_import_file_id	=	response.jsonimport_existing_file_id;
			last_success_import_file		= 	response.last_success_import_file;
			last_used_throttle				= 	response.last_used_throttle;
			json_file_change_detected		=	response.json_file_change_detected;
			throttle_changed				=	response.throttle_changed;
			throttle_old_value				=	response.throttle_old_value;
			prev_screenshot_allowed			=   response.screenshot_allowed;
			
			if (prev_screenshot_allowed	!= null){
				
				if (parseInt(products_processed)>0){
				
						if (	screenshot_allowed =="yes") {
								temp_screen_shot_allowed = 'TICKED';
						}else {
									temp_screen_shot_allowed = 'NOT TICKED';
						}
						if (	prev_screenshot_allowed =="yes") {
									temp_prev_screen_shot_allowed = 'TICKED';
						}else {
									temp_prev_screen_shot_allowed = 'NOT TICKED';
						}
						
						if (prev_screenshot_allowed != screenshot_allowed) {
							answer_tick = confirm('Sorry, you are trying to resume the SYNC process with a different option selected from the last interrupted '+
												  'SYNC process.\n\n ' +
												 'You have selected the option \"Replace blank images with the screenshot\" as ' +temp_screen_shot_allowed + ' while it was '+
												 temp_prev_screen_shot_allowed + ' on the last attempt. \n\n' +
												 'If you are particular on with the option selected as '+ temp_screen_shot_allowed +', please click on '+
												 ' OK button to re-start the SYNC process.  \nPress CANCEL if you want to resume the SYNC process with the'+
												 ' option selected as '+temp_prev_screen_shot_allowed+'.\n\n\ '	 );
						}
						
						if (answer_tick == true)
						{
							delete_entry_from_SYNC_master(sync_id);
							return false;
						}
						else{

								screenshot_allowed = prev_screenshot_allowed;
								if (screenshot_allowed == null){ screenshot_allowed="no"; }
								
								if (	screenshot_allowed =="yes") {
										document.getElementById("screenshot_allowed_sync").checked = true;
								}else {
											document.getElementById("screenshot_allowed_sync").checked = false;
								}
						}
				} // end  of (parseInt(products_processed)>0){
			}//if (prev_screenshot_allowed	!=null){

			

			
	console.log('screenshot allowed: '+ screenshot_allowed +' Skip Import : '+skip_file_import+' , Remote Import ID : '+ json_import_file_id +',  Last Interrupted Import ID : '
											+json_existing_import_file_id+', Last Success File Id : '+ last_success_import_file+ 
											', Last Used Throttle : '+ last_used_throttle+' Json detected change:'+ json_file_change_detected+
											' Throttle_changed : '+ throttle_changed +', Throttle old val : '+ throttle_old_value);
			
			if (throttle_changed=="yes") {
				
			   var strconfirm =confirm('Please change the throttle value to " '+ throttle_old_value+ ' " and try again. In fact we have set it.'
					 +'\n\nJust click on " OK " button and click on "Start SYNC" button to resume.\n\nIn case, if you are receiving any errors on this throttle value selected,'+
					 '\nplease press " CANCEL " button, to delete this pending SYNC process \nand to start with a lower throttle value.');
				$('#mycbgenie_sync_throttle').val(throttle_old_value);

				//$('#sync-submit-btn').trigger('click');
				
			if (strconfirm == true)
            {
				$('#sync-submit-btn').attr('disabled',false);
               return false;
			}
			else{
				delete_entry_from_SYNC_master(sync_id);
				return false;
            }
   
				
				
				
						
			}
			

			if (json_file_change_detected=="yes") {
				alert('We have noticied a change in the remote source file content, just after you\'ve started this MANUAL SYNC process. \n\nFor best results,'
					 +' you are requested to process MANUAL SYNC once again, just after you finish this process. \n\nWe know it is a bit embarrassing but it is worth doing!!!');
						
			}
			
			$('#sync_aad_loading').show();
			
			$('#sync_progressbar_id').show();

			if (skip_file_import == "yes") {
				$('#manual_sync_status').html('Skipping..... Importing Import file ');
			}
			else{
				$('#manual_sync_status').html('Please be patient while the import file is being downloaded. \nIt should take a minute.');
			}
			
					//setProgress(3);
					
					
					$.ajax({	
					type: 'POST',
					url: mycbgenie_manual_sync_vars.ajaxadminurl,
					data: {
						action 						 :  'mycbgenie_manual_sync_fetch_json_files_action',
						throttle_speed				 :	$('#mycbgenie_sync_throttle').val(),
						skip_file_import			 :	skip_file_import,
						json_import_file_id			 :	json_import_file_id,
						json_existing_import_file_id :	json_existing_import_file_id,
						json_file_change_detected	 :	json_file_change_detected,
						last_success_import_file	 :	last_success_import_file,
						mycbgenie_manual_sync_nonce  :  mycbgenie_manual_sync_vars.mycbgenie_manual_sync_nonce
					},
					dataType: "json",
					success: function( response ) {	
			
						console.log(response.error_message);
						//sync_id				=	response.sync_id;
						//products_processed	=	response.products_processed;
						total				=	response.total;
						total_batches		=	response.total_steps;
						destination_path	=	response.destination_path;
						
						$('#manual_sync_status').html('Import file has been fetched succesfully from remote server');

						start_sync_process();


						
						return false;
			
						
					}//ssuccess
					
					
					}).fail(function(xhr, textStatus, errorThrown) {
								if ( window.console && window.console.log ) {
									
										alert('Error! '+   xhr.responseText + textStatus + errorThrown  );
										return false;
								 }
			
					}); //ajax 
		





		}//ssuccess
		
		
		}).fail(function(xhr, textStatus, errorThrown) {
					if ( window.console && window.console.log ) {
						
							alert('Error! '+   xhr.responseText + textStatus + errorThrown  );
							return false;
				 	 }

		}); //ajax 

	
	
		function start_sync_process(){
			
			start_time=	$.now();
			e.preventDefault();


			if (parseInt(products_processed)>0){
				$('#manual_sync_status').html('Resuming from '+ addCommas(products_processed)+' products completed');
					setProgress(	parseInt((products_processed /total)*100));
	
				sync_step( 1,  answer ,total_batches, total, resume_first_step  );

			}
			else{
				sync_step( -1,  answer ,total_batches, total, resume_first_step  );
				//$('#manual_sync_status').html('Processing the first batch of '+ $('#mycbgenie_sync_throttle').val() +' products ...');
				setProgress(1);

			}
			
			

					
			function sync_step( step,   answer, total_batches, total ) {
						
			
						
					if (step < 0 ){
					$('#manual_sync_status').html('Downloading the import files from the remote server.... <br>Please be patient.');
					console.log ( ' iam from process step with value -1');
					$.ajax({
										type: 'POST',
										url: mycbgenie_vars.ajaxadminurl,
										data: {
											action :  'mycbgenie_ajax_pre_import_activities',
											delete_or_resume	:	answer,
											fresh  :  'manual'
					
										},
										dataType: "json",
										success: function( response ) {
											
											//alert (response.pre_status);
											
											if (response.pre_status=="OK") {
												
												$('#manual_sync_status').html('Getting Ready For SYNC...' );
												sync_step( 1,  answer ,total_batches, total, resume_first_step  );
												
											}else {
												alert ('Error returned from Pre-Import activities script.. Please make sure you are connected to internet.');
												$('#sync_aad_loading').hide();
												$('#manual_sync_status').html('Error returned from Pre-Import activities script...<br>Error : '+response.error_message);

												
											}
												return false;
										}//ssuccess
																
										}).fail(function(xhr, textStatus, errorThrown) {
																						
											if ( window.console && window.console.log ) {
																							
												console.log( xhr.responseText + textStatus + errorThrown  );
												alert('Error on pre import activities! '+   xhr.responseText + textStatus + errorThrown  );
												return false;
											}
										
																
					}); //ajax 					
					return false;
				}
				
				if (step==1) {
					
						//$('#manual_sync_status').html('Processing has been started');
						//$('#manual_sync_status').html('Processing the first batch of '+ $('#mycbgenie_sync_throttle').val() +' products ...');
						setProgress(2);			
				}

				//console.log('Step : '+step+' , products_processed : '+ products_processed +', action : '+answer );
				
				//if (step>=2){
	//return false;
		//	}
					var batch_start_time=$.now();
				
					console.log('Step : '+step+' , products_processed : '+ parseInt(products_processed) +',  syncID: '
											+sync_id + ', batches : '+total_batches  +', total : '+ total +', path : '
											+ destination_path );
					



					$.ajax({	
					type: 'POST',
					url: mycbgenie_manual_sync_vars.ajaxadminurl,
					data: {
						action 				:  'mycbgenie_manual_sync_process_action',
						sync_id				:	sync_id,
						destination_path	:	destination_path,
						products_processed	:	products_processed,
						batches				:	total_batches,
						total				:	total,
						resume_first_step	:	resume_first_step,
						step				:	step,
						throttle_speed		:	$('#mycbgenie_sync_throttle').val(),
						screenshot_allowed	:	screenshot_allowed,					
						mycbgenie_manual_sync_nonce : mycbgenie_manual_sync_vars.mycbgenie_manual_sync_nonce
					},
					dataType: "json",
					success: function( response ) {	
					
						resume_first_step	=	response.resume_first_step;
						
						batch_interval=parseInt(($.now()- batch_start_time) /1000);

						if( 'done' == response.step ) {
							//console.log(response.percentage);
							setProgress(100);
				
							modulus=total % parseInt($('#mycbgenie_sync_throttle').val());
							
							if (modulus >0 ) { $step_final =parseInt(response.step_final) -1; }
							else	{ $step_final =parseInt(response.step_final) ;}
							
							completed=(( ( $step_final )* $('#mycbgenie_sync_throttle').val() )+ modulus);
							$('#manual_sync_time').html('');
							$('#manual_sync_status').html('<i><font color=green>Performing some post-sync activities.... <br>(should take up to 1-2 minutes of time)<br></font></i>');
		




										
											$.ajax({	
													type: 'POST',
													url: mycbgenie_manual_sync_vars.ajaxadminurl,
													data: {
														action 	:  'mycbgenie_after_import_update_term_count_action',
														process_type :	"manual"
													},
													dataType: "json",
													success: function( response ) {	
											
														if (response.status == "OK"){
															
															$('#manual_sync_status').html('<i>'+ addCommas(completed)+' products synced out of '+ addCommas(total) + '</i>');

															$('#sync_aad_loading').hide();
															//$('#sync-submit-btn').attr('disabled',false);
															
															$('#manual_sync_final_status').html('<b><font color=green>SYNC process is completed successfully.</font></b>');				
															alert('SYNC process is over');
																						
														}
														else{
															alert(response.status + ' secs is taken. Error on update term update count!\n\n'+
																  'You are almost done but some thing went wrong in updating total count of each categories.'  );
															return false;
														}
											
													}//ssuccess
											
													}).fail(function(xhr, textStatus, errorThrown) {
																	if ( window.console && window.console.log ) {
																		
																		console.log( xhr.responseText + textStatus + errorThrown  );
																		alert('Error on update term update count! '+   xhr.responseText + textStatus + errorThrown  );
																		return false;
																	}
														
											}); //ajax 





							
							if (json_file_change_detected=="yes") {
								alert('You are requested to process MANUAL SYNC once again as we have detected a change in the source file content during this SYNC process.\n\n'+
									  'Please refresh this screen and press MANUAL SYNC button once again...! \n\nThank you for your patience. ');
							}
						
							
						} else {
							
							var status_string='';
							var tot_seconds=parseInt(($.now()- start_time) /1000);
							var tot_mins= parseInt(tot_seconds /60);
							var	tot_hours= parseInt(tot_mins / 60);
									
							if (tot_hours>0 ) {
								
								status_string=tot_hours + 'hour(s) '+parseInt(tot_mins % 60)+' minute(s) ' + 
												parseInt(tot_seconds % 60) + ' seconds';
							}
							else if (tot_mins>0 ) {

									status_string=tot_mins+' minute(s) ' + parseInt(tot_seconds % 60) + ' seconds';
								}
							else if (tot_seconds>0 ) {
								
								status_string=tot_seconds + ' seconds';
						
							}
							
							throttle=parseInt($('#mycbgenie_sync_throttle').val());
							
									

							completed=(( (response.step -1  )* $('#mycbgenie_sync_throttle').val() ));

							time_left= parseInt((total-completed) * (tot_seconds/(completed-products_processed))) ;
							
							console.log('total'+total+' comple'+completed+' pp'+products_processed);
							if( (time_left /60) > 60 ){
								time_left = parseInt(time_left/ 3600) + ' hour(s)  '+ parseInt(time_left/60) % 60 + ' mins';
							}
							else if ( time_left >60) {
								time_left = parseInt(time_left/ 60) + ' mins '+ time_left % 60 + ' seconds';
							}
							else {
								time_left = time_left + ' seconds';
							}
							time_left_string='<br><font color=maroon>[ Estimated time left : ' + time_left + ' ]</font>';
							
							$('#manual_sync_time').html('Time taken : '+ status_string + time_left_string );
							
					
							setProgress(parseInt(response.percentage));
							completed=(( (response.step -1  )* $('#mycbgenie_sync_throttle').val() )   );
							$('#manual_sync_status').html('<i>'+ addCommas(completed)+' products synced out of '+ addCommas(total) + '</i>');

							sync_step( parseInt( response.step ), answer , response.batches, response.total,resume_first_step);
							
							
						} //end of done

						return false;

					}//ssuccess
					
					
					}).fail(function(xhr, textStatus, errorThrown) {
						if ( window.console && window.console.log ) {
									
							$('#manual_sync_status').html('<b><font color=red>Error... Server interrupted in half-way. 														  Please refresh to resume. If the problem persists, please try with a lesser throttle value.</b></font>');
							console.log( xhr.responseText + textStatus + errorThrown  );
							alert('Error! '+ xhr.responseText + textStatus + errorThrown  );
							return false;
						}
			
					}); //ajax 

				
			}//end of function sync-step
			
		}	//end of start_sync_process
		
		 return false;

	
	}); //end of submit ON body tag		
		
});
		
