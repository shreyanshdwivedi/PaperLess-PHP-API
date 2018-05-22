### EndPoints

1. *POST* - ```https://paperlessapi.000webhostapp.com/createUser```  
  
	*Required Parameters*
    - name
    - username (unique)
    - email
    - phoneNum
    - address
    - password  
    - image (File Type) 

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
    - username
    - rid (RestaurantID)
   
6. ```https://paperlessapi.000webhostapp.com/showBooks```
  
	*Required Parameters*
    - No params required

7. *GET* - ```https://paperlessapi.000webhostapp.com/likeBook```
  
	*Required Parameters*
    - username
    - bid (bookID)

8. *POST* - ```https://paperlessapi.000webhostapp.com/bookmark```
  
	*Required Parameters*
    - username
    - bid (bookID)

9. *GET* - ```https://paperlessapi.000webhostapp.com/verifyUser```
  
	*Required Parameters*  
    - username
    - apikey  

10. *POST* - ```https://paperlessapi.000webhostapp.com/userLikedBooks```
  
	*Required Parameters*  
    - username
    
11. *POST* - ```https://paperlessapi.000webhostapp.com/followRestaurant```
  
	*Required Parameters*  
    - username
    - rid (RestaurantID)

12. *POST* - ```https://paperlessapi.000webhostapp.com/userFollowedRestaurants```
  
	*Required Parameters*  
    - username 
    
13. *GET* - ```https://paperlessapi.000webhostapp.com/getBook```
  
	*Required Parameters*  
    - id (BookId)

14. *POST* - ```https://paperlessapi.000webhostapp.com/addBook```
  
	*Required Parameters*  
    - name
    - author
    - publication
    - title
    - img
    - book (File Type)

15. *GET* - ```https://paperlessapi.000webhostapp.com/getPublisherBooks```
  
	*Required Parameters*  
    - publisher (Publisher Name)  

16. *GET* - ```https://paperlessapi.000webhostapp.com/getOffers```

	*Required Parameters*  
    - rid (Restaurant ID)   

17. *POST* - ```https://paperlessapi.000webhostapp.com/userLikedRestaurants```
  
	*Required Parameters*
    - username

18. *GET* - ```https://paperlessapi.000webhostapp.com/isRestaurantLiked```
  
	*Required Parameters*
    - username
    - rid (restaurantID)

19. *GET* - ```https://paperlessapi.000webhostapp.com/isRestaurantFollowed```
  
	*Required Parameters*
    - username
    - rid (restaurantID)

20. *POST* - ```https://paperlessapi.000webhostapp.com/addGiftType```
  
	*Required Parameters*
    - name (unique)
    - details
    - image (File Type)

21. ```https://paperlessapi.000webhostapp.com/showGiftTypes```
  
	*Required Parameters*
    - No params required

22. *GET* - ```https://paperlessapi.000webhostapp.com/getGiftType```
  
	*Required Parameters*  
    - id (GiftTypeId)

23. *POST* - ```https://paperlessapi.000webhostapp.com/addGift```
  
	*Required Parameters*
    - name (unique)
    - details
    - price
    - giftTypeID
    - image (File Type)

24. ```https://paperlessapi.000webhostapp.com/showGifts```
  
	*Required Parameters*
    - No params required

25. *GET* - ```https://paperlessapi.000webhostapp.com/getGift```
  
	*Required Parameters*  
    - id (GiftId)

26. *POST* - ```https://paperlessapi.000webhostapp.com/purchaseGift```
  
	*Required Parameters*
    - username
    - giftID
    - giftTypeID
    - paymentID  

27. *POST* - ```https://paperlessapi.000webhostapp.com/sendGift```
  
	*Required Parameters*
    - sender (Sender username)
    - recipient (Recipient Username)
    - paymentID (Payment ID of linked gift)

28. *POST* - ```https://paperlessapi.000webhostapp.com/followUser```
  
	*Required Parameters*
    - followedBy (Followed By username)
    - followedTo (Followed To Username)

29. *POST* - ```https://paperlessapi.000webhostapp.com/isUserFollowed```
  
	*Required Parameters*
    - followedBy (Followed By username)
    - followedTo (Followed To Username)

30. *GET* - ```https://paperlessapi.000webhostapp.com/getFollowers```
  
	*Required Parameters*
    - username

31. *GET* - ```https://paperlessapi.000webhostapp.com/getFollowing```
  
	*Required Parameters*
    - username

32. *POST* - ```https://paperlessapi.000webhostapp.com/sendFriendRequest```
  
	*Required Parameters*
    - sentBy (username)
    - sentTo (username)

33. *POST* - ```https://paperlessapi.000webhostapp.com/addFriend```
  
	*Required Parameters*
    - addedBy (username)
    - addedTo (username)

18. *POST* - ```https://paperlessapi.000webhostapp.com/doesFriendExists```
  
	*Required Parameters*
    - addedBy (username)
    - addedTo (username)

34. *GET* - ```https://paperlessapi.000webhostapp.com/showFriends```
  
	*Required Parameters*
    - username