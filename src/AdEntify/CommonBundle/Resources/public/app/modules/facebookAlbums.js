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
   "facebook",
   "select2"
], function(app, ExternalServicePhotos) {

   var FacebookAlbums = app.module();
   var error = '';

   FacebookAlbums.Model = Backbone.Model.extend({
      picture: null,

      initialize: function() {
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
         FB.api('/me/albums', function(response) {
            if (!response || response.error) {
               error = response.error;
            } else {
               var albums = [];
               for (var i=0, l=response.data.length; i<l; i++) {
                  albums[i] = response.data[i];
               }
               that.options.albums.add(albums);
            }
         });
      },

      initialize: function() {
         var that = this;
         if (app.fb.isConnected()) {
            this.loadAlbums();
         } else {
            app.on('global:facebook:connected', function() {
               that.loadAlbums();
            });
         }
         this.listenTo(this.options.albums, {
            "sync": this.render
         });
         app.trigger('domchange:title', $.t('facebook.albumsPageTitle'));
         this.categories = this.options.categories;
         this.listenTo(this.options.categories, {
            "sync": this.render
         });
      }
   });

   return FacebookAlbums;
});