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
   'moment',
   'pinterest'
], function(app, Tag, Common, Comment, Category, Hashtag, moment) {

   var Photo = app.module();
   var loaded = false;

   Photo.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_photo');
      },

      toJSON: function() {
         var jsonAttributes = jQuery.extend(true, {}, this.attributes);
         delete jsonAttributes.comments_count;
         delete jsonAttributes.comments;
         delete jsonAttributes.likes;
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
         return { photo: jsonAttributes };
      },

      defaults: {
         caption: '',
         showTags: true,
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
            this.set('profileLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('owner').id }));
            this.set('fullname', this.get('owner').firstname + ' ' + this.get('owner').lastname);
            var User = require('modules/user');
            this.set('ownerModel', new User.Model(this.get('owner')));
         }
         if (this.has('tags') && this.get('tags').length > 0 && typeof this.get('tags').models === 'undefined') {
            this.set('tags', new Tag.Collection(this.get('tags')));
         } else {
            if (typeof init !== 'undefined' && init && this.has('tags') && this.get('tags').length === 0)
               this.set('tags', new Tag.Collection());
         }
      },

      getEmbed: function() {
         var query = [];
         if (this.get('showLikes'))
            query.push('show-likes=true');
         if (this.get('showTags'))
            query.push('show-tags=true');
         if (this.get('hideCopyright'))
            query.push('hide-copyright=true')
         if (query.length > 0)
            query = '?' + query.join('&');

         return '&lt;iframe src="' + app.rootUrl +'iframe/photo-' + this.get('id') + '.html' + query + '" scrolling="no" frameborder="0" style="border:none; overflow:hidden;" width="' + this.get("large_width") + '" height="' + this.get("large_height") + '" allowTransparency="true"&gt;&lt;/iframe&gt;';
      },

      getBrands: function(sharedPlatform) {
         var hasBrands = false;
         var brands = [];
         if (this.has('tags') && this.get('tags').length > 0 && typeof this.get('tags').models !== 'undefined') {
            this.get('tags').each(function(tag) {
               if (tag.has('brandModel')) {
                  hasBrands = true;
                  var found = false;
                  var brandName = '';
                  switch (sharedPlatform) {
                     case 'twitter':
                        if (tag.get('brandModel').has('twitter_url')) {
                           var pattern = /^https?:\/\/(www\.)?twitter\.com\/(#!\/)?([^\/]+)(\w+)*$/i;
                           pattern.exec(tag.get('brandModel').get('twitter_url'));
                           if (RegExp.$3) {
                              brandName = '@' + RegExp.$3;
                              break;
                           }
                        }
                     default:
                        brandName = tag.get('brandModel').get('name');
                        break;
                  }

                  brands.forEach(function(brand) {
                     if (brand == brandName) {
                        found = true;
                     }
                  });
                  if (!found)
                     brands.push(brandName);
               }
            });
         }

         return hasBrands ? brands.join(', ') : hasBrands;
      },

      getShareText: function(sharedPlatform) {
         var brands = this.getBrands(sharedPlatform);
         if (brands !== false) {
            return $.t('photo.shareTextWithTags', { 'brands': brands });
         } else {
            return $.t('photo.shareTextWithoutTags');
         }
      },

      changeTagsCount: function(value) {
         if (this.has('tags_count')) {
            var count = this.get('tags_count') + value;
            this.set('tags_count', count < 0 ? 0 : count);
         }
      },

      isOwner: function() {
         return this && this.has('owner') ? currentUserId === this.get('owner').id : false;
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
               hashtags: this.hashtags,
               updateMetas: this.options.updateMetas
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
            url: Routing.generate('api_v1_get_photo_categories', { id: this.options.photo.get('id'), locale: currentLocale })
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
         app.appState().set('currentPhotoModel', this.model);
         this.modal = typeof this.options.modal !== 'undefined' ? this.options.modal : false;
         this.listenTo(this.options.photo, {
            'error': function(model, resp) {
               if (resp.status == 404) {
                  Common.Tools.notFound();
               } else if (resp.status == 403) {
                  app.useLayout().setView('#center-pane-content', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('photo.forbidden'),
                     showClose: true
                  })).render();
               } else {
                  app.useLayout().setView('#center-pane-content', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('photo.errorLoading'),
                     showClose: true
                  })).render();
               }
            },
            'sync': function(model, resp) {
               if (resp === null) {
                  app.useLayout().setView('#center-pane-content', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('photo.noPhoto'),
                     showClose: false
                  })).render();
               } else {
                  this.updateMetas();
                  this.render();
                  this.$el.fadeIn();
               }
            }
         });

         this.listenTo(app, 'tagMenuTools:tagAdded', function(photo) {
            if (typeof photo !== 'undefined' && typeof this.options.photo !== 'undefined' && this.options.photo.get('id') == photo.get('id')) {
               this.render();
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

         if (this.options.updateMetas)
            this.updateMetas();
      },

      updateMetas: function() {
         var that = this;
         Common.Tools.setPhotoMetas(this.options.photo);
         app.trigger('domchange:title', $.t('photo.pageTitle', {caption: this.options.photo.get('caption')}));
         if (this.options.photo.get('caption'))
            app.trigger('domchange:description', $.t('photo.pageDescription', {caption: this.options.photo.get('caption')}));
         app.oauth.loadAccessToken({
            success: function() {
               that.loadUnvalidateTags();
            }
         });
      },

      serialize: function() {
         return {
            model: this.options.photo,
            pageUrl: this.options.photo.get('link'),
            publishDate: moment(this.options.photo.get('created_at')).fromNow()
         };
      },

      beforeRender: function() {
         var that = this;

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
            app.analytic().view(this.model);
         }

         // Comments
         if (!this.getView('.comments')) {
            var commentsView = new Comment.Views.List({
               comments: this.options.comments,
               photoId: this.options.photoId
            });
            commentsView.on('comment:new', function() {
               that.model.set('comments_count', that.model.get('comments_count') + 1);
            });
            this.setView('.comments', commentsView);

         }

         // Like Button
         if (!this.getView('.like-button')) {
            var likeButtonView = new Photo.Views.LikeButton({
               photo: this.model
            });
            likeButtonView.on('like', function(liked) {
               that.updateLikedCount(liked);
               that.getView('.popover-wrapper').updateLike(liked);
            });
            this.setView('.like-button', likeButtonView);
         }

         // Pastille popover
         if (!this.getView('.popover-wrapper')) {
            var pastillePopoverView = new Photo.Views.PastillePopover({
               photo: this.model
            });
            pastillePopoverView.on('addTag', function() {
               that.addNewTag(null);
               that.hidePastillePopover();
            });
            pastillePopoverView.on('like', function(liked) {
               that.updateLikedCount(liked);
               that.getView('.like-button').likeButtonClick();
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
               hashtags: this.options.hashtags,
               showAlert: true
            }));
         }

         this.listenTo(app, 'addTagModal:hide', this.update);
      },

      afterRender: function() {
         var that = this;
         this.$('.photo-full').load(function() {
            that.photoLoaded();
         });
         this.photoLoadedTimeout = setTimeout(function() {
            that.photoLoaded();
         }, 3000);
         $(this.el).i18n();
         FB.XFBML.parse();
         gapi.plusone.go();
         this.$('.report-button').tooltip();
      },

      photoLoaded: function() {
         var that = this;
         clearTimeout(this.photoLoadedTimeout);
         this.$('.loading-gif-container').fadeOut(200, function() {
            if (that.$('.full-photo:visible').length == 0) {
               that.$('.full-photo').fadeIn('fast');
            }
         });
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
         $likeCount = this.$('.likes-count-value');
         var currentLikeCount = $likeCount.html() ? parseInt($likeCount.html()) : 0;
         if (liked) {
            this.model.set('likes_count', this.model.get('likes_count') + 1);
            $likeCount.html(currentLikeCount + 1);
         } else {
            this.model.set('likes_count', this.model.get('likes_count') > 0 ? this.model.get('likes_count') - 1 : 0);
            $likeCount.html(currentLikeCount > 0 ? currentLikeCount - 1 : 0);
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

      checkboxHideCopyright: function(e) {
         this.model.set('hideCopyright', e.currentTarget.checked);
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
         if (app.appState().isLogged()) {
            Tag.Common.addTag(evt, this.model, this.options.categories, this.options.hashtags);
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.comment');
         }
      },

      showPastillePopover: function() {
         $(this.el).find('.adentify-pastille-wrapper .popover').fadeIn(100);
      },

      hidePastillePopover: function() {
         $(this.el).find('.adentify-pastille-wrapper .popover').stop().fadeOut('fast');
      },

      report: function() {
         if (app.appState().isLogged()) {
            Photo.Common.report(this.model);
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.report');
         }
      },

      showLikers: function() {
         var User = require('modules/user');
         var users = new User.Collection();
         users.url = Routing.generate('api_v1_get_photo_likers', { id: this.model.get('id')} );
         User.Common.showModal(users, 'photo.likers', 'photo.noLiker', true);
      },

      update: function() {
         this.options.categories.fetch({
            url: Routing.generate('api_v1_get_photo_categories', { id: this.model.get('id'), locale: currentLocale })
         });
         this.options.hashtags.fetch({
            url: Routing.generate('api_v1_get_photo_hashtags', { id: this.model.get('id') })
         });
      },

      events: {
         'click .hideCopyrightCheckbox': 'checkboxHideCopyright',
         'click .showTagsCheckbox': 'checkboxShowTags',
         'click .showLikesCheckbox': 'checkboxShowLikes',
         'mouseup .selectOnFocus': 'selectTextOnFocus',
         'click #photo-tabs a': 'changeTab',
         'click .add-new-tag': 'addNewTag',
         'mouseenter .adentify-pastille-wrapper': 'showPastillePopover',
         'mouseleave .adentify-pastille-wrapper': 'hidePastillePopover',
         'click .report-button': 'report',
         'click .likes-count': 'showLikers'
      }
   });

   Photo.Views.RightMenu = Backbone.View.extend({
      template: 'photo/rightMenu',

      serialize: function() {
         return {
            model: this.model
         };
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
            this.insertView('.linked-photos-list', new Photos.Views.Item({
               model: photo,
               tagName: 'li class="ticker-item"',
               itemClickBehavior: Photos.Common.PhotoItemClickBehaviorDetail,
               addTag: false
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
                  showClose: false
               }));
            }
            this.render();
         });
      }
   });

   Photo.Views.Edit = Backbone.View.extend({
      template: 'photo/edit',
      currentHashtags: [],
      select2Loaded: false,

      serialize: function() {
         return {
            model: this.model,
            categories: this.categories,
            isSelectedCategory: this.isSelectedCategory,
            photoCategories: this.photoCategories,
            photoHashtags: this.photoHashtags
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
         this.photoCategories = this.options.photoCategories;
         this.photoHashtags = this.options.photoHashtags;
         this.categories = new Category.Collection();
         this.listenTo(this.categories, 'sync', this.render);
         this.categories.fetch();
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
         if (this.categories && this.categories.length > 0 && !this.select2Loaded) {
            this.select2Loaded = !this.select2Loaded;
            var that = this;
            $(this.el).find('.selectCategories').select2();
            $(this.el).find('.selectCategories').on('change', function() {
               that.model.set('categories', $(that.el).find('.selectCategories').select2('val'));
            });
            that.model.set('categories', $(that.el).find('.selectCategories').select2('val'));
         }
         this.$('.selectHashtags').select2({
            minimumInputLength: 1,
            multiple: true,
            createSearchChoice: function(term, data) {
               if ($(data).filter(function() {
                  return this.text.localeCompare(term) === 0;
               }).length === 0) {
                  return {id:term, text:term};
               }
            },
            initSelection: function(element, callback) {
               var hashtag = $(element).val();
               var res = hashtag.split(',');
               var data = [];
               res.forEach(function(h) {
                  data.push({
                     id: h,
                     text: h
                  })
               });
               callback(data);
            },
            ajax: {
               url: Routing.generate('api_v1_get_hashtag_search'),
               dataType: 'json',
               data: function(term, page) {
                  return {
                     query: term,
                     page: page
                  }
               },
               results: function(data, page) {
                  return {
                     results : $.map(data.data, function(item) {
                        return {
                           id : item.name,
                           text : item.name,
                           usedCount: item.used_count
                        };
                     })
                  }
               }
            },
            dropdownCssClass: "bigdrop",
            formatResult: function (hashtag) {
               var markup = "<table class='hashtag-result'><tr>";
               if (hashtag.text !== undefined) {
                  markup += "<td class='hashtag-name'>" + hashtag.text + "</td>";
               }
               if (hashtag.usedCount !== undefined) {
                  markup += "<td class='hashtag-usecount'>" + $.t('hashtag.usedCount', {count: hashtag.usedCount}) + "</td>";
               } else {
                  markup += "<td class='hashtag-usecount'>" + $.t('hashtag.usedCount', {count: 0}) + "</td>";
               }
               markup += "</tr></table>"
               return markup;
            }
         }).on("change", function(e) {
               that.model.set('hashtags', e.val);
            });
         if (this.photoHashtags && this.photoHashtags.length > 0) {
            this.$('.selectHashtags').select2('val', this.photoHashtags.map(function(model) { return model.get('name'); }));
            this.model.set('hashtags', this.$('.selectHashtags').select2('val'));
         }
      },

      isSelectedCategory: function(category) {
         if (typeof this.photoCategories !== 'undefined') {
            var found = false;
            this.photoCategories.each(function(cat) {
               if (cat.get('id') == category.get('id'))
               {
                  found = true;
                  return found;
               }
            });
            if (found)
               return 'selected="selected"';
         }

         return null;
      },

      addTag: function(e) {
         if ($(e.target).hasClass('photo-overlay')) {
            var xPosition = (e.offsetX === undefined ? e.originalEvent.layerX : e.offsetX) / e.currentTarget.clientWidth;
            var yPosition = (e.offsetY === undefined ? e.originalEvent.layerY : e.offsetY) / e.currentTarget.clientHeight;

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
            tag.set('tagIcon', this.currentTag ? this.currentTag.get('tagIcon') : 'glyphicon glyphicon-tag')
            tag.set('tempTag', true);
            this.model.get('tags').add(tag);
            this.model.setup();
            this.currentTag = tag;

            app.trigger('photo:tagAdded', tag);
         }
      },

      tagFormFabChanged: function(tabName) {
         if (this.currentTag) {
            if (tabName == '#product') {
               this.currentTag.set('tagIcon', 'glyphicon glyphicon-tag');
            } else if (tabName == '#venue') {
               this.currentTag.set('tagIcon', 'tag-place-icon');
            } else if (tabName == '#person') {
               this.currentTag.set('tagIcon', 'tag-user-icon');
            } else if (tabName == '#advertising') {
               this.currentTag.set('tagIcon', 'advertising-icon');
            }
         }
      },

      submitForm: function(evt) {
         evt.preventDefault();
         // Validate
         var caption = $(this.el).find('#photo-caption').val();
         var btn = $('#form-details button[type="submit"]');
         btn.button('loading');
         if (caption) {
            this.model.set('caption', caption);

            var that = this;
            if (this.model.has('hashtags')) {
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_post_hashtag'),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        type: 'POST',
                        data: {
                           hashtags: that.model.get('hashtags')
                        },
                        success: function(data) {
                           that.model.set('hashtags', $.map(data, function(h) { return h.id; }));
                           that.save(btn);
                        },
                        error: function() {
                           btn.button('reset');
                        }
                     });
                  }
               });
            } else
               that.save(btn);
         } else
            this.save(btn);
      },

      save: function(btn) {
         var that = this;
         this.model.getToken('photo_item', function() {
            that.model.url = Routing.generate('api_v1_put_photo', { id: that.model.get('id')});
            that.model.save(null, {
               success: function() {
                  that.setView('.alert-photo-details', new Common.Views.Alert({
                     cssClass: Common.alertSuccess,
                     message: $.t('photo.detailsUpdateSuccess'),
                     showClose: true
                  })).render();
               },
               error: function() {
                  that.setView('.alert-photo-details', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('photo.detailsUpdateError'),
                     showClose: true
                  })).render();
               },
               complete: function() {
                  btn.button('reset');
                  //app.trigger('photoEditModal:close');
               }
            });
         });
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
         var that = this;
         this.$('button[data-liked]').hover(function() {
            if (that.liked) {
               $(this).html($.t('photo.dislike'));
            }
         }, function() {
            if (that.liked) {
               $(this).html($.t('photo.liked'));
            } else {
               $(this).html($.t('photo.like'));
            }
         });
      },

      initialize: function() {
         if (this.options.photo) {
            this.photo = this.options.photo;
            var that = this;
            if (app.appState().isLogged()) {
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_get_photo_is_liked', { 'id': that.options.photo.get('id') }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           that.liked = response.liked;
                           that.render();
                        }
                     });
                  }
               });
            }
         }
      },

      events: {
         'click .like-button': 'likeButtonClick'
      },

      likeButtonClick: function(trigger) {
         trigger = trigger || false;
         if (app.appState().isLogged()) {
            // Like photo
            Photo.Common.like(this.photo);
            this.liked = !this.liked;

            this.render();
            if (trigger)
               this.trigger('like', this.liked);
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.like');
         }
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
                        that.added = response.favorites;
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
         if (app.appState().isLogged()) {
            // Favorite photo
            Photo.Common.favorite(this.photo);
            this.added = !this.added;

            this.render();
            this.trigger('favorite', this.added);
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.favorite');
         }
      }
   });

   Photo.Views.PastillePopover = Backbone.View.extend({
      template: 'photo/pastillePopover',
      isFavorite: false,
      liked: false,

      serialize: function() {
         return {
            isFavorite: this.isFavorite,
            liked: this.liked
         };
      },

      addTag: function() {
         if (app.appState().isLogged()) {
            this.trigger('addTag', this.options.photo);
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.addTag');
         }
      },

      like: function() {
         if (app.appState().isLogged()) {
            this.trigger('like', !this.liked);
            this.updateLike(!this.liked);
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.like');
         }
      },

      updateLike: function(liked) {
         this.liked = liked;
         this.render();
      },

      share: function() {
         this.trigger('share', this.options.photo);
      },

      favorite: function() {
         if (app.appState().isLogged()) {
            this.trigger('favorite', this.options.photo);
            this.isFavorite = !this.isFavorite;
            this.render();
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.favorite');
         }
      },

      initialize: function() {
         var that = this;
         if (app.appState().isLogged()) {
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     url: Routing.generate('api_v1_get_photo_is_favorites', { 'id': that.options.photo.get('id') }),
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function(response) {
                        that.isFavorite = response.favorites;
                        that.render();
                     }
                  });
                  $.ajax({
                     url: Routing.generate('api_v1_get_photo_is_liked', { 'id': that.options.photo.get('id') }),
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function(response) {
                        that.liked = response.liked;
                        that.render();
                     }
                  });
               }
            });
         }
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
         this.trigger('report:submit', this.$('.reason-textarea').val(), $.t(this.$('input[name="reportOptions"]:checked').val()));
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
                        success(data.liked);
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
            title: 'photo.reportTitle',
            modalDialogClasses: 'report-dialog'
         });
         reportView.on('report:submit', function(reason, option) {
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
                                 'option': option,
                                 'photo': photo.get('id'),
                                 '_token': data.csrf_token
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