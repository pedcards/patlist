<?php session_start();?>
<!DOCTYPE html>
<html>
<head>
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="apple-touch-icon" href="favicon.png" />
    <link href="" rel="apple-touch-startup-image" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <!--<meta name="apple-touch-fullscreen" content="YES" />-->
    <meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" />
    <link rel="stylesheet" href="./jqm/jquery.mobile-1.4.3.min.css" />
    <script src="./jqm/jquery-1.11.1.min.js"></script>
    <script src="./jqm/jquery.mobile-1.4.3.min.js"></script>
    
    <title>Patient Edit</title>
</head>
<body>
<?php
var_dump($_SERVER);
foreach ( array_keys($_SERVER) as $b ) {
    var_dump($b, filter_input(INPUT_SERVER, $b))."<br>";
}
echo '<hr>';
var_dump($_ENV);
foreach ( array_keys($_ENV) as $b ) {
    var_dump($b, filter_input(INPUT_ENV, $b))."<br>";
}

$user = $_SERVER['REMOTE_USER'];
$user1 = \filter_var(INPUT_SERVER,'REMOTE_USER');
$refer = $_SERVER['HTTP_REFERER'];
$refer1 = \filter_input(INPUT_SERVER,'HTTP_REFERER');
$mrn = \filter_input(\INPUT_GET, 'id');
$index = \filter_input(\INPUT_GET, 'date');
$idx = $_SESSION['idx'];

function medlist($a,$b,$c) {
    $medlist = $b[0]->$a;
    foreach($medlist as $med) {
        if ($c) {
            $medatt = $med->attributes();
            if (strcmp($c,$medatt) == 0) {
                echo "&#8226;&nbsp;<small>".$med."</small><br/>";
            }
        } else {
        echo "&#8226;&nbsp;<small>".$med."</small><br/>";
        }
    }
}

echo 'user: '.$user1.'<br>';
echo 'refer: '.$refer1.'<br>';
?>



</body>
