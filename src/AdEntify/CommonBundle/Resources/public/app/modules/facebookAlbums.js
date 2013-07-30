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
   "modules/facebookPhotos",
   "modules/common",
   "facebook",
   "select2"
], function(app, ExternalServicePhotos, FacebookPhotos, Common) {

   var FacebookAlbums = app.module();

   FacebookAlbums.Model = Backbone.Model.extend({
      picture: null,
      defaults: {
         confidentiality: 'private',
         categories: []
      },

      initialize: function() {
         this.listenTo(this, {
            'change': this.setup,
            'add': this.setup
         });
      },

      setup: function() {
         var that = this;
         if (this.has('cover_photo')) {
            FB.api(this.get('cover_photo'), function(response) {
               if (response && !response.error) {
                  that.set("picture", response.picture);
               }
            });
         }
         this.set('url', 'facebook/albums/' + this.get("id") + '/photos/');
      }
   });

   FacebookAlbums.Collection = Backbone.Collection.extend({
      model: FacebookAlbums.Model,
      cache: true
   });

   FacebookAlbums.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/albumList",

      initialize: function() {
         var that = this;
         if (app.fb.isConnected()) {
            this.loadAlbums();
         } else {
            this.listenTo(app, 'global:facebook:connected', function() {
               that.loadAlbums();
            });
         }
         this.listenTo(app, 'externalServicePhoto:submitAlbums', this.submitAlbums, this);
         this.listenTo(this.options.albums, {
            "sync": this.render
         });
         app.trigger('domchange:title', $.t('facebook.albumsPageTitle'));
         this.categories = this.options.categories;
         this.listenTo(this.options.categories, {
            "sync": this.render
         });
      },

      beforeRender: function() {
         this.options.albums.each(function(album) {
            this.insertView("#albums-list", new ExternalServicePhotos.Views.AlbumItem({
               model: album,
               categories: this.categories
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.albums.length > 0) {
            $('#loading-albums').hide();
         }
      },

      loadAlbums: function() {
         var that = this;
         FB.api('/me/albums?fields=from,name,cover_photo,link,privacy', function(response) {
            if (!response || response.error) {
               app.useLayout().setView('#content', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('facebook.errorLoadingAlbums'),
                  showClose: true
               }), true).render();
            } else {
               if (response.data.length > 0) {
                  var albums = [];
                  for (var i=0, l=response.data.length; i<l; i++) {
                     albums[i] = response.data[i];
                  }
                  that.options.albums.add(albums);
               } else {
                  app.useLayout().setView('#content', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('facebook.noAlbums'),
                     showClose: true
                  }), true).render();
                  $('#loading-albums').hide();
               }
            }
         });
      },

      submitAlbums: function(options) {
         var fbImages = [];
         var stack = [];
         _.each(options.albums, function(album) {
            stack.push(1);
            app.fb.loadPhotos(album.get('id'), function(response) {
               stack.splice(0, 1);
               if (!response.error) {
                  _.each(response, function(photo) {
                     model = new FacebookPhotos.Model(photo);
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
                        'confidentiality': album.get('confidentiality'),
                        'categories': album.get('categories')
                     };
                     if (model.has('place')) {
                        fbImage.place = model.get('place');
                     }
                     fbImages.push(fbImage);
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
                  data: { 'images': fbImages, 'source': 'facebook' },
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

   return FacebookAlbums;
});