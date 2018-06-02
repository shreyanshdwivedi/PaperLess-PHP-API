<?php
 
//including the required files
require_once 'includes/DbOperation.php';
require 'vendor/autoload.php';
 
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
    verifyRequiredParams(array('name', 'username', 'email', 'password', 'phoneNum', 'dob'));
 
    //Creating a response array
    $response = array();
 
    //reading post parameters
    $name = $app->request->post('name');
    $username = $app->request->post('username');
    $password = $app->request->post('password');
    $email = $app->request->post('email');
    $phoneNum = $app->request->post('phoneNum');
    $dob = $app->request->post('dob');
    
    $target_dir = "uploads/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $filename = basename( $_FILES['image']['name']);
    $path_parts = pathinfo($_FILES["image"]["name"]);
    $image_path = $path_parts['filename'].'_'.date("Y-m-d_h:i:sa").'.'.$path_parts['extension'];
    $target_file = $target_dir.$image_path;

    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $response["error"] = false;
        $uploadOk = 1;
    } else {
        $response["error"] = true;
        $response["imageUpload"] = "File is not an image.";
        $uploadOk = 0;
    }

    if(($response["error"] == false) && ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")) {
        
        $response["error"] == true;
        $response["imageUpload"] = "Sorry, only JPG, JPEG, PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $response["error"] = false;
            $response["imageUpload"] = "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
            $img = $target_file;
        } else {
            $response["error"] = true;
            $response["imageUpload"] = "Sorry, there was an error uploading your file.";
        }
    }
 
    //Creating a DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->createUser($name,$username,$email,$password,$img,$phoneNum,$dob);
 
    //If the result returned is 0 means success
    if ($res == 0 && $img == $target_file) {
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
    } else {
        $response["error"] = true;
        $response["message"] = $res;
        echoResponse(200, $response);
    }
});

//Login request
$app->post('/userLogin',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('email','password'));
 
    //getting post values
    $email = $app->request->post('email');
    $password = $app->request->post('password');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();
 
    //If username password is correct
    if($db->userLogin($email,$password)){
 
        //Getting user detail
        $user = $db->getUserByEmail($email);
 
        //Generating response
        $response['error'] = false;
        $response['id'] = $user['id'];
        $response['name'] = $user['UName'];
        $response['email'] = $user['email'];
        $response['username'] = $user['username'];
        $response['apikey'] = $user['apiKey'];
        $response['phoneNum'] = $user['phoneNum'];
        $response['image'] = $user['img'];
        $response['dob'] = $user['dob'];
        $response['verified'] = $user['verified'];
        $response['timestamp'] = $user['timestamp'];
 
    }else{
        //Generating response
        $response['error'] = true;
        $response['message'] = "Invalid username or password";
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->get('/verifyUser',function() use ($app){
    //verifying required parameters
    // verifyRequiredParams(array('username','apikey'));
 
    //getting post values
    $username = $app->request->get('username');
    $otp = $app->request->get('otp');
 
    //Creating DbOperation object
    $db = new DbOperation();
    $response = array();
 
    //Calling the method createStudent to add student to the database
    $res = $db->verifyUser($username,$otp);
 
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
        $response[$i]["image"] = $value[7];
        $response[$i]["timestamp"] = $value[8];
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

    $img = "";
    $res = -1;

    $target_dir = "uploads/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $filename = basename( $_FILES['image']['name']);
    $path_parts = pathinfo($_FILES["image"]["name"]);
    $image_path = $path_parts['filename'].'_'.date("Y-m-d_h:i:sa").'.'.$path_parts['extension'];
    $target_file = $target_dir.$image_path;

    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $response["error"] = false;
        $uploadOk = 1;
    } else {
        $response["error"] = true;
        $response["imageUpload"] = "File is not an image.";
        $uploadOk = 0;
    }

    if(($response["error"] == false) && ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")) {
        
        $response["error"] == true;
        $response["imageUpload"] = "Sorry, only JPG, JPEG, PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $response["error"] = false;
            $response["imageUpload"] = "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
            $img = $target_file;
        } else {
            $response["error"] = true;
            $response["imageUpload"] = "Sorry, there was an error uploading your file.";
        }
    }
 
    if($img == $target_file) {
        //Creating DbOperation object
        $db = new DbOperation();
    
        //Calling the method createStudent to add student to the database
        $res = $db->addRestaurant($name,$email,$contact,$address,$img);
    }
 
    //If the result returned is 0 means success
    if ($res == 0 && $img == $target_file) {
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
    } else {
        $response["error"] = true;
        echoResponse(201, $response);
    }
});

