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
   "isotope",
   "jquery-ui",
   "modernizer",
   "infinitescroll"
], function(app, Tag) {

   var Photos = app.module();
   var openedContainer = null;
   var openedImage = null;
   var lastImageContainer = null;
   var container = null;

   Photos.Model = Backbone.Model.extend({
      defaults: {
         fullSmallUrl: '',
         fullMediumUrl : '',
         fullLargeUrl : ''
      },

      initialize: function() {
         this.set('fullMediumUrl', app.rootUrl + 'uploads/photos/users/' + this.get('owner')['id'] + '/medium/' + this.get('medium_url'));
         this.set('fullLargeUrl', app.rootUrl + 'uploads/photos/users/' + this.get('owner')['id'] + '/large/' + this.get('large_url'));
         this.set('fullSmallUrl', app.rootUrl + 'uploads/photos/users/' + this.get('owner')['id'] + '/small/' + this.get('small_url'));
      }
   });

   Photos.Collection = Backbone.Collection.extend({
      model: Photos.Model,
      cache: true,

      parse: function(obj) {
         return obj;
      }
   });

   Photos.Views.Item = Backbone.View.extend({
      template: "photos/item",

      tagName: "li class='isotope-li'",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      beforeRender: function() {
         if (this.model.has('tags') && this.model.get('tags').length > 0) {
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

      events: {
         "click .adentify-pastille": "showTags"
      }
   });

   Photos.Views.Content = Backbone.View.extend({
      template: "photos/content",

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
      },

      afterRender: function() {
         var that = this;

         if (typeof this.title !== "undefined")
            $(this.el).find('.photos-title').html(this.title);

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

         // Click on photo overlay
         container.delegate('.photo-overlay', 'click', function() {
            lastImage = $(this).siblings('img[data-type="medium"]');
            that.clickOnPhoto(lastImage);
         });
      },

      clickOnPhoto: function(imageClicked) {
         var photoDiv = imageClicked.parents('.photo')
         // Already in edit mode
         if (photoDiv.hasClass('large')) {
            // Resize to medium size
            this.resizeToMediumSize(imageClicked, true);
            openedContainer = null;
         } else {
            // If an image is already in large size, go to medium size
            if (openedContainer) {
               this.resizeToMediumSize(openedContainer.children("img[data-type='large']"));
            }
            if (!openedContainer) {
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
         openedContainer = null;
      },

      resizeToLargeSize: function(image) {
         var largeUrl = image.data('large-url');
         var largeWidth = image.data('large-width');
         var parentDiv = image.parents('.photo');
         var containerDiv = image.parents('.photo-container');
         var mediumUrl = image.attr('src');

         if (parentDiv) {
            openedContainer = containerDiv;
            lastImageContainer = containerDiv;

            var largeImage = containerDiv.children('img[data-type="large"]');
            // Check if large image is already loaded
            if (largeImage.length > 0) {
               openedImage = largeImage;
               // Change photo container size
               parentDiv.removeClass('medium').addClass("large");
               image.hide();
               largeImage.show();
               container.isotope("reLayout", this.relayoutEnded);
            } else {
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

      initialize: function() {
         var that = this;
         this.listenTo(this.options.photos, {
            "sync": this.render
         });
         app.on('global:closeMenuTools', function() {
            that.clickOnPhoto(openedImage);
         });

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
      }
   });

   // Ticker (List of photos)
   Photos.Views.Ticker = Backbone.View.extend({
      template: "common/tickerPhotoList",

      serialize: function() {
         return { collection: this.options.tickerPhotos };
      },

      beforeRender: function() {
         this.options.tickerPhotos.each(function(photo) {
            this.insertView("#ticker-photos", new Photos.Views.TickerItem({
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