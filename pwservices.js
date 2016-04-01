angular.module('starter.services', [])

.factory('pw', function($http) {
	var lits = [];
	var myUrl = "http://rutapa.com/web-service/";

	return {
		getLits: function(){
			return $http.get( myUrl+"aytos/" ).then(function(response){
				return response.data;
			});
		},
		getLit: function(litId){
			return $http.get( myUrl+"ayto/"+litId ).then(function(response){
				return response.data[0];
			});
		}
	}
})
.filter('unsafe', function($sce) { return function(val) { return $sce.trustAsHtml(val); }; });
.filter( 'substitute', function () {
    return function( text, find, replace ) {
        var n = text.split( find );
        var r = n.join( replace );
        return r;
    };
})