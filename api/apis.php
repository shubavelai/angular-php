<?php 
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: POST,GET,PUT,REQUEST");
    header("Access-Control-Request-Headers: Content-type,Access-Control-Allow-Headers,Access-Control-Allow-Methods,Athorization, X-Requested-With");
    
    $conn = new mysqli('localhost','root','','aruna');
    date_default_timezone_set("Asia/Kolkata");
    $dateTime = date('d/m/Y h:i:s a', time());
  
    $data = json_decode(file_get_contents('php://input'));
    $count = 1;
    $arrayData = json_decode(file_get_contents('php://input'),true);

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if($data->action == "sendOTP" ){
            if(strlen($data->mobileNo) == 10){
                $select = "SELECT * FROM `usersdetail` WHERE `mobile`='".$arrayData["mobileNo"]."'";
                $result = $conn->query($select);
                if ($result->num_rows > 0)
                {
                    while($row = $result->fetch_assoc())
                    {
                        $userId = $row["parentId"];
                    }
                }else{
                    $insert = "INSERT INTO `usersdetail` (`mobile`) VALUES ('".$arrayData["mobileNo"]."')";
                    if($conn->query($insert))   $userId = (string)mysqli_insert_id($conn);
                }
                $arr = array("msg"=>true,"status"=>"","userid"=>$userId);
            }else{
                $arr = array("msg"=>false,"status"=>"Invalid Mobile Number!");
            }
        }
    }

    echo json_encode($arr);
?>
