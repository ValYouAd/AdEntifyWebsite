/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/venue",
   "modules/person",
   "modules/common",
   "modules/product",
   "select2",
   "bootstrap",
   "modernizer",
   "jquery.iframe-transport",
   "jquery.fileupload"
], function(app, Venue, Person, Common, Product) {

   var Tag = app.module();
   var currentPhotoOverlay = null;
   var currentTag = null;
   var tags = null;
   var currentBrands = {};
   var currentBrand = null;
   var currentProducts = {};
   var currentProduct = null;
   var newProduct = null;
   var currentVenues = {};
   var currentVenue = null;
   var currentPerson = null;
   var venuesSearchTimeout = null;

   Tag.Model = Backbone.Model.extend({

      initialize: function() {
         if (this.has('waiting_validation') && this.get('waiting_validation')) {
            if (this.has('validation_status') && this.get('validation_status') == 'waiting')
               this.set('cssClass', 'unvalidateTag');
            else
               this.set('cssClass', '');
         }

         this.listenTo(this, 'change', this.render);
      },

      urlRoot: function() {
         return Routing.generate('api_v1_get_tag');
      },

      toJSON: function() {
         var jsonAttributes = this.attributes;
         delete jsonAttributes.cssClass;
         delete jsonAttributes.tempTag;
         return { tag: jsonAttributes }
      },

      changeValidationStatus: function(status) {
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_post_tag_validation_status', {id: that.get('id') }),
                  headers : {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  type: 'POST',
                  data: {
                     'waiting_validation' : status
                  },
                  success: function(data) {
                     if (data == 'granted') {
                        that.set('cssClass', '');
                        that.set('waiting_validation', false);
                        that.set('validation_status', 'granted');
                     } else {
                        app.trigger('tag:removeTag', that);
                     }
                  }
               });
            }
         });
      },

      isOwner: function() {
          return this.has('owner') ? currentUserId == this.get('owner')['id'] : false;
      },

      delete: function() {
         // Check if currentUser is the owner
         if (this.isOwner()) {
            var that = this;
            this.destroy({
               url: Routing.generate('api_v1_delete_tag', { id : this.get('id') } ),
               success: function() {
                  that.trigger('delete:success');
               },
               error: function() {
                  that.trigger('delete:error');
               }
            });
         }
      }
   });

   Tag.Collection = Backbone.Collection.extend({
      model: Tag.Model
   });

   Tag.Views.Item = Backbone.View.extend({

      initialize: function() {
         this.popoverDesactivated = this.options.desactivatePopover;
      },

      setupPopover: function(popover, popoverArrow) {
         if (this.model.get('y_position') > 0.5) {
            popoverArrow.addClass('tag-popover-arrow-bottom');
            popoverArrow.css({bottom: '-10px'});
         } else {
            popoverArrow.css({top: '-10px'});
            popoverArrow.addClass('tag-popover-arrow-top');
         }
         if (this.model.get('x_position') > 0.5) {
            popoverArrow.css({right: '20px'});
         } else {
            popoverArrow.css({left: '20px'});
         }
         popover.css({top: this.model.get('y_position') > 0.5 ? '-'+(popover.height() + 18)+'px' : '46px'});
         popover.css({left: this.model.get('x_position') > 0.5 ? '-'+(popover.width() - 31)+'px' : '-8px'});
         popover.fadeIn(100);
      }
   });

   Tag.Views.PersonItem = Tag.Views.Item.extend({
      template: "tag/types/person",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return { model: this.model };
      },

      initialize: function(options) {
         this.constructor.__super__.initialize.apply(this, [options]);
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         if (!this.popoverDesactivated) {
            clearTimeout(this.hoverTimeout);
            var popover = $(this.el).find('.popover');
            var popoverArrow = $(this.el).find('.tag-popover-arrow');
            this.setupPopover(popover, popoverArrow);
            app.tagStats().hover(this.model);
         }
      },

      hoverOut: function() {
         if (!this.popoverDesactivated) {
            var that = this;
            this.hoverTimeout = setTimeout(function() {
               $(that.el).find('.popover').hide();
            }, 200);
         }
      },

      clickTag: function() {
         app.tagStats().click(this.model);
      },

      validateTag: function() {
         this.model.changeValidationStatus('granted');
      },

      refuseTag: function() {
         this.model.changeValidationStatus('denied');
      },

      deleteTag: function() {
         this.model.delete();
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut",
         "click a[href]": "clickTag",
         "click .validateTagButton": "validateTag",
         "click .refuseTagButton": "refuseTag",
         "click .deleteTagButton": "deleteTag"
      }
   });

   Tag.Views.VenueItem = Tag.Views.Item.extend({
      template: "tag/types/venue",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return { model: this.model };
      },

      initialize: function(options) {
         this.constructor.__super__.initialize.apply(this, [options]);
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         if (!this.popoverDesactivated) {
            clearTimeout(this.hoverTimeout);
            var popover = $(this.el).find('.popover');
            var popoverArrow = $(this.el).find('.tag-popover-arrow');
            this.setupPopover(popover, popoverArrow);
            if (!$('#map' + this.model.get('id')).hasClass('loaded')) {
               var latLng = new google.maps.LatLng(this.model.get('venue').lat, this.model.get('venue').lng);
               var mapOptions = {
                  zoom:  14,
                  center: latLng,
                  scrollwheel: false,
                  navigationControl: false,
                  mapTypeControl: false,
                  scaleControl: false,
                  draggable: false,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
               };
               var gMap = new google.maps.Map(document.getElementById('map'+this.model.get('id')), mapOptions);
               new google.maps.Marker({
                  position: latLng,
                  map: gMap
               });
               $('#map' + this.model.get('id')).addClass('loaded');
            }
            app.tagStats().hover(this.model);
         }
      },

      hoverOut: function() {
         if (!this.popoverDesactivated) {
            var that = this;
            this.hoverTimeout = setTimeout(function() {
               $(that.el).find('.popover').hide();
            }, 200);
         }
      },

      clickTag: function() {
         app.tagStats().click(this.model);
      },

      validateTag: function() {
         this.model.changeValidationStatus('granted');
      },

      refuseTag: function() {
         this.model.changeValidationStatus('denied');
      },

      deleteTag: function() {
         this.model.delete();
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut",
         "click a[href]": "clickTag",
         "click .validateTagButton": "validateTag",
         "click .refuseTagButton": "refuseTag",
         "click .deleteTagButton": "deleteTag"
      }
   });

   Tag.Views.ProductItem = Tag.Views.Item.extend({
      template: "tag/types/product",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return { model: this.model };
      },

      initialize: function(options) {
         this.constructor.__super__.initialize.apply(this, [options]);
         if (this.model.has('product') && typeof this.model.get('product').attributes === 'undefined')
            this.model.set('product', new Product.Model(this.model.get('product')));
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         if (!this.popoverDesactivated) {
            clearTimeout(this.hoverTimeout);
            var popover = $(this.el).find('.popover');
            var popoverArrow = $(this.el).find('.tag-popover-arrow');
            this.setupPopover(popover, popoverArrow);
            if (this.model.has('venue') && !$('#map' + this.model.get('id')).hasClass('loaded')) {
               var latLng = new google.maps.LatLng(this.model.get('venue').lat, this.model.get('venue').lng);
               var mapOptions = {
                  zoom:  14,
                  center: latLng,
                  scrollwheel: false,
                  navigationControl: false,
                  mapTypeControl: false,
                  scaleControl: false,
                  draggable: false,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
               };
               var gMap = new google.maps.Map(document.getElementById('map'+this.model.get('id')), mapOptions);
               new google.maps.Marker({
                  position: latLng,
                  map: gMap
               });
               $('#map' + this.model.get('id')).addClass('loaded');
            }
            app.tagStats().hover(this.model);
         }
      },

      hoverOut: function() {
         if (!this.popoverDesactivated) {
            var that = this;
            this.hoverTimeout = setTimeout(function() {
               $(that.el).find('.popover').hide();
            }, 200);
         }
      },

      clickTag: function() {
         app.tagStats().click(this.model);
      },

      validateTag: function() {
         this.model.changeValidationStatus('granted');
      },

      refuseTag: function() {
         this.model.changeValidationStatus('denied');
      },

      deleteTag: function() {
         this.model.delete();
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut",
         "click a[href]": "clickTag",
         "click .validateTagButton": "validateTag",
         "click .refuseTagButton": "refuseTag",
         "click .deleteTagButton": "deleteTag"
      }
   });

   Tag.Views.List = Backbone.View.extend({
      template: "tag/list",

      serialize: function() {
         return {
            visible: this.visible
         };
      },

      initialize: function() {
         var that = this;
         this.visible = typeof this.options.visible === 'undefined' ? false : this.options.visible;
         this.tags = typeof this.options.tags === 'undefined' ? new Tag.Collection() : this.options.tags;
         this.desactivatePopover = typeof this.options.desactivatePopover === 'undefined' ? false : true;
         this.photo = this.options.photo;
         this.listenTo(this.tags, {
            'add': this.render,
            'remove': function(tag) {
               // If it's a persisted tag, re-render the view and fire an event
               if (!tag.has('tempTag')) {
                  this.trigger('tag:remove');
                  this.render();
               }
            }
         });
         this.listenTo(app, 'tagMenuTools:tagAdded', function(photo) {
            if (typeof photo !== 'undefined' && typeof that.photo !== 'undefined' && that.photo.get('id') == photo.get('id')) {
               that.photo.changeTagsCount(1);
               that.tags.trigger('relayout');
            }
         });
      },

      beforeRender: function() {
         this.tags.each(function(tag) {
            if (tag.get('type') == 'place') {
               this.insertView(".tags", new Tag.Views.VenueItem({
                  model: tag,
                  desactivatePopover: this.desactivatePopover
               }));
            } else if (tag.get('type')  == 'person') {
               this.insertView(".tags", new Tag.Views.PersonItem({
                  model: tag,
                  desactivatePopover: this.desactivatePopover
               }));
            } else if (tag.get('type')  == 'product') {
               this.insertView(".tags", new Tag.Views.ProductItem({
                  model: tag,
                  desactivatePopover: this.desactivatePopover
               }));
            }
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      showTags: function(show) {
         $tags = $(this.el).find('.tags');
         if ($tags.length > 0) {
            if (typeof show === 'undefined') {
               if ($tags.data('state') == 'hidden') {
                  this.changeTagsVisibility(true, $tags);
               } else {
                  this.changeTagsVisibility(false, $tags);
               }
            } else {
               this.changeTagsVisibility(show, $tags);
            }
         }
      },

      changeTagsVisibility: function(show, tags) {
         if (show) {
            tags.stop().fadeIn('fast');
            tags.data('state', 'visible')
         } else {
            tags.stop().fadeOut('fast');
            tags.data('state', 'hidden');
         }
      }
   });

   Tag.Views.MenuTools = Backbone.View.extend({
      template: "tag/menuTools",

      initialize: function() {
         var that = this;
         var photo = app.appState().getCurrentPhotoModel();
         // Get current photo tags
         if (photo && app.appState().getCurrentPhotoModel().has('tags')) {
             tags = app.appState().getCurrentPhotoModel().get('tags');
         } else {
            tags = new Tag.Collection();
         }
         this.listenTo(app, 'tagMenuTools:addTag', function() {
            that.addTag();
         });
         this.listenTo(app, 'global:closeMenuTools', function() {
            that.unloadTagging();
         });
         this.listenTo(app, 'tagMenuTools:tagAdded', function() {
            that.unloadTagging();
            if (this.tagsView)
               this.tagsView.render();
         });
      },

      cancel: function() {
         this.unloadTagging();
         app.trigger('tagMenuTools:cancel');
      },

      addTag: function() {
         $photo = $('#photos-grid .large');
         if ($photo.length) {
            currentPhotoOverlay = $photo.find('.photo-overlay');
            this.setupTagging();
         }
      },

      setupTagging: function() {
         currentPhotoOverlay.css({ cursor: 'crosshair'});
         currentPhotoOverlay.bind('click', this.photoOverlayClick);
         // Get the tags view
         this.tagsView = app.useLayout().getView(currentPhotoOverlay.find('.tags-container').selector);
         // Create if not exist
         if (typeof this.tagsView === 'undefined' || !this.tagsView) {
            this.tagsView = app.useLayout().setView(currentPhotoOverlay.find('.tags-container').selector, new Tag.Views.List({
               tags: tags,
               visible: true
            })).render();
         }
         // Just show tags
         else {
            this.tagsView.visible = true;
         }
      },

      unloadTagging: function() {
         if (currentPhotoOverlay) {
            currentPhotoOverlay.css({ cursor: 'pointer'});
            currentPhotoOverlay.unbind('click', this.photoOverlayClick);
         }
      },

      photoOverlayClick: function(e) {
         var tagRadius = 12.5;
         var xPosition = (e.offsetX - tagRadius) / e.currentTarget.clientWidth;
         var yPosition = (e.offsetY - tagRadius) / e.currentTarget.clientHeight;

         // Remove tags arent persisted
         tags.each(function(tag) {
            if (tag.has('tempTag')) {
               tags.remove(tag);
            }
         });

         var tag = new Tag.Model();
         tag.set('x_position', xPosition);
         tag.set('y_position', yPosition);
         tag.set('cssClass', 'new-tag');
         tag.set('tempTag', true);
         tags.add(tag);
         currentTag = tag;

         app.useLayout().setView("#menu-tools .tag-form", new Tag.Views.AddTagForm({
            photo: this.photo
         })).render();
         $('.tag-text').fadeOut('fast', function() {
            $('.tag-form').fadeIn();
         });
      },

      close: function() {
         this.unloadTagging();
         app.trigger('global:closeMenuTools');
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      events: {
         "click .cancel-add-tag": "cancel"
      }
   });

   Tag.Views.AddTagForm = Backbone.View.extend({
      template: "tag/addForm",

      initialize: function() {
         this.photo = this.options.photo;
      },

      afterRender: function() {
         $(this.el).i18n();

         // Tabs
         $('.nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
         });

         // Brand/Product
         $('#brand-name').typeahead({
            source: function(query, process) {
               $('#loading-brand').css({'display': 'inline-block'});
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_get_brand_search', { query: query }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           if (typeof response !== 'undefined' && response.length > 0) {
                              var brands = [];
                              currentBrands = {};
                              _.each(response, function(brand) {
                                 brands.push(brand.name);
                                 currentBrands[brand.name] = brand;
                              });
                              process(brands);
                              $('#loading-brand').fadeOut(200);
                           }
                        }
                     });
                  }
               });
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               currentBrand = currentBrands[selectedItem];
               if (currentBrand) {
                  $('#brand-logo').html('<img src="' + currentBrand.medium_logo_url + '" style="margin: 10px 0px;" class="brand-logo" />');
               }
               return selectedItem;
            },
            highlighter: function(item) {
               return '<div><img style="height: 20px;" src="' + currentBrands[item].small_logo_url + '"> ' + item + '</div>'
            }
         });

         $('#product-name').typeahead({
            source: function(query, process) {
               $('#loading-product').css({'display': 'inline-block'});
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_get_product_search', { query: query }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           if (typeof response !== 'undefined' && response.length > 0) {
                              var products = [];
                              currentProducts = {};
                              _.each(response, function(product) {
                                 products.push(product.name);
                                 currentProducts[product.name] = product;
                              });
                              process(products);
                              $('#loading-product').fadeOut(200);
                           }
                        }
                     });
                  }
               });
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               currentProduct = currentProducts[selectedItem];
               if (currentProduct) {
                  $('#product-image').html('<img src="' + currentProduct.medium_url + '" style="margin: 10px 0px;" class="product-image" />');
                  $('#product-description').html(currentProduct.description);
                  $('#product-purchase-url').val(currentProduct.purchase_url);
                  // Check if product has a brand
                  if (currentProduct.brand) {
                     currentBrand = currentProduct.brand;
                     $('#brand-name').val(currentBrand.name);
                     $('#brand-logo').html('<img src="' + currentBrand.medium_logo_url + '" style="margin: 10px 0px;" class="brand-logo" />');
                  }
               }
               return selectedItem;
            },
            highlighter: function(item) {
               return '<div><img style="height: 20px;" src="' + currentProducts[item].small_url + '"> ' + item + '</div>'
            }
         });

         $('#fileupload').attr("data-url", Routing.generate('upload_product_photo'));
         $('#fileupload').fileupload({
            dataType: 'json',
            done: function (e, data) {
               if (data.result) {
                  $('#product-image').html('<img src="' + app.rootUrl + 'uploads/products/small/' + data.result.small.filename + '" style="margin: 10px 0px;" class="product-image" />');
                  if (!newProduct)
                     newProduct = new Product.Model();
                  newProduct.set('small_url', data.result.small.filename);
                  newProduct.set('medium_url', data.result.medium.filename);
                  newProduct.set('original_url', data.result.original);
               } else {
                  app.useLayout().setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.errorProductImageUpload'),
                     showClose: true
                  })).render();
               }
            }
         });

         // Venue
         if (!Modernizr.geolocation) {
            $('.support-geolocation').fadeOut('fast', function() {
               $('.not-support-geolocation').fadeIn('fast');
            });
         }
         var that = this;
         $('#venue-name').typeahead({
            source: function(query, process) {
               return that.venueSource(query, process, 'loading-venue');
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               return that.venueUpdater(selectedItem, 'previsualisation-tag-map', 'venue-informations', 'venue-link');
            },
            highlighter: that.venueHighlighter
         });
         $('#product-venue-name').typeahead({
            source: function(query, process) {
               return that.venueSource(query, process, 'product-loading-venue');
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               return that.venueUpdater(selectedItem, 'product-previsualisation-tag-map', 'product-venue-informations', null);
            },
            highlighter: that.venueHighlighter
         });

         // Person
         if (!app.fb.isConnected()) {
            $('.tab-pane .fb-loggedin').fadeOut('fast');
            $('.tab-pane .fb-loggedout').fadeIn('fast');
         } else {
            $('.tab-pane .fb-loggedout').fadeOut('fast');
            $('.tab-pane .fb-loggedin').fadeIn('fast');
         }
         $('#person-text').typeahead({
            source: function(query, process) {
               $('#loading-person').css({'display': 'inline-block'});
               app.fb.loadFriends({
                  success: function(friends) {
                     var friendsNames = [];
                     _.each(friends, function(friend) {
                        friendsNames.push(friend.name);
                     });
                     process(friendsNames);
                     $('#loading-person').fadeOut(200);
                  }
               });
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               app.fb.loadFriends({
                  success: function(friends) {
                     currentPerson = _.find(friends, function(friend) {
                        if (friend.name == selectedItem)
                           return friend;
                     });
                  }
               });
               return selectedItem;
            }
         });
      },

      venueSource: function(query, process, loadingDiv) {
         if (venuesSearchTimeout)
            clearTimeout(venuesSearchTimeout);
         venuesSearchTimeout = setTimeout(function() {
            $('#'+loadingDiv).css({'display': 'inline-block'});
            app.oauth.loadAccessToken({
               success: function() {
                  var url = Routing.generate('api_v1_get_venue_search', { query: query });
                  if (app.appState().getCurrentPosition()) {
                     url += '?ll=' + app.appState().getCurrentPosition().coords.latitude + ',' + app.appState().getCurrentPosition().coords.longitude;
                  }
                  $.ajax({
                     url: url,
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     data: {
                        radius: 3000
                     },
                     success: function(data) {
                        if (typeof data !== 'undefined' && data.length > 0) {
                           var venues = []
                           currentVenues = {};
                           _.each(data, function(venue) {
                              venues.push(venue.name + ' |{' + venue.foursquare_id);
                              currentVenues[venue.name + ' |{' + venue.foursquare_id] = venue;
                           });
                           process(venues);
                        }
                        $('#'+loadingDiv).fadeOut(200);
                     }
                  });
               }
            });
         }, 500);
      },

      venueUpdater: function(selectedItem, mapDiv, venueInformationDiv, venueLinkDiv) {
         currentVenue = currentVenues[selectedItem];
         var latLng = new google.maps.LatLng(currentVenue.lat, currentVenue.lng);
         var mapOptions = {
            zoom:  14,
            center: latLng,
            scrollwheel: false,
            navigationControl: false,
            mapTypeControl: false,
            scaleControl: false,
            draggable: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
         };
         $('#'+mapDiv).css({
            'width': '100%',
            'height': '150px',
            'margin': '10px 0px'
         });
         if (currentVenue.address) {
            $('#'+venueInformationDiv).html('<span class="muted">' + currentVenue.address + (currentVenue.postal_code ? ' ' + currentVenue.postal_code : '') + (currentVenue.city ? ' ' + currentVenue.city : '') + '</span>').css({
               'margin': '10px 0px'
            });
         }
         var gMap = new google.maps.Map(document.getElementById(mapDiv), mapOptions);
         new google.maps.Marker({
            position: latLng,
            map: gMap
         });
         if (venueLinkDiv) {
            $('#'+venueLinkDiv).val(currentVenue.link ? currentVenue.link : '');
         }
         return currentVenue.name;
      },

      venueHighlighter: function(item) {
         return '<div>' + currentVenues[item].name + ' <small class="muted">' + currentVenues[item].address + (currentVenues[item].postal_code ? ' ' + currentVenues[item].postal_code : '') + (currentVenues[item].city ? ' ' + currentVenues[item].city : '') + '</small></div>'
      },

      geolocation: function(e) {
         e.preventDefault();
         if (Modernizr.geolocation) {
            var btn = $(e.currentTarget);
            btn.button('loading');
            navigator.geolocation.getCurrentPosition(function(position) {
               app.appState().set('currentPosition', position);
               btn.button('reset');
               $(e.currentTarget).parents('.support-geolocation').html('<div class="alert fade in alert-success"><small>' + $.t('tag.geolocationSuccess') + '</small></div>');
            });
         }
      },

      cancel: function(e) {
         e.preventDefault();
         // Remove current tag
         if (currentTag)
            tags.remove(currentTag);
         app.appState().set('currentPosition', '');
         // Hide form
         app.trigger('tagMenuTools:cancel');
      },

      submit: function(e) {
         e.preventDefault();
         $activePane = $('.tab-content .active');

         var that = this;

         switch ($activePane.attr('id')) {
            case 'product':
               $submit = $('#submit-product');
               if (currentTag) {
                  if (newProduct) {
                     $submit.button('loading');
                     if (!currentBrand) {
                        $submit.button('reset');
                        app.useLayout().setView('.alert-product', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('tag.errorSelectBrand'),
                           showClose: true
                        })).render();
                        return;
                     }
                     if (!$('#product-description').val() || !$('#product-name').val()) {
                        $submit.button('reset');
                        app.useLayout().setView('.alert-product', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('tag.errorProductEmptyFields'),
                           showClose: true
                        })).render();
                        return;
                     }
                     newProduct.url = Routing.generate('api_v1_post_product');
                     newProduct.set('description', $('#product-description').val());
                     newProduct.set('purchase_url', $('#product-purchase-url').val());
                     newProduct.set('name', $('#product-name').val());
                     newProduct.set('brand', currentBrand.id);
                     newProduct.getToken('product_item', function() {
                        newProduct.save(null, {
                           success: function() {
                              if (currentVenue) {
                                 // POST currentVenue
                                 venue = new Venue.Model();
                                 venue.entityToModel(currentVenue);
                                 venue.set('products', [ newProduct.get('id') ]);
                                 venue.url = Routing.generate('api_v1_post_venue');
                                 venue.getToken('venue_item', function() {
                                    venue.save(null, {
                                       success: function() {
                                          that.postProduct($submit, newProduct);
                                       },
                                       error: function() {
                                          $submit.button('reset');
                                          app.useLayout().setView('.alert-product', new Common.Views.Alert({
                                             cssClass: Common.alertError,
                                             message: $.t('tag.errorVenuePost'),
                                             showClose: true
                                          })).render();
                                       }
                                    });
                                 });
                              } else {
                                 that.postProduct($submit, newProduct);
                              }
                           },
                           error: function() {
                              $submit.button('reset');
                              app.useLayout().setView('.alert-product', new Common.Views.Alert({
                                 cssClass: Common.alertError,
                                 message: $.t('tag.errorProductPost'),
                                 showClose: true
                              })).render();
                           }
                        });
                     });
                  } else if (currentProduct) {
                     // Check if there is a venue for the current product
                     if (currentVenue) {
                        // POST currentVenue
                        venue = new Venue.Model();
                        venue.entityToModel(currentVenue);
                        venue.set('products', [ currentProduct.id ]);
                        venue.url = Routing.generate('api_v1_post_venue');
                        venue.getToken('venue_item', function() {
                           venue.save(null, {
                              success: function() {
                                 that.postProduct($submit);
                              },
                              error: function() {
                                 $submit.button('reset');
                                 app.useLayout().setView('.alert-product', new Common.Views.Alert({
                                    cssClass: Common.alertError,
                                    message: $.t('tag.errorVenuePost'),
                                    showClose: true
                                 })).render();
                              }
                           });
                        });
                     } else {
                        this.postProduct($submit);
                     }
                  }
               } else {
                  $submit.button('reset');
                  app.useLayout().setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.noProductSelected'),
                     showClose: true
                  })).render();
               }
               break;
            case 'venue':
               $submit = $('#submit-venue');
               if (currentVenue && currentTag) {
                  $submit.button('loading');
                  // Set venue info
                  currentVenue.link = $('#venue-link').val();
                  currentVenue.description = $('#venue-description').val();
                  // POST currentVenue
                  venue = new Venue.Model();
                  venue.entityToModel(currentVenue);
                  venue.url = Routing.generate('api_v1_post_venue');
                  venue.getToken('venue_item', function() {
                     venue.save(null, {
                        success: function() {
                           // Link tag to photo
                           currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
                           // Set tag info
                           currentTag.set('type', 'place');
                           currentTag.set('venue', venue.get('id'));
                           currentTag.set('title', currentVenue.name);
                           currentTag.set('description', currentVenue.description);
                           currentTag.set('link', currentVenue.link);
                           currentTag.url = Routing.generate('api_v1_post_tag');
                           currentTag.getToken('tag_item', function() {
                              currentTag.save(null, {
                                 success: function() {
                                    currentTag.set('persisted', '');
                                    app.fb.createVenueStory(venue, app.appState().getCurrentPhotoModel());
                                    app.trigger('tagMenuTools:tagAdded', app.appState().getCurrentPhotoModel());
                                 },
                                 error: function() {
                                    $submit.button('reset');
                                    app.useLayout().setView('.alert-venue', new Common.Views.Alert({
                                       cssClass: Common.alertError,
                                       message: $.t('tag.errorTagPost'),
                                       showClose: true
                                    })).render();
                                 }
                              });
                           });
                        },
                        error: function(e, r) {
                           $submit.button('reset');
                           if (r.status === 403) {
                              app.useLayout().setView('.alert-person', new Common.Views.Alert({
                                 cssClass: Common.alertError,
                                 message: $.t('tag.forbiddenTagPost'),
                                 showClose: true
                              })).render();
                           } else {
                              app.useLayout().setView('.alert-venue', new Common.Views.Alert({
                                 cssClass: Common.alertError,
                                 message: $.t('tag.errorVenuePost'),
                                 showClose: true
                              })).render();
                           }
                        }
                     });
                  });
               } else {
                  $submit.button('reset');
                  app.useLayout().setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.noVenueSelected'),
                     showClose: true
                  })).render();
               }
               break;
            case 'person':
               $submit = $('#submit-person');
               if (currentPerson) {
                  $submit.button('loading');
                  person = new Person.Model();
                  person.entityToModel(currentPerson);
                  person.url = Routing.generate('api_v1_post_person');
                  person.getToken('person_item', function() {
                     person.save(null, {
                        success: function() {
                           // Link tag to photo
                           currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
                           // Set tag info
                           currentTag.set('type', 'person');
                           currentTag.set('person', person.get('id'));
                           currentTag.set('title', currentPerson.name);
                           currentTag.set('link', 'https://www.facebook.com/' + currentPerson.id);
                           currentTag.url = Routing.generate('api_v1_post_tag');
                           currentTag.getToken('tag_item', function() {
                              currentTag.save(null, {
                                 success: function() {
                                    currentTag.set('persisted', '');
                                    app.fb.createPersonStory(person, app.appState().getCurrentPhotoModel());
                                    app.trigger('tagMenuTools:tagAdded', app.appState().getCurrentPhotoModel());
                                 },
                                 error: function(e, r) {
                                    if (r.status === 403) {
                                       app.useLayout().setView('.alert-person', new Common.Views.Alert({
                                          cssClass: Common.alertError,
                                          message: $.t('tag.forbiddenTagPost'),
                                          showClose: true
                                       })).render();
                                    } else {
                                       app.useLayout().setView('.alert-person', new Common.Views.Alert({
                                          cssClass: Common.alertError,
                                          message: $.t('tag.errorTagPost'),
                                          showClose: true
                                       })).render();
                                    }
                                    $submit.button('reset');
                                 }
                              });
                           });
                        },
                        error: function() {
                           $submit.button('reset');
                           // TODO: show alert
                        }
                     });
                  });
               }
               break;
         }
      },

      postProduct: function($submit, newProduct) {
         // Link tag to photo
         currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
         // Set tag info
         currentTag.set('type', 'product');
         currentTag.set('product', typeof newProduct !== 'undefined' ? newProduct.get('id') : currentProduct.id);
         currentTag.set('title', typeof newProduct !== 'undefined' ? newProduct.get('name') : currentProduct.name);
         currentTag.set('description', typeof newProduct !== 'undefined' ? newProduct.get('description') : currentProduct.description);
         currentTag.set('link', typeof newProduct !== 'undefined' ? newProduct.get('purchase_url') : currentProduct.purchase_url);
         currentTag.url = Routing.generate('api_v1_post_tag');
         currentTag.getToken('tag_item', function() {
            currentTag.save(null, {
               success: function() {
                  currentTag.set('persisted', '');
                  app.fb.createBrandTagStory(currentBrand, app.appState().getCurrentPhotoModel());
                  app.trigger('tagMenuTools:tagAdded', app.appState().getCurrentPhotoModel());
               },
               error: function(e, r) {
                  $submit.button('reset');
                  if (r.status === 403) {
                     app.useLayout().setView('.alert-product', new Common.Views.Alert({
                        cssClass: Common.alertError,
                        message: $.t('tag.forbiddenTagPost'),
                        showClose: true
                     })).render();
                  } else {
                     app.useLayout().setView('.alert-product', new Common.Views.Alert({
                        cssClass: Common.alertError,
                        message: $.t('tag.errorTagPost'),
                        showClose: true
                     })).render();
                  }
               }
            });
         });
      },

      createProduct: function(e) {
         e.preventDefault();
         var that = this;
         $(this.el).find('.createProductButtonContainer').fadeOut('fast', function() {
            $(that.el).find('.createProductContainer').fadeIn('fast');
         });
         newProduct = new Product.Model();
      },

      events: {
         "click .cancel-add-tag": "cancel",
         "click .btn-geolocation": "geolocation",
         "click .submitTagButton": "submit",
         "click .createProductButton": "createProduct"
      }
   });

   return Tag;
});