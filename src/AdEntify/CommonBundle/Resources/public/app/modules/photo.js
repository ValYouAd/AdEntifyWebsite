/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/tag",
   "pinterest"
], function(app, Tag) {

   var Photo = app.module();
   var loaded = false;

   Photo.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_photo');
      },

      toJSON: function() {
         return { photo: {
            caption: this.get('caption'),
            _token: this.get('_token')
         }}
      },

      defaults: {
         fullSmallUrl: '',
         fullMediumUrl : '',
         fullLargeUrl : ''
      },

      initialize: function() {
         this.listenTo(this, {
            'change': this.updateUrl,
            'add': this.updateUrl
         });
      },

      updateUrl: function() {
         this.set('fullMediumUrl', app.rootUrl + 'uploads/photos/users/' + this.get('owner')['id'] + '/medium/' + this.get('medium_url'));
         this.set('fullLargeUrl', app.rootUrl + 'uploads/photos/users/' + this.get('owner')['id'] + '/large/' + this.get('large_url'));
         this.set('fullSmallUrl', app.rootUrl + 'uploads/photos/users/' + this.get('owner')['id'] + '/small/' + this.get('small_url'));
      }
   });

   Photo.Views.Item = Backbone.View.extend({
      template: "photo/item",

      tagName: "div",

      serialize: function() {
         return {
            model: this.model,
            pageUrl: window.location.href
         };
      },

      beforeRender: function() {
         if (this.model.has('tags') && this.model.get('tags').length > 0 && $(this.el).find('.tags').children().length == 0) {
            var that = this;
            _.each(this.model.get('tags'), function(tag) {
               if (tag.type == 'place') {
                  that.insertView(".tags", new Tag.Views.VenueItem({
                     model: new Tag.Model(tag)
                  }));
               } else if (tag.type == 'person') {
                  that.insertView(".tags", new Tag.Views.PersonItem({
                     model: new Tag.Model(tag)
                  }));
               } else {
                  that.insertView(".tags", new Tag.Views.Item({
                     model: new Tag.Model(tag)
                  }));
               }
            });
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         $('.full-photo img').load(function() {
            $('#photo').fadeIn();
         });
         FB.XFBML.parse();
      },

      like: function() {
         app.like().like(this.model);
         $likeCount = $(this.el).find('#like-count');
         $likeCount.html(this.model.get('likes_count') + 1);
      },

      showTags: function() {
         $tags = $(this.el).find('.tags');
         if ($tags.length > 0) {
            if ($tags.data('state') == 'hidden') {
               $tags.fadeIn('fast');
               $tags.data('state', 'visible');
            } else {
               $tags.fadeOut('fast');
               $tags.data('state', 'hidden');
            }
         }
      },

      events: {
         "click #like": "like",
         "click .adentify-pastille": "showTags"
      }
   });

   Photo.Views.Content = Backbone.View.extend({
      template: "photo/content",

      beforeRender: function() {
         this.insertView('#photo', new Photo.Views.Item({
            model: this.options.photo
         }));
      },

      afterRender: function() {
         if (loaded) {
            $('#loading-photo').fadeOut(200);
         }
      },

      initialize: function() {
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               that.options.photo.fetch({
                  success: function() {
                     loaded = true;
                     app.trigger('domchange:title', that.options.photo.get('caption'));
                  }
               });
            }
         });
         this.listenTo(this.options.photo, {
            'sync': this.render
         });
      }
   });

   return Photo;
});