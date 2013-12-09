/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 12/06/2013
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/hashtag',
   'modules/common'
], function(app, Hashtag, Common) {

   var User = app.module();

   User.Model = Backbone.Model.extend({
      initialize: function() {
         this.listenTo(this, 'sync', this.setup);
         this.setup();
      },

      setup: function() {
         if (!this.get('firstname'))
            this.set('fullname', this.get('username'));
         else
            this.set('fullname', this.get('firstname') + ' ' + this.get('lastname'));
         this.set('link', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('id') }));
         if (this.has('profile_picture') && this.get('profile_picture')) {
            this.set('profilePicture', this.get('profile_picture'));
            this.set('largeProfilePicture', this.get('profile_picture'));
         }
         else if (this.get('facebook_id')) {
            this.set('profilePicture', 'https://graph.facebook.com/' + this.get('facebook_id') + '/picture?width=50&height=50');
            this.set('largeProfilePicture', 'https://graph.facebook.com/' + this.get('facebook_id') + '/picture?width=120&height=120');
         }
         else {
            this.set('profilePicture', app.beginUrl + '/img/anonymous-profile-picture.png');
            this.set('largeProfilePicture', app.beginUrl + '/img/anonymous-profile-picture.png');
         }
      },

      defaults: {
         photos_count: 0
      },

      changeFollowersCount: function(follow) {
         if (follow) {
            this.set('followers_count', this.get('followers_count') + 1);
         } else {
            if (this.get('followers_count') > 0)
               this.set('followers_count', this.get('followers_count') - 1);
         }
      }
   });

   User.Collection = Backbone.Collection.extend({
      model: User.Model
   });

   User.Views.MenuLeft = Backbone.View.extend({
      template: "user/menuLeft",
      showServices: false,

      serialize: function() {
         return {
            model: this.model,
            lastPhoto: this.lastPhoto,
            showServices: this.showServices,
            rootUrl: app.beginUrl + app.root
         };
      },

      beforeRender: function() {
         var that = this;
         if (!this.getView('.followings')) {
            this.setView('.followings', new User.Views.List({
               users: this.followings,
               noUsersMessage: 'profile.noFollowings'
            }));
         }
         if (!this.getView('.followers')) {
            this.setView('.followers', new User.Views.List({
               users: this.followers,
               noUsersMessage: 'profile.noFollowers'
            }));
         }
         if (!this.getView('.follow-button')) {
            var followButtonView = new User.Views.FollowButton({
               user: this.model
            });
            this.setView('.follow-button', followButtonView);
            followButtonView.on('follow', function(follow) {
               that.options.user.changeFollowersCount(follow);
               that.render();
            });
         }
         if (!this.getView('.hashtags')) {
            this.setView('.hashtags', new Hashtag.Views.List({
               hashtags: this.options.hashtags
            }));
         }
         if (this.showServices && !this.getView('.services')) {
            var MySettings = require('modules/mySettings');
            this.setView(".services", new MySettings.Views.ServiceList());
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         if (typeof this.model !== 'undefined' && this.model.has('firstname')) {
            $('#loading-profile').fadeOut('fast', function() {
               $('#profile').fadeIn();
            });
         }
      },

      initialize: function() {
         this.lastPhoto = null;
         this.followings = this.options.followings;
         this.followers = this.options.followers;
         this.showServices = typeof this.options.showServices !== 'undefined' ? this.options.showServices : this.showServices;
         this.listenTo(this.options.user, 'sync', this.render);
         this.options.photos.once('sync', function(collection) {
            if (collection.length > 0) {
               this.lastPhoto = collection.first();
               this.render();
            }
         }, this);
         var that = this;
         this.model = this.options.user;
         this.options.user.fetch({
            url: Routing.generate('api_v1_get_user', { id: this.options.user.get('id') }),
            success: function() {
               app.trigger('domchange:title', $.t('profile.pageTitle', { name: that.options.user.get('firstname') + " " + that.options.user.get('lastname') }));
               $('#loading-photo').fadeOut('fast', function() {
                  $('#profile').fadeIn();
               });
            }
         });
      }
   });

   User.Views.List = Backbone.View.extend({
      template: "user/list",

      serialize: function() {
         return {
            visible: this.visible
         };
      },

      initialize: function() {
         this.listenTo(this.options.users, { 'sync': function() {
            if (this.options.users.length == 0) {
               this.setView('.users-alert', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t(this.options.noUsersMessage),
                  showClose: true
               }));
            } else {
               this.removeView('.users-alert');
            }
            this.render();
         }});
      },

      beforeRender: function() {
         this.options.users.each(function(user) {
            this.insertView('.users', new User.Views.Item({
               model: user
            }));
         }, this);
      }
   });

   User.Views.Item = Backbone.View.extend({
      template: 'user/item',
      tagName: 'li class="user-item"',

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });

   User.Views.FollowButton = Backbone.View.extend({
      template: 'user/followButton',
      added: false,

      serialize: function() {
         return {
            follow: this.follow
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         if (this.options.user) {
            this.user = this.options.user;
            var that = this;
            if (app.appState().isLogged()) {
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_get_user_is_following', { 'id': that.options.user.get('id') }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           that.follow = response;
                           that.render();
                        }
                     });
                  }
               });
            }
         }
      },

      events: {
         'click .follow-button': 'followButtonClick'
      },

      followButtonClick: function() {
         if (app.appState().isLogged()) {
            // Follow user
            var that = this;
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     url: Routing.generate('api_v1_post_user_follower', { id: that.user.get('id') }),
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     type: 'POST',
                     data: { userId: that.user.get('id') }
                  });
               }
            });
            this.follow = !this.follow;

            this.render();
            this.trigger('follow', this.follow);
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.follow');
         }
      }
   });

   User.Dropdown = {
      listenClick: function() {
         var that = this;
         $(document).click(function(){
            that.closeOpenedDropdown();
         });
          $('.profile-infos .user-names, .profile-infos .user-points, .profile-infos a').click(function(e) {
             e.stopPropagation();
             if ($('.profile-infos .dropdown-menu:visible').length > 0) {
                $('.profile-infos .dropdown-menu').stop().fadeOut();
             } else {
                that.closeOpenedDropdown();
                $('.profile-infos .dropdown-menu').fadeIn(100);
             }
          });
          $('.navbar .tag-button, .navbar .tag-button a').click(function(e) {
             e.stopPropagation();
             if ($('.navbar .tag-button .dropdown-menu:visible').length > 0) {
                $('.navbar .tag-button .dropdown-menu').stop().fadeOut();
             } else {
                that.closeOpenedDropdown();
                $('.navbar .tag-button .dropdown-menu').fadeIn(100);
             }
          });
      },

      closeOpenedDropdown: function() {
         if ($('.profile-infos .dropdown-menu:visible').length > 0) {
            $('.profile-infos .dropdown-menu').stop().fadeOut();
         }
         if ($('.navbar .tag-button .dropdown-menu:visible').length > 0) {
            $('.navbar .tag-button .dropdown-menu').stop().fadeOut();
         }
         if ($('#notifications .dropdown-menu:visible').length > 0) {
            $('#notifications .dropdown-menu').stop().fadeOut();
         }
         if ($('.dropdown-header-menu').hasClass('in')) {
            $('.dropdown-header-menu').removeClass('in').addClass('collapse');
         }
      }
   };

   return User;
});