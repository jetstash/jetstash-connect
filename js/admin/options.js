;(function($) {

  var $form = $('form#jetstash');

  $form.on('change', 'input#invalidate_cache', function() {
    $('tr#invalidate').toggleClass('hidden');
  });

  $form.on('change', 'input#enable_recaptcha', function() {
    $('tr#site-key').toggleClass('hidden');
    $('tr#secret-key').toggleClass('hidden');
  });

})(jQuery);