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

3. *GET* - ```https://surpriseapi.000webhostapp.com/verifyUser```
  
	*Required Parameters*  
    - username
    - apikey 

4. ```https://paperlessapi.000webhostapp.com/showRestaurants```
  
	*Required Parameters*
    - No params required

5. *POST* - ```https://paperlessapi.000webhostapp.com/addRestaurant```
  
	*Required Parameters*
    - name
    - email (unique)
    - contact
    - address  

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

25. *POST* - ```https://paperlessapi.000webhostapp.com/getFolowedPublishers```

    *Required Parameters*
    - username

26. *POST* - ```https://paperlessapi.000webhostapp.com/getRecentBooks```

    *Required Parameters*
    - No params required

27. *POST* - ```https://paperlessapi.000webhostapp.com/userLikedBooks```
  
	*Required Parameters*  
    - username