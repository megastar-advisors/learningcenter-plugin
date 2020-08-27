<?php
/**
 * LearningCenter Advisor Functions
 * 
 * Functions for advisors.
 * 
 * @package LearningCenter\Functions
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lc_create_new_advisor' ) ) {

  /**
   * Create new advisor.
   * 
   * @param   string  $email    Advisor email.
   * @param   string  $username Advisor username.
   * @param   string  $password Advisor password.
   * @param   array   $args     List of arguments to pass to `wp_insert_user()`.
   * @return  int|WP_Error Returns WP_Error on failure, Init (user ID) on success.
   */
}

/**
 * Create a unique username for a new advisor.
 * 
 * @since 1.0.0
 * @param   string  $email          New advisor email address.
 * @param   array   $new_user_args  Array of new user args, maybe including first and last names.
 * @param   string  $suffix         Append string to username to make it unique.
 * @return  string  Generated username.
 */
function lc_create_new_advisor_username( $email, $new_user_args = array(), $suffix = '' ) {
  $username_parts = array();


}

/**
 * Login a advisor (set auth cookie and set global user object).
 * 
 * @param int $advisor_id Advisor ID.
 */
function lc_set_advisor_auth_cookie( $advisor_id ) {
  wp_set_current_user( $advisor_id );
  wp_set_auth_cookie( $advisor_id, true );

  // Update session.
  LC()->session->init_session_cookie();
}

/**
 * Checks if current user has a role.
 * 
 * @param   string  $role The role.
 * @return  bool
 */
function lc_current_user_has_role( $role ) {
  return lc_user_has_role( wp_get_current_user(), $role );
}