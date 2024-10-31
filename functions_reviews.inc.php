<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function  mycbgenie_cdata($data)
{
    if (substr($data, 0, 9) === '<![CDATA[' && substr($data, -3) === ']]>') {
        $data = substr($data, 9, -3);
    }
    
    return $data;
}



function mycbgenie_product_review_import_fn()
{
    
   $idObj = get_category_by_slug( 'cs-product-reviews' );

    if ( $idObj instanceof WP_Term ) {
        $cs_cat_parent = $idObj->term_id;
       
    }else{
        
        $catarr = array('cat_name' => 'Product Reviews' , 'category_description' => 'ClickBank Product Reviews' , 'category_nicename' => 'cs-product-reviews' , 'category_parent' => '');
        $cs_cat_parent=wp_insert_category($catarr);
    }
        
        
    
    
    
    
    

    $user_id="15750";
    $items_per_page=8;
    
    $url = 'https://cbproads.com/xmlfeed/wp/main/cb_reviews.asp?show_content=yes&'
            . 'id='.$user_id
            . '&no_of_products='.(intval($items_per_page)-3);
            
     $url=$url."&".rand();
    //echo $url;
    
    $rss = fetch_feed($url);
    if (is_wp_error($rss)) return $empty_answer;
    
    if (0 == $rss->get_item_quantity(400)) return $empty_answer;

    
    $count = 0;
    $item_list = array();
    $items = $rss->get_items(0, 400);
   
    foreach ($items as $item) {
 
        $paths = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "title");
        $title = htmlspecialchars(mycbgenie_cdata($paths[0]['data']));
      
        
        $review_header = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "header_image");
        $review_image = mycbgenie_cdata($review_header[0]['data']);
        
        $mcats = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "mcat");
        $mcat = mycbgenie_cdata($mcats[0]['data']);
        
        $mcatcode = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "mccode");
        $mcatcd = mycbgenie_cdata($mcatcode[0]['data']);
        
        $scatcode = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "sccode");
        $scatcd = mycbgenie_cdata($scatcode[0]['data']);
        
        $scats = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "scat");
        $scat = mycbgenie_cdata($scats[0]['data']);
        
        $pdates = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "pdate");
        $pdate = mycbgenie_cdata($pdates[0]['data']);
        
        $sbtitle = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "sub_title");
        $stitle = mycbgenie_cdata($sbtitle[0]['data']);
        
         $review_desc = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "description");
        $review_descr = mycbgenie_cdata($review_desc[0]['data']);
        
        
        
        // Price
        $paths = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "price");
        $price = htmlspecialchars(mycbgenie_cdata($paths[0]['data']));
        
        // Content
        $contents = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, "content");
        $contentr = htmlspecialchars(mycbgenie_cdata($contents[0]['data']));
    
        
        
        
        //inserting child categories
        $idObj = get_category_by_slug( sanitize_title($mcat) );

        if ( $idObj instanceof WP_Term ) {
            $cs_sub_cat_id = $idObj->term_id;
            //print_r( $cs_sub_cat_id.'already exists'); 
           
        }else{
             $cs_sub_category=wp_insert_term(
                $mcat, // the term 
                'category', // the taxonomy
                array(
                    'description'=> $mcat,
                    'slug' => sanitize_title($mcat),
                    'parent'=> $cs_cat_parent
                )
            );
            
            $cs_sub_cat_id=$cs_sub_category['term_id'];
            //print_r( $cs_sub_cat_id.'iam fresh insert'); 
        }
 
        $post_id = wp_insert_post(
			array(
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	$author_id,
				'post_name'		    =>	sanitize_title($title),
				'post_title'		=>	htmlspecialchars_decode($title),
				'post_status'		=>	'publish',
				'post_type'		    =>	'post',
				'post_date'		    =>	date('Y-m-d H:i:s',strtotime($pdate)),
				'post_excerpt'		=>  htmlspecialchars_decode($review_descr),
				'post_content'      =>  htmlspecialchars_decode('<div id="cbpro-product-detail">'.$contentr.'</div>'),
				'post_category' => array($cs_sub_cat_id), //$cs_cat_parent, //give your category id
				
				//comments off or on...
			)
		);

        //print $post_id.$pdate; 
       mycbgenie_review_image($post_id,$review_image,'test.png');
        
        
    }



}


function mycbgenie_review_image($post_id,$image_url,$image_name)
{

//$image_url        = 'http://s.wordpress.org/style/images/wp-header-logo.png'; // Define the image URL here
$image_name       = 'wp-header-logo.png';
$upload_dir       = wp_upload_dir(); // Set upload folder
$image_data       = file_get_contents($image_url); // Get image data
$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
$filename         = basename( $unique_file_name ); // Create image file name

// Check folder permission and define file location
if( wp_mkdir_p( $upload_dir['path'] ) ) {
    $file = $upload_dir['path'] . '/' . $filename;
} else {
    $file = $upload_dir['basedir'] . '/' . $filename;
}

// Create the image  file on the server
file_put_contents( $file, $image_data );

// Check image file type
$wp_filetype = wp_check_filetype( $filename, null );

// Set attachment data
$attachment = array(
    'post_mime_type' => $wp_filetype['type'],
    'post_title'     => sanitize_file_name( $filename ),
    'post_content'   => '',
    'post_status'    => 'inherit'
);

// Create the attachment
$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

// Include image.php
require_once(ABSPATH . 'wp-admin/includes/image.php');

// Define attachment metadata
$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

// Assign metadata to attachment
wp_update_attachment_metadata( $attach_id, $attach_data );

// And finally assign featured image to post
set_post_thumbnail( $post_id, $attach_id );
}




?>
