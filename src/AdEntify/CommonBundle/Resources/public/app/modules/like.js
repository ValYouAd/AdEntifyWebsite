/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Like = app.module();

   Like.Model = Backbone.Model.extend({

      like: function(photo) {
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_like'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  type: 'POST',
                  data: { photoId: photo.get('id') }
               });
            }
         });
      }

   });

   return Like;
});