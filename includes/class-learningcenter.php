<?php
/**
 * LearningCenter setup
 * 
 * @package LearningCenter
 * @since 1.0.0
 */

 defined( 'ABSPATH' ) || exit;

/**
 * Main LearningCenter class
 * 
 * @class LearningCenter
 */
final class LearningCenter {

  /**
   * LearningCenter version
   * 
   * @var string
   */
  public $version = '1.0.0';

  /**
   * The single instance of the class
   * 
   * @var LearningCenter
   * @since 1.0.0
   */
  protected static $_instance = null;

  /**
   * Main LearningCenter instance.
   * 
   * Ensures only one instance of LearningCenter is loaded or can be loaded.
   * 
   * @since 1.0.0
   * @static
   * @see LC()
   * @return LearningCenter - Main instance.
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    } 
    return self::$_instance;
  }

  /**
   * LearningCenter Constructor
   */
  public function __construct() {
    $this->define_constants();
    $this->includes();
    $this->init_hooks();
  }

  /**
   * Hook into actions and filters
   * 
   * @since 1.0.0
   */
  public function init_hooks() {
    add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
    add_action( 'init', array( $this, 'init' ), 0 );
    add_action( 'wp', array( $this, 'frontend_redirect' ) );

    // Disable gutenberg
    if ( version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' ) ) {
      add_filter( 'use_block_editor_for_post', '__return_false' ); // New, > 5 beta
    } else {
      add_filter( 'gutenberg_can_edit_post_type', '__return_false' ); // older
    }
  }

  /**
   * Define LC Constants
   */
  private function define_constants() {
    $this->define( 'LC_ABSPATH' , dirname( LC_PLUGIN_FILE ) . '/' );
    $this->define( 'LC_PLUGIN_BASENAME', plugin_basename( LC_PLUGIN_FILE ) );
		$this->define( 'LC_VERSION', $this->version );
		$this->define( 'LEARNINGCENTER_VERSION', $this->version );
		$this->define( 'LC_NOTICE_MIN_PHP_VERSION', '7.2' );
		$this->define( 'LC_NOTICE_MIN_WP_VERSION', '5.2' );
		$this->define( 'LC_PHP_MIN_REQUIREMENTS_NOTICE', 'wp_php_min_requirements_' . LC_NOTICE_MIN_PHP_VERSION . '_' . LC_NOTICE_MIN_WP_VERSION );
  }

  /**
   * Define constant if not already set.
   * 
   * @param string      $name   Constant name.
   * @param string|bool $value  Constant value.
   */
  private function define( $name, $value ) {
    if ( ! defined( $name ) ) {
      define( $name, $value );
    }
  }

  /**
   * Include required core files used in admin and on the frontend.
   */
  public function includes() {
    /**
     * Class autoloader.
     */
    include_once LC_ABSPATH . 'includes/class-lc-autoloader.php';

    /**
     * Core classes.
     */
    include_once LC_ABSPATH . 'includes/class-lc-post-types.php';
  }

  /**
   * Init LearningCenter when WordPress initialises.
   */
  public function init() {
    // Before init action
    do_action( 'before_learningcenter_init' );

    // Set up localisation.
    $this->load_plugin_textdomain();

    // Load webhooks.
    //$this->load_webhooks();

    // Init action.
    do_action( 'learningcenter_init' );
  }

  /**
   * Load localisation files.
   */
  public function load_plugin_textdomain() {
    if ( function_exists( 'determine_locale' ) ) {
      $locale = determine_locale();
    } else {
      $locale = is_admin() ? get_user_locale() : get_locale();
    }

    $locale = apply_filters( 'plugin_locale', $locale, 'learningcenter' );

    unload_textdomain( 'learningcenter' );
    load_textdomain( 'learningcenter', WP_LANG_DIR . '/learningcenter/learningcenter-' . $locale . '.mo' );
    load_plugin_textdomain( 'learningcenter', false, plugin_basename( dirname( LC_PLUGIN_FILE ) ) . '/il8n/languages' );
  }

  /**
   * Ensure theme and server variable compatability and setup image sizes.
   */
  public function setup_environment() {
    $this->add_thumbnail_support();
  }

  /**
   * Check user status and redirect.
   */
  public function frontend_redirect() {
    if ( ! is_admin() ) {
      global $wp_query;

			$post_ID     = get_the_id();
			$homepage_id = get_option( 'page_on_front' );
			$blogpage_id = get_option( 'page_for_posts' );

			if ( $homepage_id === $post_ID || $blogpage_id === $post_ID || is_front_page() ) {

				wp_safe_redirect( get_admin_url() ); // If not an individual post, we redirect to the standard admin.
				exit;

			} else {

				$post_edit_link = admin_url( 'post.php?post=' . $post_ID . '&action=edit' );

				if ( is_user_logged_in() ) {
					wp_safe_redirect( $post_edit_link ); // Logged in users go to the post edit screen.
					exit;
				} else {
					wp_safe_redirect( wp_login_url( $post_edit_link ) ); // Not logged in users take a detour via the login page.
					exit;
				}
			}
    }
  }

  /**
   * Ensure course thumbnail support is turned on.
   */
  private function add_thumbnail_support() {
    if( ! current_theme_supports( 'post-thumbnails' ) ) {
      add_theme_support( 'post-thumbnails' );
    }
    add_post_type_support( 'course', 'thumbnail' );
  }

  /**
   * Get the plugin url.
   * 
   * @return string
   */
  public function plugin_url() {
    return untrailingslashit( plugin_url( '/' . LC_PLUGIN_FILE ) );
  }

  /**
   * Get the plugin path.
   * 
   * @return string
   */
  public function plugin_path() {
    return untrailingslashit( plugin_dir_path( 'LC_PLUGIN_FILE' ) );
  }

  /**
   * Get Ajax URL.
   * 
   * @return string
   */
  public function ajax_url() {
    return admin_url( 'admin-ajax.php', 'relative' );
  }

  /**
   * Return LC API URL for a given request.
   * 
   * @param string    $request  Requested endpoint.
   * @param bool|null $ssl      If should use SSL, null if should auto detect. Default: null.
   * @return string
   */
  public function api_request_url( $request, $ssl = null ) {
    if ( is_null( $ssl ) ) {
      $scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
    } elseif( $ssl ) {
      $scheme = 'https';
    } else {
      $scheme = 'http';
    }

    if ( strstr( get_option( 'permalink_structure' ), '/index.php/' ) ) {
			$api_request_url = trailingslashit( home_url( '/index.php/lc-api/' . $request, $scheme ) );
		} elseif ( get_option( 'permalink_structure' ) ) {
			$api_request_url = trailingslashit( home_url( '/lc-api/' . $request, $scheme ) );
		} else {
			$api_request_url = add_query_arg( 'lc-api', $request, trailingslashit( home_url( '', $scheme ) ) );
		}

		return esc_url_raw( apply_filters( 'learningcenter_api_request_url', $api_request_url, $request, $ssl ) );
  }

  /**
   * Email Class.
   * 
   * @return WC_Emails
   */
  public function mailer() {
    return LC_Emails::instance();
  }

  /**
   * Is the LearningCenter Admin actively included in the LearningCenter core?
   * Based on the presence of a basic LC Admin function.
   * 
   * @return boolean
   */
  public function is_lc_admin_active() {
    return function_exists( 'lc_admin_url' );
  }
}