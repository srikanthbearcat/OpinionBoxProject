/**
 * Created by S525796 on 05-01-2017.
 */
app.service('getEvaluations', ['$http','url', function($http,url) {
    this.studentEvaluation = function (group_id,student_to,student_by) {
        console.log(group_id+" "+student_to+" "+student_by);
        // return  $http.get(url + "/getCourseName/" + course_crn).then(function successCallback(response) {
        //
        //     return response.data.info.course_name;
        // }, function errorCallback(response) {
        //     // console.log('error');
        //     return "";
        // });
        return true;
    }
}]);
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
app.controller("studentsInGroupViewController", ['$scope', '$cookies', '$state', '$http', 'url', '$window', '$uibModal', '$stateParams','getEvaluations', function ($scope, $cookies, $state, $http, url, $uibModal, $window, $stateParams,getEvaluations) {
//Get students of group from database
    $scope.group_id = $stateParams.groupid;
    $scope.studentsInGroupData = [];
    $http.post(url + "/studentsInGroup/" + $cookies.get('username')+"/"+$scope.group_id).then(function successCallback(response) {
        // console.log(response);
        // angular.forEach(response.data.info, function () {1
        // });
        // $scope.facultyData = response.data.info;
        $.each(response.data.info, function (i, data) {
            data.i = i;
            // getEvaluations.studentEvaluation($scope.group_id,data.id,$cookies.get('username')).then(function (info) {
            //     data.evaluated = info;
            // });
            $scope.studentsInGroupData.push(data);
        });
        console.log($scope.studentsInGroupData);
        //console.log(JSON.stringify($scope.courseData));
    }, function errorCallback(response) {
    })
$scope.callEvaluation = function (student_id) {
    $state.go('studentEvaluation',{studentid:student_id,groupid:$scope.group_id});
}

}]);
app.controller("studentEvaluationController", ['$scope', '$cookies', '$state', '$http', 'url', '$window', '$uibModal', '$stateParams','$timeout', function ($scope, $cookies, $state, $http, url, $uibModal, $window, $stateParams,$timeout) {
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
        if ($scope.evaluateStudentForm.$invalid) {
            console.log("Evaluation form not valid");
        } else {
            $http.post(url + "/responsesForQuestions/" + $cookies.get('username') + "/" + $scope.student_id, $scope.responseStudent).then(function successCallback(response) {
                if (response.data.success) {
                    $("#successMessage").fadeIn(10).fadeOut(2000);
                    $("#errorMessageLabel").text("");

                        $scope.responseStudent = {};
                        $scope.evaluateStudentForm.$setPristine();
                        $scope.evaluateStudentForm.$setUntouched();
                    $timeout(function() {
                        window.history.go(-1);
                    }, 2000);
                } else {
                    $("#failedMessage").text(response.data.info).fadeIn(2000).fadeOut(6000);
                }
            }, function errorCallback(response) {
            })
        }
    }
}]);