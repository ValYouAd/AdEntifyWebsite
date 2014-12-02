/**
 * Created by pierrickmartos on 01/12/14.
 */
define([
   "app"
], function(app) {

   var Analytic = app.module();

   Analytic.Model = Backbone.Model.extend({
      hoveredTags: [],
      clickedTags: [],
      viewedPhotos: [],
      hoveredPhotos: [],

      view: function(photo) {
         if (!this.findExisting(photo, this.viewedPhotos)) {
            this.postAnalytic('view', 'photo', null, photo.get('id'), null);
         }
      },

      hover: function(tag, photo) {
         var elementName, element, haystack;
         if (photo) {
            elementName = 'photo';
            element = photo;
            haystack = this.hoveredPhotos;
         } else if (tag) {
            elementName = 'tag';
            element = tag;
            haystack = this.hoveredTags;
         }

         if (element && elementName && haystack && !this.findExisting(element, haystack)) {
            this.postAnalytic('hover', elementName, null, element.get('id'), null);
         }
      },

      click: function(tag, e) {
         if (!this.findExisting(tag, this.clickedTags)) {
            this.postAnalytic('click', 'tag', null, tag.get('id'), $(e.currentTarget).attr('href'));
         }
      },

      findExisting: function(search, haystack) {
         var found = _.find(haystack, function(i) {
            return i.get('id') == search.get('id') ? true : false;
         });
         if (!found)
            haystack.push(search);
         return found;
      },

      postAnalytic: function(action, element, tag, photo, link) {
         var analytic = {
            'platform': 'web',
            'element': element,
            'action': action
         };
         if (tag)
            analytic.tag = tag;
         if (photo)
            analytic.photo = photo;
         if (link)
            analytic.link = link;

         app.oauth.loadAccessToken({
            success: function () {
               $.ajax({
                  type: 'POST',
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  url: Routing.generate('api_v1_post_analytics'),
                  data: {
                     'action': 'ad_analytics',
                     'analytic': analytic
                  }
               });
            }
         });
      }
   });

   return Analytic;
});