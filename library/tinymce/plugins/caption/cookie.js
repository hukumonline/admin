//downloaded from http://scripts.franciscocharrua.com/javascript_cookies.php
function setCookie(name, value)
         {
         //If name is the empty string, it places a ; at the beginning
         //of document.cookie, causing clearCookies() to malfunction.
         if(name != '')
            document.cookie = name + '=' + value;
         }

function getCookie(name)
         {
         //Without this, it will return the first value 
         //in document.cookie when name is the empty string.
         if(name == '')
            return('');
         
         name_index = document.cookie.indexOf(name + '=');
         
         if(name_index == -1)
            return('');
         
         cookie_value =  document.cookie.substr(name_index + name.length + 1, 
                                                document.cookie.length);
         
         //All cookie name-value pairs end with a semi-colon, except the last one.
         end_of_cookie = cookie_value.indexOf(';');
         if(end_of_cookie != -1)
            cookie_value = cookie_value.substr(0, end_of_cookie);

         //Restores all the blank spaces.
         space = cookie_value.indexOf('+');
         while(space != -1)
              { 
              cookie_value = cookie_value.substr(0, space) + ' ' + 
              cookie_value.substr(space + 1, cookie_value.length);
							 
              space = cookie_value.indexOf('+');
              }

         return(cookie_value);
         }

function clearCookie(name)
         {                  
         expires = new Date();
         expires.setYear(expires.getYear() - 1);

         document.cookie = name + '=null' + '; expires=' + expires; 		 
         }
         
function clearCookies()
         {
         Cookies = document.cookie;
         Cookie = Cookies;
         expires = new Date();
         expires.setYear(expires.getYear() - 1);

         while(Cookie.length > 0)
              {
              //All cookie name-value pairs end with a semi-colon, except the last one.
              Cookie = Cookies.substr(0, Cookies.indexOf(';'));
              Cookies = Cookies.substr(Cookies.indexOf(';') + 1, Cookies.length);

              if(Cookie != '')
                 document.cookie = Cookie + '; expires=' + expires;
              else
                 document.cookie = Cookies + '; expires=' + expires;			  			  	  
              }		 		 
         }