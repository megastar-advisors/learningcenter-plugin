<?php
/**
 * Post Types
 * 
 * Registers post types and taxonomies.
 * 
 * @package LearningCenter\Classes\Courses
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post types Class.
 */
class LC_Post_Types {

  /**
   * Hook in methods.
   */
  public static function init() {
    add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );

   
    //add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
    //add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
    //add_action( 'learningcenter_after_register_post_type', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );
    //add_action( 'learningcenter_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
  }

  /**
   * Register core post types.
   */
  public static function register_post_types() {

    if ( ! is_blog_installed() || post_type_exists( 'course' ) ) {
      return;
    }

    do_action( 'learningcenter_register_post_type' );

    $supports = array( 'title', 'editor', 'excerpt', 'thumbnail' );

    register_post_type(
      'path',
      apply_filters(
        'learningcenter_register_post_type_path',
        array(
          'labels'              => array(
						'name'                  => __( 'Paths', 'learningcenter' ),
						'singular_name'         => __( 'Path', 'learningcenter' ),
						'all_items'             => __( 'All Paths', 'learningcenter' ),
						'menu_name'             => _x( 'Paths', 'Admin menu name', 'learningcenter' ),
						'add_new'               => __( 'Add New', 'learningcenter' ),
						'add_new_item'          => __( 'Add new path', 'learningcenter' ),
						'edit'                  => __( 'Edit', 'learningcenter' ),
						'edit_item'             => __( 'Edit path', 'learningcenter' ),
						'new_item'              => __( 'New path', 'learningcenter' ),
						'view_item'             => __( 'View path', 'learningcenter' ),
						'view_items'            => __( 'View paths', 'learningcenter' ),
						'search_items'          => __( 'Search paths', 'learningcenter' ),
						'not_found'             => __( 'No paths found', 'learningcenter' ),
						'not_found_in_trash'    => __( 'No paths found in trash', 'learningcenter' ),
						'parent'                => __( 'Parent path', 'learningcenter' ),
						'featured_image'        => __( 'Path image', 'learningcenter' ),
						'set_featured_image'    => __( 'Set path image', 'learningcenter' ),
						'remove_featured_image' => __( 'Remove path image', 'learningcenter' ),
						'use_featured_image'    => __( 'Use as path image', 'learningcenter' ),
						'insert_into_item'      => __( 'Insert into path', 'learningcenter' ),
						'uploaded_to_this_item' => __( 'Uploaded to this path', 'learningcenter' ),
						'filter_items_list'     => __( 'Filter paths', 'learningcenter' ),
						'items_list_navigation' => __( 'Paths navigation', 'learningcenter' ),
						'items_list'            => __( 'Paths list', 'learningcenter' ),
					),
					'description'         => __( 'This is where you can add new paths to your site.', 'learningcenter' ),
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'map_meta_cap'        => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
					'query_var'           => true,
					'supports'            => $supports,
					'has_archive'         => false,
					'show_in_nav_menus'   => true,
					'show_in_rest'        => true,
        )
      )
    );

    register_post_type(
      'course',
      apply_filters(
        'learningcenter_register_post_type_course',
        array(
          'labels'              => array(
						'name'                  => __( 'Courses', 'learningcenter' ),
						'singular_name'         => __( 'Course', 'learningcenter' ),
						'all_items'             => __( 'All Courses', 'learningcenter' ),
						'menu_name'             => _x( 'Courses', 'Admin menu name', 'learningcenter' ),
						'add_new'               => __( 'Add New', 'learningcenter' ),
						'add_new_item'          => __( 'Add new course', 'learningcenter' ),
						'edit'                  => __( 'Edit', 'learningcenter' ),
						'edit_item'             => __( 'Edit course', 'learningcenter' ),
						'new_item'              => __( 'New course', 'learningcenter' ),
						'view_item'             => __( 'View course', 'learningcenter' ),
						'view_items'            => __( 'View courses', 'learningcenter' ),
						'search_items'          => __( 'Search courses', 'learningcenter' ),
						'not_found'             => __( 'No courses found', 'learningcenter' ),
						'not_found_in_trash'    => __( 'No courses found in trash', 'learningcenter' ),
						'parent'                => __( 'Parent course', 'learningcenter' ),
						'featured_image'        => __( 'Course image', 'learningcenter' ),
						'set_featured_image'    => __( 'Set course image', 'learningcenter' ),
						'remove_featured_image' => __( 'Remove course image', 'learningcenter' ),
						'use_featured_image'    => __( 'Use as course image', 'learningcenter' ),
						'insert_into_item'      => __( 'Insert into course', 'learningcenter' ),
						'uploaded_to_this_item' => __( 'Uploaded to this course', 'learningcenter' ),
						'filter_items_list'     => __( 'Filter courses', 'learningcenter' ),
						'items_list_navigation' => __( 'Courses navigation', 'learningcenter' ),
						'items_list'            => __( 'Courses list', 'learningcenter' ),
					),
					'description'         => __( 'This is where you can add new courses to your site.', 'learningcenter' ),
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'map_meta_cap'        => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
					'query_var'           => true,
					'supports'            => $supports,
					'has_archive'         => false,
					'show_in_nav_menus'   => true,
					'show_in_rest'        => true,
        )
      )
    );

    register_post_type(
      'lesson',
      apply_filters(
        'learningcenter_register_post_type_lesson',
        array(
          'labels'              => array(
						'name'                  => __( 'Lessons', 'learningcenter' ),
						'singular_name'         => __( 'Lesson', 'learningcenter' ),
						'all_items'             => __( 'All Lessons', 'learningcenter' ),
						'menu_name'             => _x( 'Lessons', 'Admin menu name', 'learningcenter' ),
						'add_new'               => __( 'Add New', 'learningcenter' ),
						'add_new_item'          => __( 'Add new lesson', 'learningcenter' ),
						'edit'                  => __( 'Edit', 'learningcenter' ),
						'edit_item'             => __( 'Edit lesson', 'learningcenter' ),
						'new_item'              => __( 'New lesson', 'learningcenter' ),
						'view_item'             => __( 'View lesson', 'learningcenter' ),
						'view_items'            => __( 'View lessons', 'learningcenter' ),
						'search_items'          => __( 'Search lessons', 'learningcenter' ),
						'not_found'             => __( 'No lessons found', 'learningcenter' ),
						'not_found_in_trash'    => __( 'No lessons found in trash', 'learningcenter' ),
						'parent'                => __( 'Parent lesson', 'learningcenter' ),
						'featured_image'        => __( 'Course image', 'learningcenter' ),
						'set_featured_image'    => __( 'Set lesson image', 'learningcenter' ),
						'remove_featured_image' => __( 'Remove lesson image', 'learningcenter' ),
						'use_featured_image'    => __( 'Use as lesson image', 'learningcenter' ),
						'insert_into_item'      => __( 'Insert into lesson', 'learningcenter' ),
						'uploaded_to_this_item' => __( 'Uploaded to this lesson', 'learningcenter' ),
						'filter_items_list'     => __( 'Filter lessons', 'learningcenter' ),
						'items_list_navigation' => __( 'Lessons navigation', 'learningcenter' ),
						'items_list'            => __( 'Lessons list', 'learningcenter' ),
					),
					'description'         => __( 'This is where you can add new lessons to your site.', 'learningcenter' ),
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'map_meta_cap'        => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
					'query_var'           => true,
					'supports'            => $supports,
					'has_archive'         => false,
					'show_in_nav_menus'   => true,
					'show_in_rest'        => true,
        )
      )
		);
		
		register_post_type(
      'group',
      apply_filters(
        'learningcenter_register_post_type_group',
        array(
          'labels'              => array(
						'name'                  => __( 'Groups', 'learningcenter' ),
						'singular_name'         => __( 'Group', 'learningcenter' ),
						'all_items'             => __( 'All Groups', 'learningcenter' ),
						'menu_name'             => _x( 'Groups', 'Admin menu name', 'learningcenter' ),
						'add_new'               => __( 'Add New', 'learningcenter' ),
						'add_new_item'          => __( 'Add new group', 'learningcenter' ),
						'edit'                  => __( 'Edit', 'learningcenter' ),
						'edit_item'             => __( 'Edit group', 'learningcenter' ),
						'new_item'              => __( 'New group', 'learningcenter' ),
						'view_item'             => __( 'View group', 'learningcenter' ),
						'view_items'            => __( 'View groups', 'learningcenter' ),
						'search_items'          => __( 'Search groups', 'learningcenter' ),
						'not_found'             => __( 'No lessons found', 'learningcenter' ),
						'not_found_in_trash'    => __( 'No lessons found in trash', 'learningcenter' ),
						'parent'                => __( 'Parent group', 'learningcenter' ),
						'featured_image'        => __( 'Group image', 'learningcenter' ),
						'set_featured_image'    => __( 'Set group image', 'learningcenter' ),
						'remove_featured_image' => __( 'Remove group image', 'learningcenter' ),
						'use_featured_image'    => __( 'Use as group image', 'learningcenter' ),
						'insert_into_item'      => __( 'Insert into group', 'learningcenter' ),
						'uploaded_to_this_item' => __( 'Uploaded to this group', 'learningcenter' ),
						'filter_items_list'     => __( 'Filter groups', 'learningcenter' ),
						'items_list_navigation' => __( 'Groups navigation', 'learningcenter' ),
						'items_list'            => __( 'Groups list', 'learningcenter' ),
					),
					'description'         => __( 'This is where you can add new groups to your site.', 'learningcenter' ),
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'map_meta_cap'        => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
					'query_var'           => true,
					'supports'            => $supports,
					'has_archive'         => false,
					'show_in_nav_menus'   => true,
					'show_in_rest'        => true,
        )
      )
    );

    do_action( 'learningcenter_after_register_post_type' );
	}
}

LC_Post_Types::init();