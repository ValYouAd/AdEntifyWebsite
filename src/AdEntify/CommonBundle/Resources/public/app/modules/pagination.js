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
      defaults: {
         buttonText: 'pagination.loadMore',
         loadingText: 'pagination.loadingMore'
      }
   });

   Pagination.Views.NextPage = Backbone.View.extend({
      template: 'pagination/nextpage',
      tagName: 'div class="pagination-next-page"',
      scrollEventBind: false,
      loadMoreFired: false,
      showLoadMoreButton: false,

      serialize: function() {
         return {
            model: this.model,
            showLoadMoreButton: this.showLoadMoreButton
         };
      },

      initialize: function() {
         var that = this;
         this.collection = this.options.collection;
         this.listenTo(this, 'pagination:nextPageLoaded', function() {
            this.$('.loading-gif-container').stop().fadeOut();
            // Check if there is more data to load
            if (!that.options.collection.hasNextPage()) {
               $(this.el).find('.pagination').fadeOut('fast');
               that.loadMoreButton.hide();
            }
            // Reset button state
            that.loadMoreButton.button('reset');
         });

         this.showLoadMoreButton = typeof this.options.showLoadMoreButton !== 'undefined' ? this.options.showLoadMoreButton : this.showLoadMoreButton;
         this.listenTo(this.options.collection, 'sync', this.checkWindowScrollHandler);
      },

      afterRender: function() {
         $(this.el).i18n();
         this.loadMoreButton = this.$('.loadMore');
         if (!this.showLoadMoreButton)
            this.checkWindowScrollHandler();
      },

      checkWindowScrollHandler: function() {
         this.loadMoreFired = false;
         if (this.collection.hasNextPage() && !this.scrollEventBind) {
            var that = this;
            $(window).bind('scroll', function() {
               if ($(window).scrollTop() >= ($(document).height() - 300) - $(window).height() && !that.loadMoreFired) {
                  that.loadMore();
               }
            });
         } else {
            $(window).unbind('scroll');
         }
      },

      loadMore: function() {
         this.loadMoreFired = true;
         this.$('.loading-gif-container').stop().fadeIn();
         this.loadMoreButton.button('loading');
         this.trigger('pagination:loadNextPage');
      },

      events: {
         'click .loadMore': 'loadMore'
      }

   });

   return Pagination;
});