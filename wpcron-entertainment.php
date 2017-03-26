<?php
/**
 * WordPress Cron Implementation for hosts, which do not offer CRON or for which
 * the user has not set up a CRON job pointing to this file.
 *
 * The HTTP request to this file will not slow down the visitor who happens to
 * visit when the cron job is needed to run.
 *
 * @package WordPress
 */
//phpinfo();


 
require_once('wp-load.php');
require_once('wp-admin/includes/image.php');

function send_request($http_method, $endpoint, $auth_header=null, $postdata=null) {

  if( ($http_method == 'PUT' || $http_method == 'POST') ){
      if( is_null($postdata) ){
        print("Error: post data not set for PUT/POST method.\r\n");
        print_usage();
        exit(1);
      }
  } 

  $curl = curl_init($endpoint);  
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
  curl_setopt($curl, CURLOPT_FAILONERROR, false);  
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
  
  switch($http_method) {  
    case 'GET':
	  curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1090.0 Safari/536.6');
	  if ($auth_header) {  
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));   
      }  
      break;  
    case 'POST':  
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', $auth_header));   
      curl_setopt($curl, CURLOPT_POST, 1);                                         
      curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);  
      break;  
    case 'PUT':  
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', $auth_header));   
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);  
      curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);  
      break;  
    case 'DELETE':  
      curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));   
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);   
      break;  
  }
  
  $response = curl_exec($curl);  
  if (!$response) {  
    $response = curl_error($curl);  
  }  
  
  curl_close($curl);  
  return $response;  
}
function xml2array($contents, $get_attributes=1, $priority = 'tag') { 
    if(!$contents) return array(); 

    if(!function_exists('xml_parser_create')) { 
        //print "'xml_parser_create()' function not found!"; 
        return array(); 
    } 

    //Get the XML parser of PHP - PHP must have this module for the parser to work 
    $parser = xml_parser_create(''); 
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss 
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
    xml_parse_into_struct($parser, trim($contents), $xml_values); 
    xml_parser_free($parser); 

    if(!$xml_values) return;//Hmm... 

    //Initializations 
    $xml_array = array(); 
    $parents = array(); 
    $opened_tags = array(); 
    $arr = array(); 

    $current = &$xml_array; //Refference 

    //Go through the tags. 
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array 
    foreach($xml_values as $data) { 
        unset($attributes,$value);//Remove existing values, or there will be trouble 

        //This command will extract these variables into the foreach scope 
        // tag(string), type(string), level(int), attributes(array). 
        extract($data);//We could use the array by itself, but this cooler. 

        $result = array(); 
        $attributes_data = array(); 
         
        if(isset($value)) { 
            if($priority == 'tag') $result = $value; 
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode 
        } 

        //Set the attributes too. 
        if(isset($attributes) and $get_attributes) { 
            foreach($attributes as $attr => $val) { 
                if($priority == 'tag') $attributes_data[$attr] = $val; 
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
            } 
        } 

        //See tag status and do the needed. 
        if($type == "open") {//The starting of the tag '<tag>' 
            $parent[$level-1] = &$current; 
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
                $current[$tag] = $result; 
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 

                $current = &$current[$tag]; 

            } else { //There was another element with the same tag name 

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                    $repeated_tag_index[$tag.'_'.$level]++; 
                } else {//This section will make the value an array if multiple tags with the same name appear together 
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array 
                    $repeated_tag_index[$tag.'_'.$level] = 2; 
                     
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                        $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                        unset($current[$tag.'_attr']); 
                    } 

                } 
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
                $current = &$current[$tag][$last_item_index]; 
            } 

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
            //See if the key is already taken. 
            if(!isset($current[$tag])) { //New Key 
                $current[$tag] = $result; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data; 

            } else { //If taken, put all things inside a list(array) 
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 

                    // ...push the new element into that array. 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                     
                    if($priority == 'tag' and $get_attributes and $attributes_data) { 
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; 

                } else { //If it is not an array... 
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value 
                    $repeated_tag_index[$tag.'_'.$level] = 1; 
                    if($priority == 'tag' and $get_attributes) { 
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                             
                            $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                            unset($current[$tag.'_attr']); 
                        } 
                         
                        if($attributes_data) { 
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                        } 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken 
                } 
            } 

        } elseif($type == 'close') { //End of tag '</tag>' 
            $current = &$parent[$level-1]; 
        } 
    } 
     
    return($xml_array); 
}
function convertdata($arrayName = array(),$type){

	$arrayName = $arrayName['rss']['channel'];
	$image_data =array();
	$insert_item =array();
	for($j = 0 ;$j <count($arrayName['item']);$j++){
		/*echo "<pre>";print_r($arrayName['item'][$j]);exit;*/
		$items = $arrayName['item'][$j];
		/*echo "<pre>";print_R($items);exit;*/

		foreach($items as $key=>$value){
			if("media" == substr($key,0,5)){
				/* echo "<pre>";print_r($items[$key]);exit;*/
					if(!empty($items[$key])){
						/* echo "<pre>";print_r($items[$key]);exit;*/
						if(is_array($items[$key])){
							$dataxxxx =  found_object($items[$key]);
							$image_data = (array) $dataxxxx;
						}else{
							$image_data[] = (array) $items[$key];	
						}
			    		
					}

			  }
		}
		$items['media'] =json_encode($image_data);
		$dataaa[] =$items;
	}
	return $dataaa;	 
}
function found_object($data =array()){
	if(is_array($data)){
		$flag = '_attr';
		foreach ($data as $key => $value) {
			for($k=0;$k< count($data[$key]);$k++){
				$row = $data[$key];
				$keyu = $k.$flag;
				if(isset($row[$keyu])){	
					$data_jk[] = $row[$keyu];	
				}
			}
		}
		if(!isset($data_jk)){
			return $data;
		}
	}
	return $data_jk;
}
function strreplcecode($subject){
	$subject = descriptionremove($subject);
	$search=array('&#039;', '&ldquo;', '&rsquo;', '&rdquo;', '“', '”', '’');	
	return str_replace($search, '\'', $subject);	
	
}
function fetch_media($file_url, $post_id) {

global $wpdb;

if(!$post_id) {
    return false;
}

//directory to import to    
$artDir = 'wp-content/uploads/'.date("Y").'/'.date("m").'/';
 
if(!file_exists($artDir)) {
    mkdir($artDir);
}
 

$ext = array_pop(explode("/", $file_url));

$new_filename = 'blogmedia-'.$ext;

if (@fclose(@fopen($file_url, "r"))) {
	
    copy($file_url, ABSPATH.$artDir.$new_filename);

    $siteurl = get_option('siteurl');
    $file_info = getimagesize(ABSPATH.$artDir.$new_filename);

    
    $artdata = array();
    $artdata = array(
        'post_author' => 1, 
        'post_date' => current_time('mysql'),
        'post_date_gmt' => current_time('mysql'),
        'post_title' => $new_filename, 
        'post_status' => 'inherit',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $new_filename)),
        'post_modified' => current_time('mysql'),
        'post_modified_gmt' => current_time('mysql'),
        'post_parent' => $post_id,
        'post_type' => 'attachment',
        'guid' => $siteurl.'/'.$artDir.$new_filename,
        'post_mime_type' => $file_info['mime'],
        'post_excerpt' => '',
        'post_content' => ''
    );

    $uploads = wp_upload_dir();
    $save_path = $uploads['basedir'].'/'.date("Y").'/'.date("m").'/'.$new_filename;

    //insert the database record
    $attach_id = wp_insert_attachment( $artdata, $save_path, $post_id );


    if ($attach_data = wp_generate_attachment_metadata( $attach_id, $save_path)) {
        wp_update_attachment_metadata($attach_id, $attach_data);
    }


    $rows_affected = $wpdb->insert($wpdb->prefix.'postmeta', array('post_id' => $post_id, 'meta_key' => '_thumbnail_id', 'meta_value' => $attach_id));
}
else {
    return false;
}

	return true;
}

