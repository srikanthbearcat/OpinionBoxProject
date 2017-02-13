app.controller("settingsController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', function ($scope, $cookies, $state, $http, url, $uibModal) {

    $scope.resetPassword = function(){
        $scope.passwordChangeSuccess = false;
        $scope.passwordChangeFailed = false;
        $scope.passwordMismatch = false;
        if ($scope.settingsForm.$invalid) {
            console.log("Settings form invalis");
        } else {
            console.log("Settings form  valid");
            var data = {
                user_name: $cookies.get('username'),
                current_password: $scope.settings.currentPwd,
                new_password: $scope.settings.newPwd
            };
            $http.post(url + "/Settings", data).then(function successCallback(response) {
                console.log(response.data.success + " Reset password request success");
                if (response.data.success) {
                    $scope.passwordChangeSuccess = true;
                    $scope.passwordChangeFailed = false;
                } else {
                    $scope.passwordChangeSuccess = false;
                    $scope.passwordChangeFailed = true;
                }
            }, function errorCallback(response) {
                if (!response.data.success && response.data.info == "User doesn't exist") {
                    $scope.passwordMismatch = true;
                    console.log("User does not exist");
                }
                console.log(response.status + "Reset password request failed");
                console.log(JSON.stringify(response) + "reset password request failed");

                // $scope.alert('md');
            });
        }
    }
}]);