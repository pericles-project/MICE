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
      })
      .state('case2', {
        url: "/",
        templateUrl: 'views/home.html',
        controller: 'Case2Ctrl'
      })
      .state('case3', {
        url: "/",
        templateUrl: 'views/home.html',
        controller: 'Case3Ctrl'
      });

      $urlRouterProvider.otherwise("/");
  });
