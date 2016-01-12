<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
/*
 * Plugin Name: Ajax Modal Portal
 * Description: User registration and login modal style portal, powered by Ajax. This is a popup style user login, registration, lost password modal style login that does not require the page to refresh at any time during the user flow.
 * Author: Andy Coles
 * Version: 1.0
 * Tags: Login, Ajax, Registration, Modal, Portal
 */

class AjaxModalPortal {

  //option passed to plugin function
  public static $option;
  //current user
  public static $current_user;
  //form actions links
  public static $url_login;
  public static $url_register;
  public static $url_logout;
  
  public static function init(){
    //remember the current user
    self::$current_user = wp_get_current_user();

    //Generate URLs for login, remember, and register
    self::$url_login = self::get_login_link();
    self::$url_register = self::get_register_link();
    self::$url_logout = self::get_logout_link();

    //die(var_dump(self::$current_user));

    //loading assets
    if (!is_admin()) {
      $url_js = 'ajax-modal-portal.js';
      $url_css = 'ajax-modal-portal.css';
      $template_loc_test = self::locate_template_url($url_js);
      wp_enqueue_script('ajax-modal-portal', self::locate_template_url($url_js), array('jquery'));
      wp_enqueue_style('ajax-modal-portal', self::locate_template_url($url_css));
    }


    if (!is_user_logged_in()) {
      //die(var_dump(self::$current_user));
      self::ajax_login_init();
    }
    //add_action('login_redirect', 'AjaxModalPortal::loginRedirect');

  }

  public static function locate_template_url($template_path) {
    $content_path = '/plugin/ajax-modal-portal';
    if( file_exists(get_stylesheet_directory().$content_path.$template_path)) { 
      //Child Theme (or just theme)
      return trailingslashit(get_stylesheet_directory_uri()).$content_path.$template_path;
    }
    else if( file_exists(get_template_directory().$content_path.$template_path) ){ 
      //Parent Theme (if parent exists)
      return trailingslashit(get_template_directory_uri()).$content_path.$template_path;
    }
    //Default file in plugin folder
    return trailingslashit(plugin_dir_url(__FILE__))."/$template_path";
  }

  public static function get_current_page_link() {
    $path = untrailingslashit($_SERVER['REQUEST_URI']);
    $url = untrailingslashit(site_url()).'/'.$path;
    return $url;
  }

  public static function get_register_link() {
    $register_link = site_url('wp-login.php?action=register', 'login');
    return $register_link;
  }

  public static function get_login_link() {
    $login_link = site_url('wp-login.php','login_post');
    return $login_link;
  }

  public static function get_logout_link() {
    $logout_link = site_url('wp-login.php?action=logout', 'logout');
    return $logout_link;
  }

  public static function make_login_link() {
    $id = self::$option;
    $link_html = '<a href="'.self::get_login_link().
      '" class="amp-link-modal" id="'.$id.'">Log In</a>';
    echo $link_html;
  }

  public static function make_logout_link() {
    $link_html = '<a href="'.self::get_logout_link().
      '" class="amp-link">Log Out</a>';
    echo $link_html;
  }

  public static function make_register_link() {
    $id = self::$option;
    $link_html = '<a href="'.self::get_register_link().
      '" class="amp-link-modal" id="'.$id.'">Register</a>';
    echo $link_html;
  }

  public static function make_modal() {
    if (self::$option == 'register') {
      $form_name = 'register';
      $form_action = $form_name;
      $form_fields = 
        '<input type="text" id="amp-reg-user" placeholder="Username">'.
        '<input type="text" id="amp-reg-pw" placeholder="Password">'.
        '<input type="text" id="amp-reg-pw-confirm" placeholder="Confirm Password">'.
        '<input type="email" id="amp-reg-email" placeholder="Email">';
      $submit_elems = 
        '<input type="submit" id="amp-reg-submit" name="wp-submit" value="Register">';
    }
    else if (self::$option == 'login') {
      $form_name = 'login';
      $form_action = $form_name;
      $url_redirect = self::get_current_page_link();
      $form_fields = 
        '<input type="text" id="amp-login-user" name="username" placeholder="Username">'.
        '<input type="password" id="amp-login-pw" name="password" placeholder="Password">'.
        '<input type="checkbox" id="rememberme" name="rememberme" value="forever">';
      $submit_elems = 
        '<input type="submit" id="amp-login-submit" name="wp-submit" value="Log In">';
    }
    $modal_html = 
      '<div class="amp-modal" id="amp-modal-'.$form_name.'" style="display: none;">'.
        '<div class="amp-title-bar"></div>'.
        '<h4>'.ucfirst($form_name).'</h4>'.
        '<form name="amp-'.$form_name.'" class="amp-form" id="amp-form-'.$form_name.'" action="'.$form_action.'" method="post">'.
          $form_fields.
          $submit_elems.
          '<input type="hidden" name="ajax-modal-portal" value="'.$form_name.'">'.
          '<input type="hidden" name="action" value="ajaxlogin">'.
          wp_nonce_field('ajax-login-nonce','security').
        '</form>'.
      '</div>';
    echo $modal_html;
  }

  public static function init_content($args = '') {
    $default_args = array(
      'register' => false,
      'login' => false
    );
    $args = wp_parse_args($args, $default_args);
    if($args['register'] === true) {
      self::$option = 'register';
      if (!is_user_logged_in()) {
        self::make_register_link();
      }
    }
    else if($args['login'] === true) {
      self::$option = 'login';
      if (is_user_logged_in()) {
        echo 'Hello '.self::$current_user->display_name.', ';
        self::make_logout_link();
      } 
      else {
        self::make_login_link();
      }
    }
    self::make_modal();
  }

  public static function ajax_login_init() {
    //die(var_dump(self::$current_user));
    wp_localize_script( 'ajax-modal-portal', 'ajax_login_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Sending user info, please wait...')
    ));

    // Enable the user with no privileges to run ajax_login() in AJAX
    if (is_admin()) {
      add_action('wp_ajax_nopriv_ajaxlogin', 'AjaxModalPortal::ajax_login');
    }

  }
    
  public static function ajax_login() {
    //die(var_dump($current_user));
    check_ajax_referer('ajax-login-nonce', 'security');

    //after nonce validated, get POST
    $info = array();
    $info['user_login'] = $_POST['username'];
    $info['user_password'] = $_POST['password'];
    $info['remember'] = true;

    $user_signon = wp_signon($info, false);

    if(is_wp_error($user_signon)) {
      echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    }
    else {
      echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
    }

    die();

  }

}

add_action('init', 'AjaxModalPortal::init');
//template accessible function
function ajax_modal_portal($atts = '') {
  echo AjaxModalPortal::init_content($atts);
}


