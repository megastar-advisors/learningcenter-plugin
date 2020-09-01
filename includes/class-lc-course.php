<?php
/**
 * LearningCenter Course class.
 * 
 * All functionality pertaining to the Course Post Type in the LearningCenter.
 * 
 * @package LearningCenter\Course
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LC_Course class.
 */
class LC_Course {

  /**
   * @var $token
   */
  public $token;

  /**
   * @var array $meta_fields
   */
  public $meta_fields;

  /**
   * @var array The html allowed for message boxes.
   */
  public static $allowed_html;

  /**
   * Constructor.
   * 
   * @since 1.0.0
   */
  public function __construct() {
    $this->token = 'course';

    if ( is_admin() ) {
      add_action( 'add_meta_boxes', array( $this, 'meta_box_setup' ), 20 );
      add_action( 'save_post', array( $this, 'meta_box_save' ) );

      add_filter( 'manage_course_posts_columns', array( $this, 'add_column_headings' ), 10, 1 );
      add_action( 'manage_course_posts_custom_column', array( $this, 'add_column_data' ), 10, 2 );

      add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
    }

    self::$allowed_html = array(
      'embed'   => array(),
      'iframe'  => array(
        'width'           => array(),
        'height'          => array(),
        'src'             => array(),
        'frameborder'     => array(),
        'allowfullscreen' => array()
      ),
      'video'   => array(),
      'source'  => array()
    );

    // Update course completion upon completion of a lesson.
    add_action( 'learningcenter_user_lesson_end', array( $this, 'update_status_after_lesson_change' ), 10, 2 );
    // Update course completion upon reset of a lesson
    add_action( 'learningcenter_user_lesson_reset', array( $this, 'update_status_after_lesson_change' ), 10, 2 );
    // Update course completion upon grading of a quiz.
    add_action( 'learningcenter_user_quiz_grade', array( $this, 'update_status_after_quiz_submission' ), 10, 2 );


  }

  /**
   * Register and enqueue scripts that are needed in the backend.
   * 
   * @access private
   * @since 1.0.0
   */
  public function register_admin_scripts() {
    $screen = get_current_screen();

    if( 'course' === $screen->id ) {
      
    }
  }

  /**
	 * course_lessons function.
	 *
	 * @access public
	 * @param int    $course_id (default: 0)
	 * @param string $post_status (default: 'publish')
	 * @param string $fields (default: 'all'). WP only allows 3 types, but we will limit it to only 'ids' or 'all'
	 * @return array{ type WP_Post }  $posts_array
	 */
	public function course_lessons( $course_id = 0, $post_status = 'publish', $fields = 'all' ) {

		if ( is_a( $course_id, 'WP_Post' ) ) {
			$course_id = $course_id->ID;
		}

		$post_args     = array(
			'post_type'        => 'lesson',
			'posts_per_page'   => -1,
			'orderby'          => 'date',
			'order'            => 'ASC',
			'meta_query'       => array(
				array(
					'key'   => '_lesson_course',
					'value' => intval( $course_id ),
				),
			),
			'post_status'      => $post_status,
			'suppress_filters' => 0,
		);
		$query_results = new WP_Query( $post_args );
		$lessons       = $query_results->posts;

		// re order the lessons. This could not be done via the OR meta query as there may be lessons
		// with the course order for a different course and this should not be included. It could also not
		// be done via the AND meta query as it excludes lesson that does not have the _order_$course_id but
		// that have been added to the course.
		if ( count( $lessons ) > 1 ) {

			foreach ( $lessons as $lesson ) {

				$order = intval( get_post_meta( $lesson->ID, '_order_' . $course_id, true ) );
				// for lessons with no order set it to be 10000 so that it show up at the end
				$lesson->course_order = $order ? $order : 100000;
			}

			uasort( $lessons, array( $this, '_short_course_lessons_callback' ) );
		}

		/**
		 * Filter runs inside course_lessons function 
		 *
		 * Returns all lessons for a given course
		 *
		 * @param array $lessons
		 * @param int $course_id
		 */
		$lessons = apply_filters( 'learningcenter_course_get_lessons', $lessons, $course_id );

		// return the requested fields
		// runs after the sensei_course_get_lessons filter so the filter always give an array of lesson
		// objects
		if ( 'ids' == $fields ) {
			$lesson_objects = $lessons;
			$lessons        = array();

			foreach ( $lesson_objects as $lesson ) {
				$lessons[] = $lesson->ID;
			}
		}

		return $lessons;

  }
  
  /**
	 * Used for the uasort in $this->course_lessons()
	 *
	 * @since 1.8.0
	 * @access protected
	 *
	 * @param array $lesson_1
	 * @param array $lesson_2
	 * @return int
	 */
	protected function _short_course_lessons_callback( $lesson_1, $lesson_2 ) {

		if ( $lesson_1->course_order == $lesson_2->course_order ) {
			return 0;
		}

		return ( $lesson_1->course_order < $lesson_2->course_order ) ? -1 : 1;
	}

  public static function meta_box_setup() {
      
  }
}