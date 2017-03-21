<?php



$loginAdmin = function () use ($app) {
   try {
        $postData = $app->request->post();
        $user_name = $postData['username'];
        $password = $postData['password'];
        $core = Core::getInstance();

       $sql = "SELECT first_name,last_name,email_id FROM `admin` WHERE user_id in (SELECT id FROM user_account WHERE user_name=:user_name AND password =:password)";
       $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("password", $password);
        $response = new stdClass();
        $response->user_type = "admin";
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

$addFaculty = function () use ($app) {
    $response = new stdClass();
    try {
//        $postData = $app->request->post();
        $json = $app->request->getBody();
        $postData = json_decode($json, true); // parse the JSON into an assoc. array
        $first_name = $postData['first_name'];
        $last_name = $postData['last_name'];
        $password = $postData['password'];
        $email = $postData['email_address'];
        $user_name = $postData['user_name'];
        $core = Core::getInstance();
        $facultyExist = isFacultyExist($user_name);
        if ($facultyExist > 0) {
            throw new Exception("Username already Exists", 400);
        }else {
            $sql = "INSERT INTO user_account (user_name,password,role)  VALUES (:user_name,:password,'faculty')";
            $stmt = $core->dbh->prepare($sql);
            $stmt->bindParam("user_name", $user_name);
            $stmt->bindParam("password", $password);
            $stmt->execute();
            $user_account_id = $core->dbh->lastInsertId();
            $sql2 = "INSERT INTO faculty (first_name,last_name,email_id,user_id)  VALUES (:first_name,:last_name,:email_id,:user_id)";
            $stmt2 = $core->dbh->prepare($sql2);
            $stmt2->bindParam("first_name", $first_name);
            $stmt2->bindParam("last_name", $last_name);
            $stmt2->bindParam("user_id", $user_account_id);
            $stmt2->bindParam("email_id", $email);
            $response->success = $stmt2->execute();
            $response->data = $user_account_id;
            echo json_encode($response);
        }
    } catch (Exception $ex) {
        $response = $app->response();
        $response->status(400);
        $response->success = false;
        $response->info["reason"] = $ex->getMessage();
        echo json_encode($response);
//        $app->response()->status(400);
//        $app->response()->header('X-Status-Reason', $ex->getMessage());
//        // Append response body
//        $app->response->write('Bar');
//        echo json_encode($response);
    }
};

function isFacultyExist($user_name){
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $sql = "select user_name from user_account where user_name=:user_name AND role='faculty'";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
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
//Get faculty Data
function getFacultyData(){
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $sql = "SELECT first_name,last_name,email_id,user_name FROM `faculty`,`user_account` WHERE  user_account.id=faculty.user_id AND user_account.role='faculty'";
//        $sql = "SELECT first_name,last_name,email_id,user_name FROM `faculty`,`user_account` WHERE user_id in (SELECT id FROM user_account WHERE role='faculty')";
//        $sql = "SELECT * FROM user_account";
        $stmt = $core->dbh->prepare($sql);
        $response = new stdClass();
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records) > 0;
            $response->info = $response->success ? $records : 0;
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
}

//Edit faculty Data
function editFacultyData(){
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $json = $app->request->getBody();
        $postData = json_decode($json, true); // parse the JSON into an assoc. array
        $first_name = $postData['first_name'];
        $last_name = $postData['last_name'];
//        $password = $postData['password'];
        $email_id = $postData['email_id'];
        $user_name = $postData['user_name'];
        $original_user_name = $postData['original_user_name'];
        $faculty = new faculty($first_name, $last_name, $email_id);
        $response = new stdClass();
        if (!$user_name || !$first_name ||  !$last_name ||  !$email_id ) {
            throw new Exception('Missing faculty $original_user_name or email  or password or email of faculty');
        } else {
            if ($original_user_name != $user_name) {
                $facultyExist = isFacultyExist($user_name);
                if ($facultyExist > 0) {
                    throw new Exception("Cannot Update! Because username already Exists", 400);
                } else {
                    $response = updateFaculty($faculty,$user_name, $original_user_name);
                }
            } else {
                $response = updateFaculty($faculty,$user_name, $original_user_name);
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

function updateFaculty($faculty,$user_name,$original_user_name) {
    $app = \Slim\Slim::getInstance();
    $response = new stdClass();
    try {
        $core = Core::getInstance();
        $sql = "UPDATE user_account INNER JOIN faculty ON user_account.id = faculty.user_id SET user_name=:user_name, first_name=:first_name, last_name="
            . ":last_name, email_id=:email_id WHERE user_name=:original_user_name"; //Insert record in to user_account table
        $stmt = $core->dbh->prepare($sql);
        $first_name = $faculty->getFirstName();
        $last_name = $faculty->getLastName();
        $email_id = $faculty->getEmailId();
        $stmt->bindParam("original_user_name", $original_user_name);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("first_name", $first_name);
        $stmt->bindParam("last_name", $last_name);
        $stmt->bindParam("email_id", $email_id);
        $response->success = $stmt->execute();
        $response->data = 0;
        return $response;
    } catch (Exception $ex) {
        $response->errorMessage = $ex->getMessage();
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
    $response->success = FALSE;
    $response->data = 0;
    return $response;
}

$removeFacultyData = function() use($app) {
    $json = $app->request->getBody();
    $postData = json_decode($json, true); // parse the JSON into an assoc. array
    $user_name = $postData['user_name'];
    try {
        $core = Core::getInstance();
        $sql = "delete from user_account where user_name=:user_name";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $response = new stdClass();
        $response->success = $stmt->execute();
        $response->data = 0;
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
};

function Settings() {
    $app = \Slim\Slim::getInstance();
    $response = new stdClass();
    try {
//        $postData = $app->request->post();
        $json = $app->request->getBody();
        $postData = json_decode($json, true); // parse the JSON into an assoc. array
        $password = $postData['current_password'];
        $user_name = $postData['user_name'];
        $new_pwd = $postData['new_password'];
        $core = Core::getInstance();
        $passwordMatch = isPasswordMatch($user_name,$password);
        if ($passwordMatch >0) {
            $sql = "UPDATE user_account set password=:new_pwd WHERE user_name=:user_name && password =:password";
            $stmt = $core->dbh->prepare($sql);
            $stmt->bindParam("user_name", $user_name);
            $stmt->bindParam("password", $password);
            $stmt->bindParam("new_pwd", $new_pwd);
            $response->success = $stmt->execute();
            $response->data = 0;

        }else {

            throw new Exception("User doesn't exist", 400);

        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $response = $app->response();
        $response->status(400);
        $response->success = false;
        $response->info["reason"] = $ex->getMessage();
        echo json_encode($response);
//        $app->response()->status(400);
//        $app->response()->header('X-Status-Reason', $ex->getMessage());
//        // Append response body
//        $app->response->write('Bar');
//        echo json_encode($response);
    }
};
function isPasswordMatch($user_name,$password){
    $app = \Slim\Slim::getInstance();
    try {
        $core = Core::getInstance();
        $sql = "select user_name from user_account where user_name=:user_name && password=:password";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("password", $password);
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

$forgotPIN = function() use($app) {
    try {
        $json = $app->request->getBody();
        $postData = json_decode($json, true); // parse the JSON into an assoc. array
        $email_id = $postData['email_id'];
        $core = Core::getInstance();
        $response = new stdClass();
        $student_details = isExistInTable("student", $email_id);
        $faculty_details = isExistInTable("faculty", $email_id);
        if ($student_details->success) {
            $response->success = updatePinInTable("student", $email_id,$student_details->info);
        } else if ($faculty_details->success) {
            $response->success = updatePinInTable("faculty", $email_id,$faculty_details->info);
        } else {
            $response->success = FALSE;
        }
        echo json_encode($response);
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
};

function isExistInTable($tableName, $email_id) {
    $app = \Slim\Slim::getInstance();
    try {
        $response = new stdClass();
        $core = Core::getInstance();
        $sql = "SELECT * from " . trim($tableName) . " where email_id=:email_id";
        $stmt = $core->dbh->prepare($sql);
        $stmt->bindParam("email_id", $email_id);
        if ($stmt->execute()) {
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->success = count($records)>0;
            $response->info = $records[0];
            return $response;
        } else {
            $response->success = FALSE;
            $response->info = "";
            return $response;
        }
    } catch (Exception $ex) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $ex->getMessage());
    }
}
//For the url http://localhost/OpinionBox/services/index.php/admin/login
$app->post('/admin/login', $loginAdmin);
//For the url http://localhost/OpinionBox/services/index.php/admin/addFaculty
$app->post('/admin/addFaculty', $addFaculty);
//For the url http://localhost/OpinionBox/services/index.php/admin/viewFaculty
$app->post('/admin/getFacultyData', 'getFacultyData');
//For the url http://localhost/OpinionBox/services/index.php/admin/viewFaculty
$app->post('/admin/editFacultyData', 'editFacultyData');
//For the url http://localhost/OpinionBox/services/index.php/admin/removeFaculty
$app->post('/admin/removeFacultyData', $removeFacultyData);
$app->post('/Settings', 'Settings');
$app->post('/forgotPIN', $forgotPIN);
?>