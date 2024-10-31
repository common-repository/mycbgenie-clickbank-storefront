<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'woocommerce_product_add_to_cart_url', 'woocommerce_externalProducts_openInNewTab' );

function mycbgenie_url($id){
	//echo $id;
	$ciphering = "AES-128-CTR";
	$iv_length = openssl_cipher_iv_length($ciphering);
	$options = 0;
	$encryption_iv = '1234567891011121';
	$encryption_key = "mycbgenie";
	$encrypt=openssl_encrypt($id, $ciphering,
				$encryption_key, $options, $encryption_iv);
	$encrypt=str_replace("+","*",$encrypt);
	//echo $encrypt;
	return $encrypt;
}

function mycbgenie_id($id){
	$newdigit1= substr(rand(pow(10, 0), pow(10, 1)-1),-1);
	$newdigit2= substr(rand(pow(10, 0), pow(10, 1)-1),-1);
	return str_replace($newdigit1,$newdigit2,$id).".".substr(rand(pow(10, 2), pow(10, 3)-1),0,3);
}
function woocommerce_externalProducts_openInNewTab($product_url) {

    global $product;
	if ( is_a( $product, 'WC_Product' ) ) {

		if (			( $product->is_type('external') )  && (get_post_meta( $product->id, '_mycbgenie_managed_by', true )==='mycbgenie')				 ) {
		$id=get_post_meta( $product->id, '_mycbgenie_id', true );	
			//echo get_post_meta( $product->id, '_mycbgenie_managed_by', true );
		$newurl=str_replace($id,mycbgenie_id($id),$product->get_product_url())."&code=".mycbgenie_url($id);
		}else{  return $product_url;}
	}else{  return $product_url;}
    return ($newurl);

}
?>
