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

   var TagStats = app.module();

   TagStats.Model = Backbone.Model.extend({
      hoveredTags: [],
      clickedTags: [],

      hover: function(tag) {
         hoveredTag = _.find(this.hoveredTags, function(t) {
            return t.get('id') == tag.get('id') ? true : false;
         });
         if (!hoveredTag) {
            this.hoveredTags.push(tag);
            this.postStats(tag, 'hover');
         }
      },

      click: function(tag, e) {
         clickedTags = _.find(this.clickedTags, function(t) {
            return t.get('id') == tag.get('id') ? true : false;
         });
         if (!clickedTags) {
            this.postStats(tag, 'click', $(e.currentTarget).attr('href'));
            this.clickedTags.push(tag);
         }
      },

      postStats: function(tag, type, link) {
         link = typeof link === 'undefined' ? null : link;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_tagstats'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  type: 'POST',
                  data: { tagId: tag.get('id'), statType: type, link: link }
               });
            }
         });
      }
   });

   return TagStats;
});