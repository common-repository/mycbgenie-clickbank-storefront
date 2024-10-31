<?php



if  (isset($_REQUEST['action']) && $_GET['action']=='mycbgenie_store_view' ){



	$tracking_id	=	get_option('mycbgenie_cb_tracking_id');
	$account_id		=	get_option('mycbgenie_account_no');

	
	if ( trim($account_id)=='33333') {
		echo '<div align=center style="width:500px; border:1px solid grey; padding:20px; background:lightyellow;">
			It seems that you have not updated your <b>MyCbGenie Account #ID</b> in the settings page of the plugin.. <br><br>You cannot continue.
			</div>';

		exit;
	}
	
	$id=($_GET['id']);
	$url='http://mycbgenie.com/php/redirect/redirect.php?type=store&id='.$id.'&tracking_id='.$tracking_id.'&account_id='.$account_id."&code=".$_GET['code'];


wp_redirect($url,301); exit;
}

if  (isset($_REQUEST['action']) && $_GET['action']=='mycbgenie_review_view' ){



	$tracking_id	=	get_option('mycbgenie_cb_tracking_id');
	$account_id		=	get_option('mycbgenie_account_no');

	
	if ( trim($account_id)=='33333') {
		echo '<div align=center style="width:500px; border:1px solid grey; padding:20px; background:lightyellow;">
			It seems that you have not updated your <b>MyCbGenie Account #ID</b> in the settings page of the plugin.. <br><br>You cannot continue.
			</div>';

		exit;
	}
	
	$id=($_GET['id']);
	$url='http://mycbgenie.com/php/redirect/redirect.php?type=review&id='.$id.'&tracking_id='.$tracking_id.'&account_id='.$account_id."&code=".$_GET['code'];


wp_redirect($url,301); exit;
}

?>
