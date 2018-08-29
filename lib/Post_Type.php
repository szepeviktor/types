<?php

namespace Types;

/**
 * Class Post_Type
 */
class Post_Type {
	/**
	 * Registers post types based on an array definition.
	 *
	 * @param array $post_types {
	 *      An associative array of post types, where the name of the post type is the key of an
	 *      array.
	 *
	 *      @type string $name_singular Singular name for post type.
	 *      @type string $name_plural   Plural name for post type.
	 *      @type array  $args          Arguments that get passed to post type registration.
	 *      @type array  $query         Custom query parameters for frontend and backend query.
	 *      @type array  $admin_columns An array of admin_column definitions.
	 * }
	 */
	public static function register( $post_types = [] ) {
		foreach ( $post_types as $name => $post_type ) {
			$args = wp_parse_args( $post_type['args'], [
				'description'       => $post_type['name_plural'],
				'public'            => false,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
			] );

			$labels = self::get_post_type_labels(
				$post_type['name_singular'],
				$post_type['name_plural']
			);

			if ( isset( $post_type['query'] ) ) {
				( new Post_Type_Query( $name, $post_type['query'] ) )->init();
			}

			if ( isset( $post_type['admin_columns'] ) ) {
				( new Post_Type_Columns( $name, $post_type['admin_columns'] ) )->init();
			}

			add_filter( "post_type_labels_{$name}", function() use ( $labels ) {
				return $labels;
			} );

			register_post_type( $name, $args );
		}
	}

	/**
	 * Updates settings for a post type.
	 *
	 * For a list of settings that you can change, refer to the `register_post_type()` function.
	 *
	 * Run this function before the `init` hook.
	 *
	 * @see register_post_type()
	 *
	 * @param string $post_type The post type to update.
	 * @param array  $settings  The updated settings.
	 */
	public static function update( $post_type, $settings = [] ) {
		add_filter( 'register_post_type_args', function( $args, $name ) use ( $post_type, $settings ) {
			if ( $post_type !== $name ) {
				return $args;
			}

			$args = wp_parse_args( $settings, $args );

			return $args;
		}, 10, 2 );
	}

	/**
	 * Renames a post type.
	 *
	 * Run this function before the `init` hook.
	 *
	 * @param string $post_type     The post type to rename.
	 * @param string $name_singular The new singular name.
	 * @param string $name_plural   The new plural name.
	 */
	public static function rename( $post_type, $name_singular, $name_plural ) {
		if ( ! post_type_exists( $post_type ) ) {
			return;
		}

		// Get new post type labels
		$labels = self::get_post_type_labels( $name_singular, $name_plural );

		// Use filter to set labels for posts to make sure that any missing labels are included as well.
		add_filter( "post_type_labels_{$post_type}", function() use ( $labels ) {
			return $labels;
		} );
	}

	/**
	 * Registers admin column settings for a post type.
	 *
	 * @param array $post_types An associative array of post types, where the name of the post type
	 *                          is the key of an array that defines the admin column settings for
	 *                          this post type.
	 */
	public static function admin_columns( $post_types = [] ) {
		foreach ( $post_types as $name => $column_settings ) {
			( new Post_Type_Columns( $name, $column_settings ) )->init();
		}
	}

	/**
	 * Gets German labels for a post type based on singular and plural name.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_post_type_labels/
	 *
	 * @param string $name_singular Singular name for post type.
	 * @param string $name_plural   Plural name for post type.
	 * @return array
	 */
	public static function get_post_type_labels( $name_singular, $name_plural ) {
		return [
			'name'                  => $name_plural,
			'singular_name'         => $name_singular,
			'add_new'               => $name_singular . ' hinzufügen',
			'add_new_item'          => $name_singular . ' hinzufügen',
			'edit_item'             => $name_singular . ' bearbeiten',
			'new_item'              => $name_singular . ' hinzufügen',
			'view_item'             => $name_singular . ' anschauen',
			'view_items'            => $name_plural . ' anschauen',
			'search_items'          => $name_plural . ' suchen',
			'not_found'             => 'Keine ' . $name_plural . ' gefunden',
			'not_found_in_trash'    => 'Keine ' . $name_plural . ' im Papierkorb gefunden',
			'parent_item_colon'     => 'Übergeordnete ' . $name_plural,
			'all_items'             => 'Alle ' . $name_plural,
			'archives'              => 'Archive für ' . $name_singular,
			'attributes'            => 'Attribute für ' . $name_singular,
			'insert_into_item'      => 'In ' . $name_singular . ' einfügen',
			'uploaded_to_this_item' => 'Zu diesem ' . $name_singular . ' hochgeladen',
			'featured_image'        => 'Beitragsbild für ' . $name_singular,
			'menu_name'             => $name_plural,
			'filter_items_list'     => $name_plural . ' filtern',
			'items_list_navigation' => 'Navigation für ' . $name_plural,
			'items_list'            => $name_plural,
			'name_admin_bar'        => $name_singular,
		];
	}
}
