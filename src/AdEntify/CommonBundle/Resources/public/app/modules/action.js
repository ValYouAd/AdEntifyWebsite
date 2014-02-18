/**
 * Created by pierrickmartos on 25/10/2013.
 */
define([
   'app',
   'modules/user',
   'modules/photo',
   'modules/photos'
], function(app, User, Photo, Photos) {

   var Action = app.module();

   Action.Model = Backbone.Model.extend({

      initialize: function() {
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });
         this.setup();
      },

      setup: function() {
         if (this.has('photos')) {
            if (this.get('photos').length > 1) {
               var photos = new Photos.Collection();
               var i = 1;
               _.each(this.get('photos'), function(photo) {
                  if (i <= 3) {
                     var model = new Photo.Model(photo);
                     model.setup();
                     photos.add(model);
                     i++;
                  } else return false;
               });
               this.set('photosCollection', photos);
            } else if (this.get('photos').length == 1) {
               var model = new Photo.Model(this.get('photos')[0]);
               model.setup();
               this.set('photo', model);
            }
         }
         if (this.has('brand')) {
            var brandModule = require('modules/brand');
            this.set('brandModel', new brandModule.Model(this.get('brand')));
         }
         if (this.has('author')) {
            this.set('authorModel', new User.Model(this.get('author')));
         }
         if (this.has('target')) {
            this.set('targetModel', new User.Model(this.get('target')));
         }
      },

      urlRoot: Routing.generate('api_v1_get_actions')
   });

   Action.Collection = Backbone.Collection.extend({
      model: Action.Model,
      url: Routing.generate('api_v1_get_actions'),
      comparator: function(action) {
         return -action.get('id');
      }
   });

   Action.Views.Item = Backbone.View.extend({
      template: "action/item",
      tagName: "li class='action-item'",

      serialize: function() {
         return {
            model: this.model,
            getI18nMessage: this.getI18nMessage
         };
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.template = typeof this.options.template !== 'undefined' ? this.options.template : this.template;
         this.tagName = typeof this.options.tagName !== 'undefined' ? this.options.tagName : this.tagName;
         this.listenTo(this.model, 'change', this.render);
      },

      clickPhotoLink: function(evt) {
         var photoId = $(evt.currentTarget).data('photo-id');
         if (photoId) {
            var photo = this.model.get('photosCollection').find(function(p) {
               if (p.get('id') == photoId) {
                  return p;
               }
            });
            Photos.Common.showPhoto(evt, photo, 0, true);
         } else {
            Photos.Common.showPhoto(evt, this.model.get('photo'), 0, true);
         }
      },

      getI18nMessage: function() {
         var options = this.model.has('message_options') ? _.extend({}, this.model.get('message_options')) : {};

         if (this.model.has('authorModel')) {
            options.authorLink = this.model.get('authorModel').get('link');
            options.author = this.model.get('authorModel').get('fullname');
         }
         if (this.model.has('targetModel')) {
            options.targetLink = this.model.get('targetModel').get('link');
            options.target = this.model.get('targetModel').get('fullname');
         }
         if (this.model.has('brandModel')) {
            options.brandLink = this.model.get('brandModel').get('link');
            options.brandName = this.model.get('brandModel').get('name');
         }
         if (this.model.has('photo')) {
            options.photoLink = this.model.get('photo').get('link');
         }

         return $.t(this.model.get('message'), options);

      },

      events: {
         'click .photo-link': 'clickPhotoLink'
      }
   });

   Action.Views.List = Backbone.View.extend({
      template: "action/list",

      beforeRender: function() {
         this.options.actions.each(function(action) {
            if (action) {
               var template = 'action/item';
               var tagName = 'li class="action-item"';
               switch (action.get('type')) {
                  case 'photo-upload':
                     if (action.get('photos').length > 1) {
                        template = 'action/itemWithPhotos';
                        tagName = 'li class="action-item-with-photos"';
                     } else {
                        template = 'action/itemWithLargePhoto';
                        tagName = 'li class="action-item-with-large-photo"';
                     }
                     break;
                  case 'photo-like':
                  case 'photo-fav':
                  case 'photo-tag':
                  case 'photo-comment':
                  case 'photo-brand-tag':
                     template = 'action/itemWithSmallPhoto';
                     tagName = 'li class="action-item-with-small-photo"';
                     break;
                  default:
                     template = 'action/item';
                     tagName = 'li class="action-item"';
                     break;
               }
               this.insertView(".actions-list", new Action.Views.Item({
                  model: action,
                  template: template,
                  tagName: tagName
               }));
            }
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.options.actions, 'sync', function() {
            if (this.options.actions.length == 0) {
               this.setView('.alert-actions', new Action.Views.NoAction());
            } else {
               this.removeView('.alert-actions');
            }
            this.render();
         });
         this.listenTo(app, 'stop:polling', this.stopPolling);
         this.pollActions(this.options.actions,
            typeof this.options.routeName !== 'undefined' ? this.options.routeName : null,
            typeof this.options.routeParameters !== 'undefined' ? this.options.routeParameters : null);
      },

      pollActions: function(actions, routeName, routeParameters) {
         routeName = routeName || 'api_v1_get_actions';
         routeParameters = routeParameters || {};
         var that = this;
         if (app.appState().getCurrentUserId() > 0) {
            actions.fetch({
               url: Routing.generate(routeName, routeParameters),
               success: function() {
                  // Set a new timeout
                  that.pollTimeout = setTimeout(function() {
                     that.pollActions(actions);
                  }, app.secondsBetweenPoll * 1000);
               },
               error: function() {
                  // Error, set a new timeout for 5 seconds
                  that.pollTimeout = setTimeout(function() {
                     that.pollActions(actions);
                  }, 5000);
               }
            });
         }
      },

      stopPolling: function() {
         if (typeof this.pollTimeout !== 'undefined' && this.pollTimeout)
            clearTimeout(this.pollTimeout);
      }
   });

   Action.Views.NoAction = Backbone.View.extend({
      template: 'action/noAction',
      alertText: 'action.noAction',
      isLogged: false,

      serialize: function() {
         return {
            alertText: this.alertText,
            isLogged: this.isLogged
         };
      },

      initialize: function() {
         this.isLogged = app.appState().isLogged();
         this.alertText = this.isLogged ? 'action.noAction' : 'action.loggedOff';
      },

      followNewUsers: function() {
         User.Common.showModalTopFollowers();
      },

      events: {
         'click .followNewUsers': 'followNewUsers'
      }
   });

   return Action;
});