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

<title>Patient Main</title>
</head>

<body>
<?php
error_reporting(-1);
$user = (htmlentities($_SERVER['REMOTE_USER'])) ?: 'TEST';
$refer = htmlentities($_SERVER['HTTP_REFERER']);
    if (strpos($refer, 'ptmain.php') == FALSE) {
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
    $status = $id[0]->status;
        $statusCons = (string)$status['cons'];                      // (string)$status->attributes()->cons;
        $statusRes = (string)$status['res'];
        $statusScamp = (string)$status['scamp'];
    $info = $id[0]->xpath('info');
        $dcw = $info[0]->dcw;
        $allergies = $info[0]->allergies;
        $code = $info[0]->code;
        $hx = $info[0]->hx;
    $MAR = $id[0]->xpath('MAR');
    $DX = $id[0]->xpath('diagnoses');
        $dxNotes = $DX[0]->notes;
        $dxCrd = $DX[0]->card;
        $dxEP = $DX[0]->ep;
        $dxSurg = $DX[0]->surg;
        $dxProb = $DX[0]->prob;
    $prov = $id[0]->prov;
        $provCard = (string)$prov['provCard'];                                  // synonym for  (string)$prov->attributes()->provCard; 
        $provCSR = (string)$prov['CSR'];
        $provEP = (string)$prov['provEP'];
        $provPCP = (string)$prov['provPCP'];
        $statusTxp = (string)$prov['txp'];
        $statusMil = (string)$prov['mil'];
        $statusPM = (string)$prov['pm'];

    if (!($notes = $id[0]->notes)) {                                            // create <notes> node if missing
        $notes = $id[0]->addChild('notes');
        $xml->asXML("currlist.xml");
    }
    $notesWk = $notes[0]->weekly;
    $notesPn = $notes[0]->progress;
    
    if (!($plan = $id[0]->plan)) {                                              // create <plan> node if missing
        $plan = $id[0]->addChild('plan');
        $xml->asXML("currlist.xml");
    }
    $planTasks = $plan[0]->tasks;
    $planDone = $plan[0]->done;
    
    //
    $todoCk = \filter_input(\INPUT_GET, 'td');                                  // todo has been checked or unchecked
    if (!empty($todoCk)) {
        if ($todoCk=="cl") {                                                    // CHECKED
            if (empty($planDone)) {
                $plan[0]->addChild('done');
            }
            $todoTmp = $planTasks->xpath("todo[@created='".$index."']");
            $planDone = $plan[0]->done;
            $todoTmp[0]['done'] = $timenow;
            cloneBlob($todoTmp[0], 'todo','done');
            
            $dom_tasks = dom_import_simplexml($planTasks[0]);
            $dom_todo = dom_import_simplexml($todoTmp[0]);
            $dom_done= dom_import_simplexml($planDone[0]);
            $dom_new = $dom_done->appendChild($dom_todo->cloneNode(true));
            $new_node = simplexml_import_dom($dom_new);
            unset($todoTmp[0][0]);
        }
        if ($todoCk=="uc") {                                                    // UNCHECK from TRASH
            $todoTmp = $planDone->xpath("todo[@created='".$index."']");
            $planTasks = $plan[0]->tasks;
            $todoTmp[0]['done'] = "";
            cloneBlob($todoTmp[0],'todo','undo');
            
            $dom_done = dom_import_simplexml($planDone[0]);
            $dom_todo = dom_import_simplexml($todoTmp[0]);
            $dom_tasks = dom_import_simplexml($planTasks[0]);
            $dom_new = $dom_tasks->appendChild($dom_todo->cloneNode(true));
            $new_node = simplexml_import_dom($dom_new);
            unset($todoTmp[0][0]);
        }
        $xml->asXML("currlist.xml");
        $openme = "TD";
    }
    $trash = $id[0]->trash;
    
$edit = \filter_input(\INPUT_POST, 'edit');
    if ($edit == "dx") {
        $dxNotes =  \filter_input(\INPUT_POST, 'dxNotes00', FILTER_SANITIZE_SPECIAL_CHARS);
        $dxCrd =  \filter_input(\INPUT_POST, 'dxCrd00', FILTER_SANITIZE_SPECIAL_CHARS);
        $dxEP =   \filter_input(\INPUT_POST, 'dxEP00', FILTER_SANITIZE_SPECIAL_CHARS);
        $dxSurg = \filter_input(\INPUT_POST, 'dxSurg00', FILTER_SANITIZE_SPECIAL_CHARS);
        $dxProb = \filter_input(\INPUT_POST, 'dxProb00', FILTER_SANITIZE_SPECIAL_CHARS);
        foreach ($DX as $tmp)
        {
            unset($tmp[0]); // removes all children, and 'diagnosis' as well.
        }
        $DX = $id[0]->addChild('diagnoses'); 
            $DX[0]->addChild("notes", $dxNotes);
            $DX[0]->addChild("card", $dxCrd);
            $DX[0]->addChild("ep", $dxEP);
            $DX[0]->addChild("surg", $dxSurg);
            $DX[0]->addChild("prob", $dxProb);
        $DX['ed'] = $timenow;                       //$DX->addAttribute("date","now");
        $DX['au'] = $user;
        $xml->asXML("currlist.xml");
        cloneBlob($DX, 'dx', 'change');
        //$openme = 'DX';
    }
    if ($edit == "wksumm") {
        $editdate = \filter_input(\INPUT_POST, 'editdate');
        $editval = \filter_input(\INPUT_POST, 'wkSumm',FILTER_SANITIZE_SPECIAL_CHARS);
        $editmod = \filter_input(\INPUT_POST, 'mod');
        $editact = \filter_input(\INPUT_POST, 'action');
        $editidx = \filter_input(\INPUT_POST, 'idxdate');
        if (empty($notesWk)) {
            $notesWk = $notes->addChild('weekly');
        }
        if ($editmod) {
            //change the value
            $notesTmp = $notesWk->xpath("summary[@created='".$editidx."']");
            if ($editact=='DELETE') {
                $confirm = dialogConfirm();
                if ($confirm=='Y') {
                    if (empty($trash)) {
                        $id[0]->addChild('trash');
                    }
                    $trash = $id[0]->trash;
                    $notesTmp[0][0]['del'] = $timenow;
                    cloneBlob($notesTmp[0],'summary','del');
                    
                    $dom_wk = dom_import_simplexml($notesWk[0]);
                    $dom_summ = dom_import_simplexml($notesTmp[0]);
                    $dom_trash = dom_import_simplexml($trash[0]);
                    $dom_new = $dom_trash->appendChild($dom_summ->cloneNode(true));
                    $new_node = simplexml_import_dom($dom_new);
                    unset($notesTmp[0][0]);
                }
            } else {
                $notesTmp[0][0] = $editval;
                $notesTmp[0][0]['ed'] = $timenow;
                $notesTmp[0][0]['au'] = $user;
                cloneBlob($notesTmp[0],'summary','edit');
            }
        } else {
            //add a note
            $summ = $notesWk->addChild('summary', $editval);
            $summ->addAttribute('date', $editdate);
            $summ->addAttribute('created', $editdate);
            $summ->addAttribute('ed',$timenow);
            $summ->addAttribute('au', $user);
            cloneBlob($summ,'summary','add');
        }
        $xml->asXML("currlist.xml");
        $openme = 'WK';
    }
    if ($edit == "todo") {
        $editdate = \filter_input(\INPUT_POST, 'duedate');
        $editval = \filter_input(\INPUT_POST, 'taskTodo',FILTER_SANITIZE_SPECIAL_CHARS);
        $editmod = \filter_input(\INPUT_POST, 'mod');
        $editact = \filter_input(\INPUT_POST, 'action');
        $editidx = \filter_input(\INPUT_POST, 'idxdate');
        if (substr($editdate,2,1)=="/") {
            $editdate = substr($editdate,6,4).substr($editdate,0,2).substr($editdate,3,2);
        }
        if (empty($planTasks)) {
            $planTasks = $plan->addChild('tasks');
        }
        if ($editmod) {
            //change the value
            $todoTmp = $planTasks->xpath("todo[@created='".$editidx."']");
            if ($editact=='DELETE') {
                $confirm = dialogConfirm();
                if ($confirm=='Y') {
                    if (empty($trash)) {
                        $id[0]->addChild('trash');
                    }
                    $trash = $id[0]->trash;
                    $todoTmp[0][0]['del'] = $timenow;
                    cloneBlob($todoTmp[0],'todo','del');
                    
                    $dom_task = dom_import_simplexml($planTasks[0]);
                    $dom_todo = dom_import_simplexml($todoTmp[0]);
                    $dom_trash = dom_import_simplexml($trash[0]);
                    $dom_new = $dom_trash->appendChild($dom_todo->cloneNode(true));
                    $new_node = simplexml_import_dom($dom_new);
                    unset($todoTmp[0][0]);
                }
            } else {
                $todoTmp[0][0] = $editval;
                $todoTmp[0][0]['due'] = $editdate;
                $todoTmp[0][0]['ed'] = $timenow;
                $todoTmp[0][0]['au'] = $user;
                cloneBlob($todoTmp[0],'todo','edit');
            }
        } else {
            //add a note
            $todo = $planTasks->addChild('todo', $editval);
            $todo->addAttribute('due', $editdate);
            $todo->addAttribute('created', $timenow);
            $todo->addAttribute('ed',$timenow);
            $todo->addAttribute('au', $user);
            cloneBlob($todo,'todo','add');
        }
        $xml->asXML("currlist.xml");
        $openme = 'TD';
    }
    if ($edit == "status") {
        if (empty($status)) {
            $status = $id[0]->addChild('status');
        }
        $statusCons = \filter_input(\INPUT_POST, 'statusCons');
        $statusRes = \filter_input(\INPUT_POST, 'statusRes');
        $statusScamp = \filter_input(\INPUT_POST, 'statusScamp');
        $status['cons']=$statusCons;
        $status['res']=$statusRes;
        $status['scamp']=$statusScamp;
//        Synonyms
//        $status->attributes()->cons = $statusCons;
//        $status->addAttribute("cons",$statusCons);
        
        $status['ed']=$timenow;
        $status['au']=$user;
        $xml->asXML("currlist.xml");
        cloneBlob($status,'stat');
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
        $statusTxp = \filter_input(\INPUT_POST, 'statusTxp');
        $statusMil = \filter_input(\INPUT_POST, 'statusMil');
        $statusPM = \filter_input(\INPUT_POST, 'statusPM');
        $prov['txp']=$statusTxp;
        $prov['mil']=$statusMil;
        $prov['pm']=$statusPM;
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
        <form method="post" <?php echo 'action="ptmain.php?id='.$mrn.'"'; ?> data-ajax="false">
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
<!--    <div data-role="collapsible" data-mini="true"><h3>Error checks</h3>
    <?php
        echo '<pre><small>';
        var_dump($editdate);
        echo '</small></pre>';
    ?>
    </div>-->

<form method="post" <?php echo 'action="ptmain.php?id='.$mrn.'"'; ?>>
    <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true" class="ui-field-contain">
        <input name="statusCons" id="cbox-1a" type="checkbox" <?php if ($statusCons) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-1a">Cons</label>
        <input name="statusRes" id="cbox-1b" type="checkbox" <?php if ($statusRes) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-1b">Res</label>
        <input name="statusScamp" id="cbox-1c" type="checkbox" <?php if ($statusScamp) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-1c">SCAMP</label>
        <!--<input data-icon="camera" data-iconpos="notext" data-corners="false" value="Icon only" type="submit" >-->
    </fieldset>
    <input type="hidden" name="edit" value="status" />
</form>
<form method="post" <?php echo 'action="ptmain.php?id='.$mrn.'"'; ?>>
    <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true" class="ui-field-contain">
        <input name="statusTxp" id="cbox-2a" type="checkbox" <?php if ($statusTxp) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-2a">Txp</label>
        <input name="statusMil" id="cbox-2b" type="checkbox" <?php if ($statusMil) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-2b">Mil</label>
        <input name="statusPM" id="cbox-2c" type="checkbox" <?php if ($statusPM) { echo 'checked="checked"'; } ?> onChange="submit();">
        <label for="cbox-2c">PM</label>
        <!--<input data-icon="camera" data-iconpos="notext" data-corners="false" value="Icon only" type="submit" >-->
    </fieldset>
    <input type="hidden" name="edit" value="provider" />
</form>
<?php
    if ($statusPM) { ?>
<div data-role="collapsibleset" data-theme="a" data-content-theme="a" data-mini="true" data-collapsed-icon="carat-r" data-expanded-icon="carat-d">
    <div data-role="collapsible" data-content-theme="a" data-collapsed="true">
        <h3>Temporary pacing</h3>
        <form>
            <div class="ui-field-contain">
                <label for="editMode" class="select">Mode:</label>
                <select name="mode" id="editMode" data-native-menu="false" data-mini="true">
                    <option>Select</option>
                    <option value="DDD">DDD</option>
                    <option value="VVI">VVI</option>
                    <option value="VOO">VOO</option>
                    <option value="AAI">AAI</option>
                    <option value="AOO">AOO</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </form>
    </div>
    <div data-role="collapsible" data-content-theme="a" data-collapsed="true">
        <h3>Permanent pacing</h3>
    </div>
</div>
    <?php } ?>

<div data-role="collapsibleset" data-theme="a" data-content-theme="a" data-mini="true" data-collapsed-icon="carat-r" data-expanded-icon="carat-d">
    <div data-role="collapsible" data-content-theme="a" <?php if (empty($openme)) {echo 'data-collapsed="false"';}?>>
        <h3>Diagnoses</h3>
        <ul data-role="listview" data-inset="true" data-icon="false" class="ui-alt-icon">
            <li data-role="list-divider">Quick Notes</li>
            <li><a href="#editDx"><p style="white-space: pre-wrap"><?php echo $dxNotes; ?></p></a></li>
            <li data-role="list-divider">Diagnoses & Problems</li>
            <li><a href="#editDx"><p style="white-space: pre-wrap"><?php echo $dxCrd; ?></p></a></li>
            <li data-role="list-divider">EP diagnoses/problems</li>
            <li><a href="#editDx"><p style="white-space: pre-wrap"><?php echo $dxEP; ?></p></a></li>
            <li data-role="list-divider">Surgeries/Caths/Interventions</li>
            <li><a href="#editDx"><p style="white-space: pre-wrap"><?php echo $dxSurg; ?></p></a></li>
            <li data-role="list-divider">Problem list:</li>
            <li><a href="#editDx"><p style="white-space: pre-wrap"><?php echo $dxProb; ?></p></a></li>
        </ul>
    </div>
    <div data-role="collapsible" <?php if ($openme=="TD") {echo 'data-collapsed="false"';}?>>
        <h3>Tasks/Progress/Summaries</h3>
        <div data-role="collapsibleset" data-inset="false">
        <div data-role="collapsible" data-theme="a" <?php if ($openme=="TD") {echo 'data-collapsed="false"';}?>>
            <h3>Tasks<span class="ui-li-count"><?php if (count($planTasks->todo)) {echo 'Due '.count($planTasks->todo);}?></span></h3>
            <ul data-role="listview" data-count-theme="a">
                <?php
                foreach ($planTasks->todo as $tmp) {
                    $tmpIdx = $tmp->attributes()->created;
                    $tmpAtt = $tmp->attributes()->due;
                    $tmpDate = substr($tmpAtt,4,2).'/'.substr($tmpAtt,6,2);
                    $tmpStr = (string)$tmp;
                    $tmpDT1 = date_create('now');
                    $tmpDT2 = date_create(substr($tmpAtt,0,4).'-'.substr($tmpAtt,4,2).'-'.substr($tmpAtt,6,2));
                    $tmpDT0 = date_diff($tmpDT1,$tmpDT2)->format('%R%a');
                    $tmpDTstr1 = "";
                    $tmpDTstr2 = "";
                    if ($tmpDT0<3) {
                        $tmpDTstr1 = 'color:green';
                        $tmpDTstr2 = 'background-color:gold; color:black';
                    }
                    if ($tmpDT0<1) {
                        $tmpDTstr1 = 'color:red';
                        $tmpDTstr2 = 'background-color:red; color:black';
                    }
                    echo '
                    <li data-icon="check">
                        <a href="ptmain.php?id='.$mrn.'&idx='.$tmpIdx.'&ed=T#editTask" data-ajax="false">
                            <p style="white-space: pre-wrap"><b><span style="'.$tmpDTstr1.'">'.$tmpStr.'</span></b></p><span class="ui-li-count" style="'.$tmpDTstr2.'">'.$tmpDate.'</span>
                        </a>
                        <a href="ptmain.php?id='.$mrn.'&idx='.$tmpIdx.'&td=cl" >Check</a>
                    </li>';
                }
                echo '<div data-role="collapsible" data-inset="true" >
                    <h3>Done<span class="ui-li-count">'.count($planDone->todo).'</span></h3>
                        <ul data-role="listview" >';
                foreach ($planDone->todo as $tmp) {
                    $tmpIdx = $tmp->attributes()->created;
                    $tmpAtt = $tmp->attributes()->due;
                    $tmpDate = substr($tmpAtt,4,2).'/'.substr($tmpAtt,6,2);
                    $tmpStr = (string)$tmp;
                    
                    echo '
                    <li data-icon="back">
                        <a href="#" data-ajax="false">
                            <p style="white-space: pre-wrap"><i>'.$tmpStr.'</i></p><span class="ui-li-count"><i>'.$tmpDate.'</i></span>
                        </a>
                        <a href="ptmain.php?id='.$mrn.'&idx='.$tmpIdx.'&td=uc" >Uncheck</a>
                    </li>';
                }
                echo '
                    </ul>
                    </div>';
                ?>
                <li data-icon="plus" data-theme="b"><a style="text-align:center;" href="ptmain.php?id=<?php echo $mrn;?>&ed=T#editTask" data-ajax="false">Add task...</a></li>
            </ul>
        </div>
        <div data-role="collapsible" data-theme="a">
            <h3>Progress Notes<span class="ui-li-count">0</span></h3>
            <ul data-role="listview" >
                <?php
                $tmpNote = 'This is a really long note that represents the progress note for this date. It shouldn`t have any impact on how things look. I will just keep typing here since I don`t know how much more space that I need to take up.';
                echo '<li data-icon="false"><a href="#popup01" data-rel="popup" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-transition="pop"><p>'.$tmpNote.'</p><span class="ui-li-count">9/14</span></a></li>';
                echo '<div data-role="popup" id="popup01" data-overlay-theme="b" data-theme="a" class="ui-content"><p>'.$tmpNote.'</p></div>';
                ?>
                <li data-icon="plus" data-theme="b"><a style="text-align:center;" href="#notdone" data-rel="popup" data-transition="pop">Add progress note...</a></li>
            </ul>
        </div>
        <div data-role="collapsible" <?php if ($openme=='WK') {echo 'data-collapsed="false"';}?>>
            <h3>Weekly Summaries<span class="ui-li-count"><?php echo count($notesWk->summary);?></span></h3>
            <ul data-role="listview" data-count-theme="b">
                <?php
                foreach ($notesWk->summary as $tmp) {
                    $tmpIdx = $tmp->attributes()->created;
                    $tmpAtt = $tmp->attributes()->date;
                    $tmpDate = substr($tmpAtt,4,2).'/'.substr($tmpAtt,6,2);
                    $tmpStr = (string)$tmp;
                    echo '
                    <li data-icon="false">
                        <a href="ptmain.php?id='.$mrn.'&idx='.$tmpIdx.'&ed=S#editWkSumm" data-ajax="false">
                            <p style="white-space: pre-wrap">'.$tmpStr.'</p><span class="ui-li-count">'.$tmpDate.'</span>
                        </a>
                    </li>';
                }
                ?>
                <li data-icon="plus" data-theme="b"><a style="text-align:center;" href="ptmain.php?id=<?php echo $mrn;?>&ed=S#editWkSumm" data-ajax="false">Add summary...</a></li>
            </ul>
        </div>
        </div>
    </div>
    <div data-role="collapsible"> 
        <h3>Patient history (CORES)</h3>
            <p><small><?php echo $hx;?></small></p>
    </div>
    <div data-role="collapsible">
        <h3>Meds/Diet (CORES)</h3>
        <div data-role="collapsibleset" >
            <div data-role="collapsible" data-collapsed="false">
                <h3>Cardiac Meds</h3>
                <div id="med-Card" class="ui-content" >
                    <ul data-role="listview" data-inset="false" data-mini="true">
                        <li data-role="list-divider" data-theme="a" >Drips</li>
                        <?php medlist('drips',$MAR,'Cardiac'); medlist('drips',$MAR,'Arrhythmia'); ?>
                        <li data-role="list-divider" data-theme="a" >Scheduled</li>
                        <?php medlist('meds',$MAR,'Cardiac'); medlist('meds',$MAR,'Arrhythmia'); ?>
                        <li data-role="list-divider" data-theme="a" >PRN</li>
                        <?php medlist('prn',$MAR,'Cardiac'); medlist('prn',$MAR,'Arrhythmia'); ?>
                    </ul> 
                </div>
            </div>
            <div data-role="collapsible">
                <h3>Other Meds</h3>
                <div id="med-Other" class="ui-content" >
                    <ul data-role="listview" data-inset="false" data-mini="true">
                        <li data-role="list-divider" data-theme="a" >Drips</li>
                        <?php medlist('drips',$MAR,'Other'); ?>
                        <li data-role="list-divider" data-theme="a" >Scheduled</li>
                        <?php medlist('meds',$MAR,'Other'); ?>
                        <li data-role="list-divider" data-theme="a" >PRN</li>
                        <?php medlist('prn',$MAR,'Other'); ?>
                    </ul>
                </div>
            </div>
            <div data-role="collapsible">
                <h3>Diet</h3>
                <div id="med-Diet" class="ui-content" >
                    <ul>
                        <?php medlist('diet',$MAR); ?>
                    </ul>
                </div>
            </div>
        </div><!-- /MEDS accordion -->
    </div><!-- /MAR -->
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
    <label for="textarea-dxNotes">Notes:</label>
    <textarea cols="40" rows="8" name="dxNotes00" id="textarea-dxNotes"><?php echo $dxNotes; ?></textarea>
    <label for="textarea-dxCrd">Diagnoses & Problems:</label>
    <textarea cols="40" rows="8" name="dxCrd00" id="textarea-dxCrd"><?php echo $dxCrd; ?></textarea>
    <label for="textarea-dxEP">EP Diagnoses:</label>
    <textarea cols="40" rows="8" name="dxEP00" id="textarea-dxEP"><?php echo $dxEP; ?></textarea>
    <label for="textarea-dxSurg">Surgical/Cath/Interventions:</label>
    <textarea cols="40" rows="8" name="dxSurg00" id="textarea-dxSurg"><?php echo $dxSurg; ?></textarea>
    <label for="textarea-dxProb">Problem list:</label>
    <textarea cols="40" rows="8" name="dxProb00" id="textarea-dxProb"><?php echo $dxProb; ?></textarea>
    <input type="submit" class="ui-btn ui-shadow ui-btn-icon-right ui-corner-all ui-icon-edit" value="SAVE" data-theme="b">
    <input type="hidden" name="edit" value="dx" />
</form>
</div><!-- /content -->

</div><!-- /edit page -->
<!-- ======================================================================= -->
<div data-role="page" id="editWkSumm" data-dom-cache="false">
<div data-role="header" data-position="fixed">
    <h4 style="white-space: normal; text-align: center" >Summary note</h4>
    <a href="#" data-ajax="false" data-rel="back" class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all ui-icon-delete ui-btn-icon-notext" >Cancel</a>
</div><!-- /header -->
<DIV data-role="content" style="text-align:center;">
<?php
if ($edtype=="S") {
    $notesTmp = $notesWk->xpath("summary[@created='".$index."']")[0];
    if (!empty($notesTmp)) {
        $notesDate = $notesTmp->attributes()->date;
        $notes_Y = substr($notesDate,0,4);
        $notes_M = substr($notesDate,4,2);
        $notes_D = substr($notesDate,6,2);
    } else {
        $notesDate = date("Ymd");
        $notes_Y = date("Y");
        $notes_M = date("m");
        $notes_D = date("d");
    }
    if (($index) and !($notesTmp)) {
    ?>
        <br>
        <h1>Note deleted!</h1>
        <p><?php echo $index;?></p>
    <?php
    } else {
?>
    <form method="post" <?php echo 'action="ptmain.php?id='.$mrn.'"';?> data-ajax="false">
        <input type="hidden" name="edit" value="wksumm" />
        <input type="hidden" name="editdate" value="<?php 
            if (!empty($index)) {
                echo $index;
            } else { 
                echo date("YmdHis"); 
            } ?>" />
        <input type="hidden" name="idxdate" value="<?php echo $index; ?>" />
        <label for="textarea-wkSum">Weekly summary: <?php if ($index) {echo $notes_M.'/'.$notes_D.' @ '.$notes_Y;}?></label>
        <textarea cols="40" rows="8" name="wkSumm" id="textarea-wkSum" autofocus><?php echo $notesTmp;?></textarea>
        <input type="text" data-role="datebox" data-options='{"mode":"calbox", "overrideDateFormat":"%m/%d/%Y", "defaultValue":[<?php echo $notes_Y.','.(ltrim($notes_M,"0")-1).','.ltrim($notes_D,"0"); ?>], "showInitialValue":true}'>
        <input type="submit" class="ui-btn ui-shadow ui-btn-icon-right ui-corner-all ui-icon-edit" name="action" value="SAVE" data-theme="b" />
        <?php 
        if (!empty($index)) {
            ?>
            <input type="hidden" name="mod" value="true" />
            <input type="submit" class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all" name="action" value="DELETE" />
            <?php
        }
        ?>
    </form>
<?php 
}
} ?>
</div>  <!-- /content -->
</div>
<!-- ======================================================================= -->
<div data-role="page" id="editTask" data-dom-cache="false">
<div data-role="header" data-position="fixed">
    <h4 style="white-space: normal; text-align: center" >Task todo</h4>
    <a href="#" data-ajax="false" data-rel="back" class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all ui-icon-delete ui-btn-icon-notext" >Cancel</a>
</div><!-- /header -->
<DIV data-role="content" style="text-align:center;">
<?php
if ($edtype=="T") {
    $todoTmp = $planTasks->xpath("todo[@created='".$index."']")[0];
    if (!empty($todoTmp)) {
        $todoDate = $todoTmp->attributes()->due;
        $todo_Y = substr($todoDate,0,4);
        $todo_M = substr($todoDate,4,2);
        $todo_D = substr($todoDate,6,2);
    } else {
        $todoDate = date("Ymd");
        $todo_Y = date("Y");
        $todo_M = date("m");
        $todo_D = date("d");
    }
    if (($index) and !($todoTmp)) {
    ?>
        <br>
        <h1>Task deleted!</h1>
        <p><?php echo $index;?></p>
    <?php
    } else {
?>
    <form method="post" <?php echo 'action="ptmain.php?id='.$mrn.'"';?> data-ajax="false">
        <input type="hidden" name="edit" value="todo" />
        <input type="hidden" name="idxdate" value="<?php echo $index; ?>" />
        <label for="textarea-todo">Task: <?php if ($index) {echo $todo_M.'/'.$todo_D;}?></label>
        <textarea cols="40" rows="8" name="taskTodo" id="textarea-todo" autofocus><?php echo $todoTmp;?></textarea>
        <input name="duedate" type="text" data-role="datebox" data-options='{"mode":"calbox", "overrideDateFormat":"%m/%d/%Y", "defaultValue":[<?php echo $todo_Y.','.(ltrim($todo_M,"0")-1).','.ltrim($todo_D,"0"); ?>], "showInitialValue":true}' >
        <input type="submit" class="ui-btn ui-shadow ui-btn-icon-right ui-corner-all ui-icon-edit" name="action" value="SAVE" data-theme="b" />
        <?php 
        if (!empty($index)) {
            ?>
            <input type="hidden" name="mod" value="true" />
            <input type="submit" class="ui-btn ui-shadow ui-btn-icon-left ui-corner-all" name="action" value="DELETE" />
            <?php
        }
        ?>
    </form>
<?php 
    }
} ?>
</div>  <!-- /content -->
</div>

</body>