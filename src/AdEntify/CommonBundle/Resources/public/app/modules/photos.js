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
   var container = null;

   Photos.Collection = Backbone.Collection.extend({
      model: Photo.Model
   });

   Photos.Views.Item = Backbone.View.extend({
      template: "photos/item",
      tagName: "li class='isotope-li'",
      addTag: false,
      showPhotoInfo: false,

      serialize: function() {
         return {
            model: this.model,
            addTag: this.addTag,
            showPhotoInfo: this.showPhotoInfo
         };
      },

      initialize: function() {
         this.itemClickBehavior = typeof this.options.itemClickBehavior !== 'undefined' ? this.options.itemClickBehavior : Photos.Common.PhotoItemClickBehaviorDetail;
         this.tagName = typeof this.options.tagName !== 'undefined' ? this.options.tagName : this.tagName;
         this.addTag = typeof this.options.addTag !== 'undefined' ? this.options.addTag : this.addTag;
         this.showPhotoInfo = typeof this.options.showPhotoInfo !== 'undefined' ? this.options.showPhotoInfo : this.showPhotoInfo;
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
         this.$('.photo-img-medium').load(function() {
            $(this).animate({'opacity': '1.0'});
         });
         $(this.el).i18n();
      },

      showTags: function() {
         this.$('.tags').stop().fadeIn(100);
      },

      hideTags: function() {
         $tags = this.$('.tags');
         if ($tags.data('always-visible') == 'no')
            this.$('.tags').stop().fadeOut('fast');
      },

      showPhoto: function(evt) {
         switch (this.itemClickBehavior) {
            case Photos.Common.PhotoItemClickBehaviorDetail:
               Photos.Common.showPhoto(evt, this.model);
               break;
            case Photos.Common.PhotoItemClickBehaviorAddTag:
               Tag.Common.addTagForm(evt, this.model);
               break;
         }
      },

      addTagForm: function(evt) {
         Tag.Common.addTag(evt, this.model);
      },

      events: {
         'click .photo-link': 'showPhoto',
         'click .add-tag': 'addTagForm',
         'mouseenter .photo-container': 'showTags',
         'mouseleave .photo-container': 'hideTags'
      }
   });

   Photos.Views.Content = Backbone.View.extend({
      template: "photos/content",
      filters: false,
      showServices: false,

      initialize: function() {
         if (this.options.pageTitle) {
            app.trigger('domchange:title', this.options.pageTitle);
         }
         else if (this.options.category) {
            this.category = this.options.category;
            this.listenTo(this.options.category, {
               "change": this.render
            });
         }
         else
            app.trigger('domchange:title', $.t('photos.pageTitle'));

         if (typeof this.options.title !== 'undefined') {
            this.title = this.options.title;
         }

         this.filters = typeof this.options.filters !== 'undefined' ? this.options.filters : this.filters;
         this.showServices = typeof this.options.showServices !== 'undefined' ? this.options.showServices : this.showServices;
         this.photosUrl = typeof this.options.photosUrl !== 'undefined' ? this.options.photosUrl : Routing.generate('api_v1_get_photos', { tagged: true });
         this.photosSuccess = typeof this.options.photosSuccess !== 'undefined' ? this.options.photosSuccess : null;
         this.photosError = typeof this.options.photosError !== 'undefined' ? this.options.photosError : null;
      },

      serialize: function() {
         return {
            filters: this.filters,
            showServices: this.showServices,
            category: this.category
         };
      },

      beforeRender: function() {
         // Filters
         if (this.filters && !this.getView('.filters-wrapper')) {
            this.setView('.filters-wrapper', new Photos.Views.Filter({
               photosUrl: this.photosUrl,
               photos: this.options.photos,
               photosSuccess: this.options.photosSuccess,
               photosError: this.options.photosError
            }));
         }

         // Photos
         if (!this.getView('.photos-grid-container')) {
            this.setView('.photos-grid-container', new Photos.Views.List({
               photos: this.options.photos,
               photosUrl: this.photosUrl,
               photosSuccess: this.photosSuccess,
               photosError: this.photosError,
               listenToEnable: this.options.listenToEnable,
               addTag: typeof this.options.addTag !== 'undefined' ? this.options.addTag : false,
               showPhotoInfo: typeof this.options.showPhotoInfo !== 'undefined' ? this.options.showPhotoInfo : this.showPhotoInfo
            }));
         }

         if (this.showServices && !this.getView('.services-container')) {
            var upload = require('modules/upload');
            this.setView('.services-container', new upload.Views.ServiceButtons());
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.title) {
            this.$('.photos-title').html(this.title);
            this.$('.photos-title').fadeIn('fast');
         }
      }
   });

   Photos.Views.List = Backbone.View.extend({
      'template': 'photos/list',

      initialize: function() {
         if (typeof this.options.listenToEnable !== 'undefined') {
            this.listenTo(this.options.photos, 'sync', this.render);
         } else {
            this.options.photos.once('sync', this.render, this);
         }
         this.listenTo(this.options.photos, 'remove', this.render);
         this.listenTo(app, 'photos:submitPhotoDetails', this.submitPhotoDetails);
         this.listenTo(app, 'pagination:loadNextPage', this.loadMorePhotos);

         this.photosUrl = typeof this.options.photosUrl !== 'undefined' ? this.options.photosUrl : Routing.generate('api_v1_get_photos', { tagged: true });
         this.photosSuccess = typeof this.options.photosSuccess !== 'undefined' ? this.options.photosSuccess : null;
         this.photosError = typeof this.options.photosError !== 'undefined' ? this.options.photosError : null;
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-grid", new Photos.Views.Item({
               model: photo,
               itemClickBehavior: typeof this.options.itemClickBehavior !== 'undefined' ? this.options.itemClickBehavior : Photos.Common.PhotoItemClickBehaviorDetail,
               addTag: typeof this.options.addTag !== 'undefined' ? this.options.addTag : false,
               showPhotoInfo: typeof this.options.showPhotoInfo !== 'undefined' ? this.options.showPhotoInfo : this.showPhotoInfo
            }));
         }, this);

         // Pagination
         if (!this.getView('.pagination-wrapper')) {
            this.setView('.pagination-wrapper', new Pagination.Views.NextPage({
               collection: this.options.photos,
               model: new Pagination.Model({
                  buttonText: 'photos.loadMore',
                  loadingText: 'photos.loadingMore'
               })
            }));
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         container = this.$('#photos-grid');

         // Wait images loaded
         container.imagesLoaded( function(){
            container.isotope({
               itemSelector : 'li.isotope-li',
               animationEngine: 'best-available'
            });
            $('#loading-photos').fadeOut('fast');
         });
      },

      newRender: true,
      renderNew: function(photo) {
         view = new Photos.Views.Item({
            model: photo,
            itemClickBehavior: typeof this.options.itemClickBehavior !== 'undefined' ? this.options.itemClickBehavior : Photos.Common.PhotoItemClickBehaviorDetail,
            addTag: typeof this.options.addTag !== 'undefined' ? this.options.addTag : false,
            showPhotoInfo: typeof this.options.showPhotoInfo !== 'undefined' ? this.options.showPhotoInfo : this.showPhotoInfo
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

   Photos.Views.Filter = Backbone.View.extend({
      template: 'photos/filters',
      currentFilter: null,

      initialize: function() {
         this.photosUrl = this.options.photosUrl;
         this.photosSuccess = this.options.photosSuccess;
         this.photosError = this.options.photosError;
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      brandsFilter: function() {
         var activate = this.activateFilter(this.$('.brands-filter').parent());
         this.loadPhotos(activate ? '&brands=1' : '');
      },

      placesFilter: function() {
         var activate = this.activateFilter(this.$('.places-filter').parent());
         this.loadPhotos(activate ? '&places=1' : '');
      },

      peopleFilter: function() {
         var activate = this.activateFilter(this.$('.people-filter').parent());
         this.loadPhotos(activate ? '&people=1' : '');
      },

      dateFilter: function(way) {
         this.loadPhotos('&orderBy=date&way=' + way);
      },

      likeFilter: function(way) {
         this.loadPhotos('&orderBy=likes&way=' + way);
      },

      loadPhotos: function(query) {
         $('#loading-photos').fadeIn('fast');
         this.options.photos.fetch({
            url: this.getOriginalPhotosUrl() + query,
            reset: true,
            success: this.photosSuccess,
            error: this.photosError,
            complete: function() {
               $('#loading-photos').fadeOut('fast')
            }
         });
      },

      activateFilter: function(newFilter) {
         if (newFilter.hasClass('active')) {
            this.activeFilter = null;
            newFilter.removeClass('active');
            return false;
         }

         if (this.activeFilter) {
            this.activeFilter.removeClass('active');
         }
         this.activeFilter = newFilter;
         this.activeFilter.addClass('active');
         return true;
      },

      getOriginalPhotosUrl: function() {
         return this.photosUrl;
      },

      events: {
         'click .brands-filter': 'brandsFilter',
         'click .places-filter': 'placesFilter',
         'click .people-filter': 'peopleFilter',
         'click .date-filter .glyphicon-chevron-down': function() {
            this.dateFilter('DESC');
         },
         'click .date-filter .glyphicon-chevron-up': function() {
            this.dateFilter('ASC');
         },
         'click .like-filter .glyphicon-chevron-down': function() {
            this.likeFilter('DESC');
         },
         'click .like-filter .glyphicon-chevron-up': function() {
            this.likeFilter('ASC');
         }
      }
   });

   Photos.Common = {
      PhotoItemClickBehaviorDetail: 'detail',
      PhotoItemClickBehaviorAddTag: 'add-tag',

      showPhoto: function(evt, photo, photoId, reload) {
         reload = reload || false;
         evt.preventDefault();
         if (!photo || reload) {
            var that = this;
            photo = new Photo.Model({ 'id': photo ? photo.get('id') : photoId });
            photo.fetch({
               complete: function() {
                  that.displayPhoto(evt, photo);
               }
            });
         } else {
            this.displayPhoto(evt,photo);
         }
      },

      displayPhoto: function(evt, photo) {
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
            if (typeof modal.changeHistoryOnClose === 'undefined' || modal.changeHistoryOnClose === true) {
               if (Modernizr.history) {
                  window.history.back();
               }
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