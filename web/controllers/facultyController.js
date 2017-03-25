/**
 * Created by S525796 on 04-01-2017.
 */
 app.service('getCourseName', ['$http','url', function($http,url) {
     this.returnCourseName = function (course_crn) {
         return  $http.get(url + "/getCourseName/" + course_crn).then(function successCallback(response) {

                return response.data.info;
             }, function errorCallback(response) {
                 // console.log('error');
                 return "";
         });
     }
}]);
app.controller("facultyController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', '$rootScope', function ($scope, $cookies, $state, $http, url, $uibModal, $rootScope) {
// //Get course data from database
//     $scope.courseData = [];
//     $http.get(url + "/coursesByFaculty/" + $cookies.get('username')).then(function successCallback(response) {

//         $scope.courseData = response.data.info;
//         console.log(JSON.stringify(response.data.info));
//         console.log(JSON.stringify($scope.courseData));
//     }, function errorCallback(response) {
//     })

    $scope.getStudents = function (course_crn) {
        $rootScope.studentData = [];
        console.log("crn" + course_crn);
        $http.get(url + "/viewStudentsByCourse/" + course_crn).then(function successCallback(response) {
            $.each(response.data.info, function (i, data) {
                data.i = i;
                // data.original_course_crn = data.course_crn;
                $scope.studentData.push(data);
            });
            console.log(JSON.stringify($scope.studentData))
        }, function errorCallback(response) {
            console.log('error')
        })
    }

    //To get popup message
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
    //Get course data from database
    $scope.courseData = [];
    $http.get(url + "/coursesByFaculty/" + $cookies.get('username')).then(function successCallback(response) {

        // angular.forEach(response.data.info, function () {1
        // });
        // $scope.facultyData = response.data.info;
        $.each(response.data.info, function (i, data) {
            data.i = i;
            data.original_course_crn = data.course_crn;
            $scope.courseData.push(data);
        });
        console.log(JSON.stringify($scope.courseData));
    }, function errorCallback(response) {
    })

    $scope.checkCourseNumber = function (data) {
        // console.log(data+" sdvf"+data.length);
        var letters = /^[0-9]+$/;
        if (data === undefined || data === '') {
            return "Enter crn number";
        } else if (!data.match(letters)) {
            return "Only numbers";
        }
    };
    $scope.checkCourseName = function (data) {
        // console.log(data+" sdvf"+data.length);
        var letters = /^[A-Za-z ]+$/;
        if (data === undefined || data === '') {
            return "Enter course name";
        } else if (!data.match(letters)) {
            return "Only character";
        }
    };
    $scope.checkTrimester = function (data) {

        if (data === undefined || data === '') {
            return "Enter trimester";
        }
    };

    //Edit course data from database
    $scope.saveCourse = function (data, course) {
        console.log(JSON.stringify(data) + "Hello " + JSON.stringify(course));
        var editCourseData = {
            original_course_crn: course.original_course_crn,
            course_name: data.course_name,
            trimester: data.trimester,
            course_crn: data.course_crn,

        };
        $http.post(url + "/faculty/editCourseData", editCourseData).then(function successCallback(response) {

        }, function errorCallback(response) {
        })
    }

    //delete course data from database
    $scope.removeCourse = function (indexd, course_crn,course_name) {
        $scope.alert('sm', {
            modalHeader: "Delete Course",
            modalBody: "Are you sure you want to delete "+course_name+" course"+" ? If you click 'OK' all the data related to this course will be deleted",
            data: {indexd: indexd, course_crn: course_crn, deleteCourse: true}
        });


    };

//event handler
    $scope.$on("DeleteCourseConfirm", function (evt, modalInfo) {
        var deleteCourseData = {
            course_crn: modalInfo.data.course_crn
        };
        $http.post(url + "/faculty/removeCourseData", deleteCourseData).then(function successCallback(response) {
            if (response.data.success == true) {
                console.log(response.data.success);
                console.log(JSON.stringify($scope.courseData));
                $scope.courseData.splice(modalInfo.data.indexd, 1);
                console.log(JSON.stringify($scope.courseData));
            }

        }, function errorCallback(response) {
        })
    });

}]);

