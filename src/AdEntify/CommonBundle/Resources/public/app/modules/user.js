/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 12/06/2013
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/photos",
   "modules/common"
], function(app, Photos, Common) {

   var User = app.module();

   User.Model = Backbone.Model.extend({
      initialize: function() {
         this.listenTo(this, 'sync', this.setup);
         this.setup();
      },

      setup: function() {
         this.set('fullname', this.get('firstname') + ' ' + this.get('lastname'));
         this.set('link', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('id') }));
      },

      defaults: {
         photos_count: 0
      }
   });

   User.Collection = Backbone.Collection.extend({
      model: User.Model
   });

   User.Views.MenuRight = Backbone.View.extend({
      template: "user/menuRight",

      serialize: function() {
         return { model: this.model };
      },

      beforeRender: function() {
         this.options.likesPhotos.each(function(photo) {
            this.insertView(".ticker-photos", new Photos.Views.TickerItem({
               model: photo
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (typeof this.model !== 'undefined' && this.model.has('firstname')) {
            $('#loading-profile').fadeOut('fast', function() {
               $('#profile').fadeIn();
            });
         }
      },

      follow: function(e) {
         e.preventDefault();
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_user_follower', { id: that.model.get('id') }),
                  type: 'POST',
                  headers : {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  }
               });
            }
         });
      },

      events: {
         "click #follow": "follow"
      },

      initialize: function() {
         this.listenTo(this.options.user, {
            "sync": this.render
         });
         this.listenTo(this.options.likesPhotos, {
            "sync": function(collection) {
               if (collection.length == 0) {
                  app.useLayout().setView('.alert-ticker-photos', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('profile.noLikedPhotos'),
                     showClose: true
                  })).render();
               }
               this.render();
            },
            "error": function() {
               app.useLayout().setView('.alert-ticker-photos', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('profile.errorLikedPhotos'),
                  showClose: true
               })).render();
            }
         });
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

   return User;
});