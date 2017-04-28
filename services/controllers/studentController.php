<?php



$loginStudent = function () use ($app) {
    try {
        $postData = $app->request->post();
        $user_name = $postData['username'];
        $password = $postData['password'];
        $core = Core::getInstance();
        $sql = "SELECT first_name,last_name,email_id FROM `student` WHERE user_id in (SELECT id FROM user_account WHERE user_name=:user_name AND password =:password)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("password", $password);
        $response = new stdClass();
        $response->user_type = "student";
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

        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
};

function getCoursesByStudent($sname)
{
    try {
        // $faculty = "faculty";
        $core = Core::getInstance();
        $sql = "Select firstset.group_no,firstset.group_topic,secondset.course_name,firstset.group_id from
(SELECT course_id,group_no, group_topic,group.id as group_id FROM  `group` WHERE id IN (SELECT group_id FROM group_student WHERE student_id IN (SELECT id FROM student WHERE user_id IN (SELECT id FROM user_account WHERE user_name=:sname)))) as firstset
INNER JOIN
(select course_name,id from `course`) as secondset
ON firstset.course_id = secondset.id";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("sname", $sname);
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


function getStudentsInGroup($username,$groupId)
{
    $app = \Slim\Slim::getInstance();
    try {

        $core = Core::getInstance();
        $sql = "SELECT first_name,last_name,id FROM `student` WHERE id in (SELECT student_id FROM `group_student` WHERE group_id=:group_id AND student_id != (SELECT id FROM `student` WHERE user_id in (SELECT id FROM `user_account` WHERE user_name=:user_name)))";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("group_id", $groupId);
        $stmt->bindParam("user_name", $username);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            foreach ($records as &$student){
                $student['evaluate'] =  checkEvaluation($groupId,$student['id'],$username);
            }
            $response->info = $response->success ? $records : 0;
            $response->success = TRUE;
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

function authorization($username,$groupId)
{
    $app = \Slim\Slim::getInstance();
    try {

        $core = Core::getInstance();
        $sql = "SELECT * FROM `group_student` WHERE group_id=:group_id AND student_id in (SELECT id FROM `student` WHERE user_id in (SELECT id FROM `user_account` WHERE user_name=:user_name))";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("group_id", $groupId);
        $stmt->bindParam("user_name", $username);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
        } else {
            $response->success = FALSE;
            $response->data = 0;
        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}


function authorization2($username,$groupId,$studentid)
{
    $app = \Slim\Slim::getInstance();
    try {

        $core = Core::getInstance();
        $sql = "SELECT group_id FROM `group_student` WHERE group_id=:group_id AND student_id in (SELECT id FROM `student` WHERE user_id in (SELECT id FROM `user_account` WHERE user_name=:user_name))";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("group_id", $groupId);
        $stmt->bindParam("user_name", $username);
        $sql2 = "SELECT group_id FROM `group_student` WHERE group_id=:group_id AND student_id=:student_id";
        $stmt2 = $core->dbh->prepare($sql2);
        $stmt2->bindParam("group_id", $groupId);
        $stmt2->bindParam("student_id", $studentid);
        $response = new stdClass();
        if ($stmt->execute() and $stmt2->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $records2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            $record0 = $records[0];
            $records20 = $records2[0];
            $response->success = $record0['group_id'] == $records20['group_id'];
        } else {
            $response->success = FALSE;
            $response->data = 0;
        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}




function dueDateStudent($groupId)
{
    $app = \Slim\Slim::getInstance();
    try {
        date_default_timezone_set("America/Chicago");
        $core = Core::getInstance();
        $sql = "SELECT due_date FROM `course` WHERE id in (SELECT course_id FROM `group` WHERE id=:group_id)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("group_id", $groupId);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            $response->info = $response->success ? $records[0] : 0;
            $response->server_date = date("Y-m-d");
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


function checkEvaluation($group_id,$response_to_id,$user_name)
{
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();

        $sql = "SELECT * FROM `response_bank` WHERE response_by_id in (SELECT id FROM `student` WHERE user_id in (SELECT id FROM `user_account` WHERE user_name=:user_name )) AND response_to_id=:response_to_id AND question_id in (SELECT id FROM `question_bank` WHERE course_id in (SELECT course_id FROM `group` WHERE id=:group_id)) ";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("response_to_id", $response_to_id);
        $stmt->bindParam("group_id", $group_id);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->evaluate = count($records) > 0;

        } else {
            $response->evaluate = FALSE;
        }
        return $response->evaluate;

    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}

function getQuestionsInGroup($studentId,$groupId)
{
    try {

        $core = Core::getInstance();
        $sql = "SELECT id,question,max_rating FROM `question_bank` WHERE course_id in (SELECT course_id FROM `group` WHERE id=:group_id)";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("group_id", $groupId);
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

$insertResponses = function ($username,$studentId) use ($app)
//function insertResponses($username,$studentId)
{
    $response = new stdClass();
    $json = $app->request->getBody();
    $postData = json_decode($json, true); // parse the JSON into an assoc. array
    try {
        $core = Core::getInstance();
        $student_sql = "SELECT id FROM `student` WHERE user_id in (SELECT id FROM user_account WHERE user_name=:user_name)";
        $student_stmt = $core->dbh->prepare($student_sql);
        $student_stmt->bindParam("user_name", $username);
        $student_stmt->execute();
        $records = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
        $record=  $records[0] ;
        $response_by_id = $record['id'];
        foreach ($postData as $key => $value){
            $question_id = $key;
            $answer = $value;
            $sql = "INSERT INTO `response_bank` (response,question_id,response_by_id,response_to_id) VALUES (:response,:question_id,:response_by_id,:response_to_id)";
            $stmt = $core->dbh->prepare($sql);
            $stmt->bindParam("response", $answer);
            $stmt->bindParam("question_id", $question_id);
            $stmt->bindParam("response_by_id", $response_by_id);
            $stmt->bindParam("response_to_id", $studentId);
            if ($stmt->execute()) {
                $response->success = TRUE;
            } else {
                $response->success = FALSE;
                $response->info  = "Response submission failed";
            }
        }

        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
};
//For the url http://localhost/OpinionBox/services/index.php/student/login
$app->post('/student/login', $loginStudent);
//For the url http://localhost/OpinionBox/services/index.php/coursesByStudent/studentusername
$app->post('/coursesByStudent/:sname', 'getCoursesByStudent');
//For the url http://localhost/OpinionBox/services/index.php/studentsInGroup/:username/:groupId
$app->post('/studentsInGroup/:username/:groupId', 'getStudentsInGroup');
//For the url http://localhost/OpinionBox/services/index.php/authorization/:username/:groupId
$app->post('/authorization/:username/:groupId', 'authorization');
$app->post('/authorization2/:username/:groupId/:studentId', 'authorization2');
//For the url http://localhost/OpinionBox/services/index.php/quesionsInGroup/:studentId/:groupId
$app->post('/questionsInGroup/:studentId/:groupId', 'getQuestionsInGroup');
//For the url http://localhost/OpinionBox/services/index.php/responsesForQuestions/:responseStudent/:username/:studentId
$app->post('/responsesForQuestions/:username/:studentId', $insertResponses);
//For the url http://localhost/OpinionBox/services/index.php/dueDateStudent/:groupId
$app->post('/dueDateStudent/:groupId', 'dueDateStudent');
?>