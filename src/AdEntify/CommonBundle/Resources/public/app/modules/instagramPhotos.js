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

   var InstagramPhotos = app.module();
   var error = '';

   InstagramPhotos.Model = Backbone.Model.extend({
      thumbUrl: null,

      initialize: function() {
         var images = this.get('images');
         this.set('thumbUrl', images['low_resolution']['url']);
         // Get larger image
         this.set('originalUrl', images['standard_resolution']['url']);
         this.set('originalWidth', images['standard_resolution']['width']);
         this.set('originalHeight', images['standard_resolution']['height']);
         this.set('servicePhotoId', this.get('id'));
         if (this.has('caption'))
            this.set('title', this.get('caption')['text']);
      }
   });

   InstagramPhotos.Collection = Backbone.Collection.extend({
      model: InstagramPhotos.Model,
      cache: true
   });

   InstagramPhotos.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/list",

      serialize: function() {
         return { album: null }
      },

      initialize: function() {
         this.loadPhotos();

         this.listenTo(app, 'externalServicePhoto:submitPhotos', this.submitPhotos);
         app.trigger('domchange:title', $.t('instagram.pageTitle'));

         this.listenTo(this.options.photos, {
            'sync': this.render
         });

         this.photos = this.options.photos;
         this.categories = this.options.categories;
         this.listenTo(this.options.categories, {
            'sync': this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-list", new ExternalServicePhotos.Views.Item({
               model: photo,
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
         $(this.el).i18n();
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
      },

      loadPhotos: function() {
         var that = this;

         // Get instagram token
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_get_oauthuserinfos'),
                  headers : {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  success: function(data) {
                     if (!data || data.error) {
                        error = data.error;
                     } else {
                        var instagramOAuthInfos = _.find(data, function(service) {
                           if (service.service_name == 'instagram') {
                              return true;
                           } else { return false; }
                        });
                        // Connect to Instagram API
                        if (instagramOAuthInfos) {
                           $.ajax({
                              url: 'https://api.instagram.com/v1/users/' + instagramOAuthInfos.service_user_id + '/media/recent/?access_token='
                                 + instagramOAuthInfos.service_access_token,
                              dataType: 'jsonp',
                              success: function(response) {
                                 var photos = [];
                                 for (var i= 0, l=response.data.length; i<l; i++) {
                                    photos[i] = response.data[i];
                                 }
                                 that.options.photos.add(photos);
                                 that.options.photos.trigger('sync');
                              },
                              error : function() {
                                 console.log('impossible de récupérer les photos instagram');
                              }
                           })
                        } else {
                           // TODO error : pas de token instagram
                        }
                     }
                  },
                  error: function() {
                     error = 'Can\'t get instagram token.';
                  }
               });
            }
         });
      },

      submitPhotos: function(options) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         // Get checked images
         checkedImages = $('.checked img');
         if (checkedImages.length > 0) {
            var images = [];
            var that = this;
            _.each(checkedImages, function(image, index) {
               instagramImage = {
                  'originalSource' : $(image).data('original-url'),
                  'originalWidth' : $(image).data('original-width'),
                  'originalHeight' : $(image).data('original-height'),
                  'title' : $(image).data('title'),
                  'id': $(image).data('service-photo-id'),
                  'confidentiality': options.confidentiality,
                  'categories': options.categories
               };
               photoModel = that.photos.get(instagramImage.id);
               if (photoModel.has('location')) {
                  instagramImage.location = photoModel.get('location');
               }
               // Tags
               if (photoModel.has('users_in_photo') && photoModel.get('users_in_photo').length > 0) {
                  instagramImage.tags = [];
                  _.each(photoModel.get('users_in_photo'), function(tag) {
                     instagramImage.tags.push({
                        'x': tag.position.x * 100,
                        'y': tag.position.y * 100,
                        'username': tag.user.username,
                        'name': tag.user.full_name,
                        'id': tag.user.id,
                        'profilePicture': tag.user.profile_picture
                     });
                  });
               }
               // Hashtags
               if (photoModel.has('tags') && photoModel.get('tags').length > 0) {
                  instagramImage.hashtags = photoModel.get('tags');
               }
               images[index] = instagramImage;
            });

            // POST images to database
            $.ajax({
               url : Routing.generate('upload_load_external_photos'),
               type: 'POST',
               data: { 'images': images, 'source': 'instagram' },
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

   return InstagramPhotos;
});