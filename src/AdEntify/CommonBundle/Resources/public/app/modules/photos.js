/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "isotope",
   "jquery-ui",
   "modernizer",
   "infinitescroll"
], function(app) {

   var Photos = app.module();
   var openedContainer = null;
   var openedImage = null;
   var lastImageContainer = null;
   var container = null;

   Photos.Model = Backbone.Model.extend({
      fullSmallUrl: '',
      fullMediumUrl : '',
      fullLargeUrl : '',

      initialize: function() {
         this.set('fullMediumUrl', app.rootUrl + '/uploads/photos/users/' + app.oauth.get('userId') + '/medium/' + this.get('medium_url'));
         this.set('fullLargeUrl', app.rootUrl + '/uploads/photos/users/' + app.oauth.get('userId') + '/large/' + this.get('large_url'));
         this.set('fullSmallUrl', app.rootUrl + '/uploads/photos/users/' + app.oauth.get('userId') + '/small/' + this.get('small_url'));
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

      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Photos.Views.Content = Backbone.View.extend({
      template: "photos/content",

      serialize: function() {
         return { collection: this.options.photos };
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
         container = this.$('#photos-grid');

         // Wait images loaded
         container.imagesLoaded( function(){
            container.isotope({
               itemSelector : 'li',
               animationEngine: 'best-available'
            });
            $('#loading-photos').fadeOut('fast', function() {
               $('#photos-grid').css({visibility: 'visible'});
               $('#photos-grid').animate({'opacity': '1.0'});
            });
         });

         // Click on photo
         container.delegate('img', 'click', function() {
            lastImage = $(this);
            that.clickOnPhoto($(this));
         });
      },

      clickOnPhoto: function(imageClicked) {
         // Already in edit mode
         if (imageClicked.data('type') == 'large') {
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
         image.parent().removeClass('large').addClass('medium');
         // Relayout if ask
         if (relayout) {
            container.isotope('reLayout', this.relayoutEnded);
         }
      },

      resizeToLargeSize: function(image) {
         var largeUrl = image.data('large-url');
         var largeWidth = image.data('large-width');
         var parentDiv = image.parent();
         var mediumUrl = image.attr('src');

         if (parentDiv) {
            openedContainer = parentDiv;
            lastImageContainer = parentDiv;

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
                  style: "display: none;",
                  class: "img-polaroid"
               }).appendTo(parentDiv).load(function() {
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

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   return Photos;
});