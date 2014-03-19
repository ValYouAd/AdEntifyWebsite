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

      toJSON: function() {
         return { person: this.attributes }
      },

      entityToModel: function(personEntity) {
         this.set('firstname', personEntity.first_name);
         this.set('lastname', personEntity.last_name);
         this.set('facebookId', personEntity.id);
         this.set('gender', personEntity.gender);
         if (typeof personEntity.name !== 'undefined')
            this.set('name', personEntity.name);
         if (typeof personEntity.id !== 'undefined')
            this.set('id', personEntity.id);
      }
   });

   Person.Collection = Backbone.Collection.extend({
      model: Person.Model,
      cache: true
   });

   return Person;
});