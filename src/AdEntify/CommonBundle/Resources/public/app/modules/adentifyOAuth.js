define([
   "app"
], function(app) {

   var AdEntifyOAuth = app.module();

   AdEntifyOAuth.Model = Backbone.Model.extend({
      defaults: {
         accessToken: '',
         expiresAt: ''
      },

      loadAccessToken: function(callback) {
         var that = this;
         var currentTimestamp = Math.round(new Date().getTime() / 1000);
         if (this.get('accessToken') != ''
               && this.get('expiresAt') != ''
               && this.get('expiresAt') > currentTimestamp) {
            callback();
         } else {
            $.ajax({
               type: 'POST',
               url: Routing.generate('get_user_access_token'),
               success: function(data) {
                  that.set('accessToken', data.access_token);
                  that.set('expiresAt', data.expires_at);
                  callback();
               },
               error: function() {
                  console.log('cant retrieve access token');
                  callback();
               }
            });
         }
      },

      getAuthorizationHeader: function() {
         return "Bearer " + this.get('accessToken');
      }
   });

   return AdEntifyOAuth;
});