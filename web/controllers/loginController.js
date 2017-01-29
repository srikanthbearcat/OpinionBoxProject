app.controller("LoginController", ['$scope', '$cookies', '$state', '$http', '$rootScope', 'url', '$uibModal', function ($scope, $cookies, $state, $http, $rootScope, url, $uibModal) {

    //Variables

    $scope.loginInfo = {};
    // $scope.state = $state;
    $scope.loginAlert = false;
    if ($cookies.get('username')) {
        if ($cookies.get('user_type') === "admin") {
            $state.go('adminHome');
        }
        else if ($cookies.get('user_type') === "faculty") {
            $state.go('facultyHome');
        } else {
            $state.go('studentHome');
        }
    } else {
        $state.go('login');
    }

    //Functions
    $scope.alert = function (size, modal_Info) {
        // $scope.alert = function (size){
        // $scope.animateEnabled = true;

        var modalPopUpInstance = $uibModal.open({
            // animate:$scope.animateEnabled,
            templateUrl: 'web/views/modal.html',
            controller: 'modalInstanceController',
            // size: size,
            resolve: {
                modalInfo: function () {
                    return modal_Info;
                }
            }
        });
    }
    //called when form is submitted
    $scope.onSubmitForm = function () {
        if ($scope.loginForm.$invalid) {
            console.log("form not valid");
            return;
        }

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
            }
        }
        var data = $.param({
            usertype: $scope.loginInfo.usertype,
            username: $scope.loginInfo.username,
            password: $scope.loginInfo.password
        });
        if ($scope.loginInfo.usertype == "admin") {
            $http.post(url + "/admin/login", data, config).then(function successCallback(response) {
                console.log(response.data.success + " Login request success");
                $scope.onLoginSuccess(response);
            }, function errorCallback(response) {
                console.log(response.status + "Login request failed");
                // $scope.alert('md');
            });
        } else if ($scope.loginInfo.usertype == "faculty") {
            $http.post(url + "/faculty/login", data, config).then(function successCallback(response) {
                console.log(response.data.success + "Login request success");
                $scope.onLoginSuccess(response);
            }, function errorCallback(response) {
                console.log(response.status + "Login request failed");
            });
        } else {
            $http.post(url + "/student/login", data, config).then(function successCallback(response) {
                console.log(response.data.success + "Login request success");
                $scope.onLoginSuccess(response);
            }, function errorCallback(response) {
                console.log(response.status + "Login request failed");

            });
        }


    }
    $scope.onLoginSuccess = function (response) {
        if (response.data.success) {
            if (response.data.user_type === "admin") {
                $cookies.put('user_type', response.data.user_type);
                console.log("cookie set " + response.data.user_type);
            } else if (response.data.user_type === "faculty") {
                $cookies.put('user_type', response.data.user_type);
                console.log("cookie set " + response.data.user_type);
            } else if (response.data.user_type === "student") {
                $cookies.put('user_type', response.data.user_type);
                console.log("cookie set " + response.data.user_type);
            }
            $cookies.put('email', response.data.info.email);
            $cookies.put('first_name', response.data.info.first_name);
            $cookies.put('last_name', response.data.info.last_name);
            $cookies.put('username', response.data.info.user_name);
            $state.reload();
        } else {
            console.log("enter else");
            // $scope.alert('sm');
            // $scope.alert('sm', {modalHeader: "Login Error", modalBody: "Invalid credentials"});
            $scope.loginAlert = true;
            $scope.$apply();
        }
    }
}]);


