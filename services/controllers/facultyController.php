<?php


$loginFaculty = function () use ($app) {
    try {
        $postData = $app->request->post();
        $user_name = $postData['username'];
        $password = $postData['password'];
        $core = Core::getInstance();
        $sql = "SELECT first_name,last_name,email_id FROM `faculty` WHERE user_id in (SELECT id FROM user_account WHERE user_name=:user_name AND password =:password)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("password", $password);
        $response = new stdClass();
        $response->user_type = "faculty";
        $response->user_name = $user_name;
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            $response->info = $response->success ? $records[0] : 0;
        } else {
            $response->success = FALSE;
            $response->data = 0;
        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $response->success = FALSE;
        $response->data = $ex->getMessage();
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
};

function getCoursesByFaculty($fname)
{
    try {
        $faculty = "faculty";
        $core = Core::getInstance();
        $sql = "SELECT course_crn,course_name,trimester FROM `course` WHERE faculty_id in (select id from faculty WHERE 
                user_id in (select id from user_account WHERE user_name=:fname)) ";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("fname", $fname);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            $response->info = $response->success ? $records : 0;
        } else {
            $response->success = FALSE;
            $response->info = 0;
        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

$addCourse = function () use ($app) {
//    $postData = $app->request->post();
//    $faculty_user_name = $postData->faculty_user_name;
//    $course_name = $postData->course_name;
//    $course_crn = $postData->course_crn;
//    $course_trimester = $postData->course_trimester;
//    $jsonString = $postData->student_data;

    $json = $app->request->getBody();
    $postData = json_decode($json, true); // parse the JSON into an assoc. array
    $faculty_user_name = $postData['faculty_user_name'];
    $course_name = $postData['course_name'];
    $course_crn = $postData['course_crn'];
    $course_trimester = $postData['course_trimester'];
    $jsonString = $postData['student_data'];
    $course_id_global = NULL;
    try {
        $response = new stdClass();
        $response->success = FALSE;
        $response->data = "not all done";
        if (!$faculty_user_name || !$course_name || !$course_crn || !$course_trimester || !$jsonString) {
            $response->message = "Missing faculty username or course crn number or list of students";
//            echo json_encode($response);
//            throw new Exception('Missing faculty username or course crn number or list of students');
        } else {
            if (!isJson($jsonString)) {
                throw new Exception('Students data is not valid json string');
            } else {
                $course = new Course($course_crn, $course_name, $course_trimester);
                $courseExist = isCourseExist($course);

                if ($courseExist->success == TRUE) {
                    // 1) Adding each course in to course table if doesnot exist in it
                    if ($courseExist->flag == 0) {
                        $courseData = addCourse($course, $faculty_user_name);
                        $course_id = $courseData->data;
                        $course_id_global = $course_id;
                        $response->data =  $courseData;
                    }
                    foreach (json_decode($jsonString) as $student) {
                        $result = isUserExist($student->user_name);
                        $response->data =  $result;
//                        $group = new Group($student->group_no,$student->group_topic);
//                        $groupData = addGroup(new Group($student->group_no,$student->group_topic), $course_id_global);
                        $groupData = addGroup($student->group_no,$student->group_topic, $course_id_global);
                        if ($result->userExist == TRUE) {
                            $response->data =  addCourseStudent($course_id_global,$result->id);
                            addGroupStudent($groupData->group_id,$result->id);

                        }
                        else {
                            $user_type = "student";
                            $userData = addUser(new UserAccount($student->user_name, $student->password),$user_type);
                            if ($userData->success == TRUE) {
                                $studentData = addStudent(new Student($student->first_name, $student->last_name, $student->email_id), $userData->data);
                                $student_id = $studentData->data;
                                addCourseStudent($course_id_global,$student_id);
                                addGroupStudent($groupData->group_id,$student_id);
                            }

                        }
                    }

                    $response->success = TRUE;

//                    $response->data = $courseExist->data;
                }
                else {

                    $response->success = FALSE;

                    $response->data = $courseExist->data;
                }
            }
        }
        echo json_encode($response);
//        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
        echo json_encode($response) ;
    }
};


function addCourse($course, $faculty_user_name)
{
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $faculty_sql = "select id from faculty WHERE user_id in (select id from user_account WHERE user_name=:faculty_name)";
        $faculty_stmt = $core->dbh->prepare($faculty_sql);
        $faculty_stmt->bindParam("faculty_name", $faculty_user_name);
        $faculty_stmt->execute();
        $records = $faculty_stmt->fetchAll(PDO::FETCH_ASSOC);
        $faculty =  $records[0] ;
        $faculty_id = $faculty['id'];
        $sql = "INSERT INTO course (course_crn,course_name,trimester,faculty_id) VALUES (:course_crn,:course_name,:course_trimester,:faculty_id)";
        $stmt = $core->dbh->prepare($sql);
        $course_crn = $course->getCourseCrn();
        $course_name = $course->getCourseName();
        $course_trimester = $course->getCourseTrimester();
        $stmt->bindParam("course_crn", $course_crn);
        $stmt->bindParam("course_trimester", $course_trimester);
        $stmt->bindParam("course_name", $course_name);
        $stmt->bindParam("faculty_id", $faculty_id);
        $response = new stdClass();
        $response->success = $stmt->execute();
//        $stmt2 = $core->dbh->prepare("SELECT LAST_INSERT_ID()");
        $response->data = $core->dbh->lastInsertId();
        return $response;

    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

//add group
function addGroup($group_no,$group_topic, $course_id)
{
    $app = \Slim\Slim::getInstance();
    try {
        $response = new stdClass();
        $core = Core::getInstance();
        $group_sql = "SELECT `id` FROM `group` WHERE (course_id=:course_id AND group_no=:group_no)";
        $group_stmt = $core->dbh->prepare($group_sql);
        $group_stmt->bindParam("course_id", $course_id);
        $group_stmt->bindParam("group_no", $group_no);
        $hello = $group_stmt->execute();
        $records = $group_stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->records = $hello;
        if(count($records) > 0){
            $response->groupExist = TRUE;
            $response->group_id = $records[0]["id"];
        }else{
            $sql = "INSERT INTO `group` (`group_no`,`group_topic`,`course_id`) VALUES (:group_no,:group_topic,:course_id)";
            $stmt = $core->dbh->prepare($sql);
//            $group_no = $group->getGroupNo();
//            $group_topic = $group->getGroupTopic();
            $stmt->bindParam("group_no", $group_no);
            $stmt->bindParam("group_topic", $group_topic);
            $stmt->bindParam("course_id", $course_id);
            $stmt->execute();
            $response->groupExist = FALSE;
            $response->group_id = $core->dbh->lastInsertId();
        }
        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
        return json_encode($response);
    }
}

function addStudent($student, $data)
{
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $sql = "INSERT INTO `student` ( `first_name`, `last_name`, `email_id`,`user_id`) VALUES (:first_name, :last_name, :email_id,:user_id)"; //Insert record in to student table
        $stmt = $core->dbh->prepare($sql);
        $first_name = $student->getFirstName();
        $last_name = $student->getLastName();
        $email_id = $student->getEmailId();
        $user_id = $data;
        $stmt->bindParam("first_name", $first_name);
        $stmt->bindParam("last_name", $last_name);
        $stmt->bindParam("email_id", $email_id);
        $stmt->bindParam("user_id", $user_id);
        $response = new stdClass();
        $response->success = $stmt->execute();
//        $stmt2 = $core->dbh->prepare("SELECT LAST_INSERT_ID()");
        $response->data = $core->dbh->lastInsertId();
        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}




function addCourseStudent($course_id_global,$student_id) {
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $sql = "INSERT INTO `course_student` (`course_id`, `student_id`) VALUES"
            . "(:course_id, :student_id)"; //Insert record in to course_student table
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("course_id", $course_id_global);
        $stmt->bindParam("student_id", $student_id);;
        $response = new stdClass();
        $response->success = $stmt->execute();
        $response->data = $course_id_global." ".$student_id ;
        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}


function addGroupStudent($group_id,$student_id) {
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $sql = "INSERT INTO `group_student` (`group_id`, `student_id`) VALUES"
            . "(:group_id, :student_id)"; //Insert record in to group_student table
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("group_id", $group_id);
        $stmt->bindParam("student_id", $student_id);;
        $response = new stdClass();
        $response->success = $stmt->execute();
        $response->data = 0;
        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

function addUser($user,$user_type)
{
    $core = Core::getInstance();
    $sql = "INSERT INTO `user_account` (`user_name`, `password`, `role`) VALUES (:user_name ,:password, :role)"; //Insert record in to student table
    $stmt = $core->dbh->prepare($sql);
    $user_name = $user->getUserName();
    $password = $user->getPassword();
    $stmt->bindParam("user_name", $user_name);
    $stmt->bindParam("password", $password);
    $stmt->bindParam("role", $user_type);
    $response = new stdClass();

    $response->success = $stmt->execute();
    $response->data = $core->dbh->lastInsertId();
//    $stmt2 = $core->dbh->prepare("SELECT LAST_INSERT_ID()");
//    $response->data = $stmt2->execute();
    return $response;
}




function isUserExist($user_name)
{
    $app = \Slim\Slim::getInstance();
    $response = new stdClass();
    try {
        $core = Core::getInstance();
//        $sql = "SELECT `id` FROM `student` WHERE `user_id` in (select id from user_account WHERE user_name=:user_name)";
        $sql = "SELECT `id` FROM `student` WHERE `user_id` in (select id from user_account WHERE user_name=:user_name)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($records)>0){
                $response->userExist = TRUE;
                $response->id = $records[0]["id"];
            }else{
                $response->userExist = FALSE;
//                $sql = "INSERT INTO `group` (`group_no`,`group_topic`,`course_id`) VALUES (:group_no,:group_topic,:course_id)";
//                $stmt = $core->dbh->prepare($sql);
//                $group_no = $group->getGroupNo();
//                $group_topic = $group->getGroupTopic();
//                $stmt->bindParam("group_no", $group_no);
//                $stmt->bindParam("group_topic", $group_topic);
//                $stmt->bindParam("course_id", $course_id);
//                $response->groupExist = FALSE;
//                $response->group_id = $core->dbh->lastInsertId();
            }
//            $student_id = $records[0];
//            $result->id = $student_id;
//            $result->id = (int)($student_id["id"]);
//            $result->count = count($records);
//            return (int)$student_id;
            return $response;
        } else {
            return false;
        }
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

function isCourseExist($course)
{
    try {
        $courseExistByName = isCourseExistByName($course->getCourseName());
        $courseExistByCRN = isCourseExistByCRN($course->getCourseCrn());
        $response = new stdClass();
        $response->success = FALSE;
        $response->data = "course does not exist";
        $response->flag = 0;
        if ($courseExistByName > 0) {
            $response->success = FALSE;
            $response->course_exist = TRUE;
            $response->data = "Course name already exist, but CRN does not match. "
                . "If you want to add students to existing course and click on add button.";
            $response->flag = 1;
            if ($courseExistByCRN) {
//                $response->success = TRUE;
                $response->success = FALSE;
                $response->course_exist = TRUE;
                $response->data = "course already exist.If you want to add students to existing course and click on add button.";
                $response->flag = 1;
            }
        } else if ($courseExistByCRN > 0) {
            $response->success = FALSE;
            $response->course_exist = TRUE;
            $response->data = "Course CRN already exist, but course name doesn't exist."
                . "If you want to add students to existing course and click on add button.";
            $response->flag = 1;
        } else {
            $response->success = TRUE;
            $response->course_exist = FALSE;
            $response->data = "course does not exist";
        }
        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}


//Edit faculty Data
function editCourseData(){
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $json = $app->request->getBody();
        $postData = json_decode($json, true); // parse the JSON into an assoc. array
        $course_name = $postData['course_name'];
        $trimester = $postData['trimester'];
        $course_crn = $postData['course_crn'];
        $original_course_crn = $postData['original_course_crn'];
        $course = new Course($course_crn, $course_name, $trimester);
        $response = new stdClass();
        if (!$course_crn || !$course_name ||  !$trimester || !$original_course_crn) {
            throw new Exception('Missing course $original_course_crn or course_name  or trimester of course');
        } else {
            if ($original_course_crn != $course_crn) {
                if ( isCourseExistByCRN($course_crn)>0) {
                    $response->data = "Course name/crn already exist.";
                    // throw new Exception("Cannot Update! Because course_crn already Exists", 400);
                } else {
                    $response->data = "Course name/crn doesn't exist.";
                    // throw new Exception("Cannot Update! Because course_crn already Exists", 400);
                    $response = updateCourse($course, $original_course_crn);
                }
            } else {
                $response = updateCourse($course, $course_crn);
            }
        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $response->success = FALSE;
        $response->data = $ex->getMessage();
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

function updateCourse($course, $original_course_crn) {
    $app = \Slim\Slim::getInstance();
    $response = new stdClass();
    try {
        $core = Core::getInstance();
        $sql = "UPDATE course SET `course_crn`=:course_crn, `course_name`=:course_name, trimester="
            . ":trimester WHERE `course_crn`=:original_course_crn"; //Update record in to course table
        $stmt = $core->dbh->prepare($sql);

        $course_crn = $course->getCourseCrn();
        $course_name = $course->getCourseName();
        $trimester = $course->getCourseTrimester();
        
        $stmt->bindParam("original_course_crn", $original_course_crn);
        $stmt->bindParam("course_crn", $course_crn);
        $stmt->bindParam("course_name", $course_name);
        $stmt->bindParam("trimester", $trimester);
    
        $response->success = $stmt->execute();
        $response->data = 0;
        return $response;
    } catch (Exception $ex) {
        $response->errorMessage = $ex->getMessage();
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
//    $response->success = FALSE;
//    $response->data = 0;
//    return $response;
}




function isCourseExistByName($name)
{
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $sql = "SELECT `course_name`, `course_crn` FROM `course` WHERE `course_name` LIKE :name";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("name", $name);
        if ($stmt->execute()) {
            return count($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            return false;
        }
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

function isCourseExistByCRN($CRN)
{
    $app = \Slim\Slim::getInstance();
    try {

        $core = Core::getInstance();
        $sql = "SELECT `course_name`, `course_crn` FROM `course` WHERE `course_crn` LIKE :crn";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("crn", $CRN);
        if ($stmt->execute()) {
            return count($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            return false;
        }
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}
function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

//removing course from course data
$removeCourseData = function() use($app) {
    $json = $app->request->getBody();
    $postData = json_decode($json, true); // parse the JSON into an assoc. array
    $course_crn = $postData['course_crn'];
    try {
        $core = Core::getInstance();
        $sql = "delete from course where course_crn=:course_crn";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("course_crn", $course_crn);
        $response = new stdClass();
        $response->success = $stmt->execute();
        $response->data = 0;
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
};

//function getStudentsByCourse($course_crn){
//    try {
//        // $faculty = "faculty";
//        $core = Core::getInstance();
//        $sql = "SELECT first_name,last_name,email_id FROM `student` WHERE id in (select student_id from `course_student` WHERE course_id in (select id from `course` WHERE course_crn=:course_crn))";
//        $stmt = $core->dbh->prepare($sql);
//        $stmt->bindParam("course_crn", $course_crn);
//        $response = new stdClass();
//        if ($stmt->execute()) {
//            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
//            $response->success = count($records) > 0;
//            $response->info = $response->success ? $records : 0;
//
//        } else {
//            $response->success = FALSE;
//            $response->info = 0;
//
//        }
////        echo $course_crn;
//        echo json_encode($response);
//    } catch (Exception $ex) {
//        $app->response()->status(400);
//        $app->response()->header('X-Status-Reason', $ex->getMessage());
//    }
//}


function getStudentsByCourse($course_crn,$faculty_user_name){
    $app = \Slim\Slim::getInstance();
    try {
        // $faculty = "faculty";
        $core = Core::getInstance();
        $sql = "SELECT first_name,last_name,email_id,id FROM `student` WHERE id in (select student_id from `course_student` WHERE course_id in (select id from `course` WHERE course_crn=:course_crn))";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("course_crn", $course_crn);


        $group_sql = "select id,group_topic,group_no from `group` WHERE course_id in (select id from `course` WHERE course_crn=:course_crn)";
        $group_stmt = $core->dbh->prepare($group_sql);
        $group_stmt->bindParam("course_crn", $course_crn);

        $response = new stdClass();
        $total_student_records = [];
        if ($group_stmt->execute()) {
            $groupRecords = $group_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($groupRecords as &$group){
                $student_sql = "SELECT id,first_name,last_name,email_id FROM student WHERE id in (select student_id from `group_student` WHERE group_id=:group_id)";
                $student_stmt = $core->dbh->prepare($student_sql);
                $student_stmt->bindParam("group_id", $group['id']);
                if ($student_stmt->execute()){
                    $student_records = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($student_records as &$student1){
                        $evaluation_array = [];
                        foreach ($student_records as &$student2){

                            if($student1['id']!=$student2['id']){
                                if(checkStudentEvaluation($group['id'],$student2['id'],$student1['id'])== TRUE){
                                    array_push($evaluation_array,"TRUE");
                                }
                            }
                        }
                        if(count($evaluation_array)== count($student_records)-1){
                            $student1['evaluate'] = TRUE;
                            $student1['group_topic'] = $group['group_topic'];
                        }else{
                            $student1['evaluate'] = FALSE;
                            $student1['group_topic'] = $group['group_topic'];
                        }
                    }

                }else{

                }
                $total_student_records = array_merge($total_student_records,$student_records);
//                $total_student_records = $total_student_records + $student_records ;
            }
            $response->success = count($total_student_records) > 0;
            $response->info = $response->success ? $total_student_records : 0;

        } else {
            $response->success = FALSE;
            $response->info = 0;

        }
//        echo $course_crn;
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

function viewGroupsByCourse($course_crn){
    try {
        // $faculty = "faculty";
        $core = Core::getInstance();
        $sql = "SELECT group_no,group_topic FROM `group` WHERE course_id in (select id from `course` WHERE course_crn=:course_crn)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("course_crn", $course_crn);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            $response->info = $response->success ? $records : 0;

        } else {
            $response->success = FALSE;
            $response->info = 0;

        }
//        echo $course_crn;
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

$addQuestionsToCourse = function () use ($app) {
    $response = new stdClass();
    $json = $app->request->getBody();
    $postData = json_decode($json, true); // parse the JSON into an assoc. array
    $course_crn = $postData['course_crn'];
    $jsonString = $postData['question_data'];
    $core = Core::getInstance();
    try {
    $course_sql = "select id from `course` WHERE course_crn=:course_crn";
    $course_stmt = $core->dbh->prepare($course_sql);
    $course_stmt->bindParam("course_crn", $course_crn);
    $course_stmt->execute();
    $records = $course_stmt->fetchAll(PDO::FETCH_ASSOC);
    $course =  $records[0];
    $course_id = $course['id'];
    $delete_sql = "DELETE FROM `question_bank` WHERE course_id=:course_id";
    $delete_stmt = $core->dbh->prepare($delete_sql);
        $delete_stmt->bindParam("course_id", $course_id);
        $response->test = $delete_stmt->execute();
        foreach (json_decode($jsonString) as $ques) {
            $sql = "INSERT INTO question_bank (question,max_rating,course_id) VALUES (:question,:max_rating,:course_id)";
            $stmt = $core->dbh->prepare($sql);
            $stmt->bindParam("question", $ques->question);
            $stmt->bindParam("max_rating", $ques->max_rating);
            $stmt->bindParam("course_id", $course_id);
            if ($stmt->execute()) {
//                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response->success = TRUE;
            } else {
                $response->success = FALSE;
            }
        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $response->success = FALSE;
        $response->data = $ex->getMessage();
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
        echo json_encode($response);
    }
};


function getQuestionsByCourse($course_crn){
    try {
        // $faculty = "faculty";
        $core = Core::getInstance();
        $sql = "SELECT question,max_rating FROM `question_bank` WHERE course_id in (select id from `course` WHERE course_crn=:course_crn)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("course_crn", $course_crn);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            $response->info = $response->success ? $records : 0;

        } else {
            $response->success = FALSE;
            $response->info = 0;

        }
//        echo $course_crn;
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

//Edit faculty Data
function getCourseName($course_crn){
    $app = \Slim\Slim::getInstance();
     $response = new stdClass();
      $core = Core::getInstance();
    try {
        $sql = "SELECT course_name FROM `course` WHERE course_crn=:course_crn";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("course_crn", $course_crn);
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            $response->info = $response->success ? $records[0] : 0;

        } else {
            $response->success = FALSE;
            $response->info = 0;

        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $response->success = FALSE;
        $response->data = $ex->getMessage();
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

//For the url http://localhost/OpinionBox/services/index.php/faculty/login
$app->post('/faculty/login', $loginFaculty);
//For the url http://localhost/OpinionBox/services/index.php/coursesByFaculty/facultyusername
$app->get('/coursesByFaculty/:fname', 'getCoursesByFaculty');
//For the url http://localhost/OpinionBox/services/index.php/addStudent
$app->post('/addCourse', $addCourse);
//For the url http://localhost/OpinionBox/services/index.php/faculty/editCourse
$app->post('/faculty/editCourseData', 'editCourseData');
//For the url http://localhost/OpinionBox/services/index.php/faculty/removeCourse
$app->post('/faculty/removeCourseData', $removeCourseData);
//For the url http://localhost/OpinionBox/services/index.php/faculty/viewGroupsByCourse/:course_crn
$app->get('/viewStudentsByCourse/:course_crn','getStudentsByCourse');
//For the url http://localhost/OpinionBox/services/index.php/faculty/viewGroupsByCourse/:course_crn
$app->get('/viewGroupsByCourse/:course_crn','viewGroupsByCourse');
//For the url http://localhost/OpinionBox/services/index.php/addQuestionsToCourse/
$app->post('/addQuestionsToCourse',$addQuestionsToCourse);
//For the url http://localhost/OpinionBox/services/index.php/getQuestionsByCourse/:course_crn
$app->get('/getQuestionsByCourse/:course_crn','getQuestionsByCourse');
//For the url http://localhost/OpinionBox/services/index.php/faculty/editCourse
$app->get('/getCourseName/:course_crn', 'getCourseName');
?>