<?php
/**
 * Plugin Name:       Best Forms Forever
 * Description:       Contact and Newsletter form Gutenberg Block plugin
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Michal Trykoszko
 * Author URI:        https://github.com/trykoszko
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       best-forms-forever
 *
 * @package           bff
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! defined( 'BEST_FORMS_FOREVER_CPT' ) ) {
	define( 'BEST_FORMS_FOREVER_CPT', 'best_form_forever' );
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function bff_best_forms_forever_block_init() {
	$dir    = __DIR__ . '/build/';
	$blocks = glob( $dir . '*' );

	if ( ! empty( $blocks ) ) {

		/**
		 * Get only directories
		 */
		$blocks = array_filter(
			$blocks,
			function ( $block ) {
				return substr( $block, -3 ) !== '.js';
			}
		);

		/**
		 * Filter out needed and unneeded blocks to register (explanation below)
		 */
		$current_post_type = false;
		if ( is_admin() ) {
			global $pagenow;
			if ( 'post-new.php' === $pagenow ) {
				if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) ) {
					$current_post_type = $_REQUEST['post_type'];
				};
			} elseif ( 'post.php' === $pagenow ) {
				if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {
					// Do nothing
				} elseif ( isset( $_GET['post'] ) ) {
					$post_id = (int) $_GET['post'];
				} elseif ( isset( $_POST['post_ID'] ) ) {
					$post_id = (int) $_POST['post_ID'];
				}
				if ( $post_id ) {
					$post              = get_post( $post_id );
					$current_post_type = $post->post_type;
				}
			}
		}

		/**
		 * Register only `form` block for non-form cpt
		 * Register only fields blocks for form cpt
		 */
		if ( BEST_FORMS_FOREVER_CPT === $current_post_type ) {
			$blocks = array_filter(
				$blocks,
				function ( $block ) use ( $dir ) {
					$block_name = str_replace( $dir, '', $block );
					return 'form' !== $block_name;
				}
			);
		} else {
			$blocks = array_filter(
				$blocks,
				function ( $block ) use ( $dir ) {
					$block_name = str_replace( $dir, '', $block );
					return 'form' === $block_name;
				}
			);
		}

		if ( ! empty( $blocks ) ) {
			foreach ( $blocks as $block ) {
				$block_name = str_replace( $dir, '', $block );
				register_block_type( __DIR__ . '/build/' . $block_name . '/json/block.json' );
			}
		}
	}
}
add_action( 'init', 'bff_best_forms_forever_block_init' );

/**
 * Registers the Best Forms Forever custom post type where you can define your forms.
 *
 * @return void
 */
function register_best_form_forever_cpt() {
	$labels = array(
		'name'                  => _x( 'Forms', 'Post Type General Name', 'bff' ),
		'singular_name'         => _x( 'Form', 'Post Type Singular Name', 'bff' ),
		'menu_name'             => __( 'Best Forms Forever', 'bff' ),
		'name_admin_bar'        => __( 'Form', 'bff' ),
		'archives'              => __( 'Form Archives', 'bff' ),
		'attributes'            => __( 'Form Attributes', 'bff' ),
		'parent_item_colon'     => __( 'Parent form:', 'bff' ),
		'all_items'             => __( 'All Forms', 'bff' ),
		'add_new_item'          => __( 'Add New Form', 'bff' ),
		'add_new'               => __( 'Add New', 'bff' ),
		'new_item'              => __( 'New Form', 'bff' ),
		'edit_item'             => __( 'Edit Form', 'bff' ),
		'update_item'           => __( 'Update Form', 'bff' ),
		'view_item'             => __( 'View Form', 'bff' ),
		'view_items'            => __( 'View Forms', 'bff' ),
		'search_items'          => __( 'Search Form', 'bff' ),
		'not_found'             => __( 'Not found', 'bff' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'bff' ),
		'featured_image'        => __( 'Featured Image', 'bff' ),
		'set_featured_image'    => __( 'Set featured image', 'bff' ),
		'remove_featured_image' => __( 'Remove featured image', 'bff' ),
		'use_featured_image'    => __( 'Use as featured image', 'bff' ),
		'insert_into_item'      => __( 'Insert into form', 'bff' ),
		'uploaded_to_this_item' => __( 'Uploaded to this form', 'bff' ),
		'items_list'            => __( 'Forms list', 'bff' ),
		'items_list_navigation' => __( 'Forms list navigation', 'bff' ),
		'filter_items_list'     => __( 'Filter forms list', 'bff' ),
	);
	$args   = array(
		'label'               => __( 'Form', 'bff' ),
		'description'         => __( 'A form', 'bff' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 80,
		'menu_icon'           => 'dashicons-forms',
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'rewrite'             => false,
		'capability_type'     => 'page',
		'show_in_rest'        => true,
	);
	register_post_type( BEST_FORMS_FOREVER_CPT, $args );
}
add_action( 'init', 'register_best_form_forever_cpt', 0 );

/**
 * Allows only a Best Forms Forever block inside a
 *
 * @param stdClass $block_editor_context Block editor context.
 * @param stdClass $context Context.
 *
 * @return string[]|void
 */
function best_forms_forever_cpt_allowed_blocks( $block_editor_context, $context ) {
	$bff_allowed = array(
		'bff/best-forms-forever-input-field',
	);

	if ( property_exists( $context, 'post' ) && BEST_FORMS_FOREVER_CPT === $context->post->post_type ) {
		return $bff_allowed;
	}
}
add_filter( 'allowed_block_types_all', 'best_forms_forever_cpt_allowed_blocks', 2, 10 );
