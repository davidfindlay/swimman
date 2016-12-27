/*global require, requirejs */

'use strict';

requirejs.config({
  paths: {
    'angular': ['../lib/angularjs/angular'],
    'angular-route': ['../lib/angularjs/angular-route'],
      'angular-animate': ['../lib/angular-animate/angular-animate'],
    'ui-bootstrap':['../lib/angular-ui-bootstrap/ui-bootstrap']
  },
  shim: {
    'angular': {
      exports : 'angular'
    },
    'angular-route': {
      deps: ['angular'],
      exports : 'angular'
    },
      'ui-bootstrap': {
          deps: ['angular'],
          exports: 'ui-bootstrap'
      }
  }
});

require(['angular', './controllers', './directives', './filters', './services', 'angular-route', "ui-bootstrap", 'angular-animate'],
  function(angular, controllers) {

    // Declare app level module which depends on filters, and services
      //
      var eprogram2 = angular.module('eprogram2', ['ui.bootstrap', 'eprogram2.filters', 'eprogram2.services', 'eprogram2.directives', 'ngRoute', 'ngAnimate']).
      config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/view1', {templateUrl: 'partials/partial1.html', controller: controllers.MyCtrl1});
        $routeProvider.when('/view2', {templateUrl: 'partials/partial2.html', controller: controllers.MyCtrl2});
        $routeProvider.otherwise({redirectTo: '/view1'});
      }]);

      eprogram2.controller('menuCtrl', controllers.menuCtrl);

    angular.bootstrap(document, ['eprogram2']);


  });
