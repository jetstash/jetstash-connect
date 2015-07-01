<?php

namespace jetstash;

class JetstashConnectMarkup
{

  /*
  |--------------------------------------------------------------------------
  | Jetstash Connect Markup
  |--------------------------------------------------------------------------
  */

  /**
   * Private class vars
   *
   * @var $settings object
   */
  private $settings;

  /**
   * Class constructor
   */
  function __construct()
  {
    $this->loadSettings();
  }

  /**
   * Loads the settings to trigger optional includes
   *
   * @return void
   */
  private function loadSettings()
  {
    $this->settings = unserialize(get_option('jetstash_connect_settings'));
  }

  /**
   * Compiles our markup to be pushed to the page via the shortcode
   *
   * @param array
   *
   * @return string
   */
  public function compileMarkup($fields)
  {
    if($fields) {
      $markup  = '<form id="jetstash-connect" role="form" method="post">';
      $markup .= '<input type="text" name="first_middle_last_name"'.(!isset($this->settings->disable_stylesheet) || !$this->settings->disable_stylesheet ? 'style="display: none;"' : ' class="hidden"').'>';

      foreach($fields as $field) {
        if($field->type === 'text' || $field->type === 'tel' || $field->type === 'email') {
          $markup .= $this->compileMarkupInput($field);
        } elseif($field->type === 'checkbox') {
          $markup .= $this->compileMarkupCheckbox($field);
        } elseif($field->type === 'textarea') {
          $markup .= $this->compileMarkupTextarea($field);
        } elseif($field->type === 'radio') {
          if(isset($field->values)) {
            $markup .= $this->compileMarkupRadio($field, $field->values);
          } else {
            $markup .= $this->compileMarkupError($field->field_name);
          }
        } elseif($field->type === 'select') {
          if(isset($field->values)) {
            $markup .= $this->compileMarkupSelect($field, $field->values);
          } else {
            $markup .= $this->compileMarkupError($field->field_name);
          }
        }
      }
      if(isset($this->settings->enable_recaptcha) && $this->settings->enable_recaptcha) {
        $markup .= '<div class="g-recaptcha" data-sitekey="'.$this->settings->recaptcha_site_key.'"></div>';
      }
      $markup .= '<p id="jetstash-error"></p>';
      $markup .= '<button type="submit" class="btn btn-default">Submit</button>';
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
   * Compile markup error
   *
   * @param string
   *
   * @return string
   */
  private function compileMarkupError($name)
  {
    $markup = '<div class="form-group"><p>Error: The field "'.$name.'" is required but has no values set.</p></div>';
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
    $markup  = '<div class="form-group">';
    $markup .= '<div class="checkbox">';
    $markup .= '<label for="'.$field->field_name_adj.'">';
    $markup .= '<input type="checkbox" value="true" id="'.$field->field_name_adj.'" name="'.$field->field_name_adj.'"'.(isset($field->is_required) && $field->is_required === 'on' ? ' required' : '').'> '.$field->field_name;
    $markup .= '</label>';
    $markup .= '</div>';
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
    $count = 1;
    $markup  = '<div class="form-group">';
    $markup .= '<p class="radio-label">'.$field->field_name.'</p>';
    foreach($values as $value) {
      $markup .= '<div class="radio">';
      $markup .= '<label for="'.$field->field_name_adj.'_'.$count.'">';
      $markup .= '<input type="radio" id="'.$field->field_name_adj.'_'.$count.'"name="'.$field->field_name_adj.'" value="'.$value.'"'.(isset($field->is_required) && $field->is_required === 'on' ? ' required' : '').'> '.$value;
      $markup .= '</label>';
      $markup .= '</div>';
      $count++;
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
    $markup .= '<select id="'.$field->field_name_adj.'" name="'.$field->field_name_adj.'" class="form-control"'.(isset($field->is_required) && $field->is_required === 'on' ? ' required' : '').'>';
    foreach($values as $value) {
      $markup .= '<option value="'.$value.'">'.$value.'</option>';
    }
    $markup .= '</select>';
    $markup .= '</div>';

    return $markup;
  }

}