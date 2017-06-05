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
    <link rel="stylesheet" href="./jqm/jquery.mobile-1.4.5.min.css" />
    <script src="./jqm/jquery-1.11.1.min.js"></script>
    <script src="./jqm/jquery.mobile-1.4.5.min.js"></script>

    <link rel="stylesheet" type="text/css" href="./jqm/jqm-datebox-1.4.5.min.css">
    <script type="text/javascript" src="./jqm/jqm-datebox-1.4.5.core.min.js"></script>
    <script type="text/javascript" src="./jqm/jqm-datebox-1.4.5.mode.calbox.min.js"></script>
    <script type="text/javascript" src="./jqm/jquery.mobile.datebox.i18n.en_US.utf8.js"></script>

<title>Patient Coordination</title>
</head>

<body>
<?php
error_reporting(-1);
$user = (htmlentities($_SERVER['REMOTE_USER'])) ?: 'TEST';
$refer = htmlentities($_SERVER['HTTP_REFERER']);
    if (strpos($refer, 'ptcoord.php') == FALSE) {
        $_SESSION['ref'] = $refer;
    }

$mrn = \filter_input(\INPUT_GET, 'id');
$index = \filter_input(\INPUT_GET, 'idx');
$edtype = \filter_input(\INPUT_GET, 'ed');
$timenow = date("YmdHis");
$test = \filter_input(\INPUT_POST, 'taskval');

$xml = simplexml_load_file("currlist.xml");
$chg = (simplexml_load_file("change.xml")) ?: new SimpleXMLElement('<root />');     // load change.xml if exists or start <root> in local memory

$id = $xml->xpath("id[@mrn='".$mrn."']");
    //  This section for reading values for this ID from existing currlist
    $demog = $id[0]->xpath('demog');
        $nameL = $demog[0]->name_last;
        $nameF = $demog[0]->name_first;
    $data = $id[0]->xpath('demog/data');
        $sex = $data[0]->sex;
        $dob = $data[0]->dob;
        $age = $data[0]->age;
        $service = $data[0]->service;
        $admit = $data[0]->admit;
        $unit = $data[0]->unit;
        $room = $data[0]->room;
    $DX = $id[0]->diagnoses;
    $DXcoord = $DX[0]->coord;
    $status = $DXcoord[0]->status;
        $statusBag = (string)$status['bag'];                      // (string)$status->attributes()->cons;
        $statusPillow = (string)$status['pillow'];
        $statusTour = (string)$status['tour'];
        $statusMFM = (string)$status['mfm'];
    $dxNote = $DXcoord[0]->note;
    $info = $id[0]->xpath('info');
        $dcw = $info[0]->dcw;
        $allergies = $info[0]->allergies;
        $code = $info[0]->code;
        $hx = $info[0]->hx;
    $prov = $id[0]->prov;
        $provCard = (string)$prov['provCard'];                                  // synonym for  (string)$prov->attributes()->provCard; 
        $provCSR = (string)$prov['CSR'];
        $provEP = (string)$prov['provEP'];
        $provPCP = (string)$prov['provPCP'];
        $statusTxp = (string)$prov['txp'];
        $statusMil = (string)$prov['mil'];
        $statusPM = (string)$prov['pm'];

