/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var FacebookPhotos = app.module();
   var error = '';

   FacebookPhotos.Model = Backbone.Model.extend({
      smallPicture: null,

      initialize: function() {
         var images = this.get('images');
         if (images && images.length > 0) {
            var image = _.find(images, function(image) {
               return image['width'] == 180;
            });
            if (image) {
               this.set('smallPicture', image['source']);
            }
         }
      }
   });

   FacebookPhotos.Collection = Backbone.Collection.extend({
      model: FacebookPhotos.Model,
      cache: true
   });

   FacebookPhotos.Views.Item = Backbone.View.extend({
      template: "externalServicePhotos/item",

      tagName: "li span2",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      events: {
         "click .check-image" : "toggleCheckedImage"
      },

      toggleCheckedImage: function(e) {
         var container = $(e.currentTarget).find('.check-image-container');
         if (container.length > 0) {
            container.toggleClass('checked');
         }
         this.checkActionButtons();
      },

      afertRender: function() {
         this.checkActionButtons();
      },

      checkActionButtons: function() {
         if ($('.check-image .checked').length > 0) {
            $('.action-buttons').fadeIn('fast');
         } else {
            $('.action-buttons').fadeOut('fast');
         }
      }
   });

   FacebookPhotos.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/list",

      initialize: function() {
         var that = this;
         if (app.fb.isConnected()) {
            this.loadPhotos();
         } else {
            app.on('global:facebook:connected', function() {
               that.loadPhotos();
            });
         }

         this.listenTo(this.options.photos, {
            "add": this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-list", new FacebookPhotos.Views.Item({
               model: photo
            }));
         }, this);
      },

      afterRender: function() {
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
      },

      loadPhotos: function() {
         var that = this;
         FB.api(this.options.albumId + '/photos', function(response) {
            if (!response || response.error) {
               error = response.error;
            } else {
               var photos = [];
               for (var i=0, l=response.data.length; i<l; i++) {
                  photos[i] = response.data[i];
               }
               that.options.photos.add(photos);
            }
            $('#loading-photos').fadeOut('fast');
         });
      },

      events: {
         "click .submit-photos": "submitPhotos",
         "click .photos-rights": "photoRightsClick"
      },

      submitPhotos: function(e) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         // Get checked images
         checkedImages = $('.checked img');
         if (checkedImages.length > 0) {
            var fbImages = [];
            _.each(checkedImages, function(image, index) {
               fbImages[index] = {
                  'source' : $(image).data('source-url'),
                  'width' : $(image).data('source-width'),
                  'height' : $(image).data('source-height')
               };
            });

            // POST Fb images to database
         }
      },

      photoRightsClick: function() {
         if ($('.photos-rights:checked').length != 2) {
            $('.submit-photos').hide();
            app.useLayout().setView("#errors", new FacebookPhotos.Views.ErrorNoRights()).render();
            $('.alert').alert();
         } else {
            $('.submit-photos').fadeIn('fast');
         }
      }
   });

   FacebookPhotos.Views.ErrorNoRights = Backbone.View.extend({
      template: "externalServicePhotos/errors/noRights"
   });

   return FacebookPhotos;
});