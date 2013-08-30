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
   "isotope",
   "jquery-ui",
   "modernizer",
   "infinitescroll"
], function(app, Tag, Pagination, Photo) {

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

      serialize: function() {
         return { model: this.model };
      },

      beforeRender: function() {
         if (this.model.has('tags') && this.model.get('tags').length > 0) {
            this.tagsView = this.getView('.tags-container');
            if (!this.tagsView) {
               this.tagsView = new Tag.Views.List({
                  tags: this.model.get('tags'),
                  photo: this.model
               });
               this.listenTo(this.tagsView, 'tag:remove', function() {
                  // Update tags count
                  this.model.changeTagsCount(-1);
                  container.isotope('reLayout', this.relayoutEnded);
               });
               this.listenTo(this.tagsView, 'relayout', function() {
                  container.isotope("reLayout", this.relayoutEnded);
               });
               this.setView('.tags-container', this.tagsView).render();
            }
         }
      },

      afterRender: function() {
         $(this.el).find('img').load(function() {
            $(this).animate({'opacity': '1.0'});
         });
         $(this.el).i18n();
      },

      showTags: function() {
         $tags = $(this.el).find('.tags');
         if ($tags.length > 0) {
            if ($tags.data('state') == 'hidden') {
               $tags.fadeIn('fast');
               $tags.attr('data-state', 'visible');
            } else {
               $tags.fadeOut('fast');
               $tags.attr('data-state', 'hidden');
            }
         }
      },

      deletePhoto: function(e) {
         e.preventDefault();
         this.model.delete();
      },

      events: {
         "click .adentify-pastille": 'showTags',
         "click .deletePhotoButton": 'deletePhoto'
      }
   });

   Photos.Views.Content = Backbone.View.extend({
      template: "photos/content",

      initialize: function() {
         this.defaultLayout();
         openedContainer = null;

         var that = this;
         this.options.photos.once('sync', this.render, this);
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
               model: photo
            }));
         }, this);

         this.insertView("#menu-tools", new Photos.Views.MenuTools());
      },

      afterRender: function() {
         var that = this;
         $(this.el).i18n();
         $(this.el).find('.photos-title').html(this.title);
         container = this.$('#photos-grid');

         // Wait images loaded
         container.imagesLoaded( function(){
            container.isotope({
               itemSelector : 'li.isotope-li',
               animationEngine: 'best-available'
            });
            $('#loading-photos').fadeOut('fast');
         });

         // Click on photo overlay
         container.delegate('.photo-overlay', 'click', function() {
            lastImage = $(this).siblings('img[data-type="medium"]');
            that.clickOnPhoto(lastImage);
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

      clickOnPhoto: function(imageClicked) {
         // Already in edit mode
         if (imageClicked.data('type') == 'large') {
            this.defaultLayout();
            // Resize to medium size
            this.resizeToMediumSize(imageClicked, true);
            openedContainer = null;
         } else {
            // If an image is already in large size, go to medium size
            if (openedContainer) {
               this.resizeToMediumSize(openedContainer.children("img[data-type='large']"));
            }
            if (!openedContainer) {
               $("#dashboard").removeClass('view-mode').addClass('edit-mode');
               $('aside').switchClass("span3", "span1");
               $('#content').switchClass('span9', 'span11');
               $('#photos').switchClass('span12', 'span9');
               if (!Modernizr.csstransitions) {
                  $('#menu-tools').animate({left: "63%"});
                  $('#photos').animate({left: "28%"});
               }
               // Resize to large size
               this.resizeToLargeSize(imageClicked);
            } else {
               // Resize to large size
               this.resizeToLargeSize(imageClicked);
            }
         }
      },

      resizeToMediumSize: function(image, relayout) {
         relayout = typeof relayout !== 'undefined' ? relayout : false;

         // Hide large image
         image.hide();
         // Show medium image
         image.siblings("img[data-type='medium']").show();
         // Resize container
         image.parents('.photo').removeClass('large').addClass('medium');
         // Relayout if ask
         if (relayout) {
            container.isotope('reLayout', this.relayoutEnded);
         }
      },

      resizeToLargeSize: function(image) {
         var largeUrl = image.data('large-url');
         var largeWidth = image.data('large-width');
         var parentDiv = image.parents('.photo');
         var containerDiv = image.parents('.photo-container');
         var mediumUrl = image.attr('src');

         this.updateMenuTools(image.data("id"));

         if (parentDiv) {
            openedContainer = containerDiv;
            lastImageContainer = containerDiv;

            var largeImage = parentDiv.children("img[data-type='large']");
            // Check if large image is already loaded
            if (largeImage.length > 0) {
               openedImage = largeImage;
               // Change photo container size
               parentDiv.removeClass('medium').addClass("large");
               image.hide();
               largeImage.show();
               container.isotope("reLayout", this.relayoutEnded);
            } else {
               // Save medium width
               image.data("medium-width", image.width());
               // Increase medium image size before loading the large one
               image.width(largeWidth);
               image.height('auto');
               // Change photo container size
               parentDiv.removeClass('medium').addClass("large");
               // Relayout
               container.isotope('reLayout', this.relayoutEnded);
               // Load the large image
               $("<img/>", {
                  src: largeUrl,
                  'data-medium': mediumUrl,
                  'data-type': "large",
                  style: "display: none;"
               }).appendTo(containerDiv).load(function() {
                     openedImage = $(this);
                     image.hide();
                     image.width(image.data("medium-width"));
                     $(this).css({display: 'block'});
                  });
            }
         }
      },

      relayoutEnded: function() {
         $('html, body').animate({
            scrollTop: lastImageContainer.offset().top - 60
         }, 300);
      },

      defaultLayout: function() {
         $("#dashboard").removeClass('edit-mode').addClass('view-mode');
         $('#content').switchClass('span11', 'span9');
         $("aside").switchClass("span1", "span3");
         $('#photos').switchClass('span9', 'span12');
      },

      updateMenuTools: function(photoId) {
         app.appState().set('currentPhotoModel', this.options.photos.get(photoId));
         $('#menu-tools #photo-caption').val(app.appState().getCurrentPhotoModel().get('caption'));
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

   // Menu Tools
   Photos.Views.MenuTools = Backbone.View.extend({
      template: "photos/menuTools",

      initialize: function() {
         this.listenTo(app, 'tagMenuTools:cancel', this.showTools);
         this.listenTo(app, 'tagMenuTools:tagAdded', this.showTools);
      },

      close: function() {
         app.trigger('global:closeMenuTools');
      },

      addTag: function() {
         app.useLayout().setView("#tool-details", new Tag.Views.MenuTools({
            tags: new Tag.Collection()
         })).render();
         app.trigger('tagMenuTools:addTag');
         this.hideTools();
      },

      hideTools: function() {
         $('#tools').fadeOut('fast', function() {
            $('#tool-details').fadeIn('fast');
         });
      },

      showTools: function() {
         $('#tool-details').fadeOut('fast', function() {
            $('#tools').fadeIn('fast');
         });
      },

      // Detail Form Submit
      submitPhotoDetails: function(e) {
         e.preventDefault();
         // Validate
         if ($('#photo-caption').val()) {
            var btn = $('#form-details button[type="submit"]');
            btn.button('loading');
            app.trigger('photos:submitPhotoDetails');
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      events: {
         "click .close": "close",
         "click #add-tag": "addTag",
         "click #form-details button[type='submit']": "submitPhotoDetails"
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

      tagName: "li",

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

   return Photos;
});