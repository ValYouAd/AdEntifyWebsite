/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "facebook"
], function(app) {

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
      }
   });

   FacebookAlbums.Collection = Backbone.Collection.extend({
      model: FacebookAlbums.Model,
      cache: true
   });

   FacebookAlbums.Views.Item = Backbone.View.extend({
      template: "facebookAlbums/item",

      tagName: "li class='span2'",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   FacebookAlbums.Views.List = Backbone.View.extend({
      template: "facebookAlbums/list",

      beforeRender: function() {
         this.options.albums.each(function(album) {
            this.insertView("#albums-list", new FacebookAlbums.Views.Item({
               model: album
            }));
         }, this);
      },

      afterRender: function() {
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
            "add": this.render
         });
         app.trigger('domchange:title', $.t('facebook.albumsPageTitle'));
      }
   });

   return FacebookAlbums;
});