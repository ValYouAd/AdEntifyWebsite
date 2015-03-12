this["JST"] = this["JST"] || {};

this["JST"]["app/templates/action/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model.has('brandModel') && model.get('brandModel').has('small_logo_url')) { 
;__p+='\n<div class="pull-left">\n    <a href="'+
( model.get('brandModel').get('link') )+
'"><img src="'+
( model.get('brandModel').get('small_logo_url') )+
'" class="brand-picture" alt="'+
( model.get('brandModel').get('name') )+
'" /></a>\n</div>\n';
 } else if (model.has('authorModel')) { 
;__p+='\n<div class="pull-left">\n    <a href="'+
( model.get('authorModel').get('link') )+
'"><img src="'+
( model.get('authorModel').get('profilePicture') )+
'" class="profile-picture" alt="'+
( model.get('authorModel').get('fullname') )+
'" /></a>\n</div>\n';
 } 
;__p+='\n';
 if (model.has('targetModel') && (!model.has('authorModel') || model.get('authorModel').get('id') != model.get('targetModel').get('id'))) { 
;__p+='\n<div class="pull-left">\n    <a href="'+
( model.get('targetModel').get('link') )+
'"><img src="'+
( model.get('targetModel').get('profilePicture') )+
'" class="profile-picture" alt="'+
( model.get('targetModel').get('fullname') )+
'" /></a>\n</div>\n';
 } 
;__p+='\n';
 if (model.get('type') == 'reward-new') { 
;__p+='\n<div class="pull-right">\n    <div class="reward-'+
( model.get('message_options').type )+
'"></div>\n</div>\n';
 } 
;__p+='\n'+
( getI18nMessage() )+
'\n<div class="clearfix"></div>\n<div class="ticker_separator"></div>';
}
return __p;
};

this["JST"]["app/templates/action/itemWithLargePhoto.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model.has('authorModel')) { 
;__p+='\n<div class="pull-left">\n    <a href="'+
( model.get('authorModel').get('link') )+
'"><img src="'+
( model.get('authorModel').get('profilePicture') )+
'" class="profile-picture" alt="'+
( model.get('authorModel').get('fullname') )+
'" /></a>\n</div>\n    ';
 if (model.has('photo')) { 
;__p+='\n        '+
( $.t('action.photoUploaded', { 'authorLink' : model.get('authorModel').get('link'), 'author': model.get('authorModel').get('fullname'), 'photoLink' : model.get('photo').get('link') }) )+
'\n    ';
 } 
;__p+='\n';
 } 
;__p+='\n<div class="clearfix"></div>\n';
 if (model.has('photo')) { 
;__p+='\n<div class="action-photo">\n    <a href="'+
( model.get('photo').get('link') )+
'" data-bypass=\'\' class=\'photo-link\'><img src="'+
( model.get('photo').get('medium_url') )+
'" alt="'+
( model.get('photo').get('caption') )+
'" /></a>\n</div>\n';
 } 
;__p+='\n<div class="clearfix"></div>\n<div class="ticker_separator"></div>';
}
return __p;
};

this["JST"]["app/templates/action/itemWithPhotos.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model.has('authorModel')) { 
;__p+='\n<div class="pull-left">\n    <a href="'+
( model.get('authorModel').get('link') )+
'"><img src="'+
( model.get('authorModel').get('profilePicture') )+
'" class="profile-picture" alt="'+
( model.get('authorModel').get('fullname') )+
'" /></a>\n</div>\n'+
( $.t('action.photosUploaded', { 'count': model.get('photos').length, 'authorLink' : model.get('authorModel').get('link'), 'author': model.get('authorModel').get('fullname') }) )+
'\n';
 } 
;__p+='\n<div class="clearfix"></div>\n<div class="action-photos">\n    ';
 model.get('photosCollection').each(function(photo, index) { 
;__p+='\n        <a href="'+
( photo.get('link') )+
'" data-bypass=\'\' data-photo-id="'+
( photo.get('id') )+
'" class=\'photo-link photo-'+
( index )+
'\'><img src="'+
( photo.get('small_url') )+
'" alt="'+
( photo.get('caption') )+
'" /></a>\n    ';
 }); 
;__p+='\n</div>\n<div class="clearfix"></div>\n<div class="ticker_separator"></div>';
}
return __p;
};

this["JST"]["app/templates/action/itemWithSmallPhoto.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model.has('brandModel') && model.get('brandModel').has('small_logo_url')) { 
;__p+='\n<div class="pull-left">\n    <a href="'+
( model.get('brandModel').get('link') )+
'"><img src="'+
( model.get('brandModel').get('small_logo_url') )+
'" class="brand-picture" alt="'+
( model.get('brandModel').get('name') )+
'" /></a>\n</div>\n';
 } else if (model.has('authorModel')) { 
;__p+='\n<div class="pull-left">\n    <a href="'+
( model.get('authorModel').get('link') )+
'">\n        <img src="'+
( model.get('authorModel').get('profilePicture') )+
'" class="profile-picture" alt="'+
( model.get('authorModel').get('fullname') )+
'" />\n    </a>\n</div>\n';
 } 
;__p+='\n';
 if (model.has('photo')) { 
;__p+='\n<div class="pull-right">\n    <a href="'+
( model.get('photo').get('link') )+
'" data-bypass="" class="photo-link">\n        <div style="background-image: url(\''+
( model.get('photo').get('medium_url') )+
'\');" class="small-photo"></div>\n    </a>\n</div>\n';
 } 
;__p+='\n'+
( getI18nMessage() )+
'\n<div class="clearfix"></div>\n<div class="ticker_separator"></div>';
}
return __p;
};

this["JST"]["app/templates/action/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="alert-actions"></div>\n<ul class="actions-list list-unstyled"></ul>';
}
return __p;
};

this["JST"]["app/templates/action/noAction.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="no-action-container">\n    <p class="text">'+
( $.t(alertText) )+
'</p>\n    ';
 if (isLogged) { 
;__p+='\n    <p class="text-center mt2">\n        ';
 if (!emptyMessage) { 
;__p+='\n        <button class="followNewUsers btn-around-corner btn-red-grey-border">'+
( $.t('action.followNewUsers') )+
'</button>\n        ';
 } else if (emptyMessage == 'startTagging') { 
;__p+='\n        <a class="btn-arround-corner btn-red-grey-border" href="'+
( rootUrl )+
''+
( $.t('routing.my/photos/') )+
'">'+
( $.t('action.startTagging') )+
'</a>\n        ';
 } 
;__p+='\n    </p>\n    ';
 } 
;__p+='\n</div>\n';
}
return __p;
};

this["JST"]["app/templates/brand/content.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="text-center brand-heading">\n    <h1>'+
( title )+
'</h1>\n</div>\n<div id="loading-brands" class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n<div class="filters-wrapper"></div>\n<div class="brands-wrapper"></div>';
}
return __p;
};

this["JST"]["app/templates/brand/create.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="modal-body-wrapper mt2">\n    <div class="alert-success-brand"></div>\n    <form role="form" enctype="multipart/form-data">\n\n    <div class="alert-add-brand"></div>\n\n    <div class="row form-group">\n        <div class="col-md-6">\n            <input type="text" name="name" placeholder="'+
( $.t('brand.placeholderName') )+
'" class="form-control" />\n        </div>\n        <div class="col-md-6">\n            <input type="hidden" name="original_logo_url">\n            <img class="brand-logo fade-out" />\n        </div>\n    </div>\n\n    <div class="row form-group">\n        <div class="col-md-6">\n            <textarea name="description" placeholder="'+
( $.t('brand.placeholderDescription') )+
'" class="form-control"></textarea>\n        </div>\n        <div class="col-md-6">\n            <select class="categories" name="categories" placeholder="'+
( $.t('brand.labelCategories') )+
'" multiple="multiple">\n                ';
 _.forEach(categories.models, function(category) { 
;__p+='\n                <option value="'+
( category.get('id') )+
'">'+
( category.get('name') )+
'</option>\n                ';
 }); 
;__p+='\n            </select>\n        </div>\n    </div>\n\n    <div class="form-group">\n        <input type="text" name="website_url" placeholder="'+
( $.t('brand.placeholderWebsiteUrl') )+
'" class="form-control" />\n    </div>\n\n    <div class="form-group">\n        <div class="input-group">\n            <input type="text" name="facebook_url" placeholder="'+
( $.t('brand.placeholderFacebookUrl') )+
'" class="form-control" />\n            <span class="input-group-btn">\n                <button type="button" data-toggle="button" data-i18n="[data-loading-text]common.waiting" class="btn btn-info facebookButton">'+
( $.t('brand.loadInfo') )+
'</button>\n            </span>\n        </div>\n    </div>\n\n    <div class="form-group">\n        <input type="text" name="twitter_url" placeholder="'+
( $.t('brand.placeholderTwitterUrl') )+
'" class="form-control" />\n    </div>\n\n    <div class="form-group">\n        <input type="text" name="pinterest_url" placeholder="'+
( $.t('brand.placeholderPinterestUrl') )+
'" class="form-control" />\n    </div>\n\n    <div class="form-group">\n        <input type="text" name="instagram_url" placeholder="'+
( $.t('brand.placeholderInstagramUrl') )+
'" class="form-control" />\n    </div>\n\n    <div class="form-group">\n        <input type="text" name="tumblr_url" placeholder="'+
( $.t('brand.placeholderTumblrUrl') )+
'" class="form-control" />\n    </div>\n\n    <div class="form-group">\n        <input id="logoupload" type="file" accept="image/jpg, image/gif, image/png" name="original_logo_url" style="display: none"/>\n        <div id="uploadbutton" class="btn btn-primary btn-xs">Upload your brand\'s logo</div>\n        <div id="logo-image" style="display:inline" class="text-left"></div>\n    </div>\n\n    <button type="submit" data-i18n="[data-loading-text]brand.addInProgress" class="btn-around-corner btn-red-grey-border" data-toggle="button">'+
( $.t('common.submit') )+
'</button>\n</form>\n</div>';
}
return __p;
};

this["JST"]["app/templates/brand/filters.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<ul class="first-filters list-unstyled">\n    <li><button class="name-filter"><i class="glyphicon glyphicon-tag icon"></i> '+
( $.t('filter.az') )+
'</button></li>\n    <li><button class="number-of-tags-filter"><i class="glyphicon glyphicon-tag icon"></i> '+
( $.t('filter.numberOfTags') )+
'</button></li>\n</ul>';
}
return __p;
};

this["JST"]["app/templates/brand/followButton.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<button data-toggle="button" type="button" class="btn-large btn-around-corner ';
 if (!follow) { 
;__p+='btn-red-grey-border';
 } else { 
;__p+='btn-grey-white-border';
 } 
;__p+=' follow-button">'+
( follow ? $.t("profile.unfollow") : $.t("profile.follow") )+
'</button>';
}
return __p;
};

this["JST"]["app/templates/brand/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (showTagsCount) { 
;__p+='\n<div class="table-center">\n    <div class="table-center-cell text-center">\n        <a href="'+
( model.get('link') )+
'" class="thumbnail">\n            <img src="'+
( model.get("small_logo_url") )+
'" alt="'+
( model.get("name") )+
'" class="brand-logo" />\n            <div class="tagged-count text-center"><span data-i18n="[html]brand.tagged" data-i18n-options=\'{"count": '+
( model.get("tags_count") )+
'}\'></span></div>\n        </a>\n    </div>\n</div>\n';
 } else { 
;__p+='\n<div class="table-center">\n    <div class="table-center-cell text-center">\n        <a href="'+
( model.get('link') )+
'">\n            <img src="'+
( model.get("small_logo_url") )+
'" alt="'+
( model.get("name") )+
'" class="brand-logo" />\n        </a>\n    </div>\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/brand/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="alert-brands"></div>\n<div class="block-white-background">\n    <ul id="brands" class="row list-unstyled"></ul>\n    ';
 if (showAllButton) { 
;__p+='\n    <a data-bypass="" class="viewMore link-grey-whith-red-arrow">'+
( $.t('brand.viewAllBrands') )+
'</a>\n    ';
 } 
;__p+='\n</div>';
}
return __p;
};

this["JST"]["app/templates/brand/menuLeft.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n<aside class="profile-aside fade-out">\n    <div class="block-white-background">\n        <div class="profile-details brand-menu text-center">\n            ';
 if (model.get('medium_logo_url')) { 
;__p+='\n            <div class="profile-picture-wrapper">\n                <img src="'+
( model.get('medium_logo_url') )+
'" alt="'+
( model.get('name') )+
'" class="profile-picture brand-profile-picture" />\n            </div>\n            ';
 } else { 
;__p+='\n                <h1>'+
( model.get('name') )+
'</h1>\n            ';
 } 
;__p+='\n            ';
 if (categories && categories.length > 0) { 
;__p+='\n            <div class="brand-categories text-center">'+
( categories.map(function(cat){return cat.get('name');}).join(', ') )+
'</div>\n            ';
 } 
;__p+='\n            <div class="follow-button"></div>\n\n            <div class="indicators-container text-center">\n                <div class="row">\n                    <div class="col-xs-6">\n                        <div class="separator-sm-vertical"></div>\n                        <div class="indicator-value">'+
( photosCount ? photosCount : 0 )+
'</div>\n                        <div class="indicator-label">'+
( $.t('profile.photosIndicator') )+
'</div>\n                    </div>\n                    <div class="col-xs-6">\n                        <div class="indicator-value">'+
( model.get("tags_count") )+
'</div>\n                        <div class="indicator-label">'+
( $.t('profile.tagsIndicator') )+
'</div>\n                    </div>\n                </div>\n                <div class="row">\n                    <div class="col-xs-6">\n                        <div class="separator-sm-horizontal"></div>\n                        <div class="separator-sm-vertical"></div>\n                        <div class="indicator-value">'+
( model.get("followers_count") )+
'</div>\n                        <div class="indicator-label">'+
( $.t('profile.followersIndicator') )+
'</div>\n                    </div>\n                    <div class="col-xs-6">\n                        <div class="separator-sm-horizontal"></div>\n                        <div class="indicator-value"></div>\n                        <div class="indicator-label"></div>\n                    </div>\n                </div>\n            </div>\n\n        </div>\n    </div>\n    <div class="block-white-bottom"></div>\n\n    <h2>'+
( $.t('brand.rewards') )+
' <i class="glyphicon glyphicon-question-sign pointer rewards-hiw-link"></i></h2>\n    <div class="rewards"></div>\n\n    <hr class="menu-left-hr">\n\n    <h2>'+
( $.t('brand.followersTitle') )+
'</h2>\n    <div class="followers"></div>\n</aside>';
}
return __p;
};

