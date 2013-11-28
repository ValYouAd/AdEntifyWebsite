/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 07/06/2013
 * Time: 13:59
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/common",
   'modules/upload'
], function(app, Common, Upload) {

   var MySettings = app.module();

   MySettings.ChangePasswordModel = Backbone.Model.extend({
      url: Routing.generate('api_v1_post_user_change_password', { id: currentUserId }),

      toJSON: function() {
         return { fos_user_change_password: this.attributes }
      }
   });

   MySettings.ServicesCollection = Backbone.Collection.extend({
      url: Routing.generate('api_v1_get_user_services')
   });

   MySettings.Views.ServiceItem = Backbone.View.extend({
      template: "mySettings/serviceItem",
      tagName: 'li class="service-item"',

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

      connectInstagram: function() {
         window.location.href = Upload.Common.getInstagramUrl();
      },

      events: {
         "click .deletelink": "deleteLink",
         'click .connect-instagram': 'connectInstagram'
      }
   });

   MySettings.Views.ServiceList = Backbone.View.extend({
      template: 'mySettings/serviceList',

      beforeRender: function() {
         this.services.each(function(service) {
            this.insertView(".services-list", new MySettings.Views.ServiceItem({
               model: service
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).find('.service-icon-tooltip').tooltip();
      },

      initialize: function() {
         this.services = new MySettings.ServicesCollection();
         this.services.fetch({
            error: function() {
               app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('mySettings.errorConnectedServices'),
                  showClose: true
               })).render();
            }
         });
         this.listenTo(this.services, "sync", function(collection) {
            if (collection.length == 0) {
               app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('mySettings.noConnectedServices'),
                  showClose: true
               })).render();
            } else {
               this.render();
            }
         });
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
         });
      }
   });

   MySettings.Views.Detail = Backbone.View.extend({
      template: "mySettings/detail",

      beforeRender: function() {
         this.setView(".services", new MySettings.Views.ServiceList());
      },

      afterRender: function() {
         $(this.el).find('option[value="' + app.appState().getLocale() + '"]').attr("selected", "selected");
         $(this.el).i18n();
      },

      initialize: function() {
         // Get current user
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_get_user', { id: currentUserId }),
                  headers: {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  success: function(user) {
                       if (user && typeof user.facebook_id === 'undefined' && typeof user.twitter_id === 'undefined') {
                          $('.changePasswordForm').fadeIn('fast');
                       }
                  }
               });
            }
         });
         app.trigger('domchange:title', $.t('mySettings.pageTitle'));
      },

      submitLang: function(e) {
         e.preventDefault();
         window.location.href = Routing.generate('change_lang', {'locale': $('#lang').val()});
      },

      submitChangePassword: function(e) {
         e.preventDefault();

         if ($('.currentPassword').val() && $('.newPasswordFirst').val() && $('.newPasswordSecond').val()) {
            if ($('.newPasswordFirst').val() == $('.newPasswordSecond').val()) {
               var changePassword = new MySettings.ChangePasswordModel();
               changePassword.set('current_password', $('.currentPassword').val());
               changePassword.set('new', $('.newPasswordFirst').val());
               changePassword.getToken('change_password', function() {
                  changePassword.save(null, {
                     success: function() {
                        app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
                           cssClass: Common.alertSuccess,
                           message: $.t('mySettings.passwordChanged'),
                           showClose: true
                        })).render();
                        $('input[type="password"]').val('');
                     },
                     error: function(e, r) {
                        app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
                           cssClass: Common.alertSuccess,
                           message: $.t('mySettings.currentPasswordError'),
                           showClose: true
                        })).render();
                     }
                  })
               });
            } else {
               app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('mySettings.notSamePassword'),
                  showClose: true
               })).render();
            }
         } else {
            app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
               cssClass: Common.alertError,
                  message: $.t('mySettings.emptyFieldsChangePassword'),
               showClose: true
            })).render();
         }
      },

      deleteAccount: function() {
         var modal = new Common.Views.Modal({
            content: 'mySettings.modalDeleteAccountText',
            showFooter: true,
            showHeader: true,
            title: 'mySettings.modalDeleteAccountTitle',
            modalDialogClasses: 'deleteAccountDialog',
            showConfirmButton: true,
            confirmButton: 'mySettings.confirmDeleteAccountButton'
         });
         modal.on('confirm', function() {
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     url: Routing.generate('api_v1_delete_user', { id: currentUserId }),
                     type: 'DELETE',
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function() {
                        window.location.href = Routing.generate('fos_user_security_logout');
                     }
                  });
               }
            });
            modal.close();
         });
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      },

      events: {
         "submit .langForm": "submitLang",
         "submit .changePasswordForm": "submitChangePassword",
         'click .delete-account-button': 'deleteAccount'
      }
   });

   MySettings.Views.MenuRight = Backbone.View.extend({
      template: "mySettings/menuRight"
   });


   return MySettings;
});