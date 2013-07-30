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

   var Brand = app.module();

   Brand.Model = Backbone.Model.extend({

      initialize: function() {
         this.listenTo(this, {
            'change': this.setup,
            'add': this.setup
         });
      },

      setup: function() {
         this.set('brandLink', app.beginUrl + app.root + $.t('routing.brand/slug/', { slug: this.get('slug') }));
      },

      urlRoot: Routing.generate('api_v1_get_brands')
   });

   Brand.Collection = Backbone.Collection.extend({
      model: Brand.Model,
      cache: true,
      url: Routing.generate('api_v1_get_brands')
   });

   Brand.Views.Item = Backbone.View.extend({
      template: "brand/item",
      tagName: "li class='thumbnail span3'",

      serialize: function() {
         return { model: this.model };
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Brand.Views.List = Backbone.View.extend({
      template: "brand/list",

      serialize: function() {
         return {
            collection: this.options.brands,
            brandsCount: typeof this.options.brands !== 'undefined' ? this.options.brands.length : 0
         };
      },

      beforeRender: function() {
         this.options.brands.each(function(brand) {
            this.insertView("#brands", new Brand.Views.Item({
               model: brand
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         var that = this;
         this.listenTo(this.options.brands, {
            "sync": this.render
         });
         app.trigger('domchange:title', $.t('brand.pageTitle'));
      }
   });

   Brand.Views.Ticker = Backbone.View.extend({
      template: "brand/ticker",

      serialize: function() {
         return { model : this.options.brand }
      },

      initialize: function() {
         this.listenTo(this.options.brand, "sync", this.render);
      },

      afterRender: function() {
         $(this.el).i18n();
      }
   });

   return Brand;
});