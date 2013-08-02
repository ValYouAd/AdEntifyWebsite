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

      loadAccessToken: function(options) {
         options || (options = {});
         var that = this;
         var currentTimestamp = Math.round(new Date().getTime() / 1000);
         if (this.get('accessToken') != ''
               && this.get('expiresAt') != ''
               && this.get('userId') != ''
               && this.get('expiresAt') > currentTimestamp) {
            if (options.success)
               options.success();
         } else {
            $.ajax({
               url: Routing.generate('get_user_access_token'),
               type: 'POST',
               success: function(data) {
                  if (typeof data !== 'undefined' && typeof data.access_token !== 'undefined'
                     && typeof data.user_id !== 'undefined' && typeof data.expires_at !== 'undefined') {
                     that.set('accessToken', data.access_token);
                     that.set('expiresAt', data.expires_at);
                     that.set('userId', data.user_id);
                     if (options.success)
                        options.success();
                  } else {
                     if (options.error)
                        options.error();
                  }
               },
               error: function() {
                  if (options.error)
                     options.error();
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