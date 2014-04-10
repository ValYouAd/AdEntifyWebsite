define([
   "app"
], function(app) {

   var Facebook = app.module();

   Facebook.Model = Backbone.Model.extend({
      defaults: {
         userId : 0,
         accessToken: '',
         status: 'unknown',
         friends: null
      },

      setFacebookResponse: function(response) {
         if (response.authResponse && response.status === 'connected') {
            this.set('userId', response.authResponse.userID);
            this.set('accessToken', response.authResponse.accessToken);
         }
         this.set('status', response.status);
      },

      /*notLoggedIn: function() {
         var that = this;
         $('#loading-authent').hide();
         $('#fb-logout').hide();
         $('#fb-login').show();
         $('#twitter-authent').show();
         $('#fb-login').click(function() {
            that.login();
         });
      },*/

      login: function() {
         var that = this;
         FB.login(function(response) {
            if (response.authResponse) {
               that.connected(response);
            } else {
               // cancelled
            }
         }, { scope: facebookPermissions });
      },

      logout: function() {
         window.location.href = Routing.generate('fos_user_security_logout');
      },

      connected: function(response) {
         this.setFacebookResponse(response);
         app.trigger('global:facebook:connected');
      },

      isConnected: function() {
         return this.get('status') === 'connected' ? true : false;
      },

      loadFriends: function(options) {
         options || (options = {});
         var that = this;
         if (!this.get('friends')) {
            FB.api('/me/friends?fields=name,first_name,last_name,gender', function(response) {
               if (response && !response.error) {
                  that.set('friends', response.data);
                  if (options.success)
                     options.success(response.data);
               } else {
                  if (options.error)
                     options.error();
               }
            });
         } else {
            if (options.success)
               options.success(this.get('friends'));
         }
      },

      createBrandTagStory: function(brand, photo) {
         FB.api(
            'me/objects/adentifywww:brand',
            'post',
            {
               object: {
                  app_id: facebookAppId,
                  type: "adentifywww:brand",
                  title: brand.name,
                  url: app.beginUrl + app.root + $.t('routing.brands/')
               }
            }, function(response) {
               if (typeof response.error === "undefined") {
                  FB.api(
                     'me/adentifywww:tag',
                     'post',
                     {
                        image : photo.get('large_url'),
                        url: app.beginUrl + app.root + $.t('routing.photo/id/', { id: photo.get('id')}),
                        brand: response.id
                     },
                     function(response) {
                     }
                  );
               }
            }
         );
      },

      createVenueStory: function(venue, photo) {
         FB.api(
            'me/objects/adentifywww:venue',
            'post',
            {
               object: {
                  app_id: facebookAppId,
                  type: "adentifywww:venue",
                  title: venue.get('name'),
                  description: venue.get('description'),
                  url: app.beginUrl + app.root + $.t('routing.photo/id/', { id: photo.get('id')}),
                  data: {
                     location: {
                        latitude: venue.get('lat'),
                        longitude: venue.get('lng'),
                        altitude: '0'
                     }
                  }
               }
            }, function(response) {
               if (typeof response.error === "undefined") {
                  FB.api(
                     'me/adentifywww:tag',
                     'post',
                     {
                        image : photo.get('large_url'),
                        url: app.beginUrl + app.root + $.t('routing.photo/id/', { id: photo.get('id')}),
                        venue: response.id
                     },
                     function(response) {
                     }
                  );
               }
            }
         );
      },

      createPersonStory: function(person, photo) {
         FB.api(
            'me/objects/profile',
            'post',
            {
               object: {
                  app_id: facebookAppId,
                  type: "profile",
                  title: person.get('firstname') + ' ' + person.get('lastname'),
                  url: app.beginUrl + app.root + $.t('routing.profile/id/', { id: person.get('id') }),
                  data: {
                     first_name: person.get('firstname'),
                     last_name: person.get('lastname'),
                     gender: person.get('gender'),
                     profile_id: person.get('facebookId')
                  }
               }
            }, function(response) {
               if (typeof response.error === "undefined") {
                  FB.api(
                     'me/adentifywww:tag',
                     'post',
                     {
                        image : photo.get('large_url'),
                        url: app.beginUrl + app.root + $.t('routing.photo/id/', { id: photo.get('id')}),
                        profile: response.id
                     },
                     function(response) {
                     }
                  );
               }
            }
         );
      },

      loadPhotos: function(albumId, callback) {
         FB.api(albumId + '/photos?limit=200', function(response) {
            if (!response || response.error) {
               callback({
                  error: response.error
               });
            } else {
               var photos = [];
               for (var i=0, l=response.data.length; i<l; i++) {
                  photos[i] = response.data[i];
               }
               if (typeof callback !== 'undefined')
                  callback(photos);
            }
         });
      },

      loadAlbums: function(callback) {
         var albums = [];
         var error = null;
         var deferreds = [];

         // Get "photos of you" albums
         deferreds.push(new $.Deferred());
         this.loadUserPhotos(function(response) {
            if (!response.error && response.length > 0) {
               albums.unshift({
                  'name': $.t('facebook.photosOfYou'),
                  'picture': response[0].source,
                  'url': app.beginUrl + app.root + 'facebook/albums/photos-of-you/photos/',
                  'customAlbum': true
               });
            } else {
               error = response.error;
            }
            deferreds.pop().resolve();
         });

         // Get user albums
         deferreds.push(new $.Deferred());
         FB.api('/me/albums?fields=from,name,cover_photo,link,privacy,count', function(response) {
            if (!response || response.error) {
               error = response.error;
            } else {
               for (var i=0, l=response.data.length; i<l; i++) {
                  if (response.data[i].count > 0) {
                     albums.push(response.data[i]);
                  }
               }
            }
            deferreds.pop().resolve();
         });

         $.when.apply(null, deferreds).done(function() {
            if (!error) {
               callback(albums);
            } else {
               callback({
                  error: error
               });
            }
         });
      },

      loadAlbumName: function(albumId, callback) {
         FB.api('/'+ albumId +'?fields=name', function(response) {
            if (!response || response.error) {
               callback({
                  error: response.error
               });
            } else {
               callback(response.name);
            }
         });
      },

      loadUserPhotos: function(callback) {
         var that = this;
         FB.api('/me/photos?limit=200', function(response) {
            if (!response || response.error) {
               callback({
                  error: response.error
               });
            } else {
               if (typeof that.userPhotos === 'undefined') {
                  var photos = [];
                  for (var i=0, l=response.data.length; i<l; i++) {
                     photos[i] = response.data[i];
                  }
                  that.userPhotos = photos;
               }

               if (typeof callback !== 'undefined')
                  callback(that.userPhotos);
            }
         });
      }
   });

   return Facebook;
});