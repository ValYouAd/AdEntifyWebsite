/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/reward'
], function(app, Reward) {

   var Brand = app.module();

   Brand.Model = Backbone.Model.extend({

      initialize: function() {
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });
         this.setup();
      },

      setup: function() {
         this.set('brandLink', app.beginUrl + app.root + $.t('routing.brand/slug/', { slug: this.get('slug') }));
         this.set('profilePicRootUrl', app.beginUrl + '/');
      },

      urlRoot: Routing.generate('api_v1_get_brands'),

      changeFollowersCount: function(follow) {
         if (follow) {
            this.set('followers_count', this.get('followers_count') + 1);
         } else {
            if (this.get('followers_count') > 0)
               this.set('followers_count', this.get('followers_count') - 1);
         }
      }
   });

   Brand.Collection = Backbone.Collection.extend({
      model: Brand.Model,
      cache: true,
      url: Routing.generate('api_v1_get_brands')
   });

   Brand.Views.Item = Backbone.View.extend({
      template: "brand/item",
      tagName: "li class='col-xs-6 col-sm-4 col-md-2'",

      serialize: function() {
         return { model: this.model };
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Brand.Views.List = Backbone.View.extend({
      template: "brand/list",

      serialize: function() {
         return {
            collection: this.options.brands,
            brandsCount: typeof this.options.brands !== 'undefined' ? this.options.brands.length : 0
         };
      },

      beforeRender: function() {
         this.options.brands.each(function(brand) {
            this.insertView("#brands", new Brand.Views.Item({
               model: brand
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.options.brands, {
            "sync": this.render
         });
         app.trigger('domchange:title', $.t('brand.pageTitle'));
         app.trigger('domchange:description', $.t('brand.pageDescription'))
      }
   });

   Brand.Views.MenuLeft = Backbone.View.extend({
      template: 'brand/menuLeft',
      loaded: false,

      serialize: function() {
         return {
            model: this.model,
            lastPhoto: this.lastPhoto,
            photosCount: this.photosCount,
            categories: this.categories
         };
      },

      beforeRender: function() {
         var that = this;
         var User = require('modules/user');
         if (!this.getView('.rewards') && this.loaded) {
            this.setView('.rewards', new Reward.Views.List({
               rewards: new Reward.Collection(),
               emptyMessage: $.t('brand.noRewards', { name: this.model.get('name') })
            }));
         }
         if (!this.getView('.followers')) {
            this.setView('.followers', new User.Views.List({
               users: this.followers,
               noUsersMessage: 'profile.noFollowers'
            }));
         }
         if (!this.getView('.followings')) {
            this.setView('.followings', new User.Views.List({
               users: this.followings,
               noUsersMessage: 'profile.noFollowings'
            }));
         }
         if (!this.getView('.follow-button')) {
            var followButtonView = new Brand.Views.FollowButton({
               brand: this.model,
               slug: this.slug
            });
            this.setView('.follow-button', followButtonView);
            followButtonView.on('follow', function(follow) {
               that.model.changeFollowersCount(follow);
               that.render();
            });
            followButtonView.on('followed', function() {
               that.followers.fetch();
            });
         }
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.loaded) {
            var that = this;
            this.$('.loading-gif-container').fadeOut(200, function() {
               that.$('.profile-aside').fadeIn('fast');
            });
         }
      },

      initialize: function() {
         this.lastPhoto = null;
         this.followers = this.options.followers;
         this.followings = this.options.followings;
         this.categories = this.options.categories;
         this.slug = this.options.slug;
         this.listenTo(this.model, 'sync', function() {
            this.loaded = true;
            this.render();
         });
         this.options.photos.once('sync', function(collection) {
            if (collection.length > 0) {
               this.lastPhoto = collection.first();
               this.photosCount = this.options.photos.total;
               this.render();
            }
         }, this);
      }
   });

   Brand.Views.FollowButton = Backbone.View.extend({
      template: 'brand/followButton',
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
         if (this.options.slug && this.options.brand) {
            this.slug = this.options.slug;
            this.brand = this.options.brand;
            var that = this;
            if (app.appState().isLogged()) {
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_get_brand_is_following', { 'slug': that.slug }),
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
         // Favorite photo
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_brand_follower', { slug: that.slug }),
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  type: 'POST',
                  success: function() {
                     that.trigger('followed');
                  }
               });
            }
         });
         this.follow = !this.follow;

         this.render();
         this.trigger('follow', this.follow);
      }
   });

   return Brand;
});