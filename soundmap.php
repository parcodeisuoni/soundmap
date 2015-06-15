<?php
/**
 * Plugin Name: Soundmap
 * Plugin URI: http://audio-lab.org/es/argitalpenak/software/soundmap-plugin/
 * Description: New version of the Soinumapa Plugin for creating sound maps
 * Author: Xavier Balderas
 * Author URI: http://www.xavierbalderas.com
 * Contributors: parcodeisuoni, codiceovvio
 * Version: 0.5
 * Text Domain: soundmap
 * Domain Path: languages
 *
 * Soundmap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Soundmap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Soundmap. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Soundmap
 * @author Xavier Balderas
 * @version 0.6
 *
 */


/**
 * Require the complete getID3 class before loading soundmap environment
 * Wordpress didn't include the entire GetID3 library when they implemented it.
 * They only used the files to get the most popular video and audio tags.
 *
 * @link http://stackoverflow.com/questions/18267123/
 *
 */
if ( ! class_exists( 'getID3' ) ) {
    require( WP_PLUGIN_DIR . '/soundmap/api/getID3/getid3.php' );
}

/**
 * Require Soundmap API and template tags
 *
 */
require_once ( WP_PLUGIN_DIR . "/soundmap/api/soundmap.tags.php" );
require_once ( WP_PLUGIN_DIR . "/soundmap/api/soundmap.api.php" );


/**
 * Soundmap class
 *
 * @since 0.5
 */