$app->post('/likeRestaurant',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','restaurantID'));
 
    //getting post values
    $username = $app->request->post('username');
    $restaurantID = $app->request->post('restaurantID');
 
    //Creating DbOperation object
    $db = new DbOperation();

    $response = array();
 
    //Calling the method createStudent to add student to the database
    $res = $db->likeRestaurant($username,$restaurantID);
 
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
        $response["message"] = "Restaurant with RestaurantID ".$restaurantID." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = true;
        $response["message"] = "Restaurant already liked";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/followRestaurant',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','restaurantID'));
 
    //getting post values
    $username = $app->request->post('username');
    $restaurantID = $app->request->post('restaurantID');
 
    //Creating DbOperation object
    $db = new DbOperation();

    $response = array();
 
    //Calling the method createStudent to add student to the database
    $res = $db->followRestaurant($username,$restaurantID);
 
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
    }  else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Restaurant with RestaurantID ".$restaurantID." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = true;
        $response["message"] = "Restaurant already followed";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/userFollowedRestaurants',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username'));
    
    //getting post values
    $username = $app->request->post('username');
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $restaurantIDs = $db->userFollowedRestaurants($username);
    $i = 0;

    foreach($restaurantIDs as $k=>$value){
        $restaurantID = $value[0];
        $restaurant = $db->getRestaurant($restaurantID);
        $response[$i]["id"] = $restaurant["id"];
        $response[$i]["name"] = $restaurant["RName"];
        $response[$i]["email"] = $restaurant["email"];
        $response[$i]["contactNum"] = $restaurant["contactNum"];
        $response[$i]["likes"] = $restaurant["likes"];
        $response[$i]["stars"] = $restaurant["stars"];
        $response[$i]["address"] = $restaurant["RAddress"];
        $response[$i]["image"] = $restaurant["image"];
        $response[$i]["timestamp"] = $restaurant["timestamp"];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/userLikedRestaurants',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username'));
    
    //getting post values
    $username = $app->request->post('username');
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $restaurantIDs = $db->userLikedRestaurants($username);
    $i = 0;

    foreach($restaurantIDs as $k=>$value){
        $restaurantID = $value[0];
        $restaurant = $db->getRestaurant($restaurantID);
        $response[$i]["id"] = $restaurant["id"];
        $response[$i]["name"] = $restaurant["RName"];
        $response[$i]["email"] = $restaurant["email"];
        $response[$i]["contactNum"] = $restaurant["contactNum"];
        $response[$i]["likes"] = $restaurant["likes"];
        $response[$i]["stars"] = $restaurant["stars"];
        $response[$i]["address"] = $restaurant["RAddress"];
        $response[$i]["image"] = $restaurant["image"];
        $response[$i]["timestamp"] = $restaurant["timestamp"];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/addBook',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('name','author','publisherID','title', 'salePrice'));
 
    //getting post values
    $name = $app->request->post('name');
    $author = $app->request->post('author');
    $publisherID = $app->request->post('publisherID');
    $title = $app->request->post('title');
    $salePrice = $app->request->post('salePrice');
    $likes = 0;
    $bookmark = 0;

    $target_dir = "uploads/books/";
    $target_file = $target_dir . basename($_FILES["book"]["name"]);
    $uploadOk = 1;
    $FileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $filename = basename( $_FILES['book']['name']);
    $path_parts = pathinfo($_FILES["book"]["name"]);
    $image_path = $path_parts['filename'].'_'.date("Y-m-d_h:i:sa").'.'.$path_parts['extension'];
    $target_book = $target_dir.$image_path;

    if($FileType != "pdf") {
        $response["bookUpload"] = "Sorry, only pdf files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["book"]["tmp_name"], $target_book)) {
            $response["bookUpload"] = "The file ". basename( $_FILES["book"]["name"]). " has been uploaded.";
            $url = $target_book;
        } else {
            $response["bookUpload"] = "Sorry, there was an error uploading your file.";
        }
    }

    $target_dir = "uploads/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $filename = basename( $_FILES['image']['name']);
    $path_parts = pathinfo($_FILES["image"]["name"]);
    $image_path = $path_parts['filename'].'_'.date("Y-m-d_h:i:sa").'.'.$path_parts['extension'];
    $target_image = $target_dir.$image_path;

    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $response["error"] = false;
        $uploadOk = 1;
    } else {
        $response["error"] = true;
        $response["imageUpload"] = "File is not an image.";
        $uploadOk = 0;
    }

    if(($response["error"] == false) && ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")) {
        
        $response["error"] == true;
        $response["imageUpload"] = "Sorry, only JPG, JPEG, PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_image)) {
            $response["error"] = false;
            $response["imageUpload"] = "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
            $img = $target_image;
        } else {
            $response["error"] = true;
            $response["imageUpload"] = "Sorry, there was an error uploading your file.";
        }
    }
 
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->addBook($name,$author,$publisherID,$title,$likes,$bookmark,$img,$url,$salePrice);
 
    //If the result returned is 0 means success
    if (($res == 0) && ($url==$target_book) && ($img == $target_image)) {
        $response["error"] = false;
        echoResponse(201, $response);
 
    } else if ($res == 1) {
        $response["error"] = true;
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Publisher with ID ".$publisherID." does not exists.";
        echoResponse(200, $response);
    }
});

