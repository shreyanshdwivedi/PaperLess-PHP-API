<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
 
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
 
    public function createUser($name,$username,$email,$pass,$img,$phoneNum,$dob){
 
        //First we will check whether the student is already registered or not
        if (!$this->isUserExists($username, $email)) {
            //Encrypting the password
            $password = md5($pass);
 
            //Generating an API Key
            $apikey = $this->generateApiKey();
            $zero = 0;
            $verified = FALSE;
 
            //Crating an statement
            $stmt = $this->con->prepare("INSERT INTO users(`UName`, `username`, `UPassword`, `email`, `apiKey`, `verified`, `img`, `phoneNum`, `dob`) 
            values(?, ?, ?, ?, ?, ?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("sssssssss", $name, $username, $password, $email, $apikey, $verified, $img, $phoneNum, $dob);
 
            //Executing the statment
            $result = $stmt->execute();
 
            //Closing the statment
            $stmt->close();
 
            //If statment executed successfully
            if ($result) {
                //Returning 0 means student created successfully
                $digits = 7;
                $otp = rand(pow(10, $digits-1), pow(10, $digits)-1);
                $res = $this->sendMail($username, $email, $otp);
                if($res == 1) {
                    return 0;
                } else {
                    return $res;
                }
            } else {
                //Returning 1 means failed to create student
                return 1;
            }
        } else {
            //returning 2 means user already exist in the database
            return 2;
        }
    }

    private function sendMail($username, $email, $otp) {
        
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = '';                 // SMTP username
            $mail->Password = '';                           // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('test22091997@gmail.com', 'Paperless');
            $mail->addAddress($email, $username);     

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Verify';
            $mail->Body    = 'Please verify your Paperless account by entering this OTP - '.$otp;

            $mail->send();

            $verified = false;
            $stmt = $this->con->prepare("INSERT INTO verification(username, otp, verified) 
            values(?, ?, ?)");
            $stmt->bind_param("sss", $username, $otp, $verified);
            $result = $stmt->execute();
            $stmt->close();

            return 1;
        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }
 
 
    //Method for student login
    public function userLogin($email,$pass){
        //Generating password hash
        $password = md5($pass);
        //Creating query
        $stmt = $this->con->prepare("SELECT * FROM users WHERE email=? and UPassword=?");
        //binding the parameters
        $stmt->bind_param("ss",$email,$password);
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
    
    public function getUserByEmail($email){
        $stmt = $this->con->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        //Getting the student result array
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        //returning the student
        return $user;
    }

    public function verifyUser($username, $otp){
        $stmt = $this->con->prepare("SELECT * FROM verification WHERE username=?");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        //Getting the student result array
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $verified = $user["verified"];
        if(($verified == 0) && ($user["otp"] == $otp)){
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
    public function addRestaurant($name,$email,$contact,$address,$img){
 
        //First we will check whether the student is already registered or not
        if (!$this->isRestaurantExists($email)) {

            $likes = 0;
            $stars = 0;
            //Crating an statement
            $stmt = $this->con->prepare("INSERT INTO restaurants(RName, email, contactNum, RAddress, likes, stars, `image`) values(?, ?, ?, ?, ?, ?, ?)");
 
            //Binding the parameters
            $stmt->bind_param("sssssss", $name, $email, $contact, $address, $likes, $stars, $img);
 
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

    public function addOffer($name,$percentOff,$details,$restaurantID,$restaurantName,$img,$expiry){
 
        if($this->isRestaurantExistsByID($restaurantID)) {
            
            $restaurant = $this->getRestaurant($restaurantID);
            $name = $restaurant["RName"];
            if($restaurantName == $name){

                $stmt = $this->con->prepare("INSERT INTO offers(`name`,percentOff,details,restaurantID,restaurantName,`image`,expiry) values(?, ?, ?, ?, ?, ?, ?)");
        
                //Binding the parameters
                $stmt->bind_param("sssssss", $name,$percentOff,$details,$restaurantID,$restaurantName,$img,$expiry);
        
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
    
    public function userBookmarkedBooks($username){
        $stmt = $this->con->prepare("SELECT bookID FROM bookmark WHERE username = ?");
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
    
    public function isBookLiked($username,$bid){
        if($this->isBookExists($bid)) {
            if($this->isUserExists($username)) {
                $stmt = $this->con->prepare("SELECT * FROM bookLikes WHERE username=? and bookID=?");
                //binding the parameters
                $stmt->bind_param("ss",$username,$bid);
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

    public function isBookBookmarked($username,$bid){
        if($this->isBookExists($bid)) {
            if($this->isUserExists($username)) {
                $stmt = $this->con->prepare("SELECT * FROM bookmark WHERE username=? and bookID=?");
                //binding the parameters
                $stmt->bind_param("ss",$username,$bid);
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

    public function getAllUsers(){
        $stmt = "SELECT * FROM users";
        $result = $this->con->query($stmt);
        $users = $result->fetch_all();
        return $users;
    }

    public function sendMessage($sender, $recipient, $message){
        if($this->isUserExists($sender)) {
            if($this->isUserExists($recipient)) {
                $stmt = $this->con->prepare("INSERT INTO messages(sender, recipient, `message`) VALUES(?, ?, ?)");
                $stmt->bind_param("sss",$sender,$recipient,$message);
                $result = $stmt->execute();
                $stmt->close();

                if($result){
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

    public function getMessageBetween($firstUser, $secondUser){
        if($this->isUserExists($firstUser)) {
            if($this->isUserExists($secondUser)) {
                $stmt = $this->con->prepare("SELECT * FROM messages WHERE sender = ? AND recipient = ?");
                $stmt->bind_param("ss",$firstUser,$secondUser);
                $stmt->execute();
                //Getting the student result array
                $messages1 = $stmt->get_result()->fetch_all();
                $stmt->close();

                $stmt = $this->con->prepare("SELECT * FROM messages WHERE sender = ? AND recipient = ?");
                $stmt->bind_param("ss",$secondUser,$firstUser);
                $stmt->execute();
                //Getting the student result array
                $messages2 = $stmt->get_result()->fetch_all();
                $stmt->close();
                return $messages1 + $messages2;
            } else {
                return 1;
            }
        } else {
            return 2;
        }
    }
    
    
    public function unlikeBook($username,$bid){
        if ($this->isBLikeExists($username,$bid)) {
            if($this->isUserExists($username)) {
                if($this->isBookExists($bid)) {
                    $stmt = $this->con->prepare("DELETE FROM bookLikes WHERE username = ? AND bookID = ?");
                    $stmt->bind_param("ss",$username,$bid);
                    $result = $stmt->execute();
                    //Closing the statment
                    $stmt->close();

                    //If statment executed successfully
                    if ($result) {
                        $book = $this->getBook($bid);
                        $likes = $book["likes"] - 1;

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

    public function unfollowPublisher($username,$publisherID){
        if (!$this->isPublisherFollowed($username,$publisherID)) {
            if($this->isUserExists($username)) {
                if($this->isPublisherExists($publisherID)) {
                    $stmt = $this->con->prepare("DELETE FROM publisherFollows WHERE username = ? AND publisherID = ?");
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

    public function removeBookmark($username,$bid){
        if($this->isUserExists($username)) {
            if($this->isBookExists($bid)) {
                if ($this->isBookmarkExists($username,$bid)) {
                    $stmt = $this->con->prepare("DELETE FROM bookmark WHERE username = ? AND bookID = ?");
                    $stmt->bind_param("ss",$username,$bid);
                    $result = $stmt->execute();
                    //Closing the statment
                    $stmt->close();

                    //If statment executed successfully
                    if ($result) {
                        //Returning 0 means student created successfully
                        $book = $this->getBook($bid);
                        $bookmark = $book["bookmark"] - 1;

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

    public function downloadBook($username,$bid){
            if($this->isUserExists($username)) {
                if($this->isBookExists($bid)) {

                    $stmt = $this->con->prepare("SELECT * FROM downloadBook WHERE username=? and bookID=?");
                    $stmt->bind_param("ss",$username,$bid);
                    $stmt->execute();
                    $stmt->store_result();
                    $num_rows = $stmt->num_rows;
                    $stmt->close();
                    
                    if($num_rows == 0) {
                        $stmt = $this->con->prepare("INSERT INTO downloadBook(username, bookID) VALUES(?,?)");
                        $stmt->bind_param("ss",$username,$bid);
                        $result = $stmt->execute();
                        //Closing the statment
                        $stmt->close();

                        //If statment executed successfully
                        if ($result) {
                            $book = $this->getBook($bid);
                            $downloads = $book["downloads"] + 1;

                            $stmt = $this->con->prepare("UPDATE books SET downloads = ? WHERE id = ?");
                            $stmt->bind_param("ss",$downloads,$bid);
                            $stmt->execute();
                            $stmt->close();

                            return 0;
                        } else {
                            //Returning 1 means failed to create student
                            return 1;
                        }
                    } else {
                        return 4;
                    }
                } else {
                    return 2;
                }
            } else {
                return 3;
            }
    }

    public function downloadedBooks($username){
        $stmt = $this->con->prepare("SELECT bookID FROM downloadBook WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        //Getting the student result array
        $books = $stmt->get_result()->fetch_all();
        $stmt->close();
        //returning the student
        return $books;
    }
}