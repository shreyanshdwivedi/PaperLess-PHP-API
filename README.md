### EndPoints

1. *POST* - ```https://paperlessapi.000webhostapp.com/createUser```  
  
	*Required Parameters*
    - name
    - username (unique)
    - email
    - phoneNum
    - dob
    - password  
    - image (File Type)

2. *POST* - ```https://paperlessapi.000webhostapp.com/userLogin```
  
	*Required Parameters*  
    - username
    - password  

3. *GET* - ```https://paperlessapi.000webhostapp.com/verifyUser```
  
	*Required Parameters*  
    - username
    - otp 

4. ```https://paperlessapi.000webhostapp.com/showRestaurants```
  
	*Required Parameters*
    - No params required

5. *POST* - ```https://paperlessapi.000webhostapp.com/addRestaurant```
  
	*Required Parameters*
    - name
    - email (unique)
    - contact
    - address  
    - image

6. *POST* - ```https://paperlessapi.000webhostapp.com/likeRestaurant```
  
	*Required Parameters*
    - username
    - restaurantID

7. *POST* - ```https://paperlessapi.000webhostapp.com/userLikedRestaurants```
  
	*Required Parameters*
    - username

8. *GET* - ```https://paperlessapi.000webhostapp.com/isRestaurantLiked```
  
	*Required Parameters*
    - username
    - restaurantID

9. *GET* - ```https://paperlessapi.000webhostapp.com/isRestaurantFollowed```
  
	*Required Parameters*
    - username
    - restaurantID
    
10. *POST* - ```https://paperlessapi.000webhostapp.com/followRestaurant```
  
	*Required Parameters*  
    - username
    - restaurantID

11. *POST* - ```https://paperlessapi.000webhostapp.com/userFollowedRestaurants```
  
	*Required Parameters*  
    - username 
   
12. ```https://paperlessapi.000webhostapp.com/showBooks```
  
	*Required Parameters*
    - No params required

13. *GET* - ```https://paperlessapi.000webhostapp.com/likeBook```
  
	*Required Parameters*
    - username
    - bookID

14. *POST* - ```https://paperlessapi.000webhostapp.com/bookmark```
  
	*Required Parameters*
    - username
    - bookID 
    
15. *GET* - ```https://paperlessapi.000webhostapp.com/getBook```
  
	*Required Parameters*  
    - bookID

16. *POST* - ```https://paperlessapi.000webhostapp.com/addBook```
  
	*Required Parameters*  
    - name
    - author
    - publication
    - title
    - img
    - book (File Type) 

17. *GET* - ```https://paperlessapi.000webhostapp.com/getRestaurantOffers```

	*Required Parameters*  
    - restaurant ID

18. *POST* - ```https://paperlessapi.000webhostapp.com/showOffers```

	*Required Parameters*  
    - No param

19. *POST* - ```https://paperlessapi.000webhostapp.com/addPublisher```

    *Required Parameters*
    - name
    - email
    - country
    - image

20. *POST* - ```https://paperlessapi.000webhostapp.com/showPublishers```

    *Required Parameters*
    - No params required

21. *GET* - ```https://paperlessapi.000webhostapp.com/getPublisher```

    *Required Parameters*
    - publisherID

22. *GET* - ```https://paperlessapi.000webhostapp.com/getPublisherBooks```

    *Required Parameters*
    - publisherID

23. *POST* - ```https://paperlessapi.000webhostapp.com/followPublisher```

    *Required Parameters*
    - username
    - publisherID

24. *POST* - ```https://paperlessapi.000webhostapp.com/isPublisherFollowed```

    *Required Parameters*
    - username
    - publisherID

25. *GET* - ```https://paperlessapi.000webhostapp.com/getFollowedPublishers```

    *Required Parameters*
    - username

26. *POST* - ```https://paperlessapi.000webhostapp.com/getRecentBooks```

    *Required Parameters*
    - No params required

27. *POST* - ```https://paperlessapi.000webhostapp.com/userLikedBooks```
  
	*Required Parameters*  
    - username

28. *POST* - ```https://paperlessapi.000webhostapp.com/getAllUsers```
  
	*Required Parameters*  
    - No params

29. *POST* - ```https://paperlessapi.000webhostapp.com/sendMessage```
  
	*Required Parameters*  
    - sender
    - recipient
    - message

30. *POST* - ```https://paperlessapi.000webhostapp.com/getMessageBetween```
  
	*Required Parameters*  
    - firstUser
    - secondUser

31. *GET* - ```https://paperlessapi.000webhostapp.com/isBookLiked```
  
	*Required Parameters*
    - username
    - bookID

32. *GET* - ```https://paperlessapi.000webhostapp.com/isBookBookmarked```
  
	*Required Parameters*
    - username
    - bookID

33. *POST* - ```https://paperlessapi.000webhostapp.com/getRecentPublishers```

    *Required Parameters*
    - No params required

34. *POST* - ```https://paperlessapi.000webhostapp.com/unlikeBook```

    *Required Parameters*
    - username
    - bookID

35. *POST* - ```https://paperlessapi.000webhostapp.com/unfollowPublisher```

    *Required Parameters*
    - username
    - publisherID

36. *POST* - ```https://paperlessapi.000webhostapp.com/removeBookmark```

    *Required Parameters*
    - username
    - bookID

37. *POST* - ```https://paperlessapi.000webhostapp.com/downloadBook```

    *Required Parameters*
    - username
    - bookID

38. *POST* - ```https://paperlessapi.000webhostapp.com/downloadedBooks```

    *Required Parameters*
    - username

39. *POST* - ```https://paperlessapi.000webhostapp.com/addOffer```
  
	*Required Parameters*  
    - name
    - percentOff
    - details
    - restaurantID
    - restaurantName
    - expiry
    - image (File Type) 

40. *POST* - ```https://paperlessapi.000webhostapp.com/userBookmarkedBooks```
  
	*Required Parameters*  
    - username