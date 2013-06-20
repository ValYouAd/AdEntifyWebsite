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

   return Category;
});