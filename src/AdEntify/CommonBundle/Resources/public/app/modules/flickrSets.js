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
   "hmacsha1"
], function(app, ExternalServicePhotos, FlickrPhotos) {

   var FlickrSets = app.module();
   var error = '';
   var loaded = false;

   FlickrSets.Model = Backbone.Model.extend({
      defaults: {
         confidentiality: 'private',
         categories: []
      },

      initialize: function() {
         this.set('title', this.attributes.title._content);
         this.set('description', this.attributes.description._content);
         this.set('id', this.attributes.id);
         this.set('url', 'flickr/sets/' + this.get("id") + '/photos/');
      }
   });

   FlickrSets.Collection = Backbone.Collection.extend({
      model: FlickrSets.Model,
      cache: true
   });

   FlickrSets.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/albumList",

      initialize: function() {
         var that = this;

         this.listenTo(app, 'externalServicePhoto:submitAlbums', this.submitAlbums);

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
                        var flickrOAuthInfos = _.find(data, function(service) {
                           if (service.service_name == 'flickr') {
                              return true;
                           } else { return false; }
                        });
                        // Connect to Flickr API
                        if (flickrOAuthInfos) {
                           $.ajax({
                              url: 'http://api.flickr.com/services/rest/?method=flickr.photosets.getList&format=json&api_key=370e2e2f28c0ca81fd6a5a336a6e2c89'
                                 + '&user_id='+ flickrOAuthInfos.service_user_id + '&jsoncallback=?',
                              dataType: 'jsonp',
                              success: function(response) {
                                 var sets = [];
                                 for (var i= 0, l=response.photosets.photoset.length; i<l; i++) {
                                    sets[i] = response.photosets.photoset[i];
                                 }
                                 that.options.sets.add(sets);
                                 loaded = true;
                              },
                              error : function() {
                                 // TODO : error
                                 console.log('impossible de récupérer les albums Flickr');
                              }
                           });
                        } else {
                           // TODO : Redirect to error page
                        }
                     }
                  },
                  error: function() {
                     error = 'Can\'t get instagram token.';
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
            this.insertView("#sets-list", new ExternalServicePhotos.Views.AlbumItem({
               model: album
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (loaded) {
            $('#loading-sets').hide();
         }
      },

      submitAlbums: function(options) {
         var flickrImages = [];
         var stack = [];
         _.each(options.albums, function(album) {
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
                        'title' : model.get('title'),
                        'confidentiality': album.get('confidentiality'),
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
                  data: { 'images': flickrImages, 'source': 'flickr' },
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

               clearInterval(albumLoaded);
            }
         }, 1000);
      }
   });

   return FlickrSets;
});