$app->post('/getRecentBooks', function() use ($app){
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $books = $db->showBooks();
    // $response['books'] = $books;
    $i = 0;
    foreach($books as $k=>$value){
        if(date('Ymd', strtotime($value[10])) < strtotime('-7 day')){
            $response[$i]["id"] = $value[0];
            $response[$i]["name"] = $value[1];
            $response[$i]["author"] = $value[2];
            $response[$i]["publisherID"] = $value[3];
            
              $response[$i]["salPrice"] = $value[4];
            $response[$i]["title"] = $value[5];
            $response[$i]["likes"] = $value[6];
            $response[$i]["bookmark"] = $value[7];
            $response[$i]["img"] = $value[8];
            $response[$i]["bookUrl"] = $value[9];
            $response[$i]["downloads"] = $value[10];
            $response[$i]["timestamp"] = $value[11];
            $i++;
        }
    }
 
    //Displaying the response
    echoResponse(200,$response);
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
        $response[$i]["publisherID"] = $value[3];
        $response[$i]["salePrice"] = $value[4];
        $response[$i]["title"] = $value[5];
        $response[$i]["likes"] = $value[6];
        $response[$i]["bookmark"] = $value[7];
        $response[$i]["img"] = $value[8];
        $response[$i]["url"] = $value[9];
        $response[$i]["downloads"] = $value[10];
        $response[$i]["timestamp"] = $value[11];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/likeBook',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','bookID'));
 
    //getting post values
    $username = $app->request->post('username');
    $bookID = $app->request->post('bookID');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->likeBook($username,$bookID);
 
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
        $response["message"] = "Book with BookID ".$bookID." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = true;
        $response["message"] = "Book already liked";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/bookmark',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','bookID'));
 
    //getting post values
    $username = $app->request->post('username');
    $bookID = $app->request->post('bookID');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->bookmark($username,$bookID);
 
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
        $response["message"] = "Book with BookID ".$bookID." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }  else if ($res == 4) {
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
        $response[$i]["publisherID"] = $book["publisherID"];
        $response[$i]["title"] = $book["title"];
        $response[$i]["likes"] = $book["likes"];
        $response[$i]["bookmark"] = $book["bookmark"];
        $response[$i]["img"] = $book["img"];
        $response[$i]["url"] = $book["bookUrl"];
        $response[$i]["salePrice"] = $book["salePrice"];
        $response[$i]["downloads"] = $book["downloads"];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/userBookmarkedBooks',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username'));
    
    //getting post values
    $username = $app->request->post('username');
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $bookIDs= $db->userBookmarkedBooks($username);
    $i = 0;
    foreach($bookIDs as $k=>$value){
        $bookID = $value[0];
        $book = $db->getBook($bookID);
        $response[$i]["id"] = $book["id"];
        $response[$i]["name"] = $book["BName"];
        $response[$i]["author"] = $book["author"];
        $response[$i]["publisherID"] = $book["publisherID"];
        $response[$i]["salePrice"] = $book["salePrice"];
        $response[$i]["title"] = $book["title"];
        $response[$i]["likes"] = $book["likes"];
        $response[$i]["bookmark"] = $book["bookmark"];
        $response[$i]["downloads"] = $book["downloads"];
        $response[$i]["url"] = $book["bookUrl"];
        $response[$i]["img"] = $book["img"];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->get('/getBook', function () use ($app){
    $id = $app->request->get('bookID');

    $db = new DbOperation();
    $book = $db->getBook($id);
    // $response = array();

    if($book == null) {
        $response["error"] = true;
        $response["message"] = "Book with ID ".$id." does not exists";
        echoResponse(200,$response); 
    } else {
        $response[0]["id"] = $book["id"];
        $response[0]["name"] = $book["BName"];
        $response[0]["author"] = $book["author"];
        $response[0]["publisherID"] = $book["publisherID"];
        $response[0]["title"] = $book["title"];
        $response[0]["likes"] = $book["likes"];
        $response[0]["bookmark"] = $book["bookmark"];
        $response[0]["img"] = $book["img"];
        $response[0]["url"] = $book["bookUrl"];
        $response[0]["salePrice"] = $book["salePrice"];
        $response[0]["downloads"] = $book["downloads"];
        $response["error"] = false;
        echoResponse(200,$response);   
    }
});

$app->post('/addPublisher',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('name','email','country'));
 
    //getting post values
    $name = $app->request->post('name');
    $email = $app->request->post('email');
    $country = $app->request->post('country');

    $target_dir = "uploads/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $filename = basename( $_FILES['image']['name']);
    $path_parts = pathinfo($_FILES["image"]["name"]);
    $image_path = $path_parts['filename'].'_'.date("Y-m-d_h:i:sa").'.'.$path_parts['extension'];
    $target_image = $target_dir.$image_path;

    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $response["error"] = false;
        $uploadOk = 1;
    } else {
        $response["error"] = true;
        $response["imageUpload"] = "File is not an image.";
        $uploadOk = 0;
    }

    if(($response["error"] == false) && ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")) {
        
        $response["error"] == true;
        $response["imageUpload"] = "Sorry, only JPG, JPEG, PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_image)) {
            $response["error"] = false;
            $response["imageUpload"] = "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
            $img = $target_image;
        } else {
            $response["error"] = true;
            $response["imageUpload"] = "Sorry, there was an error uploading your file.";
        }
    }
 
 

    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->addPublisher($name,$email,$country,$img);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        $response["error"] = false;
        $response["message"] = "Publisher added successfully!";
        echoResponse(201, $response);
 
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Error";
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Publisher with email ".$email." already exists";
        echoResponse(200, $response);
    }
});

