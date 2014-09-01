define([
   "app"
], function(app) {

   var AppState = app.module();

   AppState.Model = Backbone.Model.extend({
      defaults: {
         locale: currentLocale,
         user: null
      },

      setCurrentUser: function(user) {
         this.trigger('current:user:updated');
         this.set('user', user);
      },

      getCurrentUser: function() {
         return this.get('user');
      },

      getCurrentUserId: function() {
         return currentUserId;
      },

      isLogged: function() {
         return currentUserId > 0;
      },

      doIfLogged: function(func, content) {
         if (this.isLogged) {
            func();
         } else {
            require('modules/common').Tools.notLoggedModal(false, content);
         }
      },

      getLocale: function() {
         return this.get('locale');
      },

      setLocale: function(locale) {
         this.set('locale', locale);
      },

      getCurrentPhotoModel: function() {
         if (this.has('currentPhotoModel') && typeof this.get('currentPhotoModel') !== 'undefined' && this.get('currentPhotoModel')) {
            return this.get('currentPhotoModel');
         } else
            return false;
      },

      getCurrentPhotoId: function() {
         if (this.has('currentPhotoId') && typeof this.get('currentPhotoId') !== 'undefined' && this.get('currentPhotoId')) {
            return this.get('currentPhotoId');
         } else
            return false;
      },

      getCurrentPosition: function(type) {
         if (this.has('currentPosition') && typeof this.get('currentPosition') !== 'undefined' && this.get('currentPosition')) {
            switch (type) {
               case 'lat':
                  return this.fixedDown(this.get('currentPosition').coords.latitude, 2);
               case 'lng':
                  return this.fixedDown(this.get('currentPosition').coords.longitude, 2);
               default:
                  return this.get('currentPosition');
            }
         } else
            return false;
      },

      fixedDown: function(number, digits) {
         var n = number - Math.pow(10, -digits)/2;
         n += n / Math.pow(2, 53); // added 1360765523: 17.56.toFixedDown(2) === "17.56"
         return n.toFixed(digits);
      }
   });

   return AppState;
});