if ( ! class_exists( 'Soundmap' ) ) {

    class Soundmap {

        public $config = array();

        /**
         * Constructor.
         *
         * Register hooks and filters
         */
        function __construct() {
            $this->register_hooks();
            $this->register_filters();
        }

		/**
		 * Register marker post_type
		 */
        function register_content_type() {
            $labels = array(
                    'name'               => __( 'Markers', 'soundmap' ),
                    'singular_name'      => __( 'Marker', 'soundmap' ),
                    'add_new'            => _x( 'Add New', 'Marker', 'soundmap' ),
                    'add_new_item'       => __( 'Add New Marker', 'soundmap' ),
                    'edit_item'          => __( 'Edit Marker', 'soundmap' ),
                    'new_item'           => __( 'New Marker', 'soundmap' ),
                    'all_items'          => __( 'All Markers', 'soundmap' ),
                    'view_item'          => __( 'View Marker', 'soundmap' ),
                    'search_items'       => __( 'Search Markers', 'soundmap' ),
                    'not_found'          => __( 'No Markers found', 'soundmap' ),
                    'not_found_in_trash' => __( 'No Markers found in Trash', 'soundmap' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Markers' ),
            );

            $args = array(
                    'labels'          => $labels,
                    'public'          => true,
                    'publicly_queryable' => true,
                    'show_ui'         => true,
                    'show_in_menu'    => true,
                    'query_var'       => true,
                    'rewrite'         => array( 'slug' => 'marker', 'with_front' => false ),
                    'capability_type' => 'post',
                    'has_archive'     => true,
                    'hierarchical'    => false,
                    'menu_position'   => 5,
                    'register_meta_box_cb' => array( $this, 'metaboxes_register_callback' ),
                    'supports' => array( 'title', 'editor', 'thumbnail' )
            );
            register_post_type( 'marker', $args );
			
			/* assign default categories and tags to markers */
			/* @TODO register and assign specific marker's taxonomies */
            register_taxonomy_for_object_type( 'category', 'marker' );
            register_taxonomy_for_object_type( 'post_tag', 'marker' );

        } // register_content_type

        /**
         *
         */
		
        function init() {
            $this->config['on_page'] = FALSE;
            $this->load_options();
            $this->register_content_type();
        } // init


		/**
		 * Registers marker's metaboxes for post-edit screens
		 */
		
        function metaboxes_register_callback() {
            add_meta_box( 'soundmap-map', __( "Place the Marker", 'soundmap' ), array( $this, 'map_meta_box' ), 'marker', 'normal', 'high' );
            add_meta_box( 'soundmap-media-info', __( "Info", 'soundmap' ), array( $this, 'info_meta_box' ), 'marker', 'side', 'high' );
            add_meta_box( 'soundmap-media-attachments', __( "Media files attached.", 'soundmap' ), array( $this, 'attachments_meta_box'), 'marker', 'side', 'high' );
            add_meta_box( 'soundmap-email', __( "Uploader Mail", 'soundmap' ), array( $this, 'email_meta_box' ), 'marker', 'side', 'low' );
			add_meta_box( 'soundmap-web-link', __( "Uploader Website", 'soundmap' ), array( $this, 'webpage_meta_box' ), 'marker', 'side', 'low' );
        } //metaboxes_register_callback

        /**
         * Get marker's author's email post meta
         * 
         * @return <type>
         */
        function email_meta_box() {
            global $post;
            $mail = get_post_meta( $post->ID, 'EMAIL', TRUE );
            echo "<p>" . $mail . "</p>";
        }

        /**
         * Get marker's author's name
         * 
         * @return <type>
         */
        function info_meta_box() {

            global $post;

            $soundmap_author = get_post_meta( $post->ID, 'soundmap_marker_author', TRUE );

            echo '<label for="soundmap-marker-author">' . __( 'Author', 'soundmap' ) . ': </label>';
            echo '<input type="text" name="soundmap_marker_author" id="soundmap-marker-author" value="' . $soundmap_author . '">';
        }


        /**
         * 
         * 
         * 
         * @return <type>
         */
        function attachments_meta_box() {
            global $post;

            $files = get_post_meta( $post->ID, 'soundmap_attachments_id', FALSE );
            echo '<div id="soundmap-attachments">';
            echo '<div><input type="button" id="add_files" class="button button-primary button-large" value="Add Files"></div>';
            $out = '';
            if ( $files ) {
                foreach( $files as $file ) {
                    $data = wp_prepare_attachment_for_js( $file );
                    $out .= "<div class='soundmap-attach-item'>
								<div class='att-icon'><img src='{$data['icon']}'/></div>
								<div class='att-info'>
									<a href='{$data['url']}'><strong>{$data['title']}</strong></a><br/>
									<span class='att-length'>{$data['fileLength']}</span><br/>
									<a href='#' class='delete-att-item'>" . _e('Close') . "</a>
								</div>
								<div class='clear'></div>
								<input type='hidden' name='soundmap-att-ids[]' value='{$file}' />
							</div>";
                }
            }
            echo '<div id="soundmap-attachments-list">' . $out . '</div>';
            echo '</div>';
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function map_meta_box() {

            global $post;
            $soundmap_lat = get_post_meta( $post->ID, 'soundmap_marker_lat', TRUE );
            $soundmap_lng = get_post_meta( $post->ID, 'soundmap_marker_lng', TRUE );

           echo '<input type="hidden" name="soundmap_map_noncename" id="soundmap_map_noncename" value="' .
            wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
           echo '<div id="map_canvas"></div>';
           echo '<label for="soundmap-marker-lat">' . __( 'Latitude', 'soundmap' ) . '</label>';
           echo '<input type="text" name="soundmap_marker_lat" id="soundmap-marker-lat" value = "' . $soundmap_lat . '">';
           echo '<label for="soundmap-marker-lng">' . __( 'Longitude', 'soundmap' ) . '</label>';
           echo '<input type="text" name="soundmap_marker_lng" id="soundmap-marker-lng" value = "'. $soundmap_lng . '">';
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function load_options() {

            $_config = array();

            //Load defaults;
            $defaults = array();
            $defaults['on_page'] = FALSE;
            $defaults['origin']['lat'] = 0;
            $defaults['origin']['lng'] = 0;
            $defaults['origin']['zoom'] = 10;
            $defaults['mapType'] = 'SATELLITE';
            $defaults['player_plugin'] = "";

            $_config = maybe_unserialize( get_option( 'soundmap' ) );

            $_config = wp_parse_args( $_config, $defaults );

            $this->config = $_config;

            //Load text domain for translations
            load_plugin_textdomain( 'soundmap', "wp-content/plugins/soundmap/languages", dirname( plugin_basename( __FILE__ ) ) . "/languages" );

        } //load_options

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function admin_menu() {
            add_options_page( __( 'Sound Map Configuration', 'soundmap' ), __( 'Sound Map', 'soundmap' ), 'manage_options', 'soundmap-options-menu', array( $this, 'admin_menu_page_callback' ) );
        } // admin_menu

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function enqueue_map_scripts() {
            /**
			 * Register the styles needed by leaflet and leaflet plugins.
             *   - Google Maps
             *   - Leaflet JS
             *   - Leaflet Google: google tiles plugin
             *   - Leaflet Providers: open tiles plugin
             */
            wp_enqueue_style( 'leaflet-css','http://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css', array(), '0.7.3', 'all' ); // add CSS Leaflet
			wp_enqueue_style( 'marker-cluster-default-css','https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/0.4.0/MarkerCluster.Default.css', array(), '0.4.0', 'all' );
			wp_enqueue_style( 'marker-cluster-transform-css','https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/0.4.0/MarkerCluster.css', array(), '0.4.0', 'all' );
            /**
			 * Register the basic scripts needed for presenting the maps.
             *   - Google Maps
             *   - Leaflet JS
             *   - Leaflet Google: google tiles plugin
             *   - Leaflet Providers: open tiles plugin
             */
            wp_enqueue_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false' ); // Google Maps
            wp_enqueue_script( 'leafletjs','http://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js', array(), '0.7.3', true ); // add Leaflet.js
            wp_enqueue_script( 'leaflet-plugin-google', plugins_url( 'js/leaflet/plugins/Google.js', __FILE__), array( 'leafletjs', 'google-maps' ), '0.7.2', true );
			wp_enqueue_script( 'leaflet-providers','https://cdnjs.cloudflare.com/ajax/libs/leaflet-providers/1.1.0/leaflet-providers.min.js', array( 'leafletjs' ), '1.1.0', true );
			wp_enqueue_script( 'leaflet-marker-cluster','https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/0.4.0/leaflet.markercluster.js', array( 'leafletjs' ), '0.4.0', true );
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function wp_enqueue_scripts() {

            $this->enqueue_map_scripts();
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'underscore' );
            wp_enqueue_script( 'mediaelement' );

            wp_enqueue_script( 'soundmap-front', plugins_url( 'js/soundmap.front.js', __FILE__ ), array(), '0.1', TRUE );
            wp_enqueue_style( 'soundmap-front-css', plugins_url( 'css/soundmap.front.css', __FILE__ ), array(), '0.1', 'all' );

            $params = array();
            $params['origin'] = $this->config['origin'];
            $params['mapType'] = $this->config['mapType'];
            $params['locale'] = get_locale();
            $params['ajaxurl'] = admin_url( 'admin-ajax.php' );
            wp_localize_script( 'soundmap-front', 'Soundmap', $params );
        }

        /**
         * 
         * 
         * @param <type> $hook  
         * 
         * @return <type>
         */
        function admin_enqueue_scripts( $hook ) {

            global $current_screen;

            if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && $current_screen->post_type == "marker" ) {
                $this->enqueue_map_scripts();
                wp_enqueue_script( 'soundmap-add', plugins_url( 'js/soundmap.add.js', __FILE__ ), array(), '0.1', TRUE );
                wp_enqueue_style( 'soundmap-add-css', plugins_url( 'css/soundmap.add.css', __FILE__ ), array(), '0.1', 'all' );

                $params = array();
                $params['origin'] = $this->config['origin'];
                $params['mapType'] = $this->config['mapType'];
                $params['locale'] = get_locale();
                wp_localize_script( 'soundmap-add', 'Soundmap', $params );

            } else if ( $hook == 'settings_page_soundmap-options-menu' ) {
                $this->enqueue_map_scripts();
                wp_enqueue_script( 'soundmap-config', plugins_url( 'js/soundmap.config.js', __FILE__ ), array( 'underscore' ), '0.1', TRUE);
                wp_enqueue_style( 'soundmap-config-css', plugins_url( 'css/soundmap.config.css', __FILE__ ), array(), '0.1', 'all' );

                $params = array();
                $params['origin'] = $this->config['origin'];
                $params['mapType'] = $this->config['mapType'];
                $params['locale'] = get_locale();
                wp_localize_script( 'soundmap-config', 'Soundmap', $params );
            } //if

        } // admin_enqueue_scripts

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function save_options() {
            $_config = array();

            //Load defaults;
            $_config ['origin']['lat'] = $_POST['soundmap_op_origin_lat'];
            $_config ['origin']['lng'] = $_POST['soundmap_op_origin_lng'];
            $_config ['origin']['zoom'] = $_POST['soundmap_op_origin_zoom'];
            $_config ['mapType'] = $_POST['soundmap_op_origin_type'];

            if( isset( $_POST['soundmap_op_plugin'] ) )
                $_config ['player_plugin'] = $_POST['soundmap_op_plugin'];

            update_option( 'soundmap', maybe_serialize( $_config ) );
            $this->config = wp_parse_args( $_config, $this->config );

        } // save_post

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function admin_menu_page_callback() {
            if ( !current_user_can( 'manage_options' ) )  {
                    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            // verify this came from the our screen and with proper authorization,
            // because save_post can be triggered at other times
            if ( isset( $_POST['soundmap_op_noncename'] ) ) {
                if ( !wp_verify_nonce( $_POST['soundmap_op_noncename'], plugin_basename( __FILE__ ) ) )
                    return;

                $this->save_options();
                $this->load_options();
            }
            ?>
                <div class="wrap">
                    <h2><?php  _e( "Sound Map Configuration", 'soundmap' ) ?></h2>
                    <div id="map_canvas_options"></div>
                    <form method="post" action = "" id="form-soundmap-options">
                        <h3><?php _e( 'Map Configuration', 'soundmap' ) ?></h3>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php _e( 'Origin configuration', 'soundmap' ) ?></th>
                                <td>
                                    <fieldset>
                                        <span class="description"><?php _e( "Choose the original configuration with the map.",  'soundmap' ) ?></span>
                                        <br>
                                        <label for="soundmap_op_origin_lat"><?php _e( 'Latitude', 'soundmap' ) ?>: </label><br>
                                        <input class="regular-text" name="soundmap_op_origin_lat" id="soundmap_op_origin_lat" type="text" value="<?php echo $this->config['origin']['lat'] ?>">
                                        <br>
                                        <label for="soundmap_op_origin_lng"><?php _e( 'Longitude', 'soundmap' ) ?>: </label><br>
                                        <input class="regular-text" name="soundmap_op_origin_lng" id="soundmap_op_origin_lng" type="text" value="<?php echo $this->config['origin']['lng'] ?>">
                                        <br>
                                        <label for="soundmap_op_origin_zoom"><?php _e( 'Zoom', 'soundmap' ) ?>: </label><br>
                                        <input class="small-text" name="soundmap_op_origin_zoom" id="soundmap_op_origin_zoom" type="text" value="<?php echo $this->config['origin']['zoom'] ?>">
                                        <input type="hidden" name="soundmap_op_origin_type" id="soundmap_op_origin_type" value='<?php echo $this->config['mapType'] ?>'>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
                        </p>
                        <input type="hidden" name="soundmap_op_noncename" id="soundmap_op_noncename" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ) ?>" />
                    </form>
            </div>
        <?php
        } // admin_menu_page_callback


        /**
         * 
         * 
         * @param <type> $post_id  
         * 
         * @return <type>
         */
        function save_post( $post_id ) {
            // verify if this is an auto save routine.
            // If it is our form has not been submitted, so we dont want to do anything
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
                return;

            // verify this came from the our screen and with proper authorization,
            // because save_post can be triggered at other times
            if ( isset( $_POST['soundmap_map_noncename'] ) ) {
                if ( !wp_verify_nonce( $_POST['soundmap_map_noncename'], plugin_basename( __FILE__ ) ) )
                    return;
            } else {
                return;
            }

            // Check permissions
            if ( 'marker' == $_POST['post_type'] ) {
              if ( !current_user_can( 'edit_post', $post_id ) )
                  return;
            }

            $soundmark_lat = $_POST['soundmap_marker_lat'];
            $soundmark_lng = $_POST['soundmap_marker_lng'];
            $soundmark_author = $_POST['soundmap_marker_author'];

            update_post_meta( $post_id, 'soundmap_marker_lat', $soundmark_lat );
            update_post_meta( $post_id, 'soundmap_marker_lng', $soundmark_lng );
            update_post_meta( $post_id, 'soundmap_marker_author', $soundmark_author );


            //before searching on all the $_POST array, let's take a look if there is any upload first!
            if( isset( $_POST['soundmap-att-ids'] ) ) {
                $files = $_POST['soundmap-att-ids'];
                delete_post_meta( $post_id, 'soundmap_attachments_id' );
                foreach ( $files as $key => $value ) {
                    add_post_meta( $post_id, 'soundmap_attachments_id', $value );
                }
            } else {
                delete_post_meta( $post_id, 'soundmap_attachments_id' );
                add_post_meta( $post_id, 'soundmap_attachments_id', 'null' );
            }
            delete_transient( 'soundmap_JSON_markers' );
        }


        /**
         * 
         * 
         * 
         * @return <type>
         */
        function wp_print_footer_scripts() {

            $params = array();
            $params['ajaxurl'] = admin_url( 'admin-ajax.php' );
            if ( isset( $this->config['query'] ) ) {
                if( is_array( $this->config['query'] ) ) {
                    $params ['query'] = json_encode( $this->config['query'] );
                } else {
                    $params ['query'] = $this->config['query'];
                }
            }
            $params['plugin_url'] = plugins_url( $path = '', __FILE__ );
            wp_localize_script( 'soundmap', 'WP_Soundmap', $params );
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function register_hooks() {
            add_action( 'init', array( $this, 'init' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'save_post', array( $this, 'save_post' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

            // add_action( 'wp_print_footer_scripts', array( $this, 'wp_print_footer_scripts' ), 1 );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

            add_action( 'init', array( $this, 'add_feed' ) );
            // add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            // add_action( 'admin_init', array( $this, 'register_admin_scripts' ) ); */

            //AJAX ACTIONS
            add_action( 'wp_ajax_soundmap_JSON_load_markers', array( $this, 'JSON_load_markers' ) );
            add_action( 'wp_ajax_nopriv_soundmap_JSON_load_markers', array( $this, 'JSON_load_markers' ) );
            add_action( 'wp_ajax_nopriv_soundmap_load_infowindow', array( $this, 'load_infowindow' ) );
            add_action( 'wp_ajax_soundmap_load_infowindow', array( $this, 'load_infowindow' ) );

            add_action( 'wp_ajax_soundmap-get-markers', array( $this, 'soundmap_get_markers' ) );
            add_action( 'wp_ajax_soundmap-get-content', array( $this, 'soundmap_get_content' ) );

			// add_action('wp_ajax_nopriv_soundmap_file_uploaded', array($this, 'ajax_file_uploaded_callback'));

            // add_action('wp_ajax_nopriv_soundmap_save_public_upload', array($this, 'save_public_upload'));
            // add_action('wp_ajax_nopriv_soundmap_verify_captcha', array($this, 'verify_captcha'));*/
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function add_feed() {
			
            global $wp_rewrite;
			
            add_feed( 'podcast', array( $this, 'customfeed' ) );
            add_action( 'generate_rewrite_rules', array( $this, 'rewrite_rules' ) );
            $wp_rewrite->flush_rules();
			
        }

        /**
         * 
         * 
         * @param <type> $wp_rewrite  
         * 
         * @return <type>
         */
        function rewrite_rules( $wp_rewrite ) {
			
            $new_rules = array(
                'feed/(.+)' => 'index.php?feed='.$wp_rewrite->preg_index(1)
            );
            $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
			
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function customfeed() {
			// You need to create a your-custom-feed.php file in your theme's directory
            load_template( WP_PLUGIN_DIR . '/soundmap/theme/rss-markers.php' );
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function soundmap_get_content() {
			
            if ( !isset( $_POST['id'] ) )
                wp_send_json_error();

            $id = $_POST['id'];
            $marker = get_post( $id );
            if( !$marker )
                wp_send_json_error();

            global $post;
            $post = $marker;
            setup_postdata( $marker );

            $mark = new stdclass();
            $mark->id = $id;
            $mark->autor = get_post_meta( $id, 'soundmap_marker_author', TRUE );
            $mark->files = array();

            $files = get_post_meta( $id, 'soundmap_attachments_id', FALSE );
            foreach ( $files as $key => $value ) {
                $att = wp_prepare_attachment_for_js( $value );
                $mark->files[] = $att;
            }

            if ( $theme = $this->get_template_include( 'window' ) ) {
                ob_start();
                include ( $theme );
                $mark->html = ob_get_clean();
            }

            wp_send_json_success( $mark );

        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function load_infowindow() {

            $marker_id = $_REQUEST['marker'];

            $marker = get_post( $marker_id );
            global $post;
            $post = $marker;
            setup_postdata( $marker );
            if( !$marker )
                die();

            $feature = new stdclass();
            $feature->autor = get_post_meta( $marker_id, 'soundmap_marker_author', TRUE );
            $files = get_post_meta( $marker_id, 'soundmap_attachments_id', FALSE );
            $info['m_files'] = array();
            foreach ( $files as $key => $value ) {
                $file = array();
                $att = get_post( $value );

                $file['id'] = $value;
                $file['fileURI'] = wp_get_attachment_url( $value );
                $file['filePath'] = get_attached_file( $value );
                $file['info'] = ""; // soundmap_get_id3info( $file['filePath'] );
                $file['name'] = $att->post_name;
                $info['m_files'][] = $file;
            }
            if ( $theme=$this->get_template_include( 'window' ) ) {
                ob_start();
                include ( $theme );
                $output = ob_get_flush();
                wp_send_json_success( $output );
            }

            die();
        }

        /**
         * 
         * 
         * @param <type> $templ  
         * 
         * @return <type>
         */
        function get_template_include( $templ ) {
            if ( !$templ )
                return FALSE;

            $theme_file = TEMPLATEPATH . DIRECTORY_SEPARATOR . $templ . '.php';
            $plugin_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'soundmap' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'theme_' . $templ . '.php';

            if ( file_exists( $theme_file ) )
                return $theme_file;

            if( file_exists( $plugin_file ) )
                return $plugin_file;
            return FALSE;

        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function soundmap_get_markers() {
            if ( !isset( $_POST['query'] ) )
                wp_send_json_error();

            $q = $_POST['query'];
            if ( is_array( $q ) ) {

                $options = array(
                    'post_type' => 'marker',
                    'post_status' => 'publish'
                );
                $options = array_merge( $options, $q );
                $query = new WP_Query( $options );

                if ( !$query->have_posts() )
                    wp_send_json_error();

                $posts = $query->posts;

                $feature_collection = new stdclass();
                $feature_collection->type = 'FeatureCollection';
                $feature_collection->features = array();

                foreach( $posts as $post ) {
                    $post_id = $post->ID;
                    $m_lat = get_post_meta( $post_id, 'soundmap_marker_lat', TRUE );
                    $m_lng = get_post_meta( $post_id, 'soundmap_marker_lng', TRUE );
                    $title = get_the_title ( $post_id );
                    $feature = new stdclass();
                    $feature->type = 'Feature';
                    $feature->geometry = new stdclass();
                    $feature->geometry->type = 'Point';
                    $feature->geometry->coordinates = array( floatval( $m_lng ), floatval( $m_lat ) );
                    $feature->properties = new stdclass();
                    $feature->properties->id = $post_id;
                    $feature->properties->title = $title;
                    $feature_collection->features[] = $feature;
                }

                wp_send_json_success( $feature_collection );
            }
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function JSON_load_markers() {
            if ( !isset( $_POST['query'] ) )
                return;

            //checkeo la cache
            $save_transient = false;
            if ( $_POST['query'] == 'all' ) {
                $transient_data = get_transient( 'soundmap_JSON_markers' );
                if( $transient_data !== false ) {
                    echo json_encode( $transient_data );
                    die();
                }
                $query = new WP_Query( array( 'post_type' => 'marker', 'post_status' => 'publish' ) );
                $save_transient = true;
            } else {
                if ( !is_array( $_POST["query"] ) ) {
                    $markers_list = json_decode( $_POST['query'] );
                } else { $markers_list = $_POST['query']; }

                $query = new WP_Query( array(
					'post_type' => 'marker',
					'post_status' => 'publish',
					'post__in' => $markers_list
				) );
            }

            $markers = array();

            if ( !$query->have_posts() )
            die();
            $posts = $query->posts;

            $feature_collection = new stdclass();
            $feature_collection->type = 'FeatureCollection';
            $feature_collection->features = array();

            foreach( $posts as $post ) {
                $post_id = $post->ID;
                $m_lat = get_post_meta( $post_id, 'soundmap_marker_lat', TRUE );
                $m_lng = get_post_meta( $post_id, 'soundmap_marker_lng', TRUE );
                $title = get_the_title ( $post_id );
                $feature = new stdclass();
                $feature->type = 'Feature';
                $feature->geometry = new stdclass();
                $feature->geometry->type = 'Point';
                $feature->geometry->coordinates = array( floatval( $m_lng ), floatval( $m_lat ) );
                $feature->properties = new stdclass();
                $feature->properties->id = $post_id;
                $feature->properties->title = $title;
                $feature_collection->features[] = $feature;
            }
            if ( $save_transient )
                set_transient( 'soundmap_JSON_markers', $feature_collection, 60*60*12 );

            echo json_encode( $feature_collection );

            die();
        }

        /**
         * 
         * 
         * 
         * @return <type>
         */
        function register_filters() {
            // add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
        }

        /**
         * 
         * 
         * @param <type> $query  
         * 
         * @return <type>
         */
        function set_query( $query ) {
            if ( !is_array( $query ) ) {
                //check if it is text
                if ( $query == "all" ) {
                    $this->config['query'] = 'all';
                }
            } else {
                $this->config['query'] = $query;
            }
        }

        /**
         * 
         * 
         * @param <type> $file  
         * 
         * @return <type>
         */
        function getID3( $file ) {
            $getID3 = new getID3;
            $result = array();
            $fileInfo = $getID3->analyze( $file );

            if( isset( $fileInfo['error']))
                return;

            if ( isset( $fileInfo['playtime_string'] ) )
                $result['play_time'] = $fileInfo['playtime_string'];

            if ( isset( $fileInfo['fileformat'] ) )
                $result['format'] = $fileInfo['fileformat'];

            return $result;
        }
    }

}

global $soundmap;
$soundmap = new Soundmap();
