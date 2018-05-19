<?php
 
//including the required files
require_once '../includes/DbOperation.php';
require '../vendor/autoload.php';
 
\Slim\Slim::registerAutoloader();
 
//Creating a slim instance
$app = new \Slim\Slim();
 
//Method to display response
function echoResponse($status_code, $response)
{
    //Getting app instance
    $app = Slim\Slim::getInstance();
 
    //Setting Http response code
    $app->status($status_code);
 
    //setting response content type to json
    $app->contentType('application/json');
 
    //displaying the response in json format
    echo json_encode($response);
}
 
 
function verifyRequiredParams($required_fields)
{
    //Assuming there is no error
    $error = false;
 
    //Error fields are blank
    $error_fields = "";
 
    //Getting the request parameters
    $request_params = $_REQUEST;
 
    //Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        //Getting the app instance
        $app = Slim\Slim::getInstance();
 
        //Getting put parameters in request params variable
        parse_str($app->request()->getBody(), $request_params);
    }
 
    //Looping through all the parameters
    foreach ($required_fields as $field) {
 
        //if any requred parameter is missing
        if (!isset($request_params[$field])) {
            //error is true
            $error = true;
 
            //Concatnating the missing parameters in error fields
            $error_fields .= $field . ', ';
        }
    }
 
    //if there is a parameter missing then error is true
    if ($error) {
        //Creating response array
        $response = array();
 
        //Getting app instance
        $app = Slim\Slim::getInstance();
 
        //Adding values to response array
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';

        //Displaying response with error code 400
        echoResponse(400, $response);
 
        //Stopping the app
        $app->stop();
    }
}
 
//Method to authenticate a student 
function authenticateStudent(\Slim\Route $route)
{
    //Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = Slim\Slim::getInstance();
 
    //Verifying the headers
    if (isset($headers['Authorization'])) {
 
        //Creating a DatabaseOperation boject
        $db = new DbOperation();
 
        //Getting api key from header
        $api_key = $headers['Authorization'];
 
        //Validating apikey from database
        if (!$db->isValidStudent($api_key)) {
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}

//this method will create a student
//the first parameter is the URL address that will be added at last to the root url
//The method is post
$app->post('/createUser', function () use ($app) {
 
    //Verifying the required parameters
    verifyRequiredParams(array('name', 'username', 'email', 'password'));
 
    //Creating a response array
    $response = array();
 
    //reading post parameters
    $name = $app->request->post('name');
    $username = $app->request->post('username');
    $password = $app->request->post('password');
    $email = $app->request->post('email');
 
    //Creating a DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->createUser($name,$username,$email,$password);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "You are successfully registered";
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Sorry, this email already existed";
        echoResponse(200, $response);
    }
});

//Login request
$app->post('/userLogin',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','password'));
 
    //getting post values
    $username = $app->request->post('username');
    $password = $app->request->post('password');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();
 
    //If username password is correct
    if($db->userLogin($username,$password)){
 
        //Getting user detail
        $user = $db->getUser($username);
 
        //Generating response
        $response['error'] = false;
        $response['id'] = $user['id'];
        $response['name'] = $user['UName'];
        $response['email'] = $user['email'];
        $response['username'] = $user['username'];
        $response['apikey'] = $user['apiKey'];
 
    }else{
        //Generating response
        $response['error'] = true;
        $response['message'] = "Invalid username or password";
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/verifyUser',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','apikey'));
 
    //getting post values
    $username = $app->request->post('username');
    $apikey = $app->request->post('apikey');
 
    //Creating DbOperation object
    $db = new DbOperation();
    $response = array();
 
    //Calling the method createStudent to add student to the database
    $res = $db->verifyUser($username,$apikey);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "You are successfully verified";
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while verifying you";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Already verified";
        echoResponse(200, $response);
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "Wrong credentials";
        echoResponse(200, $response);
    }
});

$app->post('/showRestaurants',function() use ($app){
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $restaurants = $db->showRestaurants();

    $i = 0;
    foreach($restaurants as $k=>$value){
        $response[$i]["id"] = $value[0];
        $response[$i]["name"] = $value[1];
        $response[$i]["email"] = $value[2];
        $response[$i]["contact"] = $value[3];
        $response[$i]["likes"] = $value[4];
        $response[$i]["address"] = $value[5];
        $response[$i]["stars"] = $value[6];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/addRestaurant',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('name','email','contact','address'));
 
    //getting post values
    $name = $app->request->post('name');
    $address = $app->request->post('address');
    $email = $app->request->post('email');
    $contact = $app->request->post('contact');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->addRestaurant($name,$email,$contact,$address);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "Your Restaurant is successfully added";
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while adding your Restaurant";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Sorry, this email already existed";
        echoResponse(200, $response);
    }
});

$app->post('/likeRestaurant',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','rid'));
 
    //getting post values
    $username = $app->request->post('username');
    $rid = $app->request->post('rid');
 
    //Creating DbOperation object
    $db = new DbOperation();

    $response = array();
 
    //Calling the method createStudent to add student to the database
    $res = $db->likeRestaurant($username,$rid);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "Success";
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Error";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Restaurant already liked";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/showBooks',function() use ($app){
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $books = $db->showBooks();
    // $response['books'] = $books;
    $i = 0;
    foreach($books as $k=>$value){
        $response[$i]["id"] = $value[0];
        $response[$i]["name"] = $value[1];
        $response[$i]["author"] = $value[2];
        $response[$i]["publication"] = $value[3];
        $response[$i]["title"] = $value[4];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/likeBook',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','bid'));
 
    //getting post values
    $username = $app->request->post('username');
    $bid = $app->request->post('bid');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->likeBook($username,$bid);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "Success";
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Error";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Book already liked";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/bookmark',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','bid'));
 
    //getting post values
    $username = $app->request->post('username');
    $bid = $app->request->post('bid');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->bookmark($username,$bid);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "Success";
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Error";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Book already bookmarked";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/userLikedBooks',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username'));
    
    //getting post values
    $username = $app->request->post('username');
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $bookIDs= $db->userLikedBooks($username);
    $i = 0;
    foreach($bookIDs as $k=>$value){
        $bookID = $value[0];
        $book = $db->getBook($bookID);
        $response[$i]["id"] = $book["id"];
        $response[$i]["name"] = $book["BName"];
        $response[$i]["author"] = $book["author"];
        $response[$i]["publication"] = $book["publication"];
        $response[$i]["title"] = $book["title"];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->run();