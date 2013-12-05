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
   'modules/category',
   'modules/hashtag',
   "pinterest"
], function(app, Tag, Common, Comment, Category, Hashtag) {

   var Photo = app.module();
   var loaded = false;

   Photo.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_photo');
      },

      toJSON: function() {
         var jsonAttributes = this.attributes;
         delete jsonAttributes.comments_count;
         delete jsonAttributes.created_at;
         delete jsonAttributes.fullname;
         delete jsonAttributes.link;
         delete jsonAttributes.likes_count;
         delete jsonAttributes.owner;
         delete jsonAttributes.ownerModel;
         delete jsonAttributes.tags;
         delete jsonAttributes.tagsConverted;
         delete jsonAttributes.profileLink;
         delete jsonAttributes.showLikes;
         delete jsonAttributes.showTags;
         delete jsonAttributes.status;
         delete jsonAttributes.tags_count;
         delete jsonAttributes.visibility_scope;
         delete jsonAttributes.id;
         return { photo: jsonAttributes }
      },

      defaults: {
         caption: '',
         showTags: false,
         showLikes: false
      },

      initialize: function() {
         this.setup(true);
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });
      },

      setup: function(init) {
         this.set('link', app.beginUrl + app.root + $.t('routing.photo/id/', { id: this.get('id') }));
         if (this.has('owner') && !this.has('ownerModel')) {
            this.set('profileLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('owner')['id'] }));
            this.set('fullname', this.get('owner')['firstname'] + ' ' + this.get('owner')['lastname']);
            var User = require('modules/user');
            this.set('ownerModel', new User.Model(this.get('owner')));
         }
         if (this.has('tags') && this.get('tags').length > 0 && !this.has('tagsConverted')) {
            this.set('tagsConverted', '');
            this.set('tags', new Tag.Collection(this.get('tags')));
         } else {
            if (typeof init !== 'undefined' && init && this.has('tags') && this.get('tags').length == 0)
               this.set('tags', new Tag.Collection());
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
         return this && this.has('owner') ? currentUserId === this.get('owner')['id'] : false;
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

   Photo.Views.Modal = Backbone.View.extend({
      template: 'photo/modal',

      beforeRender: function() {
         this.setViews({
            "#center-modal-content": new Photo.Views.Item({
               photo: this.options.photo,
               comments: this.comments,
               photoId: this.options.photo.get('id'),
               modal: true,
               categories: this.categories,
               hashtags: this.hashtags
            }),
            "#right-modal-content": new Photo.Views.RightMenu({
               photo: this.options.photo,
               tickerPhotos: this.linkedPhotos,
               tagged: false
            })
         });
      },

      initialize: function() {
         this.comments = new Comment.Collection();
         this.hashtags = new Hashtag.Collection();
         this.categories = new Category.Collection();
         var Photos = require('modules/photos');
         this.linkedPhotos = new Photos.Collection();
         this.linkedPhotos.fetch({
            url: Routing.generate('api_v1_get_photo_linked_photos', { id: this.options.photo.get('id') })
         });
         this.comments.fetch({
            url: Routing.generate('api_v1_get_photo_comments', { id: this.options.photo.get('id') })
         });
         this.categories.fetch({
            url: Routing.generate('api_v1_get_photo_categories', { id: this.options.photo.get('id') })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_photo_hashtags', { id: this.options.photo.get('id') })
         });
      }
   });

   Photo.Views.Item = Backbone.View.extend({
      template: "photo/item",
      tagName: 'div class="photo-item-container fadeOut"',

      initialize: function() {
         var that = this;
         this.model = this.options.photo;
         this.modal = typeof this.options.modal !== 'undefined' ? this.options.modal : false;
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
                        that.loadUnvalidateTags();
                     }
                  });
                  this.render();
                  this.$el.fadeIn();
               }
            }
         });

         if (this.modal) {
            app.oauth.loadAccessToken({
               success: function() {
                  that.loadUnvalidateTags();
               }
            });
            this.$el.fadeIn();
         }

         this.listenTo(app, 'tag:removeTag', function(tag) {
            this.model.get('tags').remove(tag);
            this.render();
         });
      },

      serialize: function() {
         return {
            model: this.options.photo,
            pageUrl: this.options.photo.get('link')
         };
      },

      beforeRender: function() {
         if (this.model && this.model.has('tags') && this.model.get('tags').length > 0) {
            this.tagsView = this.getView('.tags-container');
            if (!this.tagsView) {
               this.tagsView = new Tag.Views.List({
                  tags: this.model.get('tags'),
                  photo: this.model,
                  visible: true
               });
               this.listenTo(this.tagsView, 'tag:remove', function() {
                  // Update tags count
                  this.model.changeTagsCount(-1);
               });
               this.setView('.tags-container', this.tagsView).render();
            }
         }

         // Comments
         if (!this.getView('.comments')) {
            this.setView('.comments', new Comment.Views.List({
               comments: this.options.comments,
               photoId: this.options.photoId
            }));
         }

         // Like Button
         if (!this.getView('.like-button')) {
            var likeButtonView = new Photo.Views.LikeButton({
               photo: this.model
            });
            var that = this;
            likeButtonView.on('like', function(liked) {
               that.updateLikedCount(liked);
            });
            this.setView('.like-button', likeButtonView);
         }

         // Pastille popover
         if (!this.getView('.popover-wrapper')) {
            var that = this;
            var pastillePopoverView = new Photo.Views.PastillePopover({
               photo: this.model
            });
            pastillePopoverView.on('addTag', function() {
               that.addNewTag(null);
               that.hidePastillePopover();
            });
            pastillePopoverView.on('like', function() {
               Photo.Common.like(that.model, function(liked) {
                  that.updateLikedCount(liked);
               });
               that.hidePastillePopover();
            });
            pastillePopoverView.on('share', function() {
               if ($(that.el).find('.share-overlay:visible').length > 0) {
                  $(that.el).find('.share-overlay').fadeOut('fast');
               } else {
                  var view = that.getView('.share-overlay');
                  if (!view)
                  {
                     view = new Photo.Views.ShareOverlay({
                        model: that.model
                     });
                     view.on('close', function() {
                        $(that.el).find('.share-overlay').stop().fadeOut('fast');
                     });
                     that.setView('.share-overlay', view).render();
                  }
                  $(that.el).find('.share-overlay').fadeIn(100);
               }
               that.hidePastillePopover();
            });
            pastillePopoverView.on('favorite', function() {
               Photo.Common.favorite(that.model);
               that.hidePastillePopover();
            });
            this.setView('.popover-wrapper', pastillePopoverView);
         }

         // Categories
         if (!this.getView('.categories')) {
            this.setView('.categories', new Category.Views.List({
               categories: this.options.categories,
               photoId: this.options.photoId
            }));
         }

         // Hashtags
         if (!this.getView('.hashtags')) {
            this.setView('.hashtags', new Hashtag.Views.List({
               hashtags: this.options.hashtags
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

      loadUnvalidateTags: function() {
         var that = this;
         if (this.model.isOwner()) {
            $.ajax({
               url: Routing.generate('api_v1_get_photo_waiting_tags', { id: this.model.get('id') }),
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
      },

      changeTab: function(e) {
         e.preventDefault();
         $(e.currentTarget).tab('show');
      },

      updateLikedCount: function(liked) {
         $likeCount = $(this.el).find('#like-count');
         if (liked) {
            $likeCount.html(this.model.get('likes_count') + 1);
         } else {
            $likeCount.html(this.model.get('likes_count') > 0 ? this.model.get('likes_count') - 1 : 0);
         }
      },

      clickPastille: function() {
         $tags = $(this.el).find('.tags');
         if ($tags.data('always-visible') == 'no') {
            $tags.data('always-visible', 'yes');
            this.showTags();
         } else {
            $tags.data('always-visible', 'no');
            this.hideTags();
         }
      },

      showTags: function() {
         $(this.el).find('.tags').stop().fadeIn(100);
      },

      hideTags: function() {
         $tags = $(this.el).find('.tags');
         if ($tags.data('always-visible') == 'no')
            $(this.el).find('.tags').stop().fadeOut('fast');
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

      addNewTag: function(evt) {
         Tag.Common.addTag(evt, this.model);
      },

      showPastillePopover: function() {
         $(this.el).find('.adentify-pastille-wrapper .popover').fadeIn(100);
      },

      hidePastillePopover: function() {
         $(this.el).find('.adentify-pastille-wrapper .popover').stop().fadeOut('fast');
      },

      report: function() {
         Photo.Common.report(this.model);
      },

      events: {
         'click .adentify-pastille': 'clickPastille',
         'mouseenter .photo-container': 'showTags',
         'mouseleave .photo-container': 'hideTags',
         "click .showTagsCheckbox": "checkboxShowTags",
         "click .showLikesCheckbox": "checkboxShowLikes",
         "mouseup .selectOnFocus": "selectTextOnFocus",
         "click #photo-tabs a": "changeTab",
         'click .add-new-tag': "addNewTag",
         'mouseenter .adentify-pastille-wrapper': 'showPastillePopover',
         'mouseleave .adentify-pastille-wrapper': 'hidePastillePopover',
         'click .report-button': 'report'
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
         var Photos = require('modules/photos');
         this.options.tickerPhotos.each(function(photo) {
            this.insertView('.linked-photos-list', new Photos.Views.TickerItem({
               model: photo
            }));
         }, this);
      },

      initialize: function() {
         this.model = this.options.photo;
         this.listenTo(this.options.photo, {
            'sync': this.render
         });
         this.listenTo(this.options.tickerPhotos, 'sync', function() {
            if (this.options.tickerPhotos.length == 0) {
               this.setView('.alert-linked-photos-list', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('photo.noLinkedPhotos'),
                  showClose: true
               }));
            }
            this.render();
         });
      }
   });

   Photo.Views.Edit = Backbone.View.extend({
      template: 'photo/edit',

      serialize: function() {
         return {
            model: this.model
         }
      },

      initialize: function() {
         app.appState().set('currentPhotoModel', this.model);
         this.listenTo(app, 'tagform:changetab', function(tabName) {
            this.tagFormFabChanged(tabName);
         });
         this.listenTo(app, 'photo:tagRemoved', function(tag) {
            if (this.model.has('tags'))
               this.model.get('tags').remove(tag);
         });
      },

      beforeRender: function() {
         if (!this.model.has('tags'))
            this.model.set('tags', new Tag.Collection());
         this.tagsView = this.getView('.tags-container');
         if (!this.tagsView) {
            this.tagsView = new Tag.Views.List({
               tags: this.model.get('tags'),
               photo: this.model,
               visible: true
            });
            this.listenTo(this.tagsView, 'tag:remove', function() {
               // Update tags count
               this.model.changeTagsCount(-1);
            });
            this.setView('.tags-container', this.tagsView).render();
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      addTag: function(e) {
         var tagRadius = 12.5;
         var xPosition = (e.offsetX - tagRadius) / e.currentTarget.clientWidth;
         var yPosition = (e.offsetY - tagRadius) / e.currentTarget.clientHeight;

         // Remove tags aren't persisted
         var that = this;
         this.model.get('tags').each(function(tag) {
            if (tag.has('tempTag')) {
               that.model.get('tags').remove(tag);
            }
         });

         var tag = new Tag.Model();
         tag.set('x_position', xPosition);
         tag.set('y_position', yPosition);
         tag.set('cssClass', 'new-tag');
         tag.set('tagIcon', 'tag-brand-icon')
         tag.set('tempTag', true);
         this.model.get('tags').add(tag);
         this.currentTag = tag;

         app.trigger('photo:tagAdded', tag);
      },

      tagFormFabChanged: function(tabName) {
         if (this.currentTag) {
            if (tabName == '#product') {
               this.currentTag.set('tagIcon', 'tag-brand-icon');
            } else if (tabName == '#venue') {
               this.currentTag.set('tagIcon', 'tag-place-icon');
            } else if (tabName == '#person') {
               this.currentTag.set('tagIcon', 'tag-user-icon');
            }
         }
      },

      submitForm: function(evt) {
         evt.preventDefault();
         // Validate
         var caption = $(this.el).find('#photo-caption').val();
         if (caption) {
            var btn = $('#form-details button[type="submit"]');
            btn.button('loading');

            var that = this;
            this.model.set('caption', caption);
            this.model.getToken('photo_item', function() {
               that.model.url = Routing.generate('api_v1_put_photo', { id: that.model.get('id')});
               that.model.save();
               btn.button('reset');
            });
         }
      },

      events: {
         'click .photo-overlay': 'addTag',
         'submit #form-details': 'submitForm'
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
         Photo.Common.like(this.photo);
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
         Photo.Common.favorite(this.photo);
         this.added = !this.added;

         this.render();
         this.trigger('favorite', this.added);
      }
   });

   Photo.Views.PastillePopover = Backbone.View.extend({
      template: 'photo/pastillePopover',

      addTag: function() {
         this.trigger('addTag', this.options.photo);
      },

      like: function() {
         this.trigger('like', this.options.photo);
      },

      share: function() {
         this.trigger('share', this.options.photo);
      },

      favorite: function() {
         this.trigger('favorite', this.options.photo);
      },

      afterRender: function() {
         $(this.el).find('.btn-icon').tooltip();
      },

      events: {
         'click .add-tag-button': 'addTag',
         'click .like-button': 'like',
         'click .share-button': 'share',
         'click .favorite-button': 'favorite'
      }
   });

   Photo.Views.ShareOverlay = Backbone.View.extend({
      template: 'photo/shareOverlay',
      tagName: 'div class="share-overlay-wrapper"',

      serialize: function() {
         return {
            model: this.model,
            pageUrl: window.location.href,
            rootUrl: app.rootUrl
         };
      },

      afterRender: function() {
         FB.XFBML.parse();
      },

      close: function() {
         this.trigger('close');
      },

      events: {
         'click .close-share': 'close'
      }
   });

   Photo.Views.Report = Backbone.View.extend({
      'template': 'photo/report',

      serialize: function() {
         return {
            model: this.photo
         }
      },

      report: function(evt) {
         evt.preventDefault();
         this.trigger('report:submit', $(this.el).find('.reason-textarea').val());
      },

      initialize: function() {
         this.photo = this.options.photo;
      },

      deletePhoto: function(e) {
         e.preventDefault();
         this.photo.delete();
         this.trigger('close');
      },

      events: {
         'click .reportSubmit': 'report',
         'click .deletePhotoButton': 'deletePhoto'
      }
   });

   Photo.Common = {
      like: function(photo, success) {
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_like'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  type: 'POST',
                  data: { photoId: photo.get('id') },
                  success: function(data) {
                     if (success) {
                        success(data);
                     }
                  }
               });
            }
         });
      },

      favorite: function(photo, success) {
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_photo_favorite'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  type: 'POST',
                  data: { photoId: photo.get('id') },
                  success: function(data) {
                     if (success) {
                        success(data);
                     }
                  }
               });
            }
         });
      },

      report: function(photo) {
         var reportView = new Photo.Views.Report({
            photo: photo
         });
         var modal = new Common.Views.Modal({
            view: reportView,
            showFooter: false,
            showHeader: true,
            title: $.t('photo.reportTitle'),
            modalDialogClasses: 'report-dialog'
         });
         reportView.on('report:submit', function(reason) {
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     headers : {
                        "Authorization": app.oauth.getAuthorizationHeader()
                     },
                     url: Routing.generate('api_v1_get_csrftoken', { intention: 'report_item'}),
                     success: function(data) {
                        $.ajax({
                           url: Routing.generate('api_v1_post_report'),
                           headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                           type: 'POST',
                           data: {
                              report: {
                                 'reason': reason,
                                 'photo': photo.get('id'),
                                 '_token': data
                              }
                           }
                        });
                     }
                  });
               }
            });
            modal.close();
         });
         reportView.on('close', function() {
            modal.close();
         });
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      }
   };

   return Photo;
});