<?php

// echo 'Hello ' . htmlspecialchars($_POST["submit"]) . 'ing ';

//start actual code
error_reporting(E_ERROR | E_PARSE);


$serial = 'Fxxxxxxxxxxxxx';
$password = '000000';
$noTimeout="";
$jog="*";
$pull="1"; // pull relay
$release="2"; // release relay
$relay_1="1"; // open gate - relay1
$relay_2="2"; // close gate - relay2

//We store the serial and the password in local cookie, the server does not store the serial nor the password
setcookie('serial', $serial , time() + (86400 * 365), "/"); // 86400 = 1 day
setcookie('password', $password , time() + (86400 * 365), "/"); // 86400 = 1 day

//Command filename is generated from the hashed serial and password
$device = md5($serial . $password);
$file = './devices/' . $device .'_cmd';
$file2 = './devices/' . $device .'_sta';
$current = file_get_contents($file);
$current2 = file_get_contents($file2);

if($current != "\"A\"" && $current!=FALSE)
{
    //We only accept new commands if the previous one was processed by the device
    $result = 'Device ' . $device .  ' is not ready to take action!' . $current;
    echo $result;
}
elseif($_POST['submit']=="Open" and $current2=="00000000")
{
   // echo "gate relay. Pull from " . $current2;
    $action="\"A" . $pull .  $relay_1 . $noTimeout ."\"";  //$_POST['timeout']."\"";
    $current2="10000000";
    //echo " to new state = " . $current2;
    // Write the action to the file
    file_put_contents($file, $action);
    file_put_contents($file2, $current2);
}
elseif($_POST['submit']=="Open" and $current2=="10000000")
{
    //echo " gate relay release from " . $current2;
    $action="\"A" . $release .  $relay_1 . $noTimeout ."\"";  //$_POST['timeout']."\"";
    $current2="00000000";
    //echo " to new state = " . $current2;
    // Write the action to the file
    file_put_contents($file, $action);
    file_put_contents($file2,$current2);
}

elseif($_POST['submit']=="Close" and $current2=="00000000")
{
//    echo " gate relay jogged from " . $current2 . ' to 010000000 to ' . $current2;
    $action="\"A" . $pull .  $relay_2 . $jog ."\"";  //$_POST['timeout']."\"";
    // Write the action to the file
    file_put_contents($file, $action);
}
else
{
    $result = 'Device ' . $device .  ' status query only.';
}

$logfile='./logs/clients.log';
$content=date('Y-m-d H:i:s') . ': ' . $_SERVER['REMOTE_ADDR']. ' ' . $result . "\n";
file_put_contents($logfile, $content, FILE_APPEND | LOCK_EX);

?>


<!DOCTYPE html>
<html lang="en">
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="Description" content="Buttons">
                <meta http-equiv=”Pragma” content=”no-cache”>
                <meta http-equiv=”Expires” content=”-1″>
                <meta http-equiv=”CACHE-CONTROL” content=”NO-CACHE”>
                <title>GATE CONTROL</TITLE>
                <link rel="stylesheet" type="text/css" href="form.css">
                <script>
                function toggleColor(){
                 if (<?php echo $current2; ?>  == "10000000"){
                 document.getElementById("btn_O").classList.add('toggle');
                 }else {
                 document.getElementById("btn_O").classList.remove('toggle');
                 }
                }
                </script>
                <noscript>
                        You don't have javascript enabled.  Good luck with that.
                </noscript>
        </head>
                <h1 class="center">GATE CONTROL</h1>
        <body onload="toggleColor()">
                <div class="form-style-5">
                  <form action="device.php" method="post">
                        <input type="hidden" name="serial" value="<?php echo $serial; ?>">
                        <input type="hidden" name="password" value="<?php echo $password; ?>">

                        <input class="button" type="submit" name="submit" value="Open" id="btn_O">
                        <input class="button" type="submit" name="submit" value="Close" id="btn_C">
                  </form>
                </div>
        </body>
</html>
