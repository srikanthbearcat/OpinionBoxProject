/**
 * Created by S525796 on 04-01-2017.
 */
app.controller("facultyController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', function ($scope, $cookies, $state, $http, url, $uibModal) {
//Get course data from database
    $scope.courseData = [];
    $http.get(url + "/coursesByFaculty/"+ $cookies.get('username')).then(function successCallback(response) {

        $scope.courseData = response.data.info;
        console.log(JSON.stringify(response.data.info));
        console.log(JSON.stringify($scope.courseData));
    }, function errorCallback(response) {
    })
}]);