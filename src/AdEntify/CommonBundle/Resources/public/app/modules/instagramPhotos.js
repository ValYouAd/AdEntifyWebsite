/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "bootstrap"
], function(app) {

   var InstagramPhotos = app.module();
   var error = '';

   InstagramPhotos.Model = Backbone.Model.extend({
      smallPicture: null,

      initialize: function() {
         var images = this.get('images');
         this.set('smallPicture', images['thumbnail']['url']);
      }
   });

   InstagramPhotos.Collection = Backbone.Collection.extend({
      model: InstagramPhotos.Model,
      cache: true
   });

   InstagramPhotos.Views.Item = Backbone.View.extend({
      template: "instagramPhotos/item",

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

   InstagramPhotos.Views.List = Backbone.View.extend({
      template: "instagramPhotos/list",

      initialize: function() {
         this.loadPhotos();

         this.listenTo(this.options.photos, {
            "add": this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-list", new InstagramPhotos.Views.Item({
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
                     var instagramOAuthInfos = _.first(data, function(service) {
                        if (service.service_name == 'instagram') {
                           return true;
                        } else { return false; }
                     });
                     // Connect to Instagram API
                     if (instagramOAuthInfos) {
                        $.ajax({
                           url: 'https://api.instagram.com/v1/users/' + instagramOAuthInfos[0].service_user_id + '/media/recent/?access_token='
                              + instagramOAuthInfos[0].service_access_token,
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
                     }
                  }
               },
               error: function() {
                  error = 'Can\'t get instagram token.';
               }
            });
         });

         /*
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
         });*/
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

   InstagramPhotos.Views.ErrorNoRights = Backbone.View.extend({
      template: "facebookPhotos/errors/noRights"
   });

   return InstagramPhotos;
});