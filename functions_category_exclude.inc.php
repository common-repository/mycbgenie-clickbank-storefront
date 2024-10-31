<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}




function mycbgenie_exclude_categories()
{

?>

	<!--div for displaying result confirmation -->
	<div class="updated" id="ajax-result_settings_exclude_category" style="display:none;" ></div>

	<form action="" method=post id="mycbgenie_form_settings_exclude_terms_ajax">


	<div id="taxonomy-product_cat" class="categorydiv" >

	<div id="product_cat-all" class="tabs-panel" >
	<ul id="product_catchecklist" data-wp-lists="list:product_cat" class="categorychecklist form-no-clear">
	
	<?php

	$taxonomy = 'product_cat';
	$orderby = 'name';
	$show_count = 0; // 1 for yes, 0 for no
	$pad_counts = 0; // 1 for yes, 0 for no
	$hierarchical = 1; // 1 for yes, 0 for no
	$title = '';
	$empty = 0;
	
	$args = array(
	'taxonomy' => $taxonomy,
	'orderby' => $orderby,
	'show_count' => $show_count,
	'pad_counts' => $pad_counts,
	'hierarchical' => $hierarchical,
	'title_li' => $title,
	'hide_empty' => $empty
	);

	$all_categories = get_categories( $args );


	$exclude_cats = get_option( 'mycbgenie_excluded_terms'  );
	//update_option( 'mycbgenie_excluded_terms' ,array());

//var_dump($exclude_cats);

	foreach ($all_categories as $cat)
	{
	
	//echo "hi";

		if($cat->category_parent == 0)
		{
			$category_id = $cat->term_id;
			//echo "<ul class='category' style='font-size:15px; font-weight:bold;'><li>".$cat->name."</li></ul>";
			//print_r (get_option('mycbgenie_main_cat'));
			
			//mycbgenie category check... Show only imported categories.(not all woo categories)
			//var_dump(get_option('mycbgenie_imported_main_terms'));
	//secho $cat->slug."<br>";			
	
			$mycbgenie_imported_arr	= get_option('mycbgenie_imported_main_terms');
	//var_dump($mycbgenie_imported_arr);
				$tmp_push=array();
				foreach ( $mycbgenie_imported_arr as $mycbgenie_imported_array) {
				//echo $include_main_arr['slug'].'<br>';
				array_push($tmp_push, $mycbgenie_imported_array['slug']);
				}
				
			$mycbgenie_imported_arr	=	$tmp_push;
	//var_dump($mycbgenie_imported_arr);				
					
				//echo $cat->slug."->";
				
			if (	in_array(	$cat->slug, $mycbgenie_imported_arr	,	true)	) { 
			
			?>
			
			<hr />
			<li id='<?php echo $cat->slug ?>'><label class="selectit">
			<input value="<?php echo $cat->slug ?>" type="checkbox" 
			<?php if (in_array($cat->slug, $exclude_cats, true)) { echo "checked"; } ?> name="tax_input[]" 
			 /> <?php echo $cat->name ?></label>
				
	
				<?php
				
				$args2 = array(
				'taxonomy' => $taxonomy,
				'child_of' => 0,
				'parent' => $category_id,
				'orderby' => $orderby,
				'show_count' => $show_count,
				'pad_counts' => $pad_counts,
				'hierarchical' => $hierarchical,
				'title_li' => $title,
				'hide_empty' => $empty
			
				 );
						$sub_cats = get_categories( $args2 );
						if($sub_cats)
						{
							echo "<ul class='children'>";
							foreach($sub_cats as $sub_category)
							{
							//var_dump($sub_cats);
								//if (){
									//if($sub_cats->$sub_category == 0){
									//if($sub_cats[$sub_category] == 0){
									?>
									<li id='<?php echo $sub_category->slug ?>'><label class="selectit">
									<input value="<?php echo $sub_category->slug ?>" type="checkbox"  
									<?php if (in_array($sub_category->slug, $exclude_cats, true)) { echo "checked"; } ?> name="tax_input[]" />
									<?php echo $sub_category->cat_name ?></label></li>
									<?php
									//}
								//}	
							}
				
							echo "</ul>"; //ul of class childresn
						}
						
						
						
			echo "</li>"; //li close of main cat
			} // close of mycbgenie category check
		}
	}


	echo "</ul>";
	//var_dump(get_option('mycbgenie_excluded_terms'));
	?>

	</div>
	<br>
	<div>
	<input class="button-primary" id='submit-btn-category-exclude' type=submit  value="Update" />
	</form>
	</div>
	</div>
<?php	
}






function mycbgenie_ajax_exclude_category_function()
{

	if ( !isset($_POST['mycbgenie_cat_exclude_nonce']) || !wp_verify_nonce( $_POST['mycbgenie_cat_exclude_nonce'], "local_cat_exclude_mycbgenie_nonce")) {

   		  exit("No naughty business please");
	} 
	
	$exclude_cats=($_POST['tax_input']);
	
	if ( ! is_array($exclude_cats))		{ $exclude_cats=array();	}

	if (get_option( 'mycbgenie_excluded_terms') ==  $exclude_cats ) 
	{
		echo  "<b><div><br>Nothing is updated. No changes are noticied.</b></div><br>";
		}
		else
		{
			if (update_option( 'mycbgenie_excluded_terms', $exclude_cats )) {	
				echo "<b><div><br>Updated Successfully.</b></div><br>";
			}
			else{
				echo "<b><div><br>Error!   Update Failed.</b></div><br>";
			}
	}
	die();
}


function mycbgenie_set_exclude_terms()
{
	//remove excluded categories from displaying on widget
	add_filter( 'get_terms', 'mycbgenie_remove_excluded_terms_from_widget' ,10,3);
	add_action( 'pre_get_posts', 'mycbgenie_remove_excluded_term_products_from_entire_shop' );
}





//remove excluded term products showing on shop page
function mycbgenie_remove_excluded_term_products_from_entire_shop( $q ) {


	if ( ! $q->is_main_query() ) return;
//	if ( ! $q->is_post_type_archive() ) return;
	

	if ( ! is_admin()  ) {  // && is_shop()

		$q->set( 'tax_query', array(array(
			'taxonomy' => 'product_cat',
			'field' => 'slug',
			'terms' => get_option('mycbgenie_excluded_terms'), 
			'operator' => 'NOT IN'
		)));
	}

	remove_action( 'pre_get_posts', 'custom_pre_get_posts_query' );

}



//remove excluded terms showing on product categories widget
function mycbgenie_remove_excluded_terms_from_widget( $terms, $taxonomies, $args ) {

if (get_option('mycbgenie_excluded_terms'))
{

	global $mycbgenie_import_mode;
  $new_terms = array();
 
  // if a product category and on the shop page
   if ( in_array( 'product_cat', $taxonomies , true ) && (! is_admin()) && ($mycbgenie_import_mode!="yes") ) {// && is_shop() ) {
 
    foreach ( $terms as $key => $term ) {
 
      if ( ! in_array( $term->slug, get_option('mycbgenie_excluded_terms'),  true ) ) 
	  {
        $new_terms[] = $term;
      }

    }
 
    $terms = $new_terms;
  }
}



  return $terms;
}


	
?>
