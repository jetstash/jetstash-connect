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
  select, input {
    width: 200px;
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
    <tr>
      <th><label for="cache_duration">Cache Duration:</label></th>
      <td>
        <select id="cache_duration" name="cache_duration">
          <option value="30"<?php echo isset($settings->cache_duration) && $settings->cache_duration === '30' ? ' selected' : ''; ?>>30 Minutes</option>
          <option value="60"<?php echo isset($settings->cache_duration) && $settings->cache_duration === '60' ? ' selected' : ''; ?>>1 Hour</option>
          <option value="360"<?php echo isset($settings->cache_duration) && $settings->cache_duration === '360' ? ' selected' : ''; ?>>6 Hours</option>
          <option value="720"<?php echo isset($settings->cache_duration) && $settings->cache_duration === '720' ? ' selected' : ''; ?>>12 Hours</option>
          <option value="1440"<?php echo isset($settings->cache_duration) && $settings->cache_duration === '1440' ? ' selected' : ''; ?>>1 Day</option>
        </select>
  </table>
  <input class="btn button" type="submit" name="Submit" value="Update Settings" />
</form>
