<?php

class JetstashConnectTest extends WP_UnitTestCase {

  /**
   * Declare our test class vars
   *
   * @var class
   */
  private $jetstash, $config, $settings, $apiUrl;

  /**
   * Constructor function
   *
   */
  function __construct()
  {
    $this->jetstash = new JetstashConnect();
    $this->getConfig();
    $this->setSettings();
    $this->setApiUrl();
  }

  /**
   * Set our settings variable
   *
   */
  private function getConfig() {
    $envs = array('local', 'staging');
    foreach($envs as $env) {
      if(file_exists(dirname( __FILE__ ).'/../env_'.$env)) {
        $this->config = file_get_contents(realpath(__DIR__.'/../env_'.$env));
        $this->config = json_decode($this->config);
        break;
      } 
    }
    if(isset($_SERVER['environment']) && 'travis' === $_SERVER['environment']) {
      $this->config->api_url         = isset($_SERVER['api_url']) ? $_SERVER['api_url'] : null;
      $this->config->api_key         = isset($_SERVER['api_key']) ? $_SERVER['api_key'] : null;
      $this->config->form_id         = isset($_SERVER['form_id']) ? $_SERVER['form_id'] : null;
      $this->config->user            = isset($_SERVER['user']) ? $_SERVER['user'] : null;
      $this->config->success_message = isset($_SERVER['success_message']) ? $_SERVER['success_message'] : null;
      $this->config->cache_duration  = isset($_SERVER['cache_duration']) ? $_SERVER['cache_duration'] : null;
    }
  }

  private function setSettings() {
    $this->settings['api_key']            = $this->config->api_key;
    $this->settings['user']               = $this->config->user;
    $this->settings['success_message']    = $this->config->success_message;
    $this->settings['cache_duration']     = $this->config->cache_duration;
    $this->settings['disable_stylesheet'] = true;
    $this->settings['invalidate_cache']   = false;
  }

  private function setApiUrl() {
    $this->apiUrl = $this->config->api_url.'/v1/user/forms?api_key='.$this->config->api_key.'&user='.$this->config->user;
  }

  /**
   * Test the updateSettings
   *
   */
  function testUpdateSettings() 
  {
    $settings = JetstashConnect::updateSettings($this->settings);

    // Assert our object has the expected attributes
    $attributes = array('api_key', 'user', 'success_message', 'cache_duration', 'disable_stylesheet', 'invalidate_cache', 'error', 'error_message');
    foreach($attributes as $attr) {
      $this->assertObjectHasAttribute($attr, $settings);
    }
  }

  /**
   * Test the version compatibility test
   *
   */
  function testCompatibleVersion()
  {
    $versionBool = JetstashConnect::compatibleVersion();
    $this->assertTrue($versionBool);
  }

  /**
   * Test the shortcode (main gateway into the plugin)
   *
   */
  // function testConnectShortcode()
  // {
  //   $this->jetstash->settings = $this->settings;
  //   $this->jetstash->apiUrl   = $this->apiUrl;
  //   $atts['form'] = $this->config->form_id;
  //   $structure = $this->jetstash->connectShortcode($atts);
  // }

}
