<?php
/**
 * Plugin Name: Jetstash Connect
 * Plugin URI: https://www.jetstash.com/connect
 * Description: Dynamically pulls forms from Jetstash and integrates them via a shortcode into the theme.
 * Version: 0.1.0
 * Author: Jetstash
 * Author URI: https://www.jetstash.com
 */

class JetstashConnect
{

  /**
   * Define the private class vars
   *
   * @var $version string
   * @var $environment string
   * @var $apiUrl string
   * @var settings object||false
   */
  private $version, $environment, $apiUrl, $settings;

  /**
   * Construct function
   *
   * @return void
   */
  function __construct()
  {
    $this->version     = '0.1.0';
    add_action('admin_init', array($this, 'checkVersion'));
    if(!$this->compatibleVersion()) return;

    $this->setEnvironment();
    $this->setVersion();
    $this->setSettings();
    add_action('admin_menu', array(&$this,'loadAdminPanel'));
  }

  /**
   * Sets our version number in the wp_options table
   *
   * @return void
   */
  protected function setVersion()
  {
    $version = get_option('jetstash_connect_version');
    if($version !== $this->version) {
      update_option('jetstash_connect_version', $this->version);
    }
  }

  /**
   * Sets the environment of the plugin, defaults to production
   *
   * @return void
   */
  protected function setEnvironment()
  {
    $base = plugin_dir_path( __FILE__ );
    if(file_exists($base.'env_local')) {
      $this->environment = 'local';
      $this->apiUrl      = 'http://api.jetstash.dev'; 
    } elseif(file_exists($base.'env_staging')) {
      $this->environment = 'staging';
      $this->apiUrl      = 'http://qa.api.jetstash.com';
    } else {
      $this->environment = 'production';
      $this->apiUrl      = 'https://api.jetstash.com';
    }
  }

  /**
   * Get our settings from the wp_options table
   *
   * @return void
   */
  protected function setSettings()
  {
    $settings = get_option('jetstash_connect_settings');
    if($settings !== false) {
      $this->settings = unserialize($settings);
    } else {
      $this->settings = false;
    }
  }

  /**
   * Takes post data and pushes it to the database
   *
   * @param array
   *
   * @return object
   */
  public static function updateSettings($post) {
    $settings = new StdClass();
    $settings->api_key = isset($post['api_key']) ? $post['api_key'] : false;
    $settings->user    = isset($post['user']) ? $post['user'] : false;
    $cerealSettings = serialize($settings);
    update_option('jetstash_connect_settings', $cerealSettings);

    $settings->error         = false;
    $settings->error_message = false;
    return $settings;
  }

  /**
   * Check to make sure current state of environment meets plugin needs
   *
   * @return bool
   */
  static function compatibleVersion()
  {
    if(version_compare($GLOBALS['wp_version'], '3.9', '<')) {
      return false;
    }
    return true;
  }

  /**
   * Check the WordPress version against what the plugin supports
   *
   * @return void
   */
  function checkVersion()
  {
    if(!self::compatibleVersion()) {
      if(is_plugin_active(plugin_basename(__FILE__))) {
        deactivate_plugins(plugin_basename( __FILE__ ));
        add_action('admin_notices', array($this, 'pluginDisabled'));
        if(isset($_GET['activate'])) unset($_GET['activate']);
      }
    }
  }

  /**
   * Disabled plugin messaging
   *
   * @return string
   */
  function pluginDisabled()
  {
    echo '<strong>'.esc_html__('Jetstash Connect requires one of the latest 3 versions of WordPress.', 'JetstashConnect').'</strong>';
  }

  /**
   * Activation check
   *
   *
   *
   */
  static function activationCheck()
  {
    if(!self::compatibleVersion()) {
      deactivate_plugins(plugin_basename(__FILE__));
      wp_die(__('Jetstash Connect requires one of the latest 3 versions of WordPress.', 'JetstashConnect'));
    }
  }

  /**
   * Load the options panel
   *
   * @return void
   */
  function loadAdminPanel()
  {
    add_options_page( 'Jetstash Connect', 'Jetstash Connect', 'administrator', 'jetstash_connect', array(&$this,'loadAdminPanelTemplates'));
  }

  /**
   * Load the admin options panel templates
   *
   * @return void
   */
  function loadAdminPanelTemplates()
  {
    include('admin/options.php');
  }

  /**
   * Retrieves a users available forms from via the Jetstash API
   *
   * @return void
   */
  protected function retrieveForms()
  {
    $endpoint = '/user/forms';
  }

  /**
   * Retrieves the field sets for a single form field
   *
   * @param string
   *
   * @return 
   */
  protected function retrieveSingleFormFields($formId)
  {
    $endpoint = '/form/structure';
  }

  /**
   * Perform the api get requests
   *
   * @param string
   *
   * @return object
   */
  private function handleGetRequest($endpoint)
  {
    $url  = $this->apiUrl.'/v1'.$endpoint.'?api_key='.$this->settings->api_key.'&user='.$this->settings->user;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    $data = curl_exec($curl);
    curl_close($curl);

    return json_decode($data);
  }

  /**
   * Parse the shortcode from the page/post/etc
   *
   * @param array||string
   *
   * @return string
   */
  private function connectShortcode($atts) {
    $flags = shortcode_atts(array(
      'form_id' => null,
    ), $atts);
  }

}
new JetstashConnect();

register_activation_hook(__FILE__, array('JetstashConnect', 'activationCheck'));
