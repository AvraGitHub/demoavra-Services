<?php

$SAL_USER_ID = $_REQUEST['SAL_USER_ID'];
$SAP_OBJECT = $_REQUEST['SAP_OBJECT'];
$base = $_REQUEST['BASE'];
$head = $_REQUEST['HEAD'];
$reponame = $_REQUEST['REPO_NAME'];
$userauthor = $_REQUEST['USER_AUTHOR'];

$url = str_replace("+++","...","https://api.github.com/repos/AvraGitHub/$reponame/compare/$base+++$head");

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch,CURLOPT_USERAGENT,'$userauthor'); // Set a user agent
  $response =curl_exec($ch);

  curl_close($ch);
  $res = json_decode($response);

  if(json_encode($res) == '{"message":"Not Found","documentation_url":"https:\/\/developer.github.com\/v3"}')
  {
      jsonResponce(array('status' => 0, 'msg' => "User Repository Not on GitHub"));
  }
  else
  {

      $sd = json_encode($res->base_commit->commit->committer->date);
      $str = str_replace('"', " ",$sd);
      $strdt = str_replace('T', " ",$str);
      $strdate =  str_replace('Z', " ",$strdt);

      $f = json_encode($res->files[0]->filename);
      $file = str_replace('"', " ",$f);

      $s = json_encode($res->files[0]->status);
      $status = str_replace('"', "", $s);
      $st = "modified";


      if(strcmp($st,$status)==0){
            $sta = "UPDATED";
      }

      $p = json_encode($res->files[0]->patch);
      $patch = str_replace('"', " ",$p);
      $result = $sta . ': ' . $patch;

      $ed = json_encode($res->commits[0]->commit->committer->date);
      $end = str_replace('"', " ",$ed);
      $enddt =  str_replace('T', " ",$end);
      $enddate =  str_replace('Z', " ",$enddt);

      $comid = json_encode($res->commits[0]->sha);
      $commitid = str_replace('"', "",$comid);


     if (!empty($SAL_USER_ID) && !empty($SAP_OBJECT) && !empty($base) && !empty($head) && !empty($reponame) && !empty($userauthor))
     {

          $conn = getConnection();

          $sqlAtt = "INSERT INTO SAP_ACTIVITY_LOG (`SAL_USER_ID`,`SAP_OBJECT`, `SAP_OBJECT_NAME`, `SAP_OBJECT_DESC`, `SAP_STR_DATE`,
                           `SAP_END_DATE`,`COMMIT_ID`)VALUES ('$SAL_USER_ID','$SAP_OBJECT', '$file', '$result', '$strdate', '$enddate', '$commitid')";

          if ($conn->query($sqlAtt) === TRUE)
          {
            $last_id = $conn->insert_id;
            jsonResponce(array('status' => 1, 'msg' => "Record has been saved successfully", 'data' => $last_id,
                               "SAL_USER_ID" => $SAL_USER_ID, "SAP_OBJECT" => $SAP_OBJECT,
                               "SAP_OBJECT_NAME" => $file, "SAP_OBJECT_DESC" =>  $result,
                               "SAP_STR_DATE" => $strdate, "SAP_END_DATE" => $enddate, "COMMIT_ID" => $commitid));
          }else{
           jsonResponce(array('status' => 0, 'msg' => "Record not saved!"));
          }
      }
      else
      {
        jsonResponce(array('status' => 0, 'msg' => "Error in call api or some paramitter missing"));
      }
  }

  function jsonResponce($array = array())
  {
    echo json_encode($array);
    exit;
  }

  function getConnection()
  {

    $servername = "localhost:3306";
    /*$username = "root";
    $password = "root";
    $dbname = "AvraQuality";*/

    $servername = "localhost";
    $username = "dev_avra";
    $password = "green123$";
    $dbname = "AvraQuality";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error)
    {
      jsonResponce(array('status' => 0, 'msg' => "Connection failed: " . $conn->connect_error));
    }
    return $conn;
  }
?>
