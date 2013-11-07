/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/externalServicePhotos"
], function(app, ExternalServicePhotos) {

   var FacebookPhotos = app.module();
   var error = '';

   FacebookPhotos.Model = Backbone.Model.extend({
      smallUrl: null,

      initialize: function() {
         var images = this.get('images');
         if (images && images.length > 0) {
            var smallImage = _.find(images, function(image) {
               return image['width'] == 180;
            });
            var mediumImage = _.find(images, function(image) {
               return image['width'] == 320;
            });
            var largeImage = _.find(images, function(image) {
               return image['width'] == 960;
            });
            if (smallImage) {
               this.set('thumbUrl', smallImage['source']);
               this.set('smallUrl', smallImage['source']);
               this.set('smallWidth', smallImage['width']);
               this.set('smallHeight', smallImage['height']);
            }
            if (mediumImage) {
               this.set('mediumUrl', mediumImage['source']);
               this.set('mediumWidth', mediumImage['width']);
               this.set('mediumHeight', mediumImage['height']);
            }
            if (largeImage) {
               this.set('largeUrl', largeImage['source']);
               this.set('largeWidth', largeImage['width']);
               this.set('largeHeight', largeImage['height']);
            }
            // Get larger image (original)
            this.set('originalUrl', images[0].source);
            this.set('originalWidth', images[0].width);
            this.set('originalHeight', images[0].height);
            this.set('servicePhotoId', this.get('id'));
            if (this.has('name'))
               this.set('title', this.get('name'));
         }
      }
   });

   FacebookPhotos.Collection = Backbone.Collection.extend({
      model: FacebookPhotos.Model,
      cache: true
   });

   FacebookPhotos.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/list",

      serialize: function() {
         return {
            album: this.albumName
         };
      },

      initialize: function() {
         var that = this;
      this.albumName = '';
         if (app.fb.isConnected()) {
            this.loadPhotos();
         } else {
            this.listenTo(app, 'global:facebook:connected', function() {
               that.loadPhotos();
            });
         }

         this.listenTo(app, 'externalServicePhoto:submitPhotos', this.submitPhotos);
         app.trigger('domchange:title', $.t('facebook.photosPageTitle'));
         this.listenTo(this.options.photos, 'sync', this.render);
         this.photos = this.options.photos;
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-list", new ExternalServicePhotos.Views.Item({
               model: photo
            }));
         }, this);

         if (!this.getView('.upload-counter-view')) {
            this.setView('.upload-counter-view', new ExternalServicePhotos.Views.Counter({
               counterType: 'photos'
            }));
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
      },

      loadPhotos: function() {
         var that = this;
         if (this.options.albumId == 'photos-of-you') {
            app.fb.loadUserPhotos(function(response) {
               that.albumName = $.t('upload.photosOfYou');
               that.loadPhotosCompleted(response, that.options.photos);
            });
         } else {
            app.fb.loadAlbumName(this.options.albumId, function(name) {
               that.albumName = name;
               that.render();
            });
            app.fb.loadPhotos(this.options.albumId, function(response) {
               that.loadPhotosCompleted(response, that.options.photos);
            });
         }
      },

      loadPhotosCompleted: function(response, photos) {
         if (response.error){
            error = response.error;
         } else {
            photos.add(response);
         }
         $('#loading-photos').fadeOut('fast');
         photos.trigger('sync');
      },

      submitPhotos: function(options) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         // Get checked images
         checkedImages = $('.checked img');
         if (checkedImages.length > 0) {
            var fbImages = [];
            var that = this;
            _.each(checkedImages, function(image, index) {
               fbImage = {
                  'originalSource' : $(image).data('original-url'),
                  'originalWidth' : $(image).data('original-width'),
                  'originalHeight' : $(image).data('original-height'),
                  'smallSource': $(image).data('small-url'),
                  'smallWidth': $(image).data('small-width'),
                  'smallHeight': $(image).data('small-height'),
                  'mediumSource': $(image).data('medium-url'),
                  'mediumWidth': $(image).data('medium-width'),
                  'mediumHeight': $(image).data('medium-height'),
                  'largeSource': $(image).data('large-url'),
                  'largeWidth': $(image).data('large-width'),
                  'largeHeight': $(image).data('large-height'),
                  'id': $(image).data('service-photo-id'),
                  'title' : $(image).data('title'),
                  'confidentiality': options.confidentiality,
                  'categories': options.categories
               };
               photoModel = that.photos.get(fbImage.id);
               if (photoModel.has('place')) {
                  fbImage.place = photoModel.get('place');
               }
               if (photoModel.has('tags') && typeof photoModel.get('tags').data != 'undefined') {
                  fbImage.tags = photoModel.get('tags').data;
               }
               fbImages[index] = fbImage;
            });

            // POST images to database
            $.ajax({
               url : Routing.generate('upload_load_external_photos'),
               type: 'POST',
               data: { 'images': fbImages, 'source': 'facebook' },
               success: function() {
                  app.trigger('externalPhotos:uploadingInProgress');
               },
               error: function(e) {
                  // Hide loader
                  $('#loading-upload').fadeOut('fast', function() {
                     $('#photos-container').fadeIn('fast');
                  });
                  app.trigger('externalPhotos:uploadingError');
               }
            });
         }
      },

      events: {
         'click .submit-photos-button': 'submitPhotos'
      }
   });

   return FacebookPhotos;
});