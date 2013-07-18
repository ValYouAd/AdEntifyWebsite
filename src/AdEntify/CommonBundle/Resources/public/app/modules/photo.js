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
   "modules/common",
   "modules/comment",
   "pinterest"
], function(app, Tag, Common, Comment) {

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
         this.set('profileLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('owner')['id'] }));
         if (this.has('owner'))
            this.set('fullname', this.get('owner')['firstname'] + ' ' + this.get('owner')['lastname']);
      }
   });

   Photo.Views.Item = Backbone.View.extend({
      template: "photo/item",
      tagName: "div",

      initialize: function() {
         this.model = this.options.photo;

         this.listenTo(this.options.photo, {
            "error": function() {
               app.useLayout().setView('#content', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('photo.errorLoading'),
                  showClose: true
               }), true).render();
            },
            "sync": function(model, resp) {
               if (resp == null) {
                  app.useLayout().setView('#content', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('photo.noPhoto'),
                     showClose: true
                  }), true).render();
               } else {
                  app.trigger('domchange:title', this.options.photo.get('caption'));
               }
               this.render();
            }
         });

         // Comments
         app.useLayout().setView('.comments', new Comment.Views.List({
            comments: this.options.comments,
            photoId: this.options.photoId
         }));
      },

      serialize: function() {
         return {
            model: this.options.photo,
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
               } else if (tag.type == 'product') {
                  that.insertView(".tags", new Tag.Views.ProductItem({
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
         app.useLayout().getView('.comments').render();
      },

      like: function() {
         app.photoActions().like(this.model);
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

      favorite: function() {
         app.photoActions().favorite(this.model);
      },

      events: {
         "click .like-button": "like",
         "click .adentify-pastille": "showTags",
         "click .favorite-button": "favorite"
      }
   });

   return Photo;
});