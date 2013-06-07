/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Upload = app.module();

   Upload.Views.Content = Backbone.View.extend({
      template: "upload/content",

      serialize: function() {
         return {
            model: { appRoot: app.rootUrl, instagramClientId: instagramClientId }
         }
      },

      events: {
         "click #flickrUploadButton": "flickrUpload"
      },

      flickrUpload: function() {
         window.location.href = Routing.generate('flickr_request_token');
      },

      initialize: function() {
         app.trigger('domchange:title', $.t('upload.pageTitle'));
      }
   });

   return Upload;
});