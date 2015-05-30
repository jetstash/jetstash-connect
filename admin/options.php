<?php

$durations = array(
  "30"   => "30 Minutes",
  "60"   => "1 Hour",
  "360"  => "6 Hours",
  "720"  => "12 Hours",
  "1440" => "1 Day"
);

if(isset($_POST['jetstash_connect']) && $_POST['jetstash_connect'] == 'true') {
  if(1 === check_admin_referer('jetstash-connect')) {

    $settings = JetstashConnect::updateSettings($_POST);
    if(!$settings->error) {
      echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
    } else {
      echo '<div id="message" class="error"><p>'.$settings->error_message.'</p></div>';
    }
  } else {
    die('Permission denied.');
  }
} else {
  $settings = unserialize(get_option('jetstash_connect_settings'));
} ?>

<style>
  img.logo {
    max-width: 150px;
    height: auto;
  }
  input.btn {
    width: auto;
    margin-top: 15px;
  }
  select, input, textarea {
    width: 350px;
  }
  textarea {
    resize: none;
  }
  .hidden {
    display: none;
  }
</style>

<h2><img class="logo" src="<?php echo plugins_url(null, __DIR__); ?>/img/jetstash-logo.png" alt="Jetstash Connect"></h2>

<form id="jetstash" name="jetstash_connect_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
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
      <th><label for="success_message">Success Message:</label></th>
      <td><textarea id="success_message" name="success_message" rows="8"><?php echo isset($settings->success_message) ? $settings->success_message : ''; ?></textarea></td>
    </tr>
    <tr>
      <th><label for="cache_duration">Cache Duration:</label></th>
      <td>
        <select id="cache_duration" name="cache_duration"><?php
          foreach($durations as $key=>$value) {
            echo '<option value="'.$key.'"'.(isset($settings->cache_duration) && $settings->cache_duration === $key ? ' selected' : '').'>'.$value.'</option>';
          } ?>
        </select>
      </td>
    </tr>
    <tr>
      <th><label for="disable_stylesheet">Disable Stylesheet:</label></th>
      <td><input type="checkbox" id="disable_stylesheet" name="disable_stylesheet"<?php echo isset($settings->disable_stylesheet) && $settings->disable_stylesheet ? ' checked' : ''; ?>></td>
    </tr>
    <tr>
      <th><label for="invalidate_cache">Invalidate Cache:</label></th>
      <td><input type="checkbox" id="invalidate_cache" name="invalidate_cache"></td>
    </tr>
    <tr id="invalidate" class="hidden">
      <th><label for="invalidate_form_id">Invalidate Form ID:</label></th>
      <td><input type="text" id="invalidate_form_id" name="invalidate_form_id"></td>
    </tr>

  </table>
  <input class="btn button" type="submit" name="Submit" value="Update Settings" />
</form>
