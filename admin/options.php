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

<section id="jetstash-settings">
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
      <tr>
        <th><label for="enable_recaptcha">Enable Google Recaptcha:</label></th>
        <td><input type="checkbox" id="enable_recaptcha" name="enable_recaptcha" value="1"<?php echo isset($settings->enable_recaptcha) && $settings->enable_recaptcha ? ' checked' : ''; ?>></td>
      </tr>
      <tr id="site-key" class="<?php echo !$settings->enable_recaptcha ? 'hidden' : ''; ?>">
        <th><label for="recaptcha_site_key">Recaptcha Site Key:</label></th>
        <td><input type="text" id="recaptcha_site_key" name="recaptcha_site_key" value="<?php echo isset($settings->recaptcha_site_key) ? $settings->recaptcha_site_key : ''; ?>"></td>
      </tr>
      <tr id="secret-key" class="<?php echo !$settings->enable_recaptcha ? 'hidden' : ''; ?>">
        <th><label for="recaptcha_secret_key">Recaptcha Secret Key:</label></th>
        <td><input type="text" id="recaptcha_secret_key" name="recaptcha_secret_key" value="<?php echo isset($settings->recaptcha_secret_key) ? $settings->recaptcha_secret_key : ''; ?>"></td>
      </tr>
    </table>
    <button type="submit" class="btn button">Update</button>
  </form>
</section>