this["JST"]["app/templates/brand/rewards.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="brand-rewards">\n    ';
 if (brand) { 
;__p+='\n    <div class="text-center brand-logo-wrapper">\n        <img src="'+
( brand.get('medium_logo_url') )+
'" alt="'+
( brand.get('name') )+
'" class="profile-picture brand-profile-picture" />\n    </div>\n    ';
 } 
;__p+='\n    <ul class="nav nav-tabs">\n        <li class="active"><a href="#fan" data-bypass="" data-toggle="tab"><i class="icon icon-middle reward-addict"></i> '+
( $.t('brand.fan') )+
'</a></li>\n        <li><a href="#bronze" data-bypass="" data-toggle="tab"><i class="icon icon-middle reward-bronze"></i> '+
( $.t('brand.bronze') )+
'</a></li>\n        <li><a href="#silver" data-bypass="" data-toggle="tab"><i class="icon icon-middle reward-silver"></i> '+
( $.t('brand.silver') )+
'</a></li>\n        <li><a href="#gold" data-bypass="" data-toggle="tab"><i class="icon icon-middle reward-gold"></i> '+
( $.t('brand.gold') )+
'</a></li>\n    </ul>\n\n    <!-- Tab panes -->\n    <div class="tab-content">\n        <div class="tab-pane fade in active" id="fan"></div>\n        <div class="tab-pane fade" id="bronze"></div>\n        <div class="tab-pane fade" id="silver"></div>\n        <div class="tab-pane fade" id="gold"></div>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/category/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (categories.length == 0) { 
;__p+='\n    '+
( $.t('category.noCategory') )+
'\n';
 } else { 
;__p+='\n'+
( $.t('category.listTitle') )+
' <span class="categories-links">'+
( categories.map(function(category) { return '<a class="category-link" href="' + category.get("categoryLink") + '">' + category.get('name') + '</a>'; }).join(', ') )+
'</span>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/category/select.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<select class="selectCategories" multiple="multiple">\n    ';
 _.forEach(collection, function(category) { 
;__p+='\n        <option>'+
( category.get('name') )+
'</option>\n    ';
 }); 
;__p+='\n</select>';
}
return __p;
};

this["JST"]["app/templates/comment/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model.has('authorModel')) { 
;__p+='\n<a class="pull-left" href="'+
( model.get('authorModel').get("link") )+
'">\n    <img class="media-object profile-picture" src="'+
( model.get("authorModel").get('profilePicture') )+
'" alt="'+
( model.get('authorModel').get('fullname') )+
'">\n</a>\n';
 } 
;__p+='\n<div class="media-body">\n    ';
 if (model.isAuthor() || has_role_team()) { 
;__p+='\n    <div class="pull-right">\n        <button type="button" class="close" aria-hidden="true">&times;</button>\n    </div>\n    ';
 } 
;__p+='\n    <h4 class="media-heading"><span class="fullname">'+
( model.get('authorModel').get('fullname') )+
'</span> <span class="date">'+
( model.get("date") )+
'</span></h4>\n    <p>'+
( model.get("body") )+
'</p>\n</div>';
}
return __p;
};

this["JST"]["app/templates/comment/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="alert-comments"></div>\n<div class="add-comment">\n    <form>\n        <fieldset>\n            <div class="alert-add-comment"></div>\n            <textarea data-i18n="[placeholder]comment.placeholderBody" class="comment-body form-control" name="comment-body"></textarea>\n            <div class="text-center mt1 mb1">\n                <button type="submit" class="btn-around-corner btn-red-grey-border add-comment-button" data-i18n="[data-loading-text]comment.addButtonLoading"><i class="icon white-pencil-icon"></i> '+
( $.t('comment.addButton') )+
'</button>\n                ';
 if (has_role_team()) { 
;__p+='\n                    <small><a id="deleteAllComments" class="pull-right delete-comment-button">'+
( $.t('comment.deleteButton') )+
'</a></small>\n                ';
 } 
;__p+='\n            </div>\n        </fieldset>\n    </form>\n</div>\n<ul class="comments-list media-list"></ul>';
}
return __p;
};

this["JST"]["app/templates/common/alert.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="clearfix"></div>\n<div class="alert '+
( model.get('cssClass') )+
' fade in">\n    ';
 if (model.get('showClose')) { 
;__p+='\n    <button type="button" class="close" data-dismiss="alert">×</button>\n    ';
 } 
;__p+='\n    '+
( model.get('message') )+
'\n</div>';
}
return __p;
};

this["JST"]["app/templates/common/modal.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="commonModalLabel" aria-hidden="true">\n    <div class="modal-dialog '+
( modalDialogClasses ? modalDialogClasses : '' )+
'">\n        <div class="modal-content '+
( modalContentClasses ? modalContentClasses : '' )+
'">\n            <div class="modal-header'+
( !showHeader ? ' hide-modal-header' : '' )+
'">\n                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\n                ';
 if (showHeader) { 
;__p+='\n                    <h4 id="commonModalLabel" class="modal-title" data-i18n="[html]'+
( title )+
'"></h4>\n                ';
 } 
;__p+='\n            </div>\n            <div class="modal-body">\n                <div class="modal-body-view"></div>\n                ';
 if (content) { 
;__p+='\n                <div class="modal-body-wrapper mt2">\n                    <p data-i18n="[html]'+
( content )+
'"></p>\n                </div>\n                ';
 } 
;__p+='\n                ';
 if (isPaginationEnabled) { 
;__p+='\n                <div class="pagination-wrapper"></div>\n                ';
 } 
;__p+='\n            </div>\n            ';
 if (showFooter) { 
;__p+='\n            <div class="modal-footer">\n                <button class="btn btn-default" data-dismiss="modal" data-i18n="common.close"></button>\n                ';
 if (showConfirmButton) { 
;__p+='\n                <button class="btn btn-primary" data-action="confirm" data-i18n="'+
( confirmButton )+
'"></button>\n                ';
 } 
;__p+='\n            </div>\n            ';
 } 
;__p+='\n        </div><!-- /.modal-content -->\n    </div><!-- /.modal-dialog -->\n</div><!-- /.modal -->';
}
return __p;
};

this["JST"]["app/templates/common/progressBar.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="progress active">\n    <div class="progress-bar progress-bar-danger"  role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">\n        <span class="sr-only"><span class="progress-value">0</span>% Complete</span>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/externalServicePhotos/albumItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="album-inner">\n    <div class="album-thumbnail" style="background-image: url(\''+
( model.get("picture") )+
'\');">\n        <div class="checked-overlay fade-out">\n            <div class="album-selected"><i class="glyphicon glyphicon-remove unselect-button"></i><i class="glyphicon glyphicon-ok"></i> '+
( $.t('externalServicePhotos.selected') )+
'</div>\n            <label>'+
( $.t('upload.categories') )+
'</label>\n            <select class="selectCategories" multiple="multiple">\n                ';
 _.forEach(categories.models, function(category) { 
;__p+='\n                <option value="'+
( category.get('id') )+
'">'+
( category.get('name') )+
'</option>\n                ';
 }); 
;__p+='\n            </select>\n            <label>'+
( $.t('upload.hashtags') )+
'</label>\n            <input type="hidden" class="selectHashtags bigdrop" />\n        </div>\n    </div>\n    <div class="album-details">\n        <div class="caption caption-select">\n            <p class="album-name">'+
( model.get("name") )+
'</p>\n            <p><a href="'+
( model.get("url") )+
'" class="btn-around-corner btn-grey" data-i18n="externalServicePhotos.viewPhotos"></a></p>\n            <p class="selectAlbumWrapper"><button type="button" class="selectAlbum btn-around-corner btn-red" data-i18n="externalServicePhotos.selectAlbum"></button></p>\n            <p class="unselectAlbumWrapper fade-out"><button type="button" class="unselectAlbum btn-around-corner btn-red" data-i18n="externalServicePhotos.unselectAlbum"></button></p>\n        </div>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/externalServicePhotos/albumList.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-grey-title-container">\n    <div class="back-to-link">\n        <a href="'+
( rootUrl )+
''+
( $.t('routing.upload/') )+
'">'+
( $.t('upload.backToServices') )+
' <i class="glyphicon glyphicon-chevron-right"></i></a>\n    </div>\n    <h1 data-i18n="upload.addAlbumsTitle"></h1>\n    <div class="small-arrow-top"></div>\n</div>\n';
 if (typeof serviceName !== 'undefined') { 
;__p+='\n<div class="service-name-container text-center">\n    <i class="'+
( loweredServiceName )+
'-circle-icon icon"></i> <span class="'+
( loweredServiceName )+
'-text service-name">'+
( serviceName )+
'</span>\n</div>\n';
 } 
;__p+='\n<div id="loading-albums" class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n\n<div class="upload-details">\n    <div class="upload-counter-view"></div>\n    <div class="clearfix"></div>\n</div>\n\n<div class="upload-options">\n    <div class="pull-right">\n        <button class="btn-around-corner btn-red-grey-border btn-large submit-albums-button fade-out" type="button"><i class="glyphicon glyphicon-ok"></i> '+
( $.t('upload.submitAlbums') )+
'</button>\n    </div>\n    <div class="clearfix"></div>\n</div>\n\n<div class="row" id="albums-list"></div>\n\n<div class="upload-options">\n    <div class="form-inline">\n        <label data-i18n="externalServicePhotos.labelPhotosConfidentiality"></label>\n        <select class="photos-confidentiality form-control" name="photos-confidentiality">\n            <option value="public" data-i18n="externalServicePhotos.public"></option>\n            <option value="private" data-i18n="externalServicePhotos.private"></option>\n        </select>\n    </div>\n</div>\n\n<div class="modal fade" id="uploadInProgressModal" tabindex="-1" role="dialog" aria-labelledby="uploadInProgressLabel" aria-hidden="true">\n    <div class="modal-dialog">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\n                <h4 id="uploadInProgressLabel" class="modal-title" data-i18n="externalServicePhotos.modalUploadTitle"></h4>\n            </div>\n            <div class="modal-body">\n                <div class="modal-body-wrapper mt2">\n                    <p data-i18n="externalServicePhotos.modalUploadText"></p>\n                </div>\n            </div>\n            <div class="modal-footer">\n                <button class="btn btn-default" data-dismiss="modal" data-i18n="common.close"></button>\n            </div>\n        </div><!-- /.modal-content -->\n    </div><!-- /.modal-dialog -->\n</div><!-- /.modal -->';
}
return __p;
};

this["JST"]["app/templates/externalServicePhotos/counter.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<span class="upload-counter pull-right">'+
( $.t('upload.' + translationKey, {'count': count}) )+
'</span>';
}
return __p;
};

this["JST"]["app/templates/externalServicePhotos/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="photo-inner">\n    <div class="photo-thumbnail" style="background-image: url(\''+
( model.get("thumbUrl") )+
'\');">\n        <div class="hover-overlay">\n            <div class="table-center">\n                <div class="table-center-cell"><i class="icon_more_red"></i></div>\n            </div>\n        </div>\n        <div class="checked-overlay fade-out">\n            <div class="photo-selected"><i class="glyphicon glyphicon-remove unselect-button"></i><i class="glyphicon glyphicon-ok"></i> '+
( $.t('externalServicePhotos.photoSelected') )+
'</div>\n            <label>'+
( $.t('upload.categories') )+
'</label>\n            <select class="selectCategories" multiple="multiple">\n                ';
 _.forEach(categories.models, function(category) { 
;__p+='\n                <option value="'+
( category.get('id') )+
'">'+
( category.get('name') )+
'</option>\n                ';
 }); 
;__p+='\n            </select>\n            <label>'+
( $.t('upload.hashtags') )+
'</label>\n            <input type="hidden" class="selectHashtags bigdrop" />\n        </div>\n    </div>\n</div>\n\n';
}
return __p;
};

this["JST"]["app/templates/externalServicePhotos/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-grey-title-container">\n    ';
 if (typeof(showBackTo) !== 'undefined' && showBackTo) { 
;__p+='\n    <div class="pull-right back-to-link">\n        <a href="'+
( backToLink )+
'">'+
( backToText )+
' <i class="glyphicon glyphicon-chevron-right"></i></a>\n    </div>\n    ';
 } 
;__p+='\n    <h1 data-i18n="upload.'+
( typeof(title) !== 'undefined' ? title : 'addPhotosTitle' )+
'"></h1>\n    <div class="small-arrow-top"></div>\n</div>\n';
 if (typeof serviceName !== 'undefined') { 
;__p+='\n<div class="service-name-container text-center">\n    <i class="'+
( loweredServiceName )+
'-circle-icon icon"></i> <span class="'+
( loweredServiceName )+
'-text service-name">'+
( serviceName )+
'</span>\n</div>\n';
 } 
;__p+='\n<div id="loading-photos" class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n<div id="loading-upload" class="loading-gif-container" style="display: none;">\n    <div class="loader rotate"></div>\n    <p><em class="small muted" data-i18n="externalServicePhotos.download"></em></p>\n</div>\n\n<div id="photos-container">\n    <div class="upload-details">\n        ';
 if (album) { 
;__p+='\n        <div class="album-name pull-left">'+
( album )+
'</div>\n        ';
 } 
;__p+='\n        <div class="upload-counter-view"></div>\n        <div class="clearfix"></div>\n    </div>\n\n    <div class="upload-options">\n        <div class="pull-right">\n            <button class="btn-around-corner btn-red-grey-border btn-large submit-photos-button fade-out" type="button"><i class="glyphicon glyphicon-ok"></i> '+
( $.t('upload.submitPhotos') )+
'</button>\n        </div>\n        <div class="clearfix"></div>\n    </div>\n\n    <div id="errors"></div>\n    <div id="photos-list" class="row"></div>\n\n    <div class="upload-options">\n        <div class="form-inline">\n            <label data-i18n="externalServicePhotos.labelPhotosConfidentiality"></label>\n            <select class="photos-confidentiality form-control" name="photos-confidentiality">\n                <option value="public" data-i18n="externalServicePhotos.public"></option>\n                <option value="private" data-i18n="externalServicePhotos.private"></option>\n            </select>\n        </div>\n    </div>\n</div>\n\n<div class="modal fade" id="uploadInProgressModal" tabindex="-1" role="dialog" aria-labelledby="uploadInProgressLabel" aria-hidden="true">\n    <div class="modal-dialog">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\n                <h4 id="uploadInProgressLabel" class="modal-title" data-i18n="externalServicePhotos.modalUploadTitle"></h4>\n            </div>\n            <div class="modal-body">\n                <div class="modal-body-wrapper mt2">\n                    <p data-i18n="externalServicePhotos.modalUploadText"></p>\n                </div>\n            </div>\n            <div class="modal-footer">\n                <button class="btn btn-default" data-dismiss="modal" data-i18n="common.close"></button>\n            </div>\n        </div><!-- /.modal-content -->\n    </div><!-- /.modal-dialog -->\n</div><!-- /.modal -->';
}
return __p;
};

