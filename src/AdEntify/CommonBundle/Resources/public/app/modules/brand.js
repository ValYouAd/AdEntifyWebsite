/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/reward',
   'modules/common',
   'jquery.serializeJSON'
], function(app, Reward, Common) {

   var Brand = app.module();

   Brand.Model = Backbone.Model.extend({

      initialize: function() {
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });
         this.setup();
      },

      toJSON: function() {
         var jsonAttributes = jQuery.extend(true, {}, this.attributes);
         delete jsonAttributes.link;
         return { brand: jsonAttributes };
      },

      setup: function() {
         this.set('link', app.beginUrl + app.root + $.t('routing.brand/slug/', { slug: this.get('slug') }));
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

   Brand.Views.Content = Backbone.View.extend({
      template: 'brand/content',

      serialize: function() {
         return {
            collection: this.options.brands,
            brandsCount: typeof this.options.brands !== 'undefined' ? this.options.brands.length : 0,
            title: typeof this.options.title !== 'undefined' ? this.options.title : $.t('brand.pageTitle'),
            hasData: this.loaded && this.options.brands.length > 0,
            loaded: this.loaded
         };
      },

      beforeRender: function() {
         if (this.showFilters && !this.getView('.filters-wrapper')) {
            var brandFiltersView = new Brand.Views.Filter({
               brands: this.options.brands,
               brandsSuccess: this.options.brandsSuccess,
               brandsError: this.options.brandsError
            });
            this.setView('.filters-wrapper', brandFiltersView);
            var that = this;
            brandFiltersView.on('loading', function() {
               that.$('#loading-brands').fadeIn('fast');
            }).on('loaded', function() {
               that.$('#loading-brands').fadeOut('fast');
            });
         }

         if (!this.getView('.brands-wrapper')) {
            this.setView('.brands-wrapper', new Brand.Views.List({
               brands: this.options.brands,
               emptyDataMessage: this.options.emptyDataMessage
            }));
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.options.brands, {
            'sync': function() {
               this.$('#loading-brands').fadeOut('fast');
            }
         });
         this.showFilters = typeof this.options.showFilters !== 'undefined' ? this.options.showFilters : true;
         app.trigger('domchange:title', $.t('brand.pageTitle'));
         app.trigger('domchange:description', $.t('brand.pageDescription'))
      }
   });

   Brand.Views.List = Backbone.View.extend({
      template: "brand/list",
      loaded: false,
      showTagsCount: true,
      showAllButton: false,

      serialize: function() {
         return {
            collection: this.options.brands,
            brandsCount: typeof this.options.brands !== 'undefined' ? this.options.brands.length : 0,
            title: typeof this.options.title !== 'undefined' ? this.options.title : $.t('brand.pageTitle'),
            hasData: this.loaded && this.options.brands.length > 0,
            loaded: this.loaded,
            showAllButton: this.showAllButton
         };
      },

      initialize: function() {
         this.showTagsCount = typeof this.options.showTagsCount !== 'undefined' ? this.options.showTagsCount : false;
         this.showViewMore = typeof this.options.showViewMore !== 'undefined' ? this.options.showViewMore : false;
         this.listenTo(this.options.brands, {
            'sync': function() {
               this.$('#loading-brands').fadeOut('fast');
               this.loaded = true;
               this.render();
            }
         });
      },

      beforeRender: function() {
         if (this.showFilters && !this.getView('.filters-wrapper')) {
            var brandFiltersView = new Brand.Views.Filter({
               brands: this.options.brands,
               brandsSuccess: this.options.brandsSuccess,
               brandsError: this.options.brandsError
            });
            this.setView('.filters-wrapper', brandFiltersView);
            var that = this;
            brandFiltersView.on('loading', function() {
               that.$('#loading-brands').fadeIn('fast');
            }).on('loaded', function() {
               that.$('#loading-brands').fadeOut('fast');
            });
         }

         this.options.brands.each(function(brand) {
            if (brand.has('small_logo_url')) {
               this.insertView("#brands", new Brand.Views.Item({
                  model: brand,
                  showTagsCount: this.showTagsCount,
                  tagName: this.showTagsCount ? "li class='col-xs-6 col-sm-4 col-md-2'" : "li class='col-xs-6 col-md-4'"
               }));
            }
         }, this);
         if (this.loaded && this.options.brands.length === 0) {
            this.setView('.alert-brands', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: typeof this.options.emptyDataMessage !== 'undefined' ? this.options.emptyDataMessage : $.t('brand.noUserBrands'),
               showClose: false
            }));
         }

         if (this.options.brands.hasNextPage() && this.showViewMore) {
            this.showAllButton = true;
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },


      viewMore: function() {
         var brands = this.options.brands.clone(new Brand.Collection());
         var brandsListView = new Brand.Views.List({
            showAlert: true,
            brands: brands
         });
         var Pagination = require('modules/pagination');
         var modal = new Common.Views.Modal({
            view: brandsListView,
            showFooter: false,
            showHeader: true,
            title: 'brand.modalTitle',
            modalDialogClasses: 'small-modal-dialog',
            isPaginationEnabled: true,
            paginationCollection: brands,
            paginationModel: new Pagination.Model({
               buttonText: 'brand.loadMore'
            })
         });
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      },

      events: {
         'click .viewMore': 'viewMore'
      }
   });

   Brand.Views.Item = Backbone.View.extend({
      template: 'brand/item',
      tagName: "li class='col-xs-6 col-sm-4 col-md-2'",

      serialize: function() {
         return {
            model: this.model,
            showTagsCount: this.options.showTagsCount
         };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      },

      afterRender: function() {
         $(this.el).i18n();
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
         if (!this.getView('.followers')) {
            this.setView('.followers', new User.Views.List({
               users: this.followers,
               noUsersMessage: 'profile.noFollowers',
               moreMessage: 'profile.moreFollowers'
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
               that.followers.fetch();
            });
         }
         if (!this.getView('.rewards') && this.loaded) {
            this.setView('.rewards', new Reward.Views.List({
               rewards: this.options.rewards,
               emptyMessage: $.t('brand.noRewards', { name: this.model.get('name') }),
               itemTemplate: 'reward/userItem',
               brand: this.model,
               showAllButton: true
            }));
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
         };
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

   Brand.Views.Create = Backbone.View.extend({
      template: 'brand/create',

      serialize: function() {
         return {
            categories: this.options.categories
         };
      },

      initialize: function() {
         this.listenTo(this.options.categories, 'sync', this.render);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.categories.length > 0) {
            this.$('.categories').select2();
         }
      },

      formSubmit: function(e) {
         e.preventDefault();

         var btn = this.$('button[type="submit"]');
         btn.button('loading');

         var that = this;

         var brand = new Brand.Model(this.$('form').serializeJSON());
         brand.set('categories', this.$('.categories').select2('val'));
         brand.url = Routing.generate('api_v1_post_brand');
         brand.getToken('brand_item', function() {
            brand.save(null, {
               success: function() {
                  that.setView('.alert-success-brand', new Common.Views.Alert({
                     cssClass: Common.alertSuccess,
                     message: $.t('brand.createSuccessfull'),
                     showClose: true
                  })).render();
                  that.$('form').fadeOut('fast', function() {
                     that.$('.alert-success-brand').fadeIn();
                  });
                  that.trigger('created');
               },
               complete: function() {
                  btn.button('reset');
               },
               error: function(e, r) {
                  var json = $.parseJSON(r.responseText);
                  that.setView('.alert-add-brand', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: json ? Common.Tools.getHtmlErrors(json) : $.t('brand.errorCreate'),
                     showClose: true
                  })).render();
               }
            });
         });
      },

      loadFacebookData: function() {
         var that = this;
         var btn = this.$('input[name="facebook_url"]');
         $.ajax({
            url: 'http://graph.facebook.com/?id=' + btn.val(),
            success: function(json) {
               if (json && typeof json.error === 'undefined') {
                  if (json.name)
                     that.$('input[name="name"]').val(json.name);
                  if (json.description)
                     that.$('textarea[name="description"]').html(json.description);
                  if (json.website)
                     that.$('input[name="website_url"]').val(json.website);
                  if (json.id)
                  {
                     var img = 'https://graph.facebook.com/' + json.id + '/picture?width=2000';
                     that.$('input[name="original_logo_url"]').val(img);
                     that.$('.brand-logo').attr('src', img).fadeIn();
                  }
               }
            }
         });
      },

      events: {
         'click button[type="submit"]': 'formSubmit',
         'click .facebookButton': 'loadFacebookData'
      }
   });

   Brand.Views.Filter = Backbone.View.extend({
      template: 'brand/filters',
      currentFilter: null,

      initialize: function() {
         this.brandsSuccess = this.options.brandsSuccess;
         this.brandsError = this.options.brandsError;
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      nameFilter: function() {
         var activate = this.activateFilter(this.$('.name-filter').parent());
         this.loadBrands(activate ? '?orderBy=name&way=DESC' : '?orderBy=name&way=ASC');
      },

      numberOfTags: function() {
         var activate = this.activateFilter(this.$('.number-of-tags-filter').parent());
         this.loadBrands(activate ? '?orderBy=number-of-tags&way=DESC' : '');
      },

      loadBrands: function(query) {
         this.trigger('loading');
         var that = this;
         this.options.brands.fetch({
            url: this.getOriginalCollectionUrl() + query,
            reset: true,
            success: this.brandsSuccess,
            error: this.brandsError,
            complete: function() {
               that.trigger('loaded');
            }
         });
      },

      activateFilter: function(newFilter) {
         if (newFilter.hasClass('active')) {
            this.activeFilter = null;
            newFilter.removeClass('active');
            return false;
         }

         if (this.activeFilter) {
            this.activeFilter.removeClass('active');
         }
         this.activeFilter = newFilter;
         this.activeFilter.addClass('active');
         return true;
      },

      getOriginalCollectionUrl: function() {
         return Routing.generate('api_v1_get_brands');
      },

      events: {
         'click .name-filter': 'nameFilter',
         'click .number-of-tags-filter': 'numberOfTags'
      }
   });

   Brand.Views.Rewards = Backbone.View.extend({
      template: 'brand/rewards',
      fans: new Reward.Collection(),
      bronze: new Reward.Collection(),
      silver: new Reward.Collection(),
      gold: new Reward.Collection(),

      serialize: function() {
         return {
            brand: this.options.brand
         };
      },

      initialize: function() {
         this.groupRewards();
         this.listenTo(this.options.rewards, 'sync', function() {
            this.groupRewards();
            this.render();
         });
      },

      beforeRender: function() {
         if (!this.getView('#fan')) {
            this.setView('#fan', new Reward.Views.Users({
               users: this.fans
            }));
         }
         if (!this.getView('#bronze')) {
            this.setView('#bronze', new Reward.Views.Users({
               users: this.bronze
            }));
         }
         if (!this.getView('#silver')) {
            this.setView('#silver', new Reward.Views.Users({
               users: this.silver
            }));
         }
         if (!this.getView('#gold')) {
            this.setView('#gold', new Reward.Views.Users({
               users: this.gold
            }));
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      groupRewards: function() {
         this.fans.reset();
         this.bronze.reset();
         this.silver.reset();
         this.gold.reset();

         this.options.rewards.each(function(reward) {
            switch (reward.get('type')) {
               case Reward.Common.Addict:
                  this.fans.add(reward);
                  break;
               case Reward.Common.Bronze:
                  this.bronze.add(reward);
                  break;
               case Reward.Common.Silver:
                  this.silver.add(reward);
                  break;
               case Reward.Common.Gold:
                  this.gold.add(reward);
                  break;
            }
         }, this);
      },

      events: {
         'click .nav-tabs a': function(e) {
            e.preventDefault();
            $(this).tab('show');
         }
      }
   });

   return Brand;
});