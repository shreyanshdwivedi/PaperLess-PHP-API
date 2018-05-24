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
            $stmt = $this->con->prepare("INSERT INTO users(`UName`, `username`, `UPassword`, `email`, `apiKey`, `verified`, `img`, `phoneNum`, `UAddress`) 
            values(?, ?, ?, ?, ?, ?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("sssssssss", $name, $username, $password, $email, $apikey, $verified, $img, $phoneNum, $address);
 
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

    private function isRestaurantExistsByID($id) {
        $stmt = $this->con->prepare("SELECT id from restaurants WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //This method will return student detail
    public function getRestaurant($id){ 
        if($this->isRestaurantExistsByID($id)) {
            $stmt = $this->con->prepare("SELECT * FROM restaurants WHERE id = ?");
            $stmt->bind_param("s",$id);
            $stmt->execute();
            //Getting the student result array
            $restaurant = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            //returning the student
            return $restaurant;
        }
    }

    public function likeRestaurant($username,$rid){
        if (!$this->isRLikeExists($username,$rid)) {
            if($this->isUserExists($username)) {
                if($this->isRestaurantExistsByID($rid)) {
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
                    return 2;
                }
            } else {
                return 3;
            }
        } else {
            //returning 2 means user already exist in the database
            return 4;
        }
    }

    public function followRestaurant($username,$rid){
        if (!$this->isRFollowExists($username,$rid)) {
            if (!$this->isRFollowExists($username,$rid)) {
                if($this->isUserExists($username)) {
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
                    return 2;
                }
            } else {
                return 3;
            }
        } else {
            //returning 2 means user already exist in the database
            return 4;
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

    public function addBook($name,$author,$publisherID,$title,$likes,$bookmark,$img,$url,$salePrice){
 
        if($this->isPublisherExists($publisherID)) {
            $stmt = $this->con->prepare("INSERT INTO books(BName, author, publisherID, title, likes, bookmark, img, bookUrl, salePrice) values(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
            //Binding the parameters
            $stmt->bind_param("sssssssss", $name,$author,$publisherID,$title,$likes,$bookmark,$img,$url,$salePrice);
            $result = $stmt->execute();
            $stmt->close();

            $publisher = $this->getPublisher($publisherID);
            $num = $publisher["numBooks"] + 1;

            $stmt = $this->con->prepare("UPDATE publisher SET numBooks = ? WHERE id = ?");
            $stmt->bind_param("ss",$num,$publisherID);
            $stmt->execute();
            $stmt->close();
    
            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                return 0;
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        }else {
            return 2;
        }
    }

    public function showBooks(){
        $stmt = "SELECT * FROM books";
        $result = $this->con->query($stmt);
        $books = $result->fetch_all();
        return $books;
    }

    public function likeBook($username,$bid){
        if (!$this->isBLikeExists($username,$bid)) {
            if($this->isUserExists($username)) {
                if($this->isBookExists($bid)) {
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
                    return 2;
                }
            } else {
                return 3;
            }
        } else {
            //returning 2 means user already exist in the database
            return 4;
        }
    }

    private function isBookExists($bookID) {
        $stmt = $this->con->prepare("SELECT id from books WHERE id = ?");
        $stmt->bind_param("s", $bookID);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        if($num_rows > 0) {
            return 1;
        } else {
            return 0;
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

    public function addPublisher($name,$email,$country,$image){
 
        if(!$this->isPublisherExistsByEmail($email)) {
            $zero = 0;
            $stmt = $this->con->prepare("INSERT INTO publisher(`name`, email, numBooks, country, `image`) values(?, ?, ?, ?, ?)");
    
            //Binding the parameters
            $stmt->bind_param("sssss", $name,$email,$zero,$country,$image);
    
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
        }else {
            return 2;
        }
    }

    public function showPublishers(){
        $stmt = "SELECT * FROM publisher";
        $result = $this->con->query($stmt);
        $publishers = $result->fetch_all();
        return $publishers;
    }

    public function getPublisher($id){
        $stmt = $this->con->prepare("SELECT * FROM publisher WHERE id=?");
        $stmt->bind_param("s",$id);
        $stmt->execute();
        //Getting the student result array
        $publisher = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the student
        return $publisher;
    }

    public function isPublisherFollowed($username,$publisherID) {
        if($this->isPublisherExists($publisherID)) {
            if($this->isUserExists($username)) {
                $stmt = $this->con->prepare("SELECT id from publisherFollows WHERE username = ? AND publisherID = ?");
                $stmt->bind_param("ss", $username, $publisherID);
                $stmt->execute();
                $stmt->store_result();
                $num_rows = $stmt->num_rows;
                $stmt->close();
                if($num_rows > 0) {
                    return 0;
                } else {
                    return 1;
                }
            } else {
                return 2;
            }
        } else {
            return 3;
        }
    }

    public function followPublisher($username,$publisherID){
        if ($this->isPublisherFollowed($username,$publisherID)) {
            if($this->isUserExists($username)) {
                if($this->isPublisherExists($publisherID)) {
                    $stmt = $this->con->prepare("INSERT INTO publisherFollows(username, publisherID) VALUES(?,?)");
                    $stmt->bind_param("ss",$username,$publisherID);
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
                    return 2;
                }
            } else {
                return 3;
            }
        } else {
            //returning 2 means user already exist in the database
            return 4;
        }
    }

    public function getFollowedPublishers($username){
        $stmt = $this->con->prepare("SELECT * FROM publisherFollows WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        //Getting the student result array
        $publishers = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $publishers;
    }

    private function isPublisherExists($publisherID) {
        $stmt = $this->con->prepare("SELECT id from publisher WHERE id = ?");
        $stmt->bind_param("s", $publisherID);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    private function isPublisherExistsByEmail($email) {
        $stmt = $this->con->prepare("SELECT id from publisher WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function getPublisherBooks($publisher){
        $stmt = $this->con->prepare("SELECT * FROM books WHERE publisherID=?");
        $stmt->bind_param("s",$publisher);
        $stmt->execute();
        //Getting the student result array
        $books = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $books;
    }

    public function addOffer($name,$percentOff,$details,$restaurantID,$restaurantName,$img){
 
        if($this->isRestaurantExistsByID($restaurantID)) {
            
            $restaurant = $this->getRestaurant($restaurantID);
            if($restaurantName == $restaurant["RName"]){

                $stmt = $this->con->prepare("INSERT INTO offers(`name`,percentOff,details,restaurantID,restaurantName,`image`) values(?, ?, ?, ?, ?, ?)");
        
                //Binding the parameters
                $stmt->bind_param("ssssss", $name,$percentOff,$details,$restaurantID,$restaurantName,$img);
        
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
                return 2;
            }
        }else {
            return 3;
        }
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
            if($this->isUserExists($username)) {
                if($this->isBookExists($bid)) {
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
                    return 2;
                }
            } else {
                return 3;
            }
        } else {
            //returning 2 means user already exist in the database
            return 4;
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
        if($this->isUserExists($username)) {
            $stmt = $this->con->prepare("SELECT restaurantID FROM restaurantFollows WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            //Getting the student result array
            $restaurants = $stmt->get_result()->fetch_all();
            $stmt->close();
            //returning the student
            return $restaurants;
        }
    }

    public function userLikedRestaurants($username){
        if($this->isUserExists($username)) {
            $stmt = $this->con->prepare("SELECT restaurantID FROM restaurantLikes WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            //Getting the student result array
            $restaurants = $stmt->get_result()->fetch_all();
            $stmt->close();
            //returning the student
            return $restaurants;
        }
    }

    public function isRestaurantLiked($username,$rid){
        if($this->isRestaurantExistsByID($rid)) {
            if($this->isUserExists($username)) {
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
                if($num_rows>0) {
                    return 0;
                } else {
                    return 1;
                }
            } else {
                return 2;
            }
        } else {
            return 3;
        }
    }

    public function isRestaurantFollowed($username,$rid){
        if($this->isRestaurantExistsByID($rid)) {
            if($this->isUserExists($username)) {
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
                if($num_rows>0){
                    return 0;
                } else {
                    return 1;
                }
            } else {
                return 2;
            }
        } else {
            return 3;
        }
    }
}