$app->post('/showPublishers',function() use ($app){
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $publishers = $db->showPublishers();
    // $response['books'] = $books;
    $i = 0;
    foreach($publishers as $k=>$value){
        $response[$i]["id"] = $value[0];
        $response[$i]["name"] = $value[1];
        $response[$i]["email"] = $value[2];
        $response[$i]["numBooks"] = $value[3];
        $response[$i]["country"] = $value[4];
        $response[$i]["image"] = $value[5];
        $response[$i]["timestamp"] = $value[6];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->get('/getPublisher', function () use ($app){
    $id = $app->request->get('publisherID');

    $db = new DbOperation();
    $publisher = $db->getPublisher($id);
    // $response = array();

    if($publisher["id"] == null) {
        $response["error"] = true;
        $response["message"] = "Publisher with publisherID ".$id." does not exists";
        echoResponse(201,$response);
    } else {
        $response[0]["id"] = $publisher["id"];
        $response[0]["name"] = $publisher["name"];
        $response[0]["email"] = $publisher["email"];
        $response[0]["numBooks"] = $publisher["numBooks"];
        $response[0]["country"] = $publisher["country"];
        $response[0]["image"] = $publisher["image"];
        $response[0]["timestamp"] = $publisher["timestamp"];
        $response["error"] = false;
        echoResponse(200,$response);
    }
});

$app->get('/getPublisherBooks', function () use ($app){
    $publisher = $app->request->get('publisherID');

    $db = new DbOperation();
    $books = $db->getPublisherBooks($publisher);
    $response = array();

    $i = 0;
    if($books == null) {
        $response["error"] = true;
        $response["message"] = "Books with publisherID ".$publisher." does not exists";
        echoResponse(201,$response);
    } else {
        for($i=0; $i<count($books); $i++){
            $response[$i]["id"] = $books[$i][0];
            $response[$i]["name"] = $books[$i][1];
            $response[$i]["author"] = $books[$i][2];
            $response[$i]["publisherID"] = $books[$i][3];
            $response[$i]["salePrice"] = $books[$i][4];
            $response[$i]["title"] = $books[$i][5];
            $response[$i]["likes"] = $books[$i][6];
            $response[$i]["bookmark"] = $books[$i][7];
            $response[$i]["img"] = $books[$i][8];
            $response[$i]["url"] = $books[$i][9];
            $response[$i]["downloads"] = $books[$i][10];
            $response[$i]["timestamp"] = $books[$i][11];
        }
        echoResponse(200,$response);
    }
});

$app->post('/followPublisher',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','publisherID'));
 
    //getting post values
    $username = $app->request->post('username');
    $publisherID = $app->request->post('publisherID');
 
    //Creating DbOperation object
    $db = new DbOperation();

    $response = array();
 
    //Calling the method createStudent to add student to the database
    $res = $db->followPublisher($username,$publisherID);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "Successfully followed";
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
        $response["message"] = "Publisher with [publisherID] ".$publisherID." does not exists.";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists.";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = true;
        $response["message"] = "Publisher already followed";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/isPublisherFollowed', function () use ($app){

    verifyRequiredParams(array('username','publisherID'));

    $username = $app->request->post('username'); 
    $publisherID = $app->request->post('publisherID');

    $db = new DbOperation();
    $res = $db->isPublisherFollowed($username,$publisherID);

    $response = array();

    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["isFollowed"] = true;
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = false;
        //Adding a success message
        $response["isFollowed"] = false;
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists.";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "Publisher with ID ".$publisherID." does not exists.";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }

});

$app->get('/getFollowedPublishers',function() use ($app){
    
    //getting post values
    $username = $app->request->get('username');
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $publishers = $db->getFollowedPublishers($username);

    if($publishers == null) {
        $response["error"] = true;
        $response["message"] = "No publishers followed";
        echoResponse(201,$response);
    } else {
        $i = 0;
        foreach($publishers as $k=>$value){
            $response[$i]["id"] = $value[0];
            $response[$i]["username"] = $value[1];
            $response[$i]["publisherID"] = $value[2];
            $response[$i]["timestamp"] = $value[3];
            $i++;
        }
        echoResponse(200,$response);
    }
});

$app->post('/getRecentPublishers', function() use ($app){
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $books = $db->showPublishers();
    // $response['books'] = $books;
    $i = 0;
    foreach($books as $k=>$value){
        if(date('Ymd', strtotime($value[6])) < strtotime('-7 day')){
            $response[$i]["id"] = $value[0];
            $response[$i]["name"] = $value[1];
            $response[$i]["email"] = $value[2];
            $response[$i]["numBooks"] = $value[3];
            $response[$i]["country"] = $value[4];
            $response[$i]["image"] = $value[5];
            $response[$i]["timestamp"] = $value[6];
            $i++;
        }
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->post('/addOffer',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('name','percentOff','details','restaurantID', 'restaurantName', 'expiry'));
 
    //getting post values
    $name = $app->request->post('name');
    $percentOff = $app->request->post('percentOff');
    $details = $app->request->post('details');
    $restaurantID = $app->request->post('restaurantID');
    $restaurantName = $app->request->post('restaurantName');
    $expiry = $app->request->post('expiry');
    

    $target_dir = "uploads/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $filename = basename( $_FILES['image']['name']);
    $path_parts = pathinfo($_FILES["image"]["name"]);
    $image_path = $path_parts['filename'].'_'.date("Y-m-d_h:i:sa").'.'.$path_parts['extension'];
    $target_image = $target_dir.$image_path;

    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $response["error"] = false;
        $uploadOk = 1;
    } else {
        $response["error"] = true;
        $response["imageUpload"] = "File is not an image.";
        $uploadOk = 0;
    }

    if(($response["error"] == false) && ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")) {
        
        $response["error"] == true;
        $response["imageUpload"] = "Sorry, only JPG, JPEG, PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_image)) {
            $response["error"] = false;
            $response["imageUpload"] = "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
            $img = $target_image;
        } else {
            $response["error"] = true;
            $response["imageUpload"] = "Sorry, there was an error uploading your file.";
        }
    }
 
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->addOffer($name,$percentOff,$details,$restaurantID,$restaurantName,$img,$expiry);
 
    //If the result returned is 0 means success
    if (($res == 0) && ($img == $target_image)) {
        $response["error"] = false;
        $response["message"] = "Success";
        echoResponse(201, $response);
 
    } else if ($res == 1) {
        $response["error"] = true;
        $response["message"] = "Error";
        echoResponse(200, $response);

    } else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Restaurant with ID ".$restaurantID." does not have name ".$restaurantName;
        echoResponse(200, $response);
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "Restaurant with ID ".$restaurantID." does not exists.";
        echoResponse(200, $response);
    }
});

