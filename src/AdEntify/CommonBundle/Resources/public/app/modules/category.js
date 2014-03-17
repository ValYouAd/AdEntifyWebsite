/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 20/06/2013
 * Time: 20:07
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Category = app.module();

   Category.Model = Backbone.Model.extend({
      urlRoot: Routing.generate('api_v1_get_category', { locale: currentLocale }),

      initialize: function() {
         this.listenTo(this, {
            'change': this.setup,
            'add': this.setup
         });
      },

      setup: function() {
         this.set('categoryLink', app.beginUrl + app.root + $.t('routing.category/slug/', { slug: this.get('slug') }));
      }
   });

   Category.Collection = Backbone.Collection.extend({
      model: Category.Model,
      cache: true,
      url: Routing.generate('api_v1_get_categories')
   });

   Category.Views.Select = Backbone.View.extend({
      template: "category/select",

      serialize: function() {
         return { collection: this.options.categories };
      },

      initialize: function() {
         this.listenTo(this.options.categories, "sync", this.render);
      }
   });

   Category.Views.List = Backbone.View.extend({
      template: "category/list",
      tagName: 'span class="categories-inner"',

      serialize: function() {
         return {
            categories: this.options.categories
         };
      },

      initialize: function() {
         this.listenTo(this.options.categories, {
            "sync": this.render
         });
      }
   });

   return Category;
});