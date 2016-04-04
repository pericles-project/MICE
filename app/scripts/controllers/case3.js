'use strict';

/**
 * @ngdoc function
 * @name app.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the app
 */
angular.module('app')
  .controller('Case3Ctrl', function ($scope, $http) {
    $http.get("/data3.json").then(
      function(response) {
        $scope.val = response.data;
      },
      function(error) {
        throw error;
      }
    );
});