$app->get('/getRestaurantOffers', function () use ($app){
    $rid = $app->request->get('restaurantID');

    $db = new DbOperation();
    $offers = $db->getOffers($rid);
    $response = array();

    $i = 0;
    for($i=0; $i<count($offers); $i++){
        $response[$i]["name"] = $offers[$i][0];
        $response[$i]["percentOff"] = $offers[$i][1];
        $response[$i]["details"] = $offers[$i][2];
        $response[$i]["restaurantID"] = $offers[$i][3];
        $response[$i]["restaurantName"] = $offers[$i][4];
        $response[$i]["image"] = $offers[$i][5];
        $response[$i]["expiry"] = $offers[$i][6];
        $response[$i]["timestamp"] = $offers[$i][7];
    }
    echoResponse(200,$response);
});

$app->post('/showOffers', function () use ($app){

    $db = new DbOperation();
    $offers = $db->showOffers();
    $response = array();

    $i = 0;
    for($i=0; $i<count($offers); $i++){
        $response[$i]["id"] = $offers[$i][0];
        $response[$i]["name"] = $offers[$i][1];
        $response[$i]["percentOff"] = $offers[$i][2];
        $response[$i]["details"] = $offers[$i][3];
        $response[$i]["restaurantID"] = $offers[$i][4];
        $response[$i]["restaurantName"] = $offers[$i][5];
        $response[$i]["image"] = $offers[$i][6];
        $response[$i]["expiry"] = $offers[$i][7];
        $response[$i]["timestamp"] = $offers[$i][8];
    }
    echoResponse(200,$response);
});

