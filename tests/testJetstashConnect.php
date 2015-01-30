<?php

class JetstashConnectTest extends WP_UnitTestCase {

  /**
   * Declare our test class vars
   *
   * @var class
   */
  private $jetstash, $config;

  /**
   * Constructor function
   *
   */
  function __construct()
  {
    $this->jetstash = new JetstashConnect();
    $this->setSettings();
  }

  /**
   * Set our settings variable
   *
   */
  private function setSettings() {
    $envs = ['local', 'staging'];
    foreach($envs as $env) {
      if(file_exists(dirname( __FILE__ ).'/../env_'.$env)) {
        $this->config = file_get_contents(realpath(__DIR__.'/../env_'.$env));
        $this->config = json_decode($this->config);
        break;
      } 
    }
  }

  /**
   * Test the updateSettings
   *
   */
  function testUpdateSettings() 
  {
    $data['api_key']            = $this->config->api_key;
    $data['user']               = $this->config->user;
    $data['success_message']    = $this->config->success_message;
    $data['cache_duration']     = $this->config->cache_duration;
    $data['disable_stylesheet'] = true;
    $data['invalidate_cache']   = false;

    $settings = JetstashConnect::updateSettings($data);

    // Assert our object has the expected attributes
    $attributes = ['api_key', 'user', 'success_message', 'cache_duration', 'disable_stylesheet', 'invalidate_cache', 'error', 'error_message'];
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
  //   $atts['form'] = 'LSWHpdMNi4E0';
  //   $structure = $this->jetstash->connectShortcode($atts);

  //   var_dump($structure);
  // }

}

