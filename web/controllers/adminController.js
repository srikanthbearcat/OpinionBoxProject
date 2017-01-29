/**
 * Created by S525796 on 04-01-2017.
 */
app.controller("adminController", ['$scope', '$cookies', '$state', '$http', 'url', '$uibModal', function ($scope, $cookies, $state, $http, url,$uibModal) {
    $scope.addFacultyForm = {};
    $scope.addedFacultySuccess = false;
    $scope.addedFacultyFailed = false;
    $scope.addFacultyExist = false;
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
    $scope.addNewFaculty = function () {
        if ($scope.addFacultyForm.$invalid) {
            console.log("add faculty form not valid");
        } else {
            console.log("add faculty form  valid");
            // var config = {
            //     headers: {
            //         'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
            //     }
            // };

            var data = {
                first_name: $scope.addFaculty.firstName,
                last_name: $scope.addFaculty.lastName,
                email_address: $scope.addFaculty.emailAddress,
                user_name: $scope.addFaculty.username,
                password: $scope.addFaculty.password
            };
            if ($cookies.get('user_type') == "admin") {
                console.log("entered cookies");

                $http.post(url + "/admin/addFaculty", data).then(function successCallback(response) {
                    console.log(response.data.success + " add faculty request success");
                    if (response.data.success) {
                        $scope.addedFacultySuccess = true;
                        $scope.addedFacultyFailed = false;
                    } else {
                        $scope.addedFacultySuccess = false;
                        $scope.addedFacultyFailed = true;
                    }
                }, function errorCallback(response) {
                    if (!response.data.success && response.data.info.reason == "Username already Exists") {
                        $scope.addFacultyExist = true;
                        console.log("Faculty already exists");
                    }
                    console.log(response.status + "add faculty request failed");
                    console.log(JSON.stringify(response) + "add faculty request failed");

                    // $scope.alert('md');
                });
            }
        }
    }
    //Get faculty data from database
    $scope.facultyData = [];
    $http.post(url + "/admin/getFacultyData").then(function successCallback(response) {

        // angular.forEach(response.data.info, function () {
        // });
        // $scope.facultyData = response.data.info;
        $.each(response.data.info, function (i, data) {
            data.i = i;
            data.original_user_name = data.user_name;
            $scope.facultyData.push(data);
        });
        console.log(JSON.stringify($scope.facultyData));
    }, function errorCallback(response) {
    })


    $scope.checkFirstName = function (data) {
        // console.log(data+" sdvf"+data.length);
        var letters = /^[A-Za-z ]+$/;
        if (data === undefined || data === '') {
            return "Enter first name";
        } else if (!data.match(letters)) {
            return "Only character";
        }
    };
    $scope.checkLastName = function (data) {
        // console.log(data+" sdvf"+data.length);
        var letters = /^[A-Za-z ]+$/;
        if (data === undefined || data === '') {
            return "Enter last name";
        } else if (!data.match(letters)) {
            return "Only character";
        }
    };
    $scope.checkUserName = function (data) {

        if (data === undefined || data === '') {
            return "Enter user name";
        }
    };
    $scope.checkPassword = function (data) {

        if (data === undefined || data === '') {
            return "Enter password";
        }
    };
    $scope.checkEmail = function (data) {

        if (data === undefined || data === '') {
            return "Enter email address";
        }
    };
    //Edit faculty data from database
    $scope.saveFaculty = function (data, faculty) {
        console.log(JSON.stringify(data)+"Hello "+JSON.stringify(faculty));
        var editFacultyData = {
            original_user_name : faculty.original_user_name,
            first_name: data.first_name,
            last_name: data.last_name,
            email: data.email,
            user_name: data.user_name,
            password: data.password
        };
        $http.post(url + "/admin/editFacultyData",editFacultyData).then(function successCallback(response) {

        }, function errorCallback(response) {
        })
    }
    //delete faculty data from database
    $scope.removeFaculty = function (indexd, user_name) {
        $scope.alert('sm', {modalHeader: "Delete Faculty", modalBody: "Are you sure you want to delete? All the data related to this faculty will be deleted", data:{indexd:indexd,user_name:user_name}});


    };
    //event handler
    $scope.$on("DeleteFacultyConfirm", function (evt, modalInfo) {
        var deleteFacultyData = {
            user_name: modalInfo.data.user_name
        };
        $http.post(url + "/admin/removeFacultyData",deleteFacultyData).then(function successCallback(response) {
            if(response.data.success == true){
                console.log(response.data.success);
                console.log(JSON.stringify($scope.facultyData));
                $scope.facultyData.splice(modalInfo.data.indexd, 1);
                console.log(JSON.stringify($scope.facultyData));
            }

        }, function errorCallback(response) {
        })
    });


}]);