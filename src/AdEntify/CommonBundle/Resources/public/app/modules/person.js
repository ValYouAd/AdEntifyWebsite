/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Person = app.module();

   Person.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_person');
      },

      initialize: function() {
         this.setup();
         this.listenTo(this, {
            'sync': this.setup,
            'add': this.setup
         });
      },

      toJSON: function() {
         delete this.attributes.link;
         return { person: this.attributes };
      },

      entityToModel: function(personEntity) {
         this.set('firstname', personEntity.firstname);
         this.set('lastname', personEntity.lastname);
         this.set('facebookId', personEntity.id);
         this.set('gender', personEntity.gender);
         if (typeof personEntity.name !== 'undefined')
            this.set('name', personEntity.name);
         if (typeof personEntity.id !== 'undefined')
            this.set('id', personEntity.id);
      },

      setup: function() {
         var link = null;
         if (this.has('user'))
            link = app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('user').id });
         else if (this.has('facebook_id'))
            link = 'https://www.facebook.com/profile.php?id=' + this.get('facebook_id');

         if (link)
            this.set('link', link);
      },

      getFullname: function() {
         return this.has('name') && this.get('name') ? this.get('name') : this.get('firstname') + ' ' + this.get('lastname')
      }
   });

   Person.Collection = Backbone.Collection.extend({
      model: Person.Model,
      cache: true
   });

   return Person;
});