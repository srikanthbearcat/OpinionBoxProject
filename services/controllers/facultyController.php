<?php



$loginFaculty = function () use ($app) {
    try {
        $postData = $app->request->post();
        $user_name = $postData['username'];
        $password = $postData['password'];
        $core = Core::getInstance();
        $sql = "SELECT first_name,last_name,user_name,email FROM user_account WHERE user_name=:user_name AND password =:password";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("password", $password);
        $response = new stdClass();
        $response->user_type = "faculty";
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

        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
};

function getCoursesByFaculty($fname) {
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

//For the url http://localhost/OpinionBox/services/index.php/faculty/login
$app->post('/faculty/login', $loginFaculty);
//For the url http://localhost/OpinionBox/services/index.php/coursesByFaculty/facultyusername
$app->get('/coursesByFaculty/:fname', 'getCoursesByFaculty');
?>