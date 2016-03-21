(function() {
  'use strict';

  angular.module('app')
    .constant('Config', {
      api: {
         url: '@@API_URL'
      }
    });
}());
