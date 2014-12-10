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
      rootUrl: "//local.adentify.com/",
      showTags: false,
      showLikes: false,

      init: function() {
         var that = this;

         // Load options
         AdEntify.showTags = typeof jQuery(this.getValue('selector')).attr('data-adentify-tags') !== 'undefined' ? true : false;
         AdEntify.showLikes = typeof jQuery(this.getValue('selector')).attr('data-adentify-likes') !== 'undefined' ? true : false;
         AdEntify.cover = typeof jQuery(this.getValue('selector')).attr('data-cover') !== 'undefined' ? false : true;

         //if (AdEntify.cover) {
         //   jQuery(this.getValue('selector')).height(document.documentElement.clientHeight);
         //}

         // Load CSS
         if (jQuery('meta[property="adentitfy-loaded"]').length == 0) {
            $head = jQuery('head');
            $head.append('<style type="text/css">' +
               '@font-face {font-family: "asapregular";src: url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.eot");src: url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.eot?#iefix") format("embedded-opentype"),url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.woff") format("woff"),url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.ttf") format("truetype"),url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.svg#asapregular") format("svg");font-weight: normal;font-style: normal;}@font-face {font-family: "robotobold";src: url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.eot");src: url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.eot?#iefix") format("embedded-opentype"),url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.woff") format("woff"),url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.ttf") format("truetype"),url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.svg#robotobold") format("svg");font-weight: normal;font-style: normal;}@font-face {font-family: "asapbold";src: url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.eot");src: url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.eot?#iefix") format("embedded-opentype"),url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.woff") format("woff"),url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.ttf") format("truetype"),url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.svg#asapbold") format("svg");font-weight: normal;font-style: normal;}' +
               '.adentify-pastille {opacity: 1;background: url("'+ AdEntify.rootUrl +'img/adentify-pastille.png") no-repeat;-webkit-transition: opacity 0.3s ease-out;-moz-transition: opacity 0.3s ease-out;-ms-transition: opacity 0.3s ease-out;-o-transition: opacity 0.3s ease-out;transition: opacity 0.3s ease-out;width: 52px;height: 52px;position: absolute;top: 10px;right: 10px;z-index: 2;cursor: pointer;}' +
               '.adentify-photo-container:hover .adentify-pastille { opacity: 1; }' +
               (AdEntify.showTags === true ? '.tags {display: block;}' : '.tags {display: none;}') +
               '.tags li {margin: 0;padding: 0;}' +
               '.tag {position: absolute;background-image: url("'+ AdEntify.rootUrl +'/img/sprites.png");background-color: transparent;background-repeat: no-repeat;background-position: -102px -109px;width: 35px;height: 36px;}' +
               '.popover {position: absolute;display: none;padding: 4px 6px;-webkit-transition: opacity 0.3s ease-out;-moz-transition: opacity 0.3s ease-out;-ms-transition: opacity 0.3s ease-out;-o-transition: opacity 0.3s ease-out;transition: opacity 0.3s ease-out;}' +
               '.popover-product {min-width: 250px;}' +
               '.popover-product .product-image {max-width: 250px;}' +
               '.popover-product .brand-logo {max-height: 50px;}' +
               '.tag .map {width: 270px;height: 260px;}' +
               '.popover {position:absolute;top:0;left:0;z-index:1010;max-width:276px;padding:1px;text-align:left;background-color:#ffffff;-webkit-background-clip:padding-box;-moz-background-clip:padding;background-clip:padding-box;border:1px solid #ccc;border:1px solid rgba(0, 0, 0, 0.2);-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);-moz-box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);white-space:normal;}.popover.top{margin-top:-10px;}' +
               '.popover.right {margin-left:10px;}' +
               '.popover.bottom {margin-top:10px;}' +
               '.popover.left {margin-left:-10px;}' +
               '.popover-title {margin:0;padding:8px 14px;font-size:14px;font-weight:normal;line-height:18px;background-color:#f7f7f7;border-bottom:1px solid #ebebeb;-webkit-border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;border-radius:5px 5px 0 0;}.popover-title:empty{display:none;}' +
               '.popover-content {padding:9px 14px;}' +
               '.popover .arrow,.popover .arrow:after {position:absolute;display:block;width:0;height:0;border-color:transparent;border-style:solid;}' +
               '.popover .arrow {border-width:11px;}' +
               '.popover .arrow:after {border-width:10px;content:"";}' +
               '.popover.top .arrow {left:50%;margin-left:-11px;border-bottom-width:0;border-top-color:#999;border-top-color:rgba(0, 0, 0, 0.25);bottom:-11px;}.popover.top .arrow:after{bottom:1px;margin-left:-10px;border-bottom-width:0;border-top-color:#ffffff;}' +
               '.popover.right .arrow {top:50%;left:-11px;margin-top:-11px;border-left-width:0;border-right-color:#999;border-right-color:rgba(0, 0, 0, 0.25);}.popover.right .arrow:after{left:1px;bottom:-10px;border-left-width:0;border-right-color:#ffffff;}' +
               '.popover.bottom .arrow {left:50%;margin-left:-11px;border-top-width:0;border-bottom-color:#999;border-bottom-color:rgba(0, 0, 0, 0.25);top:-11px;}.popover.bottom .arrow:after{top:1px;margin-left:-10px;border-top-width:0;border-bottom-color:#ffffff;}' +
               '.popover.left .arrow {top:50%;right:-11px;margin-top:-11px;border-right-width:0;border-left-color:#999;border-left-color:rgba(0, 0, 0, 0.25);}.popover.left .arrow:after{right:1px;border-right-width:0;border-left-color:#ffffff;bottom:-10px;}' +
               '[class^="icon-"],[class*=" icon-"]{display:inline-block;width:14px;height:14px;*margin-right:.3em;line-height:14px;vertical-align:text-top;background-image:url("'+ AdEntify.rootUrl + 'img/glyphicons-halflings.png");background-position:14px 14px;background-repeat:no-repeat;margin-top:1px;}' +
               '.icon-white,.nav-pills>.active>a>[class^="icon-"],.nav-pills>.active>a>[class*=" icon-"],.nav-list>.active>a>[class^="icon-"],.nav-list>.active>a>[class*=" icon-"],.navbar-inverse .nav>.active>a>[class^="icon-"],.navbar-inverse .nav>.active>a>[class*=" icon-"],.dropdown-menu>li>a:hover>[class^="icon-"],.dropdown-menu>li>a:focus>[class^="icon-"],.dropdown-menu>li>a:hover>[class*=" icon-"],.dropdown-menu>li>a:focus>[class*=" icon-"],.dropdown-menu>.active>a>[class^="icon-"],.dropdown-menu>.active>a>[class*=" icon-"],.dropdown-submenu:hover>a>[class^="icon-"],.dropdown-submenu:focus>a>[class^="icon-"],.dropdown-submenu:hover>a>[class*=" icon-"],.dropdown-submenu:focus>a>[class*=" icon-"]{background-image:url("'+ AdEntify.rootUrl + 'img/glyphicons-halflings-white.png");}' +
               '.icon-heart {background-position: -96px 0;}' +
               '.adentify-photo-likes {position: absolute; bottom: 5px; right: 5px; color: white;text-shadow: 0 0 3px rgba(0,0,0,0.8); text-rendering: optimizelegibility;}' +
               '.list-unstyled { padding-left:0;list-style:none; }' +
               '.popover-pastille-buttons {margin: 0;padding: 0;}' +
               '.popover-pastille-buttons li {border-bottom: 1px solid #dfe2e6;width: 67px;height: 54px;line-height: 64px;text-align: center;vertical-align: middle;}' +
               '.arrow-top-adentify-pastille-hover {position: absolute;top: -7px;left: 50%;margin-left: -6px;}' +
               '.adentify-pastille-wrapper {position: absolute;top: 0px;right: 0px;cursor: pointer;}' +
               '.adentify-pastille-wrapper .popover {top: 69px; right: 4px; left: auto; border-radius: 0px;border-left: 1px solid #dfe2e6;border-top: 1px solid #dfe2e6;border-right: 1px solid #dfe2e6;padding: 0px;}' +
               '.arrow-top-adentify-pastille-hover{background-position: -106px -174px ;width: 13px;height: 8px;}' +
               '.add-tag-icon{background-position: -83px -174px ;width: 19px;height: 19px;}' +
               '.favorite-icon{background-position: -102px -150px ;width: 21px;height: 20px;}' +
               '.like-icon{background-position: -56px -185px ;width: 21px;height: 17px;}' +
               '.share-icon{background-position: -55px -159px ;width: 26px;height: 23px;}' +
               '.btn-icon {border: 0px;padding: 0px;margin: 0px; cursor: pointer;}' +
               '.share-overlay {position: absolute;left: 0px;top: 0px;width: 100%;height: 100%;background: rgba(0,0,0,0.8);}' +
               '.close-share {position: absolute;top: 10px;left: 10px;color: white;font-size: 20px;cursor: pointer;}' +
               '.share-overlay-wrapper {display: table;width: 100%;height: 100%;}' +
               '.share-overlay-cell {display: table-cell;vertical-align: middle;}' +
               '.share-overlay-inner {max-width: 500px;max-height: 200px;margin: auto;}' +
               '.share-overlay-inner textarea {margin: 20px 0px 10px 0px; height: 80px;}' +
               '.form-control{display:block;width:100%;height:34px;padding:6px 12px;font-size:14px;line-height:1.428571429;color:#555555;vertical-align:middle;background-color:#ffffff;border:1px solid #cccccc;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.075);box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.075);-webkit-transition:border-color ease-in-out .15s, box-shadow ease-in-out .15s;transition:border-color ease-in-out .15s, box-shadow ease-in-out .15s;}.form-control:focus{border-color:#66afe9;outline:0;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);box-shadow:inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);}' +
               '.fblike, .pinterest {display: inline-block;} .pinterest { height: 20px; vertical-align: top;margin-left: 40px; }' +
               '.fadeOut {display: none;}' +
               '.tag .popover {position: absolute;top: 30px;left: -50%;z-index: 2000;padding: 0px;border: 0px;border-radius: 0px;font-family: "asapregular", "Helvetica Neue", "Arial", sans-serif;max-width: none;min-width: 150px;-webkit-transition: opacity 0.3s ease-out;-moz-transition: opacity 0.3s ease-out;-ms-transition: opacity 0.3s ease-out;-o-transition: opacity 0.3s ease-out;transition: opacity 0.3s ease-out;}' +
               '.tag .tag-icon {position: absolute;}.tag .tag-brand-icon{top: 9px;left: 11px;}.tag .tag-place-icon{left: 12px;top: 10px;}.tag .tag-text-icon{left: 12px;top: 10px;}.tag .tag-user-icon{left: 11px;top: 10px;}.tag .tag-video-icon{left: 10px;top: 11px;}' +
               '.tag-brand-icon{background-position: -181px -109px ;width: 12px;height: 17px;}.tag-place-icon{background-position: -163px -127px ;width: 11px;height: 14px;}.tag-text-icon{background-position: -162px -108px ;width: 11px;height: 15px;}.tag-user-icon{background-position: -142px -126px ;width: 13px;height: 13px;}.tag-video-icon{background-position: -141px -109px ;width: 16px;height: 13px;}' +
               '.tag .tag-popover-arrow {position: absolute;}' +
               '.tag-popover-arrow-bottom{background-position: -196px -131px ;width: 10px;height: 10px;}.tag-popover-arrow-left{background-position: -191px -93px ;width: 10px;height: 10px;}.tag-popover-arrow-right{background-position: -179px -131px ;width: 10px;height: 10px;}.tag-popover-arrow-top{background-position: -207px -93px ;width: 10px;height: 10px;}' +
               '.tag .popover-inner {padding: 6px;}.tag .popover .title {font-family: "asapbold", "Helvetica Neue", "Arial", sans-serif;font-size: 14px;color: #181616;}.tag .popover address {margin-bottom: 0px;color: #5b5756;font-size: 12px;}.tag .popover a {color: #000000;font-family: "asapbold", "Helvetica Neue", "Arial", sans-serif;}.popover .popover-details {background: #f5f5f7;padding: 10px 25px;}.popover-details strong {font-family: "asapbold", "Helvetica Neue", "Arial", sans-serif;font-weight: normal;}.popover-details a.buy-link {font-size: 16px;color: #4e4e50;}.popover-product {min-width: 250px;}.popover-product .brand-logo {max-height: 50px;}.tag .map {width: 270px;height: 260px;}.brand-logo,.product-image {max-height: 100px;}.tag .tag-top-buttons {float: right;margin-bottom: 8px;}.tag-text {font-family: "robotoregular", "Helvetica Neue", "Arial", sans-serif;font-size: 16px;padding: 10px;}.product-photo {max-width: 100%;}.tag-buttons {background: url("'+ AdEntify.rootUrl +'img/dark-grey-tag-background.jpg") repeat;padding: 6px 10px;color: #b7babe;font-family: "asapregular", "Helvetica Neue", "Arial", sans-serif;font-size: 11px;margin: 2px 0px 0px;}.tag-buttons strong {font-weight: normal;font-family: "asapbold", "Helvetica Neue", "Arial", sans-serif;}.tag-buttons .tagged-by {margin-right: 4px;}.tagged-by a,.tagged-by a:hover {text-decoration: none;color: #b7babe!important;}' +
               // sprites
               '.arrow-top-adentify-pastille-hover, .add-tag-icon, .like-icon, .share-icon, .favorite-icon, .tag-place-icon, .tag-user-icon, .tag-brand-icon, .tag-popover-arrow-bottom, .tag-popover-arrow-left, .tag-popover-arrow-right, .tag-popover-arrow-top { background-image: url("'+ AdEntify.rootUrl +'/img/sprites.png");background-color: transparent;background-repeat: no-repeat; }' +
               '</style>');
            $head.append('<meta property="adentify-loaded" content="true">');
         }

         jQuery(this.getValue('selector')).wrap('<div class="adentify-photo-container" style="width: 100%; position: relative;display: inline-block;" />');
         jQuery('<div class="adentify-photo-overlay" style="position: absolute;left: 0px;top: 0px;width: 100%;height: 100%;" />').insertBefore(this.getValue('selector'));
         $tags = jQuery('<ul class="tags" data-state="hidden" data-always-visible="no" style="list-style-type: none;margin: 0;padding: 0;" />').insertBefore(this.getValue('selector'));
         $pastilleWrapper = jQuery('<div class="adentify-pastille-wrapper" />').insertBefore(this.getValue('selector'));
         $pastille = jQuery($pastilleWrapper).append('<div class="adentify-pastille" />');
         $pastillePopover = jQuery($pastilleWrapper).append('<div class="popover">\
            <div class="arrow-top-adentify-pastille-hover"></div>\
            <ul class="popover-pastille-buttons list-unstyled">\
               <li><button class="btn-icon add-tag-icon add-tag-button"></button></li>\
               <li><button class="btn-icon like-icon like-button"></button></li>\
               <li><button class="btn-icon share-icon share-button"></button></li>\
               <li><button class="btn-icon favorite-icon favorite-button"></button></li>\
            </ul>\
         </div>');
         $pastillePopover.find('.like-icon').click(function() {
            window.open(that.photoUrl());
            return false;
         });
         $pastillePopover.find('.add-tag-icon').click(function() {
            window.open(that.photoUrl());
            return false;
         });
         $pastillePopover.find('.favorite-icon').click(function() {
            window.open(that.photoUrl());
            return false;
         });
         $pastillePopover.find('.share-icon').click(function() {
            if (jQuery(that.getValue('selector')).siblings('.share-overlay:visible').length > 0) {
               jQuery(that.getValue('selector')).siblings('.share-overlay').fadeOut();
            } else {
               jQuery(that.getValue('selector')).siblings('.share-overlay').fadeIn();
            }
            return false;
         });
         $pastille.on('click', function() {
            if ($tags.data('always-visible') == 'no') {
               $tags.data('always-visible', 'yes');
            } else {
               $tags.data('always-visible', 'no');
               $tags.stop().fadeOut('fast');
               $tags.data('state', 'hidden');
            }
         });
         $pastille.on('mouseenter', function() {
            $pastillePopover.find('.popover').fadeIn('fast');
            $tags.fadeIn('fast');
            $tags.data('state', 'visible');
         });
         $pastille.on('mouseleave', function() {
            $pastillePopover.find('.popover').fadeOut('fast');
            if ($tags.data('always-visible') == 'no') {
               $tags.fadeOut('fast');
               $tags.data('state', 'hidden');
            }
         });
         var that = this;
         jQuery.ajax({
            url: AdEntify.rootUrl + 'public-api/v1/photos/' + jQuery(this.getValue('selector')).data('adentify-photo-id'),
            dataType: 'jsonp',
            success: function(photo) {
               if (typeof photo !== 'undefined' && typeof photo.tags !== 'undefined') {
                  var photoContainer = jQuery(that.getValue('selector')).parent('.adentify-photo-container');
                  if (photoContainer.length > 0) {
                     photoContainer.css({maxHeight: photo.large_height, maxWidth: photo.large_width});
                  }
                  jQuery('<div class="share-overlay fadeOut"><div class="share-overlay-wrapper"><div class="share-overlay-cell">\
                              <div class="share-overlay-inner">\
                              <iframe allowtransparency="true" frameborder="0" scrolling="no"\
                              src="https://platform.twitter.com/widgets/tweet_button.html?text=' + that.photoUrl() + '&via=AdEntify&lang=' + that.currentLang() + '" style="width:130px; height:20px;"></iframe>\
                              <div class="g-plusone" data-size="medium" data-href="' + that.photoUrl() + '"></div>\
                              <script type="text/javascript">\
                              window.___gcfg = {lang: "' + that.currentLang() + '"};\
                              (function() {\
                                 var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;\
                                 po.src = "https://apis.google.com/js/plusone.js";\
                                 var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);\
                                 })();\
                              </script>\
                              <div class="fblike"><div class="fb-like" data-href="' + that.photoUrl() + '" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false" data-font="arial"></div></div>\
                              <div class="pinterest"><a target="_blank" href="//pinterest.com/pin/create/button/?url=' + encodeURIComponent(that.photoUrl()) + '&media=' + encodeURIComponent(photo.large_url) + '&description=' + encodeURIComponent(photo.caption) + '" data-pin-do="buttonPin" data-pin-config="beside"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a></div>\
                              <textarea class="embedCode form-control input-block-level selectOnFocus" rows="3">&lt;iframe src="' + AdEntify.rootUrl + 'iframe/photo-' + jQuery(that.getValue('selector')).data('adentify-photo-id') +'.html" scrolling="no" frameborder="0" style="border:none; overflow:hidden;width:' + photo.large_width + 'px; height:' + photo.large_height + 'px;" allowTransparency="true"&gt;&lt;/iframe&gt;</textarea>\
                      </div>\
                  </div></div></div>').insertBefore(that.getValue('selector'));
                  FB.XFBML.parse();
                  if (AdEntify.showLikes === true) {
                     jQuery('<div class="adentify-photo-likes">' + photo.likes_count + ' <i class="icon-heart icon-white"></i></div>').insertBefore(that.getValue('selector'));
                  }
                  var tags = photo.tags;
                  if (typeof tags !== 'undefined' && tags.length > 0) {
                     var i = 0;
                     for (i; i <tags.length; i++) {
                        var tag = tags[i];
                        var $tag = null;
                        if (tag.type == 'place') {
                           $tag = jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="tag-place-icon tag-icon"></div><div class="popover"><div class="tag-popover-arrow"></div><div class="popover-inner"><span class="title">'+ (tag.link ? '<a href="'+ tag.link +'" target="_blank">'+ tag.title +'</a>' : tag.title) +'</span>'
                              + (tag.description ? '<p>' + tag.description + '</p>' : '') +
                              '</div><div id="map' + tag.id + '" class="map" data-lng="' + tag.venue.lng + '" data-lat="' + tag.venue.lat + '"></div>\
                              <div class="popover-details">\
                                 <address>\
                                 <strong>' + tag.title + '</strong><br>\
                                   ' + (tag.venue.address ? tag.venue.address + '<br>' : '') +
                              (tag.venue.postal_code ? tag.venue.postal_code + ' ' : '') + (tag.venue.city ? tag.venue.city + ' ' : '') + (tag.venue.country ? tag.venue.country + ' ' : '') +
                               '</address></div></div></div>');
                        } else if (tag.type == 'person') {
                           $tag = jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%">\
                              <div class="tag-user-icon tag-icon"></div><div class="popover"><div class="tag-popover-arrow"></div>\
                              <div class="popover-inner"><div class="text-center"><img src="https://graph.facebook.com/' + tag.person.facebook_id + '/picture?type=square" /></div><span class="title"><a href="' + tag.link + '" target="_blank">'+ tag.title +'</a></span>' +
                              (tag.description ? '<p>' + tag.description + '</p>' : '') +
                              '</div></div></div>');
                        } else if (tag.type == 'product') {
                           $tag = jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="tag-brand-icon tag-icon"></div><div class="popover popover-product"><div class="tag-popover-arrow"></div><div class="popover-inner"><span class="title"><a href="'+ tag.link +'" target="_blank">' + tag.title + (tag.brand ? ' - ' + tag.brand.name : '') + '</a></span>' + (tag.product && typeof tag.product.small_url !== 'undefined' ? '<img class="pull-left product-image" src="'+tag.product.small_url+'">' : '') +
                              (tag.description ? '<p>' + tag.description + '</p>' : '') +
                              '</div><div class="clearfix"></div>' +
                              (tag.brand ? typeof tag.brand.small_logo_url !== 'undefined' ? '<div class="brand pull-right"><img src="' + tag.brand.small_logo_url + '" alt="' + tag.brand.name + '" class="brand-logo" /></div>' : '' : '') +
                              (tag.product ? '<div class="popover-details"><a target="_blank" href="' + tag.product.purchase_url + '" class="btn btn-small btn-primary"><i class="icon-shopping-cart icon-white"></i> Acheter</a></div>' : '') +
                              '</div></div>');
                        } else {
                           jQuery($tags).append('');
                        }

                        if ($tag) {
                           var popoverArrow = $tag.find('.tag-popover-arrow');
                           // Arrow position
                           if (tag.y_position > 0.5) {
                              popoverArrow.addClass('tag-popover-arrow-bottom');
                              popoverArrow.css({bottom: '-10px'});
                           } else {
                              popoverArrow.css({top: '-10px'});
                              popoverArrow.addClass('tag-popover-arrow-top');
                           }
                           if (tag.x_position > 0.5) {
                              popoverArrow.css({right: '20px'});
                           } else {
                              popoverArrow.css({left: '20px'});
                           }
                        }

                        if (typeof popover !== 'undefined') {
                           popover.css({top: this.model.get('y_position') > 0.5 ? '-'+(popover.height() + 18)+'px' : '46px'});
                           popover.css({left: this.model.get('x_position') > 0.5 ? '-'+(popover.width() - 31)+'px' : '-8px'});
                           popover.fadeIn(100);
                        }
                     }
                  }
               }
            },
            error: function() {
            }
         });
         $tags = jQuery('.tags');
         $tags.on('mouseenter', '.tag', function() {
            var popover = jQuery(this).find('.popover');
            popover.css({top: jQuery(this).data('y') > 0.5 ? '-'+popover.height()+'px' : '30px', left: jQuery(this).data('x') > 0.5 ? '-'+popover.width()+'px' : '30px'});

            // Load map if found
            var map = jQuery(this).find('.map');
            if (map.length > 0 && map.data('loaded') != '1') {
               map.data('loaded', 1);
               var latLng = new google.maps.LatLng(map.data('lat'), map.data('lng'));
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
               var gMap = new google.maps.Map(document.getElementById('map'+jQuery(this).data('tag-id')), mapOptions);
               new google.maps.Marker({
                  position: latLng,
                  map: gMap
               });
            }

            popover.fadeIn();

            var xhr = AdEntify.createCORSRequest('POST', AdEntify.rootUrl + 'api/v1/tagstats');
            if (xhr) {
               xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
               xhr.send('tagId=' + jQuery(this).data('tag-id') + '&statType=hover&platform=adentify-embed');
            }
         });
         $tags.on('mouseleave', '.tag', function() {
            jQuery(this).find('.popover').fadeOut();
         });
         $tags.on('click', 'a[href]', function() {
            var xhr = AdEntify.createCORSRequest('POST', AdEntify.rootUrl + 'api/v1/tagstats');
            if (xhr) {
               xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
               xhr.send('tagId=' + jQuery(this).parents('.tag').data('tag-id') + '&statType=click&platform=adentify-embed&link=' + encodeURIComponent(jQuery(this).attr('href')));
            }
         });
      },

      getValue: function(key) {
         var i = 0;
         for (i; i<_adentify.length;i++) {
            if (_adentify[i][0] == key)
               return _adentify[i][1];
         }
         return false;
      },

      createCORSRequest: function(method, url) {
         var xhr = new XMLHttpRequest();
         if ("withCredentials" in xhr) {
            // XHR for Chrome/Firefox/Opera/Safari.
            xhr.open(method, url, true);
         } else if (typeof XDomainRequest != "undefined") {
            // XDomainRequest for IE.
            xhr = new XDomainRequest();
            xhr.open(method, url);
         } else {
            // CORS not supported.
            xhr = null;
         }
         xhr.setRequestHeader('X-Custom-Auth', 'value');
         return xhr;
      },

      photoUrl: function(https) {
         https = typeof https !== 'undefined' ? https : true;
         return (https ? 'https:' : '') + AdEntify.rootUrl + this.currentLang() + '/app/photo/' + jQuery(this.getValue('selector')).data('adentify-photo-id') + '/';
      },

      currentLang: function() {
         var language = window.navigator.userLanguage || window.navigator.language;
         if (language.indexOf('fr') !== -1) {
            return 'fr';
         }
         else {
            return 'en';
         }
      }
   };

})(); // We call our anonymous function immediately