this["JST"]["app/templates/flickrSets/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="thumbnail">\n    <div class="caption">\n        <h3>'+
( model.get("title") )+
'</h3>\n        <p>\n            <small>'+
( model.get("description") )+
'</small>\n        </p>\n        <p>\n            <a href="flickr/sets/'+
( model.get("id") )+
'/photos/" class="btn btn-primary btn-small" data-i18n="flickr.useThisAlbum"></a>\n        </p>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/flickrSets/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div id="loading-sets" class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n<ul id="sets-list" class="thumbnails"></ul>';
}
return __p;
};

this["JST"]["app/templates/hashtag/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get('link') )+
'" class="hashtag-box">#'+
( model.get('name') )+
'</a>';
}
return __p;
};

this["JST"]["app/templates/hashtag/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="hashtags-alert"></div>\n<ul class="hashtags-list list-unstyled"></ul>\n<div class="clearfix"></div>\n';
 if (showAllButton) { 
;__p+='\n<a data-bypass="" class="viewMore link-grey-whith-red-arrow">'+
( $.t('hashtag.viewAllHashtags') )+
'</a>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/mySettings/detail.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-grey-title-container">\n    <h1 data-i18n="mySettings.generalSettings"></h1>\n</div>\n\n<form class="form-horizontal settingsForm mt3" role="form">\n    <fieldset>\n        <div class="form-group">\n            <label class="col-sm-4 control-label" data-i18n="mySettings.labelLang"></label>\n            <div class="col-sm-8">\n                <select class="form-control" id="lang" name="lang">\n                    <option value="fr" data-i18n="mySettings.french"></option>\n                    <option value="en" data-i18n="mySettings.english"></option>\n                </select>\n            </div>\n        </div>\n        <div class="form-group form-inline">\n            <label class="col-sm-4 control-label" data-i18n="mySettings.labelBirthday"></label>\n            <div class="col-sm-8">\n                <div class="birthdate"></div>\n            </div>\n        </div>\n        <div class="form-group">\n            <label class="col-sm-4 control-label" data-i18n="mySettings.profilePicture"></label>\n            <div class="col-sm-8">\n                <span class="btn btn-primary fileinput-button">\n                    <i class="icon-plus icon-white"></i>\n                    <span data-i18n="mySettings.profilePictureUpload"></span>\n                    <!-- The file input field used as target for the file upload widget -->\n                    <input id="profilepictureupload" type="file" name="profilepicture">\n                </span>\n            </div>\n        </div>\n\n        <div class="form-group">\n        <div class="checkbox">\n            <div class="col-sm-8 col-sm-offset-4">\n                <label>\n                    <input id="shareDataAdvertisers" name="shareDataAdvertisers" type="checkbox" checked="checked"> '+
( $.t('mySettings.shareDataAdvertisers') )+
'\n                </label>\n            </div>\n        </div>\n        </div>\n\n        <div class="form-group">\n            <div class="checkbox">\n                <div class="col-sm-8 col-sm-offset-4">\n                    <label>\n                        <input id="partnersNewsletters" name="partnersNewsletters" type="checkbox"> '+
( $.t('mySettings.partnersNewsletters') )+
'\n                    </label>\n                </div>\n            </div>\n        </div>\n\n        <div class="form-group">\n            <div class="col-sm-offset-4 col-sm-8">\n                <button type="submit" class="btn-around-corner btn-red-grey-border submitSettings"><i class="glyphicon glyphicon-ok"></i> '+
( $.t('mySettings.submitChangeLang') )+
'</button>\n            </div>\n        </div>\n    </fieldset>\n</form>\n\n<hr>\n\n<form class="form-horizontal changePasswordForm fade-out">\n    <fieldset>\n        <legend data-i18n="mySettings.changePasswordLegend"></legend>\n        <div class="alert-changePassword"></div>\n        <div class="form-group">\n            <label class="col-sm-2 control-label" data-i18n="mySettings.actualPassword"></label>\n            <div class="col-sm-10">\n                <input class="form-control currentPassword" type="password" name="current_password">\n            </div>\n        </div>\n        <div class="form-group">\n            <label class="col-sm-2 control-label" data-i18n="mySettings.newPassword"></label>\n            <div class="col-sm-10">\n                <input class="form-control newPasswordFirst" type="password" name="plainPassword[first]">\n            </div>\n        </div>\n        <div class="form-group">\n            <label class="col-sm-2 control-label" data-i18n="mySettings.confirmPassword"></label>\n            <div class="col-sm-10">\n                <input class="form-control newPasswordSecond" type="password" name="plainPassword[second]">\n            </div>\n        </div>\n        <div class="form-group">\n            <div class="col-sm-offset-2 col-sm-10">\n                <button type="submit" class="btn-around-corner btn-red-grey-border changePasswordButton"><i class="glyphicon glyphicon-ok"></i> '+
( $.t('mySettings.submitChangePassword') )+
'</button>\n            </div>\n        </div>\n    </fieldset>\n</form>\n\n<div class="mt3 text-center">\n    <button class="btn-around-corner btn-grey delete-account-button">'+
( $.t('mySettings.deleteAccount') )+
'</button>\n</div>';
}
return __p;
};

this["JST"]["app/templates/mySettings/serviceItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="service-icon '+
( model.get('service_name').toLowerCase() )+
'-text">\n    <div class="'+
( model.get('service_name').toLowerCase() )+
'-circle-icon service-icon-tooltip" data-toggle="tooltip" title data-original-title="'+
( model.get('service_name') )+
'"></div>';
 if (showLabel) { 
;__p+=' <div class="service-name-label">'+
( model.get('service_name') )+
'</div>';
 } 
;__p+='\n</div>\n<div class="service-action">\n    ';
 if (model.has('cant_delete') && model.get('cant_delete')) { 
;__p+='\n    '+
( $.t('mySettings.serviceCannotBeDeleted') )+
'\n    ';
 } else if (model.has('linked') && !model.get('linked')) { 
;__p+='\n    <button class="btn-around-corner btn-dark-grey connect-button connect-'+
( model.get('service_name').toLowerCase() )+
'"> '+
( $.t('mySettings.linkMyAccount') )+
'</button>\n    ';
 } else { 
;__p+='\n    <button class="btn-around-corner btn-white-grey deletelink"><i class="glyphicon glyphicon-remove"></i> '+
( $.t('mySettings.deleteLink') )+
'</button>\n    ';
 } 
;__p+='\n</div>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/mySettings/serviceList.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<ul class="services-list list-unstyled"></ul>';
}
return __p;
};

this["JST"]["app/templates/notifications/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model.has("authorModel")) { 
;__p+='\n<div class="author pull-left">\n   <a href="'+
( model.get('authorModel').get('link') )+
'"><img src="'+
( model.get('authorModel').get('profilePicture') )+
'" alt="'+
( model.get('authorModel').get('fullname') )+
'" class="profile-picture" /></a>\n</div>\n';
 } 
;__p+='\n';
 if (model.has('brandModel')) { 
;__p+='\n<div class="notification-photos pull-right">\n    <a href="'+
( model.get('brandModel').get('link') )+
'" data-photo-link="" data-bypass=""><div style="background-image: url(\''+
( model.get('brandModel').get('medium_logo_url') )+
'\');" class="notification-photo"></div></a>\n</div>\n';
 } else if (model.has('photosCollection') && model.get('photosCollection').length > 0) { 
;__p+='\n<div class="notification-photos pull-right">\n    ';
 model.get('photosCollection').each(function(photo) { 
;__p+='\n    <a href="'+
( photo.get('link') )+
'" data-photo-link="" data-bypass=""><div style="background-image: url(\''+
( photo.get('small_url') )+
'\');" class="notification-photo"></div></a>\n    ';
 }); 
;__p+='\n</div>\n';
 } 
;__p+='\n<div class="message">'+
( getI18nMessage() )+
'</div>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/notifications/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (notifications.unreadCount() > 0) { 
;__p+='<button class="notifications-button" title="Notifications" data-toggle="button">'+
( notifications.unreadCount() )+
'</button>';
 } 
;__p+='\n<div class="dropdown-menu fade-out">\n    <div class="white-arrow-top"></div>\n    <div class="alert-notifications"></div>\n    <ul class="notifications-list striped"></ul>\n</div>';
}
return __p;
};

this["JST"]["app/templates/pagination/nextpage.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="loading-gif-container fade-out">\n    <div class="loader rotate"></div>\n</div>\n';
 if (showLoadMoreButton) { 
;__p+='\n    <div class="text-center">\n        <button class="loadMore btn-around-corner btn-red-grey-border" data-loading-text="'+
( $.t(model.get('loadingText')) )+
'">'+
( $.t(model.get('buttonText')) )+
'</button>\n    </div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/photo/edit.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="edit-photo-wrapper">\n    <div class="photo-container text-center">\n        <div class="photo-overlay">\n            <div class="tags-container"></div>\n        </div>\n        <img src="'+
( model.get('large_url') )+
'" alt="'+
( model.get('caption') )+
'" class="photo-full" />\n    </div>\n</div>\n';
 if (model.isOwner()) { 
;__p+='\n<div class="photo-details">\n    <div class="alert-photo-details"></div>\n\n    <form id="form-details">\n        <fieldset>\n            <div class="form-group">\n                <input class="form-control" data-i18n="[placeholder]myPhotos.labelCaption" type="text" id="photo-caption" value="'+
( model.get('caption') )+
'">\n            </div>\n            <div class="form-group">\n                <div class="row">\n                    <div class="col-sm-6">\n                        ';
 if (categories && categories.length) { 
;__p+='\n                        <select data-i18n="[placeholder]upload.categories" class="selectCategories" multiple="multiple">\n                            ';
 _.forEach(categories.models, function(category) { 
;__p+='\n                            <option value="'+
( category.get('id') )+
'" '+
( isSelectedCategory(category) )+
'>'+
( category.get('name') )+
'</option>\n                            ';
 }); 
;__p+='\n                        </select>\n                        ';
 } 
;__p+='\n                    </div>\n                    <div class="col-sm-6">\n                        <input data-i18n="[placeholder]upload.hashtags" type="hidden" class="selectHashtags bigdrop" />\n                    </div>\n                </div>\n            </div>\n            <p><button type="submit" class="btn-around-corner btn-red-grey-border" data-loading-text="Chargement..."><i class="glyphicon glyphicon-ok"></i> <span data-i18n="common.submit"></span></button></p>\n        </fieldset>\n    </form>\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/photo/favoriteButton.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<button type="button" class="btn-around-corner btn-red-grey-border favorite-button" data-toggle="button"><i class="glyphicon glyphicon-star"></i> '+
( added ? $.t('hoto.unFavorites') : $.t('photo.favorites') )+
'</button>';
}
return __p;
};

this["JST"]["app/templates/photo/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-photo-buttons pull-right">\n    <div class="like-button pull-right"></div>\n    <div class="pull-right photo-counters"><span class="likes-count"><i class="icon red-heart-icon"></i> <span class="red-count likes-count-value">'+
( model.get("likes_count") )+
'</span></span></div>\n    <div class="pull-right mr2">\n        <button class="btn-around-corner btn-red-grey-border add-new-tag">'+
( $.t('tag.addNewTag') )+
'</button>\n    </div>\n</div>\n<h1 class="lead">'+
( model.get("caption") )+
'</h1>\n<div class="clearfix"></div>\n<div class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n<div class="full-photo text-center fade-out">\n    <div class="photo-container">\n        <div class="adentify-pastille-wrapper">\n            ';
 if (model.get("tags_count") > 0) { 
;__p+='\n            <div class="adentify-pastille"></div>\n            ';
 } else { 
;__p+='\n            <div class="adentify-grey-pastille"></div>\n            ';
 } 
;__p+='\n            <div class="popover-wrapper"></div>\n        </div>\n        <div class="photo-overlay">\n            <div class="tags-container"></div>\n        </div>\n        <div class="share-overlay fade-out"></div>\n        <img src="'+
( model.get("large_url") )+
'" alt="'+
( model.get("caption") )+
'" class="photo-full" />\n    </div>\n</div>\n<div class="mt2">\n    <div class="pull-left">\n        <iframe allowtransparency="true" frameborder="0" scrolling="no" src="https://platform.twitter.com/widgets/tweet_button.html?text='+
( model.getShareText('twitter') )+
'&via=AdEntify&lang='+
( currentLocale )+
'&url='+
( pageUrl )+
'" style="width:130px; height:20px;"></iframe>\n        <div class="g-plusone" data-size="medium" data-href="'+
( pageUrl )+
'"></div>\n        <div class="fblike"><div class="fb-like" data-href="'+
( pageUrl )+
'" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false" data-font="arial"></div></div>\n        <div class="pinterest"><a target="_blank" href="//pinterest.com/pin/create/button/?url='+
( encodeURIComponent(pageUrl) )+
'&media='+
( encodeURIComponent(model.get("large_url")) )+
'&description='+
( encodeURIComponent(model.getShareText()) )+
'" data-pin-do="buttonPin" data-pin-config="beside"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a></div>\n    </div>\n    <div class="pull-right">\n        <span class="source">Source :</span> <span class="source-name">'+
( model.get("source") )+
'</span> <button class="btn btn-link report-button" data-toggle="tooltip" data-original-title="'+
( $.t('photo.report') )+
'"><i class="glyphicon glyphicon-warning-sign"></i></button>\n    </div>\n    <div class="clearfix"></div>\n    <div class="categories-hashtags">\n        <div class="categories pull-left"></div>\n        <div class="hashtags pull-left"></div>\n        <div class="pull-right alert-info">'+
( $.t('photo.publishDate') )+
' '+
( publishDate )+
'</div>\n        <div class="clearfix"></div>\n    </div>\n</div>\n\n<ul class="nav nav-tabs" id="photo-tabs">\n    <li class="active"><a href="#comments" data-bypass=""><i class="icon grey-comment-icon"></i> '+
( $.t('photo.comments') )+
'</a></li>\n    <li><a href="#share" data-bypass=""><i class="icon grey-share-icon"></i> '+
( $.t('photo.share') )+
'</a></li>\n</ul>\n\n<div class="tab-content photo-tab-content">\n    <div class="tab-pane active" id="comments">\n        <div class="comments"></div>\n    </div>\n    <div class="tab-pane" id="share">\n        <h2 data-i18n="photo.link"></h2>\n        <input type="text" value="'+
( pageUrl )+
'" class="input-block-level form-control selectOnFocus">\n\n        ';
 if (model.get('visibility_scope') == 'public') { 
;__p+='\n        <h2 data-i18n="photo.embed"></h2>\n        <label class="checkbox">\n            <input type="checkbox" checked="checked" class="showTagsCheckbox"> <span data-i18n="embed.showTags"></span>\n        </label>\n        <textarea class="embedCode form-control input-block-level selectOnFocus" rows="3">&lt;iframe src="https://adentify.com/iframe/photo-'+
( model.get('id') )+
'.html" scrolling="no" frameborder="0" style="border:none; overflow:hidden;" width="'+
( model.get("large_width") )+
'" height="'+
( model.get("large_height") )+
'" allowTransparency="true"&gt;&lt;/iframe&gt;</textarea>\n        ';
 } else { 
;__p+='\n            <div class="alert alert-info mt2">\n                '+
( $.t('photo.noEmbedOnPrivatePhoto') )+
'\n            </div>\n        ';
 } 
;__p+='\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/photo/likeButton.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<button type="button" class="btn-around-corner btn-grey-white-border like-button" data-toggle="button" data-liked="'+
( liked )+
'"><i class="glyphicon glyphicon-heart"></i> '+
( liked ? $.t('photo.liked') : $.t('photo.like') )+
'</button>';
}
return __p;
};

