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
//
                        $courseData = addCourse($course, $faculty_user_name);
                        $course_id = $courseData->data;
                        $course_id_global = $course_id;
                        $response->data =  $courseData;
                    }
                    foreach (json_decode($jsonString) as $student) {
                        $result = isUserExist($student->user_name);
                        $response->data =  $result;
                        if ($result->count > 0) {
                            addCourseStudent($course_id_global,$result->id);

                        }
                        else {
                            $userData = addUser(new UserAccount($student->user_name, $student->password));
                            if ($userData->success == TRUE) {
                                $studentData = addStudent(new Student($student->first_name, $student->last_name, $student->email_id), $userData->data);
                                $student_id = $studentData->data;
                                addCourseStudent($course_id_global,$student_id);
                            }

                        }
                    }

                    $response->success = TRUE;
                    $response->data = $courseExist->data;
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
        $faculty_id =  $records[0] ;
        $faculty_int = (int)$faculty_id;
        $sql = "INSERT INTO course (course_crn,course_name,trimester,faculty_id) VALUES (:course_crn,:course_name,:course_trimester,:faculty_id)";
        $stmt = $core->dbh->prepare($sql);
        $course_crn = $course->getCourseCrn();
        $course_name = $course->getCourseName();
        $course_trimester = $course->getCourseTrimester();
        $stmt->bindParam("course_crn", $course_crn);
        $stmt->bindParam("course_trimester", $course_trimester);
        $stmt->bindParam("course_name", $course_name);
        $stmt->bindParam("faculty_id", $faculty_int);
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
        $response->data = 0;
        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}


function addUser($user)
{
    $core = Core::getInstance();
    $sql = "INSERT INTO `user_account` (`user_name`, `password`, `role`) VALUES (:user_name ,:password, :role)"; //Insert record in to student table
    $stmt = $core->dbh->prepare($sql);
    $user_name = $user->getUserName();
    $password = $user->getPassword();
    $stmt->bindParam("user_name", $user_name);
    $stmt->bindParam("password", $password);
    $stmt->bindParam("role", 'student');
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
    try {
        $core = Core::getInstance();
        $sql = "SELECT `id` FROM `student` WHERE `user_id` in (select id from user_account WHERE user_name=:user_name)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $result = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $student_id = $records[0];
            $result->id = (int)($student_id["id"]);
            $result->count = count($records);
//            return (int)$student_id;
            return $result;
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
            $response->data = "Course name already exist, but CRN does not match. "
                . "If you want to add students to existing course go to manage tab and then students sub tab.";
            $response->flag = 1;
            if ($courseExistByCRN) {
//                $response->success = TRUE;
                $response->success = FALSE;
                $response->data = "course already exist.If you want to add students to existing course go to manage tab and then students sub tab.";
                $response->flag = 1;
            }
        } else if ($courseExistByCRN > 0) {
            $response->success = FALSE;
            $response->data = "Course CRN already exist, but course name doesn't exist."
                . "If you want to add students to existing course go to manage tab and then students sub tab.";
            $response->flag = 1;
        } else {
            $response->success = TRUE;
            $response->data = "course does not exist";
        }
        return $response;
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
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

//For the url http://localhost/OpinionBox/services/index.php/faculty/login
$app->post('/faculty/login', $loginFaculty);
//For the url http://localhost/OpinionBox/services/index.php/coursesByFaculty/facultyusername
$app->get('/coursesByFaculty/:fname', 'getCoursesByFaculty');
//For the url http://localhost/TimeClock/services/index.php/addStudent
$app->post('/addCourse', $addCourse);

?>