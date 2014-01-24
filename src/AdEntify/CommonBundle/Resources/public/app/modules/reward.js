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

      serialize: function() {
         return {
            visible: this.visible
         };
      },

      initialize: function() {
         this.listenTo(this.options.rewards, { 'sync': function() {
            if (this.options.rewards.length == 0) {
               this.setView('.rewards-alert', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: this.options.emptyMessage,
                  showClose: true
               }));
            } else {
               this.removeView('.rewards-alert');
            }
            this.render();
         }});
      },

      beforeRender: function() {
         this.options.rewards.each(function(reward) {
            this.insertView('.rewards', new Reward.Views.Item({
               model: reward
            }));
         }, this);
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
         this.template = typeof this.options.template !== 'undefined' ? this.options.template : this.template;
      }
   });

   return Reward;
});