this["JST"]["app/templates/photo/modal.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="media-body-wrapper">\n    <div class="modal-row row">\n        <div id="center-modal" class="col-sm-8 col-md-9">\n            <div id="center-modal-content">\n                <div class="loading-gif-container">\n                    <div class="loader rotate"></div>\n                </div>\n            </div>\n        </div>\n        <div id="right-modal" class="col-sm-4 col-md-3">\n            <div id="right-modal-content">\n                <div class="loading-gif-container">\n                    <div class="loader rotate"></div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/photo/pastillePopover.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="popover">\n    <div class="arrow-top-adentify-pastille-hover"></div>\n    <ul class="popover-pastille-buttons list-unstyled">\n        <li><button class="btn-icon add-tag-icon add-tag-button" data-toggle="tooltip" title data-placement="right" data-original-title="'+
( $.t('photo.addTagTooltip') )+
'"></button></li>\n        <li><button class="btn-icon like-icon '+
( liked ? 'active' : '' )+
' like-button" data-toggle="tooltip" title data-placement="right" data-original-title="'+
( $.t('photo.likeTooltip') )+
'"></button></li>\n        <li><button class="btn-icon share-icon share-button" data-toggle="tooltip" title data-placement="right" data-original-title="'+
( $.t('photo.shareTooltip') )+
'"></button></li>\n        <li><button class="btn-icon favorite-icon '+
( isFavorite ? 'active' : '' )+
' favorite-button" data-toggle="tooltip" title data-placement="right" data-original-title="'+
( $.t('photo.favoriteTooltip') )+
'"></button></li>\n    </ul>\n</div>';
}
return __p;
};

this["JST"]["app/templates/photo/report.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="modal-body-wrapper">\n    <form class="form-horizontal mt2" role="form">\n        <fieldset>\n\n            ';
 if (model.isOwner() && (model.get('tags_count') === 0 || (model.has('tags') && model.get('tags').length === 0))) { 
;__p+='\n            <div class="form-group">\n                <div class="col-md-12 text-center">\n                    <button type="button" data-bypass="" class="btn-around-corner btn-red-grey-border deletePhotoButton">'+
( $.t('photo.deletePhoto') )+
'</button>\n                </div>\n            </div>\n            ';
 } else if (model.isOwner() && model.get('tags_count') > 0) { 
;__p+='\n            <div class="form-group">\n                <div class="col-md-12 text-center">\n                    <p>'+
( $.t('photo.deleteTagsBeforePhoto') )+
'</p>\n                </div>\n            </div>\n            ';
 } 
;__p+='\n\n            <div class="form-group">\n                <div class="col-md-10 col-md-offset-2">\n                    <div class="radio">\n                        <label>\n                            <input type="radio" name="reportOptions" id="reportOptions1" value="photo.reportDontLike">\n                            '+
( $.t('photo.reportDontLike') )+
'\n                        </label>\n                    </div>\n                    <div class="radio">\n                        <label>\n                            <input type="radio" name="reportOptions" id="reportOptions2" value="photo.reportHarassing">\n                            '+
( $.t('photo.reportHarassing') )+
'\n                        </label>\n                    </div>\n                    <div class="radio">\n                        <label>\n                            <input type="radio" name="reportOptions" id="reportOptions3" value="photo.reportShouldntBe">\n                            '+
( $.t('photo.reportShouldntBe') )+
'\n                        </label>\n                    </div>\n                    <div class="radio">\n                        <label>\n                            <input type="radio" name="reportOptions" id="reportOptions4" value="photo.reportSpam">\n                            '+
( $.t('photo.reportSpam') )+
'\n                        </label>\n                    </div>\n                    <div class="radio">\n                        <label>\n                            <input type="radio" name="reportOptions" id="reportOptions5" value="photo.reportOther" checked>\n                            '+
( $.t('photo.reportOther') )+
'\n                        </label>\n                    </div>\n                </div>\n            </div>\n\n            <div class="form-group">\n                <label data-i18n="photo.reportReasonLabel" class="col-sm-4 control-label"></label>\n                <div class="col-sm-8">\n                    <textarea class="form-control reason-textarea" data-i18n="[placeholder]photo.reportReasonTextarea"></textarea>\n                </div>\n            </div>\n\n            <div class="form-group">\n                <div class="col-md-8 col-md-offset-4">\n                    <button type="button" class="btn btn-link" data-dismiss="modal">'+
( $.t('common.cancel') )+
'</button>\n                    <input type="submit" class="btn-around-corner btn-red-grey-border reportSubmit" value="'+
( $.t('common.report') )+
'" />\n                </div>\n            </div>\n        </fieldset>\n    </form>\n</div>';
}
return __p;
};

this["JST"]["app/templates/photo/rightMenu.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model.has('ownerModel')) { 
;__p+='\n<div class="photo-owner">\n    <div class="owner-picture text-center"><a href="'+
( model.get('ownerModel').get('link') )+
'"><img src="'+
( model.get('ownerModel').get('largeProfilePicture') )+
'" alt="'+
( model.get('ownerModel').get('fullname') )+
'" class="profile-picture" /></a></div>\n    <div class="owner-fullname text-center"><a href="'+
( model.get('ownerModel').get('link') )+
'">'+
( model.get('ownerModel').get('fullname') )+
'</a></div>\n    ';
 if (currentUserId != model.get('ownerModel').get('id')) { 
;__p+='\n    <div class="follow-button-container text-center">\n        <div class="follow-button"></div>\n    </div>\n    ';
 } 
;__p+='\n</div>\n';
 } 
;__p+='\n<div class="linked-photos">\n    <div class="linked-photos-icon"></div>\n    <div class="text-center">\n        <h2 class="linked-photos-title">'+
( $.t('photo.linkedPhotos') )+
'</h2>\n    </div>\n    <div class="alert-linked-photos-list"></div>\n    <ul class="linked-photos-list list-unstyled"></ul>\n</div>';
}
return __p;
};

this["JST"]["app/templates/photo/shareOverlay.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<i class="glyphicon glyphicon-remove close-share"></i>\n<div class="share-overlay-cell">\n    <div class="share-overlay-inner">\n        <iframe allowtransparency="true" frameborder="0" scrolling="no"\n                src="https://platform.twitter.com/widgets/tweet_button.html?text='+
( $.t('photo.tweetShare') )+
'&via=AdEntify&lang='+
( currentLocale )+
'" style="width:130px; height:20px;"></iframe>\n        <div class="g-plusone" data-size="medium" data-href="'+
( pageUrl )+
'"></div>\n        <script type="text/javascript">\n            window.___gcfg = {lang: \'fr\'};\n\n            (function() {\n                var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;\n                po.src = \'https://apis.google.com/js/plusone.js\';\n                var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);\n            })();\n        </script>\n        <div class="fblike"><div class="fb-like" data-href="'+
( pageUrl )+
'" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false" data-font="arial"></div></div>\n        <div class="pinterest"><a target="_blank" href="//pinterest.com/pin/create/button/?url='+
( encodeURIComponent(pageUrl) )+
'&media='+
( encodeURIComponent(model.get("large_url")) )+
'&description='+
( encodeURIComponent(model.get("caption")) )+
'" data-pin-do="buttonPin" data-pin-config="beside"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a></div>\n        <textarea class="embedCode form-control input-block-level selectOnFocus" rows="3">&lt;iframe src="'+
( rootUrl )+
'iframe/photo-'+
( model.get('id') )+
'.html" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'+
( model.get("large_width") )+
'px; height:'+
( model.get("large_height") )+
'px;" allowTransparency="true"&gt;&lt;/iframe&gt;</textarea>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/photos/content.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div id="photos">\n    <div class="top-grey-title-container text-center">\n        ';
 if (category) { 
;__p+='\n        ';
 if (category.has('name')) { 
;__p+='\n        <h1 class="photos-title">'+
( $.t('category.title', { name: category.get('name') }) )+
'</h1>\n        ';
 } 
;__p+='\n        ';
 } else { 
;__p+='\n        <h1 class="photos-title fade-out"></h1>\n        ';
 } 
;__p+='\n        <div class="small-arrow-top"></div>\n    </div>\n    ';
 if (showServices) { 
;__p+='\n    <div class="services-container"></div>\n    ';
 } 
;__p+='\n    ';
 if (filters) { 
;__p+='\n    <div class="filters-wrapper"></div>\n    ';
 } 
;__p+='\n    <div class="photos-grid-container"></div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/photos/filters.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div>\n    <ul class="first-filters list-unstyled">\n        <li><button class="brands-filter"><i class="glyphicon glyphicon-tag icon"></i> '+
( $.t('filter.brands') )+
'</button></li>\n        <li><button class="places-filter"><i class="venues-icon icon"></i> '+
( $.t('filter.places') )+
'</button></li>\n        <li><button class="people-filter"><i class="people-icon icon"></i> '+
( $.t('filter.people') )+
'</button></li>\n        <li class="pull-right">\n            <button type="button" class="dropdown-toggle collapsed" data-toggle="collapse" data-target=".more-filters">\n                <span class="caret"></span>\n                <span class="sr-only">Toggle Dropdown</span>\n            </button>\n        </li>\n    </ul>\n</div>\n<ul class="more-filters list-unstyled collapse">\n    <li class="date-filter"><i class="clock-icon icon"></i> <span class="text-filter">'+
( $.t('filter.byDate') )+
'</span> <i class="glyphicon glyphicon-chevron-down pointer"></i> <i class="glyphicon glyphicon-chevron-up pointer"></i></li>\n    <li class="like-filter"><i class="heart-icon icon"></i> <span class="text-filter">'+
( $.t('filter.byLikes') )+
'</span> <i class="glyphicon glyphicon-chevron-down pointer"></i> <i class="glyphicon glyphicon-chevron-up pointer"></i></li>\n    <li class="like-filter"><i class="glyphicon glyphicon-tower icon"></i> <span class="text-filter">'+
( $.t('filter.byPoints') )+
'</span> <i class="glyphicon glyphicon-chevron-down pointer"></i> <i class="glyphicon glyphicon-chevron-up pointer"></i></li>\n    <!--<li><button class="most-recent-filter"><i class="clock-icon icon"></i> '+
( $.t('filter.mostRecentToOldest') )+
'</button> </li>\n    <li><button class="oldest-filter"><i class="clock-icon icon"></i> '+
( $.t('filter.mostOldestToRecent') )+
'</button></li>\n    <li><button class="most-liked-filter"><i class="heart-icon icon"></i> '+
( $.t('filter.mostLiked') )+
'</button></li>-->\n</ul>';
}
return __p;
};

this["JST"]["app/templates/photos/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model) { 
;__p+='\n<div class="photo medium">\n    <div class="photo-container">\n        <div class="adentify-pastille-wrapper">\n            ';
 if (model.get("tags_count") > 0) { 
;__p+='\n            <div class="adentify-pastille-small"></div>\n            ';
 } else { 
;__p+='\n            <div class="adentify-grey-pastille-small"></div>\n            ';
 } 
;__p+='\n            <div class="popover-wrapper"></div>\n        </div>\n        ';
 if (addTag) { 
;__p+='\n        <div class="add-tag">\n            <div class="table-center">\n                <div class="table-center-cell">\n                    <i class="icon_more_red icon"></i><br>\n                    '+
( $.t('photos.addTag') )+
'\n                </div>\n            </div>\n        </div>\n        ';
 } 
;__p+='\n        <a href="'+
( model.get('link') )+
'" data-bypass="" class="photo-link photo-overlay">\n            <div class="tags-container"></div>\n        </a>\n        <img src="'+
( model.get("medium_url") )+
'" class="photo-img-medium" />\n        ';
 if (showPhotoInfo) { 
;__p+='\n        <div class="photo-info">\n            <div class="pull-left">\n                <a href="'+
( model.get('ownerModel').get('link') )+
'">\n                    <img src="'+
( model.get('ownerModel').get('profilePicture') )+
'" class="profile-picture" alt="'+
( model.get('ownerModel').get('fullname') )+
'" />\n                    <span>'+
( model.get('ownerModel').get('fullname') )+
'</span>\n                </a>\n            </div>\n            ';
 if (model.get('likes_count') > 0 || model.get('comments_count') > 0) { 
;__p+='\n            <div class="pull-right">\n                <a href="'+
( model.get('link') )+
'" data-bypass="" class="photo-link">\n                    <i class="white-heart-icon icon"></i> '+
( model.get('likes_count') )+
' <i class="white-comment-icon icon"></i> '+
( model.get('comments_count') )+
'\n                </a>\n            </div>\n            ';
 } 
;__p+='\n            <div class="clearfix"></div>\n        </div>\n        ';
 } 
;__p+='\n    </div>\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/photos/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div id="loading-photos" class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n<ul id="photos-grid" class="transitions-enabled clearfix"></ul>\n<div class="clearfix"></div>\n<div class="pagination-wrapper"></div>';
}
return __p;
};

