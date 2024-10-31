<?php
function mycbgenie_change_woocommerce_category_page_title( $page_title )
{
	if ( is_product_category() || (is_search()) ) {
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		//if detox
		if 	(stripos($page_title,"DETOX") > 0) { return ($paged==1? "Body Detoxification": "Body Detoxification") ;   }
		elseif 	(stripos($page_title,"DIABET") > 0) { return ($paged==1? "How To Manage Diabetes": "Manage Diabetes") ;   }
		elseif 	(stripos($page_title,"KETO") > 0) { return ($paged==1? "What is a Ketogenic Diet Plan": "Ketogenic Diet");   }
		elseif 	(stripos($page_title,"VEGAN") > 0) { return ($paged==1? "What is a Vegan Diet ": "Vegan Diet Programs");   }
		elseif 	(stripos($page_title,"PALEO") > 0) { return ($paged==1? "How a Paleo diet is different?": "Paleo Diet Programs");   }
		elseif 	(stripos($page_title,"ALKALINE") > 0) { return "Alkaline Diet Programs" ;   }
		elseif 	(stripos($page_title,"Mediterranean") > 0) { return "Mediterranean Diet Programs" ;   }
		elseif 	(stripos($page_title,"gluten") > 0) { return "Gluten Free Diet Programs" ;   }
		elseif 	(stripos($page_title,"Anabolic") > 0) { return "Anabolic Diet Programs" ;   }
		else {return $page_title;}
	}
}


function mycbgenie_check_special_search(){
	
	if 	(stripos($_SERVER['REQUEST_URI'],"s=keto&post_type=product") > 0) { 
		return "keto";
	}
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=paleo&post_type=product") > 0) { 
		return "paleo";
	}
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=vegan&post_type=product") > 0) { 
		return "vegan";
	}
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=alkaline&post_type=product") > 0) { 
		return "alkaline";
	}
	
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=mediterranean&post_type=product") > 0) { 
		return "mediterranean";
	}
	
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=gluten&post_type=product") > 0) { 
		return "gluten";
	}
	
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=anabolic&post_type=product") > 0) { 
		return "anabolic";
	}
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=detox&post_type=product") > 0) { 
		return "detox";
	}
	elseif 	(stripos($_SERVER['REQUEST_URI'],"s=diabet&post_type=product") > 0) { 
		return "diabetes";
	}
	else { return "not_special_search";}
	
}



function mycbgenie_remove_shop_crumb( $crumbs, $breadcrumb ){
	
	
	if (mycbgenie_check_special_search() !="not_special_search") {
		unset( $crumbs[2]);
		if ($crumbs[1][0]=='Shop') $crumbs[1][0]="Info-Products";
    	//foreach( $crumbs as $key => $crumb ){
		 //   unset($crumbs[$key]);
       //}
    }else{
		//
	}
	
    return $crumbs;
}




function mycbgenie_special_search_header() {
	
    if(is_search() || is_product_category()) {
			//get page number of the current
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		  
			$div_start= "<div style=' border: 0px solid #eef1fd; background:white; padding:7% 5% 3% 5%;   '>";
		  
		   //$div_highlight_start="<div style=' background: #D3D3D3; background: radial-gradient(circle, #f3f3f3 0%, rgba(255,255,255,1) 100%); padding:10px; padding-bottom:0px; border-radius:7px;'>";
				
		   $div_recommended_start='<br><br><div align="center" id="cs_heading_title" class="six" >';
			$div_recommended_end='</div>';
		 

			if ($paged==1) {
				if (mycbgenie_check_special_search()=="keto")  { 
					echo $div_start;
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/keto/keto1.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/keto/keto2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo $div_recommended_start."<h4>Best Keto diet programs<span>Featuring the best Keto diet products listed on ClickBank&reg;</span> </h4>".$div_recommended_end;
					echo $div_highlight_start;
				}
				elseif 	(mycbgenie_check_special_search()=="paleo") { 
					echo $div_start;
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/paleo/paleo1.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/paleo/paleo2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo $div_recommended_start."<h4>Best Paleo diet programs<span>Featuring the best Paleo diet products listed on ClickBank&reg;</span></h4>".$div_recommended_end;
					echo $div_highlight_start;
				}
				elseif 	(mycbgenie_check_special_search()=="vegan")  { 
					echo $div_start;
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/vegan/vegan1.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/vegan/vegan2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo $div_recommended_start."<h4>Best Vegan diet programs<span>Featuring the best Vegan diet products listed on ClickBank&reg;</span> </h4>".$div_recommended_end;
					echo $div_highlight_start;
				}
				elseif 	(mycbgenie_check_special_search()=="detox")  { 
					echo $div_start;
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/detox/detox1.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/detox/detox2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo $div_recommended_start."<h4>Best Body detox programs<span>Featuring the best body detoxification products listed on ClickBank&reg;</span> </h4>".$div_recommended_end;
					echo $div_highlight_start;
				}
				elseif 	(mycbgenie_check_special_search()=="diabetes")  { 
					echo $div_start;
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/diabetes/diabetes1.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/diabetes/diabetes2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo $div_recommended_start."<h4>Best Diabetes management programs<span>Featuring the best Diabetes programs listed on ClickBank&reg;</span> </h4>".$div_recommended_end;
					echo $div_highlight_start;
				}
			}

		
    }
}


function mycbgenie_special_search_content() {
	
    if(is_search() || is_product_category()) {
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			//$div_highlight_end="</div><br>";
		
			if ($paged==1) {
				//echo "</div>"; // div for highlighted.
				if (mycbgenie_check_special_search()=="keto")  { 
					echo $div_highlight_end;
					//echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/keto/keto2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo "</div>";
				}
				elseif 	(mycbgenie_check_special_search()=="paleo") { 
					echo $div_highlight_end;
					//echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/paleo/paleo2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo "</div>";
				}
				elseif 	(mycbgenie_check_special_search()=="vegan")  { 
					echo $div_highlight_end;
					//echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/vegan/vegan2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo "</div>";
				}
				elseif 	(mycbgenie_check_special_search()=="detox")  { 
					echo $div_highlight_end;
					//echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/detox/detox2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo "</div>";
				}
				elseif 	(mycbgenie_check_special_search()=="diabetes")  { 
					echo $div_highlight_end;
					//echo file_get_contents("https://cbproads.com/xmlfeed/WP/main/special_searches/diabetes/diabetes2.asp?theme=".$_SESSION['cs_theme_chosen']);
					echo "</div>";
				}
			}
    }
}

?>