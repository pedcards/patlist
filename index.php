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
    <meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" />

  <!--    Block for CDN copies of jquery/mobile. Consider fallback code on fail? -->
        <?php
        $cdnJqm = '1.4.5';
        $cdnJQ = '1.11.1';
        echo '
        <link rel="stylesheet" type="text/css" href="./jqm/jquery.mobile-'.$cdnJqm.'.min.css" >
        <script type="text/javascript" src="./jqm/jquery-'.$cdnJQ.'.min.js"></script>
        <script type="text/javascript" src="./jqm/jquery.mobile-'.$cdnJqm.'.min.js"></script>
    ';  ?>

    <title>CHIPOTLE</title>
</head>
<body>
<?php
$salt = openssl_encrypt("this is my test", "AES256", "bluebird");

$user = $_SERVER['REMOTE_USER'];
$list = \filter_input(\INPUT_GET, 'list');
$xml = simplexml_load_file("currlist.xml");
$listnums = $xml->xpath("//lists/".$list);
$listdate = $listnums[0]['date'];
$retdate = makedate($listdate);

function makedate($a) {
    if ($a) {
        $b = substr($a,4,2).'/'.substr($a,6,2).'@'.substr($a,8,2).':'.substr($a,10,2);
    }
    return $b;
}

?>

<!-- Start of first page -->
<div data-role="page" id="main">

<div data-role="panel" id="lists" data-display="overlay" data-theme="b">
    <ul data-role="listview">
        <li data-icon="delete"><a href="#" data-rel="close">Close menu</a></li>
        <li><a href="?list=EP">EP</a></li>
        <li><a href="?list=Ward">Ward/Consult</a></li>
        <li><a href="?list=CICU">CICU/Consult</a></li>
        <li><a href="?list=CSR">Cardiac Surgery</a></li>
        <li><a href="?list=TXP">Transplant</a></li>
        <li><a href="?list=PHTN">Pulm HTN</a></li>
        <li><a href="?list=Coord">Coordination</a></li>
        <li><a href="index.php">All CORES</a></li>
    </ul>
</div>

<div data-role="header" >
    <h1 style="white-space: normal; text-align: center" ><?php echo $list.$salt; ?></h1>
    <a href="#lists" class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all ui-icon-bars ui-btn-icon-notext ">tab selector</a>
    <a href="#info" data-rel="popup" class="ui-btn ui-shadow ui-btn-icon-right ui-icon-info ui-btn-icon-notext ui-corner-all" data-transition="pop" data-position-to="window">info</a>
</div><!-- /header -->

<div data-role="content">
<?php

if ($list) {
    $lastservice = "";
    $patnums = $listnums[0]->xpath('mrn');
    echo '<ul data-role="listview" data-inset="false" class="ui-mini">';
    foreach($patnums as $mrn) {
        $id = $xml->xpath("id[@mrn='".$mrn."']");
        $demog = $id[0]->xpath('demog');
            $nameL = $demog[0]->name_last;
            $nameF = $demog[0]->name_first;
        $data = $demog[0]->xpath('data');
            $unit = $data[0]->unit;
            $room = $data[0]->room;
            $service = $data[0]->service;
        $status = $id[0]->status;
            //$statusCons = (string)$status->attributes()->cons;
            //$statusTxp = (string)$status->attributes()->txp;
            if ((string)$status->attributes()->cons) {
                $statusString = '&copy; | ';
            }
        if (strcmp($service,$lastservice) !== 0) {
            echo '<li data-role="list-divider" data-theme="b" >'.$service.'</li>';
            }
        echo '<li>';
        echo    '<a href="'.($list=='Coord'?'ptcoord.php':'ptmain.php').'?id='.$mrn.'" data-ajax="false">'
                    .'<i>'.$nameL.'</i>, <small>'.$nameF.'</small>'
                    .'<span class="ui-li-count">'.$statusString.'<small>'.$room.'</small></span>'
                .'</a>';
        echo '</li>';
        $lastservice = $service;
    }
    echo '</ul>';
    } else {
    $pat = $xml->xpath('id');
    echo '<ul data-role="listview" data-filter="true" data-filter-placeholder="Find patient..." data-inset="true">';
    foreach($pat as $id) {
        $mrn = $id['mrn'];
        $demog = $id->xpath('demog');
            $nameL = $demog[0]->name_last;
            $nameF = $demog[0]->name_first;
        echo '<li class="ui-mini">';
        echo    '<a href="ptmain.php?id='.$mrn.'" data-ajax="false"><i>'.$nameL.'</i>, <small>'.$nameF.'</small></a>';
        echo '</li>';
    }
    echo '</ul>';
}
?>
</div>

<div data-role="popup" id="info" data-overlay-theme="b" data-theme="b" >
    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
    <div data-role="header" data-theme="e">
            <h2>CHIPOTLE</h2>
    </div><!-- /header -->

    <div data-role="content" data-theme="d">
        <p style="font-size:small">
The <i>Children's Heart Center InPatient Online Task List Environment</i>
is a software suite intended to improve continuity of patient care
and facilitate the process for inpatient rounding. <br>
<br>
This system obviously uses PHI that has been password protected or encrypted at
each level. Although designed to be viewed on a mobile device, no data is actually
stored on the mobile device. As when using any PHI, be wary of prying eyes.<br>
<br>
Also, this is a work in progress. Use at your own risk! I take no responsibility
for anything you do with this.<br>
<br>
- <em>TUC</em><br/>
    </p>
    </div><!-- /content -->
</div><!-- /info popup -->

<div data-role="popup" id="weekly" data-theme="a" class="ui-corner-all">

<?php
    $weekstr = "";
    $patnums = $listnums[0]->xpath('mrn');
    foreach($patnums as $mrn) {
        $id = $xml->xpath("id[@mrn='".$mrn."']");
        $demog = $id[0]->xpath('demog');
            $nameL = $demog[0]->name_last;
            $nameF = $demog[0]->name_first;
        $data = $demog[0]->xpath('data');
            $unit = $data[0]->unit;
            $room = $data[0]->room;
            $service = $data[0]->service;
            $dob = $data[0]->dob;
            $age = $data[0]->age;
            $sex = $data[0]->sex;
            $admit = $data[0]->admit;
        $weekstr .= $nameL.', '.$nameF.'\t'.$mrn.'\t'.$unit.' '.$room.'\t'.$service.'\t'.$dob.'\t'.$age.' '.$sex.'\t'.$admit.'\n<br>';
        $tmpNote = ""; $tmpStr = "";
        $notes = $id[0]->xpath('notes/weekly/summary');
        foreach($notes as $tmpNote) {
            $tmpDate = $tmpNote->attributes()->date;
            $tmpStr .= '['.substr($tmpDate,4,2).'/'.substr($tmpDate,6,2).'] '.$tmpNote[0].' ';
        }
        $tmpStr .= '\n<br>';
        $weekstr .= $tmpStr;
    }
    echo $weekstr;
?>
</div><!-- /popup -->

<div data-role="footer" class="ui-bar ui-grid-b">
    <div class="ui-block-a" ></div>
    <div class="ui-block-b" style="text-align: center;">&COPY;2016 TC<br></div>
    <div class="ui-block-c" style="text-align: right"><a href="#weekly" data-rel="popup" class="ui-btn ui-shadow ui-btn-icon-right ui-icon-mail ui-btn-icon-notext ui-corner-all" data-position-to="window" data-transition="pop"></a></div>
</div><!-- /footer -->
</div><!-- /page -->

</body>
</html>
