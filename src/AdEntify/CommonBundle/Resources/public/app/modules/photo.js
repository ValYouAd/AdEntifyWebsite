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
      },

      getEmbed: function() {
         return '&lt;img width="<%= this.get("large_width") %>" height="' + this.get("large_height") + '" src="'
            + this.get("fullLargeUrl") + '" alt="' + this.get("caption") + '" data-adentify-photo-id="' + this.get("id")
            + (this.get("showTags") ? ' data-adentify-tags="" ' : '') + (this.get("showLikes") ? ' data-adentify-likes="" ' : '') + ' /&gt;&lt;script type="text/javascript" async src="https://adentify.com/embed/'
            + this.get("id") + '.js"&gt;&lt;/script&gt;';
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

         // Comments
         app.useLayout().setView('.comments', new Comment.Views.List({
            comments: this.options.comments,
            photoId: this.options.photoId
         }));

         // Categories
         app.useLayout().setView('.categories', new Category.Views.List({
            categories: this.options.categories,
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
      },

      afterRender: function() {
         $(this.el).i18n();
         $('.full-photo img').load(function() {
            $('#photo').fadeIn();
         });
         FB.XFBML.parse();
         app.useLayout().getView('.comments').render();
         app.useLayout().getView('.categories').render();
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
               $tags.attr('data-state', 'visible');
            } else {
               $tags.fadeOut('fast');
               $tags.attr('data-state', 'hidden');
            }
         }
      },

      favorite: function() {
         app.photoActions().favorite(this.model);
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
         "click .like-button": "like",
         "click .adentify-pastille": "showTags",
         "click .favorite-button": "favorite",
         "click .showTagsCheckbox": "checkboxShowTags",
         "click .showLikesCheckbox": "checkboxShowLikes",
         "mouseup .selectOnFocus": "selectTextOnFocus"
      }
   });

   return Photo;
});