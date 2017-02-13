/**
 * Created by S525796 on 05-01-2017.
 */
app.controller("studentController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', function ($scope, $cookies, $state, $http, url, $uibModal) {
//Get course of student data from database
    $scope.courseData = [];
    $http.post(url + "/coursesByStudent/" + $cookies.get('username')).then(function successCallback(response) {
        console.log(response);
        // angular.forEach(response.data.info, function () {1
        // });
        // $scope.facultyData = response.data.info;
        $.each(response.data.info, function (i, data) {
            data.i = i;
            data.original_course_crn = data.course_crn;
            $scope.courseData.push(data);
        });
        console.log(response);
        //console.log(JSON.stringify($scope.courseData));
    }, function errorCallback(response) {
    })
}]);