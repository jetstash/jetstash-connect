<?php

class JetstashConnectTest extends WP_UnitTestCase {

  /**
   * Declare our test class vars
   *
   * @var class
   */
  private $jetstash, $settings, $api_url;

  /**
   * Constructor function
   *
   */
  function __construct()
  {
    $this->setSettings();
    $this->jetstash = new JetstashConnect();
    $this->jetstash->test = true;
    $this->jetstash->settings = $this->settings;

    var_dump($this->settings);
    die();

    $this->setApiUrl();
  }

  /**
   * Set our settings variable
   *
   */
  private function setSettings() {
    $this->settings = new StdClass();
    if(isset($_SERVER['environment']) && 'travis' === $_SERVER['environment']) {
      $this->settings->api_url         = isset($_SERVER['api_url']) ? $_SERVER['api_url'] : null;
      $this->settings->api_key         = isset($_SERVER['api_key']) ? $_SERVER['api_key'] : null;
      $this->settings->form_id         = isset($_SERVER['form_id']) ? $_SERVER['form_id'] : null;
      $this->settings->user            = isset($_SERVER['user']) ? $_SERVER['user'] : null;
      $this->settings->success_message = isset($_SERVER['success_message']) ? $_SERVER['success_message'] : null;
      $this->settings->cache_duration  = isset($_SERVER['cache_duration']) ? $_SERVER['cache_duration'] : null;
    } else {
      $envs = array('local', 'staging');
      foreach($envs as $env) {
        if(file_exists(dirname( __FILE__ ).'/../env_'.$env)) {
          $config = file_get_contents(realpath(__DIR__.'/../env_'.$env));
          $config = json_decode($config);
          break;
        }
      }
      if($config) {
        $this->settings->api_url         = isset($config->api_url) ? $config->api_url : null;
        $this->settings->api_key         = isset($config->api_key) ? $config->api_key : null;
        $this->settings->form_id         = isset($config->form_id) ? $config->form_id : null;
        $this->settings->user            = isset($config->user) ? $config->user : null;
        $this->settings->success_message = isset($config->success_message) ? $config->success_message : null;
        $this->settings->cache_duration  = isset($config->cache_duration) ? $config->cache_duration : null;
      } else {
        die('Tests cannot run without config environments');
      }
    }
  }

  private function setApiUrl() {
    $this->api_url = $this->settings->api_url.'/v1/user/forms?api_key='.$this->settings->api_key.'&user='.$this->settings->user;
  }

  /**
   * Test the updateSettings
   *
   */
  function testUpdateSettings() 
  {
    $settings = JetstashConnect::updateSettings((array) $this->settings);

    // Assert our object has the expected attributes
    $attributes = array('api_key', 'user', 'success_message', 'cache_duration', 'disable_stylesheet', 'invalidate_cache', 'error', 'error_message');
    $this->assertInternalType('object', $settings);
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
   *
   *
   */
  function testBuildStructure()
  {
    $form = array('form' => $this->settings->form_id);
    $response = $this->jetstash->buildStructure($form);

    $this->assertInternalType('string', $response);
  }

  /**
   * Test the form submission
   *
   */
  function testSubmitForm()
  {
    $data = array(
      'nonce' => 'fake_nonce',
      'post'  => 'first_middle_last_name=&first_name=first&last_name=last&email=test@example.com',
      'form'  => $this->settings->form_id,
    );
    $response   = json_decode($this->jetstash->submitForm($data));
    $attributes = array('success', 'message', 'data');
    $this->assertInternalType('object', $response);
    foreach($attributes as $attr) {
      $this->assertObjectHasAttribute($attr, $response);
    }
    $this->assertTrue($response->success);
  }

}