$app->get('/isRestaurantLiked', function () use ($app){

    $username = $app->request->get('username');
    $rid = $app->request->get('restaurantID');    

    $db = new DbOperation();
    $res = $db->isRestaurantLiked($username, $rid);

    $response = array();

    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["isLiked"] = true;
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = false;
        //Adding a success message
        $response["isLiked"] = false;
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
    } else if ($res == 3) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "Restaurant with restaurantID ".$rid." does not exists";
        echoResponse(200, $response);
    }

});

$app->get('/isRestaurantFollowed', function () use ($app){

    $username = $app->request->get('username');
    $rid = $app->request->get('restaurantID');    

    $db = new DbOperation();
    $res = $db->isRestaurantFollowed($username, $rid);

    $response = array();

    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["isFollowed"] = true;
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = false;
        //Adding a success message
        $response["isFollowed"] = false;
        echoResponse(200, $response);
    }  else if ($res == 2) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
    } else if ($res == 3) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "Restaurant with restaurantID ".$rid." does not exists";
        echoResponse(200, $response);
    }

});

$app->get('/isBookLiked', function () use ($app){

    $username = $app->request->get('username');
    $bid = $app->request->get('bookID');    

    $db = new DbOperation();
    $res = $db->isBookLiked($username, $bid);

    $response = array();

    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["isLiked"] = true;
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = false;
        //Adding a success message
        $response["isLiked"] = false;
        echoResponse(200, $response);
    } else if ($res == 2) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
    } else if ($res == 3) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "Book with ID ".$bid." does not exists";
        echoResponse(200, $response);
    }

});

