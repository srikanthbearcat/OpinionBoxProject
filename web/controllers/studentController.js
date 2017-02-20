/**
 * Created by S525796 on 05-01-2017.
 */
app.controller("studentHomeController", ['$scope', '$cookies', '$state', '$http', 'url', '$window', '$uibModal', '$stateParams', function ($scope, $cookies, $state, $http, url, $uibModal, $window, $stateParams) {
//Get groups of student data from database
    $scope.groupData = [];
    $http.post(url + "/coursesByStudent/" + $cookies.get('username')).then(function successCallback(response) {
        console.log(response);
        // angular.forEach(response.data.info, function () {1
        // });
        // $scope.facultyData = response.data.info;
        $.each(response.data.info, function (i, data) {
            data.i = i;
            $scope.groupData.push(data);
        });
        console.log(response);
        //console.log(JSON.stringify($scope.courseData));
    }, function errorCallback(response) {
    })
}]);
//studentsInGroupViewController
app.controller("studentsInGroupViewController", ['$scope', '$cookies', '$state', '$http', 'url', '$window', '$uibModal', '$stateParams', function ($scope, $cookies, $state, $http, url, $uibModal, $window, $stateParams) {
//Get students of group from database
    $scope.group_id = $stateParams.groupid;
    $scope.studentsInGroupData = [];
    console.log($scope.group_id);
    $http.post(url + "/studentsInGroup/" + $cookies.get('username')+"/"+$scope.group_id).then(function successCallback(response) {
        console.log(response);
        // angular.forEach(response.data.info, function () {1
        // });
        // $scope.facultyData = response.data.info;
        $.each(response.data.info, function (i, data) {
            data.i = i;
            $scope.studentsInGroupData.push(data);
        });
        console.log(response);
        //console.log(JSON.stringify($scope.courseData));
    }, function errorCallback(response) {
    })
}]);
app.controller("studentEvaluationController", ['$scope', '$cookies', '$state', '$http', 'url', '$window', '$uibModal', '$stateParams', function ($scope, $cookies, $state, $http, url, $uibModal, $window, $stateParams) {
//Get questions of group from database
    $scope.group_id = $stateParams.groupid;
    $scope.student_id = $stateParams.studentid;
    $scope.questionData = [];
    console.log($scope.group_id);
    $http.post(url + "/questionsInGroup/" + $scope.student_id +"/" + $scope.group_id).then(function successCallback(response) {
        console.log(response);
        // angular.forEach(response.data.info, function () {1
        // });
        // $scope.facultyData = response.data.info;
        $.each(response.data.info, function (i, data) {
            data.i = i;
            $scope.questionData.push(data);
        });
        console.log(response);
        //console.log(JSON.stringify($scope.courseData));
    }, function errorCallback(response) {
    })
	    $scope.responseStudent = {};
    $scope.responseData = [];
    $scope.submit = function () {
        $http.post(url + "/responsesForQuestions/" + $scope.responseStudent +"/" + $cookies.get('username')+"/" + $scope.student_id).then(function successCallback(response) {
            console.log(responseStudent);
            // angular.forEach(response.data.info, function () {1
            // });
            // $scope.facultyData = response.data.info;
            // $.each(response.data.info, function (i, data) {
            //     data.i = i;
            //     $scope.responseData.push(data);
            // });
            console.log(response);
            //console.log(JSON.stringify($scope.courseData));
        }, function errorCallback(response) {
        })
    }
}]);