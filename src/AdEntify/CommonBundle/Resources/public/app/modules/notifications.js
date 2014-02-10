/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/07/2013
 * Time: 15:13
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/common',
   'modules/user',
   'modules/photos',
   'modules/photo'
], function(app, Common, User, Photos, Photo) {

   var Notifications = app.module();

   Notifications.Model = Backbone.Model.extend({

      initialize: function() {
         this.listenTo(this, {
            'add': this.setup
         });
      },

      defaults: {
         rootUrl: app.beginUrl + app.root
      },

      toJSON: function() {
         return { notification: {
            '_token': this.get('_token'),
            'status': this.get('status')
         }}
      },

      setup: function() {
         if (this.has('author') ) {
            this.set('authorModel', new User.Model(this.get('author')));
         }
         if (this.has('owner') ) {
            this.set('ownerModel', new User.Model(this.get('owner')));
         }
         if (this.get('object_type') === "AdEntify\\CoreBundle\\Entity\\Photo") {
            this.set('photoLink', app.beginUrl + app.root + $.t('routing.photo/id/', {id: this.get('object_id') }));
         }
         if (this.has('brand')) {
            var brandModule = require('modules/brand');
            this.set('brandModel', new brandModule.Model(this.get('brand')));
         }
         if (this.has('photos') && this.get('photos').length > 0) {
            var photos = new Photos.Collection();
            photos.add(new Photo.Model(this.get('photos')[0]));
            this.set('photosCollection', photos);
         }
      },

      read: function() {
         if (this.get('status') != 'read') {
            this.set('status', 'read');
            this.url = Routing.generate('api_v1_put_notification', { id: this.get('id') });
            var that = this;
            this.getToken('notification_item', function() {
               that.save(null, {
                  success: function() {
                     app.trigger('notifications:read', that);
                  }
               });
            });
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
      },

      comparator: function(action) {
         return -action.get('id');
      }
   });

   Notifications.Views.Item = Backbone.View.extend({
      template: "notifications/item",
      tagName: "li",

      serialize: function() {
         return {
            model: this.model,
            getI18nMessage: this.getI18nMessage
         };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      notificationRead: function() {
         this.model.read();
      },

      getI18nMessage: function() {
         var options = this.model.has('message_options') ? _.extend({}, this.model.get('message_options')) : {};

         if (this.model.has('authorModel')) {
            options.authorLink = this.model.get('authorModel').get('link');
            options.author = this.model.get('authorModel').get('fullname');
         }
         if (this.model.has('ownerModel')) {
            options.ownerLink = this.model.get('ownerModel').get('link');
            options.owner = this.model.get('ownerModel').get('fullname');
         }
         if (this.model.has('brandModel')) {
            options.brandLink = this.model.get('brandModel').get('link');
            options.brandName = this.model.get('brandModel').get('name');
         }
         if (this.model.has('photoLink')) {
            options.photoLink = this.model.get('photoLink');
         }

         return $.t(this.model.get('message'), options);

      },

      showPhoto: function(evt) {
         Photos.Common.showPhoto(evt, null, this.model.get('object_id'));
      },

      events: {
         "click .button-read": "notificationRead",
         "click a[href]": "notificationRead",
         'click a[data-photo-link]': 'showPhoto'
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
         this.listenTo(app, 'notifications:read', function() {
            var count = that.notifications.unreadCount();
            if (count == 0) {
               this.render();
            } else {
               $('.notifications-count').html(count);
            }
         });
         this.listenTo(app, 'notifications:click', this.toggleNotifications);
      },

      pollNotifications: function(notifications) {
         var that = this;
         if (app.appState().getCurrentUserId() > 0) {
            this.currentNotificationsCount = notifications.length;
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
                  if (that.currentNotificationsCount != collection.length)
                     that.render();
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
         }
      },

      stopPolling: function() {
         clearTimeout(this.pollTimeout);
      },

      toggleNotifications: function(e) {
         e.stopPropagation();
         // Hide
         if ($(this.el).find('.dropdown-menu:visible').length > 0) {
            $(this.el).find('.dropdown-menu').stop().fadeOut();
         }
         // Show
         else {
            var that = this;
            User.Dropdown.closeOpenedDropdown(e);
            $(this.el).find('.dropdown-menu').hide().fadeIn(100, function() {
               setTimeout(function() {
                  that.notifications.each(function(notification) {
                     notification.read();
                  });
               }, 1000);
            });
         }
      },

      events: {
         "click .notifications-button": "toggleNotifications"
      }
   });

   return Notifications;
});