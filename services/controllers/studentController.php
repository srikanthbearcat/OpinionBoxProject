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
        $sql = "Select firstset.group_no,firstset.group_topic,secondset.course_name from
(SELECT course_id,group_no, group_topic FROM  `group` WHERE id IN (SELECT group_id FROM group_student WHERE student_id IN (SELECT id FROM student WHERE user_id IN (SELECT id FROM user_account WHERE user_name=:sname)))) as firstset
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
//For the url http://localhost/OpinionBox/services/index.php/student/login
$app->post('/student/login', $loginStudent);
//For the url http://localhost/OpinionBox/services/index.php/coursesByStudent/studentusername
$app->post('/coursesByStudent/:sname', 'getCoursesByStudent');
//For the url http://localhost/OpinionBox/services/index.php/studentsInGroup/:username/:groupId
$app->post('/studentsInGroup/:username/:groupId', 'getStudentsInGroup');
//For the url http://localhost/OpinionBox/services/index.php/quesionsInGroup/:studentId/:groupId
$app->post('/questionsInGroup/:studentId/:groupId', 'getQuestionsInGroup');
?>