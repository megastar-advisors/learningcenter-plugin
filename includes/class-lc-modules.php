<?php
/**
 * LearningCenter Modules Class
 * 
 * Handles all LearningCenter module functionality.
 * 
 * @package LearningCenter\Modules
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exitl;

class LC_Modules {

  private $file;
  public $taxonomy;

  public function __construct() {
    $this->taxonomy   = 'module';

    // setup taxonomy
    add_action( 'init', array( $this, 'setup_modules_taxonomy' ), 10 );
  }

  /**
	 * Register the modules taxonomy
	 *
	 * @since 1.8.0
	 * @since 1.9.7 Added `not_found` label.
	 */
	public function setup_modules_taxonomy() {

		$labels = array(
			'name'              => __( 'Modules', 'sensei-lms' ),
			'singular_name'     => __( 'Module', 'sensei-lms' ),
			'search_items'      => __( 'Search Modules', 'sensei-lms' ),
			'all_items'         => __( 'All Modules', 'sensei-lms' ),
			'parent_item'       => __( 'Parent Module', 'sensei-lms' ),
			'parent_item_colon' => __( 'Parent Module:', 'sensei-lms' ),
			'view_item'         => __( 'View Module', 'sensei-lms' ),
			'edit_item'         => __( 'Edit Module', 'sensei-lms' ),
			'update_item'       => __( 'Update Module', 'sensei-lms' ),
			'add_new_item'      => __( 'Add New Module', 'sensei-lms' ),
			'new_item_name'     => __( 'New Module Name', 'sensei-lms' ),
			'menu_name'         => __( 'Modules', 'sensei-lms' ),
			'not_found'         => __( 'No modules found.', 'sensei-lms' ),
			'back_to_items'     => __( '&larr; Back to Modules', 'sensei-lms' ),
		);

		/**
		 * Filter to alter the Sensei Modules rewrite slug
		 *
		 * @since 1.8.0
		 * @param string default 'modules'
		 */
		$modules_rewrite_slug = apply_filters( 'learningcenter_module_slug', 'modules' );

		$args = array(
			'public'             => true,
			'hierarchical'       => true,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => false,
			'show_in_quick_edit' => false,
			'show_ui'            => true,
			'rewrite'            => array( 'slug' => $modules_rewrite_slug ),
			'labels'             => $labels,
		);

		register_taxonomy( 'module', array( 'course', 'lesson' ), $args );

	}
}

new LC_Modules();