this["JST"]["app/templates/photos/menuTools.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<button class="close">&times;</button>\n<h2 data-i18n="myPhotos.tools"></h2>\n<ul id="tools">\n    <li><button id="add-tag" class="btn"><i class="icon-tag"></i> <span data-i18n="myPhotos.addTagButton"></span></button></li>\n</ul>\n<div id="tool-details"></div>\n<form id="form-details">\n    <fieldset>\n        <legend data-i18n="myPhotos.legendPhotoDetails"></legend>\n        <label data-i18n="myPhotos.labelTitle"></label>\n        <input type="text" id="photo-caption">\n        <p><button type="submit" class="btn btn-success btn-small" data-loading-text="Chargement..."><i class="icon-ok icon-white"></i> <span data-i18n="common.submit"></span></button></p>\n    </fieldset>\n</form>';
}
return __p;
};

this["JST"]["app/templates/photos/tickerPhotoItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get('link') )+
'" data-bypass="" class="photo-link"><img src="'+
( model.get("medium_url") )+
'" class="photo" /></a>';
}
return __p;
};

this["JST"]["app/templates/photos/tickerPhotoList.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div id="ticker-photos-title" class="lead text-info"></div>\n<ul class="ticker-photos"></ul>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/reward/brandItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get('brandModel').get('link') )+
'">\n    <div class="reward-icon">\n        <div class="reward-'+
( model.get('type') )+
'"></div>\n    </div>\n    <i class="glyphicon glyphicon-info-sign pull-right"></i>\n    <span class="reward-text-'+
( model.get('type') )+
'">'+
( model.get('brandModel').get('name') )+
' '+
( model.get('type') )+
'</span>\n    <div class="clearfix"></div>\n</a>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/reward/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="rewards-alert"></div>\n<div class="block-white-background">\n    <ul class="rewards list-unstyled striped"></ul>\n    ';
 if (showAllButton) { 
;__p+='\n    <a data-bypass="" class="viewMore link-grey-whith-red-arrow">'+
( $.t('brand.viewAllRewards') )+
'</a>\n    ';
 } 
;__p+='\n</div>';
}
return __p;
};

this["JST"]["app/templates/reward/presentation.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="rewards-presentation">\n    <h2 class="text-center">'+
( $.t('reward.presentationTitle') )+
'</h2>\n    <ul class="nav nav-tabs">\n        <li class="active"><a href="#presentation" data-bypass="" data-toggle="tab">'+
( $.t('reward.presentationTab') )+
'</a></li>\n        <li><a href="#points" data-bypass="" data-toggle="tab">'+
( $.t('reward.pointsTab') )+
'</a></li>\n        <li><a href="#badges" data-bypass="" data-toggle="tab">'+
( $.t('reward.badgesTab') )+
'</a></li>\n        <li><a href="#cash" data-bypass="" data-toggle="tab">'+
( $.t('reward.cashTab') )+
'</a></li>\n    </ul>\n\n    <!-- Tab panes -->\n    <div class="tab-content">\n        <div class="tab-pane fade in active" id="presentation">\n            '+
( $.t('reward.presentationIntro') )+
'\n        </div>\n        <div class="tab-pane fade" id="points">\n            <table class="table table-striped">\n                <thead>\n                    <tr>\n                        <th></th>\n                        <th>'+
( $.t('reward.pointsTableYourPhoto') )+
'</th>\n                        <th colspan="2">'+
( $.t('reward.pointsTableAnotherUserPhoto') )+
'</th>\n                    </tr>\n                    <tr>\n                        <th>'+
( $.t('reward.pointsTableTypeOfTag') )+
'</th>\n                        <th>'+
( $.t('reward.pointsTableYouWin') )+
'</th>\n                        <th>'+
( $.t('reward.pointsTableYouWin') )+
'</th>\n                        <th>'+
( $.t('reward.pointsTablePhotoOwnerWins') )+
'</th>\n                    </tr>\n                </thead>\n                <tbody>\n                    <tr>\n                        <td>'+
( $.t('reward.pointsTableProduct') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable10Pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable5pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable5pts') )+
'</td>\n                    </tr>\n                    <tr>\n                        <td>'+
( $.t('reward.pointsTableProductWithDetails') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable15pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable10Pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable5pts') )+
'</td>\n                    </tr>\n                    <tr>\n                        <td>'+
( $.t('reward.pointsTablePlace') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable10Pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable5pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable5pts') )+
'</td>\n                    </tr>\n                    <tr>\n                        <td>'+
( $.t('reward.pointsTablePerson') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable10Pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable5pts') )+
'</td>\n                        <td>'+
( $.t('reward.pointsTable5pts') )+
'</td>\n                    </tr>\n                </tbody>\n            </table>\n        </div>\n        <div class="tab-pane fade" id="badges">\n            <table class="table table-striped">\n                <thead>\n                <tr>\n                    <th>'+
( $.t('reward.badgesBadge') )+
'</th>\n                    <th>'+
( $.t('reward.badgesRanking') )+
'</th>\n                </tr>\n                </thead>\n                <tbody>\n                    <tr>\n                        <td><i class="icon icon-middle reward-gold"></i> <span class="icon-middle">'+
( $.t('brand.gold') )+
'</span></td>\n                        <td>'+
( $.t('reward.badgesTop10') )+
'</td>\n                    </tr>\n                    <tr>\n                        <td><i class="icon icon-middle reward-silver"></i> <span class="icon-middle">'+
( $.t('brand.silver') )+
'</span></td>\n                        <td>'+
( $.t('reward.badgesTop20') )+
'</td>\n                    </tr>\n                    <tr>\n                        <td><i class="icon icon-middle reward-bronze"></i> <span class="icon-middle">'+
( $.t('brand.bronze') )+
'</span></td>\n                        <td>'+
( $.t('reward.badgesTop30') )+
'</td>\n                    </tr>\n                </tbody>\n            </table>\n        </div>\n        <div class="tab-pane fade" id="cash">\n            <p>'+
( $.t('reward.cashSoon') )+
'</p>\n        </div>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/reward/user.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="user-reward col-sm-4 col-md-3 col-lg-2 text-center">\n    <div class="profile-picture-wrapper">\n        <a href="'+
( model.get('ownerModel').get('link') )+
'"><img src="'+
( model.get('ownerModel').get('largeProfilePicture') )+
'" alt="'+
( model.get('ownerModel').get('fullname') )+
'" class="profile-picture" /></a>\n    </div>\n    <h3>'+
( model.get('ownerModel').get('fullname') )+
'</h3>\n    ';
 if (currentUserId != model.get('ownerModel').get('id')) { 
;__p+='\n    <div class="follow-button"></div>\n    ';
 } 
;__p+='\n</div>';
}
return __p;
};

this["JST"]["app/templates/reward/userItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get('ownerModel').get('link') )+
'">\n    <div class="pull-left reward-icon">\n        <div class="reward-'+
( model.get('type') )+
'"></div>\n    </div>\n    <div class="pull-left user-info">\n        <img src="'+
( model.get('ownerModel').get('profilePicture') )+
'" alt="'+
( model.get('ownerModel').get('fullname') )+
'" class="profile-picture" />\n        <span>'+
( model.get('ownerModel').get('fullname') )+
'</span>\n    </div>\n    <div class="clearfix"></div>\n</a>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/reward/users.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="users-alert"></div>\n<div class="row users reward-users"></div>';
}
return __p;
};

this["JST"]["app/templates/search/feedItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get("link") )+
'" class="hashtag-box">#'+
( model.get('name') )+
'</a>';
}
return __p;
};

this["JST"]["app/templates/search/filters.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div>\n    <ul class="first-filters list-unstyled">\n        <li><button class="mostRecent-filter"><i class="clock-icon icon"></i> '+
( $.t('filter.mostRecentToOldest') )+
'</button></li>\n        <li><button class="mostOldest-filter"><i class="clock-icon icon"></i> '+
( $.t('filter.mostOldestToRecent') )+
'</button></li>\n        <li><button class="mostLiked-filter"><i class="heart-icon icon"></i> '+
( $.t('filter.mostLiked') )+
'</button></li>\n        <li><button class="taggedPhotos-filter"><i class="grey-tagged-icon icon"></i> '+
( $.t('filter.taggedPhotos') )+
'</button></li>\n    </ul>\n</div>';
}
return __p;
};

this["JST"]["app/templates/search/fullFeedItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
}
return __p;
};

this["JST"]["app/templates/search/fullUserItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get("link") )+
'">\n    <img src="'+
( model.get("profilePicture") )+
'" class="profile-picture" />\n    <span class="user-fullname">'+
( model.get('fullname') )+
'</span>\n</a>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/search/fullitem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='-';
 if (model.get('type') == 'product') { 
;__p+='\n<a href="photo/'+
( model.get('photo')['id'] )+
'/"><img src="'+
( model.get("photo")["small_url"] )+
'" class="mh150 p5 pull-left" alt="'+
( model.get("photo")["caption"] )+
'" /></a> <a class="lead" href="photo/'+
( model.get('photo')['id'] )+
'/">'+
( model.get('title') )+
'</a>\n';
 } else if (model.get('type') == 'person') { 
;__p+='\n<a href="photo/'+
( model.get('photo')['id'] )+
'/"><img src="'+
( model.get("photo")["small_url"] )+
'" class="mh150 p5 pull-left" alt="'+
( model.get("photo")["caption"] )+
'" /></a> <a class="lead" href="photo/'+
( model.get('photo')['id'] )+
'/">'+
( model.get('title') )+
'</a>\n';
 } else { 
;__p+='\n<a href="photo/'+
( model.get('photo')['id'] )+
'/"><img src="'+
( model.get("photo")["small_url"] )+
'" class="mh150 p5 pull-left" alt="'+
( model.get("photo")["caption"] )+
'" /></a> <a class="lead" href="photo/'+
( model.get('photo')['id'] )+
'/">'+
( model.get('title') )+
'</a>\n';
 } 
;__p+='\n<div>\n    <div class="pull-right">\n        <i class="icon-heart"></i> '+
( model.get("photo")["likes_count"] )+
' <i class="icon-tag"></i> '+
( model.get("photo")["tags_count"] )+
'\n    </div>\n    Par <a href="'+
( model.get("profileLink") )+
'">'+
( model.get("fullname") )+
'</a>\n</div>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/search/fulllist.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>\n<h1 class="text-center search-infos">'+
( terms ? terms : $.t('search.noSearch') )+
' <button class="search-button"></button></h1>\n<div class="results-count text-center" data-i18n="[html]search.resultsCount" data-i18n-options=\'{"count": '+
( resultsCount )+
'}\'></div>\n<div class="photos-container results-container">\n    <div class="search-title" data-i18n="search.photos"></div>\n    <div class="alert-search-tags-results"></div>\n    <div class="search-filters"></div>\n    <div class="search-photos-results"></div>\n</div>\n<div class="users-container results-container fade-out">\n    <div class="search-title" data-i18n="search.users"></div>\n    <div class="alert-search-users-results"></div>\n    <ul class="search-users-results list-unstyled"></ul>\n</div>\n<div class="feeds-container results-container fade-out">\n    <div class="search-title" data-i18n="search.feeds"></div>\n    <div class="alert-search-feeds-results"></div>\n    <div class="search-feeds-results"></div>\n</div>\n<div class="brands-container results-container fade-out">\n    <div class="search-title" data-i18n="search.brands"></div>\n    <div class="alert-search-brands-results"></div>\n    <ul id="brands" class="search-brands-results row list-unstyled"></ul>\n</div>';
}
return __p;
};

this["JST"]["app/templates/search/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get('link') )+
'" class="item-link" data-bypass="" data-photo-link="">\n    <div style="background-image: url(\''+
( model.get('small_url') )+
'\')" class="photo pull-left"></div>\n    <div class="photo-details">\n        '+
( model.get('caption') )+
'\n    </div>\n</a>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/search/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="text-center search-loading fade-out"><span data-i18n="search.inProgress"></span> <i class="glyphicon glyphicon-refresh rotate"></i></div>\n<div class="alert-search-results"></div>\n<div class="photos-container">\n    <div class="search-title">'+
( $.t('search.photos') )+
'</div>\n    <div class="alert-search-tags-results"></div>\n    <ul class="search-tags-results list-unstyled striped"></ul>\n</div>\n<div class="users-container">\n    <div class="search-title">'+
( $.t('search.users') )+
'</div>\n    <div class="alert-search-users-results"></div>\n    <ul class="search-users-results list-unstyled striped"></ul>\n</div>\n<div class="feeds-container">\n    <div class="search-title">'+
( $.t('search.feeds') )+
'</div>\n    <div class="alert-search-feeds-results"></div>\n    <ul class="search-feeds-results list-unstyled striped"></ul>\n</div>\n<div class="brands-container">\n    <div class="search-title">'+
( $.t('search.brands') )+
'</div>\n    <div class="alert-search-brands-results"></div>\n    <ul class="search-brands-results brands list-unstyled striped"></ul>\n</div>\n<div class="clearfix"></div>\n<div class="view-more-results fade-out">\n    <a href="'+
( searchUrl )+
'">\n        <span data-i18n="search.viewMore" class="pull-left"></span>\n        <div class="pull-right red-cross-right"></div>\n    </a>\n</div>';
}
return __p;
};

