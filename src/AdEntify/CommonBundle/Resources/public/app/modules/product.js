/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 09/08/2013
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Product = app.module();

   Product.Model = Backbone.Model.extend({
      initialize: function() {
         if (this.has('medium_url'))
            this.set('fullMediumUrl', app.rootUrl + 'uploads/photos/products/medium/' + this.get('medium_url'));
         if (this.has('small_url'))
            this.set('fullSmallUrl', app.rootUrl + 'uploads/photos/products/small/' + this.get('small_url'));
      },

      toJSON: function() {
         return { product: this.attributes }
      }
   });

   return Product;
});