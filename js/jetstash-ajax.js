jQuery(document).ready(function($) {
  var $form  = $('#jetstash-connect'),
      $error = $('#jetstash-error');

  $form.on('submit',function(e) {
    var error, data;

    error = checkFields();
    data  = $(this).serialize();

    if(error.status !== 'error') {

      $('form#jetstash-connect *').fadeOut(200);
      $('form#register').prepend('<p class="message">Your form is being processed...</p>');

      $.post(jetstashConnect.ajaxurl, {
        action : 'jetstash_connect',
        nonce  : jetstashConnect.nonce,
        form   : jetstashConnect.form_id,
        post   : data,
      },
      function(response) {
        successMessage(response);
      });

    } else {
      errorOutput(error);
    }

    return false;
  });

  function checkFields() {
    var error = {};

    error.status  = 'success';
    error.message = 'All fields complete';

    clearErrors();

    $('#jetstash-connect *').filter(':input').each(function() {
      var attr, type, value, field_name;

      attr  = $(this).attr('required');
      type  = $(this).attr('type');
      value = $(this).val();

      if(typeof attr !== typeof undefined && attr !== false) {
        if(value === "") {
          field_name = $(this).siblings('label').text();

          error.status  = 'error';
          error.message = field_name + ' is required.';
          error.element = $(this);

          return false;
        }
        if('email' === type && false === looseEmailValidate(value)) {
          error.status  = 'error';
          error.message = 'Your email is not valid.';
          error.element = $(this);

          return false;
        }
      }
    });

    return error;
  }

  function looseEmailValidate(email) {
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
  }

  function clearErrors() {
    $form.find('div.form-group').removeClass('has-error');
    $error.empty();
  }

  function errorOutput(error) {
    clearErrors();

    error.element.closest('div.form-group').addClass('has-error');
    $error.text(error.message);
  }

  function successMessage(data) {
    var response = JSON.parse(data);
    if(jetstashConnect.environment === 'local' || jetstashConnect.environment === 'staging') {
      console.log(response);
    }
    if(response.success) {
      $form.html('<p class="message">' + jetstashConnect.message + '</p>');
    } else {
      $form.html('<p class="message">' + response.message + '</p>');
    }
  }

});