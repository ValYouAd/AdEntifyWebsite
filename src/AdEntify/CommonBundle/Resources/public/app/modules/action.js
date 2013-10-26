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
      },

      setup: function() {
         if (this.has('photos')) {
            if (this.get('photos').length > 1) {
               var photos = new Photos.Collection();
               _.each(this.get('photos'), function(photo) {
                  var model = new Photo.Model(photo);
                  model.setup();
                  photos.add(model);
               });
               this.set('photosCollection', photos);
            } else if (this.get('photos').length == 1) {
               var model = new Photo.Model(this.get('photos')[0]);
               model.setup();
               this.set('photo', model);
            }
         }
         if (this.has('author') ) {
            this.set('authorModel', new User.Model(this.get('author')));
         }
         if (this.has('target') ) {
            this.set('targetModel', new User.Model(this.get('target')));
         }
      },

      urlRoot: Routing.generate('api_v1_get_actions')
   });

   Action.Collection = Backbone.Collection.extend({
      model: Action.Model,
      cache: true,
      url: Routing.generate('api_v1_get_actions')
   });

   /*Action.Views.BaseItem = Backbone.View.extend({
      serialize: function() {
         return { model: this.model };
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });*/

   Action.Views.Item = Backbone.View.extend({
      template: "action/item",
      tagName: "li class='action-item'",

      serialize: function() {
         return { model: this.model };
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
         Photos.Common.showPhoto(evt, this.model.get('photo'));
      },

      events: {
         'click .photo-link': 'clickPhotoLink'
      }
   });

  /* Action.Views.ItemWithLargePhoto = Action.Views.BaseItem({
      template: 'action/itemWithLargePhoto',
      tagName: 'li class="action-item-with-large-photo"'
   });

   Action.Views.ItemWithLargePhoto = Backbone.View.extend({
      template: 'action/itemWithLargePhoto',
      tagName: 'li class="action-item-with-large-photo"'
   });*/

   Action.Views.List = Backbone.View.extend({
      template: "action/list",

      beforeRender: function() {
         this.options.actions.each(function(action) {
            var template = 'action/item';
            var tagName = 'li class="action-item"';
            switch (action.get('type')) {
               case 'photo-upload':
                  if (action.get('photos').length > 3) {
                     template = 'action/itemWithPhotos';
                     tagName = 'action-item-with-photos';
                  } else {
                     template = 'action/itemWithLargePhoto';
                     tagName = 'action-item-with-large-photo';
                  }
                  break;
               case 'photo-like':
               case 'photo-fav':
               case 'photo-tag':
               case 'photo-comment':
                  template = 'action/itemWithSmallPhoto';
                  tagName = 'action-item-with-small-photo';
                  break;
               case 'reward-new':
               case 'user-follow':
                  template = 'action/item';
                  tagName = 'action-item';
                  break;
            }
            this.insertView(".actions-list", new Action.Views.Item({
               model: action,
               template: template,
               tagName: tagName
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.listenTo(this.options.actions, 'sync', this.render);
      }
   });

   return Action;
});