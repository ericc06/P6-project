# P6-project
Openclassrooms Symfony project (P6)

Demo application URL: https://www.arts-majeurs.com/
    
Demo application user connection details:

    Login: eric
    
    Pwd  : eric
    
Codacy and Codeclimate code analysis accessible here:

    - https://app.codacy.com/project/ericc06/P6-project/dashboard
    
    - https://codeclimate.com/github/ericc06/P6-project

**To install the application on a production server from the development server:**

1. On the production server launch these commands:

    wget https://github.com/ericc06/P6-project/archive/master.zip
    unzip master.zip
    mv P6-project-master/* P6-project-master/.* .
    rm -r P6-project-master/
    rm master.zip

2. On the dev server, build the assets files for the production:

    ./node_modules/.bin/encore production

   And transfer these files to the production server using FTP ou rsync.

3. On the production server:

  a. At this point, make sure that the Document Root of the web site is the root directory of the web site: check that no virtualhost is configured for the Document Root to be a subdirectory.
  
  b. Check that the Symfony requirements are OK:
  https://symfony.com/doc/current/reference/requirements.html
  
  c. Make a copy of the ".env.dist", call this copy ".env" and edit it this way:
  
    -	APP_ENV=prod
    -	APP_DEBUG=0
    -	APP_SECRET: 32 random characters (letters & figures)
    -	In DATABASE_URL, enter a string of this form "mysql://mysql_login:mysql_password@mysql_server_IP:mysql_port_number/database_name" where:
    
      o	mysql_login = MySQL user login (ex : root)
      o	mysql_password = this user's password
      o	mysql_server_IP = MySQL server IP address (ex : 127.0.0.1)
      o	mysql_port_number = MySQL server port (ex : 3306)
      o	database_name = database name
      
  d. Create the MySQL database manually and grant all privileges for the MySQL user configured in the ".env" file.
  
  e. Execute these commands:
  
      composer install --no-dev --optimize-autoloader --no-scripts
      composer require symfony/dotenv
      php bin/console cache:clear
      php bin/console doctrine:schema:create
      
  f. Configure a virtualhost for the Document Root to be the "public" folder.
  
4. Through a web navigator, browse to this URL to initialize the hard-coded trick groups:
     https://<domaine>/trickgroups/init-list
  
==> The production server is ready to go!


**To configure a development environment, follow the previous steps, and add these ones:**

1. Edit the "phpunit.xml.dist" file. On the following line:
    env name="DATABASE_URL" value="mysql://root:@127.0.0.1:3306/p6"/
   insert the database connection details like in the ".env" file.
   
2. Run these commands to load the fixtures and perform the unit tests:

    composer update
    php bin/console doctrine:fixtures:load
    php bin/phpunit
    
==> If the unit tests are OK, the development environment is ready.

