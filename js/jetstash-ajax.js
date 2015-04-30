(function($) {

  function Jetstash() {
    var self = this;

    self.$form   = $('#jetstash-connect');
    self.$error  = $('#jetstash-error');
    self.options = jetstashConnect;
    self.state   = { error: false, message: "All fields completed successfully.", element: null };

    self.submit();
  }

  Jetstash.prototype.submit = function() {
    var self = this;

    self.$form.on('submit',function(e) {
      var data = $(this).serialize();

      e.preventDefault();
      self.checkRequired();

      if(self.state.error === false) {

        self.$form.find('button.btn').toggle();
        self.$error.append('Your form is being submitted...');

        $.post(jetstashConnect.ajaxurl, {
          action : 'jetstash_connect',
          nonce  : jetstashConnect.nonce,
          form   : jetstashConnect.form_id,
          post   : data,
        },
        function(response) {
          self.successOutput(response);
        });
      } else {
        self.errorOutput();
      }
    });
  };

  Jetstash.prototype.checkRequired = function() {
    var self = this;

    self.clearErrors();

    self.$form.find(':input').each(function() {
      var el, attr, type, value, field_name;

      el    = $(this);
      attr  = el.attr('required');
      type  = el.attr('type');
      value = el.val();

      if(typeof attr !== typeof undefined && attr !== false) {

        field_name = el.siblings('label').text() || el.closest('label').text().trim() || el.attr('name');

        if(value === "" || value === null) {
          self.setStateError(field_name + ' is required.',el);
          return false;
        }

        if('checkbox' === type && !el.is(':checked')) {
          self.setStateError(field_name + ' is required.',el);
          return false;
        }

        if('radio' === type && !el.is(':checked') && !el.closest('div.form-group').find(':input').is(':checked')) {
          self.setStateError(field_name + ' is required.', el)
          return false;
        }
  
        if('email' === type && false === self.validateEmail(value)) {
          self.setStateError('Your email is not valid.', el)
          return false;
        }

      } else {
        self.state.error   = false;
        self.state.message = 'All fields complete.';
        self.state.element = null;
      }
    });
  };

  Jetstash.prototype.validateEmail = function(email) {
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
  };

  Jetstash.prototype.clearErrors = function() {
    var self = this;

    self.$form.find('div.form-group').removeClass('has-error');
    self.$error.empty();
  };

  Jetstash.prototype.setStateError = function(message, el) {
    var self = this;

    self.state.error   = true;
    self.state.message = message;
    self.state.element = el;
  };

  Jetstash.prototype.errorOutput = function() {
    var self = this;

    self.clearErrors();
    console.log(self.state);
    self.state.element.closest('div.form-group').addClass('has-error');
    self.$error.text(self.state.message);
  };

  Jetstash.prototype.successOutput = function(data) {
    var self = this, response = JSON.parse(data);

    self.clearErrors();

    if(response.success === true) {
      $('#jetstash-connect *').fadeOut(200);
      self.$form.prepend('<p class="jetstash-success">' + self.options.message + '</p>');
    } else {
      self.$form.find('button.btn').toggle();
      self.$error.append(response.message);
    }

    if(jetstashConnect.environment === 'local' || jetstashConnect.environment === 'staging') {
      console.log(response);
    }
  };

  new Jetstash();

})(jQuery);
