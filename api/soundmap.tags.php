<?php

/**
 * 
 * 
 * @param <type> $query  
 * 
 * @return <type>
 */
function insert_map(  $query = undefined  )  {
    if(  is_array(  $query  )  )  {
        // Tenemos una consulta propia, por lo que vamos a cargar los marcadores.

        $options = array(
            'post_type' => 'marker',
            'post_status' => 'publish',
        );

        $options = array_merge(  $options, $query  );
        $_q = new WP_Query(  $options  );
        var_dump(  $_q->found_posts  );

    }
}

/**
 * 
 * 
 * @param <type> $id   
 * 
 * @return <type>
 */
function get_the_latitude(  $id  )  {
    return get_post_meta(  $id,  'soundmap_marker_lat', TRUE  );
}

/**
 * 
 * 
 * @param <type> $id   
 * 
 * @return <type>
 */
function get_the_longitude(  $id  )  {
    return get_post_meta(  $id,'soundmap_marker_lng', TRUE  );
}

/**
 * 
 * 
 * @param string $css_id  
 * @param bool $all_markers  
 * @param array $options  
 * 
 * @return <type>
 */
function the_map(  $css_id = 'map_canvas', $all_markers = FALSE, $options = array()  )  {

    global $soundmap; ?>

    <div class="<?php echo $css_id ?> soundmap-canvas">
        <div style="height:100%;">

        </div>
    </div>
    <div id="hidden-markers-content" style ="display:hidden;"></div>
	
    <?php
    if ( $all_markers ) {
        // load all markers
        $soundmap->set_query( 'all' );

    } else {

        global $posts;
		
		//Array with the posts to show
        if ( is_array( $posts ) ) {
            $list = array();
            foreach ( $posts as $post ) {
                array_push( $list, $post->ID );
            }
        }
        if ( !is_array( $list ) )
            $soundmap->set_query( $list );
    }
}

/**
 * 
 * 
 * @param <type> $pre  
 * @param <type> $sep  
 * @param <type> $after  
 * 
 * @return <type>
 */
function the_marker_info( $pre = '<ul><li class="post-info">', $sep = '</li><li>', $after = '</li></ul>' ) {
    global $post;
    $marker_id = $post->ID;

    if ( $post->post_type != "marker" )
        return;

    $author = get_post_meta( $marker_id, 'soundmap_marker_author', TRUE );
    $date = get_post_meta( $marker_id, 'soundmap_marker_date', TRUE );
    echo $pre;
    echo $author;
    echo $sep;
    echo $date;
    echo $after;

}



/**
 * 
 * 
 * @param <type> $id  
 * 
 * @return <type>
 */
function get_marker_author( $id ){

    return get_post_meta( $id, 'soundmap_marker_author', TRUE );

}


/**
 * 
 * 
 * @param <type> $id  
 * 
 * @return <type>
 */
function get_marker_date( $id ){

    return get_post_meta( $id, 'soundmap_marker_date', TRUE );

}

/**
 * 
 * 
 * @param <type> $marker_id  
 * 
 * @return <type>
 */
function the_player( $marker_id ) {
	
	global $soundmap;

    $data = array();
    $files = get_post_meta( $marker_id, 'soundmap_attachments_id', FALSE );
    foreach ( $files as $key => $value ) {
        $file = array();
        $att = get_post( $value );
        $file['id'] = $value;
        $file['fileURI'] = wp_get_attachment_url( $value );
        $file['filePath'] = get_attached_file( $value );
        $file['info'] = $soundmap->getID3( $file['filePath'] );
        $file['name'] = $att->post_name;
        $data['m_files'][] = $file;
    }
    add_player_interface( $data['m_files'], $marker_id );
}


/**
 * 
 * 
 * @param <type> $text  
 * 
 * @return <type>
 */
function insert_upload_form( $text ){

	if (function_exists( "qtrans_init" ) ) {
		//We are using multilanguage
		global $q_config;
		$lang=$q_config['language'];
		$dir = WP_PLUGIN_URL . "/soundmap/modules/module.soundmap.upload.php?lang=$lang&TB_iframe=true&width=960&height=550";
	} else {
		$dir = WP_PLUGIN_URL . "/soundmap/modules/module.soundmap.upload.php?TB_iframe=true&width=960&height=550";
	}
	$t="";
	$title=__( "Add new recording", "soundmap" );
	$t .="<a class=\"thickbox\" title=\"$title\" href=\"$dir\">$text</a>";
	echo $t;
}

/**
 * 
 * 
 * 
 * @return <type>
 */
function soundmap_rss_enclosure() {

    $id = get_the_ID();

    $files = get_post_meta( $id, 'soundmap_attachments_id', FALSE );

    foreach ( (array) $files as $key => $value ) {

        $fileURI= wp_get_attachment_url( $value );
        $info= soundmap_get_id3info( $file['filePath']);
        echo apply_filters(
			  'rss_enclosure', '<enclosure url="'
			. trim( htmlspecialchars( $fileURI ) )
			. '" length="'
			. trim( $info['playtime_string'] )
			. '" type="audio/mpeg" />'
			. "\n"
		);

    }
}


/**
 * 
 * 
 * @param <type> $files 
 * @param <type> $id  
 * 
 * @return <type>
 */
function add_player_interface( $files, $id ) {

    global $soundmap;
    $out = '';
    if( !is_array( $files ) || ( count( $files ) == 0 ) )
        return;

    if( count( $files ) == 1 ) {
        $fileInfo = $soundmap->getID3( $files[0]['filePath'] );
        $script = '';
        if ( is_array( $fileInfo ) ) {
            switch ( $fileInfo['format'] ) {
                case 'ogg':
                case 'mp3':
                    $script = "<script type='text/javascript'>soundMapPlayer.create_player('audio', '{$fileInfo['format']}', '{$files[0]['fileURI']}', 'soundmap-player-{$id}', '{$id}');</script>";
                break;
            }
        $out = "<div id='soundmap-player-{$id}'></div>{$script}";
        }

    }

    foreach ( $files as $file ) {

    }

    return $out;
	// global $soundmap_Player;

	// $insert_content = $soundmap_Player->print_audio_content($files, $id);
    //echo $insert_content;

}
