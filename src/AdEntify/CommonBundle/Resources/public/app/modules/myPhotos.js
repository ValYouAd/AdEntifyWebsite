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
   "infinitescroll",
   "bootstrap",
   "modules/tag"
], function(app, Tag) {

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

      fullSmallUrl: '',
      fullMediumUrl : '',
      fullLargeUrl : '',

      toJSON: function() {
         return { photo: {
            caption: this.get('caption'),
            _token: this.get('_token')
         }}
      },

      initialize: function() {
         this.set('fullMediumUrl', app.rootUrl + '/uploads/photos/users/' + app.oauth.get('userId') + '/medium/' + this.get('medium_url'));
         this.set('fullLargeUrl', app.rootUrl + '/uploads/photos/users/' + app.oauth.get('userId') + '/large/' + this.get('large_url'));
         this.set('fullSmallUrl', app.rootUrl + '/uploads/photos/users/' + app.oauth.get('userId') + '/small/' + this.get('small_url'));
      }
   });

   MyPhotos.Collection = Backbone.Collection.extend({
      model: MyPhotos.Model,

      cache: true,

      parse: function(obj) {
         return obj;
      }
   });

   MyPhotos.Views.Item = Backbone.View.extend({
      template: "myPhotos/item",

      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   MyPhotos.Views.Content = Backbone.View.extend({
      template: "photos/content",

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

         // Click on photo overlay
         container.delegate('.photo-overlay', 'click', function() {
            lastImage = $(this).siblings('img[data-type="medium"]');
            that.clickOnPhoto(lastImage);
         });
      },

      clickOnPhoto: function(imageClicked) {
         // Already in edit mode
         if (imageClicked.data('type') == 'large') {
            $("#dashboard").toggleClass('edit-mode view-mode');
            $('#content').switchClass('span11', 'span9');
            $("aside").switchClass("span1", "span3");
            $('#photos').switchClass('span9', 'span12');
            // Resize to medium size
            this.resizeToMediumSize(imageClicked, true);
            openedContainer = null;
         } else {
            // If an image is already in large size, go to medium size
            if (openedContainer) {
               this.resizeToMediumSize(openedContainer.children("img[data-type='large']"));
            }
            if (!openedContainer) {
               $("#dashboard").toggleClass('edit-mode view-mode');
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

      updateMenuTools: function(photoId) {
         currentPhoto = this.options.photos.get(photoId);
         $('#menu-tools #photo-caption').val(currentPhoto.get('caption'));
      },

      submitPhotoDetails: function() {
         if (currentPhoto) {
            currentPhoto.set('caption', $('#menu-tools #photo-caption').val());
            currentPhoto.getToken('photo_item', function() {
               currentPhoto.url = Routing.generate('api_v1_get_photo', { id: currentPhoto.get('id')});
               currentPhoto.save();
               var btn = $('#form-details button[type="submit"]');
               btn.button('reset');
            });
         }
      },

      initialize: function() {
         var that = this;
         this.listenTo(this.options.photos, {
            "sync": this.render
         });
         app.on('global:closeMenuTools', function() {
            that.clickOnPhoto(openedImage);
         });
         app.on('myPhotos:submitPhotoDetails', this.submitPhotoDetails);
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
            this.insertView("#ticker-photos", new MyPhotos.Views.TickerItem({
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

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   // Menu Tools
   MyPhotos.Views.MenuTools = Backbone.View.extend({
      template: "myPhotos/menuTools",

      close: function() {
         this.unloadTagging();
         app.trigger('global:closeMenuTools');
      },

      addTag: function() {
         $photo = $('#photos-grid .large');
         currentPhotoOverlay = $photo.find('.photo-overlay');
         this.setupTagging();
      },

      setupTagging: function() {
         currentPhotoOverlay.css({ cursor: 'crosshair'});
         currentPhotoOverlay.bind('click', this.addTagHandler);
      },

      unloadTagging: function() {
         if (currentPhotoOverlay) {
            currentPhotoOverlay.css({ cursor: 'pointer'});
            currentPhotoOverlay.unbind('click', this.addTagHandler);
         }
      },

      addTagHandler: function(e) {
         var tagRadius = 12.5;
         var xPosition = (e.offsetX - tagRadius) / e.currentTarget.clientWidth;
         var yPosition = (e.offsetY - tagRadius) / e.currentTarget.clientHeight;

         // Add new tag
         tag = document.createElement("div");
         /*tag.innerHTML = '<i class="icon-tag icon-white"></i>';*/
         tag.setAttribute('style', 'left: ' + xPosition*100 + '%; top: ' + yPosition*100  + '%');
         tag.setAttribute('class', 'tag');
         $(tag).appendTo(currentPhotoOverlay);
         app.useLayout().setView("#menu-tools .form", new Tag.Views.AddTagForm()).render();
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

      events: {
         "click .close": "close",
         "click #add-tag": "addTag",
         "click .cancel": "close",
         "click #form-details button[type='submit']": "submitPhotoDetails"
      }
   });

   MyPhotos.Views.AddTagForm = Backbone.View.extend({
      template: "myPhotos/addTagForm",

      afterRender: function() {
         $('.nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
         })
      }
   });

   return MyPhotos;
});