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
         this.checkActionButtons();
      },

      afertRender: function() {
         this.checkActionButtons();
      },

      checkActionButtons: function() {
         if ($('.check-image .checked').length > 0) {
            $('.action-buttons').fadeIn('fast');
         } else {
            $('.action-buttons').fadeOut('fast');
         }
      }
   });

   ExternalServicePhotos.Views.ErrorNoRights = Backbone.View.extend({
      template: "externalServicePhotos/errors/noRights"
   });

   return ExternalServicePhotos;
});