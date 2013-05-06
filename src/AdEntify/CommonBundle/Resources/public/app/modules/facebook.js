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
         if (response.authResponse && response.status === 'connected') {
            this.set('userId', response.authResponse.userID);
            this.set('accessToken', response.authResponse.accessToken);
         }
         this.set('status', response.status);
      },

      notLoggedIn: function() {
         var that = this;
         $('#loading-authent').hide();
         $('#fb-logout').hide();
         $('#fb-login').show();
         $('#twitter-authent').show();
         $('#fb-login').click(function() {
            that.login();
         });
      },

      login: function() {
         var that = this;
         FB.login(function(response) {
            if (response.authResponse) {
               that.connected(response);
            } else {
               // cancelled
            }
         });
      },

      logout: function() {
         window.location.href = Routing.generate('fos_user_security_logout');
      },

      connected: function(response) {
         setTimeout(function() {
            // Check facebook connect to the server
            $.ajax({ url: Routing.generate('_security_check_facebook') } );
         }, 500);
         var that = this;
         this.setFacebookResponse(response);
         app.trigger('global:facebook:connected');
         $('#loading-authent').hide();
         $('#fb-login').hide();
         $('#twitter-authent').hide();
         $('#fb-logout').show();
         $('#fb-logout').click(function() {
            that.logout();
         });
         $("#user-information").html('<img src="https://graph.facebook.com/' + app.fb.get('userId') + '/picture?width=20&height=20" />');
      },

      isConnected: function() {
         return this.get('status') === 'connected' ? true: false;
      }
   });

   return Facebook;
});