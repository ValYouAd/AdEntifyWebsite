/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/photos',
   'modules/externalServicePhotos',
   'modules/common'
], function(app, Photos, ExternalServicePhotos, Common) {

   var Upload = app.module();

   Upload.Views.Content = Backbone.View.extend({
      template: "upload/content",

      serialize: function() {
         return {
            model: {
               appRoot: app.rootUrl,
               instagramClientId: instagramClientId,
               localUpload: app.beginUrl + app.root + $.t('routing.upload/local/')
            }
         }
      },

      events: {
         "click #flickrUploadButton": "flickrUpload"
      },

      flickrUpload: function() {
         window.location.href = Routing.generate('flickr_request_token');
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         app.trigger('domchange:title', $.t('upload.pageTitle'));
      }
   });

   Upload.Views.LocalUpload = Backbone.View.extend({
      template: 'upload/localUpload',

      initialize: function() {
         this.photos = new ExternalServicePhotos.Collection();
         this.listenTo(app, 'externalServicePhoto:submitPhotos', this.submitPhotos);
      },

      beforeRender: function() {
         this.insertView('.upload-photos-container', new Upload.Views.PhotosList({
            photos: this.photos,
            categories: this.options.categories
         }));
      },

      afterRender: function() {
         var that = this;
         $('#fileupload').attr("data-url", Routing.generate('upload_local_photo'));
         $('#fileupload').fileupload({
            dataType: 'json',
            start: function() {
               $('#loading-photos').stop().fadeIn();
            },
            done: function (e, data) {
               $('#loading-photos').stop().fadeOut();
               if (data.result) {
                  var photo = new ExternalServicePhotos.Model();
                  photo.set('thumbUrl', data.result['small']['filename']);
                  photo.set('smallSource', data.result['small']['filename']);
                  photo.set('smallWidth', data.result['small']['width']);
                  photo.set('smallHeight', data.result['small']['height']);
                  if ('medium' in data.result) {
                     photo.set('mediumSource', data.result['medium']['filename']);
                     photo.set('mediumWidth', data.result['medium']['width']);
                     photo.set('mediumHeight', data.result['medium']['height']);
                  }
                  if ('large' in data.result) {
                     photo.set('largeSource', data.result['large']['filename']);
                     photo.set('largeWidth', data.result['large']['width']);
                     photo.set('largeHeight', data.result['large']['height']);
                  }
                  photo.set('originalSource', data.result['original']['filename']);
                  photo.set('originalWidth', data.result['original']['width']);
                  photo.set('originalHeight', data.result['original']['height']);
                  that.photos.add(photo);
                  app.trigger('externalServicePhotos:selectPhoto', photo);
               } else {
                  app.useLayout().setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('upload.errorProductImageUpload'),
                     showClose: true
                  })).render();
               }
            }
         });
         $(this.el).i18n();
      },

      submitPhotos: function(options) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         if (this.photos.length > 0) {
            this.photos.each(function(image) {
               image.set('confidentiality', options.confidentiality);
               image.set('categories', options.categories);
            });

            // POST images to database
            $.ajax({
               url : Routing.generate('upload_load_external_photos'),
               type: 'POST',
               data: { 'images': this.photos.toJSON(), 'source': 'local' },
               success: function() {
                  app.trigger('externalPhotos:uploadingInProgress');
               },
               error: function() {
                  // Hide loader
                  $('#loading-upload').fadeOut('fast', function() {
                     $('#photos-container').fadeIn('fast');
                  });
                  app.trigger('externalPhotos:uploadingError');
               }
            });
         }
      }
   });

   Upload.Views.PhotosList = Backbone.View.extend({
      template: 'externalServicePhotos/list',
      albumName: '',

      serialize: function() {
         return {
            album: this.albumName,
            title: 'localPhotosUploaded'
         };
      },

      initialize: function() {
         this.listenTo(this.options.photos, 'add', this.render);
         this.categories = this.options.categories;
         this.listenTo(this.options.categories, {
            'sync': this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView('#photos-list', new ExternalServicePhotos.Views.Item({
               model: photo,
               enableCheck: false,
               categories: this.categories
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
         $('#loading-photos').hide();
         $(this.el).i18n();
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
      }
   });

   return Upload;
});