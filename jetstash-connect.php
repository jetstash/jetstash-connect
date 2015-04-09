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
   */
  private $version, $environment;

  /**
   * Define the public class vars
   *
   * @var settings object||false
   * @var $apiUrl string
   */
  public $settings, $apiUrl;

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

    add_shortcode('jetstash', array(&$this, 'connectShortcode'));
    add_action('admin_menu', array(&$this, 'loadAdminPanel'));
    add_action('get_header', array(&$this, 'loadPublicAssets'));
    add_action('wp_ajax_jetstash_connect', array(&$this, 'submitForm'));
    add_action('wp_ajax_nopriv_jetstash_connect', array(&$this, 'submitForm'));
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
    if(version_compare($GLOBALS['wp_version'], '3.8', '<')) {
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
   * Load public assets
   *
   * @return void
   */
  function loadPublicAssets()
  {
    if(!is_admin()) { 
      wp_enqueue_script('jetstash-connect', plugins_url() . '/jetstash-connect/js/jetstash-ajax.js', array('jquery'), null, true);
    }
    if(isset($this->settings->disable_stylesheet) && true !== $this->settings->disable_stylesheet) {
      wp_enqueue_style('jetstash-connect-css', plugins_url() . '/jetstash-connect/css/jetstash.css', false, $this->version);
    }
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
    $settings->invalidate_cache   = isset($post['invalidate_cache']) ? true : false;
    $cerealSettings               = serialize($settings);
    update_option('jetstash_connect_settings', $cerealSettings);

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
   * @return object
   */
  public function submitForm()
  {
    $nonce = $_POST['nonce'];
    $post  = $_POST['post'];
    $form  = $_POST['form'];

    parse_str($post, $data);

    // Validate our nonce and also our hidden spam field
    if(wp_verify_nonce($nonce, 'jetstash-connect') === false) {
      return $this->ajaxResponse(false, 'Session expired, please refresh and try again.', $data);
    }
    if((!isset($data) || empty($data)) || $data["first_middle_last_name"] !== "") {
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
    $data['json'] = true;

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

    exit(json_encode($response));
  }

  /*
  |--------------------------------------------------------------------------
  | Form Setup
  |--------------------------------------------------------------------------
  */

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
   * Build the structure 
   *
   */
  public function buildStructure($flags)
  {
    if(isset($flags['form']) && $flags['form'] !== null) {
      $structure = $this->retrieveSingleFormFields($flags['form']);
      if(isset($structure->data->status_code) && 403 === $structure->data->status_code) {
        $this->invalidateCache($flags['form']);
      } else {
        $structure = $this->compileMarkup($structure->data);
        $this->loadLocalizedData($flags['form']);

        return $structure;
      }
    }
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
    $endpoint = $this->urlBuilder('/form/structure', array('form' => $formId));
    $formStructure = $this->cacheFormStructure($formId, $endpoint);
    return $formStructure;
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
   * Invalidate cache on failure
   *
   * @param string
   *
   * @return void
   */
  private function invalidateCache($formId)
  {
    if($settings->invalidateCache) {
      update_option('jetstash_connect_'.$formId, 'null');
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Markup
  |--------------------------------------------------------------------------
  */

  /**
   * Compiles our markup to be pushed to the page via the shortcode
   *
   * @param array
   *
   * @return string
   */
  private function compileMarkup($fields)
  {
    if($fields) {
      $markup  = '<form id="jetstash-connect" role="form" method="post">';
      $markup .= '<input type="text" class="hidden" name="first_middle_last_name">';

      foreach($fields as $field) {
        if($field->type === 'text' || $field->type === 'tel' || $field->type === 'email') {
          $markup .= $this->compileMarkupInput($field);
        } elseif($field->type === 'checkbox') {
          $markup .= $this->compileMarkupCheckbox($field);
        } elseif($field->type === 'textarea') {
          $markup .= $this->compileMarkupTextarea($field);
        } elseif($field->type === 'radio') {
          if(isset($field->values)) {
            $markup .= $this->compileMarkupLabel($field);
            $markup .= $this->compileMarkupRadio($field, $field->values);
          }
        } elseif($field->type === 'select') {
          if(isset($field->values)) {
            $markup .= $this->compileMarkupSelect($field, $field->values);
          }
        }
      }

      $markup .= '<button type="submit" class="btn btn-default">Submit</button>';
      $markup .= '<p id="jetstash-error"></p>';
      $markup .= '</form>';
    } else {
      $markup = '<p>Jetstash Connect Error: Check your settings, no field structure was found.';
    }
    return $markup;
  }

  /**
   * Compile the form label
   *
   * @param object
   *
   * @return string
   */
  private function compileMarkupLabel($field)
  {
    $markup = '<label for="'.$field->field_name_adj.'">'.$field->field_name.'</label>';
    return $markup;
  }

  /**
   * Compiles the markup for all input field types
   *
   * @param object
   *
   * @return string
   */
  private function compileMarkupInput($field)
  {
    $markup  = '<div class="form-group">';
    $markup .= $this->compileMarkupLabel($field);
    $markup .= '<input type="'.($field->type).'" class="form-control" id="'.$field->field_name_adj.'" name="'.$field->field_name_adj.'"'.(isset($field->is_required) && $field->is_required === 'on' ? ' required' : '').'>';
    $markup .= '</div>';

    return $markup;
  }

  /**
   * Compiles the markup for all checkbox field types
   *
   * @param object
   *
   * @return string
   */
  private function compileMarkupCheckbox($field)
  {
    $markup  = '<div class="checkbox">';
    $markup .= '<label for="'.$field->field_name_adj.'">';
    $markup .= '<input type="checkbox" id="'.$field->field_name_adj.'" name="'.$field->field_name_adj.'"'.(isset($field->is_required) && $field->is_required === 'on' ? ' required' : '').'>'.$field->field_name;
    $markup .= '</label>';
    $markup .= '</div>';

    return $markup;
  }

  /**
   * Compiles the markup for all radio field types
   *
   * @param object, array
   *
   * @return string
   */
  private function compileMarkupRadio($field, $values)
  {
    $markup  = '<div class="form-group>';
    foreach($values as $value) {
      $markup .= '<div class="radio">';
      $markup .= '<label>';
      $markup .= '<input type="radio" name="'.$field->field_name_adj.'" value="'.$value.'"'.(isset($field->is_required) && $field->is_required === 'on' ? ' required' : '').'>'.$value;
      $markup .= '</label>';
      $markup .= '</div>';
    }
    $markup .= '</div>';

    return $markup;
  }

  /**
   * Compiles the markup for all textarea field types
   *
   * @param object
   *
   * @return string
   */
  private function compileMarkupTextarea($field)
  {
    $markup  = '<div class="form-group">';
    $markup .= $this->compileMarkupLabel($field);
    $markup .= '<textarea id="'.$field->field_name_adj.'" name="'.$field->field_name_adj.'" class="form-control"'.(isset($field->is_required) && $field->is_required === 'on' ? ' required' : '').'></textarea>';
    $markup .= '</div>';

    return $markup;
  }

  /**
   * Compiles the markup for all select field types
   *
   * @param object
   *
   * @return string
   */
  private function compileMarkupSelect($field, $values)
  {
    $markup  = '<div class="form-group">';
    $markup .= $this->compileMarkupLabel($field);
    $markup .= '<select id="'.$field->field_name_adj.'" name="'.$field->field_name_adj.'" class="form-control">';
    foreach($values as $value) {
      $markup .= '<option value="'.$value.'">'.$value.'</option>';
    }
    $markup .= '</select>';
    $markup .= '</div>';

    return $markup;
  }

}

new JetstashConnect();
register_activation_hook(__FILE__, array('JetstashConnect', 'activationCheck'));
