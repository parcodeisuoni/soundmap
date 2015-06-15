<?php

require( '../../../../wp-load.php' );

global $SoinuMapa;
global $wpdb;


$filename=$_GET["fileName"];
$attachment=Array(
    "post_title"=>$filename,
    "post_content"=>"",
    "post_status"=>'inherit',
    "post_mime_type"=>'audio/mpeg',
    "guid"=>$_GET['fileURL']
);

$attach_id = wp_insert_attachment( $attachment, $filename);

$content = mountPostContent();
$title = mountPostTitle();
            
$post_d = array(
	'post_category' => $_GET['categoria'],
	'post_content' => $content,
	'post_status' => 'publish', 
	'post_title' => $title,
	'post_type' => 'post',
				  //'tags_input' => [ '<tag>, <tag>, <...>' ] //For tags.
);
			
$id=wp_insert_post($post_d);
add_post_meta($id, 'EMAIL', $_GET["email"], true);
$mark = Array(
	'type' => 'audio',
    'attachmentID' => $attach_id,
	'file' => $_GET['fileURL']
);

$record = mysql_real_escape_string(maybe_serialize($mark));

$lat=$_GET['posLat'];
$long=$_GET['posLong'];

$tabla = MARKERS_TABLE;

$q = "INSERT INTO $tabla (id, lat, lng, data) VALUES (\"$id\", \"$lat\", \"$long \", \"$record\" );";

$SoinuMapa->markers[$id]['lat']=$lat;
$SoinuMapa->markers[$id]['lng']=$long;
$SoinuMapa->markers[$id]['recording']=$mark;

$resultado=$wpdb->query($q);

if($resultado===false){

	echo "error";
}else{
	echo "ok";
}


function mountPostContent(){
		
	$t = "";
	
	if (function_exists("qtrans_init")){
		//we are using multilanguage;
		global $q_config;
			foreach ($q_config['enabled_languages'] as $key=>$value){
				$sel_Lang = $value;
				$lang_Name = $q_config['language_name'][$sel_Lang];
				$texts[$sel_Lang] =mountFileInfo($q_config["locale"][$sel_Lang], $sel_Lang);
			}
			$t = qtrans_join($texts);
							
	}
	return $t;	
}
function mountPostTitle(){
		
	$t = "";
	
	if (function_exists("qtrans_init")){
		//we are using multilanguage;
		global $q_config;
			foreach ($q_config['enabled_languages'] as $key=>$value){
				$sel_Lang = $value;
				$lang_Name = $q_config['language_name'][$sel_Lang];
				$texts[$sel_Lang] = $_GET["title_" . $sel_Lang];
			}
			$t = qtrans_join($texts);
							
	}
	return $t;	
}

function mountFileInfo($loc, $langu){
		
	$t = "";
	
		//we are using multilanguage;
		
	global $q_config;
	global $locale;
	$o_locale=$locale;
	$locale=$loc;
	$q_config['language']=$langu;
	load_plugin_textdomain("soinumapa");
	$t=__("Author: ","soinumapa") . $_GET["author"] . "<br>";
	$t.=__("Length: ","soinumapa") . $_GET["duracion"] . "<br>";
	$t.=__("Date: ","soinumapa") . $_GET["date"] . "<br>";
	$t.=__("Description: ","soinumapa") . $_GET["descripcion" . $langu] . "<br>";
	$locale=$o_locale;
	load_plugin_textdomain("soinumapa");								
	return $t;
}


?>