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

//controller to read CSV file
app.controller('createCourseController', ['$scope', '$window','$uibModal','$http', 'url','$cookies', function ($scope, $window,$uibModal,$http, url,$cookies) {

    var USER_NAME_COLUMN_HEADER = "user_name";
    var PASSWORD_COLUMN_HEADER = "password";
    var FIRST_NAME_COLUMN_HEADER = "first_name";
    var LAST_NAME_COLUMN_HEADER = "last_name";
    var EMAIL_ID_COLUMN_HEADER = "email_id";
    var GROUP_NUMBER_COLUMN_HEADER = "group_no";

    $scope.csvformatwrong = false;

    $scope.studentData = [];
    $scope.courseCrn = "";
    $scope.files = null;
    $scope.courseName = "";
    $scope.callBack = function (data) {
//                $scope.studentData = [];
        setTimeout(function () {
            $scope.studentData = data;
        }, 2000);
        $scope.studentData = data;
    };
    $scope.$watch('files', function (newValue, oldValue) {
        var data = [];
        if ($scope.files == null){
            return;
        }
        console.log(JSON.stringify($scope.files));
        $window.Papa.parse($scope.files, {
            header: true,
            dynamicTyping: true,
            skipEmptyLines: true,
            step: function (results) {
                var row = results.data[0];

                if (row[USER_NAME_COLUMN_HEADER] == (undefined || "" )
                    || row[FIRST_NAME_COLUMN_HEADER] == undefined
                    || row[LAST_NAME_COLUMN_HEADER] == undefined
                    || row[EMAIL_ID_COLUMN_HEADER] == undefined
                    || row[PASSWORD_COLUMN_HEADER] == undefined
                    || row[GROUP_NUMBER_COLUMN_HEADER] == undefined) {
                    // $scope.alert('sm', {modalHeader: "Error", modalBody: "Format Error: Please check the format of the csv file you are up", data:{}});
                    $scope.csvformatwrong = true;

                    // alertDialog({
                    //     title: "Error",
                    //     message: "Format Error: Please check the format of the csv file you are up "
                    // });
                } else {
                    data.push({
                        user_name: row[USER_NAME_COLUMN_HEADER],
                        first_name: row[FIRST_NAME_COLUMN_HEADER],
                        last_name: row[LAST_NAME_COLUMN_HEADER],
                        email_id: row[EMAIL_ID_COLUMN_HEADER],
                        password: row[PASSWORD_COLUMN_HEADER],
                        group_no: row[GROUP_NUMBER_COLUMN_HEADER]
                    });
                }
            },
            complete: function (results) {
                $scope.studentData = data;
                $("#previewLink").trigger('click');
            }

        });
    });
    $scope.preview = function (callBack) {
        // console.log($scope.studentData);
//                var data = [];
//                $window.Papa.parse($scope.files, {
//                    header: true,
//                    dynamicTyping: true,
//                    complete: function (results) {
//                        callBack(results.data);
//                    }
//
//                });
    };
    $scope.remove = function () {
        $scope.cancel();
//                $("#preview").css("visibility", "visible");
//                $("#save").css("visibility", "hidden");
//                $("#cancel").css("visibility", "hidden");
    };
    $scope.change = function () {
//                $("#previewTable").css("visibility", "hidden");
//                $("#preview").css("visibility", "visible");
//                $("#cancel").css("visibility", "hidden");
//                $("#save").css("visibility", "hidden");
    };
    $scope.saveData = function () {
        if (!$scope.courseName || !$scope.courseCrn || $scope.studentData.length == 0)
            return;

        $.ajax({
            type: 'POST',
            url: url+"/addCourse",
            headers: {
                "user_name": $cookies.get("username"),
                "course_name": $scope.courseName,
                "course_crn": $scope.courseCrn,
                "data": JSON.stringify($scope.studentData)
            }
        }).done(function (data) {
            if (data.success) {
                $("#successMessage").fadeIn(2000).fadeOut(6000);
                $("#errorMessageLabel").text("");
                $scope.cancel();
            } else {
                $("#errorMessageLabel").text(data.data).fadeIn(3000);
            }
        });
    };
    $scope.cancel = function () {
        $scope.courseCrn = "";
        $scope.courseName = "";
        $scope.studentData = [];
        $("#filebutton").val('');
        $("#previewLink").trigger('click');
    };

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
    };

}]);

app.directive("fileread", [function () {
    return {
        scope: {
            fileread: "="
        }, link: function (scope, element, attributes) {
            element.bind("change", function (changeEvent) {
                scope.$apply(function () {
                    scope.fileread = changeEvent.target.files[0];

                });
            });
        }
    }
}]);