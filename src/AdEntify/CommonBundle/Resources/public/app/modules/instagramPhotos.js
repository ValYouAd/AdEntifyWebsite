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
         this.set('thumbUrl', images['thumbnail']['url']);
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

      initialize: function() {
         this.loadPhotos();

         app.on('externalServicePhoto:submitPhotos', this.submitPhotos);

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
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
      },

      loadPhotos: function() {
         var that = this;

         // Get instagram token
         app.oauth.loadAccessToken(function() {
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
                  'id': $(image).data('service-photo-id')
               };
            });

            // POST images to database
            $.ajax({
               url : Routing.generate('upload_load_external_photos'),
               type: 'POST',
               data: { 'images': images, 'source': 'instagram' },
               success: function(response) {
                  if (!response.error) {
                     // redirect to untagged tab
                     Backbone.history.navigate('me/untagged/', true);
                  } else {
                     // TODO error
                  }
               },
               error: function(e) {
                  // TODO error
               }
            });
         }
      }
   });

   return InstagramPhotos;
});