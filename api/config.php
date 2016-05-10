<?php
//This file holds all of the configuration necessary in the $config array based off of this tutorial:
//http://code.tutsplus.com/tutorials/organize-your-next-php-project-the-right-way--net-5873
$config = array(
    "db" => array(
        "final_exams_summer_2016" => array(                  //remote host database has same name but different username and password
            "dbname" => "final_exams_summer_2016",
            "username" => "root",
            "password" => "root",
            "host" => "localhost"
        )
    )
);