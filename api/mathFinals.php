<?php

require_once 'apiHelperFunctions.php';
$dbHost = "localhost";
$dbName = "final_exams_summer_2016";
$dbUser = "root";
$dbPass = "root";
//
// session_start();
//API for math final exams website. The different actions we should be able to perform are:
//GET Requests (in no particular order):
// Get the list of exam sessions
// Get the enrollment list
//   Get the enrollment list by CRN
//   Get the enrollment list by DateTime

//POST Requests (in no particular order):
//  Add a student to an enrollment list
//  Remove a student from an enrollment list
//  Change a student from one section to another
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['enrollment'])) {
        //Lets get the request. The possibilities are:
            //1 - all    //get all enrolled students
            //2 - crn    //get all enrolled students given a crn
            //3 - datetime  //get all students enrolled in a particular session

        $request = $_GET['enrollment'];
        switch ($request) {
            case 'all': //1 - get all students
                echo json_encode(getStudents());
                break;
            case 'availability':
                echo json_encode(getExamSessionAvailability());
                break;
            case 'crn': //2 - crn    //get all enrolled students given a crn
                if (isset($_GET['crn'])) {
                    echo json_encode(getStudents("crn",$_GET['crn']));
                } else {
                    error_log('//Error. We need a CRN to do this');
                    exit(1);
                }
                break;
            case 'date': //3 - datetime  //get all students enrolled in a particular session
                if (isset($_GET['date'])) {
                    echo json_encode(getStudents("date",null, $_GET['date']));
                } else {
                    error_log('//ERROR. We need a date!');
                    exit(1);
                }
                break;
            default:
                error_log('//Not a recognized request!');
                exit(1);
        }
    } else {
        //Return an error header. Not a supported request
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //The supported POST requests are:
    //1 - add a student to a specific exam session
    //2 - change a student from one session to another
    //3 - remove a student from a session

    $action = $_POST['action'];
    if (!isset($action)) {
        echo "ERROR, empty post request!";
        exit(1);
    }
    
    switch ($action) {
        case 'add':
            echo json_encode(addStudent());
            break;
        case 'remove':
            echo json_encode(removeStudentFromSession());
            break;
        case 'change':
            echo json_encode(changeStudent());
            break;

    }
} else {
    echo "No GET OR POST!!! YIKES!";
}


    //8) list of user info ?list=user

    //POST requests

    //2)TODO:  Register user for course. Currently we only support a single user
    //3) Login user into account

