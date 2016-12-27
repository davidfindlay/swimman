/*global define */

'use strict';

define(function() {

    /* Controllers */

    var controllers = {};

    controllers.menuCtrl = function($scope, $window) {

        // Detect if mobile portrait screen size
        $scope.is_mobile = ($window.innerWidth <= 480);

        console.log ($window.innerWidth + " therefore " + $scope.is_mobile);

        if ($scope.is_mobile) {
            $scope.menuClosed = true;
            console.log("mobile detected - close menu")
        } else {
            $scope.menuClosed = false;
            console.log("not mobile - open menu")
        }

        //console.log("menu = " + showMenu);

    }
    controllers.menuCtrl.$inject = ['$scope', '$window'];

    return controllers;

});