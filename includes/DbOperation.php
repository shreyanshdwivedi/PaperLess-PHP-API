<?php
 
class DbOperation
{
    //Database connection link
    private $con;
 
    //Class constructor
    function __construct()
    {
        //Getting the DbConnect.php file
        require_once dirname(__FILE__) . '/DbConnect.php';
 
        //Creating a DbConnect object to connect to the database
        $db = new DbConnect();
 
        //Initializing our connection link of this class
        //by calling the method connect of DbConnect class
        $this->con = $db->connect();
    }
 
    //Method will create a new student
    public function createUser($name,$username,$email,$pass){
 
        //First we will check whether the student is already registered or not
        if (!$this->isUserExists($username)) {
            //Encrypting the password
            $password = md5($pass);
 
            //Generating an API Key
            $apikey = $this->generateApiKey();
 
            //Crating an statement
            $stmt = $this->con->prepare("INSERT INTO users(UName, username, UPassword, email, apiKey) values(?, ?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("sssss", $name, $username, $password, $email, $apikey);
 
            //Executing the statment
            $result = $stmt->execute();
 
            //Closing the statment
            $stmt->close();
 
            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
            return 2;
        }
    }
 
    //Method for student login
    public function userLogin($username,$pass){
        //Generating password hash
        $password = md5($pass);
        //Creating query
        $stmt = $this->con->prepare("SELECT * FROM users WHERE username=? and UPassword=?");
        //binding the parameters
        $stmt->bind_param("ss",$username,$password);
        //executing the query
        $stmt->execute();
        //Storing result
        $stmt->store_result();
        //Getting the result
        $num_rows = $stmt->num_rows;
        //closing the statment
        $stmt->close();
        //If the result value is greater than 0 means user found in the database with given username and password
        //So returning true
        return $num_rows>0;
    }

    //This method will generate a unique api key
    private function generateApiKey(){
        return md5(uniqid(rand(), true));
    }

    //Checking whether a student already exist
    private function isUserExists($username) {
        $stmt = $this->con->prepare("SELECT id from users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //This method will return student detail
    public function getUser($username){
        $stmt = $this->con->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        //Getting the student result array
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the student
        return $user;
    }

    public function verifyUser($username, $apikey){
        $user = $this->getUser($username);
        $verified = $user["verified"];
        if(($verified == 0) && ($user["apiKey"] == $apikey)){
            $stmt = $this->con->prepare("UPDATE users SET verified=TRUE WHERE username = ?");
            $stmt->bind_param("s",$username);
            //Executing the statment
            $result = $stmt->execute();
 
            //Closing the statment
            $stmt->close();
 
            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else if(($verified == 1) && ($user["apiKey"] == $apikey)){
            return 2;
        } else {
            //returning 2 means user already exist in the database
            return 3;
        }
    }

        //Method will create a new student
    public function addRestaurant($name,$email,$contact,$address){
 
        //First we will check whether the student is already registered or not
        if (!$this->isRestaurantExists($email)) {

            $likes = 0;
            $stars = 0;
            //Crating an statement
            $stmt = $this->con->prepare("INSERT INTO restaurants(RName, email, contactNum, RAddress, likes, stars) values(?, ?, ?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("ssssss", $name, $email, $contact, $address, $likes, $stars);
 
            //Executing the statment
            $result = $stmt->execute();
 
            //Closing the statment
            $stmt->close();
 
            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
            return 2;
        }
    }

    public function showRestaurants(){
        $stmt = "SELECT * FROM restaurants";
        $result = $this->con->query($stmt);
        $restaurants = $result->fetch_all();
        return $restaurants;
    }

    //Checking whether a student already exist
    private function isRestaurantExists($email) {
        $stmt = $this->con->prepare("SELECT id from restaurants WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //This method will return student detail
    public function getRestaurant($email){
        $stmt = $this->con->prepare("SELECT * FROM resturants WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        //Getting the student result array
        $restaurant = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the student
        return $restaurant;
    }

    public function likeRestaurant($username,$rid){
        if (!$this->isRLikeExists($username,$rid)) {
            $stmt = $this->con->prepare("INSERT INTO restaurantLikes(username, restaurantID) VALUES(?,?)");
            $stmt->bind_param("ss",$username,$rid);
            $result = $stmt->execute();
            //Closing the statment
            $stmt->close();

            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
            return 2;
        }
    }

    //Checking whether a student already exist
    private function isRLikeExists($username,$rid) {
        $stmt = $this->con->prepare("SELECT id from restaurantLikes WHERE username = ? AND restaurantID = ?");
        $stmt->bind_param("ss", $username, $rid);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function showBooks(){
        $stmt = "SELECT * FROM books";
        $result = $this->con->query($stmt);
        $books = $result->fetch_all();
        return $books;
    }

    public function likeBook($username,$bid){
        if (!$this->isBLikeExists($username,$bid)) {
            $stmt = $this->con->prepare("INSERT INTO bookLikes(username, bookID) VALUES(?,?)");
            $stmt->bind_param("ss",$username,$bid);
            $result = $stmt->execute();
            //Closing the statment
            $stmt->close();

            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
            return 2;
        }
    }

    //Checking whether a student already exist
    private function isBLikeExists($username,$bid) {
        $stmt = $this->con->prepare("SELECT id from bookLikes WHERE username = ? AND bookID = ?");
        $stmt->bind_param("ss", $username, $bid);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //This method will return student detail
    public function getBook($id){
        $stmt = $this->con->prepare("SELECT * FROM books WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        //Getting the student result array
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the student
        return $book;
    }

    public function bookmark($username,$bid){
        if (!$this->isBookmarkExists($username,$bid)) {
            $stmt = $this->con->prepare("INSERT INTO bookmark(username, bookID) VALUES(?,?)");
            $stmt->bind_param("ss",$username,$bid);
            $result = $stmt->execute();
            //Closing the statment
            $stmt->close();

            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
            return 2;
        }
    }

    //Checking whether a student already exist
    private function isBookmarkExists($username,$bid) {
        $stmt = $this->con->prepare("SELECT id from bookmark WHERE username = ? AND bookID = ?");
        $stmt->bind_param("ss", $username, $bid);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function userLikedBooks($username){
        $stmt = $this->con->prepare("SELECT bookID FROM bookLikes WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        //Getting the student result array
        $books = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $books;
    }

}