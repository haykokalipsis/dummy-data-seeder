# dummy-data-seeder
Dummy data seeder for databases on your localhost. I made this app, so it can help you to fill some dummy test data to your applications database, to make it easier to test your tables with a lot of records. Currently works only with MySQL.

Instructions.
  1. Git clone this project into your localhost folder (for example C:\xampp\htdocs\Username\).
  2. Navigate to the cloned folder, launch your command line utility, type 'composer update' (without quotes), hit enter. Wait till            composer installs dependencies.
  3. If your localhosts username is not root and your password is not empty, then navigate to Model.php, and on the top of the file make necessary changes (will make it more user friendly in future).
  4. The path to apps launch file should be something like 'http://localhost/dummy-data-seeder/index.php'. If your browser has a speed dial feature, you can add the path to index.php into it, and launch the app from there. If not, just enter the path in the url and hit enter.
  5. If everything is ok, the app should be able to retrieve your databases from localhost. 
  6. Select a database, a table you want to fill with dummy test records. Select what type of data you want to insert. Select the needed amount and hit Generate button. 
  7. Profit.