//controller to read CSV file
app.controller('createCourseController', ['$scope', '$window', '$uibModal', '$http', 'url', '$cookies','$timeout', function ($scope, $window, $uibModal, $http, url, $cookies,$timeout) {

    var USER_NAME_COLUMN_HEADER = "user_name";
    var PASSWORD_COLUMN_HEADER = "password";
    var FIRST_NAME_COLUMN_HEADER = "first_name";
    var LAST_NAME_COLUMN_HEADER = "last_name";
    var EMAIL_ID_COLUMN_HEADER = "email_id";
    var GROUP_NUMBER_COLUMN_HEADER = "group_no";
    var GROUP_TOPIC_COLUMN_HEADER = "group_topic";
    $scope.csvformatwrong = false;

    $scope.studentData = [];
    $scope.courseCrn = "";
    $scope.files = null;
    $scope.courseName = "";
    $scope.courseTrimester = "";
    $scope.callBack = function (data) {
//                $scope.studentData = [];
        setTimeout(function () {
            $scope.studentData = data;
        }, 2000);
        $scope.studentData = data;
    };
    $scope.$watch('files', function (newValue, oldValue) {
        var data = [];
        if ($scope.files == null) {
            return;
        }
        console.log(JSON.stringify($scope.files));
        $window.Papa.parse($scope.files, {
            header: true,
            dynamicTyping: true,
            skipEmptyLines: true,
            step: function (results) {
                var row = results.data[0];

                if (row[USER_NAME_COLUMN_HEADER] == (undefined )
                    || row[FIRST_NAME_COLUMN_HEADER] == undefined
                    || row[LAST_NAME_COLUMN_HEADER] == undefined
                    || row[EMAIL_ID_COLUMN_HEADER] == undefined
                    || row[PASSWORD_COLUMN_HEADER] == undefined
                    || row[GROUP_NUMBER_COLUMN_HEADER] == undefined
                    || row[GROUP_TOPIC_COLUMN_HEADER] == undefined) {
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
                        group_no: row[GROUP_NUMBER_COLUMN_HEADER],
                        group_topic: row[GROUP_TOPIC_COLUMN_HEADER]
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
        if (!$scope.courseName || !$scope.courseCrn || !$scope.courseTrimester || $scope.studentData.length == 0)
            return;
        $scope.newData = {
            "course_name": $scope.courseName
        }
        var data = {
            "faculty_user_name": $cookies.get("username"),
            "course_name": $scope.courseName,
            "course_crn": $scope.courseCrn,
            "course_trimester": $scope.courseTrimester,
            "student_data": JSON.stringify($scope.studentData)
        }
        console.log(JSON.stringify(data));
        $http.post(url + "/addCourse", data).then(function successCallback(response) {
            console.log(response.data.success + " add course request success");
            if (response.data.success) {
                $("#successMessage").fadeIn(2000).fadeOut(4000);
                $("#errorMessageLabel").text("");
                $scope.cancel();
                $scope.courseName = '';
                $scope.courseCrn = '';
                $scope.courseTrimester = '';
                $scope.createCourse.$setPristine();
                $scope.createCourse.$setUntouched();
                $timeout(function() {
                    window.history.go(-1);
                }, 2000);
            } else {
                $("#failedMessage").text(response.data.data).fadeIn(2000).fadeOut(6000);
            }
        }, function errorCallback(response) {
            if (!response.data.success) {

            }

        });


        // $.ajax({
        //     type: 'POST',
        //     url: url + "/addCourse",
        //     headers: {
        //         "user_name": $cookies.get("username"),
        //         "course_name": $scope.courseName,
        //         "course_crn": $scope.courseCrn,
        //         "data": JSON.stringify($scope.studentData)
        //     }
        // }).done(function (data) {
        //     if (data.success) {
        //         $("#successMessage").fadeIn(2000).fadeOut(6000);
        //         $("#errorMessageLabel").text("");
        //         $scope.cancel();
        //     } else {
        //         $("#errorMessageLabel").text(data.data).fadeIn(3000);
        //     }
        // });
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


// app.controller("courseViewController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', '$rootScope', '$stateParams','getCourseName', function ($scope, $cookies, $state, $http, url, $uibModal, $rootScope, $stateParams,getCourseName) {
// //display course details
//
//     $scope.studentData = [];
//     $scope.course_crn = $stateParams.courseid;
//     getCourseName.returnCourseName($scope.course_crn).then(function (data) {
//         $scope.course_name = data;
//     });
//     console.log($scope.course_name);
//     $http.get(url + "/viewStudentsByCourse/" + $scope.course_crn).then(function successCallback(response) {
//         $.each(response.data.info, function (i, data) {
//             data.i = i;
//             // data.original_course_crn = data.course_crn;
//             $scope.studentData.push(data);
//         });
//         console.log(JSON.stringify($scope.studentData))
//     }, function errorCallback(response) {
//         console.log('error')
//     })
//     $scope.questions = [{id: 1, selectedRating: 10, question: ''}];
//     $scope.range = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
// }]);

app.controller("courseViewController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', '$rootScope', '$stateParams','getCourseName', function ($scope, $cookies, $state, $http, url, $uibModal, $rootScope, $stateParams,getCourseName) {
//display course details

    $scope.studentData = [];
    $scope.course_crn = $stateParams.courseid;
    getCourseName.returnCourseName($scope.course_crn).then(function (data) {
        console.log(data);
        $scope.course_name = data.course_name;
        $scope.trimester = data.trimester;
    });
    console.log($scope.course_name);
    $http.get(url + "/viewStudentsByCourse/" + $scope.course_crn+"/"+$cookies.get('username')).then(function successCallback(response) {
        $.each(response.data.info, function (i, data) {
            data.i = i;
            // data.original_course_crn = data.course_crn;
            $scope.studentData.push(data);
        });
        console.log(JSON.stringify($scope.studentData))
    }, function errorCallback(response) {
        console.log('error')
    })
    $scope.questions = [{id: 1, selectedRating: 10, question: ''}];
    $scope.range = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    $scope.courseReport = {};
    $scope.downloadReport = function () {
        $http.get(url + "/courseReport/" + $scope.course_crn).then(function successCallback(response) {
                $scope.courseReport = response.data.info;
            var csv = Papa.unparse(response.data.info);
            var anchor = angular.element('<a/>');
            anchor.css({display: 'none'}); // Make sure it's not visible
            angular.element(document.body).append(anchor); // Attach to document

            anchor.attr({
                href: 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv),
                // href: 'data:attachment/csv' + csv,
                target: '_blank',
                download: 'Extraction1.csv'
            })[0].click();
            anchor.remove(); // Clean it up afterwards
            console.log(JSON.stringify(csv))
        }, function errorCallback(response) {
            console.log('error')
        })
    }
}]);

app.controller("groupViewController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', '$rootScope', '$stateParams','getCourseName', function ($scope, $cookies, $state, $http, url, $uibModal, $rootScope, $stateParams,getCourseName) {
//display group details
    $scope.groupData = [];
    $scope.course_crn = $stateParams.courseid;
    getCourseName.returnCourseName($scope.course_crn).then(function (data) {
        $scope.course_name = data.course_name;
    });
    console.log($stateParams.courseid);
    $http.get(url + "/viewGroupsByCourse/" + $scope.course_crn).then(function successCallback(response) {
        $.each(response.data.info, function (i, data) {
            data.i = i;
            // data.original_course_crn = data.course_crn;
            $scope.groupData.push(data);
        });

    }, function errorCallback(response) {
        console.log('error')
    })


}]);

app.controller("addQuestionsController", ['$scope', '$cookies', '$state', '$http', 'url', '$window', '$uibModal', '$stateParams','getCourseName', function ($scope, $cookies, $state, $http, url, $uibModal, $window, $stateParams,getCourseName) {
    //get questions for the course
    $scope.getQuestionData = [];
    $scope.course_crn = $stateParams.courseid;
    getCourseName.returnCourseName($scope.course_crn).then(function (data) {
        $scope.course_name = data.course_name;
    });
    console.log($stateParams.courseid);
    $http.get(url + "/getQuestionsByCourse/" + $scope.course_crn).then(function successCallback(response) {
        if (response.data.success){
            $.each(response.data.info, function (i, data) {
                data.i = i;
                // data.original_course_crn = data.course_crn;
                $scope.getQuestionData.push(data);
            });
        }else{

        }


    }, function errorCallback(response) {
        console.log('error')
    })


    //Add questions to the course
    $scope.course_crn = $stateParams.courseid;
    var QUESTION_COLUMN_HEADER = "question";
    var MAX_RATING_COLUMN_HEADER = "max_rating";

    $scope.csvformatwrong = false;

    $scope.questionData = [];
    $scope.files = null;
    $scope.callBack = function (data) {
        setTimeout(function () {
            $scope.questionData = data;
        }, 2000);
        $scope.questionData = data;
    };
    $scope.$watch('files', function (newValue, oldValue) {
        var data = [];
        if ($scope.files == null) {
            return;
        }
        console.log(JSON.stringify($scope.files));
        Papa.parse($scope.files, {
            header: true,
            dynamicTyping: true,
            skipEmptyLines: true,
            step: function (results) {
                var row = results.data[0];

                if (row[QUESTION_COLUMN_HEADER] == undefined
                    || row[MAX_RATING_COLUMN_HEADER] == undefined) {
                    // $scope.alert('sm', {modalHeader: "Error", modalBody: "Format Error: Please check the format of the csv file you are up", data:{}});
                    $scope.csvformatwrong = true;

                    // alertDialog({
                    //     title: "Error",
                    //     message: "Format Error: Please check the format of the csv file you are up "
                    // });
                } else {
                    data.push({
                        question: row[QUESTION_COLUMN_HEADER],
                        max_rating: row[MAX_RATING_COLUMN_HEADER]

                    });
                }
            },
            complete: function (results) {
                $scope.questionData = data;
                $("#previewLink").trigger('click');
            }

        });
    });
    $scope.preview = function (callBack) {
        // console.log($scope.questionData);
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
        if ($scope.questionData.length == 0)
            return;
        var data = {
            "faculty_user_name": $cookies.get("username"),
            "course_crn": $scope.course_crn,
            "question_data": JSON.stringify($scope.questionData)
        }
        console.log(JSON.stringify(data));
        $http.post(url + "/addQuestionsToCourse", data).then(function successCallback(response) {
            console.log(response.data.success + " add questions request success");
            if (response.data.success) {
                $("#successMessage").fadeIn(2000).fadeOut(6000);
                $("#errorMessageLabel").text("");
                $scope.cancel();
            } else {
                $("#failedMessage").text(response.data.data).fadeIn(2000).fadeOut(6000);
            }
        }, function errorCallback(response) {
            if (!response.data.success) {

            }

        });


        // $.ajax({
        //     type: 'POST',
        //     url: url + "/addCourse",
        //     headers: {
        //         "user_name": $cookies.get("username"),
        //         "course_name": $scope.courseName,
        //         "course_crn": $scope.courseCrn,
        //         "data": JSON.stringify($scope.questionData)
        //     }
        // }).done(function (data) {
        //     if (data.success) {
        //         $("#successMessage").fadeIn(2000).fadeOut(6000);
        //         $("#errorMessageLabel").text("");
        //         $scope.cancel();
        //     } else {
        //         $("#errorMessageLabel").text(data.data).fadeIn(3000);
        //     }
        // });
    };
    $scope.cancel = function () {
        $scope.questionData = [];
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