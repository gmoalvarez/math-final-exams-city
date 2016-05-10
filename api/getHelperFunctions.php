<?php
require_once 'config.php';
require_once 'DatabaseHelper.php';

function getAllStudents() {
    $dbHost = "localhost";
    $dbName = "final_exams_summer_2016";
    $dbUser = "root";
    $dbPass = "root";

    try {
        $dbHelper = new DatabaseHelper($dbHost, $dbName, $dbUser, $dbPass);
        $dbHandle = $dbHelper->getConnection();
        $sql = "SELECT student.studentId, enrollment.examSessionId, courseCRN, firstName, lastName, dateTime
                    FROM enrollment 
                      INNER JOIN student 
                        ON enrollment.studentId=student.studentId
                      INNER JOIN examSession
                        ON enrollment.examSessionId=examSession.examSessionId;";
        $stmt = $dbHandle->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            return "NO RESULT FROM SQL!";
        }
        $dbHandle = null;
        return $result;

    } catch(PDOException $e) {
        return $e->getMessage();
    }
}



function getList($type = "all",
                 $videoId = null, 
                 $studentId = null,
                 $courseId = null) {
    $dbHost = "localhost";
    $dbName = "final_exams_summer_2016";
    $dbUser = "root";
    $dbPass = "root";
    try {
        $dbHelper = new DatabaseHelper($dbHost,$dbName,$dbUser,$dbPass);
        $dbHandle = $dbHelper->getConnection();

        //What type of fetching do we want? Just one or all?
        if ($type === "all") {
            //return all videos
            $sql = "SELECT * FROM video";
        } else if($type === 'video') {
            //return single video with youtube_id=$videoId
            $sql = "SELECT * FROM video WHERE youtube_id='$videoId'";
        } else if ($type === 'student' && $videoId !== null) {
            //return list of students that have watched video with video_id=$videoId
            $sql = "SELECT * FROM student_video WHERE video_id='$videoId'";
        } else if ($type === 'student' && $studentId !== null) {
            //return list of videos that student with student_id=$studentId has watched
            $sql = "SELECT list_title,list_youtubeId,list_chapter,list_section, watch_count 
                    FROM calc_video_list 
                    INNER JOIN student_video 
                    ON calc_video_list.list_youtubeId=student_video.video_id
                    WHERE student_video.student_id=$studentId";
            error_log($sql);
        } else if ($type === 'student' && $courseId !== null) {
            $sql = "SELECT id, first_name, last_name, email, course_id
                    FROM student
                    INNER JOIN enrollment
                    ON student.id=enrollment.student_id
                    WHERE enrollment.course_id=$courseId";
        } else if ($type === 'course' && $courseId !== null) {
            $sql = "SELECT course_id, semester, year, course_name, admin_email
                    FROM course
                    WHERE course_id=$courseId";
        } else if ($type === 'course') {
            $sql = "SELECT course_id, semester, year, course_name, admin_email
                    FROM course";
        }
        //LETS FETCH
        $stmt = $dbHandle->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            return "NO RESULT FROM SQL!";
        }
        $dbHandle = null;
        return $result;

    } catch(PDOException $e) {
        return $e->getMessage();
    }
}

//Change the watch count. Typically byAmount will equal to 1 but if we want to change it
//by a different amount we can do so by changing that value.
//The return value is the number of rows that were affected (should be 1)
function changeWatchCount($studentId, $videoId, $byAmount) {
    $dbHost = "localhost";
    $dbName = "calcsuccess1";
    $dbUser = "root";
    $dbPass = "root";
    try {
        $dbHelper = new DatabaseHelper($dbHost,$dbName,$dbUser,$dbPass);
        $dbHandle = $dbHelper->getConnection();

        $row = array("video_id" => "$videoId", "student_id" => $studentId);
        if ($dbHelper->exists('student_video',$row)) { //if student has already watched this video, update it
            $sql = "UPDATE student_video 
                SET watch_count = watch_count + $byAmount 
                WHERE video_id='$videoId' 
                AND student_id=$studentId;";
            $count = $dbHandle->exec($sql);
        } else { //if the student has never watched the video, lets add it to the student_video table
            $row = array(
                'video_id' => $videoId,
                'student_id' => $studentId,
                'watch_count' => 1
            );
            $count = $dbHelper->insert('student_video',$row);
        }

        return $count;
    } catch(PDOException $e) {
        return $e->getMessage();
    }
}

function processRegistration() {
    $studentId = trim($_POST['student_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $userEmail = trim($_POST['user_email']);
    $userPassword = trim($_POST['password']);
    $courseId = trim($_POST['course_id']);
    $password = password_hash($userPassword, PASSWORD_DEFAULT)."\n";

    $dbHost = "localhost";
    $dbName = "calcsuccess1";
    $dbUser = "root";
    $dbPass = "root";

    try {
        $dbHelper = new DatabaseHelper($dbHost,$dbName,$dbUser,$dbPass);

        //First we check to see if the student is already registered
        if ($dbHelper->exists('student',array('email'=>$userEmail))) {
            echo "User already has an account. Please login";
            return false;
        }

        //Lets also make sure the course id is valid
        if (!$dbHelper->exists('course',array('course_id'=>$courseId))) {
            echo "Course not found. Please enter a correct course id";
            return false;
        }

        //At this point we know that the user does not already exist and the course id is valid
        //Lets add the student to the student table and the enrollment table to sign in
        //to the course
        $studentRow = array(
            'id' => $studentId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $userEmail ,
            'password' => $password
        );
        $dbHelper->insert('student',$studentRow);

        echo "OK, we inserted the student!\n";

        //Now lets update the enrollment table
        $enrollmentRow = array(
            'student_id' => $studentId,
            'course_id' => $courseId
        );
        $dbHelper->insert('enrollment',$enrollmentRow);
        echo "OK, we updated the enrollment list!\n";

    } catch(PDOException $e) {
        echo $e->getMessage();
    }
}