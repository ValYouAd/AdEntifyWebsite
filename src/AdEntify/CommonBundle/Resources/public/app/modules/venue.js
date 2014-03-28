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
         return { venue: this.attributes };
      },

      entityToModel: function(venueEntity) {
         this.set('foursquareId', venueEntity.foursquare_id);
         this.set('foursquareShortLink', venueEntity.foursquare_short_link);
         this.set('name', venueEntity.name);
         this.set('description', venueEntity.description);
         this.set('link', venueEntity.link);
         this.set('lat', venueEntity.lat);
         this.set('lng', venueEntity.lng);
         this.set('address', venueEntity.address);
         this.set('postalCode', venueEntity.postal_code);
         this.set('city', venueEntity.city);
         this.set('state', venueEntity.state);
         this.set('country', venueEntity.country);
         this.set('cc', venueEntity.cc);
         if (typeof venueEntity.id !== 'undefined')
            this.set('id', venueEntity.id);
      },

      googleMapsQueryString: function() {
         var queryString = '?q=';
         if (this.has('lat') && !this.get('lat') && this.has('lng') && !this.get('lng')) {
            queryString += this.get('lat') + ',' + this.get('lng');
         } else {
            var fullAddress = [ this.get('name') ];
            if (this.has('address'))
               fullAddress.push(this.get('address'));
            if (this.has('postalCode'))
               fullAddress.push(this.get('postalCode'));
            if (this.has('country'))
               fullAddress.push(this.get('country'));
            if (this.has('city'))
               fullAddress.push(this.get('city'));
            queryString += encodeURIComponent(fullAddress.join(' '));
         }

         return queryString + '&zoom=12';
      }
   });

   Venue.Collection = Backbone.Collection.extend({
      model: Venue.Model,
      cache: true
   });

   Venue.Views.Address = Backbone.View.extend({
      template: 'venue/address',

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      },

      afterRender: function() {
         $(this.el).i18n();
      }
   });

   return Venue;
});