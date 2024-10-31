
jQuery(document).ready( function($){
					
var total='';
var throttle=$('#mycbgenie_throttle').val();
var	start_time='';
var completed='';
var time_left;
var answer='';
var	import_id='';
var	products_completed='';
var time_left_string='';
var total_batches ='';
var	products_processed='';
var	resume_first_step='';
var	destination_path='';
var batch_interval='';
var screenshot_allowed='';
var prev_screenshot_allowed='';
var temp_screen_shot_allowed='';
var temp_prev_screen_shot_allowed='';	
var answer_tick='';

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
				
function update_term_update_count()
{



}																	 
								 
	$('body').on( 'submit', '#mycbgenie_form_id_ajax', function(e) {
																
																
		if ($('#screenshot_allowed_import').is(":checked"))
		{
  			// it is checked
			screenshot_allowed="yes";																
		}else{
			screenshot_allowed="no";																		
		}
																

																
																
		$('#product_import_status').html('');
		$('#product_import_time').html('');
		$('#product_import_final_status').html('');
		$('#product_import_status').html('Initializing...');
														
		answer="fresh";
		
		$('#submit-btn').attr('disabled',true);

		$.ajax({	//ajax for already import checking

		type: 'POST',
		url: mycbgenie_vars.ajaxadminurl,
		data: {
			action :  'mycbgenie_ajax_cb_import_check_already_exists_action',
			mycbgenie_nonce : mycbgenie_vars.global_mycbgenie_nonce
			//delete_or_resume	:	answer,
			//step: step
		},
		dataType: "json",
		//async:	false,
		success: function( response ) {	
		
					 existing_file_id	=	response.existing_import_file_id;
					 remote_file_id		=	response.remote_import_file_id;
					 last_used_throttle =	response.last_used_throttle;
					 products_processed	= 	response.products_processed;
					 batch_interval		=	response.batch_interval;
					 prev_screenshot_allowed =	response.screenshot_allowed;
			
					 if (response.status=='error'){
							console.log(response.error_message);
							alert(response.error_message);
							return false;
					 }
			
						
					if (parseInt(response.already_imported)>0 && ((response.import_status=='success') || (response.modified_status=='success') ) ) {
						
						answer = confirm('You\'ve already imported products successfully.\n\nDo you want to delete previous import entries and proceed with a fresh re-import? Please note this process can take about 1-2 hours of time.');
					
						if (answer)
						{
							answer="delete";
						}
						else{	
						
						$('#submit-btn').attr('disabled',false);
						$('#product_import_status').html('');
						return false;	
						}
									
					}
					
						$('#sync-submit-btn').attr('disabled',true);

						$('#product_import_status').html('<marquee>Please be patient while the file is being downloaded</marquee>');

						$('#progressbar_id').show();
					
					if (parseInt(response.already_imported)>0 && (response.import_status!='success') &&  (response.modified_status!='success')) {
						
						answer = confirm('Your previous import process didn\'t complete successfully.\n\nDo you want to DELETE previous import entries and proceed with a fresh import process? \n\nPress CANCEL to resume from the previous import point. \n\nPress OK to delete previous entries and re-import from scratch');
						if (answer)
						{
							answer="delete";
						}
						else
						{
							answer="resume";
						}
						//alert(answer);
						
						if (answer=="resume"){
						
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
										answer_tick = confirm('Sorry, you are trying to resume the import process with a different option selected from the last interrupted '+
													 'process.\n\n ' +
													'You have selected the option \"Replace blank images with the screenshot\" as ' +temp_screen_shot_allowed+ ' while it was '+
													 temp_prev_screen_shot_allowed + ' on the last attempt. \n\n' +
													 'If you are particular on with the option selected as '+ temp_screen_shot_allowed +', please click on '+
													 ' OK button to re-start the import process.  \nPress CANCEL if you want to resume the import process with the'+
													 ' option selected as '+temp_prev_screen_shot_allowed+'.\n\n\ '	 );
									}
			
									if (answer_tick)
									{
										answer="delete";
									}
									else{
											screenshot_allowed = prev_screenshot_allowed;
											if (screenshot_allowed == null){ screenshot_allowed="no"; }
											
											if (	screenshot_allowed =="yes") {
													document.getElementById("screenshot_allowed_import").checked = true;
											}else {
														document.getElementById("screenshot_allowed_import").checked = false;
											}
									}
						}//end of resume
					}
					
					//alert('Answer : ' +answer+' sc: '+screenshot_allowed);
					//return false;
					
	
					if(answer=="resume" || answer=="delete" || answer=="fresh"){


						if (answer=="delete" || answer=="fresh") {
							$('#product_import_status').html('Deleting previous import entries, if any ...');
						}
						
						if (answer=="resume" ){
							$('#product_import_status').html('The script will wait for '+batch_interval+' seconds for the previous import to finish');
						}
						  
						$.ajax({	//ajax for already import checking

						type: 'POST',
						url: mycbgenie_vars.ajaxadminurl,
						data: {
							action :  'mycbgenie_ajax_cb_import_resume_delete_action',
							mycbgenie_nonce : mycbgenie_vars.global_mycbgenie_nonce,
							throttle_speed:	$('#mycbgenie_throttle').val(),
							batch_interval	:	batch_interval,
							remote_file_id	:	remote_file_id,
							delete_or_resume	:	answer
							//step: step
						},
						dataType: "json",
						//async:	false,
						success: function( response ) {
							
							
							//if (parseInt(response.error_message.length)>5)	{
							//alert(response.error_message);
							console.log(response.error_message);
							//}
						
							import_id			=	response.import_id;
							products_processed	=	response.products_processed;
							total				=	response.total;
							total_batches		=	response.total_steps;
							destination_path	=	response.destination_path;
							skip_file_import	=	response.skip_file_import;
							json_file_change_detected	= response.json_file_change_detected;
							last_success_import_file	= response.last_success_import_file;
							json_import_file_id	= response.json_import_file_id;
							
							//alert('pp'+products_processed+'tot'+total+'totstep'+total_batches);
							console.log('import_resume_delete_action=>Del/resum/fresh : '+answer+import_id);
							console.log('skip_file_import : '+skip_file_import);
							console.log('json_file_change_detected : '+json_file_change_detected);
							console.log('last_success_import_file : '+last_success_import_file);
							console.log('json_import_file_id : '+json_import_file_id);
							//alert(import_id);
							//return false;
							start_import_process();
						}
						}).fail(function(xhr, textStatus, errorThrown) {
							if ( window.console && window.console.log ) {
						
							alert('Error! '+   xhr.responseText + textStatus + errorThrown  );
							return false;
				  			}
						}); //ajax - import_resume_delete_action
					
					} //if(answer=="resume" || answer="delete" ){
					//else{
						//start_import_process();
					//} //elseif(answer=="resume" || answer="delete" ){
						
					
				}
				}).fail(function(xhr, textStatus, errorThrown) {
						if ( window.console && window.console.log ) {
						
							alert('Error! '+   xhr.responseText + textStatus + errorThrown  );
							return false;
				  }
				
				}); //end of ajax for already import checking
		
			
			
			

			return false;		
			

		function start_import_process(){
						//alert('insidefunction :'+import_id);		
						
						
			start_time=	$.now();
			e.preventDefault();
			var data_image = $(this).serialize();
			
			$('#aad_loading').show();
			$('#import_notice').show();
			
			$('#product_import_status').html('');
			$('#product_import_time').html('');
			$('#product_import_final_status').html('');
			if (answer=="resume"){
				$('#product_import_status').html('Resuming from '+ addCommas(products_processed)+' products completed');
					setProgress(	parseInt((products_processed /total)*100));
	
					process_step( 1,  answer ,total_batches, total, resume_first_step  );

			}
			else{
				
				process_step( -1,  answer ,total_batches, total, resume_first_step  );

				//$('#product_import_status').html('Processing the first batch of '+ $('#mycbgenie_throttle').val() +' products ...');
				setProgress(1);

			}

			
			
			function process_step( step,   answer, total_batches, total ) {
				
				if (step < 0 ){
					$('#product_import_status').html('Downloading the import files from the remote server.... <br>may take up to couple of minutes.');
					console.log ( ' iam from process step with value -1');
					$.ajax({
										type: 'POST',
										url: mycbgenie_vars.ajaxadminurl,
										data: {
											action :  'mycbgenie_ajax_pre_import_activities',
											delete_or_resume	:	answer,
											fresh  :  'fresh'
					
										},
										dataType: "json",
										success: function( response ) {
											if (response.pre_status=="OK") {
												console.log ( ' iam going to call step 1');

												process_step( 1,  answer ,total_batches, total, resume_first_step  );
											}else {
												alert ('Error returned from Pre-Import activities script.. Please make sure you are connected to internet.');
												$('#aad_loading').hide();
												$('#product_import_status').html('Error returned from Pre-Import activities script...<br>Error : '+response.error_message);

												return false;
											}
												
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
						$('#product_import_status').html('Getting ready for import...');
						setProgress(2);			
				}

	//alert(products_completed+'-'+import_id+answer);
	//return false;
	//alert('zzzsss');

		console.log('Step : '+step+' , products_processed : '+ products_processed +', action : '+answer + ', import_id: '+import_id + ', batches: '+total_batches  +', total : '+ total +', path : '+ destination_path + ', batch_interval:'+batch_interval);
		
				var batch_start_time=$.now();
				

				$.ajax({
		  			
					type: 'POST',
					url: mycbgenie_vars.ajaxadminurl,
					data: {
						action 				:  'mycbgenie_ajax_cb_import_action',
						mycbgenie_nonce 	: 	mycbgenie_vars.global_mycbgenie_nonce,
						delete_or_resume	:	answer,
						import_id			:	import_id,
						destination_path	:	destination_path,
						products_processed	:	products_processed,
						batches				:	total_batches,
						total				:	total,
						resume_first_step	:	resume_first_step,
						throttle_speed		:	$('#mycbgenie_throttle').val(),
						screenshot_allowed	:	screenshot_allowed,
						batch_interval		:	batch_interval,
						step: step
					},
					dataType: "json",
					success: function( response ) {
						
						if (response.status=='error'){
							console.log(response.error_message);
							alert(response.error_message);
							
							alert('It seems like your server is incapable of handling large amount of data. \n\n,Please switch to a reliable hosting partner, if the problem persists. \n\nWe recommend HostGator.com');
							
								$('#aad_loading').hide();
								$('#product_import_status').html('Error');
								$('#product_import_time').html('Error');
								$('#product_import_final_status').html('Error');

							return false;
						}
						
						 	batch_interval=parseInt(($.now()- batch_start_time) /1000);								

							console.log('After step : ' + step +', batch interval : '+ batch_interval);
						//if (parseInt(response.diagonostics.length)>5)	{
							//alert(response.diagonostics);
							console.log(response.diagonostics);
						//}
							
						
									total	=	response.total;
									resume_first_step=response.resume_first_step;
									//console.log('Diagonostics : ' + response.diagonostics);
										//console.log('In step:'+ step +', total : '+total + ' batches '+response.batches);
										//console.log(response.percentage);

									var status_string='';
									var tot_seconds=parseInt(($.now()- start_time) /1000);
									var tot_mins= parseInt(tot_seconds /60);
									var	tot_hours= parseInt(tot_mins / 60);
									
									if (tot_hours>0 ) {
										
										status_string=tot_hours + 'hour(s) '+parseInt(tot_mins % 60)+' minute(s) ' + parseInt(tot_seconds % 60) + ' seconds';
									}
									else if (tot_mins>0 ) {
										
										//if (tot_mins>60 ){
										//}
										//else{
											status_string=tot_mins+' minute(s) ' + parseInt(tot_seconds % 60) + ' seconds';
										}
									else if (tot_seconds>0 ) {
										
										status_string=tot_seconds + ' seconds';
										
									}
							
									throttle=parseInt($('#mycbgenie_throttle').val());
							
									
									if( 'done' == response.step ) { time_left_string =''; }
									else{
										completed=(( (response.step -1  )* $('#mycbgenie_throttle').val() ));
		
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
									//	if (answer!="resume") {		
											time_left_string='<br><font color=maroon>[ Estimated time left : ' + time_left + ' ]</font>';
										//}
									}
									$('#product_import_time').html('Time taken : '+ status_string + time_left_string );
							
								if( 'done' == response.step ) {
									//console.log(response.percentage);
										setProgress(100);
										
										modulus=total % parseInt($('#mycbgenie_throttle').val());
										
										if (modulus >0 ) { $step_final =parseInt(response.step_final) -1; }
										else	{ $step_final =parseInt(response.step_final) ;}
										
										completed=(( ( $step_final )* $('#mycbgenie_throttle').val() )+ modulus);
										
										$('#product_import_status').html('<i><font color=green>Performing some post-import activities.... <br>(should take up to 1-3 minutes of time)</font></i>');
										
										
											$.ajax({	
													type: 'POST',
													url: mycbgenie_manual_sync_vars.ajaxadminurl,
													data: {
														action 		:  'mycbgenie_after_import_update_term_count_action',
														process_type :	"fresh"
													},
													dataType: "json",
													success: function( response ) {	
											
														if (response.status == "OK"){
															
															$('#aad_loading').hide();
															$('#submit-btn').attr('disabled',true);
															
															$('#product_import_status').html('<i>'+addCommas(completed)+' product images imported out of '+ addCommas(total) +'</i>');
					
															$('#import_notice').hide();
															$('#product_import_final_status').html("<b><font color=green>Import process is over.</font></b>");		
															$('#submit-btn').hide();
										
															alert('Import process is completed    (' + response.time_taken +')'  );
															
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





										

										
										

													//setProgress(100);
													
													//$('#mycbgenie_form_id_ajax').remove();
												//	$('#mycbgenie_form_id_ajax_import_images').prepend("<b>"+ addCommas(total)+ " products imported successfully....</b>.<br> Proceed to product image import >><br>&nbsp;");
													//$('#mycbgenie_form_id_ajax_import_images').show();
													//$('#aad_loading').hide();
													//$('#submit-btn').attr('disabled',false);
													
					

									} else {
										
										setProgress(parseInt(response.percentage));
										completed=(( (response.step -1  )* $('#mycbgenie_throttle').val() )   );
										$('#product_import_status').html('<i>'+ addCommas(completed)+' products imported out of '+ addCommas(total) + '</i>');

										process_step( parseInt( response.step ), answer , response.batches, response.total,resume_first_step);
									}
					
							}
					}).fail(function(xhr, textStatus, errorThrown) {
						if ( window.console && window.console.log ) {
								$('#aad_loading').hide();
							//$('#submit-btn').attr('disabled',false);
							//$('#submit-btn').prop('value', 'Try Again');
							
							answer="resume";
							$('#product_import_status').html('<b><font color=red>Error... Server interrupted in half-way. Please refresh to resume. If the problem persists, please try with a lesser throttle value.</b></font>');
							console.log( xhr.responseText + textStatus + errorThrown  );
							alert('Error! '+ xhr.responseText + textStatus + errorThrown  );
							return false;
						}

					
				}); //ajax
				
			}//end of function process_step
			
		}	//end of function start_import_process
	
			
	
	}); //end of submit ON body tag
	
	//return false;
		
});
		
