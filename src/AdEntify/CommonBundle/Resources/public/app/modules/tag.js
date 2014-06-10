/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/venue',
   'modules/person',
   'modules/common',
   'modules/product',
   'select2',
   'bootstrap',
   'modernizer',
   'jquery.iframe-transport',
   'jquery.fileupload',
   'typeahead'
], function(app, Venue, Person, Common, Product) {

   var Tag = app.module();
   var currentTag = null;
   var tags = null;
   var currentBrands = [];
   var currentBrand = null;
   var currentProducts = [];
   var currentProductTypes = [];
   var currentProduct = null;
   var currentProductType = null;
   var currentPeople = [];
   var newProduct = null;
   var currentVenues = [];
   var currentVenue = null;
   var currentPerson = null;
   var venuesSearchTimeout = null;

   Tag.Model = Backbone.Model.extend({

      defaults: {
         cssClass: null
      },

      initialize: function() {
         this.setup();
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });

         this.listenTo(this, 'change', this.render);
      },

      setup: function() {
         if (this.has('waiting_validation') && this.get('waiting_validation')) {
            if (this.has('validation_status') && this.get('validation_status') == 'waiting')
               this.set('cssClass', 'unvalidateTag');
            else
               this.set('cssClass', '');
         }
         if (this.has('owner') && !this.has('ownerModel')) {
            var User = require('modules/user');
            this.set('ownerModel', new User.Model(this.get('owner')));
         }
         if (this.has('person') && !this.has('personModel')) {
            var User = require('modules/user');
            this.set('personModel', new User.Model(this.get('person')));
         }
         if (this.has('brand') && !this.has('brandModel')) {
            var Brand = require('modules/brand');
            this.set('brandModel', new Brand.Model(this.get('brand')));
         }
         if (this.has('venue') && typeof this.get('venue').attributes === 'undefined') {
            this.set('venue', new Venue.Model(this.get('venue')));
         }
         if (this.has('product') && typeof this.get('product').attributes === 'undefined') {
            this.set('product', new Product.Model(this.get('product')));
         }
         if (this.has('person') && typeof this.get('person').attributes === 'undefined') {
            this.set('person', new Person.Model(this.get('person')));
         }
         this.set('cssStyle', 'left: ' + this.get('x_position') * 100 + '%; top: ' + this.get('y_position') * 100 + '%; margin-left: ' + (-Tag.Common.tagRadius) + 'px; margin-top: ' + (-Tag.Common.tagRadius) + 'px');
      },

      urlRoot: function() {
         return Routing.generate('api_v1_get_tag');
      },

      toJSON: function() {
         var jsonAttributes = this.attributes;
         delete jsonAttributes.cssClass;
         delete jsonAttributes.cssStyle;
         delete jsonAttributes.tempTag;
         delete jsonAttributes.tagIcon;
         return { tag: jsonAttributes };
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
          return this && this.has('owner') ? currentUserId === this.get('owner')['id'] : false;
      },

      isPhotoOwner: function() {
         return app.appState().getCurrentPhotoModel() !== 'undefined' ? app.appState().getCurrentPhotoModel().isOwner() : false;
      },

      isWaitingValidation: function() {
         return this.get('validation_status') == "waiting" && this.get('waiting_validation') && this.isPhotoOwner();
      },

      delete: function() {
         // Check if currentUser is the owner
         if (this.isOwner()) {
            var that = this;
            this.destroy({
               url: Routing.generate('api_v1_delete_tag', { id : this.get('id') } ),
               success: function() {
                  that.trigger('delete:success');
                  app.trigger('tag:deleted', that);
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
      popoverDesactivated: false,

      initialize: function() {
         this.popoverDesactivated = typeof this.options.desactivatePopover !== 'undefined' ? this.options.desactivatePopover : this.popoverDesactivated;
      },

      afterRender: function() {
         var deleteTagButton = this.$('.deleteTagButton');
         if (deleteTagButton.length > 0)
            deleteTagButton.tooltip();
      },

      setupPopover: function(popover, popoverArrow) {
         if (this.model) {
            if (this.model.get('y_position') > 0.7) {
               popoverArrow.addClass('tag-popover-arrow-bottom');
               popoverArrow.css({bottom: '-10px'});
            } else {
               popoverArrow.css({top: '-10px'});
               popoverArrow.addClass('tag-popover-arrow-top');
            }
            if (this.model.get('x_position') > 0.5) {
               popoverArrow.css({right: '10px'});
            } else {
               popoverArrow.css({left: '20px'});
            }
            popover.css({top: this.model.get('y_position') > 0.7 ? '-'+(popover.height() + 18)+'px' : '46px'});
            popover.css({left: this.model.get('x_position') > 0.5 ? '-'+(popover.width() - 31)+'px' : '-8px'});
            popover.fadeIn(100);
         }
      }
   });

   Tag.Views.NewItem = Backbone.View.extend({
      template: 'tag/types/newTag',
      tagName: 'li',

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });

   Tag.Views.PersonItem = Tag.Views.Item.extend({
      template: "tag/types/person",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return {
            model: this.model,
            popoverDesactivated: this.popoverDesactivated,
            rootUrl: app.beginUrl + app.root
         };
      },

      initialize: function(options) {
         this.constructor.__super__.initialize.apply(this, [options]);
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         if (!this.popoverDesactivated) {
            clearTimeout(this.hoverTimeout);
            var popover = this.$('.popover');
            var popoverArrow = this.$('.tag-popover-arrow');
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

      clickTag: function(e) {
         app.tagStats().click(this.model, e);
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

      reportTag: function() {
         Tag.Common.reportTag(this.model);
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut",
         "click a[href]": "clickTag",
         "click .validateTagButton": "validateTag",
         "click .refuseTagButton": "refuseTag",
         "click .deleteTagButton": "deleteTag",
         'click .reportTagButton': 'reportTag'
      }
   });

   Tag.Views.VenueItem = Tag.Views.Item.extend({
      template: "tag/types/venue",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return {
            model: this.model,
            popoverDesactivated: this.popoverDesactivated
         };
      },

      initialize: function(options) {
         this.constructor.__super__.initialize.apply(this, [options]);
         this.listenTo(this.model, 'change', this.render);
      },

      beforeRender: function() {
         if (!this.getView('.popover-details')) {
            this.setView('.popover-details', new Venue.Views.Address({
               model: this.model.get('venue')
            }));
         }
      },

      hoverIn: function() {
         if (!this.popoverDesactivated) {
            clearTimeout(this.hoverTimeout);
            var popover = this.$('.popover');
            var popoverArrow = this.$('.tag-popover-arrow');
            this.setupPopover(popover, popoverArrow);
            if (!$('#map' + this.model.get('id')).hasClass('loaded')) {
               Tag.Common.setupGoogleMap(this.model);
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

      clickTag: function(e) {
         app.tagStats().click(this.model, e);
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

      reportTag: function() {
         Tag.Common.reportTag(this.model);
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut",
         "click a[href]": "clickTag",
         "click .validateTagButton": "validateTag",
         "click .refuseTagButton": "refuseTag",
         "click .deleteTagButton": "deleteTag",
         'click .reportTagButton': 'reportTag'
      }
   });

   Tag.Views.ProductItem = Tag.Views.Item.extend({
      template: "tag/types/product",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return {
            model: this.model,
            popoverDesactivated: this.popoverDesactivated
         };
      },

      beforeRender: function() {
         if (this.model.has('venue') && !this.getView('.venue-adress-wrapper')) {
            this.setView('.venue-adress-wrapper', new Venue.Views.Address({
               model: this.model.get('venue')
            }));
         }
      },

      initialize: function(options) {
         this.constructor.__super__.initialize.apply(this, [options]);
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         if (!this.popoverDesactivated) {
            clearTimeout(this.hoverTimeout);
            var popover = this.$('.popover');
            var popoverArrow = this.$('.tag-popover-arrow');
            this.setupPopover(popover, popoverArrow);
            if (this.model.has('venue') && !$('#map' + this.model.get('id')).hasClass('loaded')) {
               Tag.Common.setupGoogleMap(this.model);
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

      clickTag: function(e) {
         app.tagStats().click(this.model, e);
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

      reportTag: function() {
         Tag.Common.reportTag(this.model);
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut",
         "click a[href]": "clickTag",
         "click .validateTagButton": "validateTag",
         "click .refuseTagButton": "refuseTag",
         "click .deleteTagButton": "deleteTag",
         'click .reportTagButton': 'reportTag'
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
         this.tags = this.options.tags instanceof Array ? new Tag.Collection(this.options.tags) : this.options.tags;
         this.desactivatePopover = typeof this.options.desactivatePopover === 'undefined' ? false : true;
         this.photo = this.options.photo;
         this.listenTo(this.tags, {
            'add': this.render,
            'remove': function(tag) {
               this.trigger('tag:remove');
               this.render();
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
         var that = this;
         this.tags.each(function(tag) {
            if (tag.get('validation_status') == 'waiting' && !that.photo.isOwner()) {
               return;
            }

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
            } else if (tag.get('type')  == 'product' || tag.get('type') == 'brand') {
               this.insertView(".tags", new Tag.Views.ProductItem({
                  model: tag,
                  desactivatePopover: this.desactivatePopover
               }));
            } else if (tag.has('tempTag')) {
               this.insertView('.tags', new Tag.Views.NewItem({
                  model: tag
               }));
            }

         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      showTags: function(show) {
         $tags = this.$('.tags');
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
            tags.data('state', 'visible');
         } else {
            tags.stop().fadeOut('fast');
            tags.data('state', 'hidden');
         }
      }
   });

   Tag.Views.AddModal = Backbone.View.extend({
      template: 'tag/addModal',

      beforeRender: function() {
         var photoEditView = new this.Photo.Views.Edit({
            model: this.options.photo,
            photoCategories: this.options.photoCategories,
            photoHashtags: this.options.photoHashtags
         });
         this.setViews({
            "#center-modal-content": photoEditView,
            "#right-modal-content": new Tag.Views.AddTagForm({
               photo: this.options.photo
            })
         });
      },

      initialize: function() {
         this.Photo = require('modules/photo');
         this.photo = this.options.photo;
         var that = this;
         this.listenTo(app, 'tagMenuTools:tagAdded', function(photo) {
            if (typeof photo !== 'undefined' && typeof that.photo !== 'undefined' && that.photo.get('id') == photo.get('id')) {
               that.render();
               this.setView('.alert-container', new Common.Views.Alert({
                  cssClass: Common.alertSuccess,
                  message: $.t('tag.tagSuccessfullyAdded'),
                  showClose: true
               })).render();
               setTimeout(function() {
                  that.removeView('.alert-container');
               }, 3000);
            }
         });
      }
   });

   Tag.Views.AddTagForm = Backbone.View.extend({
      template: "tag/addForm",

      initialize: function() {
         this.photo = this.options.photo;
         this.listenTo(app, 'photo:tagAdded', function(tag) {
            var that = this;
            currentTag = tag;
            if (this.$('.tag-text:visible').length > 0) {
               this.$('.tag-text').fadeOut('fast', function() {
                  $(that.el).find('.tag-tabs').fadeIn('fast');
               });
            }
         });
         this.listenTo(app, 'addTagModal:hide', function() {
            if (currentTag && currentTag.has('tempTag') && currentTag.get('tempTag') == true)
               app.trigger('photo:tagRemoved', currentTag);
         });
      },

      afterRender: function() {
         $(this.el).i18n();

         // Tabs
         $('.nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
            app.trigger('tagform:changetab', $(this).attr('href'));
         });

         var that = this;

         // Brand/Product
         $('#brand-name').keydown(function() {
            currentBrand = null;
         }).typeahead({
            source: function(query, process) {
               $('#loading-brand').css({'display': 'inline-block'});
               app.oauth.loadAccessToken({
                  success: function() {
                     $.ajax({
                        url: Routing.generate('api_v1_get_brand_search', { query: query }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           if (typeof response !== 'undefined' && response.data.length > 0) {
                              var brands = [];
                              brands.push(query);
                              currentBrands = {};
                              _.each(response.data, function(brand) {
                                 brands.push(brand.name);
                                 currentBrands[brand.name] = brand;
                              });
                              process(brands);
                           }
                        },
                        complete: function() {
                           $('#loading-brand').fadeOut(200);
                        }
                     });
                  }
               });
            },
            minLength: 1,
            items: 15,
            updater: function(selectedItem) {
               currentBrand = currentBrands[selectedItem];
               that.checkBrand();
               if (currentBrand && currentBrand.medium_logo_url) {
                  $('#brand-logo').html('<img src="' + currentBrand.medium_logo_url + '" style="margin: 10px 0px;" class="brand-logo" />');
               }
               return selectedItem;
            },
            highlighter: function(item) {
               var found = currentBrands[item];
               if (found) {
                  return '<div>' + (currentBrands[item].small_logo_url ? '<img style="height: 20px;" src="' + currentBrands[item].small_logo_url + '"> ' : '') + item + '</div>'
               } else {
                  return '<div class="new-item"><i class="glyphicon glyphicon-plus-sign"></i> ' + item + '</div>';
               }
            }
         });

         this.$('#product-name').typeahead({
            source: function(query, process) {
               $('#loading-product').css({'display': 'inline-block'});
               app.oauth.loadAccessToken({
                  success: function() {
                     that.productQuery = query;
                     var products = [];
                     currentProducts = [];
                     currentProductTypes = [];
                     $.when($.ajax({
                        url: Routing.generate('api_v1_get_product_search', { query: query, brandId: currentBrand ? currentBrand.id : 0 }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           if (typeof response !== 'undefined' && response.length > 0) {
                              _.each(response, function(product) {
                                 products.push(product.name);
                                 currentProducts.push(product);
                              });
                           }
                        }
                     }), $.ajax({
                        url: Routing.generate('api_v1_get_producttype_search', { query: query }),
                        headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                        success: function(response) {
                           if (typeof response !== 'undefined' && response.length > 0) {
                              _.each(response, function(productType) {
                                 products.push(productType.name);
                                 currentProductTypes.push(productType);
                              });
                           }
                        }
                     })).done(function(a1, a2) {
                           products.push(query + '#query');
                           process(products);
                           $('#loading-product').fadeOut(200);
                        });
                  }
               });
            },
            minLength: 1,
            items: 15,
            updater: function(selectedItem) {
               var productName = null;
               if (selectedItem.indexOf('#query') == -1) {
                  currentProduct = _.find(currentProducts, function(product) { return product.name == selectedItem; });
               } else {
                  productName = selectedItem.substring(0, selectedItem.indexOf('#query'));
               }

               that.checkBrand();
               if (currentProduct) {
                  if (currentProduct.medium_url)
                     $('#product-image').html('<img src="' + currentProduct.medium_url + '" style="margin: 10px 0px;" class="product-image" />');
                  $('#product-description').html(currentProduct.description);
                  $('#product-purchase-url').val(currentProduct.purchase_url);
                  // Check if product has a brand
                  if (currentProduct.brand) {
                     currentBrand = currentProduct.brand;
                     $('#brand-name').val(currentBrand.name);
                     if (currentBrand.medium_logo_url)
                        $('#brand-logo').html('<img src="' + currentBrand.medium_logo_url + '" style="margin: 10px 0px;" class="brand-logo" />');
                  }
               } else {
                  currentProductType = currentProductTypes[selectedItem];
               }

               return productName ? productName : selectedItem;
            },
            highlighter: function(item) {
               if (item.indexOf('#query') == -1) {
                  var product = _.find(currentProducts, function(product) { return product.name == item; });
                  var html = '<div>' + (typeof product.small_url !== 'undefined' && product.small_url ? '<img style="height: 20px;" src="' + product.small_url + '"> ' : '') + product.name;
                  if (product.brand) {
                     html += product.brand.small_logo_url ? ' <img style="height: 20px;" src="' + product.brand.small_logo_url + '" />' : product.brand.name;
                  }
                  html += '</div>';
                  return html;
               } else {
                  return '<div class="new-item"><i class="glyphicon glyphicon-plus-sign"></i> ' + item.substring(0, item.indexOf('#query')) + '</div>';
               }
            }
         });

         this.$('#fileupload').attr("data-url", Routing.generate('upload_product_photo'));
         this.$('#fileupload').fileupload({
            dataType: 'json',
            done: function (e, data) {
               if (data.result) {
                  $('#product-image').html('<img src="' + data.result.small.filename + '" style="margin: 10px 0px;" class="product-image" />');
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

         this.$('#venue-name').typeahead({
            source: function(query, process) {
               return that.venueSource(query, process, 'loading-venue');
            },
            minLength: 2,
            items: 30,
            updater: function(selectedItem) {
               return that.venueUpdater(selectedItem, 'previsualisation-tag-map', 'venue-informations', 'venue-link');
            },
            highlighter: that.venueHighlighter
         });
         this.$('#product-venue-name').typeahead({
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
         this.$('#person-text').typeahead({
            source: function(query, process) {
               if (app.fb.isConnected()) {
                  $('#loading-person').css({'display': 'inline-block'});
                  var people = [];

                  var deferreds = [];
                  /*deferreds.push(new $.Deferred());
                  // Load FB Friends
                  app.fb.loadFriends({
                     success: function(friends) {
                        _.each(friends, function(friend) {
                           people.push(friend.name);
                        });
                        deferreds.pop().resolve();
                     },
                     error: function() {
                        deferreds.pop().resolve();
                     }
                  });*/
                  deferreds.push(new $.Deferred());
                  app.oauth.loadAccessToken({
                     success: function() {
                        currentPeople = {};
                        $.ajax({
                              url: Routing.generate('api_v1_get_person_search', { query: query }),
                              headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                              success: function(response) {
                                 if (typeof response !== 'undefined' && response.length > 0) {
                                    _.each(response, function(person) {
                                       var personName = person.name ? person.name : person.firstname + ' ' + person.lastname;
                                       people.push(personName);
                                       currentPeople[personName] = person;
                                    });
                                 }
                                 deferreds.pop().resolve();
                              },
                              error: function() {
                                 deferreds.pop().resolve();
                              }
                           });
                     }
                  });

                  $.when.apply(null, deferreds).done(function() {
                     process(people);
                     $('#loading-person').fadeOut(200);
                  });
               }
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               currentPerson = currentPeople[selectedItem];
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
                     url += '?ll=' + app.appState().getCurrentPosition('lat') + ',' + app.appState().getCurrentPosition('lng');
                  }
                  $.ajax({
                     url: url,
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function(data) {
                        if (typeof data !== 'undefined' && data.length > 0) {
                           var venues = [];
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
            $('#'+venueInformationDiv).html('<span class="muted">' + (currentVenue.address + ' ' ? currentVenue.address : '') + (currentVenue.postal_code ? ' ' + currentVenue.postal_code : '') + (currentVenue.city ? ' ' + currentVenue.city : '') + '</span>').css({
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
         return '<div>' + currentVenues[item].name + ' <small class="muted">' + (currentVenues[item].address ? currentVenues[item].address : '') + (currentVenues[item].postal_code ? ' ' + currentVenues[item].postal_code : '') + (currentVenues[item].city ? ' ' + currentVenues[item].city : '') + '</small></div>'
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
         if (e)
            e.preventDefault();
         // Remove current tag
         if (currentTag)
            app.trigger('photo:tagRemoved', currentTag);
         app.appState().set('currentPosition', '');
         // Hide form
         var that = this;
         this.$('.tag-tabs').fadeOut('fast', function() {
            $(that.el).find('.tag-text').fadeIn('fast');
         });
      },

      submit: function(e) {
         e.preventDefault();
         var that = this;
         $activePane = this.$('.tab-content .active');
         switch ($activePane.attr('id')) {
            case 'product':
               $submit = $('#submit-product');
               that.removeView('.alert-product');
               if (currentTag) {
                  $submit.button('loading');

                  var tmpSubmitProduct = function() {
                     if (currentProduct || currentProductType) {
                        $submit.button('loading');
                        // Check if there is a venue for the current product
                        if (currentVenue && currentProduct) {
                           // POST currentVenue
                           venue = new Venue.Model();
                           venue.entityToModel(currentVenue);
                           if (venue.has('id')) {
                              that.postProduct($submit, undefined, venue);
                           } else {
                              venue.url = Routing.generate('api_v1_post_venue');
                              venue.getToken('venue_item', function() {
                                 venue.save(null, {
                                    success: function() {
                                       that.postProduct($submit, undefined, venue);
                                    },
                                    error: function() {
                                       $submit.button('reset');
                                       that.setView('.alert-product', new Common.Views.Alert({
                                          cssClass: Common.alertError,
                                          message: $.t('tag.errorVenuePost'),
                                          showClose: true
                                       })).render();
                                    }
                                 });
                              });
                           }
                        } else {
                           that.postProduct($submit);
                        }
                     } else if ($('#product-name').val()) {
                        that.createProduct();
                        that.submitNewProduct();
                     } else {
                        $submit.button('reset');
                        that.setView('.alert-product', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('tag.errorProductEmpty'),
                           showClose: true
                        })).render();
                     }
                  };

                  if (!currentBrand || (currentBrand && currentBrand.name != that.$('#brand-name').val())) {
                     if (that.$('#brand-name').val()) {
                        var brandModule = require('modules/brand');
                        currentBrand = new brandModule.Model();
                        currentBrand.set('name', that.$('#brand-name').val());
                        currentBrand.url = Routing.generate('api_v1_post_brand');
                        currentBrand.getToken('brand_item', function() {
                           currentBrand.save(null, {
                              success: function() {
                                 tmpSubmitProduct();
                              },
                              error: function() {
                                 $submit.button('reset');
                                 that.setView('.alert-product', new Common.Views.Alert({
                                    cssClass: Common.alertError,
                                    message: $.t('tag.errorBrandPost'),
                                    showClose: true
                                 })).render();
                              }
                           });
                        });
                     } else {
                        $submit.button('reset');
                        that.setView('.alert-product', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('tag.errorSelectBrand'),
                           showClose: true
                        })).render();
                        return;
                     }
                  } else {
                     tmpSubmitProduct();
                  }
               } else {
                  $submit.button('reset');
                  that.setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.noProductSelected'),
                     showClose: true
                  })).render();
               }
               break;
            case 'venue':
               $submit = $('#submit-venue');
               that.removeView('.alert-venue');
               if (!currentVenue && !this.$('#venue-name').val()) {
                  that.setView('.alert-venue', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.noVenueSelected'),
                     showClose: true
                  })).render();
               } else if (!currentVenue && this.$('#venue-name').val()) {
                  $submit.button('loading');
                  venue = new Venue.Model();
                  venue.set('name', this.$('#venue-name').val());
                  venue.set('link', $('#venue-link').val());
                  venue.set('description', $('#venue-description').val());
                  that.postVenue(venue, $submit);
               } else {
                  $submit.button('loading');
                  // Set venue info
                  currentVenue.link = $('#venue-link').val();
                  currentVenue.description = $('#venue-description').val();
                  // POST currentVenue
                  venue = new Venue.Model();
                  venue.entityToModel(currentVenue);
                  that.postVenue(venue, $submit);
               }
               break;
            case 'person':
               $submit = $('#submit-person');
               that.removeView('.alert-person');
               if (!currentPerson && !this.$('#person-text').val()) {
                  that.setView('.alert-venue', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.noPersonSelected'),
                     showClose: true
                  })).render();
                  return;
               } else if (!currentPerson && this.$('#person-text').val()) {
                  $submit.button('loading');
                  person = new Person.Model();
                  person.set('name', this.$('#person-text').val());
                  that.postPerson(person, $submit);
               } else {
                  $submit.button('loading');
                  person = new Person.Model();
                  person.entityToModel(currentPerson);
                  that.postPerson(person, $submit);
               }
               break;
         }
      },

      submitNewProduct: function() {
         var that = this;

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
                     if (venue.has('id')) {
                        that.postProduct($submit, newProduct, venue);
                     } else {
                        venue.url = Routing.generate('api_v1_post_venue');
                        venue.getToken('venue_item', function() {
                           venue.save(null, {
                              success: function() {
                                 that.postProduct($submit, newProduct, venue);
                              },
                              error: function() {
                                 $submit.button('reset');
                                 that.setView('.alert-product', new Common.Views.Alert({
                                    cssClass: Common.alertError,
                                    message: $.t('tag.errorVenuePost'),
                                    showClose: true
                                 })).render();
                              }
                           });
                        });
                     }
                  } else {
                     that.postProduct($submit, newProduct);
                  }
               },
               error: function() {
                  $submit.button('reset');
                  that.setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.errorProductPost'),
                     showClose: true
                  })).render();
               }
            });
         });
      },

      postProduct: function($submit, newProduct, venue) {
         var that = this;

         // Link tag to photo
         currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
         // Set tag info
         currentTag.set('type', 'product');

         if (typeof venue !== 'undefined') {
            currentTag.set('venue', venue.get('id'));
         }

         // Check if it's a product or a productType
         if (currentProductType) {
            currentTag.set('productType', currentProductType.id);
            currentTag.set('title', currentProductType.name);
            currentTag.set('description', that.$('#product-description').val());
            currentTag.set('link', that.$('#product-purchase-url').val());
         } else {
            currentTag.set('product', typeof newProduct !== 'undefined' ? newProduct.get('id') : currentProduct.id);
            currentTag.set('title', typeof newProduct !== 'undefined' ? newProduct.get('name') : currentProduct.name);
            currentTag.set('description', typeof newProduct !== 'undefined' ? newProduct.get('description') : currentProduct.description);
            currentTag.set('link', typeof newProduct !== 'undefined' ? newProduct.get('purchase_url') : currentProduct.purchase_url);
         }

         if (currentBrand)
            currentTag.set('brand', currentBrand.id);
         currentTag.url = Routing.generate('api_v1_post_tag');
         currentTag.getToken('tag_item', function() {
            currentTag.save(null, {
               success: function() {
                  currentTag.set('persisted', '');
                  currentTag.setup();
                  if (currentBrand)
                     app.fb.createBrandTagStory(currentBrand, app.appState().getCurrentPhotoModel());
                  currentBrand = null;
                  currentProduct = null;
                  currentVenue = null;
                  app.trigger('tagMenuTools:tagAdded', app.appState().getCurrentPhotoModel());
               },
               error: function(e, r) {
                  delete currentTag.id;
                  $submit.button('reset');
                  if (r.status === 403) {
                     that.setView('.alert-product', new Common.Views.Alert({
                        cssClass: Common.alertError,
                        message: $.t('tag.forbiddenTagPost'),
                        showClose: true
                     })).render();
                  } else {
                     var json = $.parseJSON(r.responseText);
                     if (json && typeof json.errors !== 'undefined') {
                        that.setView('.alert-product', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: Common.Tools.getHtmlErrors(json.errors),
                           showClose: true
                        })).render();
                     } else {
                        that.setView('.alert-product', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('tag.errorTagPost'),
                           showClose: true
                        })).render();
                     }
                  }
               }
            });
         });
      },

      postVenue: function(venue, $submit) {
         var that = this;
         if (venue.has('id')) {
            that.postVenueTag(venue, $submit);
         } else {
            venue.url = Routing.generate('api_v1_post_venue');
            venue.getToken('venue_item', function() {
               venue.save(null, {
                  success: function() {
                     that.postVenueTag(venue, $submit);
                  },
                  error: function(e, r) {
                     $submit.button('reset');
                     if (r.status === 403) {
                        that.setView('.alert-venue', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('tag.forbiddenTagPost'),
                           showClose: true
                        })).render();
                     } else {
                        that.setView('.alert-venue', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('tag.errorVenuePost'),
                           showClose: true
                        })).render();
                     }
                  }
               });
            });
         }
      },

      postVenueTag: function(venue, $submit) {
         var that = this;
         // Link tag to photo
         currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
         // Set tag info
         currentTag.set('type', 'place');
         currentTag.set('venue', venue.get('id'));
         currentTag.set('title', venue.get('name'));
         currentTag.set('description', venue.get('description'));
         currentTag.set('link', venue.get('link'));
         currentTag.url = Routing.generate('api_v1_post_tag');
         currentTag.getToken('tag_item', function() {
            currentTag.save(null, {
               success: function() {
                  currentTag.set('persisted', '');
                  currentTag.setup();
                  app.fb.createVenueStory(venue, app.appState().getCurrentPhotoModel());
                  currentVenue = null;
                  app.trigger('tagMenuTools:tagAdded', app.appState().getCurrentPhotoModel());
               },
               error: function() {
                  $submit.button('reset');
                  that.setView('.alert-venue', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.errorTagPost'),
                     showClose: true
                  })).render();
               }
            });
         });
      },

      postPerson: function(person, $submit) {
         var that = this;
         if (person.has('id')) {
            that.postPersonTag(person, $submit);
         } else {
            person.url = Routing.generate('api_v1_post_person');
            person.getToken('person_item', function() {
               person.save(null, {
                  success: function() {
                     that.postPersonTag(person, $submit);
                  },
                  error: function() {
                     $submit.button('reset');
                     that.setView('.alert-person', new Common.Views.Alert({
                        cssClass: Common.alertError,
                        message: $.t('tag.errorPerson'),
                        showClose: true
                     })).render();
                  }
               });
            });
         }
      },

      postPersonTag: function(person, $submit) {
         var that = this;
         // Link tag to photo
         currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
         // Set tag info
         currentTag.set('type', 'person');
         currentTag.set('person', person.get('id'));
         currentTag.set('title', person.getFullname());
         if (that.$('#person-link').val())
            currentTag.set('link', that.$('#person-link').val());
         currentTag.url = Routing.generate('api_v1_post_tag');
         currentTag.getToken('tag_item', function() {
            currentTag.save(null, {
               success: function() {
                  currentTag.set('persisted', '');
                  currentTag.setup();
                  app.fb.createPersonStory(person, app.appState().getCurrentPhotoModel());
                  currentPerson = null;
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
                     that.setView('.alert-person', new Common.Views.Alert({
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

      createProduct: function(e) {
         newProduct = new Product.Model();
      },

      moreDetailsPlace: function(e) {
         var i = this.$('.more-details-button i');
         var span = this.$('.more-details-button span');
         if (i.hasClass('glyphicon-plus-sign')) {
            i.removeClass('glyphicon-plus-sign').addClass('glyphicon-minus-sign');
            span.html($.t('tag.placeLessDetails'));
         } else {
            i.removeClass('glyphicon-minus-sign').addClass('glyphicon-plus-sign');
            span.html($.t('tag.placeMoreDetails'));
         }
         e.preventDefault();
      },

      checkBrand: function() {
         if (currentProduct && currentBrand && typeof currentProduct.brand !== 'undefined') {
            if (currentProduct.brand.id != currentBrand.id) {
               currentProduct = null;
               this.$('#product-name').val('');
               this.setView('.alert-product', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('tag.errorDifferentBrand'),
                  showClose: true
               })).render();
            }
         }
      },

      events: {
         "click .cancel-add-tag": "cancel",
         "click .btn-geolocation": "geolocation",
         "click .submitTagButton": "submit",
         'click .more-details-button': 'moreDetailsPlace'
      }
   });

   Tag.Views.Report = Backbone.View.extend({
      'template': 'tag/report',

      report: function(evt) {
         evt.preventDefault();
         this.trigger('report:submit', this.$('.reason-textarea').val());
      },

      events: {
         'click .reportSubmit': 'report'
      }
   });

   Tag.Common = {
      tagRadius: 17,

      addTag: function(evt, photo, photoCategories, photoHashtags) {
         if (evt)
            evt.preventDefault();

         if (typeof photoCategories === 'undefined') {
            var Category = require('modules/category');
            photoCategories = new Category.Collection();
            photoCategories.fetch({
               url: Routing.generate('api_v1_get_photo_categories', { id: photo.get('id'), locale: currentLocale })
            });
         }
         if (typeof photoHashtags === 'undefined') {
            var Hashtag = require('modules/hashtag');
            photoHashtags = new Hashtag.Collection();
            photoHashtags.fetch({
               url: Routing.generate('api_v1_get_photo_hashtags', { id: photo.get('id') })
            });
         }

         var tagView = new Tag.Views.AddModal({
            photo: photo,
            photoCategories: photoCategories,
            photoHashtags: photoHashtags
         });
         var modal = new Common.Views.Modal({
            view: tagView,
            showFooter: false,
            showHeader: false,
            modalContentClasses: 'photoModal'
         });
         app.listenTo(app, 'photoEditModal:close', function() {
            modal.close();
         });
         modal.on('hide', function() {
            app.trigger('addTagModal:hide');
            var currentModalView = app.useLayout().getView('#modal-container');
            if (currentModalView)
               currentModalView.$('.modal-dialog').removeClass('slideOutLeft').addClass('animated slideInLeft');
         });
         // Check if there is current modal
         var currentModalView = app.useLayout().getView('#modal-container');
         if (currentModalView) {
            app.useLayout().getView('#modal-container').$('.modal-dialog').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
               app.useLayout().setView('#front-modal-container', modal).render();
            }).addClass('animated slideOutLeft');
         } else {
            app.useLayout().setView('#front-modal-container', modal).render();
         }
      },

      reportTag: function(tag) {
         var reportView = new Tag.Views.Report();
         var modal = new Common.Views.Modal({
            view: reportView,
            showFooter: false,
            showHeader: true,
            title: 'tag.reportTitle',
            modalDialogClasses: 'report-dialog'
         });
         reportView.on('report:submit', function(reason) {
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     headers : {
                        "Authorization": app.oauth.getAuthorizationHeader()
                     },
                     url: Routing.generate('api_v1_get_csrftoken', { intention: 'report_item'}),
                     success: function(data) {
                        $.ajax({
                           url: Routing.generate('api_v1_post_report'),
                           headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                           type: 'POST',
                           data: {
                              report: {
                                 'reason': reason,
                                 'tag': tag.get('id'),
                                 '_token': data.csrf_token
                              }
                           }
                        });
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

      setupGoogleMap: function(model) {
         if (model.get('venue').has('lat') && model.get('venue').has('lng')) {
            var latLng = new google.maps.LatLng(model.get('venue').get('lat'), model.get('venue').get('lng'));
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
            var gMap = new google.maps.Map(document.getElementById('map' + model.get('id')), mapOptions);
            new google.maps.Marker({
               position: latLng,
               map: gMap
            });
            $('#map' + model.get('id')).addClass('loaded map');
         }
      }
   };

   return Tag;
});