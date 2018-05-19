### EndPoints

1. *POST* - ```https://paperlessapi.000webhostapp.com/createUser```  
  
	*Required Parameters*
    - name
    - username (unique)
    - email
    - password  

2. *POST* - ```https://paperlessapi.000webhostapp.com/userLogin```
  
	*Required Parameters*  
    - username
    - password  

3. ```https://paperlessapi.000webhostapp.com/showRestaurants```
  
	*Required Parameters*
    - No params required

4. *POST* - ```https://paperlessapi.000webhostapp.com/addRestaurant```
  
	*Required Parameters*
    - name
    - email (unique)
    - contact
    - address  

5. *POST* - ```https://paperlessapi.000webhostapp.com/likeRestaurant```
  
	*Required Parameters*
    - uid (userID)
    - rid (RestaurantID)
   
6. ```https://paperlessapi.000webhostapp.com/showBooks```
  
	*Required Parameters*
    - No params required

7. *POST* - ```https://paperlessapi.000webhostapp.com/likeBook```
  
	*Required Parameters*
    - uid (userID)
    - bid (bookID)

8. *POST* - ```https://paperlessapi.000webhostapp.com/bookmark```
  
	*Required Parameters*
    - uid (userID)
    - bid (bookID)

9. *POST* - ```https://paperlessapi.000webhostapp.com/verifyUser```
  
	*Required Parameters*  
    - username
    - apikey  

10. *POST* - ```https://paperlessapi.000webhostapp.com/userLikedBooks```
  
	*Required Parameters*  
    - username