$app->get('/isBookBookmarked', function () use ($app){

    $username = $app->request->get('username');
    $bid = $app->request->get('bookID');    

    $db = new DbOperation();
    $res = $db->isBookBookmarked($username, $bid);

    $response = array();

    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["isFollowed"] = true;
        //Displaying response
        echoResponse(201, $response);
 
    //If the result returned is 1 means failure
    } else if ($res == 1) {
        $response["error"] = false;
        //Adding a success message
        $response["isFollowed"] = false;
        echoResponse(200, $response);
    }  else if ($res == 2) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
    } else if ($res == 3) {
        $response["error"] = true;
        //Adding a success message
        $response["message"] = "Book with ID ".$bid." does not exists";
        echoResponse(200, $response);
    }

});

$app->post('/getAllUsers', function () use ($app){

    $db = new DbOperation();
    $user = $db->getAllUsers();    

    $i = 0;
    $response['error'] = false;
    for($i=0; $i<count($user); $i++){
        $response[$i]['id'] = $user[$i][0];
        $response[$i]['name'] = $user[$i][1];
        $response[$i]['username'] = $user[$i][2];
        $response[$i]['email'] = $user[$i][3];
        $response[$i]['img'] = $user[$i][4];
        $response[$i]['phoneNum'] = $user[$i][5];
        $response[$i]['verified'] = $user[$i][7];
        $response[$i]['apiKey'] = $user[$i][8];
        $response[$i]['dob'] = $user[$i][9];
        $response[$i]['timestamp'] = $user[$i][10];
    }
    echoResponse(200,$response);
});

$app->post('/sendMessage', function () use ($app){
    verifyRequiredParams(array('sender','recipient','message'));

    $sender = $app->request->post('sender');
    $recipient = $app->request->post('recipient');
    $message = $app->request->post('message');

    $db = new DbOperation();
    $res = $db->sendMessage($sender, $recipient, $message);    

    if($res == 0){
        $response["error"] = false;
        $response["message"] = "Success";
        echoResponse(201, $response);
    } else if($res == 1) {
        $response["error"] = true;
        $response["message"] = "Error";
        echoResponse(200, $response);
    } else if($res == 2) {
        $response["error"] = true;
        $response["message"] = "Recipient with username ".$recipient." does not exists";
        echoResponse(200, $response);
    } else if($res == 3) {
        $response["error"] = true;
        $response["message"] = "Sender with username ".$sender." does not exists";
        echoResponse(200, $response);
    }
});

