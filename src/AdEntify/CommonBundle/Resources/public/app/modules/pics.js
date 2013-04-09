define([
   "app"
], function(app) {

   var Pics = app.module();

   Pics.Views.Content = Backbone.View.extend({
      template: "pics/content",

      serialize: function() {
         return {};
      }
   });

   return Pics;
});