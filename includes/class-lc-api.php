<?php
/**
 * LC-API endpoint handler.
 * 
 * This handles API relatedfunctionality in the LearningCenter.
 * 
 * @package LearningCenter\API
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LC-API class.
 */
class LC_API {

  /**
   * Rest API prefix.
   */
  private $prefix = 'learningcenter/v1';
  
  /**
   * Init the API by setting up action and filter hooks.
   */
  public function __construct() {
    add_action( 'rest_api_init', array( $this, 'register_routes' ) );
  }

  public function  register_routes() {
 
    // register_rest_route( $this->prefix, '/course/(?P<id>\d+)', array(
    //   'methods' => 'GET',
    //   'callback' => array( $this, 'get_single_course' )
    // ) );

    register_rest_route( $this->prefix, '/users/', array(
      'methods' => 'GET',
      'callback' => array( $this, 'get_learningcenter_users' )
    ) );
  }

  public function get_single_course( $request ) {
    $course_id = $request['id'];

    
  }

  public function get_learningcenter_users() {
    $users = get_users();

    if ( empty( $users ) ) {
      return null;
    }
    return $users;
  }

}

new LC_API();