$edit = \filter_input(\INPUT_POST, 'edit');
    if ($edit == "dx") {
        $dxMisc = \filter_input(\INPUT_POST, 'dxMisc00', FILTER_SANITIZE_SPECIAL_CHARS);
        $DX[0]->misc = $dxMisc;
        $DX['ed'] = $timenow;                       //$DX->addAttribute("date","now");
        $DX['au'] = $user;
        $xml->asXML("currlist.xml");
        cloneBlob($DX, 'dx', 'change');
        //$openme = 'DX';
    }
    if ($edit == "note") {
        $dxNote = \filter_input(\INPUT_POST, 'dxNote00', FILTER_SANITIZE_SPECIAL_CHARS);
        $DXcoord[0]->note = $dxNote;
        $DXcoord['ed'] = $timenow;                       //$DX->addAttribute("date","now");
        $DXcoord['au'] = $user;
        $xml->asXML("currlist.xml");
        cloneBlob($DXcoord, 'note', 'change');
    }
    if ($edit == "status") {
        $statusBag = \filter_input(\INPUT_POST, 'statusBag');
        $statusPillow = \filter_input(\INPUT_POST, 'statusPillow');
        $statusTour = \filter_input(\INPUT_POST, 'statusTour');
        $statusMFM = \filter_input(\INPUT_POST, 'statusMFM');
        $status['bag']=$statusBag;
        $status['pillow']=$statusPillow;
        $status['tour']=$statusTour;
        $status['mfm']=$statusMFM;
//        Synonyms
//        $status->attributes()->cons = $statusCons;
//        $status->addAttribute("cons",$statusCons);
        
        $status['ed']=$timenow;
        $status['au']=$user;
        $xml->asXML("currlist.xml");
        cloneBlob($status,'statCo', 'change');
    }
    if ($edit == "provider") {
        $provCard = \filter_input(\INPUT_POST, 'provCard',FILTER_SANITIZE_SPECIAL_CHARS);
        $provEP = \filter_input(\INPUT_POST, 'provEP',FILTER_SANITIZE_SPECIAL_CHARS);
        $provPCP = \filter_input(\INPUT_POST, 'provPCP',FILTER_SANITIZE_SPECIAL_CHARS);
        $provCSR = \filter_input(\INPUT_POST, 'provCSR',FILTER_SANITIZE_SPECIAL_CHARS);
        $prov['provCard'] = $provCard;
        $prov['provEP'] = $provEP;
        $prov['provPCP'] = $provPCP;
        $prov['CSR'] = $provCSR;
        if (empty($prov)) {
            $prov = $id[0]->addChild('prov');
            $prov->addAttribute("provCard",$provCard);
            $prov->addAttribute("provEP",$provEP);
            $prov->addAttribute("provPCP",$provPCP);
            $prov->addAttribute("CSR",$provCSR);
        }
        $prov['ed'] = $timenow;
        $prov['au'] = $user;
        $xml->asXML("currlist.xml");
        cloneBlob($prov,'prov');
    }

function cloneBlob($blob,$type,$change='') {
    global $mrn, $chg;
    $node = $chg[0]->addChild('node');
    $node['MRN'] = $mrn;
    $node['type'] = $type;
    $node['change'] = $change;
    $dom_blob = dom_import_simplexml($blob[0]);
    $dom_node = dom_import_simplexml($node[0]);
    $dom_new = $dom_node->appendChild($dom_node->ownerDocument->importNode($dom_blob,true));
    simplexml_import_dom($dom_new);
    $chg->asXML("change.xml");
}

function makedate($a) {
    if ($a) {
        $b = substr($a,4,2).'/'.substr($a,6,2).'@'.substr($a,8,2).':'.substr($a,10,2);
    }
    return $b;
}

function dialogConfirm() {
    ?>
    <div data-role="dialog" id="confirmDialog" data-overlay-theme="b">
        <div data-role="header" data-theme="a" >
            <h1>Delete note?</h1>
        </div>
        <DIV data-role="content" >
            <h3>Are you sure?</h3>
            <p>This cannot be undone.</p>
            <a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Cancel</a>
            <a href="#confirmYes" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Delete</a>
        </DIV>
    </div>
    <div id="confirmYes">
        <?php $confirm='Y';
        return $confirm;?>
    </div>
    <?php
}

?>

<!-- Start of first page -->
<div data-role="page" id="main" data-dom-cache="false">

<div data-role="popup" id="notdone" data-overlay-theme="b" data-theme="a" class="ui-content">
    <p>Under<br>construction!</p>
</div>

<div data-role="panel" id="ptinfo" data-display="overlay" data-position="right" data-theme="a" data-mini="true">
    <p><?php echo $mrn; ?></p>
    <p>Sex: <?php echo $sex; ?><br/>
       DOB: <?php echo $dob; ?><br/>
       Age: <?php echo $age; ?></p>
    <p>Edit: <?php echo $edit; ?></p>
    <?php
        if (!empty($dcw)) { echo '<p>DCW: '.$dcw.'<br/>'; } 
        if (!empty($allergies)) { echo 'Allergies: '.$allergies.'<br/>'; }
        if (!empty($code)) { echo 'Code status: '.$code.'</p>'; } 
    ?>
    <p>Service: <?php echo $service; ?><br/>
       Admit: <?php echo $admit; ?><br/>
       Room: <?php echo $unit.' '.$room; ?></p>
    <p>Cardiologist: <?php echo $provCard.'  '; ?><a href="#popupEditCard" data-rel="popup" data-position-to="window" class="ui-btn ui-btn-corner-all ui-btn-inline ui-icon-edit ui-btn-icon-notext" data-mini="true" data-transition="pop"></a></p>
    <div data-role="popup" id="popupEditCard">
        <form method="post" <?php echo 'action="ptcoord.php?id='.$mrn.'"'; ?> data-ajax="false">
            <div style="padding:10px 20px;">
                <input name="provCard" id="editCard" value="<?php if (!empty($provCard)) { echo $provCard; } ?>" placeholder="Cardiologist" data-theme="a" type="text">
                <input name="provCSR" id="editCSR" value="<?php if (!empty($provCSR)) { echo $provCSR; } ?>" placeholder="Surgeon" data-theme="a" type="text">
                <input name="provEP" id="editEP" value="<?php if (!empty($provEP)) { echo $provEP; } ?>" placeholder="Electrophysiologist" data-theme="a" type="text">
                <input name="provPCP" id="editPCP" value="<?php if (!empty($provPCP)) { echo $provPCP; } ?>" placeholder="PCP" data-theme="a" type="text">
                <input type="hidden" name="edit" value="provider">
                <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-b ui-btn-icon-left ui-icon-check" >Save</button>
            </div>
        </form>
    </div>
