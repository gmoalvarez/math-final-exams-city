<?php
require_once 'config.php';
require_once 'DatabaseHelper.php';

function getExamSessionAvailability() {
    try {
        $dbHelper = new DatabaseHelper($GLOBALS['dbhost'], $GLOBALS['dbname'], $GLOBALS['dbusername'], $GLOBALS['dbpassword']);
        $dbHandle = $dbHelper->getConnection();
        $sql = "SELECT examSessionId, dateTime, seatsAvailable
                    FROM examSession";
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

function getStudents($type="all",
                     $crn = null,
                     $examSessionDate = null) {
    try {
        $dbHelper = new DatabaseHelper($GLOBALS['dbhost'], $GLOBALS['dbname'], $GLOBALS['dbusername'], $GLOBALS['dbpassword']);
        $dbHandle = $dbHelper->getConnection();
        switch ($type) {
            case "all":
                $sql = "SELECT student.studentId, enrollment.examSessionId, courseCRN, firstName, lastName, dateTime
                    FROM enrollment 
                      INNER JOIN student 
                        ON enrollment.studentId=student.studentId
                      INNER JOIN examSession
                        ON enrollment.examSessionId=examSession.examSessionId;";
                break;
            case "crn":
                $sql = "SELECT student.studentId, enrollment.examSessionId, courseCRN, firstName, lastName, dateTime
                    FROM enrollment 
                      INNER JOIN student 
                        ON enrollment.studentId=student.studentId
                      INNER JOIN examSession
                        ON enrollment.examSessionId=examSession.examSessionId
                      WHERE courseCRN=$crn;";
                break;
            case "date":
                $sql = "SELECT student.studentId, enrollment.examSessionId, courseCRN, firstName, lastName, dateTime
                    FROM enrollment 
                      INNER JOIN student 
                        ON enrollment.studentId=student.studentId
                      INNER JOIN examSession
                        ON enrollment.examSessionId=examSession.examSessionId
                      WHERE dateTime='$examSessionDate';";
                break;
            default:
                //Should not be requested
                echo "Unknown request";
        }

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

function addStudent() {
    $dbHost = $GLOBALS['dbhost'];
    $dbName = $GLOBALS['dbname'];
    $dbUser = $GLOBALS['dbusername'];
    $dbPass = $GLOBALS['dbpassword'];

    try {
        $dbHelper = new DatabaseHelper($dbHost, $dbName, $dbUser, $dbPass);

        $row = array(
            "studentId" => "$_POST[csid]",
            "courseCRN" => "$_POST[crn]",
            "firstName" => "$_POST[firstName]",
            "lastName" => "$_POST[lastName]"
            );
        //Lets first check to see if the student has already signed up. If so, we should
        //prompt them to change instead
        if($dbHelper->exists("student", $row)) { 
            return errorResponse('Student is already signed up. Change instead');
            exit(1);
        }
        //Lets make sure there is enough space in a session (this is checked client side but what if
        //two requests come in at the same time?
        if (!seatAvailableInSessionWithId($_POST['examSessionId'])) {
            return errorResponse('This session is currently full. Please choose another session');
        }
        //The student is not enrolled in a session, lets add them to the student table
        $dbHelper->insert('student', $row);
        //Now lets also add them to the enrollment table
        $row = array(
            "studentId" => "$_POST[csid]",
            "examSessionId" => "$_POST[examSessionId]"
        );
        $dbHelper->insert('enrollment', $row);
        //Now lets reduce the seats available for this session by one
        $row = array (
            "examSessionId" => "$_POST[examSessionId]"
        );
        $dbHelper->reduceByOne('examSession', 'seatsAvailable', $row);
        //If we make it here without any errors, lets return a success message
        $dbHelper->closeConnection();
        return "success";
    } catch(PDOException $e) {
        return $e->getMessage();
    }
}

function changeStudent(){

    try {
        $dbHelper = new DatabaseHelper($GLOBALS['dbhost'], $GLOBALS['dbname'], $GLOBALS['dbusername'], $GLOBALS['dbpassword']);
        $dbHandle = $dbHelper->getConnection();

        $newExamSessionId = $_POST['examSessionId'];
        $studentId = $_POST['csid'];
        //Lets first get the current student Id.
        // If we find one, we will just update the examSessionId for this student
        //If we do not find one, we will add the student id and examSessionId to the enrollment table
        //Either way we will also update the seats available for the different sessions
        $sql = "SELECT examSessionId FROM enrollment WHERE studentId=$studentId;";
        $stmt = $dbHandle->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldExamSessionId = $result['examSessionId'];
        if ($oldExamSessionId) {
            //update enrollment table with new session id
            $sql = "UPDATE enrollment SET examSessionId=$newExamSessionId WHERE studentId=$studentId";
            $count = $dbHandle->exec($sql);
            if ($count !== 1) {
                return errorResponse("We updated $count students. That is not right! It should be 1");
                exit(1);
            }
            //Make the seat available for the old session
            $sql = "UPDATE examSession SET seatsAvailable=seatsAvailable+1 WHERE examSessionId=$oldExamSessionId";
            $count = $dbHandle->exec($sql);
            if ($count !== 1) {
                return errorResponse("There was an error increasing the seats available for id:$oldExamSessionId");
                exit(1);
            }
        } else { //Student was enrolled at one point but not right now, lets just add him to the enrollment table
            $row = array(
                'studentId' => $studentId,
                'examSessionId' => $newExamSessionId
            );
            $dbHelper->insert('enrollment', $row);
        }
        //Reduce the new session seats available by 1
        $sql = "UPDATE examSession SET seatsAvailable=seatsAvailable-1 WHERE examSessionId=$newExamSessionId";
        $count = $dbHandle->exec($sql);
        if ($count !== 1) {
            return errorResponse("There was an error reducing the seats available for id:$newExamSessionId");
            exit(1);
        }

        $dbHelper->closeConnection();
        return "success";
    } catch(PDOException $e) {
        return $e->getMessage();
    }
}

function removeStudentFromSession() {
    try {
        $dbHelper = new DatabaseHelper($GLOBALS['dbhost'], $GLOBALS['dbname'], $GLOBALS['dbusername'], $GLOBALS['dbpassword']);
        $dbHandle = $dbHelper->getConnection();

        $row = array(
            "studentId" => "$_POST[csid]",
            "examSessionId" => "$_POST[examSessionId]",
        );
        //Lets first check to see if the student has already signed up. He should be, but
        //lets just double check anyways
        if(!$dbHelper->exists("enrollment", $row)) {
            return errorResponse('Student is not enrolled in this section. Removal is unnecessary');
            exit(1);
        }

        //The student is enrolled in this session, lets remove them from the enrollment table
        $sql = "DELETE FROM enrollment
                WHERE studentId=$_POST[csid];";
        $count = $dbHandle->exec($sql); //should be 1 since we will remove one student from enrollment
        if ($count !== 1) {
            return errorResponse("We removed $count students. That is not right!");
            exit(1);
        }

        $dbHelper->closeConnection();
        return "success";
    } catch(PDOException $e) {
        return $e->getMessage();
    }
}

//Change the watch count. Typically byAmount will equal to 1 but if we want to change it
//by a different amount we can do so by changing that value.
//The return value is the number of rows that were affected (should be 1)
function changeWatchCount($studentId, $videoId, $byAmount) {
    $dbHost = $GLOBALS['dbhost'];
    $dbName = $GLOBALS['dbname'];
    $dbUser = $GLOBALS['dbusername'];
    $dbPass = $GLOBALS['pass'];
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

    $dbHost = $GLOBALS['dbhost'];
    $dbName = $GLOBALS['dbname'];
    $dbUser = $GLOBALS['dbusername'];
    $dbPass = $GLOBALS['dbpassword'];

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

function seatAvailableInSessionWithId($id) {
    $dbHelper = new DatabaseHelper($GLOBALS['dbhost'],$GLOBALS['dbname'],$GLOBALS['dbusername'],$GLOBALS['dbpassword']);
    $dbHandle = $dbHelper->getConnection();
    $sql = "SELECT seatsAvailable
                FROM examSession
                WHERE examSessionId=$id;";
    $stmt = $dbHandle->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $seatsAvailable = intval($result['seatsAvailable']);
    return $seatsAvailable > 0;
}

function errorResponse($message) {
    return array(
            'status' => 'error',
            'message' => $message
           );
}