function getplink($itemlink){
	global $wpdb;
	$plink_table = $wpdb->prefix."plinks";
	$itemlink = str_replace("'","",$itemlink);
	$user_count = $wpdb->get_var( "SELECT link_id FROM $plink_table WHERE link_url LIKE '%".addslashes($itemlink)."%'");
	return $user_count;
}

function insertplink($itemlink,$post_id){
	global $wpdb;
	$plink_table = $wpdb->prefix."plinks";
	$itemlink = str_replace("'","",$itemlink);
	$sql = "INSERT INTO ".$plink_table." (link_url, link_pid,category) VALUES ('".addslashes($itemlink)."', ".$post_id.",'entertain');";    
	$wpdb->query($sql);
}


function getdplink($itemlink){
	global $wpdb;
	$plink_table = $wpdb->prefix."plinks";
	$itemlink = str_replace("'","",$itemlink);
	$user_count = $wpdb->get_row( "SELECT link_id,link_pid FROM $plink_table WHERE is_update=0 AND link_url LIKE '%".addslashes($itemlink)."%'");
	return $user_count;
}

function updateinsertplink($id){
	global $wpdb;
	$plink_table = $wpdb->prefix."plinks";
	$zz = $wpdb->query( $wpdb->prepare( "UPDATE $plink_table SET is_update = '%s'WHERE link_id = '%d'",1,$id) );
}

