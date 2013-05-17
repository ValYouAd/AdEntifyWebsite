define([
   "app"
], function(app) {

   var AdEntifyOAuth = app.module();

   AdEntifyOAuth.Model = Backbone.Model.extend({
      defaults: {
         accessToken: '',
         expiresAt: '',
         userId: ''
      },

      loadAccessToken: function(callback) {
         var that = this;
         var currentTimestamp = Math.round(new Date().getTime() / 1000);
         if (this.get('accessToken') != ''
               && this.get('expiresAt') != ''
               && this.get('userId') != ''
               && this.get('expiresAt') > currentTimestamp) {
            if (typeof callback !== "undefined")
               callback();
         } else {
            $.ajax({
               type: 'POST',
               url: Routing.generate('get_user_access_token'),
               success: function(data) {
                  that.set('accessToken', data.access_token);
                  that.set('expiresAt', data.expires_at);
                  that.set('userId', data.user_id);
                  if (typeof callback !== "undefined")
                     callback();
               },
               error: function() {
                  console.log('cant retrieve access token');
                  if (typeof callback !== "undefined")
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