/**
 * Created by pierrickmartos on 29/10/2013.
 */
define([
   'app',
   'modules/common'
], function(app, Common) {

   var Hashtag = app.module();

   Hashtag.Model = Backbone.Model.extend({

      initialize: function() {
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });
         this.setup();
      },

      setup: function() {
         if (this.has('name'))
            this.set('link', app.beginUrl + app.root + $.t('routing.search/keywords', { keywords: '%23' + this.get('name') }));
      },

      urlRoot: Routing.generate('api_v1_get_hashtags')
   });

   Hashtag.Collection = Backbone.Collection.extend({
      model: Hashtag.Model,
      url: Routing.generate('api_v1_get_hashtags')
   });

   Hashtag.Views.Item = Backbone.View.extend({
      template: 'hashtag/item',
      tagName: 'li class="hashtag-item"',

      serialize: function() {
         return { model: this.model };
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });

   Hashtag.Views.List = Backbone.View.extend({
      template: "hashtag/list",
      showAlert: false,

      beforeRender: function() {
         this.options.hashtags.each(function(hashtag) {
            this.insertView(".hashtags-list", new Hashtag.Views.Item({
               model: hashtag
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.hashtags.length === 0)
            this.$('.hashtags-list').hide();
      },

      initialize: function() {
         this.listenTo(this.options.hashtags, 'sync', function() {
            if (this.options.hashtags.length === 0 && this.showAlert) {
               this.setView('.hashtags-alert', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('profile.noHashtag'),
                  showClose: false
               }));
            } else {
               this.removeView('.hashtags-alert');
            }
            this.render();
         });
         this.showAlert = typeof this.options.showAlert !== 'undefined' ? this.options.showAlert : this.showAlert;
      }
   });

   return Hashtag;
});