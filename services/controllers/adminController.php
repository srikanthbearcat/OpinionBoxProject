<?php



$loginAdmin = function () use ($app) {
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
        $response->user_type = "admin";
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
            $sql = "INSERT INTO user_account (first_name,last_name,user_name,email,password)  VALUES (:first_name,:last_name,:user_name,:email,:password)";
            $stmt = $core->dbh->prepare($sql);
            $stmt->bindParam("first_name", $first_name);
            $stmt->bindParam("last_name", $last_name);
            $stmt->bindParam("user_name", $user_name);
            $stmt->bindParam("password", $password);
            $stmt->bindParam("email", $email);

            $response->success = $stmt->execute();
            $response->data = 0;
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
        $sql = "select user_name from user_account where user_name=:user_name";
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
        $sql = "SELECT * FROM user_account";
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
        $password = $postData['password'];
        $email = $postData['email'];
        $user_name = $postData['user_name'];
        $original_user_name = $postData['original_user_name'];
        $user = new Users($user_name, $first_name, $last_name, $email, $password);
        $response = new stdClass();
        if (!$user_name || !$first_name ||  !$last_name ||  !$email ||  !$password) {
            throw new Exception('Missing faculty $original_user_name or email  or password or email of faculty');
        } else {
            if ($original_user_name != $user_name) {
                $facultyExist = isFacultyExist($user_name);
                if ($facultyExist > 0) {
                    throw new Exception("Cannot Update! Because username already Exists", 400);
                } else {
                    $response = updateFaculty($user, $original_user_name);
                }
            } else {
                $response = updateFaculty($user, $original_user_name);
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

function updateFaculty($user, $original_user_name) {
    $app = \Slim\Slim::getInstance();
    $response = new stdClass();
    try {
        $core = Core::getInstance();
        $sql = "UPDATE user_account SET `user_name`=:user_name, `first_name`=:first_name, last_name="
            . ":last_name, `email`=:email, `password`=:password WHERE `user_name`=:original_user_name"; //Insert record in to user_account table
        $stmt = $core->dbh->prepare($sql);

        $user_name = $user->getUsername();
        $first_name = $user->getFirstName();
        $last_name = $user->getLastName();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $stmt->bindParam("original_user_name", $original_user_name);
        $stmt->bindParam("user_name", $user_name);
        $stmt->bindParam("first_name", $first_name);
        $stmt->bindParam("last_name", $last_name);
        $stmt->bindParam("email", $email);
        $stmt->bindParam("password", $password);
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
?>