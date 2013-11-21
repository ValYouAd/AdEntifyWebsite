define([
   'app',
   'modules/common',
   'modules/photos'
], function(app, Common, Photos) {

   var Search = app.module();

   Search.Views.Item = Backbone.View.extend({
      template: "search/item",
      tagName: 'li',

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      },

      showPhoto: function(evt) {
         Photos.Common.showPhoto(evt, this.model);
      },

      events: {
         'click a[data-photo-link]': 'showPhoto'
      }
   });

   Search.Views.UserItem = Backbone.View.extend({
      template: "search/userItem",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });

   Search.Views.List = Backbone.View.extend({
      template: "search/list",

      serialize: function() {
         return {
            searchUrl : app.beginUrl + app.root + $.t('routing.search/')
         }
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            photo.setup();
            this.insertView(".search-tags-results", new Search.Views.Item({
               model: photo
            }));
         }, this);
         this.options.users.each(function(user) {
            this.insertView(".search-users-results", new Search.Views.UserItem({
               model: user
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         var that = this;
         this.listenTo(this.options.photos, 'sync', this.render);
         this.listenTo(this.options.users, 'sync', this.render);
         this.listenTo(app, 'search:starting', function() {
            if (!this.isFullscreenSearch()) {
               $('.search-bar .dropdown-menu').fadeIn();
               $('.search-loading').fadeIn();
               $('.alert-search-tags-results').html();
               $('.alert-search-users-results').html();
            }
         });
         this.listenTo(app, 'search:completed', function() {
            if (!this.isFullscreenSearch()) {
               $('.search-loading').stop().fadeOut();
               if (that.options.photos.length == 0) {
                  app.useLayout().setView('.alert-search-tags-results', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('search.noResults'),
                     showClose: true
                  })).render();
                  $('.view-more-results').hide();
               } else {
                  $('.view-more-results').show();
               }
               if (that.options.users.length == 0) {
                  app.useLayout().setView('.alert-search-users-results', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('search.noResults'),
                     showClose: true
                  })).render();
               } else {
                  $('.view-more-results').show();
               }
            }
         });
         this.listenTo(app, 'search:close', function() {
            $('.search-bar .dropdown-menu').stop().fadeOut();
         });
         this.listenTo(app, 'search:show', function() {
            if (!that.isFullscreenSearch && that.options.photos.length > 0)
               $('.search-bar .dropdown-menu').stop().fadeIn();
         });
      },

      isFullscreenSearch: function() {
         return Backbone.history.fragment == $.t('routing.search/');
      }
   });

   Search.Views.Form = Backbone.View.extend({
      template: "search/searchBar",
      searchTimeout: null,
      terms: null,

      serialize: function() {
         return { terms: this.terms };
      },

      initialize: function() {
         this.photos = this.options.photos;
         this.users = this.options.users;
         app.useLayout().setView('.search-results-container', new Search.Views.List({
            photos: this.photos,
            users: this.users
         })).render();
         this.listenTo(app, 'search:start', function(terms) {
            if (terms) {
               this.terms = terms;
               this.startSearch(terms);
            }
         });
      },

      events : {
         'keyup .search-query': 'search',
         'click .search-button': 'search',
         'blur .search-query': 'closeSearchResults',
         'focus .search-query': 'showSearchResults'
      },

      search: function(e) {
         e.preventDefault();
         if (e.keyCode == 13 || $(e.currentTarget).hasClass('search-button')) {
            Backbone.history.navigate($.t('routing.search/'), { trigger: true });
         } else {
            if (this.searchTimeout)
               clearTimeout(this.searchTimeout);
            var that = this;
            this.searchTimeout = setTimeout(function() {
               that.startSearch($(that.el).find('.search-query').val());
            }, 500);
         }
      },

      startSearch: function(terms) {
         if (terms) {
            app.trigger('search:starting', terms);
            this.photos.fetch({
               url: Routing.generate('api_v1_get_photo_search'),
               data: { 'query': terms },
               complete: function() {
                  app.trigger('search:completed');
               },
               error: function() {
                  app.useLayout().setView('.alert-search-tags-results', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('search.error'),
                     showClose: true
                  })).render();
               }
            });
            this.users.fetch({
               url: Routing.generate('api_v1_get_user_search'),
               data: { 'query': terms },
               complete: function() {
                  app.trigger('search:completed');
               },
               error: function() {
                  app.useLayout().setView('.alert-search-users-results', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('search.error'),
                     showClose: true
                  })).render();
               }
            });
         }
      },

      closeSearchResults: function() {
         app.trigger('search:close');
      },

      showSearchResults: function() {
         app.trigger('search:show');
      },

      afterRender: function() {
         $(this.el).i18n();
      }
   });

   Search.Views.FullItem =  Backbone.View.extend({
      template: "search/fullitem",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Search.Views.FullUserItem =  Backbone.View.extend({
      template: "search/fullUserItem",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Search.Views.FullList =  Backbone.View.extend({
      template: "search/fulllist",

      serialize: function() {
         return {
            terms : this.terms
         }
      },

      initialize: function() {
         var that = this;
         this.terms = typeof this.options.terms !== 'undefined' ? this.options.terms : null;
         this.listenTo(this.options.photos, {
            'sync': this.render
         });
         this.listenTo(app, 'search:starting', function(terms) {
            $('.search-loading').fadeIn();
            $('.alert-search-tags-results').html();
            $('.alert-search-users-results').html();
            this.terms = terms;
         });
         this.listenTo(app, 'search:completed', function() {
            $('.search-loading').stop().fadeOut();
            if (that.options.photos.length == 0) {
               app.useLayout().setView('.alert-search-tags-results', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('search.noResults'),
                  showClose: true
               })).render();
            }
            if (that.options.users.length == 0) {
               app.useLayout().setView('.alert-search-users-results', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('search.noResults'),
                  showClose: true
               })).render();
            }
         });
         if (this.terms) {
            app.trigger('search:start', this.terms);
         } else
            this.terms = $('.search-query').val();
      },

      beforeRender: function() {
         if (!this.getView('.search-photos-results')) {
            var Photos = require('modules/photos');
            this.setView('.search-photos-results', new Photos.Views.Content({
               photos: this.options.photos,
               listenToEnable: true
            }));
         }
         this.options.users.each(function(result) {
            this.insertView(".search-users-results", new Search.Views.FullUserItem({
               model: result
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.photos.length == 0) {
            app.useLayout().setView('.alert-search-tags-results', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t('search.noResults'),
               showClose: true
            })).render();
         }
         if (this.options.users.length == 0) {
            app.useLayout().setView('.alert-search-users-results', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t('search.noResults'),
               showClose: true
            })).render();
         }
      }
   });

   Search.Views.FullSearch = Backbone.View.extend({
      template: "search/fullsearch",

      initialize: function() {

      }
   });

   return Search;
});