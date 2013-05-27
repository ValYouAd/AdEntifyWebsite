define([
   "app"
], function(app) {

   var AppState = app.module();

   AppState.Model = Backbone.Model.extend({
      defaults: {
         currentPhoto: null
      },

      getCurrentPhoto: function() {
         if (typeof(this.get('currentPhoto')) != 'undefined') {
            return this.get('currentPhoto');
         } else
            return false;
      }
   });

   return AppState;
});