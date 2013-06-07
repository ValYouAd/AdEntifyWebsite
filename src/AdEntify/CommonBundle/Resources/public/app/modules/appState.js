define([
   "app"
], function(app) {

   var AppState = app.module();

   AppState.Model = Backbone.Model.extend({
      defaults: {
         locale: currentLocale
      },

      getLocale: function() {
         return this.get('locale');
      },

      setLocale: function(locale) {
         this.set('locale', locale);
      },

      getCurrentPhotoModel: function() {
         if (this.has('currentPhotoModel') && typeof this.get('currentPhotoModel') !== 'undefined'
            && this.get('currentPhotoModel')) {
            return this.get('currentPhotoModel');
         } else
            return false;
      },

      getCurrentPhotoId: function() {
         if (this.has('currentPhotoId') && typeof this.get('currentPhotoId') !== 'undefined'
            && this.get('currentPhotoId')) {
            return this.get('currentPhotoId');
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