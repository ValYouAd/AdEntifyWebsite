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
      url: Routing.generate('api_v1_get_comments')
   });

   Comment.Views.Item = Backbone.View.extend({
      template: "comment/item",
      tagName: "li class='thumbnail span3'",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Comment.Views.List = Backbone.View.extend({
      template: "comment/list",

      serialize: function() {
         return {
            collection: this.options.comments,
            commentsCount: typeof this.options.comments !== 'undefined' ? this.options.comments.length : 0
         };
      },

      beforeRender: function() {
         this.options.comments.each(function(comment) {
            this.insertView("#comments", new Comment.Views.Item({
               model: comment
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         var that = this;
         this.listenTo(this.options.comments, {
            "sync": this.render
         });
      }
   });

   return Comment;
});