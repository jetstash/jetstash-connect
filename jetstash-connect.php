<?php
/**
 * Plugin Name: Jetstash Connect
 * Plugin URI: https://www.jetstash.com/jetstash-connect
 * Description: Dynamically pulls forms from Jetstash and integrates them via a shortcode into the theme.
 * Version: 1.1.0
 * Author: Jetstash
 * Author URI: https://www.jetstash.com
 */

require_once(plugin_dir_path(__FILE__).'inc/JetstashConnectMarkup.php');

class JetstashConnect
{

  // use jetstash;

  /**
   * Define the private class vars
   *
   * @var $version string
   * @var $environment string
   * @var $baseDir string
   * @var $baseWeb
   */
  private $version, $environment, $markup, $baseDir, $baseWeb;

  /**
   * Define the public class vars
   *
   * @var settings object||false
   * @var $apiUrl string
   * @var bool
   */
  public $settings, $apiUrl, $test = false;

  /**
   * Construct function
   *
   * @return void
   */
  function __construct()
  {
    $this->version     = '1.1.0';
    add_action('admin_init', array($this, 'checkVersion'));
    if(!$this->compatibleVersion()) return;

    $this->setBases();
    $this->setEnvironment();
    $this->setVersion();
    $this->setSettings();
    $this->markup = new \jetstash\JetstashConnectMarkup();

    if(is_admin()) {
      add_action('admin_menu', array(&$this, 'loadAdminPanel'));
      add_action('admin_enqueue_scripts', array($this, 'loadAdminAssets'));
    } else {
      add_shortcode('jetstash', array(&$this, 'connectShortcode'));
      add_action('get_header', array(&$this, 'loadPublicAssets'));
      add_action('wp_ajax_jetstash_connect', array(&$this, 'submitForm'));
      add_action('wp_ajax_nopriv_jetstash_connect', array(&$this, 'submitForm'));
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Setup Plugin
  |--------------------------------------------------------------------------
  */

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
   * Check to make sure current state of environment meets plugin needs
   *
   * @return bool
   */
  static function compatibleVersion()
  {
    if(version_compare($GLOBALS['wp_version'], '4.0', '<')) {
      return false;
    }
    return true;
  }

  /**
   * Activation check
   *
   * @return void
   */
  static function activationCheck()
  {
    if(!self::compatibleVersion()) {
      deactivate_plugins(plugin_basename(__FILE__));
      wp_die(__('Jetstash Connect requires one of the latest 3 versions of WordPress.', 'JetstashConnect'));
    }
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
    $envs   = array('local', 'staging');
    $config = false;

    foreach($envs as $env) {
      if(file_exists($this->baseDir.'env_'.$env)) {
        $config            = file_get_contents($this->baseDir.'env_'.$env);
        $config            = json_decode($config);
        $this->environment = $env;
        $this->apiUrl      = $config->api_url;
        break;
      }
    }

    if(!$config) {
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
   * Parse the shortcode from the page/post/etc
   *
   * @param array||string
   *
   * @return string
   */
  public function connectShortcode($atts)
  {
    $flags = shortcode_atts(array(
      'form' => null,
    ), $atts);

    return $this->buildStructure($flags);
  }

  /*
  |--------------------------------------------------------------------------
  | Setup Admin
  |--------------------------------------------------------------------------
  */

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
   * Load the options panel
   *
   * @return void
   */
  function loadAdminPanel()
  {
    add_menu_page('Jetstash Connect', 'Jetstash Connect', 'administrator', 'jetstash_connect', array(&$this,'loadAdminPanelTemplates'), 'dashicons-forms', 80);
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
   * Sets the plugin base directory and base web url
   *
   * @return string
   */
  private function setBases()
  {
    $this->baseDir = plugin_dir_path(__FILE__);
    $this->baseWeb = plugins_url(null, __FILE__);
  }

  /**
   * Load public assets
   *
   * @return void
   */
  function loadPublicAssets()
  {
    wp_enqueue_script('jetstash-connect', $this->baseWeb.'/js/jetstash-app.js', array('jquery'), null, true);

    if(isset($this->settings->disable_stylesheet) && true !== $this->settings->disable_stylesheet) {
      wp_enqueue_style('jetstash-connect-css', $this->baseWeb.'/css/jetstash.css', false, $this->version);
    }
  }

  function loadAdminAssets()
  {
    wp_enqueue_script('jetstash-connect-admin', $this->baseWeb.'/js/jetstash-admin.js', array('jquery'), null, true);
  }

  /*
  |--------------------------------------------------------------------------
  | Admin Interactions
  |--------------------------------------------------------------------------
  */

  /**
   * Takes post data and pushes it to the database
   *
   * @param array
   *
   * @return object
   */
  public static function updateSettings($post)
  {
    $settings = new StdClass();
    $settings->api_key            = isset($post['api_key']) ? $post['api_key'] : false;
    $settings->user               = isset($post['user']) ? $post['user'] : false;
    $settings->success_message    = isset($post['success_message']) ? $post['success_message'] : false;
    $settings->cache_duration     = isset($post['cache_duration']) ? $post['cache_duration'] : false;
    $settings->disable_stylesheet = isset($post['disable_stylesheet']) ? true : false;
    $cerealSettings               = serialize($settings);
    update_option('jetstash_connect_settings', $cerealSettings);

    if(isset($post['invalidate_cache']) && isset($post['invalidate_form_id'])) {
      self::invalidateCache($post['invalidate_form_id']);
    }

    $settings->error         = false;
    $settings->error_message = false;
    return $settings;
  }

  /*
  |--------------------------------------------------------------------------
  | User Interactions
  |--------------------------------------------------------------------------
  */

  /**
   * Loads the CDATA to the page for consumption by the ajax script
   *
   * @return void
   */
  public function loadLocalizedData($form)
  {
    $parameters = array(
      'ajaxurl'     => admin_url('admin-ajax.php'),
      'nonce'       => wp_create_nonce('jetstash-connect'),
      'form_id'     => $form,
      'message'     => $this->settings->success_message,
      'environment' => $this->environment,
    );
    wp_localize_script('jetstash-connect', 'jetstashConnect', $parameters);
  }

  /**
   * Submits the form data to the mothership
   *
   * @param null|array (only pass an array for TESTING purposes)
   *
   * @return object
   */
  public function submitForm($test = null)
  {
    if($this->test && is_array($test)) {
      $nonce = $test['nonce'];
      $post  = $test['post'];
      $form  = $test['form'];
    } else {
      $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : null;
      $post  = isset($_POST['post']) ? $_POST['post'] : null;
      $form  = isset($_POST['form']) ? $_POST['form'] : null;
    }

    parse_str($post, $data);

    // Validate our nonce and also our hidden spam field
    if(wp_verify_nonce($nonce, 'jetstash-connect') === false && !$this->test) {
      return $this->ajaxResponse(false, 'Session expired, please refresh and try again.', $data);
    }
    if((!isset($data) || empty($data)) || (isset($data['first_middle_last_name']) && $data['first_middle_last_name'] !== "")) {
      return $this->ajaxResponse(false, 'No post was made, please refresh and try again.', $data);
    }

    $endpoint     = $this->apiUrl.'/v1/form/submit?form='.$form;
    $postResponse = json_decode($this->handlePostRequest($endpoint, $data));
    $response     = $this->ajaxResponse($postResponse->success, $postResponse->message, $data);

    return $response;
  }

  /**
   * Perform the api post requests
   *
   * @param data
   *
   * @return object
   */
  private function handlePostRequest($endpoint, $data)
  {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $endpoint);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:6.0.2) Gecko/20100101 Firefox/6.0.2");
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);

    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
  }


  /**
   * Builds our ajax response to the front end
   *
   * @param bool, string, array
   *
   * @return json
   */
  private function ajaxResponse($success, $message, $data = null)
  {
    $response = array (
      'success' => $success,
      'message' => $message,
      'data'    => $data,
    );

    if($this->test) {
      return json_encode($response);
    } else {
      exit(json_encode($response));
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Form Setup
  |--------------------------------------------------------------------------
  */

  /**
   * Build the structure 
   *
   */
  public function buildStructure($flags)
  {
    if(isset($flags['form']) && $flags['form'] !== null) {
      $structure = $this->retrieveSingleFormFields($flags['form']);

      if(isset($structure->data->status_code) && 403 === $structure->data->status_code) {
        self::invalidateCache($flags['form']);
      } else {
        $structure = $this->markup->compileMarkup($structure->data);
        $this->loadLocalizedData($flags['form']);

        return $structure;
      }
    }
  }

  /**
   * Builds our URLs for the GET requests
   *
   * @param string, array
   *
   * @return string
   */
  private function urlBuilder($endpoint, $queries)
  {
    $url = $this->apiUrl.'/v1'.$endpoint;
    $url = $url.'?api_key='.$this->settings->api_key;
    foreach($queries as $key=>$value) {
      $url = $url.'&'.$key.'='.$value;
    }
    return $url;
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
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $endpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    $data = curl_exec($curl);
    curl_close($curl);

    return json_decode($data);
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
    $endpoint  = $this->urlBuilder('/form/structure', array('form' => $formId));
    $structure = $this->cacheFormStructure($formId, $endpoint);
    return $structure;
  }

  /**
   * Retrieve our cached data or request a fresh set and cache that baby
   *
   * @param string
   *
   * @return object
   */
  private function cacheFormStructure($formId, $endpoint) {
    $time  = time();
    $cache = get_option('jetstash_connect_'.$formId);
    $cache = $cache ? json_decode($cache) : false;

    if($cache === false || $cache->data === null || ($time - $cache->time > $this->settings->cache_duration * 60)) {
      $cache = new StdClass();
      $cache->time = $time;
      $cache->data = $this->handleGetRequest($endpoint);
      $cache->data = json_decode($cache->data->users_form->form_structure);

      update_option('jetstash_connect_'.$formId, json_encode($cache));
    }

    return $cache;
  }

  /**
   * Invalidate form cache
   *
   * @param string
   *
   * @return void
   */
  private static function invalidateCache($formId)
  {
    delete_option('jetstash_connect_'.$formId);
  }

}

new JetstashConnect();
register_activation_hook(__FILE__, array('JetstashConnect', 'activationCheck'));
