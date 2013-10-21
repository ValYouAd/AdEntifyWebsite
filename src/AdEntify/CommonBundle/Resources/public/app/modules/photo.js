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
   "modules/category",
   "pinterest"
], function(app, Tag, Common, Comment, Category) {

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
         fullLargeUrl : '',
         caption: '',
         showTags: false,
         showLikes: false
      },

      initialize: function() {
         this.listenTo(this, {
            /*'change': this.setup,*/
            'sync': this.setup,
            'add': this.setup
         });
      },

      setup: function() {
         this.set('fullMediumUrl', this.get('medium_url'));
         this.set('fullLargeUrl', this.get('large_url'));
         this.set('fullSmallUrl', this.get('small_url'));
         this.set('profileLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('owner')['id'] }));
         this.set('link', app.beginUrl + app.root + $.t('routing.photo/id/', { id: this.get('id') }));
         if (this.has('owner'))
            this.set('fullname', this.get('owner')['firstname'] + ' ' + this.get('owner')['lastname']);
         if (!this.has('tagsConverted')) {
            this.set('tagsConverted', '');
            var tags = new Tag.Collection();
            if (this.has('tags') && this.get('tags').length > 0) {
               _.each(this.get('tags'), function(tag) {
                  tags.add(new Tag.Model(tag));
               });
            }
            this.set('tags', tags);
         }
         if (this.has('owner')) {
            var User = require('modules/user');
            this.set('ownerModel', new User.Model(this.get('owner')));
         }
      },

      getEmbed: function() {
         var query = [];
         if (this.get('showLikes'))
            query.push('show-likes=true');
         if (this.get('showTags'))
            query.push('show-tags=true');
         if (query.length > 0)
            query = '?' + query.join('&');

         return '&lt;iframe src="https://local.adentify.com/iframe/photo-' + this.get('id') + '.html' + query + '" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:' + this.get("large_width") + 'px; height:' + this.get("large_height") + 'px;" allowTransparency="true"&gt;&lt;/iframe&gt;';
      },

      changeTagsCount: function(value) {
         if (this.has('tags_count')) {
            var count = this.get('tags_count') + value;
            this.set('tags_count', count < 0 ? 0 : count);
         }
      },

      isOwner: function() {
         return this.has('owner') ? currentUserId == this.get('owner')['id'] : false;
      },

      delete: function() {
         // Check if currentUser is the owner
         if (this.isOwner()) {
            var that = this;
            this.destroy({
               url: Routing.generate('api_v1_delete_photo', { id : this.get('id') } ),
               success: function() {
                  that.trigger('delete:success');
               },
               error: function() {
                  that.trigger('delete:error');
               }
            });
         }
      }
   });

   Photo.Views.Item = Backbone.View.extend({
      template: "photo/item",
      tagName: 'div class="photo-item-container fadeOut"',

      initialize: function() {
         var that = this;
         this.model = this.options.photo;
         this.listenTo(this.options.photo, {
            'error': function() {
               app.useLayout().setView('#content', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('photo.errorLoading'),
                  showClose: true
               })).render();
            },
            'sync': function(model, resp) {
               if (resp == null) {
                  app.useLayout().setView('#content', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('photo.noPhoto'),
                     showClose: true
                  })).render();
               } else {
                  app.trigger('domchange:title', this.options.photo.get('caption'));
                  app.oauth.loadAccessToken({
                     success: function() {
                        $.ajax({
                           url: Routing.generate('api_v1_get_photo_waiting_tags', { id: that.model.get('id') }),
                           headers : {
                              "Authorization": app.oauth.getAuthorizationHeader()
                           },
                           success: function(data) {
                              if (data && data.length > 0) {
                                 that.unvalidateTags = [];
                                 for (var i = 0, len = data.length; i<len; i++) {
                                    that.options.photo.get('tags').push(data[i]);
                                 }
                                 that.render();
                              }
                           }
                        });
                     }
                  });
                  this.render();
                  this.$el.fadeIn();
               }
            }
         });

         this.listenTo(app, 'tag:removeTag', function(tag) {
            this.model.get('tags').remove(tag);
            this.render();
         });
      },

      serialize: function() {
         return {
            model: this.options.photo,
            pageUrl: window.location.href
         };
      },

      beforeRender: function() {
         if (this.model.has('tags') && this.model.get('tags').length > 0) {
            this.tagsView = this.getView('.tags-container');
            if (!this.tagsView) {
               this.tagsView = new Tag.Views.List({
                  tags: this.model.get('tags'),
                  photo: this.model
               });
               this.listenTo(this.tagsView, 'tag:remove', function() {
                  // Update tags count
                  this.model.changeTagsCount(-1);
               });
               this.setView('.tags-container', this.tagsView).render();
            }
         }

         // Comments
         this.setView('.comments', new Comment.Views.List({
            comments: this.options.comments,
            photoId: this.options.photoId
         }));

         // Categories
         this.setView('.categories', new Category.Views.List({
            categories: this.options.categories,
            photoId: this.options.photoId
         }));

         // Like Button
         if (!this.getView('.like-button')) {
            var likeButtonView = new Photo.Views.LikeButton({
               photo: this.model
            });
            var that = this;
            likeButtonView.on('like', function(liked) {
               $likeCount = $(that.el).find('#like-count');
               if (liked) {
                  $likeCount.html(that.model.get('likes_count') + 1);
               } else {
                  $likeCount.html(that.model.get('likes_count'));
               }
            });
            this.setView('.like-button', likeButtonView);
         }

         // Favorite Button
         if (!this.getView('.favorite-button')) {
            this.setView('.favorite-button', new Photo.Views.FavoriteButton({
               photo: this.model
            }));
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         $('.full-photo img').load(function() {
            $('#photo').fadeIn();
         });
         FB.XFBML.parse();
      },

      changeTab: function(e) {
         e.preventDefault();
         $(e.currentTarget).tab('show');
      },

      showTags: function() {
         $tags = $(this.el).find('.tags');
         if ($tags.length > 0) {
            if ($tags.data('state') == 'hidden') {
               $tags.fadeIn('fast');
               $tags.attr('data-state', 'visible');
            } else {
               $tags.fadeOut('fast');
               $tags.attr('data-state', 'hidden');
            }
         }
      },

      checkboxShowTags: function(e) {
         this.model.set('showTags', e.currentTarget.checked);
         this.updateEmbedCode();
      },

      checkboxShowLikes: function(e) {
         this.model.set('showLikes', e.currentTarget.checked);
         this.updateEmbedCode();
      },

      updateEmbedCode: function() {
         $('.embedCode').html(this.model.getEmbed());
      },

      selectTextOnFocus: function(e) {
         e.preventDefault();
         $(e.currentTarget).select();
      },

      events: {
         "click .adentify-pastille": "showTags",
         "click .showTagsCheckbox": "checkboxShowTags",
         "click .showLikesCheckbox": "checkboxShowLikes",
         "mouseup .selectOnFocus": "selectTextOnFocus",
         "click #photo-tabs a": "changeTab"
      }
   });

   Photo.Views.RightMenu = Backbone.View.extend({
      template: 'photo/rightMenu',

      serialize: function() {
         return {
            model: this.model
         }
      },

      beforeRender: function() {
         // Favorite Button
         if (!this.getView('.follow-button') && this.model.has('ownerModel')) {
            var User = require('modules/user');
            this.setView('.follow-button', new User.Views.FollowButton({
               user: this.model.get('ownerModel')
            }));
         }
      },

      initialize: function() {
         this.model = this.options.photo;
         this.listenTo(this.options.photo, {
            'sync': this.render
         });
      }
   });

   Photo.Views.LikeButton = Backbone.View.extend({
      template: 'photo/likeButton',
      liked: false,

      serialize: function() {
         return {
            liked: this.liked
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         if (this.options.photo) {
            this.photo = this.options.photo;
            var that = this;
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     url: Routing.generate('api_v1_get_photo_is_liked', { 'id': that.options.photo.get('id') }),
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function(response) {
                        that.liked = response;
                        that.render();
                     }
                  });
               }
            });
         }
      },

      events: {
         'click .like-button': 'likeButtonClick'
      },

      likeButtonClick: function() {
         // Like photo
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_like'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  type: 'POST',
                  data: { photoId: that.photo.get('id') }
               });
            }
         });
         this.liked = !this.liked;

         this.render();
         this.trigger('like', this.liked);
      }
   });

   Photo.Views.FavoriteButton = Backbone.View.extend({
      template: 'photo/favoriteButton',
      added: false,

      serialize: function() {
         return {
            added: this.added
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         if (this.options.photo) {
            this.photo = this.options.photo;
            var that = this;
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     url: Routing.generate('api_v1_get_photo_is_favorites', { 'id': that.options.photo.get('id') }),
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function(response) {
                        that.added = response;
                        that.render();
                     }
                  });
               }
            });
         }
      },

      events: {
         'click .favorite-button': 'favoriteButtonClick'
      },

      favoriteButtonClick: function() {
         // Favorite photo
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_photo_favorite'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  type: 'POST',
                  data: { photoId: that.photo.get('id') }
               });
            }
         });
         this.added = !this.added;

         this.render();
         this.trigger('favorite', this.added);
      }
   });

   return Photo;
});