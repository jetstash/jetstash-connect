jQuery(document).ready(function($) {

  $('#jetstash-connect').on('submit',function(e) {
    error = checkEmpty();

    var data = $(this).serialize();

    if(error.status !== 'error') {

      $.post(jetstashConnect.ajaxurl, {
        action : 'jetstash_connect',
        nonce  : jetstashConnect.nonce,
        post   : data
      },
      function(response) {
        console.log(response);
        console.log(jetstashConnect);
        // successMessage(response);
      });

    } else {
      errorOutput(error);
    }

    return false;
  });

  function checkEmpty() {
  }

  function errorOutput(error) {

  }


});