/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/07/2013
 * Time: 15:13
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/common"
], function(app, Common) {

   var Notifications = app.module();

   Notifications.Model = Backbone.Model.extend({

      initialize: function() {
         this.listenTo(this, {
            'change': this.setup,
            'add': this.setup
         });
      },

      toJSON: function() {
         return { notification: {
            '_token': this.get('_token'),
            'status': this.get('status')
         }}
      },

      setup: function() {
         if (this.has('author') && this.get('author')['id']) {
            this.set('authorLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('author')['id']}));
         }
         if (this.has('message_options') && this.get('message_options')) {
            var json = $.parseJSON(this.get('message_options'));
            if (json.author) {
               this.set('authorFullname', $.parseJSON(this.get('message_options')).author);
            }
         }
         if (this.get('object_type') === "AdEntify\\CoreBundle\\Entity\\Photo") {
            this.set('photoLink', app.beginUrl + app.root + $.t('routing.photo/id/', {id: this.get('object_id') }));
         }
      }
   });

   Notifications.Collection = Backbone.Collection.extend({
      model: Notifications.Model,

      unreadCount: function() {
         var unreadNotifications = 0;
         this.each(function(notification) {
            if (notification.get('status') == 'unread')
               unreadNotifications++;
         });
         return unreadNotifications;
      }
   });

   Notifications.Views.Item = Backbone.View.extend({
      template: "notifications/item",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      notificationRead: function() {
         this.model.set('status', 'read');
         this.model.url = Routing.generate('api_v1_put_notification', { id: this.model.get('id') });
         var that = this;
         this.model.getToken('notification_item', function() {
            that.model.save(null, {
               success: function() {
                  //app.trigger('notifications:delete', that.model);
               }
            });
         });
      },

      events: {
         "click .button-read": "notificationRead",
         "click a[href]": "notificationRead"
      }
   });

   Notifications.Views.List = Backbone.View.extend({
      template: "notifications/list",
      pollTimeout: null,

      serialize: function() {
         return { notifications: this.notifications };
      },

      beforeRender: function() {
         this.options.notifications.each(function(notification) {
            this.insertView(".notifications-list", new Notifications.Views.Item({
               model: notification
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         var that = this;
         this.notifications = this.options.notifications;
         this.listenTo(this.options.notifications, {
            "add": this.render,
            "remove": this.render
         });
         // Start polling
         this.pollNotifications(this.options.notifications);
         this.listenTo(app, 'notifications:delete', function(model) {
            if (model)
               that.notifications.remove(model);
         });
      },

      pollNotifications: function(notifications) {
         var that = this;
         notifications.fetch({
            url: Routing.generate('api_v1_get_user_notifications', { id: app.appState().getCurrentUserId() }),
            success: function(collection) {
               if (collection.length == 0) {
                  app.useLayout().setView('.alert-notifications', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('notification.noNotifications'),
                     showClose: true
                  })).render();
               }
               // Set a new timeout
               that.pollTimeout = setTimeout(function() {
                  that.pollNotifications(notifications);
               }, app.secondsBetweenPoll * 1000);
            },
            error: function() {
               // Error, set a new timeout for 5 seconds
               that.pollTimeout = setTimeout(function() {
                  that.pollNotifications(notifications);
               }, 5000);
            }
         });
      },

      stopPolling: function() {
         clearTimeout(this.pollTimeout);
      },

      toggleNotifications: function(e) {
         if ($(e.currentTarget).hasClass('active')) {
            $(this.el).find('.popover').stop().fadeOut();
         } else {
            $(this.el).find('.popover').stop().fadeIn();
         }
      },

      events: {
         "click .notifications-button": "toggleNotifications"
      }
   });

   return Notifications;
});