this["JST"]["app/templates/search/searchBar.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="search-bar">\n    <form class="navbar-form navbar-left form-search" role="search">\n        <input type="text" class="search-input search-query" data-i18n="[placeholder]search.placeHolderKeywords" value="'+
( terms )+
'">\n        <button type="submit" class="sprites search-icon"></button>\n    </form>\n    <div class="dropdown-menu fade-out">\n        <div class="white-arrow-top"></div>\n        <div class="search-results-container">\n            <div class="text-center search-loading"><span data-i18n="search.inProgress"></span> <i class="icon-refresh rotate"></i></div>\n        </div>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/search/userItem.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get("link") )+
'">\n    <img src="'+
( model.get("profilePicture") )+
'" class="profile-picture" />\n    <span class="user-fullname">'+
( model.get('fullname') )+
'</span>\n</a>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/tag/addForm.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<form class="add-tag-form">\n    <fieldset>\n        <legend class="pacifico-adentify"><i class="adentify-flat-pastille icon"></i> '+
( $.t('tag.legendAddTag') )+
'</legend>\n        <div class="alert-container"></div>\n        <div class="tag-text">\n            <ol class="add-tag-info">\n                <li><span>'+
( $.t('tag.addTagInfoStep1') )+
'</span></li>\n                <li><span>'+
( $.t('tag.addTagInfoStep2') )+
'</span></li>\n                <li><span>'+
( $.t('tag.addTagInfoStep3') )+
'</span></li>\n            </ol>\n        </div>\n        <div class="tag-tabs fade-out">\n            <ul class="nav nav-tabs">\n                <li class="active"><a href="#product" data-toggle="tab" data-bypass="" data-i18n="tag.product"></a></li>\n                <li><a href="#venue" data-toggle="tab" data-bypass="" data-i18n="tag.place"></a></li>\n                <li><a href="#person" data-toggle="tab" data-bypass="" data-i18n="tag.person"></a></li>\n                ';
 if (showAdvertisingTag) { 
;__p+='\n                <li><a href="#advertising" data-toggle="tab" data-bypass="" data-i18n="tag.advertising"></a></li>\n                ';
 } 
;__p+='\n            </ul>\n            <div class="tab-content">\n                <!-- Produit -->\n                <div class="tab-pane active" id="product">\n                    <div class="alert-product"></div>\n\n                    <div class="form-group">\n                        <label><span data-i18n="tag.labelBrandName"></span> <i class="glyphicon glyphicon-refresh fade-out rotate" id="loading-brand"></i></label>\n                        <input class="form-control" id="brand-name" name="brand-name" type="text" data-i18n="[placeholder]tag.placeholderBrandName">\n                    </div>\n                    <div id="brand-logo" class="text-center"></div>\n\n                    <div class="form-group">\n                        <label><span data-i18n="tag.labelProductName"></span> <i class="glyphicon glyphicon-refresh fade-out rotate" id="loading-product"></i></label>\n                        <input class="form-control" id="product-name" name="product-text" type="text" data-i18n="[placeholder]tag.placeholderProductName">\n                    </div>\n\n                    <p class="text-center">\n                        <label data-i18n="tag.tellUsMore"></label>\n                        <button class="btn-around-corner btn-dark-grey more-details-button" data-bypass="" data-toggle="collapse" data-target=".more-product-details"><i class="glyphicon glyphicon-plus-sign"></i> <span>'+
( $.t('tag.placeMoreDetails') )+
'</span></button>\n                    </p>\n\n                    <div class="more-product-details collapse">\n                        <span class="btn btn-success fileinput-button">\n                            <i class="glyphicon glyphicon-plus"></i>\n                            <span data-i18n="tag.productImageUploadLabel"></span>\n                            <!-- The file input field used as target for the file upload widget -->\n                            <input id="fileupload" type="file" name="files[]" accept="image/jpg, image/gif, image/png">\n                        </span>\n                        <div id="product-image" class="text-center" style="display: inline"></div>\n                        <div class="form-group">\n                            <label data-i18n="tag.labelProductPurchaseUrl"></label>\n                            <input class="form-control" id="product-purchase-url" name="product-purchase-url" type="text" data-i18n="[placeholder]tag.placeholderProductPurchaseUrl">\n                        </div>\n                        <div class="form-group">\n                            <label data-i18n="tag.labelProductDescription"></label>\n                            <textarea class="form-control" id="product-description" name="product-description" ></textarea>\n                        </div>\n                        <label data-i18n="tag.productBuyPlace"></label>\n                        <p class="support-geolocation">\n                            <span><small data-i18n="tag.textGeolocation"></small></span><br>\n                            <button class="btn-around-corner btn-dark-grey btn-geolocation" data-i18n="[data-loading-text]tag.loadingGeolocalization"><i class="glyphicon glyphicon-map-marker"></i> <span data-i18n="tag.useGeolocation"></span></button>\n                        </p>\n                        <p class="fade-out not-support-geolocation" data-i18n="tag.noSupportGeolocation"></p>\n                        <div class="form-group">\n                            <label><span data-i18n="tag.labelPlaceName"></span> <i class="glyphicon glyphicon-refresh fade-out rotate" id="product-loading-venue"></i></label>\n                            <input class="form-control" id="product-venue-name" autocomplete="off" data-provide="typeahead" type="text" data-i18n="[placeholder]tag.placeholderProductPlaceName">\n                            <p class="help-block"><img src="https://playfoursquare.s3.amazonaws.com/press/logo/poweredByFoursquare_16x16.png" /> '+
( $.t('tag.poweredByFoursquare') )+
'</p>\n                        </div>\n                        <div id="product-previsualisation-tag-map"></div>\n                        <div id="product-venue-informations"></div>\n                    </div>\n                    <p><button id="submit-product" data-i18n="[data-loading-text]tag.addingInProgress" type="submit" class="btn-around-corner btn-red-grey-border submitTagButton"><i class="glyphicon glyphicon-ok"></i> <span data-i18n="tag.submitProduct"></span></button>\n                        <button class="btn btn-link cancel-add-tag"><span data-i18n="common.cancel"></span></button>\n                    </p>\n                </div>\n                <!-- Lieu -->\n                <div class="tab-pane" id="venue">\n                    <div class="alert-venue"></div>\n                    <div class="support-geolocation">\n                        <p><small data-i18n="tag.textGeolocation"></small></p>\n                        <p class="text-center">\n                            <button class="btn-around-corner btn-dark-grey btn-geolocation" data-i18n="[data-loading-text]tag.loadingGeolocalization"><i class="glyphicon glyphicon-map-marker"></i> '+
( $.t('tag.useGeolocation') )+
'</button>\n                        </p>\n                    </div>\n                    <p class="fade-out not-support-geolocation" data-i18n="tag.noSupportGeolocation"></p>\n                    <div class="form-group">\n                        <label><span data-i18n="tag.labelPlaceName"></span> <i class="glyphicon glyphicon-refresh fade-out rotate" id="loading-venue"></i></label>\n                        <input class="form-control" id="venue-name" autocomplete="off" data-provide="typeahead" type="text" data-i18n="[placeholder]tag.placeholderPlaceName">\n                        <p class="help-block"><img src="https://playfoursquare.s3.amazonaws.com/press/logo/poweredByFoursquare_16x16.png" /> '+
( $.t('tag.poweredByFoursquare') )+
'</p>\n                    </div>\n                    <div id="previsualisation-tag-map"></div>\n                    <div id="venue-informations"></div>\n                    <div class="form-group">\n                        <label data-i18n="tag.labelPlaceDescription"></label>\n                        <textarea class="form-control" id="venue-description"></textarea>\n                    </div>\n                    <div class="form-group">\n                        <label data-i18n="tag.labelPlaceLink"></label>\n                        <input class="form-control" data-i18n="[placeholder]tag.placeLinkPlaceholder" id="venue-link" type="text">\n                    </div>\n                    <p><button id="submit-venue" data-i18n="[data-loading-text]tag.addingInProgress" type="submit" class="btn-around-corner btn-red-grey-border submitTagButton"><i class="glyphicon glyphicon-ok"></i> <span data-i18n="tag.submitPlace"></span></button>\n                        <button class="btn btn btn-link cancel-add-tag"><span data-i18n="common.cancel"></span></button></p>\n                </div>\n                <!-- Personne -->\n                <div class="tab-pane" id="person">\n                    <div class="alert-person"></div>\n                    <div class="fb-loggedin">\n                        <div class="form-group">\n                            <label><span data-i18n="tag.labelPersonName"></span> <i class="glyphicon glyphicon-refresh fade-out rotate" id="loading-person"></i></label>\n                            <input class="form-control" id="person-text" autocomplete="off" data-provide="typeahead" type="text" data-i18n="[placeholder]tag.placeholderPersonName">\n                        </div>\n                        <div class="form-group">\n                            <label data-i18n="tag.labelPersonLink"></label>\n                            <input class="form-control" data-i18n="[placeholder]tag.personLinkPlaceholder" id="person-link" type="text">\n                        </div>\n                        <p><button id="submit-person" data-i18n="[data-loading-text]tag.addingInProgress" type="submit" class="btn-around-corner btn-red-grey-border submitTagButton"><i class="glyphicon glyphicon-ok"></i> <span data-i18n="tag.submitPerson"></span></button>\n                            <button class="btn btn-link cancel-add-tag"><span data-i18n="common.cancel"></span></button></p>\n                    </div>\n                    <div class="fb-loggedout alert alert-danger fade-out" data-i18n="[html]tag.textNoFacebookConnect"></div>\n                </div>\n                <!-- Publicité -->\n                <div class="tab-pane" id="advertising">\n                    <div class="alert-advertising"></div>\n\n                    <div class="form-group">\n                        <label data-i18n="tag.labelAdvertisingFormats"></label>\n                        <select id="ad-formats" class="form-control">\n                            <option value="300-250">300x250</option>\n                            <option value="320-100">320x100</option>\n                            <option value="custom">'+
( $.t('tag.advertisingCustomFormat') )+
'</option>\n                        </select>\n                    </div>\n                    <div class="form-group">\n                        <div class="row">\n                            <div class="col-xs-6">\n                                <label data-i18n="tag.labelAdvertisingWidth"></label>\n                                <input class="form-control" type="text" id="ad-width" value="300">\n                            </div>\n                            <div class="col-xs-6">\n                                <label data-i18n="tag.labelAdvertisingHeight"></label>\n                                <input class="form-control" type="text" id="ad-height" value="250">\n                            </div>\n                        </div>\n                    </div>\n                    <div class="form-group">\n                        <label data-i18n="tag.labelAdvertisingCode"></label>\n                        <textarea class="form-control" id="ad-code"></textarea>\n                    </div>\n                    <p><button id="submit-advertising" data-i18n="[data-loading-text]tag.addingInProgress" type="submit" class="btn-around-corner btn-red-grey-border submitTagButton"><i class="glyphicon glyphicon-ok"></i> <span data-i18n="tag.submitAdvertising"></span></button>\n                        <button class="btn btn-link cancel-add-tag"><span data-i18n="common.cancel"></span></button></p>\n                </div>\n            </div>\n        </div>\n\n    </fieldset>\n</form>';
}
return __p;
};

this["JST"]["app/templates/tag/addModal.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="edit-modal-row row">\n    <div id="center-modal" class="col-sm-8 col-md-8 no-padding">\n        <div id="center-modal-content">\n            <div class="loading-gif-container">\n                <div class="loader rotate"></div>\n            </div>\n        </div>\n    </div>\n    <div id="right-modal" class="col-sm-4 col-md-4 no-padding">\n        <div id="right-modal-content"></div>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/tag/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<ul class="tags';
 if (!visible) { 
;__p+=' fade-out';
 } 
;__p+='" data-always-visible="'+
( visible ? 'yes' : 'no' )+
'"></ul>';
}
return __p;
};

this["JST"]["app/templates/tag/menuTools.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="tag-text">\n    <p data-i18n="tag.textAddTagToPhoto"></p>\n    <p><button class="btn btn-danger cancel-add-tag"><i class="icon-remove icon-white"></i> <span data-i18n="tag.cancelTagAdding"></span></button></p>\n</div>\n<div class="tag-form fade-out"></div>';
}
return __p;
};

this["JST"]["app/templates/tag/report.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="modal-body-wrapper">\n    <form class="form-horizontal mt2" role="form">\n        <fieldset>\n            <div class="form-group">\n                <label data-i18n="tag.reportReasonLabel" class="col-sm-4 control-label"></label>\n                <div class="col-sm-8">\n                    <textarea class="form-control reason-textarea" data-i18n="[placeholder]tag.reportReasonTextarea"></textarea>\n                </div>\n            </div>\n\n            <div class="form-group">\n                <div class="col-md-8 col-md-offset-4">\n                    <button type="button" class="btn btn-link" data-dismiss="modal">'+
( $.t('common.cancel') )+
'</button>\n                    <input type="submit" class="btn-around-corner btn-red-grey-border reportSubmit" value="'+
( $.t('common.report') )+
'" />\n                </div>\n            </div>\n        </fieldset>\n    </form>\n</div>';
}
return __p;
};

