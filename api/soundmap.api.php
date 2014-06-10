<?php

if (!class_exists('Soundmap_Helper')){

	class Soundmap_Helper{

		function get_all_markers(){
			//coge todos los marcadores.
			$query = new WP_Query(array(
            	'post_type' => 'marker',
	            'post_status' => 'publish',
	            'posts_per_page' => -1
    	        )
        	);

		  if ( !$query->have_posts() )
				return false;

			foreach($query->posts as &$post){
				$post_id = $post->ID;
				$custom = get_post_custom($post_id);
				$post->marker = array();
				if(isset($custom['soundmap_marker_lat']))
					$post->marker['lat'] = $custom['soundmap_marker_lat'];

				if(isset($custom['soundmap_marker_lng']))
					$post->marker['lng'] = $custom['soundmap_marker_lng'];

				if(isset($custom['soundmap_marker_author']))
					$post->marker['author'] = $custom['soundmap_marker_author'];

				if(isset($custom['soundmap_marker_date']))
					$post->marker['date'] = $custom['soundmap_marker_date'];

				if(isset($custom['soundmap_attachments_id']))
					$post->marker['attachments'] = $custom['soundmap_attachments_id'];
			}

		    return $query->posts;

		}

		function get_marker($id){

			//coge todos los marcadores.
			$q = get_post($id);
			global $soundmap;

		    if ( !$q )
				return false;

			$post_id = $q->ID;
			$custom = get_post_custom($post_id);
			$q->marker = array();
			$q->marker['lat'] = $custom['soundmap_marker_lat'];
			$q->marker['lng'] = $custom['soundmap_marker_lng'];
			$q->marker['author'] = $custom['soundmap_marker_author'];
//			$q->marker['date'] = $custom['soundmap_marker_date'];
			$files = $custom['soundmap_attachments_id'];

			$info = array();
		    foreach ($files as $key => $value){
				$file = array();
				$att = get_post($value);
				$file['id'] = $value;
				$file['fileURI'] = wp_get_attachment_url($value);
				$file['filePath'] = get_attached_file($value);
//				$file['info'] = $soundmap->getID3($file['filePath']);
				$file['name'] = $att->post_name;
				$file['file_name'] = basename($file['filePath']);
				$info[] = $file;
		    }
    		$q->marker['attachments'] = $info;
		    return $q;

		}


	}//Soundmap_Helper

}


if (!class_exists('Soundmap_AJAX')){

    class Soundmap_AJAX{


        function __construct(){
            //Register hooks and filters
            $this->register_hooks();
        }

        function register_hooks(){
            add_action('wp_ajax_get-markers-collection', array($this, 'get_markers_collection'));
        }

        function get_markers_collection(){

//            $query = new WP_Query(array('posts_per_page' => -1,'cat' => 4, 'post_type' => 'marker', 'post_status' => 'publish'));
            $query = new WP_Query(array('posts_per_page' => -1,'post_type' => 'marker', 'post_status' => 'publish'));
            if ( !$query->have_posts() )
                die();
            $posts = $query->posts;
            $markers_collection = array();

            foreach($posts as $post){
                $post_id = $post->ID;
                $m_lat = get_post_meta($post_id,'soundmap_marker_lat', TRUE);
                $m_lng = get_post_meta($post_id,'soundmap_marker_lng', TRUE);
                $title = get_the_title ($post_id);
                $autor = get_post_meta($post_id, 'soundmap_marker_author', TRUE);
                $files = get_post_meta($post_id, 'soundmap_attachments_id', FALSE);
                $feature = new stdClass();
                $feature->id = $post_id;
                $feature->lat = $m_lat;
                $feature->lng = $m_lng;
                $feature->title = $title;
                $feature->autor = $autor;
                $feature->files = $files[0];
                $markers_collection[] = $feature;
            }
            wp_send_json_success($markers_collection);
            die();
        }



    }//Soundmap_AJAX

}

global $soundmap_AJAX;
$soundmap_AJAX = new Soundmap_AJAX();
