define([
   "app"
], function(app) {

   var Facebook = app.module();

   Facebook.Model = Backbone.Model.extend({
      defaults: {
         userId : 0,
         accessToken: '',
         status: 'unknown'
      },

      setFacebookResponse: function(response) {
         if ((response.session || response.authResponse) && response.status === 'connected') {
            this.set('userId', response.authResponse.userID);
            this.set('accessToken', response.authResponse.accessToken);
         }
         this.set('status', response.status);
         app.trigger('global:facebook:connected');
      },

      isConnected: function() {
         return this.get('status') === 'connected' ? true: false;
      }
   });

   return Facebook;
});