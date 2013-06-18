/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 14/06/2013
 * Time: 18:38
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Comment = app.module();

   Comment.Model = Backbone.Model.extend({

   });

   Comment.Collection = Backbone.Collection.extend({
      model: Comment.Model,
      cache: true,
      url: Routing.generate('api_v1_get_brands')
   });

   Comment.Views.Item = Backbone.View.extend({
      template: "brand/item",
      tagName: "li class='thumbnail span3'",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Comment.Views.List = Backbone.View.extend({
      template: "brand/list",

      serialize: function() {
         return {
            collection: this.options.brands,
            brandsCount: typeof this.options.brands !== 'undefined' ? this.options.brands.length : 0
         };
      },

      beforeRender: function() {
         this.options.brands.each(function(brand) {
            this.insertView("#brands", new Comment.Views.Item({
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

   return Comment;
});