this["JST"]["app/templates/tag/types/advertising.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model) { 
;__p+='\n<div class="tag advertising-tag-container '+
( model.get("cssClass") )+
'" style="'+
( model.get('cssStyle') )+
'">\n<div class="advertising-icon tag-icon"></div>\n';
 if (!popoverDesactivated) { 
;__p+='\n<div class="popover">\n    <div class="tag-popover-arrow"></div>\n    <div class="popover-inner" style="'+
( model.get('popoverInnerStyle') )+
'">\n        '+
( model.get('tag_info').info.code )+
'\n    </div>\n    <div class="clearfix"></div>\n    <div class="tag-buttons">\n        ';
 if (model.has('ownerModel')) { 
;__p+='\n        <span class="tagged-by">'+
( $.t('tag.taggedBy', { 'author': model.get('ownerModel').get('fullname'), 'link': model.get('ownerModel').get('link') }) )+
'</span>\n        ';
 } 
;__p+='\n        <div class="btn-group">\n            ';
 if (model.isWaitingValidation()) { 
;__p+='\n            <button type="button" class="btn btn-black btn-sm validateTagButton" data-i18n="tag.validateTag"></button>\n            <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                <span class="caret"></span>\n                <span class="sr-only">Toggle Dropdown</span>\n            </button>\n            <ul class="dropdown-menu" role="menu">\n                <li><button class="btn btn-link refuseTagButton" data-i18n="tag.refuseTag"></button></li>\n                <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n            </ul>\n            ';
 } else { 
;__p+='\n            <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                '+
( $.t('tag.actions') )+
' <span class="caret"></span>\n            </button>\n            <ul class="dropdown-menu" role="menu">\n                ';
 if (model.isOwner()) { 
;__p+='\n                <li><button class="btn btn-link deleteTagButton" data-i18n="tag.deleteTag"></button></li>\n                ';
 } 
;__p+='\n                <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n            </ul>\n            ';
 } 
;__p+='\n        </div>\n    </div>\n</div>\n';
 } 
;__p+='\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/tag/types/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model) { 
;__p+='\n<div class="tag '+
( model.get("cssClass") )+
'" style="'+
( model.get('cssStyle') )+
'">\n    <div class="tag-text-icon tag-icon"></div>\n    <div class="popover">\n        <div class="tag-popover-arrow"></div>\n        <div class="tag-buttons">\n            ';
 if (model.isOwner()) { 
;__p+='\n            <i class="glyphicon glyphicon-remove deleteTagButton"></i>\n            ';
 } 
;__p+='\n        </div>\n        <span class="title"><a href="'+
( model.get("link") )+
'" target="_blank">'+
( model.get("title") )+
'</a></span>\n        ';
 if (model.has('description') && model.get("description")) { 
;__p+='\n        <p>'+
( model.get("description") )+
'</p>\n        ';
 } 
;__p+='\n        <div class="tag-buttons">\n            ';
 if (model.has('ownerModel')) { 
;__p+='\n            <span class="tagged-by">'+
( $.t('tag.taggedBy', { 'author': model.get('ownerModel').get('fullname'), 'link': model.get('ownerModel').get('link') }) )+
'</span>\n            ';
 } 
;__p+='\n            <div class="btn-group">\n                ';
 if (model.isWaitingValidation()) { 
;__p+='\n                <button type="button" class="btn btn-black btn-sm validateTagButton" data-i18n="tag.validateTag"></button>\n                <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                    <span class="caret"></span>\n                    <span class="sr-only">Toggle Dropdown</span>\n                </button>\n                <ul class="dropdown-menu" role="menu">\n                    <li><button class="btn btn-link refuseTagButton" data-i18n="tag.refuseTag"></button></li>\n                    <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                </ul>\n                ';
 } else { 
;__p+='\n                <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                    '+
( $.t('tag.actions') )+
' <span class="caret"></span>\n                </button>\n                <ul class="dropdown-menu" role="menu">\n                    ';
 if (model.isOwner()) { 
;__p+='\n                    <li><button class="btn btn-link deleteTagButton" data-i18n="tag.deleteTag"></button></li>\n                    ';
 } 
;__p+='\n                    <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                </ul>\n                ';
 } 
;__p+='\n            </div>\n        </div>\n    </div>\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/tag/types/newTag.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="tag '+
( model.get("cssClass") )+
'" style="'+
( model.get('cssStyle') )+
'">\n<div class="'+
( model.get('tagIcon') )+
' tag-icon"></div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/tag/types/person.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model) { 
;__p+='\n<div class="tag person-tag-container '+
( model.get("cssClass") )+
'" style="'+
( model.get('cssStyle') )+
'">\n    <div class="tag-user-icon tag-icon"></div>\n    ';
 if (!popoverDesactivated) { 
;__p+='\n        <div class="popover">\n            <div class="tag-popover-arrow"></div>\n            <div class="popover-inner">\n                ';
 if (model.has('link') || model.get('person').has('link')) { 
;__p+='\n                <a class="person-picture" href="'+
( model.has('link') ? model.get("link") : model.get('person').get('link') )+
'" target="_blank"><img src="'+
( model.get('personModel').get('largeProfilePicture') )+
'" /></a>\n                <span class="title"><a href="'+
( model.has('link') ? model.get("link") : model.get('person').get('link') )+
'" target="_blank">'+
( model.get("title") )+
'</a></span>\n                ';
 } else { 
;__p+='\n                <div class="person-picture"><img src="'+
( model.get('personModel').get('largeProfilePicture') )+
'" /></div>\n                <span class="title">'+
( model.get("title") )+
'</span>\n                ';
 } 
;__p+='\n                <div class="clearfix"></div>\n            </div>\n            <div class="tag-buttons">\n                ';
 if (model.has('ownerModel')) { 
;__p+='\n                <span class="tagged-by">'+
( $.t('tag.taggedBy', { 'author': model.get('ownerModel').get('fullname'), 'link': model.get('ownerModel').get('link') }) )+
'</span>\n                ';
 } 
;__p+='\n                <div class="btn-group">\n                    ';
 if (model.isWaitingValidation()) { 
;__p+='\n                    <button type="button" class="btn btn-black btn-sm validateTagButton" data-i18n="tag.validateTag"></button>\n                    <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                        <span class="caret"></span>\n                        <span class="sr-only">Toggle Dropdown</span>\n                    </button>\n                    <ul class="dropdown-menu" role="menu">\n                        <li><button class="btn btn-link refuseTagButton" data-i18n="tag.refuseTag"></button></li>\n                        <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                    </ul>\n                    ';
 } else { 
;__p+='\n                    <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                        '+
( $.t('tag.actions') )+
' <span class="caret"></span>\n                    </button>\n                    <ul class="dropdown-menu" role="menu">\n                        ';
 if (model.isOwner()) { 
;__p+='\n                        <li><button class="btn btn-link deleteTagButton" data-i18n="tag.deleteTag"></button></li>\n                        ';
 } 
;__p+='\n                        <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                    </ul>\n                    ';
 } 
;__p+='\n                </div>\n            </div>\n        </div>\n    ';
 } 
;__p+='\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/tag/types/product.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model) { 
;__p+='\n<div class="tag product-tag-container '+
( model.get("cssClass") )+
'" style="'+
( model.get('cssStyle') )+
'">\n    <div class="glyphicon glyphicon-tag tag-icon"></div>\n    ';
 if (!popoverDesactivated) { 
;__p+='\n        <div class="popover">\n            <div class="tag-popover-arrow"></div>\n            <div class="popover-inner">\n\n                ';
 if (model.get('link')) { 
;__p+='\n                <span class="title"><a href="'+
( model.get("link") )+
'" target="_blank">'+
( model.get("title") )+
'</a></span>\n                ';
 } else { 
;__p+='\n                <span class="title">'+
( model.get("title") )+
'</span>\n                ';
 } 
;__p+='\n\n                ';
 if (model.has("product") && model.get("product").get("small_url")) { 
;__p+='\n                <div class="pull-left">\n                    ';
 if (model.get('link')) { 
;__p+='<a href="'+
( model.get("link") )+
'" target="_blank">';
 } 
;__p+='\n                    <img src="'+
( model.get("product").get("small_url") )+
'" class="product-photo" />\n                    ';
 if (model.get('link')) { 
;__p+='</a>';
 } 
;__p+='\n                </div>\n                ';
 } 
;__p+='\n\n                ';
 if (model.has('description') && model.get("description")) { 
;__p+='\n                <p class="tag-description">'+
( model.get("description") )+
'</p>\n                ';
 } else if (model.has('product') && model.get('product').has('description') && model.get('product').get('description')) { 
;__p+='\n                <p class="tag-description">'+
( model.get('product').get('description') )+
'</p>\n                ';
 } 
;__p+='\n\n                ';
 if (model.has("brandModel") && model.get('brandModel')) { 
;__p+='\n                <div class="brand">\n                    <a href="'+
( model.get('brandModel').get('link') )+
'">\n                        ';
 if (!model.get('brandModel').has('small_logo_url')) { 
;__p+='\n                        '+
( model.get("brandModel").get('name') )+
'\n                        ';
 } else { 
;__p+='\n                        <img src="'+
( model.get("brandModel").get('small_logo_url') )+
'" alt="'+
( model.get("brandModel").get('name') )+
'" class="brand-logo" />\n                        ';
 } 
;__p+='\n                    </a>\n                </div>\n                ';
 } 
;__p+='\n\n            </div>\n            <div class="clearfix"></div>\n            ';
 if ((model.has('product') && model.get('product').get('purchase_url'))
                    || (model.has('product') && model.get('product').get('legal_notice'))
                    || (model.has("brandModel") && model.get("brandModel").get('legal_notice'))) { 
;__p+='\n            <div class="popover-details">\n                ';
 if (model.has('product') && model.get('product').get('purchase_url')) { 
;__p+='\n                <p>\n                    <a href="'+
( model.get('link') )+
'" target="_blank" class="more-info"><i class="icon-shopping-cart icon-white"></i> '+
( $.t('tag.moreInfo') )+
'</a>\n                </p>\n                <div class="row">\n                    <div class="col-xs-6 more-info-url-wrapper">\n                        <a href="'+
( model.get('link') )+
'" target="_blank" class="more-info-url"><i class="glyphicon glyphicon-globe icon"></i> '+
( model.get('link') )+
'</a>\n                    </div>\n                    <div class="col-xs-6">\n                        <div class="venue-adress-wrapper"></div>\n                    </div>\n                </div>\n                ';
 } 
;__p+='\n                ';
 if (model.has('product') && model.get('product').get('legal_notice')) { 
;__p+='\n                <div class="legal-notice"><small><em>'+
( $.t('legalNotice.' + model.get('product').get('legal_notice')) )+
'</em></small></div>\n                ';
 } 
;__p+='\n                ';
 if (model.has("brandModel") && model.get("brandModel").get('legal_notice')) { 
;__p+='\n                <div class="legal-notice"><small><em>'+
( $.t('legalNotice.' + model.get("brandModel").get('legal_notice')) )+
'</em></small></div>\n                ';
 } 
;__p+='\n            </div>\n            ';
 } 
;__p+='\n            <div class="tag-buttons">\n                ';
 if (model.has('ownerModel')) { 
;__p+='\n                <span class="tagged-by">'+
( $.t('tag.taggedBy', { 'author': model.get('ownerModel').get('fullname'), 'link': model.get('ownerModel').get('link') }) )+
'</span>\n                ';
 } 
;__p+='\n                <div class="btn-group">\n                    ';
 if (model.isWaitingValidation()) { 
;__p+='\n                    <button type="button" class="btn btn-black btn-sm validateTagButton" data-i18n="tag.validateTag"></button>\n                    <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                        <span class="caret"></span>\n                        <span class="sr-only">Toggle Dropdown</span>\n                    </button>\n                    <ul class="dropdown-menu" role="menu">\n                        <li><button class="btn btn-link refuseTagButton" data-i18n="tag.refuseTag"></button></li>\n                        <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                    </ul>\n                    ';
 } else { 
;__p+='\n                    <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                        '+
( $.t('tag.actions') )+
' <span class="caret"></span>\n                    </button>\n                    <ul class="dropdown-menu" role="menu">\n                        ';
 if (model.isOwner()) { 
;__p+='\n                        <li><button class="btn btn-link deleteTagButton" data-i18n="tag.deleteTag"></button></li>\n                        ';
 } 
;__p+='\n                        <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                    </ul>\n                    ';
 } 
;__p+='\n                </div>\n            </div>\n        </div>\n    ';
 } 
;__p+='\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/tag/types/venue.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model) { 
;__p+='\n<div class="tag venue-tag-container '+
( model.get("cssClass") )+
'" style="'+
( model.get('cssStyle') )+
'">\n    <div class="tag-place-icon tag-icon"></div>\n    ';
 if (!popoverDesactivated) { 
;__p+='\n        <div class="popover">\n            <div class="tag-popover-arrow"></div>\n            <div class="popover-inner">\n                <span class="title">';
 if (model.get("link")) { 
;__p+='<a href="'+
( model.get("link") )+
'" target="_blank">'+
( model.get("title") )+
'</a>';
 } else { 
;__p+=''+
( model.get("title") )+
'';
 } 
;__p+='</span>\n                ';
 if (model.has('description') && model.get("description")) { 
;__p+='\n                <p>'+
( model.get("description") )+
'</p>\n                ';
 } 
;__p+='\n            </div>\n            <div id="map'+
( model.get("id") )+
'"></div>\n            <div class="popover-details"></div>\n            <div class="tag-buttons">\n                ';
 if (model.has('ownerModel')) { 
;__p+='\n                <span class="tagged-by">'+
( $.t('tag.taggedBy', { 'author': model.get('ownerModel').get('fullname'), 'link': model.get('ownerModel').get('link') }) )+
'</span>\n                ';
 } 
;__p+='\n                <div class="btn-group">\n                    ';
 if (model.isWaitingValidation()) { 
;__p+='\n                    <button type="button" class="btn btn-black btn-sm validateTagButton" data-i18n="tag.validateTag"></button>\n                    <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                        <span class="caret"></span>\n                        <span class="sr-only">Toggle Dropdown</span>\n                    </button>\n                    <ul class="dropdown-menu" role="menu">\n                        <li><button class="btn btn-link refuseTagButton" data-i18n="tag.refuseTag"></button></li>\n                        <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                    </ul>\n                    ';
 } else { 
;__p+='\n                    <button type="button" class="btn btn-black btn-sm dropdown-toggle" data-toggle="dropdown">\n                        '+
( $.t('tag.actions') )+
' <span class="caret"></span>\n                    </button>\n                    <ul class="dropdown-menu" role="menu">\n                        ';
 if (model.isOwner()) { 
;__p+='\n                        <li><button class="btn btn-link deleteTagButton" data-i18n="tag.deleteTag"></button></li>\n                        ';
 } 
;__p+='\n                        <li><button class="btn btn-link reportTagButton" data-i18n="tag.reportTag"></button></li>\n                    </ul>\n                    ';
 } 
;__p+='\n                </div>\n            </div>\n        </div>\n    ';
 } 
;__p+='\n</div>\n';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/upload/content.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-grey-title-container">\n    <h1 data-i18n="upload.sendPhotos"></h1>\n    <div class="small-arrow-top"></div>\n</div>\n\n<p class="lead mt2 text-center" data-i18n="upload.textWhichService"></p>\n<div class="services-container"></div>';
}
return __p;
};

this["JST"]["app/templates/upload/localUpload.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-grey-title-container">\n    <h1 data-i18n="upload.sendPhotosFromLocal"></h1>\n</div>\n<div class="text-center mt3">\n    <span class="btn-around-corner btn-red-grey-border btn-large fileinput-button">\n        <i class="icon-plus icon-white"></i>\n        <span data-i18n="upload.browseLocalPhotos"></span>\n        <input id="fileupload" type="file" name="files[]" multiple="multiple" accept="image/*">\n    </span>\n</div>\n<div class="alert-upload"></div>\n<div class="upload-photos-container mt3"></div>';
}
return __p;
};

