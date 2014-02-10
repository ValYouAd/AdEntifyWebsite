/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/externalServicePhotos",
   'modules/upload'
], function(app, ExternalServicePhotos, Upload) {

   var FacebookPhotos = app.module();
   var error = '';

   FacebookPhotos.Model = Backbone.Model.extend({
      smallUrl: null,

      initialize: function() {
         var images = this.get('images');
         if (images && images.length > 0) {
            var smallImage = _.find(images, function(image) {
               return image['width'] <= 200;
            });
            var mediumImage = _.find(images, function(image) {
               return image['width'] <= 500 && image['width'] > 300;
            });
            var largeImage = _.find(images, function(image) {
               return image['width'] <= 1600 && image['width'] > 700;
            });
            if (smallImage) {
               this.set('smallUrl', smallImage['source']);
               this.set('smallWidth', smallImage['width']);
               this.set('smallHeight', smallImage['height']);
            }
            if (mediumImage) {
               this.set('thumbUrl', mediumImage['source']);
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
      confidentiality: 'public',
      albumName: '',

      serialize: function() {
         return {
            album: this.albumName,
            showBackTo: true,
            backToText: $.t('externalServicePhotos.backToAlbums'),
            backToLink: app.beginUrl + app.root + $.t('routing.facebook/albums/'),
            serviceName: 'Facebook',
            loweredServiceName: 'facebook'
         };
      },

      initialize: function() {
         var that = this;
         if (app.fb.isConnected()) {
            this.loadPhotos();
         } else {
            this.listenTo(app, 'global:facebook:connected', function() {
               that.loadPhotos();
            });
         }

         app.trigger('domchange:title', $.t('facebook.photosPageTitle'));
         this.listenTo(this.options.photos, 'sync', this.render);
         this.listenTo(this.options.categories, {
            'sync': this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-list", new ExternalServicePhotos.Views.Item({
               model: photo,
               categories: this.options.categories
            }));
         }, this);

         if (!this.getView('.upload-counter-view')) {
            var counterView = new ExternalServicePhotos.Views.Counter({
               counterType: 'photos'
            });
            var that = this;
            counterView.on('checkedPhoto', function(count) {
               var submitButton = $(that.el).find('.submit-photos-button');
               if (count > 0) {
                  if ($(that.el).find('.submit-photos-button:visible').length == 0)
                     submitButton.fadeIn('fast');
               } else {
                  if ($(that.el).find('.submit-photos-button:hidden').length == 0)
                     submitButton.fadeOut('fast');
               }
            });
            this.setView('.upload-counter-view', counterView);
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
         var that = this;
         $(this.el).find('.photos-confidentiality').change(function() {
            if ($(this).val())
               that.confidentiality = $(this).val();
         });
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

      submitPhotos: function() {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         // Get checked images
         var counterView = this.getView('.upload-counter-view');
         var that = this;
         if (counterView.checkedPhotos.length > 0) {
            var fbImages = [];
            _.each(counterView.checkedPhotos, function(model) {
               fbImage = {
                  'originalSource' : model.get('originalUrl'),
                  'originalWidth' : model.get('originalWidth'),
                  'originalHeight' : model.get('originalHeight'),
                  'smallSource': model.get('smallUrl'),
                  'smallWidth': model.get('smallWidth'),
                  'smallHeight': model.get('smallHeight'),
                  'mediumSource': model.get('mediumUrl'),
                  'mediumWidth': model.get('mediumWidth'),
                  'mediumHeight': model.get('mediumHeight'),
                  'largeSource': model.get('largeUrl'),
                  'largeWidth': model.get('largeWidth'),
                  'largeHeight': model.get('largeHeight'),
                  'id': model.get('servicePhotoId'),
                  'title' : model.get('title'),
                  'confidentiality': that.confidentiality,
                  'categories': model.get('categories'),
                  'hashtags': model.get('hashtags')
               };
               if (model.has('place')) {
                  fbImage.place = model.get('place');
               }
               if (model.has('tags') && typeof model.get('tags').data != 'undefined') {
                  fbImage.tags = model.get('tags').data;
               }
               fbImages.push(fbImage);
            });

            // POST images to database
            $.ajax({
               url : Routing.generate('upload_load_external_photos'),
               type: 'POST',
               data: { 'images': fbImages, 'source': 'facebook' },
               success: function() {
                  Upload.Common.showUploadInProgressModal();
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