function descriptionremove($description){
	$description = preg_replace('/<p class="published(\d*)?">.+<\/p>/siU', '', $description);
	$description = preg_replace('/<p class="topics(\d*)?">.+<\/p>/siU', '', $description);
	$description = preg_replace('/<p class="article__meta(\d*)?">.+<\/p>/siU', '', $description);
	$description = preg_replace('/<div id="article-byline(\d*)?">.+<\/div>/siU', '', $description);
	
	$description = preg_replace('/<a[^<>]*?[^<>]*?>(.*?)<\/a>/', '', $description);
	/*$keyget = explode("http://redirect.viglink.com?key=",$description);
	$keyget = explode("&u=",$keyget[1]);
	$description = str_replace('http://redirect.viglink.com?key='.$keyget[0].'&u=http%3A%2F%2F', 'http://', $description);*/
	$description = html_entity_decode($description);
	return $description; 
}

function updatedesc($feed2){
	$feed = 'https://docs.wprssaggregator.com/fulltext/makefulltextfeed.php?url='.$feed2.'&max=10&links=preserve&exc=&submit=Create+Feed';
	if(!empty($feed)) {
		$res=send_request('GET', $feed);
		$data = xml2array($res);
		$Items_data = convertdata($data,$type=$rss_url[0]);
		if(count($Items_data ) > 0){
			foreach ($Items_data as $items) {
				$description = strreplcecode($items['description']);
			    $user_count = getdplink($items['link']);
				if(!empty($user_count)) {
					if(!empty($description)) {
						wp_update_post( array( 'ID' => $user_count->link_pid, 'post_content' => $description ) );
						updateinsertplink($user_count->link_id);
					}
				}
			}
		}
	}
}

/*ABC News*/

$feed = 'http://www.abc.net.au/news/feed/46800/rss.xml'; 
$term = term_exists('Entertainment', 'category');
if ($term == 0 && $term == null) {
  wp_insert_term( 'Entertainment', 'category');
  $term = term_exists('Entertainment', 'category');
}

if(!empty($feed)) {
	$res=send_request('GET', $feed);
	$data = xml2array($res);
	$Items_data = convertdata($data,$type=$rss_url[0]);
	
	if(count($Items_data ) > 0){
		foreach ($Items_data as $items) {
			$description = strreplcecode($items['description']);
			$title = addslashes($items['title']);
			date_default_timezone_set('America/New_York');		
			$pubDate = date("Y-m-d H:i:s", strtotime($items['pubDate']));
		    $plink_table = $wpdb->prefix."plinks";
			
		    $user_count = getplink($items['link']);
			if(empty($user_count)) {
				$my_post = array(
				  'post_title'    => wp_strip_all_tags( $title ),
				  'post_content'  => $description,
				  'post_date' => $pubDate,
				  'post_status'   => 'publish',
				  'post_author'   => 1,
				  'post_category' => array($term['term_id'])
				);
				$post_id =  wp_insert_post( $my_post );
				$mediaurl = $items['media:group']['media:content']['0_attr']['url'];
				wp_set_post_terms( $post_id, 40, 'news-provider-category');
				$td_post_theme_settings = array();
				$td_post_theme_settings['td_source'] =  'ABC News';
				$td_post_theme_settings['td_source_url'] =  $feed;
 				$td_post_theme_settings['td_via'] =  'ABC News';
				$td_post_theme_settings['td_via_url'] =  $items['link'];
				update_post_meta($post_id, 'td_post_theme_settings', $td_post_theme_settings, true);
				if(!empty($mediaurl)) {
					fetch_media($mediaurl,$post_id);
				}          
		        insertplink($items['link'],$post_id);
			}
		}
	}
}


