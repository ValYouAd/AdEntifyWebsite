define([
   "app"
], function(app) {

   var HomePage = app.module();

   HomePage.Views.Content = Backbone.View.extend({
      template: "homepage/content",

      serialize: function() {
         return {};
      },

      beforeRender: function() {
         this.$("#fb-connect-status").html(app.fb.get('status'));
      }
   });

   return HomePage;
});