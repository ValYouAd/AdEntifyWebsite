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

   var Venue = app.module();

   Venue.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_venue');
      },

      toJSON: function() {
         var jsonAttributes = this.attributes;
         delete jsonAttributes.class;
         return { venue: jsonAttributes }
      },

      entityToModel: function(venueEntity) {
         this.set('foursquareId', venueEntity.foursquare_id);
         this.set('foursquareShortLink', venueEntity.foursquare_short_link);
         this.set('name', venueEntity.name);
         this.set('description', venueEntity.description);
         this.set('link', venueEntity.link);
         this.set('lat', venueEntity.lat);
         this.set('lng', venueEntity.lng);
      }
   });

   Venue.Collection = Backbone.Collection.extend({
      model: Venue.Model,
      cache: true
   });

   return Venue;
});