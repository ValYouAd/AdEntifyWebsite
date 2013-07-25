/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/06/2013
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Common = app.module();
   Common.alertError = 'alert-error';
   Common.alertInfo = 'alert-info';
   Common.alertSuccess = 'alert-success';
   Common.alertWarning = 'alert-block';


   Common.AlertModel = Backbone.Model.extend({
      defaults: {
         showClose: false
      },

      initialize: function(options) {
         switch(options.cssClass) {
            case Common.alertError:
               this.set('cssClass', Common.alertError);
            break;
            case Common.alertInfo:
               this.set('cssClass', Common.alertInfo);
            break;
            case Common.alertSuccess:
               this.set('cssClass', Common.alertSuccess);
            break;
            default:
               this.set('cssClass', Common.alertWarning);
            break;
         }
         this.set('message', options.message);
         if (typeof options.showClose !== 'undefined') {
            this.set('showClose', options.showClose)
         }
      }
   });

   Common.Views.Alert = Backbone.View.extend({
      template: "common/alert",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         if (typeof this.options.showClose === 'undefined')
            this.options.showClose = false;

         this.model = new Common.AlertModel({
            cssClass: this.options.cssClass,
            message: this.options.message,
            showClose: this.options.showClose
         });
         this.listenTo(this.model, "change", this.render);
      }
   });

   Common.ModalModel = Backbone.Model.extend({});

   Common.Views.Modal = Backbone.View.extend({
      template: "common/modal",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.model = new Common.ModalModel({
            title: this.options.title,
            content: this.options.content
         });
         this.listenTo(this.model, "change", this.render);
      },

      afterRender: function() {
         $(this.el).i18n();
         this.$('.commonModal').modal({
            backdrop: true,
            show: true
         });
         this.$('.commonModal').on('hidden', function() {
            Backbone.history.navigate('', true);
         });
      }
   });

   return Common;
});