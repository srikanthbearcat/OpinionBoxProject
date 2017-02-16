app.controller("navigationController", ['$scope','$cookies','$state' ,function ($scope,$cookies,$state) {
    //
    $scope.first_name = $cookies.get('first_name');
    $scope.last_name = $cookies.get('last_name');
    $scope.logout = function () {
        $cookies.remove('email');
        $cookies.remove('first_name');
        $cookies.remove('last_name');
        $cookies.remove('username');
        $cookies.remove('user_type');
        $state.go('login');
    }
	$scope.homepage = function(){
	    console.log($cookies.get('user_type'));
        	if ($cookies.get('user_type') === "admin") {
            		$state.go('adminHome');
        	}
       		 else if ($cookies.get('user_type') === "faculty") {
            		$state.go('facultyHome');
        	}
		else {
            		$state.go('studentHome');
        	}

}
}]);