</div>

    
<div data-role="header" data-position="fixed">
    <h4 style="text-align: center" ><?php echo $nameL; ?></h4>
    <a <?php echo 'href="'.$_SESSION['ref'].'"';?> class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all ui-icon-carat-l ui-btn-icon-notext">back</a>
    <a href="#ptinfo" class="ui-btn ui-shadow ui-btn-icon-right ui-corner-all ui-icon-user ui-btn-icon-notext " >Patient info</a>
</div><!-- /header -->

<div data-role="content">

<form method="post" <?php echo 'action="ptcoord.php?id='.$mrn.'"'; ?>>
    <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true" class="ui-field-contain">
        <input name="statusBag" id="cbox-1a" type="checkbox" <?php if ($statusBag) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-1a">Bag given</label>
        <input name="statusPillow" id="cbox-1b" type="checkbox" <?php if ($statusPillow) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-1b">Pillow given</label>
        <input name="statusTour" id="cbox-1c" type="checkbox" <?php if ($statusTour) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-1c">Tour given</label>
        <!--<input data-icon="camera" data-iconpos="notext" data-corners="false" value="Icon only" type="submit" >-->
    </fieldset>
    <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true" class="ui-field-contain">
        <input name="statusMFM" id="cbox-2a" type="checkbox" <?php if ($statusMFM) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-2a">MFM notified</label>
        <!--<input data-icon="camera" data-iconpos="notext" data-corners="false" value="Icon only" type="submit" >-->
    </fieldset>
    <input type="hidden" name="edit" value="status" />
</form>

<div data-role="collapsibleset" data-theme="a" data-content-theme="a" data-mini="true" data-collapsed-icon="carat-r" data-expanded-icon="carat-d">
    <div data-role="collapsible" data-content-theme="a" <?php if (empty($openme)) {echo 'data-collapsed="false"';}?>>
        <h3>Notes</h3>
        <ul data-role="listview" data-inset="true" data-icon="false" class="ui-alt-icon">
            <li data-role="list-divider">Misc Notes (seen by all)</li>
            <li><a href="#editDx"><p style="white-space: pre-wrap"><?php echo $dxMisc; ?></p></a></li>
            <li data-role="list-divider">Notes (only seen by Coordinator)</li>
            <li><a href="#editDx"><p style="white-space: pre-wrap"><?php echo $dxNote; ?></p></a></li>
        </ul>
    </div>
</div><!-- /collapsible set -->
</div><!-- /content -->

</div><!-- /main page -->

<!-- ======================================================================= -->
<div data-role="page" id="editDx" data-dom-cache="false">
<div data-role="header" data-position="fixed">
    <h4 style="white-space: normal; text-align: center" ><?php echo $nameL.', '.$nameF; ?></h4>
    <a href="#" data-ajax="false" data-rel="back" class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all ui-icon-delete ui-btn-icon-notext" >Cancel</a>
</div><!-- /header -->

<div data-role="content">
<form method="post" action="#">
    <label for="textarea-dxMisc">Misc Notes (seen by all):</label>
    <textarea cols="40" rows="8" name="dxMisc00" id="textarea-dxMisc"><?php echo $dxMisc; ?></textarea>
    <label for="textarea-dxNote">Special Notes (only seen by Coordinator):</label>
    <textarea cols="40" rows="8" name="dxNote00" id="textarea-dxNote"><?php echo $dxNote; ?></textarea>
    <input type="submit" class="ui-btn ui-shadow ui-btn-icon-right ui-corner-all ui-icon-edit" value="SAVE" data-theme="b">
    <input type="hidden" name="edit" value="dx" />
</form>
</div><!-- /content -->

</div><!-- /edit page -->
<!-- ======================================================================= -->

</body>