<h2>Jetstash Connect</h2><?php

if(isset($_POST['jetstash_connect']) && $_POST['jetstash_connect'] == 'true') {
  if(1 === check_admin_referer('jetstash-connect')) {

    $settings = JetstashConnect::updateSettings($_POST);

    if(!$settings->error) {
      '<div class="updated"><p><strong>Options saved.</strong></p></div>';
    } else {
      '<div id="message" class="error"><p>'.$settings->error_message.'</p></div>';
    }
  } else {
    die('Permission denied.');
  }
} else {
  $settings = unserialize(get_option('jetstash_connect_settings'));
} ?>

<style>
  input.btn {
    width: auto;
    margin-top: 15px;
  }
</style>

<form name="jetstash_connect_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
  <input type="hidden" name="jetstash_connect" value="true">
  <?php wp_nonce_field('jetstash-connect'); ?>
  <table class="form-table">
    <tr>
      <th><label for="api_key">API Key:</label></th>
      <td><input id="api_key" type="text" name="api_key" value="<?php echo isset($settings->api_key) ? $settings->api_key : ''; ?>"></td>
    </tr>
    <tr>
      <th><label for="user">User ID:</label></th>
      <td><input id="user" type="text" name="user" value="<?php echo isset($settings->user) ? $settings->user : ''; ?>"></td>
    </tr>
  </table>
  <input class="btn button" type="submit" name="Submit" value="Update Settings" />
</form>
