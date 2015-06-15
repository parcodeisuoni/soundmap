<?php
	/* Note: This thumbnail creation script requires the GD PHP Extension.  
		If GD is not installed correctly PHP does not render this page correctly
		and SWFUpload will get "stuck" never calling uploadSuccess or uploadError
	 */
require( '../../../../../../wp-load.php' );
//require_once( "FastJSON.class.php");
//require_once('getid3/getid3.php');

	// Get the session Id passed from SWFUpload. We have to do this to work-around the Flash Player Cookie Bug
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}

	session_start();
	
		// Check the upload
	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
		echo "ERROR:invalid upload";
		exit(0);
	}
	
	$file_a=array();
	
	$months_folders=get_option( 'uploads_use_yearmonth_folders' );
	$wud = wp_upload_dir();
	if ($months_folders){
		$destino=$wud['path'];
		$destino_url=$wud['url'];	
	}else{
		$destino=$wud['basedir'];
		$destino_url=$wud['baseurl'];
	}
	     
     $fileName = sanitize_file_name($_FILES["Filedata"]["name"]);
     
       //Check the directory.
    $months_folders=get_option( 'uploads_use_yearmonth_folders' );
    $wud = wp_upload_dir();
    if ($wud['error']){
        $rtn['error'] = $wud['error'];
        echo $rtn['error'];
      exit(0);
    }
    $targetDir = $wud['path'];
    $targetURL = $wud['url'];
    //check if the file exists.
    if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
	$ext = strrpos($fileName, '.');
	$fileName_a = substr($fileName, 0, $ext);
	$fileName_b = substr($fileName, $ext);

	$count = 1;
	while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
		$count++;

	$fileName = $fileName_a . '_' . $count . $fileName_b;
    }
    $tempDir = ini_get("upload_tmp_dir");
    //move it.
    $fileDir = $targetDir . DIRECTORY_SEPARATOR . $fileName;
    $fileURL = $targetURL . DIRECTORY_SEPARATOR . $fileName;
    //if(!rename($tempDir . DIRECTORY_SEPARATOR . $file_data['target_name'], $fileDir)){
    if(!move_uploaded_file($_FILES['Filedata']['tmp_name'], $fileDir)){
        $rtn['error'] = __('Error moving the file.','soundmap');
        echo $rtn['error'];
        exit(0);
    }
    
    $fileInfo = soundmap_get_id3info($fileDir);
    if(!$sound_attach_id = soundmap_add_media_attachment($fileDir, $fileURL))
        die();
        
    
    $rtn['attachment'] = $sound_attach_id;
    $rtn['length'] = $fileInfo['play_time'];
    $rtn['fileName'] = $fileName;
    $rtn['error'] = 0;
    $rtn['fileURL'] = $fileURL;
    $rtn['attachID'] = $sound_attach_id;
    echo json_encode($rtn);    
     
     
     
     
     
     
     
     
     
     
     
    /* 
     // guardamos el archivo a la carpeta files
     $destino .= "/" . $prefijo."_".$fileName;
     if (move_uploaded_file($_FILES['Filedata']['tmp_name'],$destino)) {
		chmod($destino, 0755);
		$file_a["error"]=0;
		$file_a["name"]=$_FILES["Filedata"]["name"];
		$file_a["file"]=$destino;
		$file_a["url"]=$destino_url . "/" . $prefijo."_".$nombre_seguro;
		$getID3 = new getID3;
		$ThisFileInfo = $getID3->analyze($destino);
        getid3_lib::CopyTagsToComments($ThisFileInfo);
        $file_a["duracion"]=$ThisFileInfo['playtime_string'];
        $file_a["bitrate"]=$ThisFileInfo['bitrate'];
		echo $json->encode($file_a);
     }else{
     	$file_a["error"]=1;
     	echo $json->encode($file_a);
     }*/
     
     
?>