jQuery(document).ready(function($) {
  console.log('the ajax-modal-portal plugin js had been loaded');

  var AjaxModalPortal = (function() {
    
    var retObj = {};

    retObj.init = function(options) {
      module = this;
      retObj.options = $.extend({}, retObj.options, options);
      retObj.moveModal();
      retObj.bindEvents();
    };

    retObj.bindEvents = function() {

      //show modal event
      $(document).on('click', '.amp-link-modal', function(e) {
        e.preventDefault();
        var modalLink = 'amp-modal-'+$(this).attr('id');
        $('#'+modalLink).fadeIn('fast');
        $('.amp-bg-overlay').fadeIn('fast');
      });

      //overlay bg click exit event
      $(document).on('click', '.amp-bg-overlay', function() {
        $(this).fadeOut('fast');
        $('.amp-modal').fadeOut('fast');
      });

      //form submit event
      $('form.amp-form').submit(function(e) {
        var form = $(this);
        //create status elements before response
        var statusElem = form.find('.amp-status');
        if (statusElem.length === 0) {
          statusElem = $('<span class="amp-status"></span>');
          form.prepend(statusElem);
        }
        var ajaxFlag = form.find('.amp-ajax');
        if (ajaxFlag.length === 0) {
          ajaxFlag = $('<input class="amp-ajax" name="amp" type="hidden" value="1" />');
          form.prepend(ajaxFlag);
        }
        $('<div class="amp-loading"></div>').prependTo(form);
        
        //ajax post
        var formData = form.serializeArray();
        var jsonData = {};

        $.each(formData, function() {
          if (jsonData[this.name]) {
            if (!jsonData[this.name].push) {
              jsonData[this.name] = [jsonData[this.name]];
            }
            jsonData[this.name].push(this.value || '');
          } 
          else {
            jsonData[this.name] = this.value || '';
          }
        });

        console.log('jsonData:');
        console.log(jsonData);
        console.log('ajax_login_object.ajaxurl');
        console.log(ajax_login_object.ajaxurl);
        $.ajax({
          type : 'POST',
          url: ajax_login_object.ajaxurl,
          data : jsonData,
          success: function(data) {
            //ampAjax( data, statusElement );
            //$(document).trigger('amp_' + data.action, [data, form]);
            console.log('success: here is the data output');
            console.log(data);
          },
          error: function(data) {
            console.log('error: here is the data output');
            console.log(data); 
          },
          dataType: 'json'
        });
        e.preventDefault();
        
      });

      //catch form actions from aja amp_
      $(document).on('amp_login', function(event, data, form) {
        if(data.result === true) {
          console.log(data);
        }
        else {
          console.log('no data returned');
        }
      });

    };

    retObj.moveModal = function() {
      $('.amp-modal').each(function() {
        $(this).appendTo('body');
      });
      var bgOverlay = '<div class="amp-bg-overlay" style="display: none;"></div>';
      $(bgOverlay).appendTo('body');
    };

    return retObj;

  })();

  //utility function
  $.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
      if (o[this.name] !== undefined) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };

  AjaxModalPortal.init();

})(jQuery);

