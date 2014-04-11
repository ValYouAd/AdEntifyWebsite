/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "bootstrap",
   "modules/externalServicePhotos",
   'modules/upload'
], function(app, bootstrap, ExternalServicePhotos, Upload) {

   var FlickrPhotos = app.module();
   var error = '';

   FlickrPhotos.Model = Backbone.Model.extend({
      smallUrl: null,
      originalUrl: null,
      originalWidth: null,
      originalHeight: null,
      servicePhotoId: null,
      title: null,

      initialize: function() {
         this.set('thumbUrl', this.get('url_s'));
         this.set('servicePhotoId', this.get('id'));
         if (this.has('url_o'))
            this.set('originalUrl', this.get('url_o'));
      }
   });

   FlickrPhotos.Collection = Backbone.Collection.extend({
      model: FlickrPhotos.Model,
      cache: true
   });

   FlickrPhotos.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/list",
      confidentiality: 'public',
      albumName: '',

      serialize: function() {
         return {
            album: this.albumName,
            showBackTo: true,
            backToText: $.t('externalServicePhotos.backToAlbums'),
            backToLink: app.beginUrl + app.root + $.t('routing.flickr/sets/'),
            serviceName: 'Flickr',
            loweredServiceName: 'flickr'
         };
      },

      initialize: function() {
         this.loadPhotos();

         app.trigger('domchange:title', $.t('flickr.photosPageTitle'));

         this.listenTo(this.options.photos, {
            "add": this.render
         });
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

         // Get flickr set photos
         $.ajax({
            url: Routing.generate('flickr_sets_photos', { 'id': this.options.albumId }),
            dataType: 'json',
            success: function(response) {
               var photos = [];
               for (var i= 0, l=response.length; i<l; i++) {
                  photos[i] = response[i];
               }
               that.options.photos.add(photos);
            },
            error : function(e) {
               // TODO : error
               console.log('impossible de récupérer les photos flickr');
            }
         });
      },

      submitPhotos: function(options) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         // Get checked images
         // Get checked images
         var counterView = this.getView('.upload-counter-view');
         var that = this;
         if (counterView.checkedPhotos.length > 0) {
            var images = [];
            _.each(counterView.checkedPhotos, function(model) {
               images.push({
                  'originalSource' : model.get('originalUrl'),
                  'originalWidth' : model.get('originalWidth'),
                  'originalHeight' : model.get('originalHeight'),
                  'title' : model.get('title'),
                  'id': model.get('servicePhotoId'),
                  'confidentiality': that.confidentiality,
                  'categories': model.get('categories'),
                  'hashtags': model.get('hashtags')
               });
            });

            // Get larger images size
            var deferreds = [];
            _.each(images, function(image) {
               if (!image.source) {
                  deferreds.push($.ajax({
                     url: 'https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&format=json&api_key=' + flickrClientId
                        + '&photo_id='+ image.id + '&jsoncallback=?',
                     dataType: 'jsonp',
                     success: function(response) {
                        if (response.sizes.size.length) {
                           var largerSize = null;
                           _.each(response.sizes.size, function(size) {
                              width = parseInt(size.width);
                              if (width == 1024) {
                                 image.largeSource = size.source;
                                 image.largeWidth = size.width;
                                 image.largeHeight = size.height;
                              } else if(width == 320) {
                                 image.mediumSource = size.source;
                                 image.mediumWidth = size.width;
                                 image.mediumHeight = size.height;
                              } else if (width == 100) {
                                 image.smallSource = size.source;
                                 image.smallWidth = size.width;
                                 image.smallHeight = size.height;
                              }
                              if (!largerSize) {
                                 largerSize = size;
                              } else {
                                 if (parseInt(largerSize.width) < width) {
                                    largerSize = size;
                                 }
                              }
                           });
                           image.originalSource = largerSize.source;
                           image.originalWidth = largerSize.width;
                           image.originalHeight = largerSize.height;
                        }
                     },
                     error : function() {
                        // TODO : error
                     }
                  }));
               }
            });

            $.when.apply(null, deferreds).done(function() {
               // POST images to database
               $.ajax({
                  url : Routing.generate('upload_load_external_photos'),
                  type: 'POST',
                  data: { 'images': images, 'source': 'Flickr' },
                  success: function() {
                     Upload.Common.showUploadInProgressModal();
                  },
                  error: function() {
                     // Hide loader
                     $('#loading-upload').fadeOut('fast', function() {
                        $('#photos-container').fadeIn('fast');
                     });
                     app.trigger('externalPhotos:uploadingError');
                  }
               })
            });
         }
      },

      events: {
         'click .submit-photos-button': 'submitPhotos'
      }
   });

   return FlickrPhotos;
});