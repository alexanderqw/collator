collator 5.0.2
==============

Question Writer Collator is PHP software used for processing Question Writer quiz results into a database


Important Note:
---------------

Collator 5 is for use in conjunction with Question Writer HTML5 and is not backward compatible with earlier versions of QW nor of Collator.

If you're processing results for quizzes made with Question Writer 4 or earlier, use Collator 1.4
http://www.questionwriter.com/collator.html

Use a different database for Collator 1.4 and Collator 5 if you are running both.

Installation
-----------------------------------
1. Firstly create a new database and user on your MySQL database.

2. Run an SQL statement with the contents of the schema.txt file to create the tables in your database.

3. Place all the files in the collator5 directory onto your webserver.

4. Edit the config.php file with your database, email and backup server settings.

5. Change the true/false values in the 'codedpreferences.inc.php' file to set whether to send email, log xml, store results in the database.

6. Change the result server and backup server URLs in your Question Writer quiz to point to your new server. It should look something like
http://www.yourserver.com/collator5/qwhtml5.php
