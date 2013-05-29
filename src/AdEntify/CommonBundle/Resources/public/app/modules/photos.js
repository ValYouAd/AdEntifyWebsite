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

   Photos.Model = Backbone.Model.extend({ });

   Photos.Collection = Backbone.Collection.extend({
      model: Photos.Model,

      url: function() {
         return "http://api.flickr.com/services/feeds/photos_public.gne?format=json&jsoncallback=?";
      },

      cache: true,

      parse: function(obj) {
         return obj.items;
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

            /*// Setup infinite scroll
            container.infinitescroll({
                  navSelector  : '#infinite-scroll',    // selector for the paged navigation
                  nextSelector : '#infinite-scroll a',  // selector for the NEXT link (to page 2)
                  itemSelector : 'li',     // selector for all items you'll retrieve
                  loading: {
                     finishedMsg: 'No more pages to load.',
                     img: 'http://i.imgur.com/qkKy8.gif'
                  },
                  debug: true
               },
               // call Isotope as a callback
               function( newElements ) {
                  alert('toto');
                  container.isotope( 'appended', $( newElements ) );
               }
            );*/
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

   Photos.Views.Ticker = Backbone.View.extend({
      template: "photos/ticker",

      serialize: function() {
         return {};
      }
   });

   Photos.Views.MenuTools = Backbone.View.extend({
      template: "photos/menuTools",

      serialize: function() {
         return {};
      },

      close: function() {
         app.trigger('global:closeMenuTools');
      },

      events: {
         "click .close": "close"
      }
   });

   return Photos;
});