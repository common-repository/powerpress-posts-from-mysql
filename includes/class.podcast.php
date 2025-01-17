<?php

class Podcast {
	private $db_id;
	private $post_title;
	private $post_category;
	private $post_body;
	private $post_url;
	private $post_size;
	private $post_type;
	private $post_date;
	private $error;
	private $guid_string;

	function __construct( $id ){

		require_once plugin_dir_path( __FILE__ ) . 'class.local-podcast.php';
		$podcast                   = LocalPodcast::find_podcast_by_id( $id );
		$this->post_image_location = $podcast[LocalPodcast::$field_options['post_image']];
		$this->db_id               = $id;
		$this->post_title          = $podcast[LocalPodcast::$field_options['post_title']];
		$this->post_category       = $podcast[LocalPodcast::$field_options['post_category']];
		$this->post_body           = $podcast[LocalPodcast::$field_options['post_body']];
		// $this->post_image       = $podcast[LocalPodcast::$field_options['post_image']];
		$this->post_url            = $podcast[LocalPodcast::$field_options['post_url']];
		$this->post_size           = $podcast[LocalPodcast::$field_options['post_size']];
		$this->post_type           = $podcast[LocalPodcast::$field_options['post_type']];
		$this->post_date           = $podcast[LocalPodcast::$field_options['post_date']];
		$this->guid_string         = LocalPodcast::create_guid( $id );

	}

	function details(){
		return array(
			'db_id'               => $this->db_id,
			'post_title'          => $this->post_title,
			'post_category'       => $this->post_category,
			'post_body'           => $this->post_body,
			'post_image_location' => $this->post_image_location,
			'post_url'            => $this->post_url,
			'post_size'           => $this->post_size,
			'post_type'           => $this->post_type,
			'post_date'           => $this->post_date,
			'guid'                => $this->guid_string
			);

	}


	public function publish(  ){
		return $this->_publish_post( );
	}

	public static function update_podcast_status( $id, $status ) {
		if ( $status == 'draft' ){
			$post = array(
				'ID'          => $id,
				'post_status' => 'draft',
				'edit_date'   => true
				);
		} else {
			$post = array(
				'ID'          => $id,
				'post_status' => 'publish',
				'edit_date'   => true
				);
		}
		if ( !wp_update_post( $post )){
			$return = new WP_Error( 'wp_update_post_failure', __('wp_update_podcast_status() failed.', 'ppfm' ) );
		}
	}

	public function publish_draft(){
		// can we find a record with this guid?
		if ( $id = LocalPodcast::guid_exists( $this->guid_string ) ){
			// what's its status?
			$status = get_post_status( $id );
			if ( $status == 'publish' ){
				$post = array(
					'ID'          => $id,
					'post_status' => 'draft',
					'edit_date'   => true
					);
			} else {
				$post = array(
					'ID'          => $id,
					'post_status' => 'publish',
					'edit_date'   => true
					);
			}

			if ( !wp_update_post( $post )){
				$return = new WP_Error( 'wp_update_post_failure', __('publish_draft() failed.', 'ppfm' ) );
			}
		} else {
			return $this->_publish_post( 'draft' );
		}
	}

	public function remove(){
		// here we need to remove the post, its related image(s) and anything else that was created
		$ID = LocalPodcast::find_wp_id_by_guid( $this->guid_string );

		$args = array(
			'numberposts'    => -1,
			'order'          => 'ASC',
			'post_mime_type' => 'image',
			'post_parent'    => $ID,
			'post_status'    => null,
			'post_type'      => 'attachment',
			);
		$children = get_children( $args );
		foreach( $children as $child ) {
			$child_ID = $child->ID;
			wp_delete_post( $child_ID, TRUE );
		}
		if ( ! wp_delete_post( $ID, TRUE ) ) {
			$return = new WP_Error( 'wp_delete_post_failure', __( 'wp_delete_post() failed.', 'ppfm' ) );
		}
	}

	private function _publish_post( $status="publish" ){
		
		$time_difference = get_option('gmt_offset') * HOUR_IN_SECONDS;
		
		// Setup PowerPress enclosure
		$enclosure_value = $this->post_url;
		$enclosure_value .= "\n";
		$enclosure_value .= $this->post_size;
		$enclosure_value .= "\n";
		$enclosure_value .= $this->post_type;

		$cat_id          = get_cat_ID($this->post_category);
		
		$ddate_U = strtotime( $this->post_date );
		$post_date = gmdate( 'Y-m-d H:i:s', $ddate_U + $time_difference );
		$post_date_gmt = gmdate( 'Y-m-d H:i:s', $ddate_U );

		// Setup the post
		$post = array();
		$post['post_title'] = $this->post_title;
		$post['post_content'] = $this->post_body;
		$post['post_date'] = $post_date;
		$post['post_date_gmt'] = $post_date_gmt;
		if( !empty($cat_id) )
			$post['post_category'] = array( $cat_id );
		$post['post_status'] = $status;
		if( !empty($this->guid_string) )
			$post['guid'] = $this->guid_string;
		
		// If post is successfully inserted, the new post_id will be returned
		// and we can use that to handle the image and the podcast
		$post_id = wp_insert_post($post);
		
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		
		if( $post_id ) {

			$url  = $this->post_image_location;
			if( !empty($url) ) {
				$tmp  = download_url( $url );
				$desc = $this->post_title;
				$file = basename( $url );

		// Set variables for storage
		// fix file filename for query strings
				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $file, $matches);
				$file_array['name'] = basename($matches[0]);
				$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
				if ( is_wp_error( $tmp ) ) {
					@unlink($file_array['tmp_name']);
					$file_array['tmp_name'] = '';
				}

		// do the validation and storage stuff
				$thumb_id = media_handle_sideload( $file_array, $post_id, $desc );

		// If error storing permanently, unlink
				if ( is_wp_error($thumb_id) ) {
					@unlink($file_array['tmp_name']);
					return $thumb_id;
				}

				if ( is_wp_error( $thumb_id ) ){
					$return = new WP_Error( 'media_handle_sideload_failure', __('media_handle_sideload() failed.', 'ppfm' ) );
				}
				set_post_thumbnail( $post_id, $thumb_id );
			}
			if( ! update_post_meta( $post_id, 'enclosure', $enclosure_value )){
				$return = new WP_Error( 'update_post_meta_failure', __( 'update_post_meta() failed.', 'ppfm' ) );
			}
			
		} else {
			$error = new WP_Error('insert_post_failure', __( 'wp_insert_post() failed.', 'ppfm' ) );
			return $error;
		}
	}


}