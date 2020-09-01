<?php
/**
	 * Sensei_Teacher::create_teacher_role
	 *
	 * This function checks if the role exist, if not it creates it.
	 * for the teacher role
	 *
	 * @since 1.8.0
	 * @access public
	 * @return void
	 */

defined( 'ABSPATH' ) || exit;

class LC_Advisors {

  /**
   * Advisor role.
   * 
   * Keeps a reference to the advisor role object.
   * 
   * @access protected
   * @since 1.0.0
   */
  protected $advisor_role;

  /**
   * Advisor constructor
   * 
   * @access public
   * @since 1.0.0
   */
  public function __construct() {
    add_action( 'after_setup_theme', array( $this, 'my_add_role_function' ) );
  }

  /**
   * Create advisor role.
   * 
   * This function checks if the role exists, if not it creates it.
   * 
   * @since 1.0.0
   * @return void
   */
	public function create_role() {

		// check if the role exists
		$this->advisor_role = get_role( 'teacher' );

		// if the the advisor is not a valid WordPress role create it
		if ( ! is_a( $this->advisor_role, 'WP_Role' ) ) {
			// create the role
			$this->advisor_role = add_role( 'advisor', __( 'Advisor', 'learningcenter' ) );
		}

		// add the capabilities before returning
		$this->add_capabilities();

	}//end create_role()

	/**
	 * Add advisor capabilities.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_capabilities() {

		// if this is not a valid WP_Role object exit without adding anything
		if ( ! is_a( $this->advisor_role, 'WP_Role' ) || empty( $this->advisor_role ) ) {
			return;
		}

		/**
		 * Advisor capabilities array filter
		 *
		 * These capabilities will be applied to the advisor role
		 *
		 * @param array $capabilities
		 * keys: (string) $cap_name => (bool) $grant
		 */
		$caps = apply_filters(
			'learningcenter_advisor_role_capabilities',
			array(
				// General access rules
				'read'                           => true,
				'manage_sensei_grades'           => true,
				'moderate_comments'              => true,
				'upload_files'                   => true,
				'edit_files'                     => true,

				// Lessons
				'publish_lessons'                => true,
				'manage_lesson_categories'       => true,
				'edit_lessons'                   => true,
				'edit_published_lessons'         => true,
				'edit_private_lessons'           => true,
				'read_private_lessons'           => true,
				'delete_published_lessons'       => true,

				// Courses
				'create_courses'                 => true,
				'publish_courses'                => false,
				'manage_course_categories'       => true,
				'edit_courses'                   => true,
				'edit_published_courses'         => true,
				'edit_private_courses'           => true,
				'read_private_courses'           => true,
				'delete_published_courses'       => true,

				// Quiz
				'publish_quizzes'                => true,
				'edit_quizzes'                   => true,
				'edit_published_quizzes'         => true,
				'edit_private_quizzes'           => true,
				'read_private_quizzes'           => true,

				// Questions
				'publish_questions'              => true,
				'edit_questions'                 => true,
				'edit_published_questions'       => true,
				'edit_private_questions'         => true,
				'read_private_questions'         => true,

				// messages
				'publish_sensei_messages'        => true,
				'edit_sensei_messages'           => true,
				'edit_published_sensei_messages' => true,
				'edit_private_sensei_messages'   => true,
				'read_private_sensei_messages'   => true,

				// Comments -
				// Necessary cap so Advisors can moderate comments
				// on their own lessons. We restrict access to other
				// post types in $this->restrict_posts_menu_page()
				'edit_posts'                     => true,

			)
		);

		foreach ( $caps as $cap => $grant ) {

			// load the capability on to the advisor role
			$this->advisor_role->add_cap( $cap, $grant );

		} // End foreach().

	}
}

new LC_Advisors();