'use strict';

/**
 * Config for the router
 */

angular.module('app')
  .config(function($stateProvider, $urlRouterProvider) {
    $stateProvider
      .state('app', {
        url: "/",
        templateUrl: 'views/home.html',
        controller: 'HomeCtrl'
      });

      $urlRouterProvider.otherwise("/");
  });
