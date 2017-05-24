This repository is for the V3 of the filer. 

It is made by _Emmanuel MICARD_ and _Jonathan SIMONNEY_.

To use this repository, do not forget to install composer. (The **doc** is available [here](https://getcomposer.org/))  
and to run the command
>composer install  

or 
>php composer.phar install

(depending of whether you installed it locally or ,globally.)

You'll need to create your own private.yml file, and within it,
 put the following line :   
 >
 >db_config:
 >> name: _'YOUR_DB_NAME'_  
 >> host: _'YOUR_HOST'_  
 >> user: _'YOUR_ADMIN_USERNAME'_  
 >> pass:  _'YOUR_ADMIN_PASSWORD'_
 >
 >

This file should be put in the config folder. 

Also, 

Finally, create an _access.log_ AND a _security.log_ file in the logs directory.

TODO : 
avoid access to forbidden files via /.. (url rewrite???)\
add a go to root button (easier navigation...)