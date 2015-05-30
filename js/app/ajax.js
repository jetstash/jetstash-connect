;(function($) {

  function Jetstash() {
    this.$form   = $('#jetstash-connect');
    this.$error  = $('#jetstash-error');
    this.options = jetstashConnect;
    this.state   = { error: false, message: "All fields completed successfully.", element: null };

    this.submit();
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
    this.$form.find('div.form-group').removeClass('has-error');
    this.$error.empty();
  };

  Jetstash.prototype.setStateError = function(message, el) {
    this.state.error   = true;
    this.state.message = message;
    this.state.element = el;
  };

  Jetstash.prototype.errorOutput = function() {
    this.clearErrors();
    this.state.element.closest('div.form-group').addClass('has-error');
    this.$error.text(this.state.message);
  };

  Jetstash.prototype.successOutput = function(data) {
    var response = JSON.parse(data);

    this.clearErrors();

    if(response.success === true) {
      $('#jetstash-connect *').fadeOut(200);
      this.$form.prepend('<p class="jetstash-success">' + this.options.message + '</p>');
    } else {
      this.$form.find('button.btn').toggle();
      this.$error.append(response.message);
    }

    if(jetstashConnect.environment === 'local' || jetstashConnect.environment === 'staging') {
      console.log(response);
    }
  };

  new Jetstash();

})(jQuery);
