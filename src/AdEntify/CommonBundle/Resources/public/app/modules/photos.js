/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/tag",
   "modules/pagination",
   "modules/photo",
   'modules/common',
   "isotope",
   "jquery-ui",
   "modernizer",
   "infinitescroll"
], function(app, Tag, Pagination, Photo, Common) {

   var Photos = app.module();
   var openedContainer = null;
   var openedImage = null;
   var lastImageContainer = null;
   var container = null;

   Photos.Collection = Backbone.Collection.extend({
      model: Photo.Model
   });

   Photos.Views.Item = Backbone.View.extend({
      template: "photos/item",
      tagName: "li class='isotope-li'",
      addTag: false,

      serialize: function() {
         return {
            model: this.model,
            addTag: this.addTag
         };
      },

      initialize: function() {
         this.itemClickBehavior = typeof this.options.itemClickBehavior !== 'undefined' ? this.options.itemClickBehavior : Photos.Common.PhotoItemClickBehaviorDetail;
         this.addTag = typeof this.options.addTag !== 'undefined' ? this.options.addTag : this.addTag;
      },

      beforeRender: function() {
         if (this.model && this.model.has('tags') && this.model.get('tags').length > 0) {
            this.tagsView = this.getView('.tags-container');
            if (!this.tagsView) {
               this.tagsView = new Tag.Views.List({
                  tags: this.model.get('tags'),
                  photo: this.model,
                  desactivatePopover: true
               });
               this.listenTo(this.tagsView, 'tag:remove', function() {
                  // Update tags count
                  this.model.changeTagsCount(-1);
                  container.isotope('reLayout', this.relayoutEnded);
               });
               this.listenTo(this.tagsView, 'relayout', function() {
                  container.isotope("reLayout", this.relayoutEnded);
               });
               this.setView('.tags-container', this.tagsView);
            }
         }
      },

      afterRender: function() {
         $(this.el).find('.photo-img-medium').load(function() {
            $(this).animate({'ohoto-acity': '1.0'});
         });
         $(this.el).i18n();
      },

      /*clickPastille: function() {
         $tags = $(this.el).find('.tags');
         if ($tags.data('always-visible') == 'no') {
            $tags.data('always-visible', 'yes');
            this.showTags();
         } else {
            $tags.data('always-visible', 'no');
            this.hideTags();
         }
      },*/

      showTags: function() {
         $(this.el).find('.tags').stop().fadeIn(100);
      },

      hideTags: function() {
         $tags = $(this.el).find('.tags');
         if ($tags.data('always-visible') == 'no')
            $(this.el).find('.tags').stop().fadeOut('fast');
      },

      deletePhoto: function(e) {
         e.preventDefault();
         this.model.delete();
      },

      showPhoto: function(evt) {
         switch (this.itemClickBehavior) {
            case Photos.Common.PhotoItemClickBehaviorDetail:
               Photos.Common.showPhoto(evt, this.model);
               break;
            case Photos.Common.PhotoItemClickBehaviorAddTag:
               Tag.Common.addTag(evt, this.model);
               break;
         }
      },

      events: {
         'click .deletePhotoButton': 'deletePhoto',
         'click .photo-link': 'showPhoto',
         'mouseenter .photo-container': 'showTags',
         'mouseleave .photo-container': 'hideTags'
      }
   });

   Photos.Views.Content = Backbone.View.extend({
      template: "photos/content",

      initialize: function() {
         openedContainer = null;
         var that = this;
         if (typeof this.options.listenToEnable !== 'undefined') {
            this.listenTo(this.options.photos, 'sync', this.render);
         } else {
            this.options.photos.once('sync', this.render, this);
         }
         this.listenTo(this.options.photos, 'remove', this.render);
         this.listenTo(app, 'global:closeMenuTools', function() {
            that.clickOnPhoto(openedImage);
         });
         this.listenTo(app, 'photos:submitPhotoDetails', this.submitPhotoDetails);
         this.listenTo(app, 'pagination:loadNextPage', this.loadMorePhotos);

         if (this.options.tagged) {
            app.trigger('domchange:title', $.t('photos.pageTitleTagged'));
         } else if (this.options.pageTitle) {
            app.trigger('domchange:title', this.options.pageTitle);
         }
         else if (this.options.category) {
            this.category = this.options.category;
            this.listenTo(this.options.category, {
               "change": this.render
            });
         }
         else if (!this.options.tagged) {
            app.trigger('domchange:title', $.t('photos.pageTitleUntagged'));
         }

         if (typeof this.options.title !== 'undefined') {
            this.title = this.options.title;
         }
      },

      serialize: function() {
         return {
            collection: this.options.photos,
            category: this.category
         };
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-grid", new Photos.Views.Item({
               model: photo,
               itemClickBehavior: typeof this.options.itemClickBehavior !== 'undefined' ? this.options.itemClickBehavior : Photos.Common.PhotoItemClickBehaviorDetail,
               addTag: typeof this.options.addTag !== 'undefined' ? this.options.addTag : false
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.title) {
            $(this.el).find('.photos-title').html(this.title);
            $(this.el).find('.photos-title').fadeIn('fast');
         }
         container = this.$('#photos-grid');

         // Wait images loaded
         container.imagesLoaded( function(){
            container.isotope({
               itemSelector : 'li.isotope-li',
               animationEngine: 'best-available'
            });
            $('#loading-photos').fadeOut('fast');
         });

         // Pagination
         app.useLayout().insertView("#photos", new Pagination.Views.NextPage({
            collection: this.options.photos,
            model: new Pagination.Model({
               buttonText: 'photos.loadMore',
               loadingText: 'photos.loadingMore'
            })
         })).render();
      },

      newRender: true,
      renderNew: function(photo) {
         view = new Photos.Views.Item({
            model: photo
         });
         if (this.newRender) {
            this.newRender = false;
            view.on('afterRender', function() {
               // Wait images loaded
               container.imagesLoaded( function() {
                  container.isotope('appended', $('.isotope-li:not(.isotope-item)'));
               });
            });
         }
         app.useLayout().insertView("#photos-grid", view).render();
      },

      relayoutEnded: function() {
         $('html, body').animate({
            scrollTop: lastImageContainer.offset().top - 60
         }, 300);
      },

      submitPhotoDetails: function() {
         if (app.appState().getCurrentPhotoModel()) {
            app.appState().getCurrentPhotoModel().set('caption', $('#menu-tools #photo-caption').val());
            app.appState().getCurrentPhotoModel().getToken('photo_item', function() {
               app.appState().getCurrentPhotoModel().url = Routing.generate('api_v1_get_photo', { id: app.appState().getCurrentPhotoModel().get('id')});
               app.appState().getCurrentPhotoModel().save();
               var btn = $('#form-details button[type="submit"]');
               btn.button('reset');
            });
         }
      },

      loadMorePhotos: function() {
         this.newRender = true;
         this.stopListening(this.options.photos, 'add');
         this.listenTo(this.options.photos, 'add', this.renderNew);
         this.options.photos.nextPage(function() {
            app.trigger('pagination:nextPageLoaded');
         });
      }
   });

   // Ticker (List of photos)
   Photos.Views.Ticker = Backbone.View.extend({
      template: 'common/tickerPhotoList',

      serialize: function() {
         return { collection: this.options.tickerPhotos };
      },

      beforeRender: function() {
         this.options.tickerPhotos.each(function(photo) {
            this.insertView('.ticker-photos', new Photos.Views.TickerItem({
               model: photo
            }));
         }, this);
      },

      initialize: function() {
         this.listenTo(this.options.tickerPhotos, {
            'sync': this.render
         });
      }
   });

   // Ticker item (Photo)
   Photos.Views.TickerItem = Backbone.View.extend({
      template: "common/tickerPhotoItem",
      tagName: 'li class="ticker-item"',

      serialize: function() {
         return { model: this.model };
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      showPhoto: function(evt) {
         Photos.Common.showPhoto(evt, this.model);
      },

      events: {
         'click .photo-link': 'showPhoto'
      }
   });

   Photos.Common = {
      PhotoItemClickBehaviorDetail: 'detail',
      PhotoItemClickBehaviorAddTag: 'add-tag',

      showPhoto: function(evt, photo) {
         evt.preventDefault();
         var photoView = new Photo.Views.Modal({
            photo: photo
         });
         var modal = new Common.Views.Modal({
            view: photoView,
            showFooter: false,
            showHeader: false,
            modalContentClasses: 'photoModal'
         });
         modal.on('hide', function() {
            if (Modernizr.history) {
               window.history.back();
            }
         });
         modal.on('show', function() {
            if (Modernizr.history) {
               history.pushState(null, photo.get('caption'), evt.currentTarget.href);
            }
         });
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      }
   };

   return Photos;
});