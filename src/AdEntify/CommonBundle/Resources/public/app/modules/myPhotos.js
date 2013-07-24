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
   "isotope",
   "jquery-ui",
   "modernizer",
   "infinitescroll"
], function(app, Tag, Pagination) {

   var MyPhotos = app.module();
   var openedContainer = null;
   var openedImage = null;
   var lastImageContainer = null;
   var container = null;
   var currentPhotoOverlay = null;
   var currentPhoto = null;

   MyPhotos.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_photo');
      },

      defaults: {
         fullSmallUrl: '',
         fullMediumUrl : '',
         fullLargeUrl : ''
      },

      toJSON: function() {
         return { photo: {
            caption: this.get('caption'),
            _token: this.get('_token')
         }}
      },

      initialize: function() {
         this.set('fullMediumUrl', app.rootUrl + '/uploads/photos/users/' + this.get('owner')['id'] + '/medium/' + this.get('medium_url'));
         this.set('fullLargeUrl', app.rootUrl + '/uploads/photos/users/' + this.get('owner')['id'] + '/large/' + this.get('large_url'));
         this.set('fullSmallUrl', app.rootUrl + '/uploads/photos/users/' + this.get('owner')['id'] + '/small/' + this.get('small_url'));
         this.set('profileLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('owner')['id'] }));
         if (this.has('owner'))
            this.set('fullname', this.get('owner')['firstname'] + ' ' + this.get('owner')['lastname']);
      }
   });

   MyPhotos.Collection = Backbone.Collection.extend({
      model: MyPhotos.Model,

      cache: true
   });

   MyPhotos.Views.Item = Backbone.View.extend({
      template: "myPhotos/item",

      tagName: "li class='isotope-li'",

      beforeRender: function() {
         if (this.model.has('tags') && this.model.get('tags').length > 0 && $(this.el).find('.tags').children().length == 0) {
            var that = this;
            _.each(this.model.get('tags'), function(tag) {
               if (tag.type == 'place') {
                  that.insertView(".tags", new Tag.Views.VenueItem({
                     model: new Tag.Model(tag)
                  }));
               } else if (tag.type == 'person') {
                  that.insertView(".tags", new Tag.Views.PersonItem({
                     model: new Tag.Model(tag)
                  }));
               } else if (tag.type == 'product') {
                  that.insertView(".tags", new Tag.Views.ProductItem({
                     model: new Tag.Model(tag)
                  }));
               } else {
                  that.insertView(".tags", new Tag.Views.Item({
                     model: new Tag.Model(tag)
                  }));
               }
            });
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
               $tags.data('state', 'visible');
            } else {
               $tags.fadeOut('fast');
               $tags.data('state', 'hidden');
            }
         }
      },

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      events: {
         "click .adentify-pastille": "showTags"
      }
   });

   MyPhotos.Views.Content = Backbone.View.extend({
      template: "myPhotos/content",

      initialize: function() {
         this.defaultLayout();
         openedContainer = null;

         var that = this;
         this.options.photos.once("sync", this.render, this);
         app.on('global:closeMenuTools', function() {
            that.clickOnPhoto(openedImage);
         });
         app.on('myPhotos:submitPhotoDetails', this.submitPhotoDetails);
         app.on('pagination:loadNextPage', this.loadMorePhotos, this);

         if (this.options.tagged) {
            app.trigger('domchange:title', $.t('myPhotos.pageTitleTagged'));
         } else {
            app.trigger('domchange:title', $.t('myPhotos.pageTitleUntagged'));
         }
         if (typeof this.options.title !== 'undefined') {
            this.title = this.options.title;
         }
      },

      serialize: function() {
         return { collection: this.options.photos };
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-grid", new MyPhotos.Views.Item({
               model: photo
            }));
         }, this);

         this.insertView("#menu-tools", new MyPhotos.Views.MenuTools());
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
            $('#loading-photos').fadeOut('fast', function() {
               /*$('#photos-grid').css({visibility: 'visible'});
               $('#photos-grid').animate({'opacity': '1.0'});*/
            });
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
         view = new MyPhotos.Views.Item({
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

   // Ticker (List of photos)
   MyPhotos.Views.Ticker = Backbone.View.extend({
      template: "common/tickerPhotoList",

      serialize: function() {
         return { collection: this.options.tickerPhotos };
      },

      beforeRender: function() {
         this.options.tickerPhotos.each(function(photo) {
            this.insertView(".ticker-photos", new MyPhotos.Views.TickerItem({
               model: photo
            }));
         }, this);
      },

      initialize: function() {
         this.listenTo(this.options.tickerPhotos, {
            "sync": this.render
         });
      }
   });

   // Ticker item (Photo)
   MyPhotos.Views.TickerItem = Backbone.View.extend({
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

   // Menu Tools
   MyPhotos.Views.MenuTools = Backbone.View.extend({
      template: "myPhotos/menuTools",

      initialize: function() {
         app.on('tagMenuTools:cancel', this.showTools);
         app.on('tagMenuTools:tagAdded', this.showTools);
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
            app.trigger('myPhotos:submitPhotoDetails');
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

   return MyPhotos;
});