/* 	BBC News */

$feed = 'http://feeds.bbci.co.uk/news/entertainment_and_arts/rss.xml';
if(!empty($feed)) {
	$res=send_request('GET', $feed);
	$data = xml2array($res);
	$Items_data = convertdata($data,$type=$rss_url[0]);
	
	if(count($Items_data ) > 0){
		foreach ($Items_data as $items) {
			$description = strreplcecode($items['description']);
			$title = addslashes($items['title']);
			date_default_timezone_set('GMT');		
			$pubDate = date("Y-m-d H:i:s", strtotime($items['pubDate']));
		    $plink_table = $wpdb->prefix."plinks";
		    $user_count = getplink($items['link']);
			if(empty($user_count)) {
				$my_post = array(
				  'post_title'    => wp_strip_all_tags( $title ),
				  'post_content'  => $description,
				  'post_date' => $pubDate,
				  'post_status'   => 'publish',
				  'post_author'   => 1,
				  'post_category' => array($term['term_id'])
				);
				$post_id =  wp_insert_post( $my_post );
				$mediaurl = $items['media:thumbnail_attr']['url'];
				wp_set_post_terms( $post_id, 38, 'news-provider-category');
				$td_post_theme_settings = array();
				$td_post_theme_settings['td_source'] =  'BBC News';
				$td_post_theme_settings['td_source_url'] =  $feed;
				$td_post_theme_settings['td_via'] =  'BBC News';
				$td_post_theme_settings['td_via_url'] =  $items['link'];
				update_post_meta($post_id, 'td_post_theme_settings', $td_post_theme_settings, true);
				if(!empty($mediaurl)) {
					fetch_media($mediaurl,$post_id);
				}          
		        insertplink($items['link'],$post_id);
			}
		}
	}
	
}

/* 	Reuters */

$feed = 'http://feeds.reuters.com/reuters/entertainment';
if(!empty($feed)) {
	$res=send_request('GET', $feed);
	$data = xml2array($res);
	$Items_data = convertdata($data,$type=$rss_url[0]);
	
	if(count($Items_data ) > 0){
		foreach ($Items_data as $items) {
			$description = strreplcecode($items['description']);
			$title = addslashes($items['title']);
			date_default_timezone_set('America/Anguilla');		
			$pubDate = date("Y-m-d H:i:s", strtotime($items['pubDate']));
		    $plink_table = $wpdb->prefix."plinks";
		    $user_count = getplink($items['link']);
			
			if(empty($user_count)) {
				$my_post = array(
				  'post_title'    => wp_strip_all_tags( $title ),
				  'post_content'  => strip_tags($description),
				  'post_date' => $pubDate,
				  'post_status'   => 'publish',
				  'post_author'   => 1,
				  'post_category' => array($term['term_id'])
				);
				$post_id =  wp_insert_post( $my_post );
				$mediaurl = '';
				wp_set_post_terms( $post_id, 34, 'news-provider-category');
				$td_post_theme_settings = array();
				$td_post_theme_settings['td_source'] =  'Reuters';
				$td_post_theme_settings['td_source_url'] =  $feed;
				$td_post_theme_settings['td_via'] =  'Reuters';
				$td_post_theme_settings['td_via_url'] =  $items['link'];
				update_post_meta($post_id, 'td_post_theme_settings', $td_post_theme_settings, true);
				if(!empty($mediaurl)) {
					fetch_media($mediaurl,$post_id);
				}          
		        insertplink($items['link'],$post_id);
			}
			
		}
	}
	
}

