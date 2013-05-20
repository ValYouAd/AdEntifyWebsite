/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "bootstrap"
], function(app) {

   var ExternalServicePhotos = app.module();
   var error = '';

   ExternalServicePhotos.Views.Item = Backbone.View.extend({
      template: "externalServicePhotos/item",

      tagName: "li span2",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      events: {
         "click .check-image" : "toggleCheckedImage"
      },

      toggleCheckedImage: function(e) {
         var container = $(e.currentTarget).find('.check-image-container');
         if (container.length > 0) {
            container.toggleClass('checked');
         }
         app.trigger('externalServicePhoto:imageChecked', $('.check-image .checked').length);
      },

      afertRender: function() {
         this.checkActionButtons();
      }
   });

   ExternalServicePhotos.Views.ErrorNoRights = Backbone.View.extend({
      template: "externalServicePhotos/errors/noRights"
   });

   ExternalServicePhotos.Views.MenuRight = Backbone.View.extend({
      template: "externalServicePhotos/menuRight",

      imageChecked: function(count) {
         if (count > 0) {
            $('.no-photo-selected').fadeOut('fast');
            $('.photos-selected').fadeIn('fast');
            $('.photo-count').html(count);
         } else {
            $('.photos-selected').fadeOut('fast');
            $('.no-photo-selected').fadeIn('fast');
         }
      },

      initialize: function() {
         var that = this;
         app.on('externalServicePhoto:imageChecked', function(count) {
            that.imageChecked(count);
         });
      },

      events: {
         "click .photos-rights": "photoRightsClick",
         "click .submit-photos": "submitPhotos"
      },

      photoRightsClick: function() {
         if ($('.photos-rights:checked').length != 1) {
            $('.submit-photos').hide();
            app.useLayout().setView("#errors", new ExternalServicePhotos.Views.ErrorNoRights()).render();
            $('.alert').alert();
         } else {
            $('.alert').alert('close');
            $('.submit-photos').fadeIn('fast');
         }
      },

      submitPhotos: function(e) {
         app.trigger('externalServicePhoto:submitPhotos', e);
      }
   });

   return ExternalServicePhotos;
});