<?php

//Value to be obtained from Session variable
$UserID     = 1234;

if(isset($_POST))
{
    if($_POST['param'] == 'Load')
    {
        //Some common parameters
        $ContestID      = 4567;             //Contest ID for which gallery is to be built
        $ContestFolder  ='contestentry';    //Folder for all Images
        $ThumbName      ='thumb_';          //prefix of thumbnails
        //Check for webkit support
        if($_POST['browser'] == 'webkit')
        {
            $ImageArray = RetrieveFromDB($ContestID, $ContestFolder, $ThumbName);
            echo $ImageArray;
        }
        //Fall Back gallery
        else if($_POST['browser'] == 'others')
        {
            $count = filter($_POST["count"]);
            $ImageArray = RetrieveFromDBWithCount($ContestID, $ContestFolder, $ThumbName, $count);
            echo $ImageArray; 
        }
    }
    else if($_POST['param'] == 'Vote')
    {

        $ImageOwnerID  = $_POST['user'];

        $ImageID = $_POST['image'];

        //Check if user has already voted for the particular image
        if(CheckVote($ImageID,$UserID))
        {
            if(UpdateVoteDB($ImageID, $ImageOwnerID, $UserID))
                echo ("Voted");
            else
                echo("Error registering Vote, Try after some time");
        }
        else
            echo("You have already voted for this Image");
    }
}

//Input argument validation - only numbers permitted
function filter($data) 
{
    if(is_numeric($data)) 
    {
        return $data;
    }
    else 
    { 
        header("Location: ../../index.html"); 
    }
}

//Retrieven data and convert to json array
function RetrieveFromDB($ContestID, $ContestFolder, $ThumbName)
{
    require("config.php");
    $con = mysql_connect($mySqlServer, $mySqlUserName, $mySqlPass);
    if (!$con) die('Could not connect: ' . mysql_error());
   
    mysql_select_db($mySqlTable, $con);

    $sql = "SELECT  `IMGE_ID` ,  `USR_ID` ,  `IMGE_CPTION` FROM  `IMAGES` WHERE  `CNTST_ID` = $ContestID";
    $result = mysql_query($sql);

    $JsonArray = array();
    while($row = mysql_fetch_array($result))
    {
        $ImageID        = $row['IMGE_ID'];
        $UserID         = $row['USR_ID'];
        $ImageCaption   = $row['IMGE_CPTION'];

        $FullImagePath  = $ContestFolder.'/'.$ContestID.'/'.$UserID.'/'.$ImageID.'.jpg';
        $ThumbImagePath = $ContestFolder.'/'.$ContestID.'/'.$UserID.'/'.$ThumbName.$ImageID.'.jpg';

        $JsonFile       = array(
                                "title" => $ImageCaption,
                                "thumb" => $ThumbImagePath,
                                "link"  => $FullImagePath,
                                "zoom"  => $FullImagePath,
                                "user"  => $UserID,
                                "image" => $ImageID
                            );
        array_push($JsonArray,$JsonFile);
    }
    mysql_close($con);
    return(json_encode($JsonArray));
}
//Retrieven data and convert to json array
function RetrieveFromDBWithCount($ContestID, $ContestFolder, $ThumbName, $count)
{
    require("config.php");
    $con = mysql_connect($mySqlServer, $mySqlUserName, $mySqlPass);
    if (!$con) die('Could not connect: ' . mysql_error());
   
    mysql_select_db($mySqlTable, $con);

    $sql = "SELECT COUNT( * ) FROM `IMAGES`";
    $result = mysql_query($sql);
    while($totalRecord = mysql_fetch_array($result))
    {
        $TotalRecordCount = $totalRecord['COUNT( * )'];
    }
    $final = $count+12;
    $EOF = ($TotalRecordCount < $final ? true : false);

    $sql = "SELECT  `IMGE_ID` ,  `USR_ID` ,  `IMGE_CPTION` FROM  `IMAGES` WHERE  `CNTST_ID` = $ContestID LIMIT $count, $final";
    $result = mysql_query($sql);

    $JsonArray = array();
    while($row = mysql_fetch_array($result))
    {
        $ImageID        = $row['IMGE_ID'];
        $UserID         = $row['USR_ID'];
        $ImageCaption   = $row['IMGE_CPTION'];

        $FullImagePath  = $ContestFolder.'/'.$ContestID.'/'.$UserID.'/'.$ImageID.'.jpg';
        $ThumbImagePath = $ContestFolder.'/'.$ContestID.'/'.$UserID.'/'.$ThumbName.$ImageID.'.jpg';

        $JsonFile       = array(
                                "title" => $ImageCaption,
                                "thumb" => $ThumbImagePath,
                                "link"  => $FullImagePath,
                                "zoom"  => $FullImagePath,
                                "user"  => $UserID,
                                "image" => $ImageID,
                                "EOF"   => $EOF
                            );
        array_push($JsonArray,$JsonFile);
    }
    mysql_close($con);
    return(json_encode($JsonArray));
}
//Check if the vote has already been registered
function CheckVote($ImageID,$UserID)
{
    require("config.php");
     $con = mysql_connect($mySqlServer, $mySqlUserName, $mySqlPass);
    if (!$con) die('Could not connect: ' . mysql_error());
   
    mysql_select_db($mySqlTable, $con);

    $sql = "SELECT COUNT( * ) FROM  `VOTES` WHERE  `IMAGE_ID` = $ImageID AND  `USR_NAME` = $UserID";
    $result = mysql_query($sql);

    while($row =mysql_fetch_array($result))
    {
        $Count = $row['COUNT( * )'];
    }
    mysql_close($con);
    return ($Count == 0? true:false);
}

//Update Vote details to database
function UpdateVoteDB($ImageID, $ImageOwnerID, $UserID) 
{
    require("config.php");
    $con = mysql_connect($mySqlServer, $mySqlUserName, $mySqlPass);
    if (!$con) die('Could not connect: ' . mysql_error());
   
    mysql_select_db($mySqlTable, $con);

    //$date = date('Y-m-d');
    //$voteDate = date('Y-m-d', strtotime($date));

    $sql = "INSERT INTO  `VOTES` (`IMAGE_ID` ,`USR_NAME`) VALUES ('$ImageID',  '$UserID')";
    if(!mysql_query($sql, $con))
    {
        mysql_close($con);
        return false;
    }
    mysql_close($con);
    return true;

}
?>