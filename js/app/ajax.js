;(function($) {

  /**
   * Declares our Jetstash function
   *
   */
  function Jetstash() {
    this.$form   = $('#jetstash-connect');
    this.$error  = $('#jetstash-error');
    this.options = jetstashConnect;
    this.state   = { error: false, message: "All fields completed successfully.", element: null };

    this.loadListeners();
  }

  /**
   * Loads the form listener
   *
   */
  Jetstash.prototype.loadListeners = function() {
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
          response = JSON.parse(response);
          self.successOutput(response);
          self.loadCustomEvent("jetstash", response);
        });
      } else {
        self.errorOutput();
      }
    });
  };

  /**
   * Checks all required fields
   *
   */
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
          self.setStateError(field_name + ' is required.', el);
          return false;
        }

        if('checkbox' === type && !el.is(':checked')) {
          self.setStateError(field_name + ' is required.', el);
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

  /**
   * LOOSE email validation, there is PHP validation in the class
   * and validation in the actual application
   *
   * @param string
   */
  Jetstash.prototype.validateEmail = function(email) {
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
  };

  /**
   * Clears errors from the DOM
   *
   */
  Jetstash.prototype.clearErrors = function() {
    this.$form.find('div.form-group').removeClass('has-error');
    this.$error.empty();
  };

  /**
   * Sets our state object
   *
   * @param string, node object
   */
  Jetstash.prototype.setStateError = function(message, el) {
    this.state.error   = true;
    this.state.message = message;
    this.state.element = el;
  };

  /**
   * Displays the error output and updates the dom
   *
   */
  Jetstash.prototype.errorOutput = function() {
    this.clearErrors();

    if(this.state.element !== null) {
      this.state.element.closest('div.form-group').addClass('has-error');
      this.$error.text(this.state.message);
    }
  };

  /**
   * On submission success displays state
   *
   * @param string
   */
  Jetstash.prototype.successOutput = function(response) {
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

  /**
   * Loads custom events
   *
   * @param string, object
   */
  Jetstash.prototype.loadCustomEvent = function(name, param) {
    $.event.trigger({ type: name, 'state': param });
  };

  // Load the Jetstash class if exists
  if($('form#jetstash-connect').length > 0) {
    new Jetstash();
  }

})(jQuery);