/* 	CBS News */

$feed = 'http://www.cbsnews.com/latest/rss/entertainment';
if(!empty($feed)) {
	$res=send_request('GET', $feed);
	$data = xml2array($res);
	$Items_data = convertdata($data,$type=$rss_url[0]);
	
	if(count($Items_data ) > 0){
		foreach ($Items_data as $items) {
			$description = strreplcecode($items['description']);
			$title = addslashes($items['title']); 
			date_default_timezone_set('America/Anguilla');		
			$pubDate = date("Y-m-d H:i:s", strtotime($items['pubDate']));
		    $plink_table = $wpdb->prefix."plinks";
		    $user_count = getplink($items['link']);
			if(empty($user_count)) {
				$my_post = array(
				  'post_title'    => wp_strip_all_tags( $title ),
				  'post_content'  => $description,
				  'post_date' => $pubDate,
				  'post_status'   => 'publish',
				  'post_author'   => 1,
				  'post_category' => array($term['term_id'])
				);
				$post_id =  wp_insert_post( $my_post );
				$mediaurl = str_replace('60x60/', '640x360/',$items['image']);
				wp_set_post_terms( $post_id, 41, 'news-provider-category');
				$td_post_theme_settings = array();
				$td_post_theme_settings['td_source'] =  'CBS News';
				$td_post_theme_settings['td_source_url'] =  $feed;
				$td_post_theme_settings['td_via'] =  'CBS News';
				$td_post_theme_settings['td_via_url'] =  $items['link'];
				update_post_meta($post_id, 'td_post_theme_settings', $td_post_theme_settings, true);
				if(!empty($mediaurl)) {
					fetch_media($mediaurl,$post_id);
				}          
		        insertplink($items['link'],$post_id);
			}
			
		}
	}
	
}

/* 	Fox News Channel */

$feed = 'http://feeds.foxnews.com/foxnews/entertainment';
if(!empty($feed)) {
	$res=send_request('GET', $feed);
	$data = xml2array($res);
	$Items_data = convertdata($data,$type=$rss_url[0]);
	
	if(count($Items_data ) > 0){
		foreach ($Items_data as $items) {
			$description = strreplcecode($items['description']);
			$title = addslashes($items['title']);
			date_default_timezone_set('GMT');
			$pubDate = date("Y-m-d H:i:s", strtotime($items['pubDate']));
		    $plink_table = $wpdb->prefix."plinks";
		    $user_count = getplink($items['link']);
			if(empty($user_count)) {
				$my_post = array(
				  'post_title'    => wp_strip_all_tags( $title ),
				  'post_content'  => $description,
				  'post_date' => $pubDate,
				  'post_status'   => 'publish',
				  'post_author'   => 1,
				  'post_category' => array($term['term_id'])
				);
				$post_id =  wp_insert_post( $my_post );
				if($items['media:group']['media:content']['0_attr']['url']){
					$mediaurl = $items['media:group']['media:content']['0_attr']['url'];
				} else {
					$mediaurl = '';
				}
				wp_set_post_terms( $post_id, 43, 'news-provider-category');
				$td_post_theme_settings = array();
				$td_post_theme_settings['td_source'] =  'Fox News Channel';
				$td_post_theme_settings['td_source_url'] =  $feed;
				$td_post_theme_settings['td_via'] =  'Fox News Channel';
				$td_post_theme_settings['td_via_url'] =  $items['link'];
				update_post_meta($post_id, 'td_post_theme_settings', $td_post_theme_settings, true);
				if(!empty($mediaurl)) {
					fetch_media($mediaurl,$post_id);
				}          
		        insertplink($items['link'],$post_id);
			}
		}
	}
	
}

/* CNN News */

