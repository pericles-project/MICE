'use strict';

/**
 * @ngdoc overview
 * @name miceApp
 * @description
 * # miceApp
 *
 * Main module of the application.
 */
angular
  .module('miceApp', [
    'ngCookies',
    'ngResource',
    'ngRoute',
    'ngSanitize',
    'ngTouch'
  ])
  .config(function ($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/main.html',
        controller: 'MainCtrl',
        controllerAs: 'main'
      })
      .otherwise({
        redirectTo: '/'
      });
  });
