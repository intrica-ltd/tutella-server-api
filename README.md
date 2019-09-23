sudo apt-get install php7.1-curl

sudo apt-get install php7.1-mongodb


# AUTH WEB SERVICE

Create virtual host  auth.tutella.local ( you can change domain name ) 

composer install

sudo chmod 777 -R storage/

- open .env file and set DB credentionals ( see .env example )

php artisan migrate

php artisan passport:install ( see password client ID and secret and copy to main microservice's .env file => OAUTH client id and secret section )

php artisan l5-swagger:publish  ( http://auth.tutella.local/api/doc  )

php artisan l5-swagger:generate

php artisan db:seed (roles in DB)

 --  optional  (create admin from terminal )
     
         php artisan add_user:admin

# MAIN WEB SERVICE

create virtualhost domain.tutella.local 

composer install

sudo chmod 777 -R storage/

- open .env file and set DB credentionals and set AUTH client ID and client secret  ( see .env example )

php artisan migrate

php artisan l5-swagger:publish  ( http://domain.tutella.local/api/docs  )

php artisan l5-swagger:generate

php artisan app_access_token:reset