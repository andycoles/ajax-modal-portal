<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
class ProcessQuery {

  public static function route_request() {
    if($_POST) {
      if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        $output = [
          'type' => 'error',
          'msg' => 'Sorry Request must be an Ajax POST'
        ];
        echo $output;
      }
      switch($_POST['ajax-modal-portal']) {
        case 'login':
          $output = self::login();
          break;
        default:
          $output = self::default_action();
      }
      /*
      $user_name = filter_var($_POST['log'], FILTER_SANITIZE_STRING);
      $user_password = filter_var($_POST['pwd'], FILTER_SANITIZE_STRING);

      $output = json_encode(array('user_name'=>$user_name, 'user_password'=>$user_password));
      echo $output;
      */
    }
    echo $output;
  }

  private static function default_action() {
    $return = array(
      'type' => 'error',
      'msg' => 'ajax-modal-portal directive not supported'
    );
    return $return;
  }

  private static function login() {
    $return = json_encode(array(
      'type' => 'sucess',
      'msg' => 'the login function was called',
      'user_login' => $_POST['log'],
      'user_password' => $_POST['pwd']
    ));
    $creds = array('user_login' => $_POST['log'], 'user_password' => $_POST['pwd'], 'remember' => true);
    //return $return;
    $results = wp_signon($creds);
    $ret_obj = json_encode($results);
    return $ret_obj;
  }

}
$processor = new ProcessQuery;
$processor->route_request();
?>
