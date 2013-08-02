define([
   "app",
   "modules/common"
], function(app, Common) {

   var Search = app.module();

   Search.Model = Backbone.Model.extend({
      initialize: function() {
         this.listenTo(this, {
            'change': this.updateUrl,
            'add': this.updateUrl
         });
      },

      updateUrl: function() {
         this.set('photoSmallUrl', app.rootUrl + 'uploads/photos/users/' + this.get('photo')['owner']['id'] + '/small/' + this.get('photo')['small_url']);
         this.set('profileLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('photo')['owner']['id'] }));
         this.set('fullname', this.get('photo')['owner']['firstname'] + ' ' + this.get('photo')['owner']['lastname']);
      }
   });

   Search.Collection = Backbone.Collection.extend({
      model: Search.Model,

      url: function()
      {
         return Routing.generate("api_v1_get_tag_search");
      },

      cache: true
   });

   Search.Views.Item = Backbone.View.extend({
      template: "search/item",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
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
         this.options.searchResults.each(function(result) {
            this.insertView(".search-results-list", new Search.Views.Item({
               model: result
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         var that = this;
         this.listenTo(this.options.searchResults, {
            "sync": this.render
         });
         this.listenTo(app, 'search:starting', function() {
            if (!this.isFullscreenSearch()) {
               $('.search-results-container').fadeIn();
               $('.search-loading').fadeIn();
               $('.alert-search-results').html();
            }
         });
         this.listenTo(app, 'search:completed', function() {
            if (!this.isFullscreenSearch()) {
               $('.search-loading').stop().fadeOut();
               if (that.options.searchResults.length == 0) {
                  app.useLayout().setView('.alert-search-results', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('search.noResults'),
                     showClose: true
                  })).render();
                  $('.view-more-results').stop().fadeOut();
               } else {
                  $('.view-more-results').stop().fadeIn();
               }
            }
         });
         this.listenTo(app, 'search:close', function() {
            $('.search-results-container').stop().fadeOut();
         });
         this.listenTo(app, 'search:show', function() {
            if (that.options.searchResults.length > 0)
               $('.search-results-container').stop().fadeIn();
         });
      },

      isFullscreenSearch: function() {
         return Backbone.history.fragment == $.t('routing.search/') ? true : false;
      }
   });

   Search.Views.Form = Backbone.View.extend({
      template: "search/searchBar",

      initialize: function() {
         this.searchResults = this.options.searchResults;
         app.useLayout().setView('.search-results-container', new Search.Views.List({
            searchResults: this.searchResults
         })).render();
      },

      events : {
         'keyup .search-query': 'search',
         'click .search-button': 'search',
         'blur .search-query': 'closeSearchResults',
         'focus .search-query': 'showSearchResults'
      },

      search: function(e) {
         e.preventDefault();
         this.startSearch();
      },

      startSearch: function() {
         $searchInput = $(this.el).find('.search-query');
         if ($searchInput.val()) {
            app.trigger('search:starting', $searchInput.val());
            this.searchResults.fetch({
               data: { 'query': $searchInput.val() },
               complete: function() {
                  app.trigger('search:completed');
               },
               error: function() {
                  app.useLayout().setView('.alert-search-results', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('search.error'),
                     showClose: true
                  })).render();
               }
            })
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

   Search.Views.FullList =  Backbone.View.extend({
      template: "search/fulllist",

      serialize: function() {
         return {
            terms : this.terms
         }
      },

      beforeRender: function() {
         this.options.searchResults.each(function(result) {
            this.insertView(".search-results-list", new Search.Views.FullItem({
               model: result
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.options.searchResults.length == 0) {
            app.useLayout().setView('.alert-search-results', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t('search.noResults'),
               showClose: true
            })).render();
         }
      },

      initialize: function() {
         var that = this;
         this.listenTo(this.options.searchResults, {
            "sync": this.render
         });
         this.listenTo(app, 'search:starting', function(terms) {
            $('.search-loading').fadeIn();
            $('.alert-search-results').html();
            this.terms = terms;
         });
         this.listenTo(app, 'search:completed', function() {
            $('.search-loading').stop().fadeOut();
            if (that.options.searchResults.length == 0) {
               app.useLayout().setView('.alert-search-results', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('search.noResults'),
                  showClose: true
               })).render();
            } else {
            }
         });
         this.terms = $('.search-query').val();
      }
   });

   Search.Views.FullSearch = Backbone.View.extend({
      template: "search/fullsearch",

      initialize: function() {

      }
   });

   return Search;
});