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

  public static function meta_box_setup() {
      
  }
}