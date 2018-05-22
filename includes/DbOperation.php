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
    public function createUser($name,$username,$email,$pass,$img,$phoneNum,$address){
 
        //First we will check whether the student is already registered or not
        if (!$this->isUserExists($username, $email)) {
            //Encrypting the password
            $password = md5($pass);
 
            //Generating an API Key
            $apikey = $this->generateApiKey();
            $zero = 0;
            $verified = FALSE;
 
            //Crating an statement
            $stmt = $this->con->prepare("INSERT INTO users(`UName`, `username`, `UPassword`, `email`, `apiKey`, `verified`, `img`, `phoneNum`, `UAddress`, `numGiftSent`, `numGiftReceived`, `numFollowers`, `numFollowing`, `numGiftsPurchased`) 
            values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("ssssssssssssss", $name, $username, $password, $email, $apikey, $verified, $img, $phoneNum, $address, $zero, $zero, $zero, $zero, $zero);
 
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

    public function addBook($name,$author,$publication,$title,$likes,$bookmark,$img,$url){
 
        $stmt = $this->con->prepare("INSERT INTO books(BName, author, publication, title, likes, bookmark, img, bookUrl) values(?, ?, ?, ?, ?, ?, ?, ?)");
 
        //Binding the parameters
        $stmt->bind_param("ssssssss", $name,$author,$publication,$title,$likes,$bookmark,$img,$url);
 
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
    public function getRestaurant($id){
        $stmt = $this->con->prepare("SELECT * FROM restaurants WHERE id = ?");
        $stmt->bind_param("s",$id);
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

    public function followRestaurant($username,$rid){
        if (!$this->isRFollowExists($username,$rid)) {
            $stmt = $this->con->prepare("INSERT INTO restaurantFollows(username, restaurantID) VALUES(?,?)");
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

    private function isRFollowExists($username,$rid) {
        $stmt = $this->con->prepare("SELECT id from restaurantFollows WHERE username = ? AND restaurantID = ?");
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
                $book = $this->getBook($bid);
                $likes = $book["likes"] + 1;

                $stmt = $this->con->prepare("UPDATE books SET likes = ? WHERE id = ?");
                $stmt->bind_param("ss",$likes,$bid);
                $stmt->execute();
                $stmt->close();

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

    public function getPublisherBooks($publisher){
        $stmt = $this->con->prepare("SELECT * FROM books WHERE publication=?");
        $stmt->bind_param("s",$publisher);
        $stmt->execute();
        //Getting the student result array
        $books = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $books;
    }

    public function getOffers($rid){
        $stmt = $this->con->prepare("SELECT * FROM offers WHERE restaurantID=?");
        $stmt->bind_param("s",$rid);
        $stmt->execute();
        //Getting the student result array
        $offers = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $offers;
    }

    public function showOffers(){
        $stmt = "SELECT * FROM offers";
        $result = $this->con->query($stmt);
        $offers = $result->fetch_all();
        return $offers;
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
                $book = $this->getBook($bid);
                $bookmark = $book["bookmark"] + 1;

                $stmt = $this->con->prepare("UPDATE books SET bookmark = ? WHERE id = ?");
                $stmt->bind_param("ss",$bookmark,$bid);
                $stmt->execute();
                $stmt->close();

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

    public function userFollowedRestaurants($username){
        $stmt = $this->con->prepare("SELECT restaurantID FROM restaurantFollows WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        //Getting the student result array
        $restaurants = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $restaurants;
    }

    public function userLikedRestaurants($username){
        $stmt = $this->con->prepare("SELECT restaurantID FROM restaurantLikes WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        //Getting the student result array
        $restaurants = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $restaurants;
    }

    public function isRestaurantLiked($username,$rid){
        $stmt = $this->con->prepare("SELECT * FROM restaurantLikes WHERE username=? and restaurantID=?");
        //binding the parameters
        $stmt->bind_param("ss",$username,$rid);
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

    public function isRestaurantFollowed($username,$rid){
        $stmt = $this->con->prepare("SELECT * FROM restaurantFollows WHERE username=? and restaurantID=?");
        //binding the parameters
        $stmt->bind_param("ss",$username,$rid);
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
























    /* SURPRISE API */

        //Method will create a new student
    public function addGiftType($name,$details,$img){
 
        //First we will check whether the student is already registered or not
        if (!$this->isGiftTypeExists($name)) {

            $stmt = $this->con->prepare("INSERT INTO giftTypes(giftTypeName, details, img) values(?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("sss", $name, $details, $img);
 
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

    public function showGiftTypes(){
        $stmt = "SELECT * FROM giftTypes";
        $result = $this->con->query($stmt);
        $giftTypes = $result->fetch_all();
        return $giftTypes;
    }

    //Checking whether a student already exist
    private function isGiftTypeExists($name) {
        $stmt = $this->con->prepare("SELECT id from giftTypes WHERE giftTypeName = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //This method will return student detail
    public function getGiftType($id){
        $stmt = $this->con->prepare("SELECT * FROM giftTypes WHERE id = ?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        //Getting the student result array
        $giftType = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the student
        return $giftType;
    }

        //Method will create a new student
    public function addGift($name,$details,$price,$img,$giftTypeID){
 
        //First we will check whether the student is already registered or not
        if (!$this->isGiftExists($name,$giftTypeID)) {

            $stmt = $this->con->prepare("INSERT INTO gifts(giftName, details, price, img, giftTypeID)
                    values(?, ?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("sssss", $name, $details, $price, $img, $giftTypeID);
 
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

    public function showGifts(){
        $stmt = "SELECT * FROM gifts";
        $result = $this->con->query($stmt);
        $gifts = $result->fetch_all();
        return $gifts;
    }

    //Checking whether a student already exist
    private function isGiftExists($name, $giftTypeID) {
        $stmt = $this->con->prepare("SELECT id from gifts WHERE giftName = ? AND giftTypeID = ?");
        $stmt->bind_param("ss", $name, $giftTypeID);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //This method will return student detail
    public function getGift($id){
        $stmt = $this->con->prepare("SELECT * FROM gifts WHERE id = ?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        //Getting the student result array
        $gift = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the student
        return $gift;
    }

    public function purchaseGift($username,$giftID,$giftTypeID,$paymentID){
 
        //First we will check whether the student is already registered or not
        if (!$this->isGiftTypeExists($name)) {

            $stmt = $this->con->prepare("INSERT INTO purchasedGifts(giftID, giftTypeID, username, paymentID)
                    values(?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("ssss",$username,$giftID,$giftTypeID,$paymentID);
 
            //Executing the statment
            $result = $stmt->execute();
 
            //Closing the statment
            $stmt->close();

            $user = $this->getUser($username);
            $num = $user["numGiftsPurchased"] + 1;

            $stmt = $this->con->prepare("UPDATE users SET numGiftsPurchased = ? WHERE username = ?");
            $stmt->bind_param("ss", $num, $username);
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

    public function sendGift($sender,$recipient,$purchaseID){
 
        $status = "Order Placed";
        $stmt = $this->con->prepare("INSERT INTO sentGifts(sender, recipient, purchaseID, giftStatus)
                values(?, ?, ?, ?)");
 
        //Binding the parameters
        $stmt->bind_param("ssss", $sender, $recipient, $purchaseID, $status);
 
        //Executing the statment
        $result = $stmt->execute();
 
        //Closing the statment
        $stmt->close();

        $sender = $this->getUser($sender);
        $num = $sender["numGiftSent"] + 1;
        $stmt = $this->con->prepare("UPDATE users SET numGiftSent = ? WHERE username = ?");
        $stmt->bind_param("ss", $num, $sender);
        $res = $stmt->execute();
        $stmt->close();

        $recipient = $this->getUser($recipient);
        $num = $recipient["numGiftReceived"] + 1;
        $stmt = $this->con->prepare("UPDATE users SET numGiftReceived = ? WHERE username = ?");
        $stmt->bind_param("ss", $num, $recipient);
        $res = $stmt->execute();
        $stmt->close();
 
        //If statment executed successfully
        if ($result) {
            //Returning 0 means student created successfully
            return 0;
        } else {
            //Returning 1 means failed to create student
            return 1;
        }
    }

    public function followUser($followedBy,$followedTo){
        if (!$this->isFollowExists($followedBy,$followedTo)) {
            $stmt = $this->con->prepare("INSERT INTO userFollows(followedBy, followedTo) VALUES(?,?)");
            $stmt->bind_param("ss",$followedBy,$followedTo);
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

    private function isFollowExists($followedBy,$followedTo) {
        $stmt = $this->con->prepare("SELECT id from userFollows WHERE followedBy = ? AND followedTo = ?");
        $stmt->bind_param("ss", $followedBy, $followedTo);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function isUserFollowed($followedBy,$followedTo){
        $stmt = $this->con->prepare("SELECT * FROM userFollows WHERE followedBy = ? AND followedTo = ?");
        //binding the parameters
        $stmt->bind_param("ss",$followedBy,$followedTo);
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

    public function getFollowing($username){
        $stmt = $this->con->prepare("SELECT * FROM userFollows WHERE followedBy = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        //Getting the student result array
        $users = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $users;
    }

    public function getFollowers($username){
        $stmt = $this->con->prepare("SELECT * FROM userFollows WHERE followedTo = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        //Getting the student result array
        $users = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $users;
    }

    public function sendFriendRequest($sentBy,$sentTo){
        if (!$this->doesRequestExists($sentBy,$sentTo)) {
            $stmt = $this->con->prepare("INSERT INTO friendRequest(sentBy, sentTo) VALUES(?,?)");
            $stmt->bind_param("ss",$sentBy,$sentTo);
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

    private function doesRequestExists($sentBy,$sentTo) {
        $stmt = $this->con->prepare("SELECT id from friendRequest WHERE sentBy = ? AND sentTo = ?");
        $stmt->bind_param("ss", $sentBy, $sentTo);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        
        if($num_rows > 0){
            return $num_rows > 0;
        } else {
            $stmt = $this->con->prepare("SELECT id from friendRequest WHERE sentBy = ? AND sentTo = ?");
            $stmt->bind_param("ss", $sentTo, $sentBy);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
        }
    }

    public function addFriend($addedBy,$addedTo){
        if (!$this->doesFriendExists($addedBy,$addedTo)) {
            $stmt = $this->con->prepare("INSERT INTO friends(addedBy, addedTo) VALUES(?,?)");
            $stmt->bind_param("ss",$addedBy,$addedTo);
            $res = $stmt->execute();
            //Closing the statment
            $stmt->close();

            $stmt = $this->con->prepare("DELETE * FROM friendRequest WHERE sentBy = ? AND sentTO = ?");
            $stmt->bind_param("ss",$addedBy,$addedTo);
            //Executing the statment
            $result = $stmt->execute();
 
            //Closing the statment
            $stmt->close();

            $stmt = $this->con->prepare("DELETE * FROM friendRequest WHERE sentBy = ? AND sentTo = ?");
            $stmt->bind_param("ss",$addedTo,$addedBy);
            //Executing the statment
            $result = $stmt->execute();
 
            //Closing the statment
            $stmt->close();

            //If statment executed successfully
            if ($res) {
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

    private function doesFriendExists($addedBy,$addedTo) {
        $stmt = $this->con->prepare("SELECT id from friends WHERE addedBy = ? AND addedTo = ?");
        $stmt->bind_param("ss", $addedBy, $addedTo);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        
        if($num_rows > 0){
            return $num_rows > 0;
        } else {
            $stmt = $this->con->prepare("SELECT id from friends WHERE addedBy = ? AND addedTo = ?");
            $stmt->bind_param("ss", $addedTo, $addedBy);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            $stmt->close();
            return $num_rows > 0;
        }
    }

    public function getFriends($username){
        $stmt = $this->con->prepare("SELECT * FROM friends WHERE addedBy = ? OR addedTo = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        //Getting the student result array
        $friends = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $friends;
    }

}