<?php

namespace Carbon_Field_Relationship;

use Carbon_Fields\Field\Field;
use WP_Error;

class Relationship_Field extends Field {

	/**
	 * Context to query objects
	 *
	 * @var null|float
	 */
	protected $object_context = null;

	/**
	 * Type to query objects
	 *
	 * @var null|float
	 */
	protected $object_type = null;

	/**
	 * Additional args to query objects
	 * 
	 * @var array
	 */
	protected $query_args = [];

	/**
	 * Used to detect error
	 * 
	 * @var null|string
	 */
	protected $error = null;

	/**
	 * Prepare the field type for use
	 * Called once per field type when activated
	 */
	public static function field_type_activated() {
		$dir = \Carbon_Field_Relationship\DIR . '/languages/';
		$locale = get_locale();
		$path = $dir . $locale . '.mo';
		load_textdomain( 'carbon-field-relationship', $path );
	}

	/**
	 * Enqueue scripts and styles in admin
	 * Called once per field type
	 */
	public static function admin_enqueue_scripts() {
		$root_uri = \Carbon_Fields\Carbon_Fields::directory_to_url( \Carbon_Field_Relationship\DIR );

		// Enqueue field styles.
		wp_enqueue_style(
			'carbon-field-relationship',
			$root_uri . '/build/bundle' . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' ) . '.css'
		);

		// Enqueue field scripts.
		wp_enqueue_script(
			'carbon-field-relationship',
			$root_uri . '/build/bundle' . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' ) . '.js',
			array( 'carbon-fields-core' )
		);
	}

	/**
	 * Load the field value from an input array based on its name
	 *
	 * @param array $input Array of field names and values.
	 */
	public function set_value_from_input( $input ) {
		parent::set_value_from_input( $input );

		$value = $this->get_value();
		if ( $value === '' ) {
			return;
		}

		$value = intval( $value );

		$this->set_value( $value );
	}

	/**
	 * Returns an array that holds the field data, suitable for JSON representation.
	 *
	 * @param bool $load  Should the value be loaded from the database or use the value from the current instance.
	 * @return array
	 */
	public function to_json( $load ) {
		$field_data = parent::to_json( $load );

		$field_data = array_merge($field_data, array(
			'object_context' => $this->object_context,
			'object_type' => $this->object_type,
			'options' => $this->query(),
			'empty' => __("No objects found")
		) );

		return $field_data;
	}

	/**
	 * Set field context to query objects
	 *
	 * @param  string	 $context
	 * @param  string	 $type
	 * @return self      $this
	 */
	function set_object( $context, $type ) {
		$this->object_context = strtolower(trim($context));
		$this->object_type = trim($type);

		if(!$this->validate_context()){
			$error_array = explode("|", $this->error);
			$wp_error = new WP_Error($error_array[0], $error_array[1]);
			wp_die($wp_error);
		}

		return $this;
	}

	/**
	 * Set additional arguments to query objects
	 * 
	 * @param array $args
	 * @return self	$this
	 */
	function set_args( $args = [] ) {
		$this->query_args = $args;
		
		return $this;
	}

	/**
	 * Check if context and type are valid
	 * 
	 * @return bool
	 */
	private function validate_context()
	{
		$contexts = [
			'post' => get_post_types(),
			'taxonomy_term' => get_taxonomies(),
			'user' => []
		];

		if(array_key_exists($this->object_context, $contexts)){
			$types = $contexts[$this->object_context];
			if(count($types) > 0){
				if(in_array($this->object_type, $contexts[$this->object_context])){
					return true;
				}
				$this->error = "invalid-type|Invalid type ".$this->object_type;
				return false;
			}
			return true;
		}

		$this->error = "invalid-context|Invalid context ".$this->object_context;
		return false;
	}

	/**
	 * Query objects according to specified context and type
	 * 
	 * @return array
	 */
	private function query()
	{
		$options = [];

		switch($this->object_context){
			case "post":
				$args = [
					'post_type' => $this->object_type,
					'post_status' => "any",
					'order' => "DESC",
					'orderby' => "date",
					'posts_per_page' => -1
				];
				$args = array_merge($args, $this->query_args);
				foreach(get_posts($args) as $post){
					$options[] = [
						'label' => $post->post_title . ($post->post_status != "publish" ? " - ".$post->post_status : ""),
						'value' => $post->ID
					];
				}
				break;

			case "taxonomy_term":
				$args = [
					'taxonomy' => $this->object_type,
					'hide_empty' => false
				];
				$args = array_merge($args, $this->query_args);
				foreach(get_terms($args) as $term){
					$options[] = [
						'label' => $term->name . " (" . $term->count . ")",
						'value' => $term->term_id
					];
				}
				break;

			case "user":
				foreach(get_users($this->query_args) as $user){
					$meta = get_userdata($user->ID);
					$options[] = [
						'label' => $meta->display_name . " - " . $meta->user_email,
						'value' => $user->ID,
					];
				}
				break;
		}

		return $options;
	}
}