this["JST"]["app/templates/upload/serviceButton.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<button class="service-button '+
( model.get('service_name').toLowerCase() )+
'-service-button';
 if (model.get('linked')) { 
;__p+=' active';
 } 
;__p+='"><i class="'+
( model.get('service_name').toLowerCase() )+
'-circle-white-icon icon"></i> '+
( model.get('service_name') )+
'</button>';
}
return __p;
};

this["JST"]["app/templates/upload/serviceButtons.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<ul class="services list-unstyled">\n\n</ul>';
}
return __p;
};

this["JST"]["app/templates/upload/services.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<ul class="services list-unstyled">\n    <li><a href="'+
( rootUrl )+
''+
( $.t('routing.facebook/albums/') )+
'" class="service-button facebook-service-button active"><i class="facebook-circle-white-icon icon"></i> Facebook</a></li>\n    <li><a href="'+
( rootUrl )+
''+
( $.t('routing.instagram/photos/') )+
'" class="service-button instagram-service-button active"><i class="instagram-circle-white-icon icon"></i> Instagram</a></li>\n    <li><a href="'+
( rootUrl )+
''+
( $.t('routing.upload/local/') )+
'" class="service-button local-service-button active"><i class="local-circle-white-icon icon"></i> '+
( $.t('upload.myComputer') )+
'</a></li>\n</ul>';
}
return __p;
};

this["JST"]["app/templates/upload/uploadInProgress.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="modal-body-wrapper">\n    <div class="progress-bar-container"></div>\n    <p>'+
( $.t('upload.inProgress') )+
'</p>\n</div>';
}
return __p;
};

this["JST"]["app/templates/user/creditsDetail.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-grey-title-container">\n    <div class="back-to-link">\n        <a href="'+
( rootUrl )+
''+
( $.t('routing.my/dashboard/') )+
'">'+
( $.t('user.backToDashboard') )+
' <i class="glyphicon glyphicon-chevron-right"></i></a>\n    </div>\n    <h1>'+
( $.t('user.dashboardTitle') )+
'</h1>\n    <div class="small-arrow-top"></div>\n</div>\n\n<div class="dashboard">\n\n    <h2>'+
( $.t('user.detailsHeading', { date: moment(date).format('L')}) )+
'</h2>\n\n    <hr>\n\n    <table class="table table-condensed table-striped">\n        <thead>\n        <tr>\n            <th>#</th>\n            <th>'+
( $.t('user.tagType') )+
'</th>\n            <th>'+
( $.t('user.photo') )+
'</th>\n            <th>'+
( $.t('user.hour') )+
'</th>\n            <th>'+
( $.t('user.brand') )+
'</th>\n            <th>'+
( $.t('user.points') )+
'</th>\n            <th>'+
( $.t('user.cash') )+
'</th>\n        </tr>\n        </thead>\n        <tbody></tbody>\n    </table>\n    <div class="alert-credits"></div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/user/creditsDetailRow.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<td>'+
( index )+
'</td>\n<td>'+
( $.t('tag.' + model.get('type')) )+
'</td>\n<td><a href="'+
( model.get('photoLink') )+
'" data-bypass="" class="photo-link">';
 if (model.get('photo')) { 
;__p+=''+
( model.get('photo') )+
'';
 } else { 
;__p+=''+
( $.t('user.viewPhoto') )+
'';
 } 
;__p+='</a></td>\n<td>'+
( moment(model.get('date')).format('HH:mm') )+
'</td>\n';
 if (model.has('brandLink')) { 
;__p+='\n<td><a href="'+
( model.get('brandLink') )+
'">'+
( model.get('brand') )+
'</a></td>\n';
 } else { 
;__p+='\n<td>'+
( model.get('brand') )+
'</td>\n';
 } 
;__p+='\n<td>'+
( model.get('points') )+
'</td>\n<td>'+
( model.get('income') )+
'</td>';
}
return __p;
};

this["JST"]["app/templates/user/creditsRow.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<td>'+
( model.get('formatDate') )+
'</td>\n<td>'+
( model.get('points') )+
'</td>\n<td>';
 if (model.has('incomes')) { 
;__p+=''+
( model.get('incomes') )+
'';
 } 
;__p+='</td>\n<td><a href="'+
( model.get('link') )+
'">'+
( $.t('user.creditsDetails') )+
'</a></td>';
}
return __p;
};

this["JST"]["app/templates/user/creditsTable.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<table class="table table-condensed table-striped">\n    <thead>\n        <tr>\n            <th>'+
( $.t('user.dateHeading') )+
'</th>\n            <th>'+
( $.t('user.points') )+
'</th>\n            <th>'+
( $.t('user.cash') )+
'</th>\n            <th></th>\n        </tr>\n    </thead>\n    <tbody></tbody>\n</table>\n<div class="alert-credits"></div>';
}
return __p;
};

this["JST"]["app/templates/user/dashboard.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="top-grey-title-container">\n    <h1>'+
( $.t('user.dashboardTitle') )+
'</h1>\n    <div class="small-arrow-top"></div>\n</div>\n\n<div class="dashboard">\n\n    <div class="form-inline">\n        <div class="period-picker form-group">\n            <span>'+
( $.t('user.period') )+
'</span> <input type="text" class="form-control" name="daterange">\n        </div>\n    </div>\n\n    <hr>\n\n    <div class="row">\n        <div class="col-md-7">\n            <h2>'+
( $.t('user.credits') )+
' <i class="icon curve-arrow-left"></i> <span class="user-points-red"></span> <i class="glyphicon glyphicon-question-sign pointer rewards-hiw-link"></i></h2>\n            <div class="user-credits-table"></div>\n        </div>\n        <div class="col-md-5">\n            <h2>'+
( $.t('user.photos') )+
'</h2>\n            <canvas class="userPhotosChart" width="300" height="150"></canvas>\n            ';
 if (rawData) { 
;__p+='\n            <div class="chart-legend">\n                <div class="legend-columns">\n                    <div class="legend-column">\n                        <span class="puce legend-grey">•</span> <label>'+
( $.t('user.labelUnTaggedPhotos', { 'count': parseInt(rawData.untaggedPhotos) }) )+
'</label>\n                    </div>\n                    <div class="legend-column">\n                        <span class="puce legend-red">•</span> <label>'+
( $.t('user.labelTaggedPhotos', { 'count': parseInt(rawData.taggedPhotos) }) )+
'</label>\n                    </div>\n                </div>\n            </div>\n            ';
 } 
;__p+='\n        </div>\n    </div>\n\n    <div class="my-history">\n        <h2 class="border">'+
( $.t('user.history') )+
'</h2>\n        <div class="my-history-content actions-white"></div>\n    </div>\n</div>';
}
return __p;
};

this["JST"]["app/templates/user/followButton.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<button data-toggle="button" type="button" class="btn-large btn-around-corner btn-grey-white-border follow-button"\n        data-followed="'+
( follow )+
'" data-loading-text="'+
( $.t('common.waiting') )+
'">'+
( follow ? $.t("profile.followed") : $.t("profile.follow") )+
'</button>';
}
return __p;
};

this["JST"]["app/templates/user/item.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<a href="'+
( model.get('link') )+
'">\n    <div class="pull-left">\n        <img src="'+
( model.get('profilePicture') )+
'" alt="'+
( model.get('fullname') )+
'" class="profile-picture" /> <div class="user-item-name"><span>'+
( model.get('fullname') )+
'</span></div>\n    </div>\n    <div class="pull-right more-profile"></div>\n    <div class="clearfix"></div>\n</a>\n<div class="clearfix"></div>';
}
return __p;
};

this["JST"]["app/templates/user/list.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="users-alert"></div>\n<div class="block-white-background">\n    <ul class="users list-unstyled striped"></ul>\n    ';
 if (showAllButton) { 
;__p+='\n    <a data-bypass="" class="viewMore link-grey-whith-red-arrow">'+
( moreMessage )+
'</a>\n    ';
 } 
;__p+='\n</div>';
}
return __p;
};

this["JST"]["app/templates/user/menuLeft.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='<div class="loading-gif-container">\n    <div class="loader rotate"></div>\n</div>\n<aside class="profile-aside fade-out">\n    <div class="block-white-background">\n        <div class="profile-details text-center">\n            ';
 if (lastPhoto) { 
;__p+='\n            <div class="last-photo" style="background-image: url(\''+
( lastPhoto.get('medium_url') )+
'\');"></div>\n            <div class="last-photo-spacer"></div>\n            ';
 } 
;__p+='\n\n            <div class="profile-picture-wrapper">\n                <div class="table-center">\n                    <div class="table-center-cell">\n                        <a href="'+
( model.get('link') )+
'"><img src="'+
( model.get('largeProfilePicture') )+
'" alt="'+
( model.get('fullname') )+
'" class="profile-picture" /></a>\n                    </div>\n                </div>\n            </div>\n            <h1>'+
( model.get('fullname') )+
'</h1>\n            ';
 if (showFollowButton) { 
;__p+='\n                ';
 if (currentUserId != model.get('id')) { 
;__p+='\n                <div class="follow-button"></div>\n                ';
 } else { 
;__p+='\n                <a href="'+
( rootUrl )+
''+
( $.t('routing.my/settings/') )+
'" class="btn btn-large btn-around-corner btn-grey-white-border">'+
( $.t('profile.editMyProfile') )+
'</a>\n                ';
 } 
;__p+='\n            ';
 } 
;__p+='\n\n            <div class="indicators-container text-center">\n                ';
 if (currentUserId === model.get('id')) { 
;__p+='\n                <a href="'+
( rootUrl )+
''+
( $.t('routing.my/photos/') )+
'">\n                ';
 } 
;__p+='\n                <div class="row">\n                    <div class="col-xs-6">\n                        <div class="separator-sm-vertical"></div>\n                        <div class="indicator-value">'+
( model.get("photos_count") )+
'</div>\n                        <div class="indicator-label">'+
( $.t('profile.photosIndicator') )+
'</div>\n                    </div>\n                    <div class="col-xs-6">\n                        <div class="indicator-value">'+
( model.get("tags_count") )+
'</div>\n                        <div class="indicator-label">'+
( $.t('profile.tagsIndicator') )+
'</div>\n                    </div>\n                </div>\n                ';
 if (currentUserId === model.get('id')) { 
;__p+='\n                </a>\n                ';
 } 
;__p+='\n                <div class="row">\n                    <div class="col-xs-6 followings-link">\n                        <div class="separator-sm-horizontal"></div>\n                        <div class="separator-sm-vertical"></div>\n                        <div class="indicator-value">'+
( model.get("followings_count") )+
'</div>\n                        <div class="indicator-label">'+
( $.t('profile.followingIndicator') )+
'</div>\n                    </div>\n                    <div class="col-xs-6 followers-link">\n                        <div class="separator-sm-horizontal"></div>\n                        <div class="indicator-value">'+
( model.get("followers_count") )+
'</div>\n                        <div class="indicator-label">'+
( $.t('profile.followersIndicator') )+
'</div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n    <div class="block-white-bottom"></div>\n\n    <hr class="menu-left-hr">\n\n    ';
 if (showRewards) { 
;__p+='\n    <h2>'+
( $.t('profile.rewards') )+
' <i class="glyphicon glyphicon-question-sign pointer rewards-hiw-link"></i></h2>\n    <div class="rewards"></div>\n    <hr class="menu-left-hr">\n    ';
 } 
;__p+='\n\n    ';
 if (showHashtags) { 
;__p+='\n    <h2>'+
( $.t('profile.hashtagsTitle') )+
'</h2>\n    <div class="hashtags"></div>\n    <hr class="menu-left-hr">\n    ';
 } 
;__p+='\n\n    ';
 if (showFollowings) { 
;__p+='\n    <h2>'+
( $.t(currentUserId != model.get('id') ? 'profile.followingsTitle' : 'profile.myFollowingsTitle') )+
'</h2>\n    <div class="followings"></div>\n    <hr class="menu-left-hr">\n    ';
 } 
;__p+='\n\n    ';
 if (showFollowers) { 
;__p+='\n    <h2>'+
( $.t(currentUserId != model.get('id') ? 'profile.followersTitle' : 'profile.myFollowersTitle') )+
'</h2>\n    <div class="followers"></div>\n    <hr class="menu-left-hr">\n    ';
 } 
;__p+='\n\n    ';
 if (showBrands) { 
;__p+='\n    <h2>'+
( $.t(currentUserId != model.get('id') ? 'profile.brandsTitle' : 'profile.myBrandsTitle') )+
'</h2>\n    <div class="brands"></div>\n    ';
 } 
;__p+='\n\n    ';
 if (showServices) { 
;__p+='\n    <h2>'+
( $.t('profile.myLinkedServices') )+
'</h2>\n    <div class="block-white-background">\n        <div class="services"></div>\n    </div>\n    ';
 } 
;__p+='\n</aside>';
}
return __p;
};

this["JST"]["app/templates/user/points.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (points != null) { 
;__p+='<div';
 if (animate) { 
;__p+=' class="animated flash"';
 } 
;__p+='><span class="user-points-value">'+
( points )+
'</span> <sup>Points</sup></div>';
 } 
;__p+='';
}
return __p;
};

this["JST"]["app/templates/venue/address.html"] = function(obj){
var __p='';var print=function(){__p+=Array.prototype.join.call(arguments, '')};
with(obj||{}){
__p+='';
 if (model && typeof model.attributes !== 'undefined') { 
;__p+='\n<address>\n    <i class="glyphicon glyphicon-home"></i>\n    <strong>'+
( model.get('name') )+
'</strong><br>\n    ';
 if (model.has('address')) { 
;__p+=''+
( model.get('address') )+
'<br>';
 } 
;__p+='\n    ';
 if (model.has('postal_code')) { 
;__p+=''+
( model.get('postal_code') )+
'';
 } 
;__p+=' ';
 if (model.has('city')) { 
;__p+=''+
( model.get('city') )+
'';
 } 
;__p+=' ';
 if (model.get('country')) { 
;__p+=''+
( model.get('country') )+
'';
 } 
;__p+='<br>\n    <a href="http://maps.google.com/maps'+
( model.googleMapsQueryString() )+
'" target="_blank" class="link-google-maps"><i class="glyphicon glyphicon-map-marker"></i> '+
( $.t('tag.viewMap') )+
'</a>\n</address>\n';
 } 
;__p+='';
}
return __p;
};