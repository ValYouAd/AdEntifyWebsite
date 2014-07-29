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
   'modules/common',
   'modules/reward',
   'modules/brand',
   'Chart',
   'daterangepicker'
], function(app, Hashtag, Common, Reward, Brand) {

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
      showFollowButton: true,
      loaded: false,

      serialize: function() {
         return {
            model: this.model,
            lastPhoto: this.lastPhoto,
            showServices: this.showServices,
            rootUrl: app.beginUrl + app.root,
            showHashtags: this.showHashtags,
            showFollowers: this.showFollowers,
            showFollowings: this.showFollowings,
            showFollowButton: this.showFollowButton,
            showRewards: this.showRewards,
            showBrands: this.showBrands
         };
      },

      beforeRender: function() {
         var that = this;
         if (!this.getView('.rewards') && this.loaded) {
            this.setView('.rewards', new Reward.Views.List({
               rewards: new Reward.Collection(),
               emptyMessage: $.t('profile.noRewards')
            }));
         }
         if (!this.getView('.brands') && this.options.brands) {
            this.setView('.brands', new Brand.Views.List({
               brands: this.options.brands,
               emptyDataMessage: $.t('profile.noBrands'),
               showTagsCount: false,
               showViewMore: true
            }));
         }
         if (!this.getView('.followings') && this.options.followings) {
            this.setView('.followings', new User.Views.List({
               users: this.options.followings,
               noUsersMessage: 'profile.noFollowings',
               moreMessage: 'profile.moreFollowings',
               showViewMore: true,
               usersType: User.Common.Followings,
               user: this.options.user
            }));
         }
         if (!this.getView('.followers') && this.options.followers) {
            this.setView('.followers', new User.Views.List({
               users: this.options.followers,
               noUsersMessage: 'profile.noFollowers',
               moreMessage: 'profile.moreFollowers',
               showViewMore: true,
               usersType: User.Common.Followers,
               user: this.options.user
            }));
         }
         if (!this.getView('.follow-button')) {
            var followButtonView = new User.Views.FollowButton({
               user: this.model
            });
            this.setView('.follow-button', followButtonView);
            followButtonView.on('follow', function(follow) {
               that.options.user.changeFollowersCount(follow);
               that.options.followers.fetch();
               that.render();
            });
         }
         if (!this.getView('.hashtags') && this.options.hashtags) {
            this.setView('.hashtags', new Hashtag.Views.List({
               hashtags: this.options.hashtags,
               showAlert: true,
               showViewMore: true
            }));
         }
         if (!this.getView('.rewards') && this.options.rewards) {
            this.setView('.rewards', new Reward.Views.List({
               rewards: this.options.rewards,
               emptyMessage: $.t('profile.noRewards'),
               showViewMore: true
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
         if (this.loaded) {
            var that = this;
            this.$('.loading-gif-container').fadeOut(200, function() {
               that.$('.profile-aside').fadeIn('fast');
            });
         }
      },

      initialize: function() {
         this.lastPhoto = null;
         this.showFollowings = typeof this.options.followings !== 'undefined';
         this.showFollowers = typeof this.options.followers !== 'undefined';
         this.showHashtags = typeof this.options.hashtags !== 'undefined';
         this.showRewards = typeof this.options.rewards !== 'undefined';
         this.showBrands = typeof this.options.brands !== 'undefined';
         this.showServices = typeof this.options.showServices !== 'undefined' ? this.options.showServices : this.showServices;
         this.showFollowButton = typeof this.options.showFollowButton !== 'undefined' ? this.options.showFollowButton : this.showFollowButton;
         this.listenTo(this.options.user, 'sync', function() {
            this.loaded = true;
            this.render();
         });
         this.options.photos.once('sync', function(collection) {
            if (collection.length > 0) {
               this.lastPhoto = collection.first();
               this.render();
            }
         }, this);
         this.listenTo(app, 'photo:removed', function() {
            this.loadData(true);
         });
         this.model = this.options.user;
         this.loadData();
      },

      loadData: function(reload) {
         var that = this;
         reload = reload || false;
         if (reload) {
            this.options.hashtags.fetch({
               url: Routing.generate('api_v1_get_user_hashtags', { id: this.options.user.get('id') })
            });
         }
         this.options.user.fetch({
            url: Routing.generate('api_v1_get_user', { id: this.options.user.get('id') }),
            success: function() {
               app.trigger('domchange:title', $.t('profile.pageTitle', { name: that.options.user.get('firstname') + " " + that.options.user.get('lastname') }));
               $('#loading-photo').fadeOut('fast', function() {
                  $('#profile').fadeIn();
               });
            }
         });
      },

      events: {
         'click .followings-link': function() {
            User.Common.showModalFollowings(this.options.followings);
         },
         'click .followers-link': function() {
            User.Common.showModalFollowers(this.options.followers);
         },
         'click .rewards-hiw-link': Reward.Common.showPresentation
      }
   });

   User.Views.List = Backbone.View.extend({
      template: "user/list",
      showAllButton: false,

      serialize: function() {
         return {
            visible: this.visible,
            showAllButton: this.showAllButton,
            moreMessage: this.moreMessage
         };
      },

      initialize: function() {
         this.listenTo(this.options.users, { 'sync': function() {
            if (this.options.users.length === 0) {
               this.setView('.users-alert', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t(this.options.noUsersMessage),
                  showClose: false
               }));
            } else {
               this.removeView('.users-alert');
            }
            this.render();
         }});

         this.moreMessage = typeof this.options.moreMessage !== 'undefined' ? $.t(this.options.moreMessage) : '';
         this.showViewMore = typeof this.options.showViewMore !== 'undefined' ? this.options.showViewMore : false;
      },

      beforeRender: function() {
         if (this.options.users.hasNextPage() && this.showViewMore) {
            this.showAllButton = true;
         }
         this.options.users.each(function(user) {
            this.insertView('.users', new User.Views.Item({
               model: user
            }));
         }, this);
      },

      viewMore: function() {
         if (this.options.usersType == User.Common.Followings)
            User.Common.showModalFollowings(undefined, this.options.user);
         else if (this.options.usersType === User.Common.Followers)
            User.Common.showModalFollowers(undefined, this.options.user);
      },

      events: {
         'click .viewMore': 'viewMore'
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
         };
      },

      afterRender: function() {
         $(this.el).i18n();
         var that = this;
         this.$('button[data-followed]').hover(function() {
            if (that.follow) {
               $(this).html($.t('profile.unfollow'));
            }
         }, function() {
            if (that.follow) {
               $(this).html($.t('profile.followed'));
            } else {
               $(this).html($.t('profile.follow'));
            }
         });
      },

      initialize: function() {
         if (this.options.user) {
            this.user = this.options.user;
            var that = this;
            if (app.appState().isLogged()) {
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_get_user_is_followed', { 'id': that.options.user.get('id') }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           that.follow = response.followed;
                           that.render();
                        },
                        error: function() {
                           that.follow = false;
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
         var btn = this.$('.follow-button');
         btn.button('loading');
         if (app.appState().isLogged()) {
            // Follow user
            var that = this;
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     url: Routing.generate('api_v1_post_user_follower', { id: that.user.get('id') }),
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     type: 'POST',
                     data: { userId: that.user.get('id') },
                     success: function() {
                        that.follow = !that.follow;
                        that.render();
                        that.trigger('follow', that.follow);
                     }
                  }).always(function() {
                     btn.button('reset');
                  });
               }
            });
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.follow');
         }
      }
   });

   User.Views.Dashboard = Backbone.View.extend({
      template: 'user/dashboard',
      chartData: null,
      rawData: null,

      serialize: function() {
         return {
            rawData: this.rawData
         };
      },

      beforeRender: function() {
         if (!this.getView('.my-history-content')) {
            var Action = require('modules/action');
            this.setView('.my-history-content', new Action.Views.List({
               actions: this.options.actions,
               routeName: 'api_v1_get_user_actions',
               emptyMessage: 'startTagging'
            }));
         }
         if (!this.getView('.user-credits-table')) {
            this.creditsTableView = new User.Views.CreditsTable();
            this.setView('.user-credits-table', this.creditsTableView);
         }
      },

      afterRender: function() {
         var that = this;
         $(this.el).i18n();

         if (this.chartData) {
            var ctx = this.$('.userPhotosChart').get(0).getContext('2d');
            var chart = new Chart(ctx);
            chart.Pie(this.chartData, {
               animationEasing: 'easeInOutCubic'
            });
         }

         if (app.useLayout().getView('.user-points').points) {
            this.$('.user-points-red').html($.t('user.totalPoints', { points: app.useLayout().getView('.user-points').points}));
         } else {
            app.useLayout().getView('.user-points').on('pointsUpdated', function() {
               that.$('.user-points-red').html($.t('user.totalPoints', { points: app.useLayout().getView('.user-points').points}));
            });
         }

         this.$('input[name="daterange"]').daterangepicker(
            {
               ranges: Common.Tools.getDaterangepickerRanges(),
               opens: 'right',
               format: $.t('common.formatDateShort'),
               startDate: moment().startOf('month'),
               endDate: moment().endOf('month')
            },
            function(start, end) {
               that.creditsTableView.credits.refresh(start, end);
            });
      },

      initialize: function() {
         var that = this;
         this.listenTo(this.options.actions, 'sync', function() {
            if (this.getView('.my-history-content'))
               this.getView('.my-history-content').render();
         });
         User.Tools.getAnalytics({
            success: function(data) {
               that.rawData = data;
               that.chartData = [
                  {
                     value: parseInt(data.taggedPhotos),
                     color: "#ff4140"
                  },
                  {
                     value: parseInt(data.untaggedPhotos),
                     color: "#e0e4ea"
                  }
               ];
               that.render();
            },
            error: function() {

            }
         });
      },

      events: {
         'click .rewards-hiw-link': Reward.Common.showPresentation
      }
   });

   User.Views.CreditsTable = Backbone.View.extend({
      template: 'user/creditsTable',

      initialize: function() {
         var that = this;
         moment.lang(app.appState().getLocale());
         this.credits = new User.Credits();
         this.listenTo(this.credits, 'sync', this.render);
         this.credits.fetch({
            success: function(credits) {
               if (credits.length === 0) {
                  that.setView('.alert-credits', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('user.noCredits')
                  })).render();
               }
            }
         });
      },

      beforeRender: function() {
         this.credits.each(function(credit) {
            this.insertView('tbody', new User.Views.CreditsRow({
               model: credit
            }));
         }, this);
      }
   });

   User.Views.CreditsRow = Backbone.View.extend({
      template: 'user/creditsRow',
      tagName: 'tr',

      serialize: function() {
         return {
            model: this.model
         };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });

   User.Views.CreditsDetail = Backbone.View.extend({
      template: 'user/creditsDetail',

      serialize: function() {
         return {
            rootUrl: app.beginUrl + app.root,
            date: this.options.date
         };
      },

      initialize: function() {
         var that = this;
         moment.lang(app.appState().getLocale());
         this.listenTo(this.options.credits, 'sync', this.render);
         this.options.credits.fetch({
            url: Routing.generate('api_v1_get_user_credits_by_date', { date: this.options.date }),
            success: function(credits) {
               if (credits.length === 0) {
                  that.setView('.alert-credits', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('user.noCreditsDetail')
                  })).render();
               }
            }
         });
      },

      beforeRender: function() {
         this.options.credits.each(function(credit, i) {
            this.insertView('tbody', new User.Views.CreditsDetailRow({
               model: credit,
               index: i + 1
            }));
         }, this);
      }
   });

   User.Views.CreditsDetailRow = Backbone.View.extend({
      template: 'user/creditsDetailRow',
      tagName: 'tr',

      serialize: function() {
         return {
            model: this.model,
            index: this.options.index
         };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      },

      viewPhoto: function(evt) {
         var Photos = require('modules/photos');
         Photos.Common.showPhoto(evt, null, this.model.get('photoId'));
      },

      events: {
         'click .photo-link': 'viewPhoto'
      }
   });

   User.Credit = Backbone.Model.extend({
      initialize: function() {
         this.listenTo(this, 'sync', this.setup);
         this.setup();
      },

      setup: function() {
         if (this.has('date')) {
            this.set('formatDate', moment(this.get('date'), 'YYYY-MM-DD').format('L'));
         }
         this.set('link', app.beginUrl + app.root + $.t('routing.my/dashboard/credits/date/', { date: moment(this.get('date'), 'YYYY-MM-DD').format('YYYY-MM-DD') }));
         if (this.has('photoId'))
            this.set('photoLink', app.beginUrl + app.root + $.t('routing.photo/id/', { id: this.get('photoId') }));
         if (this.has('brandSlug') && this.get('brandSlug'))
            this.set('brandLink', app.beginUrl + app.root + $.t('routing.brand/slug/', { slug: this.get('brandSlug') }));
      }
   });

   User.Credits = Backbone.Collection.extend({
      model: User.Credit,
      url: Routing.generate('api_v1_get_user_credits_by_date_range'),

      refresh: function(start, end) {
         var options = {};
         if (start)
            options.begin = moment(start).format('YYYY-MM-DD');
         if (end)
            options.end = moment(end).format('YYYY-MM-DD');
         this.url = Routing.generate('api_v1_get_user_credits_by_date_range', options);
         this.fetch();
      }
   });

   User.Views.Points = Backbone.View.extend({
      template: 'user/points',
      points: null,
      animate: false,

      serialize: function() {
         return {
            points: this.points,
            animate: this.animate
         };
      },

      initialize: function() {
         this.listenTo(app, 'tagMenuTools:tagAdded', function() {
            this.updatePoints(true);
         });
         this.listenTo(app, 'tag:deleted', function() {
            this.updatePoints(false);
         });
         this.updatePoints(false);
      },

      afterRender: function() {
         this.animate = false;
      },

      updatePoints: function(animate) {
         var that = this;
         this.animate = animate;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_get_user_points'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  success: function(points) {
                     that.points = points;
                     that.render();
                     that.trigger('pointsUpdated');
                  }
               });
            }
         });
      }
   });

   User.Dropdown = {
      openedDropdown: null,
      documentClickTimeout: null,

      listenClick: function() {
         this.listenDocumentClick();

         var that = this;
         $('.profile-infos .user-names, .profile-infos .user-points, .profile-infos a').click(function(e) {
             if ($('.profile-infos .dropdown-menu:visible').length > 0) {
                $('.profile-infos .dropdown-menu').stop().fadeOut();
             } else {
                that.closeOpenedDropdown(e);
                $('.profile-infos .dropdown-menu').fadeIn(100);
             }
         });
         $('.navbar .tag-button, .navbar .tag-button a').click(function(e) {
            if ($('.navbar .tag-button .dropdown-menu:visible').length > 0) {
               $('.navbar .tag-button .dropdown-menu').stop().fadeOut();
            } else {
               that.closeOpenedDropdown(e);
               $('.navbar .tag-button .dropdown-menu').fadeIn(100);
            }
         });
      },

      listenDocumentClick: function() {
         var that = this;
         $(document).on('click', function(evt) {
            that.closeOpenedDropdown(evt);
         });
      },
      closeOpenedDropdown: function(evt) {
         if ($('.profile-infos .dropdown-menu:visible').length > 0 && $(evt.target).parents('.profile-infos').length === 0) {
            $('.profile-infos .dropdown-menu').stop().fadeOut();
         }
         if ($('.navbar .tag-button .dropdown-menu:visible').length > 0 && !$(evt.target).hasClass('tag-button')) {
            $('.navbar .tag-button .dropdown-menu').stop().fadeOut();
         }
         if ($('#notifications .dropdown-menu:visible').length > 0 && $(evt.target).parents('#notifications').length === 0) {
            $('#notifications .dropdown-menu').stop().fadeOut();
         }
         if ($('.dropdown-header-menu').hasClass('in')) {
            $('.dropdown-header-menu').removeClass('in').addClass('collapse');
         }
      }
   };

   User.Views.ModalList = Backbone.View.extend({
      template: 'user/modalList'
   });

   User.Common = {
      Followings: 'followings',
      Followers: 'followers',

      showModalFollowings: function(users, user) {
         if (typeof users === 'undefined') {
            var users = new User.Collection();
            users.url = Routing.generate('api_v1_get_user_followings', { id: typeof user !== 'undefined' ? user.get('id') : currentUserId, limit: 10 });
         }
         this.showModal(users, 'user.modalFollowingsTitle', 'profile.noFollowings', false, true);
      },

      showModalFollowers: function(users, user) {
         if (typeof users === 'undefined') {
            var users = new User.Collection();
            users.url = Routing.generate('api_v1_get_user_followers', { id: typeof user !== 'undefined' ? user.get('id') : currentUserId, limit: 10 });
         }
         this.showModal(users, 'user.modalFollowersTitle', 'profile.noFollowers', false, true);
      },

      showModalTopFollowers: function() {
         var users = new User.Collection();
         users.url = Routing.generate('api_v1_get_user_top_followers');
         this.showModal(users, 'user.modalTopFollowersTitle', 'profile.noFollowers', false, true);
      },

      showModal: function(users, title, noUsersMessage, stack, forceRender, modalDialogClass) {
         stack = stack || false;
         forceRender = forceRender || false;
         modalDialogClass= modalDialogClass || 'small-modal-dialog';
         var userListView = new User.Views.List({
            users: users,
            noUsersMessage: noUsersMessage
         });
         if (users.length === 0 && typeof users.url !== 'undefined') {
            users.fetch({
               success: function() {
                  if (forceRender)
                     userListView.render();
               }
            });
         } else {
            users.trigger('sync');
            if (forceRender)
               userListView.render();
         }
         var modal = new Common.Views.Modal({
            view: userListView,
            showFooter: false,
            showHeader: true,
            title: title,
            modalDialogClasses: modalDialogClass,
            isPaginationEnabled: true,
            paginationCollection: users
         });
         if (stack) {
            var currentModal = app.useLayout().getView('#modal-container');
            // if a modal is opened, slide out to left before rendering the new one
            if (currentModal) {
               modal.on('hide', function() {
                  app.useLayout().getView('#modal-container').$('.modal-dialog').removeClass('slideOutLeft').addClass('animated slideInLeft');
               });
               currentModal.$('.modal-dialog').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
                  app.useLayout().setView('#front-modal-container', modal).render();
               }).addClass('animated slideOutLeft');
            } else {
               app.useLayout().setView('#front-modal-container', modal).render();
            }
         } else {
            Common.Tools.hideCurrentModalIfOpened(function() {
               app.useLayout().setView('#modal-container', modal).render();
            });
         }
      }
   };

   User.Tools = {
      getAnalytics: function(options) {
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_get_user_analytics'),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  success: function(data) {
                     options.success(data);
                  },
                  error: function(e, r) {
                     options.error(e, r);
                  }
               });
            }
         });
      }
   };

   return User;
});