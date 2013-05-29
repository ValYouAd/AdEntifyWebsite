define([
   "app"
], function(app) {

   var AppState = app.module();

   AppState.Model = Backbone.Model.extend({
      getCurrentPhotoModel: function() {
         if (this.has('currentPhotoModel') && typeof this.get('currentPhotoModel') !== 'undefined'
            && this.get('currentPhotoModel')) {
            return this.get('currentPhotoModel');
         } else
            return false;
      },

      getCurrentPosition: function() {
         if (this.has('currentPosition') && typeof this.get('currentPosition') !== 'undefined'
            && this.get('currentPosition')) {
            return this.get('currentPosition');
         } else
            return false;
      }
   });

   return AppState;
});