<?php
/**
 * Plugin Name: Jetstash Connect
 * Plugin URI: https://www.jetstash.com/connect
 * Description: Dynamically pulls forms from Jetstash and integrates them via a shortcode into the theme.
 * Version: 0.1.0
 * Author: Jetstash
 * Author URI: https://www.jetstash.com
 */

class jetstashConnect
{

  /**
   * Define the private class vars
   *
   * @var $version string
   */

  /**
   * Construct function
   *
   * @return void
   */
  function __construct()
  {
    $this->version = '0.1.0';
    $this->setVersion();
  }

  protected function setVersion()
  {
    $version = get_option('jetstash_connect_version');
    if($version !== $this->version) {
      update_option('jetstash_connect_version', $this->version);
    }

  }




}
new jetstashConnect();
