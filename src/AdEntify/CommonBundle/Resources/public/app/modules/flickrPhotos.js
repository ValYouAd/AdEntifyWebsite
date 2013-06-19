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
   "modules/externalServicePhotos"
], function(app, bootstrap, ExternalServicePhotos) {

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

      initialize: function() {
         this.loadPhotos();

         app.on('externalServicePhoto:submitPhotos', this.submitPhotos);
         app.trigger('domchange:title', $.t('flickr.photosPageTitle'));

         this.listenTo(this.options.photos, {
            "add": this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-list", new ExternalServicePhotos.Views.Item({
               model: photo
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
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

      submitPhotos: function(e) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         // Get checked images
         checkedImages = $('.checked img');
         if (checkedImages.length > 0) {
            var images = [];
            _.each(checkedImages, function(image, index) {
               images[index] = {
                  'originalSource' : $(image).data('original-url'),
                  'originalWidth' : $(image).data('original-width'),
                  'originalHeight' : $(image).data('original-height'),
                  'title' : $(image).data('title'),
                  'id': $(image).data('service-photo-id'),
                  'confidentiality': options.confidentiality
               };
            });

            // Get larger images size
            var deferreds = [];
            _.each(images, function(image) {
               if (!image.source) {
                  deferreds.push($.ajax({
                     url: 'http://api.flickr.com/services/rest/?method=flickr.photos.getSizes&format=json&api_key=370e2e2f28c0ca81fd6a5a336a6e2c89'
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
                              } else if (width == 150) {
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
                  data: { 'images': images, 'source': 'flickr' },
                  success: function(response) {
                     if (!response.error) {
                        // redirect to untagged tab
                        Backbone.history.navigate($.t('routing.my/photos/untagged/'), true);
                     } else {
                        // TODO error
                     }
                  },
                  error: function(e) {
                     // TODO error
                  }
               })
            });
         }
      }
   });

   return FlickrPhotos;
});