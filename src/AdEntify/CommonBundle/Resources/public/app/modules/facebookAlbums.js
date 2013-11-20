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
                  that.set("picture", response.source);
               }
            });
         }
         if (!this.has('url'))
            this.set('url', app.beginUrl + app.root + 'facebook/albums/' + this.get("id") + '/photos/');
      }
   });

   FacebookAlbums.Collection = Backbone.Collection.extend({
      model: FacebookAlbums.Model,
      cache: true
   });

   FacebookAlbums.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/albumList",
      confidentiality: 'public',

      serialize: function() {
         return {
            rootUrl: app.beginUrl + app.root
         };
      },

      initialize: function() {
         var that = this;
         if (app.fb.isConnected()) {
            this.loadAlbums();
         } else {
            this.listenTo(app, 'global:facebook:connected', function() {
               that.loadAlbums();
            });
         }
         this.albums = this.options.albums;
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
         if (this.options.albums.length > 0) {
            $('#loading-albums').hide();
         }
         var that = this;
         $(this.el).find('.photos-confidentiality').change(function() {
            if ($(this).val())
               that.confidentiality = $(this).val();
         });
      },

      loadAlbums: function() {
         var that = this;
         app.fb.loadAlbums(function(response) {
            if (!response.error) {
               if (response.length > 0) {
                  that.options.albums.add(response);
                  that.render();
               } else {
                  app.useLayout().setView('#content', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('facebook.noAlbums'),
                     showClose: true
                  }), true).render();
                  $('#loading-albums').hide();
               }
            } else {
               app.useLayout().setView('#content', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('facebook.errorLoadingAlbums'),
                  showClose: true
               }), true).render();
               $('#loading-albums').hide();
            }
         });
      },

      submitAlbums: function() {
         var fbImages = [];
         var stack = [];
         var counterView = this.getView('.upload-counter-view');
         var that = this;
         _.each(counterView.checkedAlbums, function(album) {
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
                        'confidentiality': that.confidentiality,
                        'categories': album.get('categories')
                     };
                     if (model.has('place')) {
                        fbImage.place = model.get('place');
                     }
                     if (model.has('tags') && typeof model.get('tags').data != 'undefined') {
                        fbImage.tags = model.get('tags').data;
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
                     ExternalServicePhotos.Common.showUploadInProgressModal();
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

   return FacebookAlbums;
});