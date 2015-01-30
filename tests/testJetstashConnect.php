<?php

class JetstashConnectTest extends WP_UnitTestCase {

  /**
   * Declare our test class vars
   *
   * @var class
   */
  private $jetstash;

  /**
   * Constructor function
   *
   */
  function __construct()
  {
    $this->jetstash = new JetstashConnect();
  }

  /**
   * Test the updateSettings
   *
   */
  function testUpdateSettings() 
  {
    $data['api_key']            = '12PE82F3a33UiGt6wzmGLCmF';
    $data['user']               = 'x7kD9PQw';
    $data['success_message']    = 'I am success message!';
    $data['cache_duration']     = '30';
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

