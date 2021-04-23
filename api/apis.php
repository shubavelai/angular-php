<?php
header('Content-type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST,GET,PUT,REQUEST");
header("Access-Control-Request-Headers: Content-type,Access-Control-Allow-Headers,Access-Control-Allow-Methods,Athorization, X-Requested-With");

// $conn = new mysqli('localhost', 'triplech_aruna', 'rHDcc6PItFwK', 'triplech_aruna');
$conn = new mysqli('localhost', 'root', '', 'aruna');
date_default_timezone_set("Asia/Kolkata");
$dateTime = date('d/m/Y h:i:s a', time());
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$uploadDir = $baseUrl . "/../uploads/";
$data = json_decode(file_get_contents('php://input'));
$count = 1;
$arrayData = json_decode(file_get_contents('php://input'), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data->action)) {
        if ($data->action == "sendOTP") {
            if (strlen($data->mobileNo) == 10) {
                $select = "SELECT * FROM `usersdetail` WHERE `mobile`='" . $arrayData["mobileNo"] . "' ";
                $result = $conn->query($select);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        if (!$row["status"] || $row["status"] == 0) {
                            $arr = array("msg" => false, "status" => "Invalid User!");
                            echo json_encode($arr);
                            exit;
                        }
                        $userId = $row["parentId"];
                    }
                    $status = "Existing User";
                } else {
                    $insert = "INSERT INTO `usersdetail` (`mobile`,`status`,`createdOn`) VALUES ('" . $arrayData["mobileNo"] . "',1,'" . $dateTime . "')";
                    if ($conn->query($insert))   $userId = (string)mysqli_insert_id($conn);
                    $status = "New User";
                }
                $arr = array("msg" => true, "status" => $status, "userid" => $userId);
            } else {
                $arr = array("msg" => false, "status" => "Invalid Mobile Number!");
            }
        } else
    
        if ($data->action == "verifyOTP") {
            $arr = array("msg" => true, "status" => "verified", "userid" => $data->userid);
        } else if ($data->action == "getNotification") {
            $res = array(
                array("id" => 1, "icon" => "eyedrop", "msg" => "Next Vaccination Reminder", "date" => "20th Aug 2021"),
                array("id" => 1, "icon" => "eyedrop", "msg" => "Next Vaccination Reminder", "date" => "20th Aug 2021"),
                array("id" => 1, "icon" => "eyedrop", "msg" => "Next Vaccination Reminder", "date" => "20th Aug 2021"),
                array("id" => 1, "icon" => "eyedrop", "msg" => "Next Vaccination Reminder", "date" => "20th Aug 2021"),
                array("id" => 1, "icon" => "eyedrop", "msg" => "Next Vaccination Reminder", "date" => "20th Aug 2021"),
            );
            $arr = array("msg" => true);
            array_push($arr, $res);
        } else if ($data->action == "getChildDetail") { //to get child details bu child ID
            //$arr = array("id" => "1", "img" => "https://www.itl.cat/pngfile/big/62-625306_child-cute-dp-pic-whatsapp.jpg", "name" => "haha", "age" => "05", "gender" => "male");
            $id = ($arrayData && isset($arrayData['id'])) ? $arrayData['id'] : '';
            $arr = [];
            if ($id) {
                $select = "SELECT * FROM `childdetail` WHERE `childId`= $id and childStatus=1";

                $result = $conn->query($select);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $dob = $row["childDOB"];
                        $age = 0;
                        if ($dob) {
                            $age = getAge($dob);
                        }
                        $dp = ($row['dp'] != "") ? $uploadDir . $row['dp'] : "";
                        $arr = array(
                            "sno" => $count++, "parentId" => (int) $row["parentId"], "childId" => (int) $row["childId"], "name" => $row["childName"], "gender" => $row["childGender"], "age" => $age, "blood" => $row["childBlood"], "dp" => $dp
                        );
                    }
                }
            }
        } else if ($data->action == "getTime") {
            $res = array(
                array("time" => "10.00 A.M"), array("time" => "09.00 A.M"), array("time" => "8.00 A.M"), array("time" => "11.00 A.M"),
                array("time" => "12.00 A.M"), array("time" => "01.00 A.M"), array("time" => "02.00 A.M"), array("time" => "03.00 A.M"),
                array("time" => "05.00 A.M"), array("time" => "06.00 A.M"), array("time" => "08.00 A.M"), array("time" => "10.00 A.M"),
            );
            $arr = array("msg" => $data->id);
            array_push($arr, $res);
        } else if ($data->action == "getUsers") {
            $select = "SELECT * FROM `usersdetail` WHERE `status`='" . $arrayData["id"] . "'";
            // echo $select;exit;
            $arr = array();
            $result = $conn->query($select);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $dp = ($row["dp"] != "") ? $uploadDir . $row["dp"] : "";
                    $res = array(
                        "sno" => $count++, "id" => $row["parentId"], "name" => $row["name"], "address" => $row["address1"] . "," . $row["address2"] . "," . $row["address3"], "mobile" => $row["mobile"], "mail" => $row["mail"], "gender" => $row["gender"], "blood" => $row["blood"], "dp" => $dp
                    );
                    array_push($arr, $res);
                }
            }
        } else if ($data->action == "filterData") {
            // $select = "SELECT * FROM `usersdetail` WHERE `status`='" . $arrayData["id"] . "'";

            $result = $conn->query($arrayData["id"]["query"]);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $arr = $row;
                }
            }
        } else if ($data->action == "addUser") {
            $data = $arrayData["id"];
            $dp = '';
            if (isset($data["dp"]) && $data["dp"] != '') {
                $dp = uploadImage($data["dp"]);
            }

            $insert = "INSERT INTO `usersdetail` (`name`,`mail`,`mobile`,`address1`,`address2`,`address3`,`blood`,`gender`,`status`,`createdOn`,`dp`) VALUES 
            ('" . $data["name"] . "','" . $data["mail"] . "','" . $data["mobile"] . "','" . $data["address1"] . "','" . $data["address2"] . "','" . $data["address3"] . "','" . $data["blood"] . "','" . $data["gender"] . "','1','$dateTime','" . $dp . "')";
            if ($conn->query($insert)) $arr = true;
            else $arr = mysqli_errno($conn);
        } else if ($data->action == "updateUser") {
            $data = $arrayData["id"];
            if (isset($data['dp']) && $data['dp'] != '') {
                $dp = uploadImage($data['dp']);
                $update = "UPDATE `usersdetail` SET `name`='" . $data["name"] . "',`mail`='" . $data["mail"] . "',`mobile`='" . $data["mobile"] . "',`address1`='" . $data["address1"] . "',`address2`='" . $data["address2"] . "',`address3`='" . $data["address3"] . "',`blood`='" . $data["blood"] . "',`gender`='" . $data["gender"] . "',`updatedOn`='$dateTime', `dp`='" . $dp . "' WHERE `parentId`='" . $data["id"] . "'";
            } else {
                $update = "UPDATE `usersdetail` SET `name`='" . $data["name"] . "',`mail`='" . $data["mail"] . "',`mobile`='" . $data["mobile"] . "',`address1`='" . $data["address1"] . "',`address2`='" . $data["address2"] . "',`address3`='" . $data["address3"] . "',`blood`='" . $data["blood"] . "',`gender`='" . $data["gender"] . "',`updatedOn`='$dateTime' WHERE `parentId`='" . $data["id"] . "'";
            }
            if ($conn->query($update)) $arr = true;
            else $arr = mysqli_errno($conn);
        } else if ($data->action == "delete") {
            $data = $arrayData["id"];
            $query = "DELETE FROM `" . $data["table"] . "` WHERE `" . $data["idname"] . "`='" . $data["id"] . "'";
            if ($conn->query($query)) $arr = true;
            else $arr = mysqli_error($conn);
        } else if ($data->action == "remove") {
            $data = $arrayData["id"];
            $query = "UPDATE `" . $data["table"] . "` SET `status`='" . $data["status"] . "'  WHERE `" . $data["idname"] . "`='" . $data["id"] . "'";
            if ($conn->query($query)) $arr = true;
            else $arr = mysqli_error($conn);
        } else if ($data->action == "addNewChild") {
            $data = $arrayData["id"];
            $arr = $data;
            $dp = '';
            if (isset($data["dp"]) && $data["dp"] != '') {
                $dp = uploadImage($data["dp"]);
            }
            $query = "INSERT INTO `childdetail` (`parentId`,`relationship`,`childName`,`childGender`,`childDOB`,`childBlood`,`childStatus`,`dp`,`createdOn`) VALUES 
            ('" . $data["parentId"] . "','".$data["relationship"]."','" . $data["name"] . "','" . $data["gender"] . "','" . $data["dob"] . "','" . $data["blood"] . "','1','" . $dp . "','" . $dateTime . "')";
            if ($conn->query($query)) {
                $arr = true;
                
                $data["dob"] = str_replace('/','-',$data['dob']);
                $sixweeks = date('d/m/Y', strtotime($data["dob"]. ' + 42 day'));
                $tenweeks = date('d/m/Y', strtotime($data["dob"]. ' + 70 day'));
                $fourteenweeks = date('d/m/Y', strtotime($data["dob"]. ' + 98 day'));
                $sixmonths = date('d/m/Y', strtotime($data["dob"]. ' + 180 day'));
                $sevenmonths = date('d/m/Y', strtotime($data["dob"]. ' + 210 day'));
                $ninemonths = date('d/m/Y', strtotime($data["dob"]. ' + 270 day'));
                $ninetotwelvemonths = date('d/m/Y', strtotime($data["dob"]. ' + 271 day')).' to '.date('d/m/Y', strtotime($data["dob"]. ' + 360 day'));
                $twelvemonths = date('d/m/Y', strtotime($data["dob"]. ' + 360 day'));
                $fifteenmonths = date('d/m/Y', strtotime($data["dob"]. ' + 450 day'));
                $sixteentoeighteenmonths = date('d/m/Y', strtotime($data["dob"]. ' + 480 day')).' to '.date('d/m/Y', strtotime($data["dob"]. ' + 540 day'));
                $eighteenmonths = date('d/m/Y', strtotime($data["dob"]. ' + 540 day'));
                $fourtosixyears = date('d/m/Y', strtotime($data["dob"]. ' + 1440 day')).' to '.date('d/m/Y', strtotime($data["dob"]. ' + 2190 day'));
                $tentotwelveyears = date('d/m/Y', strtotime($data["dob"]. ' + 3650 day')).' to '.date('d/m/Y', strtotime($data["dob"]. ' + 4380 day'));
                
                $userId = (string)mysqli_insert_id($conn);
                
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','6th Week','$sixweeks','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','10th Week','$tenweeks','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','14th Week','$fourteenweeks','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','6th Month','$sixmonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','7th Month','$sevenmonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','9th Month','$ninemonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','9 to 12 Month','$ninetotwelvemonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','12th Month','$twelvemonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','15th Month','$fifteenmonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','16 to 18 Month','$sixteentoeighteenmonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','18th Month','$eighteenmonths','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','4 to 6 Year','$fourtosixyears','0')";$conn->query($insert);
                $insert = "INSERT INTO `vaccinationdetail` (`childId`,`period`,`date`,`status`) VALUES ('$userId','10 to 12 Year','$tentotwelveyears','0')";$conn->query($insert);
            }
            else $arr = mysqli_error($conn);
        } else if ($data->action == "getLoginDetails") { //to handle login
            $data = $arrayData["id"];
            $arr = [];
            $userName = $data['username'];
            $password = md5($data['password']);
            $select = "SELECT * FROM `usersdetail` WHERE name='$userName' and password='$password' and status=1";
            $result = $conn->query($select);
            if ($result->num_rows > 0) {
                $arr = $result;
            } else {
                $arr = false; //"Invalid Credentials";
            }
        } else if ($data->action == "getChildList") { //to get all the child details by parent ID
            $parentId = (isset($arrayData) && $arrayData['id']) ? $arrayData['id'] : '';
            $data = [];
            $res = [];
            $msg = false;
            if ($parentId) {
                $select = "SELECT * FROM `childdetail` WHERE `parentId`= $parentId and childStatus=1";
                $result = $conn->query($select);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $data = [
                            "sno" => $count++,
                            "parentId" => (int) $row["parentId"],
                            "childId" => (int) $row["childId"],
                            "name" => $row["childName"],
                            "gender" => $row["childGender"],
                            "dob" => $row["childDOB"],
                            "age" => getAge($row["childDOB"]),
                            "blood" => $row["childBlood"],
                            "dp" => ($row["dp"] != '') ? $uploadDir . $row["dp"] : ""
                        ];
                        $res[] = $data;
                    }
                    $msg = true;
                }
            }
            $arr = array("msg" => $msg);
            array_push($arr, $res);
        } else if ($data->action == "bookAppointment") { //to create new appointment
            $data = $arrayData["id"];
            $date = $data['date'];
            $time = $data['time'];

            $insert = "INSERT INTO `appointments` (`parentId`,`childId`,`date`,`time`,`reason`,`createdOn`) VALUES (" . $data["parentId"] . "," . $data["childId"] . ",'" . $date . "','" . $time . "','" . $data["reason"] . "','$dateTime')";
            if ($conn->query($insert)) $arr = true;
            else $arr = mysqli_errno($conn);
        } else  if ($data->action == "getAppointments") { //to get the appointment by Parent and Child ID
            $data = (isset($arrayData) && $arrayData['id']) ? $arrayData['id'] : '';
            $parentId  = ($data && isset($data['parentId'])) ? $data['parentId'] : '';
            $childId  = ($data && isset($data['childId'])) ? $data['childId'] : '';

            $res = [];
            $msg = false;
            if ($data &&  ($parentId || $childId)) {
                $select = "SELECT ap.*,ch.childName,ch.dp as childDp, ch.childGender,ch.childDOB,ch.childBlood,ch.childStatus,pa.name parentName,pa.address1,pa.address2,pa.address3  FROM `appointments` ap LEFT JOIN usersdetail pa ON pa.parentId = ap.parentId LEFT JOIN childdetail ch ON ch.childId = ap.childId WHERE childStatus=1 AND pa.status=1 ";

                if ($parentId) {
                    $select .= " AND ap.parentId=$parentId ";
                }
                if ($childId) {
                    $select .= " AND ap.childId=$childId ";
                }

                $result = $conn->query($select);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $arr = [];
                        $approvalStatus = $row["approvalStatus"];
                        $address = $row['address1'];
                        if (isset($row['address2']) && $row['address2'] != '') {
                            $address =  $address . ', ' . $row['address2'];
                        }
                        if (isset($row['address3']) && $row['address3'] != '') {
                            $address =  $address . ', ' . $row['address3'];
                        }

                        $arr["sno"] = (int) $count++;
                        $arr["id"] = (int) $row["appointmentId"];
                        $arr["user"] = $row["parentName"];
                        $arr["child"] = $row['childName'];
                        $arr["dp"] = ($row["childDp"] != "") ? $uploadDir . $row["childDp"] : "";
                        $arr["address"] = $address;
                        $arr["age"] = getAge($row['childDOB']);
                        $arr["date"] = $row["date"];
                        $arr["time"] = $row["time"];
                        $arr["disease"] = $row["reason"];
                        $arr["approvalStatus"] = $approvalStatus;
                        $arr["status"] = $row["status"];
                        if ($approvalStatus != 'pending') {
                            $arr["doctorNotes"] = $row['doctorNotes'];
                        }

                        $res[] = $arr;
                    }
                    $msg = true;
                }
            }
            $arr = array("msg" => $msg);
            array_push($arr, $res);
        } else  if ($data->action == "getUpcomingAppointment") { //to get the upcoming appointments
            $data = (isset($arrayData) && $arrayData['id']) ? $arrayData['id'] : '';
            $parentId  = ($data && isset($data['parentId'])) ? $data['parentId'] : '';
            $childId  = ($data && isset($data['childId'])) ? $data['childId'] : '';

            $res = [];
            $msg = false;
            if ($data &&  ($parentId || $childId)) {
                $select = "SELECT ap.*,ch.childName,ch.dp as childDp,ch.childGender,ch.childDOB FROM `appointments` ap LEFT JOIN childdetail ch ON ch.childId = ap.childId WHERE childStatus=1 AND ap.approvalStatus='approved' AND date <= DATE_FORMAT(DATE(NOW() + INTERVAL (7 - DAYOFWEEK(NOW())) DAY), '%d/%m/%Y') ";
                if ($parentId) {
                    $select .= " AND ap.parentId=$parentId ";
                }
                if ($childId) {
                    $select .= " AND ap.childId=$childId ";
                }
                $select .= " ORDER BY date,time ASC LIMIT 1";
                $result = $conn->query($select);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $arr = [];
                        $approvalStatus = $row["approvalStatus"];
                        $arr["id"] = (int) $row["appointmentId"];
                        $arr["childId"] = (int) $row["childId"];
                        $arr["parentId"] = (int) $row["parentId"];
                        $arr["id"] = (int) $row["appointmentId"];
                        $arr["child"] = $row['childName'];
                        $arr["dp"] = ($row["childDp"] != "") ? $uploadDir . $row["childDp"] : "";
                        $arr["gender"] = $row['childGender'];
                        $arr["age"] = getAge($row['childDOB']);
                        $arr["date"] = $row["date"];
                        $arr["time"] = $row["time"];
                        $arr["disease"] = $row["reason"];
                        $arr["approvalStatus"] = $approvalStatus;
                        $arr["status"] = $row["status"];
                        $res[] = $arr;
                    }
                    $msg = true;
                }
            }
            $arr = array("msg" => $msg);
            array_push($arr, $res);
        } else  if ($data->action == "getTotalAppointments") { //to get the total appointment by approval status
            $data = (isset($arrayData) && $arrayData['id']) ? $arrayData['id'] : '';
            //$status = ($data && isset($data['status'])) ? $data['status'] : '';
            $arr = [];
            $totalCount = $pendingCount = $approvedCount = $rejectedCount = 0;

            $totalQuery =  "SELECT count(*) as count from appointments ";
            $approvedQuery =  "SELECT count(*) as count from appointments WHERE approvalStatus='approved'";
            $pendingQuery =  "SELECT count(*) as count from appointments WHERE approvalStatus='pending'";
            $rejectedQuery =  "SELECT count(*) as count from appointments WHERE approvalStatus='rejected'";

            $totalResult = $conn->query($totalQuery);
            $approvedResult = $conn->query($approvedQuery);
            $pendingResult = $conn->query($pendingQuery);
            $rejectedResult = $conn->query($rejectedQuery);

            if ($totalResult) {
                $totalResult = $totalResult->fetch_assoc();
                $totalCount = $totalResult['count'];
            }
            if ($approvedResult) {
                $approvedResult = $approvedResult->fetch_assoc();
                $approvedCount = $approvedResult['count'];
            }
            if ($pendingResult) {
                $pendingResult = $pendingResult->fetch_assoc();
                $pendingCount = $pendingResult['count'];
            }
            if ($rejectedResult) {
                $rejectedResult = $rejectedResult->fetch_assoc();
                $rejectedCount = $rejectedResult['count'];
            }

            $arr = [
                "total" => $totalCount,
                "approved" => $approvedCount,
                "pending" => $pendingCount,
                "rejected" => $rejectedCount
            ];
        } else  if ($data->action == "getTotalPatients") { //to get the total patients
            $data = (isset($arrayData) && $arrayData['id']) ? $arrayData['id'] : '';
            //$status = ($data && isset($data['status'])) ? $data['status'] : '';
            $res = [];
            $count = 0;

            $select =  "SELECT count(*) as count from childdetail WHERE childStatus=1";
            $result = $conn->query($select);
            //echo json_encode($$result);exit;
            if ($result) {
                $result = $result->fetch_assoc();
                $count = $result['count'];
            }
            $arr = $count;
        } else  if ($data->action == "getParentMonths") { //to get the total months of parents
            $data = (isset($arrayData) && $arrayData['id']) ? $arrayData['id'] : '';
            $parentId  = ($data && isset($data['parentId'])) ? $data['parentId'] : '';
            $arr = $resArr = [];
            $msg=false;
            if ($parentId) {
                $select =  "SELECT createdOn from usersdetail WHERE parentId=" . $parentId;
                $result = $conn->query($select);
                if ($result) {
                    $result = $result->fetch_assoc();
                    $date = $result['createdOn'];
                    $startDate = '';
                    if ($date) {
                        $res = explode("/", $date);
                        $changedDate = $res[2] . "-" . $res[0] . "-" . $res[1];
                        $monthArr = explode(" ", $res[2]);
                        $startDate = $monthArr[0] . "-" . $res[1] . "-" . $res[0];
                    }
                    $endDate = date("Y-m-d");
                    if ($startDate && $endDate) {
                        $resArr = getYearMonth($startDate, $endDate);
                        $msg=true;
                    }
                }
            }
            $arr = array("msg" => $msg);
            array_push($arr, $resArr);
        }else
        if($data->action == "getAllAppointments"){
            $arr = [];$res = [];$count = 0;
            $select = "SELECT * FROM `appointments` INNER JOIN usersdetail ON usersdetail.parentId = appointments.parentId INNER JOIN childdetail ON childdetail.childId = appointments.childId WHERE appointments.status= '0' ORDER BY appointments.date ASC";
            $result = $conn->query($select);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $dp = ($row['dp'] != "") ? $uploadDir . $row['dp'] : "";
                    $a = array("name"=>$row["name"],"reason"=>$row["reason"],"dp"=>$dp,"date"=>$row["date"],"childName"=>$row["childName"],"time"=>$row["time"],"mobile"=>$row["mobile"]);
                    array_push($res,$a);
                    $count++;
                }
            }
            $arr = array("msg"=>true,"data"=>$res,"count"=>$count);
        }else
        if($data->action == "getChild"){
            $data = $arrayData["id"];
            $select = "SELECT * FROM `childdetail` WHERE `patientId`='".$data["childId"]."' AND `childName`='".$data["name"]."' AND `childDOB`='".$data["dob"]."' ";
            $result = $conn->query($select);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if($row["parentId"] == ""){
                        $dp = ($row['dp'] != "") ? $uploadDir . $row['dp'] : "";
                        $a = array("id"=>$row["childId"],"dp"=>$dp,"name"=>$row["childName"],"dob"=>$row["childDOB"],"gender"=>$row["childGender"],"relation"=>$row["relationship"],"blood"=>$row["childBlood"]);
                        $arr = array("msg"=>true,"data"=>$a,"status"=>"");
                    }else 
                    if($row["parentId"] != ""){
                        $arr = array("msg"=>false,"status"=>"This child already has Parent");
                    } 
                }
            }else{
                $arr = array("msg"=>false,"status"=>"No Child match with provided details");
            }
        }else 
        if($data->action == "updateChild"){
            $data = $arrayData["id"];

            if($data["dp"] != ""){
                $update = "UPDATE `childdetail` SET `dp`='".$data["dp"]."' ";
                $conn->query($update);
            }

            $update = "UPDATE `childdetail` SET `parentId`='".$data["parentId"]."',`childName`='".$data["name"]."',`childDOB`='".$data["dob"]."',`childGender`='".$data["gender"]."',
            `childBlood`='".$data["blood"]."',`relationship`='".$data["relationship"]."'  WHERE `childId`='".$data["childId"]."' ";
            $arr = $conn->query($update);
        }
        else {
            $arr = ["msg" => false];
        }
    }
    echo (isset($arr) && $arr && $arr != null) ? json_encode($arr) : false;
    exit;
}



