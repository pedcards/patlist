<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
<?php
error_reporting(-1);
$user = htmlentities($_SERVER['REMOTE_USER']);
$refer = htmlentities($_SERVER['HTTP_REFERER']);
    if (strpos($refer, 'ptmain.php') == FALSE) {
        $_SESSION['ref'] = $refer;
    }

$mrn = (\filter_input(\INPUT_GET, 'id')) ?: '1504193';
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
        $statusCons = (string)$status->attributes()->cons;
        $statusTxp = (string)$status->attributes()->txp;
        $statusRes = (string)$status->attributes()->res;
        $statusScamp = (string)$status->attributes()->scamp;
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
    $prov = $id[0]->prov;
        $provCard = (string)$prov->attributes()->provCard;
        $provEP = (string)$prov->attributes()->provEP;
        $provPCP = (string)$prov->attributes()->provPCP;

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
    $todoCk = \filter_input(\INPUT_GET, 'td');
    if (!empty($todoCk)) {
        if ($todoCk=="cl") {
            if (empty($planDone)) {
                $plan[0]->addChild('done');
            }
            $todoTmp = $planTasks->xpath("todo[@created='".$index."']");
            $planDone = $plan[0]->done;
            $todoTmp[0]->addAttribute('done',$timenow);
            $dom_tasks = dom_import_simplexml($planTasks[0]);
            $dom_todo = dom_import_simplexml($todoTmp[0]);
            $dom_done= dom_import_simplexml($planDone[0]);
            $dom_new = $dom_done->appendChild($dom_todo->cloneNode(true));
            $new_node = simplexml_import_dom($dom_new);
            unset($todoTmp[0][0]);
        }
        if ($todoCk=="uc") {
            $todoTmp = $planDone->xpath("todo[@created='".$index."']");
            $planTasks = $plan[0]->tasks;
            $todoTmp[0]->addAttribute('done',"");
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
        foreach ($DX as $tmp)
        {
            unset($tmp[0]); // removes all children, and 'diagnosis' as well.
        }
        $DX = $id[0]->addChild('diagnoses'); //$DX->addAttribute("date","now");
            $DX[0]->addChild("notes", $dxNotes);
            $DX[0]->addChild("card", $dxCrd);
            $DX[0]->addChild("ep", $dxEP);
            $DX[0]->addChild("surg", $dxSurg);
        $DX['ed'] = $timenow;
        $DX['au'] = $user;
        $xml->asXML("currlist.xml");
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
                        #$xml->asXML("currlist.xml");
                    }
                    $trash = $id[0]->trash;
                    $notesTmp[0]->addAttribute('del',$timenow);
                    $dom_wk = dom_import_simplexml($notesWk[0]);
                    $dom_summ = dom_import_simplexml($notesTmp[0]);
                    $dom_trash = dom_import_simplexml($trash[0]);
                    $dom_new = $dom_trash->appendChild($dom_summ->cloneNode(true));
                    $new_node = simplexml_import_dom($dom_new);
                    unset($notesTmp[0][0]);
                    #$xml->asXML("currlist.xml");
                }
            } else {
                $notesTmp[0][0] = $editval;
                $notesTmp[0][0]['ed'] = $timenow;
                $notesTmp[0][0]['au'] = $user;
            }
        } else {
            //add a note
            $summ = $notesWk->addChild('summary', $editval);
            $summ->addAttribute('date', $editdate);
            $summ->addAttribute('created', $editdate);
            $summ->addAttribute('ed',$timenow);
            $summ->addAttribute('au', $user);
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
                    $todoTmp[0]->addAttribute('del',$timenow);
                    $dom_task = dom_import_simplexml($planTasks[0]);
                    $dom_todo = dom_import_simplexml($todoTmp[0]);
                    $dom_trash = dom_import_simplexml($trash[0]);
                    $dom_new = $dom_trash->appendChild($dom_todo->cloneNode(true));
                    simplexml_import_dom($dom_new);
                    unset($todoTmp[0][0]);
                }
            } else {
                $todoTmp[0][0] = $editval;
                $todoTmp[0][0]['due'] = $editdate;
                $todoTmp[0][0]['ed'] = $timenow;
                $todoTmp[0][0]['au'] = $user;
            }
        } else {
            //add a note
            $summ = $planTasks->addChild('todo', $editval);
            $summ->addAttribute('due', $editdate);
            $summ->addAttribute('created', $timenow);
            $summ->addAttribute('ed',$timenow);
            $summ->addAttribute('au', $user);
        }
        $xml->asXML("currlist.xml");
        $openme = 'TD';
    }
    if ($edit == "status") {
        if (empty($status)) {
            $status = $id[0]->addChild('status');
        }
        $statusCons = \filter_input(\INPUT_POST, 'statusCons');
        $statusTxp = \filter_input(\INPUT_POST, 'statusTxp');
        $statusRes = \filter_input(\INPUT_POST, 'statusRes');
        $statusScamp = \filter_input(\INPUT_POST, 'statusScamp');
        $status['cons']=$statusCons;
        $status['txp']=$statusTxp;
        $status['res']=$statusRes;
        $status['scamp']=$statusScamp;
        
        $status['ed']=$timenow;
        $status['au']=$user;
        $xml->asXML("currlist.xml");
//        print_r($status);
        cloneBlob($status,'stat');
    }
    if ($edit == "provider") {
        $provCard = \filter_input(\INPUT_POST, 'provCard',FILTER_SANITIZE_SPECIAL_CHARS);
        $provEP = \filter_input(\INPUT_POST, 'provEP');
        $provPCP = \filter_input(\INPUT_POST, 'provPCP');
        $prov->attributes()->provCard = $provCard;
        $prov->attributes()->provEP = $provEP;
        $prov->attributes()->provPCP = $provPCP;
        if (empty($prov)) {
            $prov = $id[0]->addChild('prov');
            $prov->addAttribute("provCard",$provCard);
            $prov->addAttribute("provEP",$provEP);
            $prov->addAttribute("provPCP",$provPCP);
        }
        $prov['ed'] = $timenow;
        $prov['au'] = $user;
        $xml->asXML("currlist.xml");
    }
    if ($edit) {
        file_put_contents("../change",$timenow.':'.$user);
    }
    
    
    cloneBlob($status[0],'stat');
    
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
echo 'done';
?>
    </body>
</html>
