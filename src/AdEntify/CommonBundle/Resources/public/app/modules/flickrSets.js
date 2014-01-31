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
   "modules/flickrPhotos",
   'modules/upload',
   "hmacsha1"
], function(app, ExternalServicePhotos, FlickrPhotos, Upload) {

   var FlickrSets = app.module();
   var error = '';
   var loaded = false;
   var flickrOAuthInfos = null;

   FlickrSets.Model = Backbone.Model.extend({
      defaults: {
         confidentiality: 'public',
         categories: []
      },

      initialize: function() {
         this.listenTo(this, {
            'change': this.setup,
            'add': this.setup
         });
      },

      setup: function() {
         if (flickrOAuthInfos && this.get('id') && !this.has('picture')) {
            var that = this;
            $.ajax({
               url: 'https://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&photoset_id=' + that.get("id") + '&format=json&api_key=' + flickrClientId
                  + '&user_id='+ flickrOAuthInfos.service_user_id + '&per_page=1&extras=url_m&jsoncallback=?',
               dataType: 'jsonp',
               success: function(response) {
                  if (response.photoset.photo.length > 0) {
                     that.set('picture', response.photoset.photo[0].url_m);
                  }
               },
               error : function() {
               }
            });
         }
         this.set('url', app.beginUrl + app.root + $.t('routing.flickr/sets/id/photos/', { id: this.get("id") }));
      }
   });

   FlickrSets.Collection = Backbone.Collection.extend({
      model: FlickrSets.Model
   });

   FlickrSets.Views.List = Backbone.View.extend({
      template: 'externalServicePhotos/albumList',
      confidentiality: 'public',

      serialize: function() {
         return {
            rootUrl: app.beginUrl + app.root,
            serviceName: 'Flickr',
            loweredServiceName: 'flickr'
         };
      },

      initialize: function() {
         var that = this;

         // Get flickr token
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
                        flickrOAuthInfos = _.find(data, function(service) {
                           if (service.service_name == 'Flickr') {
                              return true;
                           } else { return false; }
                        });
                        // Connect to Flickr API
                        if (flickrOAuthInfos) {
                           $.ajax({
                              url: 'https://api.flickr.com/services/rest/?method=flickr.photosets.getList&format=json&api_key=' + flickrClientId
                                 + '&user_id='+ flickrOAuthInfos.service_user_id + '&jsoncallback=?',
                              dataType: 'jsonp',
                              success: function(response) {
                                 for (var i= 0, l=response.photosets.photoset.length; i<l; i++) {
                                    var model = new FlickrSets.Model();
                                    var album = response.photosets.photoset[i];
                                    model.set('name', album.title._content);
                                    model.set('id', album.id);
                                    model.set('description', album.description._content);
                                    model.setup();
                                    that.options.sets.add(model);
                                 }
                                 loaded = true;
                              },
                              error : function() {
                                 // TODO : error
                                 console.log('impossible de récupérer les albums Flickr');
                              }
                           });
                        } else {
                           Backbone.history.navigate($.t('routing.upload/'), { trigger: true });
                           // TODO error : pas de token flickr
                        }
                     }
                  },
                  error: function() {
                     Backbone.history.navigate($.t('routing.upload/'), { trigger: true });
                  }
               });
            }
         });

         this.listenTo(this.options.sets, {
            "add": this.render
         });

         app.trigger('domchange:title', $.t('flickr.albumsPageTitle'));
      },

      beforeRender: function() {
         this.options.sets.each(function(album) {
            this.insertView("#albums-list", new ExternalServicePhotos.Views.AlbumItem({
               model: album,
               categories: this.options.categories
            }));
         }, this);

         if (!this.getView('.upload-counter-view')) {
            var counterView = new ExternalServicePhotos.Views.Counter({
               counterType: 'album'
            });
            var that = this;
            counterView.on('checkedAlbum', function(count) {
               var submitButton = $(that.el).find('.submit-albums-button');
               if (count > 0) {
                  if ($(that.el).find('.submit-albums-button:visible').length == 0)
                     submitButton.fadeIn('fast');
               } else {
                  if ($(that.el).find('.submit-albums-button:hidden').length == 0)
                     submitButton.fadeOut('fast');
               }
            });
            this.setView('.upload-counter-view', counterView);
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.sets.length > 0) {
            $('#loading-albums').hide();
         }
         var that = this;
         $(this.el).find('.photos-confidentiality').change(function() {
            if ($(this).val())
               that.confidentiality = $(this).val();
         });
      },

      submitAlbums: function() {
         var flickrImages = [];
         var stack = [];
         var counterView = this.getView('.upload-counter-view');
         var that = this;
         _.each(counterView.checkedAlbums, function(album) {
            stack.push(1);
            app.fb.loadPhotos(album.get('id'), function(response) {
               stack.splice(0, 1);
               if (!response.error) {
                  _.each(response, function(photo) {
                     model = new FlickrPhotos.Model(photo);
                     flickrImage = {
                        'originalSource' : model.get('originalUrl'),
                        'originalWidth' : model.get('originalWidth'),
                        'originalHeight' : model.get('originalHeight'),
                        'id': model.get('servicePhotoId'),
                        'title' : model.get('name'),
                        'confidentiality': that.confidentiality,
                        'categories': album.get('categories')
                     };
                     flickrImages.push(flickrImage);
                  });
               }
            });
         });

         var albumLoaded = setInterval(function() {
            if (stack.length == 0) {
               // POST images to database
               $.ajax({
                  url : Routing.generate('upload_load_external_photos'),
                  type: 'POST',
                  data: { 'images': flickrImages, 'source': 'Flickr' },
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

               clearInterval(albumLoaded);
            }
         }, 1000);
      },

      events: {
         'click .submit-albums-button': 'submitAlbums'
      }
   });

   return FlickrSets;
});