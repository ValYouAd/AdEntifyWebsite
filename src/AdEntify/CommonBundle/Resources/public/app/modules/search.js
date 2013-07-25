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
            $('.search-results-container').fadeIn();
            $('.search-loading').fadeIn();
            $('.alert-search-results').html();
         });
         this.listenTo(app, 'search:completed', function() {
            $('.search-loading').stop().fadeOut();
            if (that.options.searchResults.length == 0) {
               app.useLayout().setView('.alert-search-results', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('search.noResults'),
                  showClose: true
               })).render();
            }
         });
      }
   });

   Search.Views.Form = Backbone.View.extend({
      template: "search/searchBar",

      events : {
         'keyup .search-query': 'search',
         'click .search-button': 'search'
      },

      search: function(e) {
         e.preventDefault();
         if (typeof e.keyCode == "undefined" || e.keyCode == 13) {
            this.startSearch();
         }
      },

      startSearch: function() {
         $searchInput = $(this.el).find('.search-query');
         if ($searchInput.val()) {
            app.trigger('search:starting');
            var that = this;
            this.searchResults.fetch({
               data: { 'query': $searchInput.val() },
               complete: function() {
                  app.trigger('search:completed');
               },
               success: function() {

               },
               error: function() {

               }
            })
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         this.searchResults = this.options.searchResults;
         app.useLayout().setView('.search-results-container', new Search.Views.List({
            searchResults: this.searchResults
         })).render();
      }
   });

   return Search;
});