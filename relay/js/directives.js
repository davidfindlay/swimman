/*global define */

'use strict';

define(['angular'], function(angular) {

/* Directives */

angular.module('eprogram2.directives', []).
  directive('appVersion', ['version', function(version) {
    return function(scope, elm, attrs) {
      elm.text(version);
    };
  }]);

});