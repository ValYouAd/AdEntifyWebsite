/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/07/2013
 * Time: 15:05
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Pagination = app.module();

   Pagination.Model = Backbone.Model.extend({
      default: {
         buttonText: 'pagination.loadMore',
         loadingText: 'pagination.loadingMore'
      }
   });

   Pagination.Views.NextPage = Backbone.View.extend({
      template: 'pagination/nextpage',

      serialize: function() {
         return { model: this.model }
      },

      initialize: function() {
         var that = this;
         this.collection = this.options.collection;
         app.on('pagination:nextPageLoaded', function() {
            // Check if there is more data to load
            if (!that.options.collection.hasNextPage()) {
               that.loadMoreButton.fadeOut('fast');
            }
            // Reset button state
            setTimeout(function() {
               that.loadMoreButton.button('reset');
            }, 1000);
         });

         this.listenTo(this.options.collection, {
            "sync": this.checkButtonVisibility
         });
      },

      afterRender: function() {
         $(this.el).i18n();
         this.loadMoreButton = this.$('.loadMore');
         this.checkButtonVisibility();
      },

      checkButtonVisibility: function() {
         if (this.collection.hasNextPage()) {
            this.loadMoreButton.fadeIn('fast');
         }
      },

      loadMore: function() {
         this.loadMoreButton.button('loading');
         app.trigger('pagination:loadNextPage');
      },

      events: {
         "click .loadMore": "loadMore"
      }

   });

   return Pagination;
});