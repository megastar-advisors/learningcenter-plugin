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
    add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
    add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );

    add_action( 'admin_menu', array( __CLASS__, 'remove_lessons_menu_model_taxonomy' ), 10 );
		//add_action( 'admin_menu', array( __CLASS__, 'remove_courses_menu_model_taxonomy' ), 10 );
    add_action( 'admin_menu', array( __CLASS__, 'redirect_to_lesson_module_taxonomy_to_course' ), 20 );
    
    add_action( 'add_meta_boxes', array( __CLASS__, 'modules_metaboxes' ), 20, 2 );

    // store new modules created on the course edit screen
		add_action( 'wp_ajax_sensei_add_new_module_term', array( __CLASS__, 'add_new_module_term' ) );
		add_action( 'wp_ajax_sensei_get_course_modules', array( __CLASS__, 'ajax_get_course_modules' ) );

    //add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_styles' ) );
    add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 20, 2 );
    add_action( 'admin_init', array( __CLASS__, 'remove_default_modules_box' ) );

    add_action( 'edited_module', array( __CLASS__, 'save_module_course' ), 10, 2 );
		add_action( 'created_module', array( __CLASS__, 'save_module_course' ), 10, 2 );
    //add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
    //add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
    //add_action( 'learningcenter_after_register_post_type', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );
    //add_action( 'learningcenter_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
  }

  /**
	 * Save module course on add/edit
	 *
	 * @since 1.8.0
	 * @param  int $module_id ID of module.
	 * @return void
	 */
	public function save_module_course( $module_id ) {
		/*
		 * It is safe to ignore nonce verification here because this is called
		 * from `edited_{$taxonomy}` and `created_{$taxonomy}` on a post to
		 * `edit-tags.php`, which occur after WordPress performs its own nonce
		 * verification.
		 */

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['action'] ) && 'inline-save-tax' == $_POST['action'] ) {
			return;
		}
		// Get module's existing courses
		$args    = array(
			'post_type'      => 'course',
			'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'module',
					'field'    => 'id',
					'terms'    => $module_id,
				),
			),
		);
		$courses = get_posts( $args );

		// Remove module from existing courses
		if ( isset( $courses ) && is_array( $courses ) ) {
			foreach ( $courses as $course ) {
				wp_remove_object_terms( $course->ID, $module_id, 'module' );
			}
		}

		// Add module to selected courses
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['module_courses'] ) && ! empty( $_POST['module_courses'] ) ) {

			// phpcs:ignore WordPress.Security.NonceVerification
			$course_ids = is_array( $_POST['module_courses'] ) ? $_POST['module_courses'] : explode( ',', $_POST['module_courses'] );

			foreach ( $course_ids as $course_id ) {

				wp_set_object_terms( absint( $course_id ), $module_id, $this->taxonomy, true );

			}
		}
	}


  /**
	 * Remove modules metabox that come by default
	 * with the modules taxonomy. We are removing this as
	 * we have created our own custom meta box.
	 */
	public static function remove_default_modules_box() {

		remove_meta_box( 'tagsdiv-module', 'course', 'side' );

	}

  /**
	 * Submits a new module term prefixed with the
	 * the current author id.
	 *
	 * @since 1.8.0
	 */
	public static function add_new_module_term() {

		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], '_ajax_nonce-add-module' ) ) {
			wp_send_json_error( array( 'error' => 'wrong security nonce' ) );
		}

		// get the term an create the new term storing infomration
		$term_name = sanitize_text_field( $_POST['newTerm'] );

		if ( current_user_can( 'manage_options' ) ) {

			$term_slug = str_ireplace( ' ', '-', trim( $term_name ) );

		} else {

			$term_slug = get_current_user_id() . '-' . str_ireplace( ' ', '-', trim( $term_name ) );

		}

		$course_id = sanitize_text_field( $_POST['course_id'] );

		// save the term
		$slug = wp_insert_term( $term_name, 'module', array( 'slug' => $term_slug ) );

		// send error for all errors except term exits
		if ( is_wp_error( $slug ) ) {

			// prepare for possible term name and id to be passed down if term exists
			$term_data = array();

			// if term exists also send back the term name and id
			if ( isset( $slug->errors['term_exists'] ) ) {

				$term              = get_term_by( 'slug', $term_slug, 'module' );
				$term_data['name'] = $term_name;
				$term_data['id']   = $term->term_id;

				// set the object terms
				wp_set_object_terms( $course_id, $term->term_id, 'module', true );
			}

			wp_send_json_error(
				array(
					'errors' => $slug->errors,
					'term'   => $term_data,
				)
			);

		}

		// make sure the new term is checked for this course
		wp_set_object_terms( $course_id, $slug['term_id'], 'module', true );

		// Handle request then generate response using WP_Ajax_Response
		wp_send_json_success(
			array(
				'termId'   => $slug['term_id'],
				'termName' => $term_name,
			)
		);

	}

	/**
	 * Get course modules
	 */
	public function ajax_get_course_modules() {
		// Security check
		check_ajax_referer( 'get-course-modules', 'security' );

		$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : null;
		if ( null === $course_id ) {
			wp_send_json_error( array( 'error' => 'invalid course id' ) );
		}

		$html_content = $this->render_module_select_for_course( $course_id );

		wp_send_json_success( array( 'content' => $html_content ) );
	}

  /**
   * Load admin JS.
   * 
   * @since   1.0.0
   * @return void
   */
  public function admin_enqueue_scripts( $hook ) {
    $screen = get_current_screen();

    // Only load module scripts when adding or editing modules or courses.
    $screen_related = $screen && ( 'module' === $screen->taxonomy || 'course' === $screen->id );

    if ( ! $screen_related ) {
      return;
    }

    wp_enqueue_script( 'modules-admin', plugins_url( '/', LC_PLUGIN_FILE ) . 'assets/js/modules-admin.js', array( 'jquery' ), true );

    // localized module data
		$localize_modulesAdmin = array(
			'search_courses_nonce'  => wp_create_nonce( 'search-courses' ),
			'getCourseModulesNonce' => wp_create_nonce( 'get-course-modules' ),
			'selectPlaceholder'     => __( 'Search for courses', 'learningcenter' ),
		);

		wp_localize_script( 'modules-admin', 'modulesAdmin', $localize_modulesAdmin );
  }
 
  /**
   * Register core taxonomies
   */
  public static function register_taxonomies() {

    if ( ! is_blog_installed() ) {
      //return;
    }

    if ( taxonomy_exists( 'module') ) {
      //return;
    }

    register_taxonomy(
      'module',
      apply_filters( 'learningcenter_taxonomy_objects_module', array( 'course', 'lesson' ) ),
      apply_filters( 
        'learningcenter_taxonomy_args_module',
        array(
          'hierarchical'      => false,
					'show_ui'           => true,
          'show_in_nav_menus' => false,
          'show_admin_columns' => true,
					'query_var'         => is_admin(),
          'rewrite'           => false,
					'public'            => false,
					'labels'            => array(
            'name' => _x( 'Modules', 'taxonomy general name' ),
            'singular_name' => _x( 'Module', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search Modules' ),
            'all_items' => __( 'All Modules' ),
            'parent_item' => __( 'Parent Module' ),
            'parent_item_colon' => __( 'Parent Module:' ),
            'edit_item' => __( 'Edit Module' ), 
            'update_item' => __( 'Update Module' ),
            'add_new_item' => __( 'Add New Module' ),
            'new_item_name' => __( 'New Module Name' ),
            'menu_name' => __( 'Module' ),
          )
        )
      )
    );
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

    do_action( 'learningcenter_after_register_post_type' );
  }

  /**
	 * When the wants to edit the lesson modules redirect them to the course modules.
	 *
	 * This function is hooked into the admin_menu
	 *
	 * @since   1.0.0
	 * @return void
	 */
	function redirect_to_lesson_module_taxonomy_to_course() {

		global $typenow , $taxnow;

		if ( 'lesson' == $typenow && 'module' == $taxnow ) {
			wp_safe_redirect( esc_url_raw( 'edit-tags.php?taxonomy=module&post_type=course' ) );
		}

	}

  /**
   * Remove module menu item under lessons
   * 
   * This function is hooked ino the admin_menu
   * 
   * @since   1.0.0
   * @return  void
   */
  public function remove_lessons_menu_model_taxonomy() {
    global $submenu;

		if ( ! isset( $submenu['edit.php?post_type=lesson'] ) || ! is_array( $submenu['edit.php?post_type=lesson'] ) ) {
			return; // exit
		}

		$lesson_main_menu = $submenu['edit.php?post_type=lesson'];
		foreach ( $lesson_main_menu as $index => $sub_item ) {

			if ( 'edit-tags.php?taxonomy=module&amp;post_type=lesson' == $sub_item[2] ) {
				unset( $submenu['edit.php?post_type=lesson'][ $index ] );
			}
		}
  }

  /**
	 * Completely remove the second modules under courses
	 *
	 * This function is hooked into the admin_menu
	 *
	 * @since   1.0.0
	 * @return void
	 */
	public function remove_courses_menu_model_taxonomy() {
		global $submenu;

		if ( ! isset( $submenu['edit.php?post_type=course'] ) || ! is_array( $submenu['edit.php?post_type=course'] ) ) {
			return; // exit
		}

		$course_main_menu = $submenu['edit.php?post_type=course'];
		foreach ( $course_main_menu as $index => $sub_item ) {

			if ( 'edit-tags.php?taxonomy=module&amp;post_type=course' == $sub_item[2] ) {
				unset( $submenu['edit.php?post_type=course'][ $index ] );
			}
		}
  }

  /**
   * Hook into all meta boxes related to the modules taxonomy.
   * 
   * @since 1.0.0
   * @param   string  $post_type
   * @param   WP_Post $post
   * @return  void
   */
  public function modules_metaboxes( $post_type, $post ) {
    if ( 'lesson' == $post_type ) {

      // remove default metabox from lesson edit screen.
      remove_meta_box( 'module' . 'div', 'lesson', 'side' );

      // add custom metabox to limit module selection to one per lesson.
      add_meta_box( 'module' . '_select', __( 'Module', 'learningcenter' ), array( __CLASS__, 'lesson_module_metabox' ), 'lesson', 'side', 'default' );
    } elseif ( 'course' == $post_type ) {
      // Course module selection metabox
      add_meta_box( 'module' . '_course_mb', __( 'Course Modules', 'learningcenter' ), array( __CLASS__, 'course_module_metabox' ), 'course', 'side', 'core' );
    }
  }
  
  /**
   * Display the modules taxonomy terms metabox.
   * 
   * @since 1.0.0
   * 
   * @hooked into add_meta_box.
   * 
   * @param WP_Post $post Post object.
   */
  public function course_module_metabox( $post ) {

    $tax_name = 'module';
    $taxonomy = get_taxonomy( $tax_name );

    ?>
    <div id="taxonomy-<?php echo esc_attr( $tax_name ); ?>" class="categorydiv">
			<div id="<?php echo esc_attr( $tax_name ); ?>-all" class="tabs-panel">
				<?php
				$name = ( $tax_name === 'category' ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
				echo "<input type='hidden' name='" . esc_attr( $name ) . "[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
				?>
				<ul id="<?php echo esc_attr( $tax_name ); ?>checklist" data-wp-lists="list:<?php echo esc_attr( $tax_name ); ?>" class="categorychecklist form-no-clear">
					<?php
					wp_terms_checklist(
						$post->ID,
						array(
							'taxonomy' => $tax_name,
						)
					);
					?>
				</ul>
			</div>
			<?php if ( current_user_can( $taxonomy->cap->edit_terms ) ) : ?>
				<div id="<?php echo esc_attr( $tax_name ); ?>-adder" class="wp-hidden-children">
					<h4>
						<a id="sensei-<?php echo esc_attr( $tax_name ); ?>-add-toggle" href="#<?php echo esc_url( $tax_name ); ?>-add" class="hide-if-no-js">
							<?php
							/* translators: %s: add new taxonomy label */
							printf( esc_html__( '+ %s', 'sensei-lms' ), esc_html( $taxonomy->labels->add_new_item ) );
							?>
						</a>
					</h4>
					<p id="sensei-<?php echo esc_attr( $tax_name ); ?>-add" class="category-add wp-hidden-child">
						<label class="screen-reader-text" for="new<?php echo esc_attr( $tax_name ); ?>"><?php echo esc_html( $taxonomy->labels->add_new_item ); ?></label>
						<input type="text" name="new<?php echo esc_attr( $tax_name ); ?>" id="new<?php echo esc_attr( $tax_name ); ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $taxonomy->labels->new_item_name ); ?>" aria-required="true"/>
						<a class="button" id="sensei-<?php echo esc_attr( $tax_name ); ?>-add-submit" class="button category-add-submit"><?php echo esc_attr( $taxonomy->labels->add_new_item ); ?></a>
						<?php wp_nonce_field( '_ajax_nonce-add-' . $tax_name, 'add_module_nonce' ); ?>
						<span id="<?php echo esc_attr( $tax_name ); ?>-ajax-response"></span>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
  }

  /**
	 * Build content for custom module meta box
	 *
	 * @since 1.8.0
	 * @param  object $post Current post object
	 * @return void
	 */
	public function lesson_module_metabox( $post ) {
		// Get lesson course
		$lesson_course = get_post_meta( $post->ID, '_lesson_course', true );

		$html = '<div id="lesson-module-metabox-select">';

		// Only show module selection if this lesson is part of a course
		if ( $lesson_course && $lesson_course > 0 ) {

			// Get existing lesson module
			$lesson_module = self::get_lesson_module_if_exists( $post );

			$html .= self::render_module_select_for_course( $lesson_course, $lesson_module );

		} else {
			// translators: The placeholders are opening and closing <em> tags.
			$html .= '<p>' . sprintf( __( 'No modules are available for this lesson yet. %1$sPlease select a course first.%2$s', 'sensei-lms' ), '<em>', '</em>' ) . '</p>';
		} // End If Statement
		$html .= '</div>';

		echo wp_kses(
			$html,
			array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'input'  => array(
						'id'    => array(),
						'name'  => array(),
						'type'  => array(),
						'value' => array(),
					),
					'option' => array(
						'selected' => array(),
						'value'    => array(),
					),
					'select' => array(
						'class' => array(),
						'id'    => array(),
						'name'  => array(),
						'style' => array(),
					),
				)
			)
		);
  }
  
  /**
	 * Get the lesson module if it Exists. Defaults to 0 if none found.
	 *
	 * @param WP_Post $post The post.
	 * @return int
	 */
	private function get_lesson_module_if_exists( $post ) {
		// Get existing lesson module
		$lesson_module      = 0;
		$lesson_module_list = wp_get_post_terms( $post->ID, 'module' );
		if ( is_array( $lesson_module_list ) && count( $lesson_module_list ) > 0 ) {
			foreach ( $lesson_module_list as $single_module ) {
				$lesson_module = $single_module->term_id;
				break;
			}
		}
		return $lesson_module;
	}

	private function render_module_select_for_course( $lesson_course, $lesson_module = 0 ) {
		// Get the available modules for this lesson's course
		$modules = self::get_course_modules( $lesson_course );

		$html  = '';
		$html .= '<input type="hidden" name="' . esc_attr( 'woo_lesson_' . 'module' . '_nonce' ) . '" id="' . esc_attr( 'woo_lesson_' . 'module' . '_nonce' ) . '" value="' . esc_attr( wp_create_nonce( LC_PLUGIN_BASENAME ) ) . '" />';

		// Build the HTML to output
		if ( is_array( $modules ) && count( $modules ) > 0 ) {
			$html .= '<select id="lesson-module-options" name="lesson_module" class="widefat" style="width: 100%">' . "\n";
			$html .= '<option value="">' . esc_html__( 'None', 'sensei-lms' ) . '</option>';
			foreach ( $modules as $module ) {
				$html .= '<option value="' . esc_attr( absint( $module->term_id ) ) . '"' . selected( $module->term_id, $lesson_module, false ) . '>' . esc_html( $module->name ) . '</option>' . "\n";
			}
			$html .= '</select>' . "\n";
		} else {
			$course_url = admin_url( 'post.php?post=' . urlencode( $lesson_course ) . '&action=edit' );

			/*
			 * translators: The placeholders are as follows:
			 *
			 * %1$s - <em>
			 * %2$s - </em>
			 * %3$s - Opening <a> tag to link to the Course URL.
			 * %4$s - </a>
			 */
			$html .= '<p>' . wp_kses_post( sprintf( __( 'No modules are available for this lesson yet. %1$sPlease add some to %3$sthe course%4$s.%2$s', 'sensei-lms' ), '<em>', '</em>', '<a href="' . esc_url( $course_url ) . '">', '</a>' ) ) . '</p>';
		} // End If Statement
		return $html;
  }
  
  /**
	 * Get ordered array of all modules in course
	 *
	 * @since 1.8.0
	 *
	 * @param  integer $course_id ID of course
	 * @return array              Ordered array of module taxonomy term objects
	 */
	public function get_course_modules( $course_id = 0 ) {

		$course_id = intval( $course_id );
		if ( empty( $course_id ) ) {
			return array();
		}

		// Get modules for course
		$modules = wp_get_post_terms( $course_id, 'module' );

		// Get custom module order for course
		$order = self::get_course_module_order( $course_id );

		if ( ! $order ) {
			return $modules;
		}

		// Sort by custom order
		$ordered_modules   = array();
		$unordered_modules = array();
		foreach ( $modules as $module ) {
			$order_key = array_search( $module->term_id, $order );
			if ( $order_key !== false ) {
				$ordered_modules[ $order_key ] = $module;
			} else {
				$unordered_modules[] = $module;
			}
		}

		// Order modules correctly
		ksort( $ordered_modules );

		// Append modules that have not yet been ordered
		if ( count( $unordered_modules ) > 0 ) {
			$ordered_modules = array_merge( $ordered_modules, $unordered_modules );
		}

		// remove order key but maintain order
		$ordered_modules_with_keys_in_sequence = array();
		foreach ( $ordered_modules as $key => $module ) {

			$ordered_modules_with_keys_in_sequence[] = $module;

		}

		return $ordered_modules_with_keys_in_sequence;

  }
  
  /**
	 * Get module order for course
	 *
	 * @since 1.8.0
	 *
	 * @param  integer $course_id ID of course
	 * @return mixed              Module order on success, false if no module order has been saved
	 */
	public function get_course_module_order( $course_id = 0 ) {
		if ( $course_id ) {
			$order = get_post_meta( intval( $course_id ), '_module_order', true );
			return $order;
		}
		return false;
	}
}

LC_Post_Types::init();