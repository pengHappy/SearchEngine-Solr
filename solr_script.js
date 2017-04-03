var myApp = angular.module('myApp', ['angularUtils.directives.dirPagination', 'ngAnimate']);

//var phpSearchUrl = "main.php?keyword=";
var phpUrl = "main.php?keyword=";

function controller($scope, $http, $window, $timeout) {
    
    $scope.autoCorrectWord = '';
    $scope.search_input = '';
    $scope.currentPage = 1;
    $scope.pageSize = 10;
    $scope.description_limit = 120;
    $scope.title_limit = 50;
    $scope.hideFontBar = false;
    $scope.resArray = [];
    $scope.sgArray = [];
    
    // formal search
    $scope.searchHandler = function() {
        var key_word = document.getElementById("key_word").value;
        var realUrl = phpUrl + key_word;
        $http({
            method: 'GET',
            url: realUrl
        }).then(function successCallback(response) {
            console.log(response.data);
            $scope.resArray = response.data.results;
            $scope.autoCorrectWord = response.data.auto_correct;
        }, function errorCallback(response) {
            console.log(response);
        });
    }
    
    // spell hint
    $scope._suggestHandler = function() {
        $scope.autoCorrectWord = null;
        var key_word = null;
        if(document.getElementById("key_word_font") === null) {
            key_word = document.getElementById("key_word").value;
        }
        else {
            key_word = document.getElementById("key_word_font").value;
        }
         
        console.log(key_word);
        var realUrl = phpUrl + key_word + "&requestHandler=suggest";
        $http({
            method: 'GET',
            url: realUrl
        }).then(function successCallback(response) {
            console.log(response.data);
            $scope.sgArray = response.data.results;
            
        }, function errorCallback(response) {
            console.log(response);
        });
    }
    
    $scope.formatDate = function(date_str) {
//        console.log(date_str);
        return date_str.substring(0, 10);
    }
    
    $scope.formatDescription = function(des_str) {
//        console.log(date_str);
        if(des_str !== null && des_str.length > $scope.description_limit) {
            des_str = des_str.substring(0, $scope.description_limit) + "...";
        }
        return des_str;
    }
    
    $scope.formatTitle = function(title_str) {
//        console.log(date_str);
        if(title_str.length > $scope.title_limit) {
            title_str = title_str.substring(0, $scope.title_limit) + "...";
        }
        return title_str;
    }
    
    $scope.showPanelAtTimeout = function() {
        document.getElementById("results_panal").style.display = 'block';
    }
    
    $scope.fontBarHandler = function(event) {
        $scope.hideFontBar = true;
        
        var key_word = document.getElementById("key_word_font").value;
        document.getElementById("key_word").value = key_word;
        
        $scope.searchHandler();
        
        $timeout(function(){ $scope.showPanelAtTimeout(); }, 1000);
//        $scope.showPanelAtTimeout();
    }
    
}

function OtherController($scope) {
  $scope.pageChangeHandler = function(num) {
    console.log('going to page ' + num);
  };
}

myApp.controller('controller', controller);
myApp.controller('OtherController', OtherController);