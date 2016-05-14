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
        return successResponse($result);
    } catch(PDOException $e) {
        return errorResponse($e->getMessage());

    }
}

function getStudents($type="all",
                     $crn = null,
                     $examSessionDate = null,
                     $id = null) {
    try {
        $dbHelper = new DatabaseHelper($GLOBALS['dbhost'], $GLOBALS['dbname'], $GLOBALS['dbusername'], $GLOBALS['dbpassword']);
        $dbHandle = $dbHelper->getConnection();
        $sql = '';
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
            case "single":
                $sql = "SELECT student.studentId, enrollment.examSessionId, courseCrn, firstName, lastName, dateTime
                        FROM enrollment
                          INNER JOIN student
                            ON enrollment.studentId=student.studentId
                          INNER JOIN examSession
                            ON enrollment.examSessionId=examSession.examSessionId
                          WHERE student.courseCRN=$crn AND student.studentId=$id;";
                break;
            default:
                //Should not be requested
                echo "Unknown request";
        }

        $stmt = $dbHandle->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            return errorResponse('no result from database');
            exit(1);
        }
        $dbHandle = null;
        return successResponse($result);

    } catch(PDOException $e) {
        return errorResponse($e->getMessage());
    }
}

function addStudent() {
    error_log('adding student');
//    var_dump($_POST);
    try {
        $dbHelper = new DatabaseHelper($GLOBALS['dbhost'], $GLOBALS['dbname'], $GLOBALS['dbusername'], $GLOBALS['dbpassword']);

        $row = array(
            "studentId" => "$_POST[id]",
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
            exit(1);
        }
        //The student is not enrolled in a session, lets add them to the student table
        $dbHelper->insert('student', $row);
        //Now lets also add them to the enrollment table
        $row = array(
            "studentId" => "$_POST[id]",
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
        $studentId = $_POST['id'];
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
        $responseData = array(
            "studentId" => "$_POST[id]",
            "examSessionId" => "$_POST[examSessionId]"
        );
        $response = array(
            'data' => $responseData
        );
//        error_log('The response will be');
//        error_log(json_encode($response));
        return $response;
    } catch(PDOException $e) {
        return $e->getMessage();
    }
}

function removeStudentFromSession() {
    try {
        $dbHelper = new DatabaseHelper($GLOBALS['dbhost'], $GLOBALS['dbname'], $GLOBALS['dbusername'], $GLOBALS['dbpassword']);
        $dbHandle = $dbHelper->getConnection();

        $row = array(
            "studentId" => "$_POST[id]",
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
                WHERE studentId=$_POST[id];";
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
            'message' => $message,
            'data' => ''
           );
}

function successResponse($data) {
    return array(
        'status' => 'ok',
        'data' => $data
    );
}
