(function() {
   var jQuery;
   if (window.jQuery === undefined || window.jQuery.fn.jquery !== '1.4.2') {
      var script_tag = document.createElement('script');
      script_tag.setAttribute("type","text/javascript");
      script_tag.setAttribute("src",
         "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js");
      if (script_tag.readyState) {
         script_tag.onreadystatechange = function () { // For old versions of IE
            if (this.readyState == 'complete' || this.readyState == 'loaded') {
               scriptLoadHandler();
            }
         };
      } else { // Other browsers
         script_tag.onload = scriptLoadHandler;
      }
      (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
   } else {
      jQuery = window.jQuery;
      AdEntify.init();
   }
   function scriptLoadHandler() {
      jQuery = window.jQuery.noConflict(true);
      AdEntify.init();
   }

   AdEntify = {
      hoverTimeout: null,

      init: function() {
         if (jQuery('meta[property="adentitfy-loaded"]').length == 0) {
            jQuery('head').append('<style type="text/css">' +
               '.adentify-pastille {opacity: 0;background: url("./img/pastille.png") no-repeat;-webkit-transition: opacity 0.3s ease-out;-moz-transition: opacity 0.3s ease-out;-ms-transition: opacity 0.3s ease-out;-o-transition: opacity 0.3s ease-out;transition: opacity 0.3s ease-out;width: 25px;height: 25px;position: absolute;top: 10px;right: 10px;z-index: 2;cursor: pointer;}' +
               '.adentify-photo-container:hover .adentify-pastille { opacity: 1; }' +
               '.tags { display: none;  }' +
               '.tags li {margin: 0;padding: 0;}' +
               '.tag {position: absolute;background: rgba(0,0,0,0.9);width: 25px;height: 25px;border-radius: 12.5px;}' +
               '.popover {position: absolute;top: 30px;left: -50%;z-index: 2000;padding: 4px 6px;-webkit-transition: opacity 0.3s ease-out;-moz-transition: opacity 0.3s ease-out;-ms-transition: opacity 0.3s ease-out;-o-transition: opacity 0.3s ease-out;transition: opacity 0.3s ease-out;}' +
               '.popover-product {min-width: 250px;}' +
               '.popover-product .product-image {max-width: 250px;}' +
               '.popover-product .brand-logo {max-height: 50px;}' +
               '.tag .map {width: 270px;height: 260px;}' +
               '.popover{position:absolute;top:0;left:0;z-index:1010;display:none;max-width:276px;padding:1px;text-align:left;background-color:#ffffff;-webkit-background-clip:padding-box;-moz-background-clip:padding;background-clip:padding-box;border:1px solid #ccc;border:1px solid rgba(0, 0, 0, 0.2);-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);-moz-box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);white-space:normal;}.popover.top{margin-top:-10px;}' +
               '.popover.right{margin-left:10px;}' +
               '.popover.bottom{margin-top:10px;}' +
               '.popover.left{margin-left:-10px;}' +
               '.popover-title{margin:0;padding:8px 14px;font-size:14px;font-weight:normal;line-height:18px;background-color:#f7f7f7;border-bottom:1px solid #ebebeb;-webkit-border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;border-radius:5px 5px 0 0;}.popover-title:empty{display:none;}' +
               '.popover-content{padding:9px 14px;}' +
               '.popover .arrow,.popover .arrow:after{position:absolute;display:block;width:0;height:0;border-color:transparent;border-style:solid;}' +
               '.popover .arrow{border-width:11px;}' +
               '.popover .arrow:after{border-width:10px;content:"";}' +
               '.popover.top .arrow{left:50%;margin-left:-11px;border-bottom-width:0;border-top-color:#999;border-top-color:rgba(0, 0, 0, 0.25);bottom:-11px;}.popover.top .arrow:after{bottom:1px;margin-left:-10px;border-bottom-width:0;border-top-color:#ffffff;}' +
               '.popover.right .arrow{top:50%;left:-11px;margin-top:-11px;border-left-width:0;border-right-color:#999;border-right-color:rgba(0, 0, 0, 0.25);}.popover.right .arrow:after{left:1px;bottom:-10px;border-left-width:0;border-right-color:#ffffff;}' +
               '.popover.bottom .arrow{left:50%;margin-left:-11px;border-top-width:0;border-bottom-color:#999;border-bottom-color:rgba(0, 0, 0, 0.25);top:-11px;}.popover.bottom .arrow:after{top:1px;margin-left:-10px;border-top-width:0;border-bottom-color:#ffffff;}' +
               '.popover.left .arrow{top:50%;right:-11px;margin-top:-11px;border-right-width:0;border-left-color:#999;border-left-color:rgba(0, 0, 0, 0.25);}.popover.left .arrow:after{right:1px;border-right-width:0;border-left-color:#ffffff;bottom:-10px;}' +
               '</style>');
            jQuery('head').append('<meta property="adentify-loaded" content="true">');
         }
         jQuery(this.getValue('selector')).wrap('<div class="adentify-photo-container" style="position: relative;display: inline-block;" />');
         jQuery('<div class="adentify-photo-overlay" style="position: absolute;left: 0px;top: 0px;width: 100%;height: 100%;" />').insertBefore(this.getValue('selector'));
         $pastille = jQuery('<div class="adentify-pastille" />').insertBefore(this.getValue('selector'));
         $tags = jQuery('<ul class="tags" data-state="hidden" style="list-style-type: none;margin: 0;padding: 0;" />').insertBefore(this.getValue('selector'));
         $pastille.on('click', function() {
            if ($tags.length > 0) {
               if ($tags.data('state') == 'hidden') {
                  $tags.fadeIn('fast');
                  $tags.data('state', 'visible');
               } else {
                  $tags.fadeOut('fast');
                  $tags.data('state', 'hidden');
               }
            }
         });
         jQuery.ajax({
            url: './public-api/v1/tags/' + jQuery(this.getValue('selector')).data('adentify-photo-id'),
            success: function(tags) {
               if (typeof tags !== 'undefined' && tags.length > 0) {
                  var i = 0;
                  for (i; i <tags.length; i++) {
                     var tag = tags[i];
                     if (tag.type == 'place') {
                        jQuery($tags).append('<div class="tag" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="popover"><span class="title">'+ (tag.link ? '<a href="'+ tag.link +'" target="_blank">'+ tag.title +'</a>' : tag.title) +'</span>'
                           + (tag.description ? '<p>' + tag.description + '</p>' : '') +
                           '<div id="map' + tag.id + '" class="map"></div></div></div>');
                     } else if (tag.type == 'person') {
                        jQuery($tags).append('<div class="tag" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="popover"><div class="text-center"><img src="https://graph.facebook.com/' + tag.person.facebook_id + '/picture?type=square" /></div><span class="title"><a href="' + tag.link + '" target="_blank">'+ tag.title +'</a></span>' +
                           (tag.description ? '<p>' + tag.description + '</p>' : '') +
                           '</div></div>');
                     } else if (tag.type == 'product') {
                        jQuery($tags).append('<div class="tag" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="popover popover-product"><span class="title"><a href="'+ tag.link +'" target="_blank">' + tag.title + (tag.product.brand ? ' - ' + tag.product.brand.name : '') + '</a></span><img class="pull-left product-image" src="'+tag.product.small_url+'">' +
                           (tag.description ? '<p>' + tag.description + '</p>' : '') +
                           (tag.product && tag.product.brand ? '<div class="brand pull-right"><img src="' + tag.product.brand.small_logo_url + '" alt="' + tag.product.brand.name + '" class="brand-logo" /></div>' : '') +
                           '<a href="' + tag.product.purchase_url + '" class="btn btn-small btn-primary"><i class="icon-shopping-cart icon-white"></i> Acheter</a></div></div>');
                     } else {
                        jQuery($tags).append('');
                     }
                  }
               }
            }
         });

         jQuery('.tags').on('mouseenter', '.tag', function() {
            jQuery(this).find('.popover').show();
            jQuery.ajax({
               url: './public-api/v1/tag/stat',
               type: 'POST',
               data: {
                  tagId: jQuery(this).data('tag-id'),
                  statType: 'hover'
               }
            });
         });
         jQuery('.tags').on('mouseleave', '.tag', function() {
            jQuery(this).find('.popover').hide();
         });
         jQuery('.tags').on('click', 'a[href]', function() {
            jQuery.ajax({
               url: './public-api/v1/tag/stat',
               type: 'POST',
               data: {
                  tagId: jQuery(this).parents('.tag').data('tag-id'),
                  statType: 'click'
               }
            });
         });
      },

      getValue: function(key) {
         var i = 0;
         for (i; i<_adentify.length;i++) {
            if (_adentify[i][0] == key)
               return _adentify[i][1];
         }
         return false;
      }
   };

})(); // We call our anonymous function immediately