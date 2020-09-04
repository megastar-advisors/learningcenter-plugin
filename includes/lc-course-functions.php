<?php
/**
 * LearningCenter Course Functions
 * 
 * Functions for course specific things.
 * 
 * @package LearningCenter\Functions
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_user_enrolled' ) ) {
  /**
   * Check if a user is enrolled in a course.
   * 
   * @param   int       $course_id  Course post ID.
   * @param   int|null  $user_id    User ID.
   * @return  bool
   */
  function is_user_enrolled( $course_id, $user_id ) {

  }
}

if ( ! function_exists( 'can_access_course_content' ) ) {
  /**
   * Check if a visitor can access course content.
   * 
   * This is just part of the check for lessons and quizes.
   * 
   * @param int     $course_id  Course post ID.
   * @param int     $user_id    User ID.
   * @param string  $context    Context that we're checking for course content access (`lesson`, `quiz`, `module`)
   */
  function can_access_course_content( $course_id, $user_id = null, $context  = 'lesson' ) {
    if ( null === $user_id) {
      $user_id = get_current_user();
    }
  }
}