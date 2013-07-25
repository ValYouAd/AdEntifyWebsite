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
      urlRoot: Routing.generate('api_v1_get_category'),

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

   Category.Views.Item = Backbone.View.extend({
      template: "category/item",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Category.Views.List = Backbone.View.extend({
      template: "category/list",

      initialize: function() {
         this.photoId = this.options.photoId;
         this.listenTo(this.options.categories, {
            "sync": this.render
         });
         this.categories = this.options.categories;
      },

      beforeRender: function() {
         this.options.categories.each(function(category) {
            this.insertView(".categories-list", new Category.Views.Item({
               model: category
            }));
         }, this);
      }
   });

   return Category;
});