$feed = 'http://rss.cnn.com/rss/edition_entertainment.rss';
if(!empty($feed)) {
	$res=send_request('GET', $feed);
	$data = xml2array($res);
	$Items_data = convertdata($data,$type=$rss_url[0]);
	 
	if(count($Items_data ) > 0){
		foreach ($Items_data as $items) {
			$description = strreplcecode($items['description']);
			$title = addslashes($items['title']);
			date_default_timezone_set('GMT');		
			$pubDate = date("Y-m-d H:i:s", strtotime($items['pubDate']));
		    $plink_table = $wpdb->prefix."plinks";
		   $user_count = getplink($items['link']);
			if(empty($user_count)) {
				$my_post = array(
				  'post_title'    => wp_strip_all_tags( $title ),
				  'post_content'  => $description,
				  'post_date' => $pubDate,
				  'post_status'   => 'publish',
				  'post_author'   => 1,
				  'post_category' => array($term['term_id'])
				);
				$post_id =  wp_insert_post( $my_post );
				$mediaurl = $items['media:group']['media:content']['0_attr']['url'];
				wp_set_post_terms( $post_id, 42, 'news-provider-category');
				$td_post_theme_settings = array();
				$td_post_theme_settings['td_source'] =  'CNN News';
				$td_post_theme_settings['td_source_url'] =  $feed;
 				$td_post_theme_settings['td_via'] =  'CNN News';
				$td_post_theme_settings['td_via_url'] =  $items['link'];
				update_post_meta($post_id, 'td_post_theme_settings', $td_post_theme_settings, true);
				if(!empty($mediaurl)) {
					fetch_media($mediaurl,$post_id);
				}          
		        insertplink($items['link'],$post_id);
			}
			
		}
	}
}

/* 	WSJ Online */

$feed = 'http://feeds.wsjonline.com/wsj/video/arts-and-entertainment/feed';
if(!empty($feed)) {
	$res=send_request('GET', $feed);
	$data = xml2array($res);
	$Items_data = convertdata($data,$type=$rss_url[0]);
	
	if(count($Items_data ) > 0){
		foreach ($Items_data as $items) {
			$description = strreplcecode($items['description']);
			$title = addslashes($items['title']);
			date_default_timezone_set('GMT');
			$pubDate = date("Y-m-d H:i:s", strtotime($items['pubDate']));
		    $plink_table = $wpdb->prefix."plinks";
			$items['link'] = trim($items['link']);
		    $user_count = getplink($items['link']);
			if(empty($user_count)) {
				$my_post = array(
				  'post_title'    => wp_strip_all_tags( $title ),
				  'post_content'  => $description,
				  'post_date' => $pubDate,
				  'post_status'   => 'publish',
				  'post_author'   => 1,
				  'post_category' => array($term['term_id'])
				);
				$post_id =  wp_insert_post( $my_post );
				preg_match('/< *img[^>]*src *= *["\']?([^"\']*).jpg>/i', $description, $matches);
				if($matches[1]){
					$mediaurl = str_replace('_167x94', '.jpg', $matches[1]);
				} else {
					$mediaurl = '';
				}
				wp_set_post_terms( $post_id, 63, 'news-provider-category');
				$td_post_theme_settings = array();
				$td_post_theme_settings['td_source'] =  'WSJ Online';
				$td_post_theme_settings['td_source_url'] =  $feed;
				$td_post_theme_settings['td_via'] =  'WSJ Online';
				$td_post_theme_settings['td_via_url'] =  $items['link'];
				update_post_meta($post_id, 'td_post_theme_settings', $td_post_theme_settings, true);
				if(!empty($mediaurl)) {
					fetch_media($mediaurl,$post_id);
				}          
		        insertplink($items['link'],$post_id);
			}
		}
	}
}

/*only  Description*/
updatedesc('www.abc.net.au/news/feed/46800/rss.xml');
updatedesc('feeds.bbci.co.uk/news/entertainment_and_arts/rss.xml');
updatedesc('feeds.reuters.com/reuters/entertainment');
updatedesc('www.cbsnews.com/latest/rss/entertainment');
updatedesc('feeds.foxnews.com/foxnews/entertainment');
updatedesc('rss.cnn.com/rss/edition_entertainment.rss');
updatedesc('feeds.wsjonline.com/wsj/video/arts-and-entertainment/feed');

echo "Entertainment category success";
exit;


?>