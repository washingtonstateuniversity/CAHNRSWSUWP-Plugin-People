<?php

namespace WSUWP\CAHNRSWSUWP_Plugin_People;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class CAHNRSWSUWP_Content_Syndicate {


	public function __construct() {

		add_filter( 'wsuwp_people_response', array( $this, 'add_local_people' ), 10, 2 );

	} // End __construct


	public function add_local_people( $people, $atts ) {

		$local_people = $this->get_local_people( $atts );

		if ( is_array( $people ) && ! empty( $people ) ) {

			foreach ( $people as $index => &$remote_person ) {

				if ( ! isset( $remote_person->profile_photo ) || empty( $remote_person->profile_photo ) ) {

					$remote_person->profile_photo = people_get_plugin_url() . '/images/person-placeholder.png';
		
				} // End if

				$nid = ( isset( $remote_person->nid ) && ! empty( $remote_person->nid ) ) ? $remote_person->nid : '';

				if ( ! empty( $nid ) ) {

					if ( array_key_exists( $nid, $local_people ) ) {

						$local_person = $local_people[ $nid ];

						$remote_person = $this->merge_people( $remote_person, $local_person );

						unset( $local_people[ $nid ] );

					} // End if
				} // End if
			} // End foreach
		} // End if

		if ( ! empty( $atts['nid'] ) ) {

			$nid = $atts['nid'];

			if ( ! empty( $local_people[ $nid ] ) ) {

				$people[] = $local_people[ $nid ];

			}
		} else {

			foreach ( $local_people as $id => $local_person ) {

				$people[] = $local_person;

			} // End $local_person
		} // End if

		return $people;

	} // End add_local_people


	public function merge_people( $remote_person, $local_person ) {

		if ( isset( $local_person->position_title ) && ! empty( $local_person->position_title ) ) {

			$remote_person->working_titles = array( $local_person->position_title );

		} // End if

		if ( isset( $local_person->display_name ) && ! empty( $local_person->display_name  ) ) {

			$remote_person->title->rendered = $local_person->display_name;

		} // End if 

		if ( isset( $local_person->bio ) && ! empty( $local_person->bio ) ) {

			$remote_person->content->rendered = $local_person->bio;

		} // End if

		return $remote_person;

	} // End merge_people


	public function get_local_people( $atts ) {

		$people = array();

		$args = array(
			'post_type'      => 'profile',
			'posts_per_page' => '-1',
			'post_status'    => 'publish',
		);

		$the_query = new \WP_Query( $args );

		if ( $the_query->have_posts() ) {

			include_once people_get_plugin_dir_path() . '/classes/class-person.php';

			include_once people_get_plugin_dir_path() . '/classes/class-rest-person.php';

			while ( $the_query->have_posts() ) {

				$the_query->the_post();

				$person = new REST_Person( $the_query->post );

				$nid = ( isset( $person->nid ) && ! empty( $person->nid ) ) ? $person->nid : $the_query->post->ID;

				$people[ $nid ] = $person;

			} // End while

			wp_reset_postdata();

		} // End if

		return $people;

	} // End get_local_people



} // End CAHNRSWSUWP_Content_Syndicate

$cahrnswsuwp_content_syndicate = new CAHNRSWSUWP_Content_Syndicate();