$app->post('/getMessageBetween', function () use ($app){
    verifyRequiredParams(array('firstUser','secondUser'));

    $firstUser = $app->request->post('firstUser');
    $secondUser = $app->request->post('secondUser');

    $db = new DbOperation();
    $messages = $db->getMessageBetween($firstUser, $secondUser);    

    if($messages == 1) {
        $response["error"] = true;
        $response["messages"] = "User with username ".$secondUser." does not exists";
        echoResponse(201,$response);
    } else if($messages == 2) {
        $response["error"] = true;
        $response["messages"] = "User with username ".$firstUser." does not exists";
        echoResponse(201,$response);
    } else if(!($messages == null)) {
        $i = 0;
        $response["error"] = false;
        $response["m"] = $messages;
        for($i=0; $i<count($messages); $i++){
            $response[$i]["id"] = $messages[$i][0];
            $response[$i]["sender"] = $messages[$i][1];
            $response[$i]["recipient"] = $messages[$i][2];
            $response[$i]["message"] = $messages[$i][3];
            $response[$i]["timestamp"] = $messages[$i][4];
        }
        echoResponse(200,$response);
    } else {
        $response["error"] = true;
        $response["messages"] = "No messages";
        echoResponse(201,$response);
    }
});

$app->post('/unlikeBook',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','bookID'));
 
    //getting post values
    $username = $app->request->post('username');
    $bookID = $app->request->post('bookID');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->unlikeBook($username,$bookID);
 
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
        $response["message"] = "Book with BookID ".$bookID." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = true;
        $response["message"] = "Book already unliked";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/unfollowPublisher',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','publisherID'));
 
    //getting post values
    $username = $app->request->post('username');
    $publisherID = $app->request->post('publisherID');
 
    //Creating DbOperation object
    $db = new DbOperation();

    $response = array();
 
    //Calling the method createStudent to add student to the database
    $res = $db->unfollowPublisher($username,$publisherID);
 
    //If the result returned is 0 means success
    if ($res == 0) {
        //Making the response error false
        $response["error"] = false;
        //Adding a success message
        $response["message"] = "Successfully unfollowed";
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
        $response["message"] = "Publisher with [publisherID] ".$publisherID." does not exists.";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists.";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = true;
        $response["message"] = "Publisher already unfollowed";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/removeBookmark',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','bookID'));
 
    //getting post values
    $username = $app->request->post('username');
    $bookID = $app->request->post('bookID');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->removeBookmark($username,$bookID);
 
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
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "Book with BookID ".$bookID." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }  else if ($res == 2) {
        $response["error"] = true;
        $response["message"] = "Bookmark already removed";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    }
});

$app->post('/downloadBook',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username','bookID'));
 
    //getting post values
    $username = $app->request->post('username');
    $bookID = $app->request->post('bookID');
 
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Calling the method createStudent to add student to the database
    $res = $db->downloadBook($username,$bookID);
 
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
        $response["message"] = "Book with BookID ".$bookID." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 3) {
        $response["error"] = true;
        $response["message"] = "User with username ".$username." does not exists";
        echoResponse(200, $response);
 
    //If the result returned is 2 means user already exist
    } else if ($res == 4) {
        $response["error"] = false;
        $response["message"] = "Book already downloaded";
        echoResponse(201, $response);
    }
});

$app->post('/downloadedBooks',function() use ($app){
    //verifying required parameters
    verifyRequiredParams(array('username'));
    
    //getting post values
    $username = $app->request->post('username');
    //Creating DbOperation object
    $db = new DbOperation();
 
    //Creating a response array
    $response = array();

    //Getting user detail
    $bookIDs= $db->downloadedBooks($username);
    $i = 0;
    foreach($bookIDs as $k=>$value){
        $bookID = $value[0];
        $book = $db->getBook($bookID);
        $response[$i]["id"] = $book["id"];
        $response[$i]["name"] = $book["BName"];
        $response[$i]["author"] = $book["author"];
        $response[$i]["publisherID"] = $book["publisherID"];
        $response[$i]["salePrice"] = $book["salePrice"];
        $response[$i]["title"] = $book["title"];
        $response[$i]["likes"] = $book["likes"];
        $response[$i]["bookmark"] = $book["bookmark"];
        $response[$i]["downloads"] = $book["downloads"];
        $response[$i]["url"] = $book["bookUrl"];
        $response[$i]["img"] = $book["img"];
        $i++;
    }
 
    //Displaying the response
    echoResponse(200,$response);
});

$app->run();

?>