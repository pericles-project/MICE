'use strict';

/**
 * @ngdoc function
 * @name app.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the app
 */
angular.module('app')
  .controller('Case2Ctrl', function ($scope, $http, $location) {
    var path = $location.absUrl().replace('/#/', '');
    $http.get(path + "/data2.json").then(
      function(response) {
        $scope.val = response.data;
      },
      function(error) {
        throw error;
      }
    );
});
