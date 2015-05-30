;(function($) {

  $('form#jetstash').on('change', 'input#invalidate_cache', function() {
    $('tr#invalidate').toggleClass('hidden');
  });

})(jQuery);