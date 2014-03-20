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
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });
         this.setup();
      },

      setup: function() {
         if (this.has('brand')) {
            var Brand = require('modules/brand');
            this.set('brandModel', new Brand.Model(this.get('brand')));
         }
      },

      toJSON: function() {
         return { product: this.attributes };
      }
   });

   return Product;
});