/**
 * To get the age by date of birth the format of param is dd/mm/yyyy
 */
function getAge($dob)
{
    $res = 0;

    if ($dob) {
        $newDate = date("Y-m-d", strtotime($dob));
        $newDate = str_replace('/', '-', $dob);
        $dob = date("Y-m-d", strtotime($newDate));
        $today = date("Y-m-d");
        $diff = date_diff(date_create($dob), date_create($today));
        $res = $diff->y;
    }

    return (int) $res;
}

/**
 * to convert basee64 string to image and upload to directory
 */
function uploadImage($img)
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: PUT, GET, POST");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

    $folder = "uploads/";

    $img = explode(";base64,", $img);
    $img_aux = explode("image/", $img[0]);

    $img_base64 = base64_decode($img[1]);
    $fileName = uniqid() . '.png';
    $image = $folder . $fileName;
    $res = file_put_contents($image, $img_base64);
    return ($res) ? $fileName : false;
}

function getYearMonth($start, $end)
{
    $start    = (new DateTime($start))->modify('first day of this month');
    $end      = (new DateTime($end))->modify('first day of next month');

    $interval = DateInterval::createFromDateString('1 month');
    $period   = new DatePeriod($start, $interval, $end);

    $res = [];
    if ($period) {
        foreach ($period as $dt) {
            $val = $dt->format("F Y");
            $res[] = $val;
        }
    }
    return $res;
}
