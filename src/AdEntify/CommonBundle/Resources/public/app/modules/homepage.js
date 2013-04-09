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
         alert(app.fb.get('status'));
         this.$("#fb-connect-status").html(app.fb.get('status'));
      }
   });

   return HomePage;
});