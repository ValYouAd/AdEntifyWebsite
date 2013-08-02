/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 07/06/2013
 * Time: 13:59
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/common"
], function(app, Common) {

   var MySettings = app.module();

   MySettings.Model = Backbone.Model.extend({

   });

   MySettings.ServicesCollection = Backbone.Collection.extend({
      url: Routing.generate('api_v1_get_user_services')
   });

   MySettings.Views.ServiceItem = Backbone.View.extend({
      template: "mysettings/serviceItem",
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

      deleteLink: function(e) {
         e.preventDefault();
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  headers : {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  url: Routing.generate('api_v1_delete_oauthuserinfo', {id: that.model.get('id')}),
                  type: 'DELETE',
                  success: function() {
                     app.trigger('mysettings:serviceDeleted', that.model);
                  },
                  error: function() {
                     app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                        cssClass: Common.alertError,
                        message: $.t('mySettings.cantDeleteServiceLink'),
                        showClose: true
                     })).render();
                  }
               });
            },
            error: function () {
               window.location.href = Routing.generate('home_logoff', { '_locale': app.appState().getLocale() });
            }
         });
      },

      events: {
         "click .deletelink": "deleteLink"
      }
   });

   MySettings.Views.Detail = Backbone.View.extend({
      template: "mySettings/detail",

      beforeRender: function() {
         this.services.each(function(service) {
            this.insertView(".connected-services-list", new MySettings.Views.ServiceItem({
               model: service
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).find('option[value="' + app.appState().getLocale() + '"]').attr("selected", "selected");
         $(this.el).i18n();
      },

      initialize: function() {
         app.trigger('domchange:title', $.t('mySettings.pageTitle'));
         this.services = new MySettings.ServicesCollection();
         this.services.fetch({
            success: function(collection) {
               if (collection.length == 0) {
                  app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('mySettings.noConnectedServices'),
                     showClose: true
                  })).render();
               }
            },
            error: function() {
               app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('mySettings.errorConnectedServices'),
                  showClose: true
               })).render();
            }
         });
         this.listenTo(this.services, "sync", this.render);
         this.listenTo(this.services, "remove", this.render);
         this.listenTo(app, 'mysettings:serviceDeleted', function(service) {
            this.services.remove(service);
            if (this.services.length == 0) {
               app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('mySettings.noConnectedServices'),
                  showClose: true
               })).render();
            }
         })
      },

      submit: function(e) {
         e.preventDefault();
         window.location.href = Routing.generate('change_lang', {'locale': $('#lang').val()});
      },

      events: {
         "submit form": "submit"
      }
   });

   MySettings.Views.MenuRight = Backbone.View.extend({
      template: "mySettings/menuRight"
   });


   return MySettings;
});