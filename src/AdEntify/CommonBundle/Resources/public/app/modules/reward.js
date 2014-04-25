/**
 * Created by pierrickmartos on 18/12/2013.
 */
/**
 * Created with JetBrains PhpStorm.
 * Reward: pierrickmartos
 * Date: 12/06/2013
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/common'
], function(app, Common) {

   var Reward = app.module();

   Reward.Model = Backbone.Model.extend({
      initialize: function() {
         this.listenTo(this, 'sync', this.setup);
         this.setup();
      },

      setup: function() {
         if (!this.has('ownerModel')) {
            var userModule = require('modules/user');
            this.set('ownerModel', new userModule.Model(this.get('owner')));
         }
         if (!this.has('brandModel')) {
            var brandModule = require('modules/brand');
            this.set('brandModel', new brandModule.Model(this.get('brand')));
         }
      }
   });

   Reward.Collection = Backbone.Collection.extend({
      model: Reward.Model
   });

   Reward.Views.List = Backbone.View.extend({
      template: 'reward/list',
      showAllButton: false,

      serialize: function() {
         return {
            visible: this.visible,
            showAllButton: this.showAllButton
         };
      },

      initialize: function() {
         this.listenTo(this.options.rewards, { 'sync': function() {
            this.checkRewardsLength();
            this.render();
         }});
         this.checkRewardsLength();
         this.showViewMore = typeof this.options.showViewMore !== 'undefined' ? this.options.showViewMore : false;
         this.showAllButton = typeof this.options.showAllButton !== 'undefined' ? this.options.showAllButton : this.showAllButton;
      },

      checkRewardsLength: function () {
         if (this.options.rewards.length === 0) {
            this.setView('.rewards-alert', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: this.options.emptyMessage,
               showClose: false
            }));
         } else {
            this.removeView('.rewards-alert');
         }
      },

      beforeRender: function() {
         if (this.options.rewards.hasNextPage() && this.showViewMore) {
            this.showAllButton = true;
         }
         this.options.rewards.each(function(reward) {
            this.insertView('.rewards', new Reward.Views.Item({
               model: reward,
               itemTemplate: typeof this.options.itemTemplate !== 'undefined' ? this.options.itemTemplate : null
            }));
         }, this);
      },

      viewMore: function() {
         var rewards = this.options.rewards.clone(new Reward.Collection());

         var Brand = require('modules/brand');
         var rewardsViews = null;
         var Pagination = require('modules/pagination');
         var modal = null;
         if (typeof this.options.brand !== 'undefined') {
            rewardsViews = new Brand.Views.Rewards({
               rewards: rewards,
               brand: this.options.brand
            });
            modal = new Common.Views.Modal({
               view: rewardsViews,
               showFooter: false,
               showHeader: false,
               modalContentClasses: 'photoModal'
            });
         } else {
            rewardsViews = new Reward.Views.List({
               rewards:rewards
            });
            modal = new Common.Views.Modal({
               view: rewardsViews,
               showFooter: false,
               showHeader: true,
               title: 'reward.modalTitle',
               modalDialogClasses: 'small-modal-dialog',
               isPaginationEnabled: true,
               paginationCollection: rewards,
               paginationModel: new Pagination.Model({
                  buttonText: 'reward.loadMore'
               })
            });
         }
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      },

      events: {
         'click .viewMore': 'viewMore'
      }
   });

   Reward.Views.Item = Backbone.View.extend({
      template: 'reward/brandItem',
      tagName: 'li class="reward-item"',

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
         this.template = typeof this.options.itemTemplate !== 'undefined' && this.options.itemTemplate !== null ? this.options.itemTemplate : this.template;
      }
   });

   Reward.Views.Users = Backbone.View.extend({
      template: 'reward/users',

      initialize: function() {
         this.listenTo(this.options.users, 'sync', function() {
            this.render();
         });
      },

      beforeRender: function() {
         if (this.options.users.length === 0) {
            this.setView('.users-alert', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t('brand.noUserRewards')
            }));
         } else {
            this.removeView('.users-alert');
         }
         this.options.users.each(function(user) {
            this.insertView('.users', new Reward.Views.User({
               model: user
            }));
         }, this);
      }
   });

   Reward.Views.User = Backbone.View.extend({
      template: 'reward/user',

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });

   Reward.Common = {
      Addict: 'addict',
      Gold: 'gold',
      Silver: 'silver',
      Bronze: 'bronze',

      showPresentation: function() {
         var presentationView = new Reward.Views.Presentation();
         var modal = new Common.Views.Modal({
            view: presentationView,
            showFooter: false,
            showHeader: false,
            modalContentClasses: 'photoModal'
         });
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      }
   };

   Reward.Views.Presentation = Backbone.View.extend({
      template: 'reward/presentation',

      afterRender: function() {
         $(this.el).i18n();
      },

      events: {
         'click .nav-tabs a': function(e) {
            e.preventDefault();
            $(this).tab('show');
         }
      }
   });

   return Reward;
});