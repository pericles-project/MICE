'use strict';

/**
 * @ngdoc function
 * @name app.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the app
 */
angular.module('app')
  .controller('HomeCtrl', function ($scope, $http, Config) {
    $http.get(Config.api.url + "/data.json").then(
      function(response) {
        $scope.val = response.data;
      },
      function(error) {
        throw error;
      }
    );
});
