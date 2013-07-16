define([
   // Application.
   "app",

   // Modules
   "modules/facebook",
   "modules/homepage",
   "modules/photos",
   "modules/myPhotos",
   "modules/upload",
   "modules/facebookAlbums",
   "modules/facebookPhotos",
   "modules/instagramPhotos",
   "modules/adentifyOAuth",
   "modules/flickrSets",
   "modules/flickrPhotos",
   "modules/externalServicePhotos",
   "modules/photo",
   "modules/brand",
   "modules/mySettings",
   "modules/profile",
   "modules/common",
   "modules/category",
   "modules/search"
],

function(app, Facebook, HomePage, Photos, MyPhotos, Upload, FacebookAlbums, FacebookPhotos, InstagramPhotos,
         AdEntifyOAuth, FlickrSets, FlickrPhotos, ExternalServicePhotos, Photo, Brand, MySettings, Profile,
         Common, Category, Search) {
   var searchSetup = false;
   var Router = Backbone.Router.extend({
      initialize: function() {
         this.listenTo(this, {
            'route': this.routeTriggered
         });

         // Initialize Fb
         app.fb = new Facebook.Model();
         // Get AdEntify accesstoken for AdEntify API
         app.oauth = new AdEntifyOAuth.Model();
         app.oauth.loadAccessToken();

         // Facebook init
         FB.init({
            appId      : facebookAppId,                                   // App ID from the app dashboard
            channelUrl : channelUrl,  // Channel file for x-domain comms
            status     : false,                                                // Check Facebook Login status
            xfbml      : true,                                               // Look for social plugins on the page
            cookie     : true,
            oauth      : true
         });
         FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
               app.fb.connected(response);
            } else if (response.status === 'not_authorized') {
               app.fb.notLoggedIn();
            } else {
               app.fb.notLoggedIn();
            }
         });

         // Collections init
         var collections = {
            photos: new Photos.Collection(),
            tickerPhotos: new Photos.Collection(),
            myPhotos: new MyPhotos.Collection(),
            myTickerPhotos: new MyPhotos.Collection(),
            fbAlbums: new FacebookAlbums.Collection(),
            fbPhotos: new FacebookPhotos.Collection(),
            istgPhotos : new InstagramPhotos.Collection(),
            flrSets: new FlickrSets.Collection(),
            flrPhotos: new FlickrPhotos.Collection(),
            brands: new Brand.Collection(),
            categories: new Category.Collection(),
            searchResults: new Search.Collection()
         };
         _.extend(this, collections);

         // Nav current
         currentPage = window.location.pathname.replace(app.root, '');
         $currentLink = $('.nav a[href="' + currentPage + '"]');
         if ($currentLink.length > 0) {
            $currentLink.parent().siblings('.active').removeClass('active');
            $currentLink.parent().addClass('active');
         }
         $('.nav a').click(function() {
            $(this).parent().siblings('.active').removeClass('active');
            $(this).parent().addClass('active');
         });

         // Dom events
         app.on('domchange:title', this.onDomChangeTitle, this);
      },

      routes: function() {
         i18nRoutes = {
            "fr": {
               "": "homepage",
               "photos/non-taguees/": "untagged",
               "upload/": "upload",
               "mes/photos/taguees/": "myTagged",
               "mes/photos/non-taguees/": "myUntagged",
               "mes/parametres/": "mySettings",
               "facebook/albums/": "facebookAlbums",
               "facebook/albums/:id/photos/": "facebookAlbumsPhotos",
               "instagram/photos/": "instagramPhotos",
               "flickr/sets/": "flickrSets",
               "flickr/sets/:id/photos/": "flickrPhotos",
               "photo/:id/": "photoDetail",
               "marques/": "viewBrands",
               "mon/profil/": "myProfile",
               "profil/:id/": "profile",
               "categorie/:slug/": "category",
               "mon/adentify/": "myAdentify",
               "mes/photos/favorites/": "favoritesPhotos"
            },
            "en" : {
               "": "homepage",
               "photos/untagged/": "untagged",
               "upload/": "upload",
               "my/photos/tagged/": "myTagged",
               "my/photos/untagged/": "myUntagged",
               "my/settings/": "mySettings",
               "facebook/albums/": "facebookAlbums",
               "facebook/albums/:id/photos/": "facebookAlbumsPhotos",
               "instagram/photos/": "instagramPhotos",
               "flickr/sets/": "flickrSets",
               "flickr/sets/:id/photos/": "flickrPhotos",
               "photo/:id/": "photoDetail",
               "brands/": "viewBrands",
               "my/profile/": "myProfile",
               "profile/:id/": "profile",
               "category/:slug/": "category",
               "my/adentify/": "myAdentify",
               "my/photos/favorites/": "favoritesPhotos"
            }
         };
         switch (app.appState().getLocale()) {
            case 'fr':
               return i18nRoutes.fr;
            case 'en':
               return i18nRoutes.en;
            default:
               return i18nRoutes.fr;
         }
      },

      homepage: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               photos: this.photos,
               tagged: true,
               title: $.t('category.titleAll')
            }),
            "#menu-right": new Photos.Views.Ticker({
               tickerPhotos: this.tickerPhotos
            })
         }).render();

         var that = this;
         this.photos.fetch({
            url: Routing.generate('api_v1_get_photos', { tagged: true }),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading');
            }
         });
         this.tickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photos', { tagged: false }),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos', '#menu-right');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading', '#menu-right');
            }
         });
      },

      untagged: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               photos: this.photos,
               tagged: false
            }),
            "#menu-right": new Photos.Views.Ticker({
               tickerPhotos: this.tickerPhotos
            })
         }).render();

         var that = this;
         this.photos.fetch({
            url: Routing.generate('api_v1_get_photos', { tagged: false }),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading');
            }
         });
         this.tickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photos', { tagged: true }),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos', '#menu-right');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading', '#menu-right');
            }
         });
      },

      myTagged: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new MyPhotos.Views.Content({
               photos: this.myPhotos,
               tagged: true,
               title: $.t('myPhotos.titleTagged')
            }),
            "#menu-right": new MyPhotos.Views.Ticker({
               tickerPhotos: this.myTickerPhotos
            })
         }).render();

         var that = this;
         this.myPhotos.fetch({
            url: Routing.generate('api_v1_get_photo_user_photos', { tagged: true }),
            success: function(collection) {
               that.successCallback(collection, 'myPhotos.noPhotos');
            },
            error: function() {
               that.errorCallback('myPhotos.errorPhotosLoading');
            }
         });
         this.myTickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photo_user_photos', { tagged: false }),
            success: function(collection) {
               that.successCallback(collection, 'myPhotos.noPhotos', '#menu-right');
            },
            error: function() {
               that.errorCallback('myPhotos.errorPhotosLoading', '#menu-right');
            }
         });
      },

      myUntagged: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new MyPhotos.Views.Content({
               photos: this.myPhotos,
               tagged: false,
               title: $.t('myPhotos.titleUntagged')
            }),
            "#menu-right": new MyPhotos.Views.Ticker({
               tickerPhotos: this.myTickerPhotos
            })
         }).render();

         var that = this;
         this.myPhotos.fetch({
            url: Routing.generate('api_v1_get_photo_user_photos', { tagged: false }),
            success: function(collection) {
               that.successCallback(collection, 'myPhotos.noPhotos');
            },
            error: function() {
               that.errorCallback('myPhotos.errorPhotosLoading');
            }
         });
         this.myTickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photo_user_photos', { tagged: true }),
            success: function(collection) {
               that.successCallback(collection, 'myPhotos.noPhotos', '#menu-right');
            },
            error: function() {
               that.errorCallback('myPhotos.errorPhotosLoading', '#menu-right');
            }
         });
      },

      upload: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Upload.Views.Content(),
            "#menu-right": new ExternalServicePhotos.Views.MenuRightPhotos()
         }).render();
      },

      facebookAlbums: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new FacebookAlbums.Views.List({
               albums: this.fbAlbums,
               categories: this.categories
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRightAlbums({
               categories: this.categories
            })
         }).render();

         this.categories.fetch();
      },

      facebookAlbumsPhotos: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new FacebookPhotos.Views.List({
               albumId: id,
               photos: this.fbPhotos
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRightPhotos({
               categories: this.categories
            })
         }).render();

         this.categories.fetch();
      },

      instagramPhotos: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new InstagramPhotos.Views.List({
               photos: this.istgPhotos
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRightPhotos({
               categories: this.categories
            })
         }).render();

         this.categories.fetch();
      },

      flickrSets: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new FlickrSets.Views.List({
               sets: this.flrSets,
               categories: this.categories
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRightAlbums({
               categories: this.categories
            })
         }).render();

         this.categories.fetch();
      },

      flickrPhotos: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new FlickrPhotos.Views.List({
               photos: this.flrPhotos,
               albumId: id
            }),
            "#menu-right": new ExternalServicePhotos.Views.MenuRightPhotos({
               categories: this.categories
            })
         }).render();

         this.categories.fetch();
      },

      photoDetail: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photo.Views.Content({
               photo: new Photo.Model({ 'id': id })
            }),
            "#menu-right": new MyPhotos.Views.Ticker({
               tickerPhotos: this.myTickerPhotos,
               tagged: false
            })
         }).render();

         this.myTickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photo_user_photos', { tagged: true })
         });
      },

      viewBrands: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Brand.Views.List({
               brands: this.brands
            })
         }).render();

         this.brands.fetch();
      },

      mySettings: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new MySettings.Views.Detail(),
            "#menu-right": new MySettings.Views.MenuRight()
         }).render();
      },

      profile: function(id) {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               photos: this.photos,
               userId: id,
               tagged: true
            }),
            "#menu-right": new Profile.Views.MenuRight({
               user: new Profile.Model({
                  id: id
               })
            })
         }).render();

         this.photos.fetch({
            url: Routing.generate('api_v1_get_user_photos', { id: id })
         });
      },

      category: function(slug) {
         this.reset();

         var category = new Category.Model();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               photos: this.photos,
               category: category
            }),
            "#menu-right": new Photos.Views.Ticker({
               tickerPhotos: this.tickerPhotos
            })
         }).render();

         var that = this;

         // Get category
         if (this.categories.length > 0) {
            var foundCategory = _.first(this.categories.where({ slug: slug }));
            if (foundCategory) {
               category.set('name', foundCategory.get('name'));
               that.onDomChangeTitle($.t('category.pageTitle', { 'name': foundCategory.get('name') }));
            }
         } else {
            this.categories.fetch({
               success: function(collection) {
                  var foundCategory = _.first(collection.where({ slug: slug }));
                  if (foundCategory) {
                     category.set('name', foundCategory.get('name'));
                     that.onDomChangeTitle($.t('category.pageTitle', { 'name': foundCategory.get('name') }));
                  }
               }
            });
         }

         // Get category photos
         this.photos.fetch({
            url: Routing.generate('api_v1_get_category_photos', { slug: slug }),
            success: function(collection) {
               that.successCallback(collection, 'category.noPhotos');
            },
            error: function() {
               that.errorCallback('category.errorPhotosLoading');
            }
         });
         // Get category photos untagged
         this.tickerPhotos.fetch({
            url: Routing.generate('api_v1_get_category_photos', { slug: slug, tagged: false }),
            success: function(collection) {
               that.successCallback(collection, 'category.noPhotos', '#menu-right');
            },
            error: function() {
               that.errorCallback('category.errorPhotosLoading', '#menu-right');
            }
         });
      },

      myAdentify: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new Photos.Views.Content({
               photos: this.photos,
               pageTitle: $.t('myAdentify.pageTitle'),
               title: $.t('myAdentify.title')
            }),
            "#menu-right": new Photos.Views.Ticker({
               tickerPhotos: this.tickerPhotos
            })
         }).render();

         var that = this;
         this.photos.fetch({
            url: Routing.generate('api_v1_get_user_feed'),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading');
            }
         });
         this.tickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photos', { tagged: false }),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos', '#menu-right');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading', '#menu-right');
            }
         });
      },

      favoritesPhotos: function() {
         this.reset();

         app.useLayout().setViews({
            "#content": new MyPhotos.Views.Content({
               photos: this.myPhotos,
               tagged: true,
               title: $.t('myPhotos.titleFavorites')
            }),
            "#menu-right": new MyPhotos.Views.Ticker({
               tickerPhotos: this.myTickerPhotos
            })
         }).render();

         var that = this;
         this.myPhotos.fetch({
            url: Routing.generate('api_v1_get_user_favorites'),
            success: function(collection) {
               that.successCallback(collection, 'myPhotos.noPhotos');
            },
            error: function() {
               that.errorCallback('myPhotos.errorPhotosLoading');
            }
         });
      },

      reset: function() {
         if (this.photos.length) {
            this.photos.reset();
         }
         if (this.tickerPhotos.length) {
            this.tickerPhotos.reset();
         }
         if (this.fbAlbums.length) {
            this.fbAlbums.reset();
         }
         if (this.fbPhotos.length) {
            this.fbPhotos.reset();
         }
         if (this.istgPhotos.length) {
            this.istgPhotos.reset();
         }
         if (this.flrSets.length) {
            this.flrSets.reset();
         }
         if (this.flrPhotos.length) {
            this.flrPhotos.reset();
         }
         if (this.myPhotos.length) {
            this.myPhotos.reset();
         }
         if (this.myTickerPhotos.length) {
            this.myTickerPhotos.reset();
         }
         if (this.brands.length) {
            this.brands.reset();
         }
         if (this.categories.length) {
            this.categories.reset();
         }
         // Add search form if not already set
         if (!searchSetup) {
            searchSetup = true;
            app.useLayout().setView('#search-bar', new Search.Views.Form({
               searchResults: this.searchResults
            })).render();
         }
      },

      // Shortcut for building a url.
      go: function() {
         return this.navigate(_.toArray(arguments).join("/"), true);
      },

      // Change title of window
      onDomChangeTitle: function(title) {
         if (typeof title !== 'undefined' && title != '') {
            $(document).attr('title', title);
         }
      },

      routeTriggered: function() {
         if ($('#dashboard').hasClass('edit-mode')) {
            $("#dashboard").removeClass('edit-mode').addClass('view-mode');
         }
         if ($('#content').hasClass('span11')) {
            $('#content').switchClass('span11', 'span9');
         }
         if ($("aside").hasClass('span1')) {
            $("aside").switchClass("span1", "span3");
         }
         app.stopLoading();
      },

      successCallback: function(collection, translationKey, target) {
         target = (typeof target === "undefined") ? "#content" : target;
         // Check if collection is empty
         if (collection.length == 0) {
            app.useLayout().setView(target, new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t(translationKey)
            }), true).render();
         }
      },

      errorCallback: function(translationKey, target) {
         target = (typeof target === "undefined") ? "#content" : target;
         app.useLayout().setView(target, new Common.Views.Alert({
            cssClass: Common.alertError,
            message: $.t(translationKey),
            showClose: true
         }), true).render();
      }
   });

   return Router;
});
