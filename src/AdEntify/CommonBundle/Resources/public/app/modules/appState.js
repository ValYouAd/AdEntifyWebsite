define([
   "app"
], function(app) {

   var AppState = app.module();

   AppState.Model = Backbone.Model.extend({
      defaults: {
         currentPhotoModel: null
      },

      getCurrentPhotoModel: function() {
         if (typeof(this.get('currentPhotoModel')) != 'undefined') {
            return this.get('currentPhotoModel');
         } else
            return false;
      }
   });

   return AppState;
});