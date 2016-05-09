<?php

require_once 'getHelperFunctions.php';
$dbHost = "localhost";
$dbName = "calcsuccess1";
$dbUser = "root";
$dbPass = "root";
//
// session_start();
//API for calcsuccess website. The different actions we should be able to perform are:
//GET Requests (in no particular order):
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['list'])) { //Fetch list of some videos, students, or courses
        $listRequest = $_GET['list'];
        if ($listRequest === 'video') {
            //This means we want a list of videos
            if (isset($_GET['student_id'])) {
                //4) list of videos that a student has watched?list=video&student_id=student_id
                $studentId = $_GET['student_id'];
                echo json_encode(getList('student', null, $studentId));
            } else {
                //1) Video List ?list=video
                echo json_encode(getList());
            }
        } else if ($listRequest === 'student') {//we want a list of students
            //3) list of students that have watched video ?list=student&video_id=youtube_id
            //This means we want the list of students that have watched
            //a particular video

            //we need a video id to get the list of students
            if (isset($_GET['video_id'])) {
                $videoId = $_GET['video_id'];
                echo json_encode(getList('student',$videoId));
            }


            //5) list of students in course ?list=student&course_id=course_id
            //This means we want the list of students in a particular course
            if (isset($_GET['course_id'])) {
                $courseId = $_GET['course_id'];
                echo json_encode(getList('student', null, null, $courseId));
            }
        } else if ($listRequest === 'course') {
            //6) list of courses ?list=course
            echo json_encode(getList('course'));
        }
    } else if(isset($_GET['video_id'])) { //FETCH A SINGLE VIDEO
        //2) video with youtube id &video_id=youtube_id
        $videoId = $_GET['video_id'];
        echo json_encode(getList('video', $videoId));
    } else if (isset($_GET['course_id'])) {
        $courseId = $_GET['course_id'];
        //7) course info given id ?course_id=course_id
        echo json_encode(getList('course', null, null, $courseId));
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //1) Update watch list of student after they watch it
    //The post request should contain both student id and video_id
    $action = $_POST['action'];
    if (!isset($action)) {
        echo "ERROR, empty post request!";
        exit(1);
    }
    if ($action === 'update-watch-count') {
        $studentId = $_POST['student_id'];
        $videoId = $_POST['video_id'];
        //Check for null values and return error if there is one
        if ($studentId === null || $videoId == null) {
            echo "ERROR, we need both student id and video id to update";
            exit(1);
        }

        //If the student has already watched this video, we will increment
        //If the student has not watched the video, we will add it to the
        //student_video table
        //$count should equal to 1 since we are only changing one row
        $count = changeWatchCount($studentId, $videoId, 1); //should return 1
        if ($count === 1) {
            echo "SUCCESSFULLY updated watch count";
        } else {
            echo "Count is $count but it should equal 1";
        }
    } else if ($action === 'register') {
        processRegistration();
    }
} else {
    echo "No GET OR POST!!! YIKES!";
}


    //8) list of user info ?list=user

    //POST requests

    //2)TODO:  Register user for course. Currently we only support a single user
    //3) Login user into account

