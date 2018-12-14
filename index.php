<?php
// Include the phpQuery library
// Download at http://code.google.com/p/phpquery/
include 'libs/phpQuery.php';

// path to save in
$path = 'data/';
$fileName = 'data_'.date('Ymd_His').'.txt';


# Website URL
$url = "http://www.asianodds.com/next_200_games.asp";// "http://www.asianodds.com/Italy__Serie_A.html";
/*$ch = curl_init();
$timeout = 7;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$html = curl_exec($ch);
curl_close($ch);*/


// return the html content from URL,could be used for local and remote files
function getURLContent($url){
    $doc = new DOMDocument;
    $doc->preserveWhiteSpace = FALSE;
    @$doc->loadHTMLFile($url);
    return $doc->saveHTML();
}


function fcl_utilities_is_html($string) {
    // Check if string contains any html tags.
    return preg_match('/<\s?[^\>]*\/?\s?>/i', $string);
}

$html = getURLContent($url);

// Create phpQuery document with returned HTML
$doc = phpQuery::newDocument($html);
// $doc = phpQuery::newDocument(file_get_contents("static_gistfile3.html"));

$table = pq('table')->get(4);

// print_r($table);
// echo $table->getElementsByTagName('tr')->length;

$records = [];
$dayIndex = -1;
$dayEventIndex  = -1;
$teamIndex = -1;
foreach ($table->getElementsByTagName('tr') as $item) {
    $tr = pq($item);
    if($tr->hasClass('down')){
        $teamIndex = -1;
        if($tr->find('img')->length === 0){
            // start of new date
            $dayIndex += 1;
            // reset events counter
            $dayEventIndex  = 0;
        }else{
            //start of new event
            $dayEventIndex  += 1;
            // reset teams counter
            $teamIndex = -1;

        }

    }
    if($tr->hasClass('main')){
        // we increment for teams
        $teamIndex  += 1;
    }


//    echo "dayIndex: $dayIndex || dayEventIndex: $dayEventIndex || teamIndex: $teamIndex.\n";
//    print_r($tr->html());


    if($dayIndex >= 0 && $dayEventIndex === 0 && $teamIndex === -1){

        $records[$dayIndex]['date'] = $tr->html();
    }
    if($dayIndex >= 0 && $dayEventIndex >= 0 && $teamIndex >= 0){

        $records[$dayIndex]['events'][$dayEventIndex][$teamIndex] = $tr;
    }


    // print_r($item);
    // echo "\n----\n";
}

//print_r($records);

$dateRegex = "/(\d{2}\/\d{2}\/\d{4})$/";
$scrappedRecords = [];
$recordsCounter = 0;

foreach ($records as $day){
    //print_r($day);
    // echo pq($day['date']);
    $date = trim(pq($day['date'])->find('strong')->html());
    preg_match($dateRegex, $date, $output_array);
    $date = $output_array[0];

    foreach ($day['events'] as $dayEvent){
        $record = sprintf('%03d', ++$recordsCounter)."|$date";
        $team1 = pq($dayEvent[0]);
        $team2 = pq($dayEvent[1]);
        // time
        $time = trim($team1->find('td:nth-child(1) font')->text());
        $record .= "|$time";
        // betting event
        $teamsName = trim($team1->find('td:nth-child(2)')->text()) . ":" . trim($team2->find('td:nth-child(1)')->text());
        $record .= "|$teamsName";
        //currentSpread
        $currentSpread1Obj = $team1->find('td:nth-child(3)');
        while(fcl_utilities_is_html($currentSpread1Obj->html())){
            // has childs
            $currentSpread1Obj = pq($currentSpread1Obj->html());
        }
        $currentSpread1 = $currentSpread1Obj->text();
        $currentSpread1Color = trim($currentSpread1Obj->attr('color'));
        $currentSpread1 .= $currentSpread1Color === "#FF0000" ? 'R' : '';
        $currentSpread1 .= $currentSpread1Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $currentSpread2Obj = $team2->find('td:nth-child(2)');
        while(fcl_utilities_is_html($currentSpread2Obj->html())){
            // has childs
            $currentSpread2Obj = pq($currentSpread2Obj->html());
        }
        $currentSpread2 = $currentSpread2Obj->text();
        $currentSpread2Color = trim($currentSpread2Obj->attr('color'));
        $currentSpread2 .= $currentSpread2Color === "#FF0000" ? 'R' : '';
        $currentSpread2 .= $currentSpread2Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $currentSpread = "$currentSpread1:$currentSpread2";
        $record .= "|$currentSpread";

        // currentOdds
        $currentOdd1Obj = $team1->find('td:nth-child(4)');
        while(fcl_utilities_is_html($currentOdd1Obj->html())){
            // has childs
            $currentOdd1Obj = pq($currentOdd1Obj->html());
        }
        $currentOdd1 = $currentOdd1Obj->text();
        $currentOdd1Color = trim($currentOdd1Obj->attr('color'));
        $currentOdd1 .= $currentOdd1Color === "#FF0000" ? 'R' : '';
        $currentOdd1 .= $currentOdd1Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $currentOdd2Obj = $team2->find('td:nth-child(3)');
        while(fcl_utilities_is_html($currentOdd2Obj->html())){
            // has childs
            $currentOdd2Obj = pq($currentOdd2Obj->html());
        }
        $currentOdd2 = $currentOdd2Obj->text();
        $currentOdd2Color = trim($currentOdd2Obj->attr('color'));
        $currentOdd2 .= $currentOdd2Color === "#FF0000" ? 'R' : '';
        $currentOdd2 .= $currentOdd2Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $currentOdd = "$currentOdd1:$currentOdd2";
        $record .= "|$currentOdd";

        //openSpread
        $openSpread1Obj = $team1->find('td:nth-child(5)');
        while(fcl_utilities_is_html($openSpread1Obj->html())){
            // has childs
            $openSpread1Obj = pq($openSpread1Obj->html());
        }
        $openSpread1 = $openSpread1Obj->text();
        $openSpread1Color = trim($openSpread1Obj->attr('color'));
        $openSpread1 .= $openSpread1Color === "#FF0000" ? 'R' : '';
        $openSpread1 .= $openSpread1Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored



        $openSpread2Obj = $team2->find('td:nth-child(4)');
        while(fcl_utilities_is_html($openSpread2Obj->html())){
            // has childs
            $openSpread2Obj = pq($openSpread2Obj->html());
        }
        $openSpread2 = $openSpread2Obj->text();
        $openSpread2Color = trim($openSpread2Obj->attr('color'));
        $openSpread2 .= $openSpread2Color === "#FF0000" ? 'R' : '';
        $openSpread2 .= $openSpread2Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $openSpread = "$openSpread1:$openSpread2";
        $record .= "|$openSpread";


        // openOdds
        $openOdd1Obj = $team1->find('td:nth-child(6)');
        while(fcl_utilities_is_html($openOdd1Obj->html())){
            // has childs
            $openOdd1Obj = pq($openOdd1Obj->html());
        }
        $openOdd1 = $openOdd1Obj->text();
        $openOdd1Color = trim($openOdd1Obj->attr('color'));
        $openOdd1 .= $openOdd1Color === "#FF0000" ? 'R' : '';
        $openOdd1 .= $openOdd1Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $openOdd2Obj = $team2->find('td:nth-child(5)');
        while(fcl_utilities_is_html($openOdd2Obj->html())){
            // has childs
            $openOdd2Obj = pq($openOdd2Obj->html());
        }
        $openOdd2 = $openOdd2Obj->text();
        $openOdd2Color = trim($openOdd2Obj->attr('color'));
        $openOdd2 .= $openOdd2Color === "#FF0000" ? 'R' : '';
        $openOdd2 .= $openOdd2Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $openOdd = "$openOdd1:$openOdd2";
        $record .= "|$openOdd";

        //lineCurrent
        $lineCurrentObj = $team1->find('td:nth-child(7)');
        while(fcl_utilities_is_html($lineCurrentObj->html())){
            // has childs
            $lineCurrentObj = pq($lineCurrentObj->html());
        }
        $lineCurrent = $lineCurrentObj->text();
        $lineCurrentColor = trim($lineCurrentObj->attr('color'));
        $lineCurrent .= $lineCurrentColor === "#FF0000" ? 'R' : '';
        $lineCurrent .= $lineCurrentColor === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored


        // lineTotal
        $lineTotalObj = $team1->find('td:nth-child(8)');
        while(fcl_utilities_is_html($lineTotalObj->html())){
            // has childs
            $lineTotalObj = pq($lineTotalObj->html());
        }
        $lineTotal = $lineTotalObj->text();
        $lineTotalColor = trim($lineTotalObj->attr('color'));
        $lineTotal .= $lineTotalColor === "#FF0000" ? 'R' : '';
        $lineTotal .= $lineTotalColor === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $line = "$lineCurrent:$lineTotal";
        $record .= "|$line";


        // overUnderCurrent
        $overUnderCurrent1Obj = $team1->find('td:nth-child(10)');
        while(fcl_utilities_is_html($overUnderCurrent1Obj->html())){
            // has childs
            $overUnderCurrent1Obj = pq($overUnderCurrent1Obj->html());
        }
        $overUnderCurrent1 = $overUnderCurrent1Obj->text();
        $overUnderCurrent1Color = trim($overUnderCurrent1Obj->attr('color'));
        $overUnderCurrent1 .= $overUnderCurrent1Color === "#FF0000" ? 'R' : '';
        $overUnderCurrent1 .= $overUnderCurrent1Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $overUnderCurrent2Obj = $team2->find('td:nth-child(7)');
        while(fcl_utilities_is_html($overUnderCurrent2Obj->html())){
            // has childs
            $overUnderCurrent2Obj = pq($overUnderCurrent2Obj->html());
        }
        $overUnderCurrent2 = $overUnderCurrent2Obj->text();
        $overUnderCurrent2Color = trim($overUnderCurrent2Obj->attr('color'));
        $overUnderCurrent2 .= $overUnderCurrent2Color === "#FF0000" ? 'R' : '';
        $overUnderCurrent2 .= $overUnderCurrent2Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $overUnderCurrent = "$overUnderCurrent1:$overUnderCurrent2";
        $record .= "|$overUnderCurrent";

        // overUnderOpen
        $overUnderOpen1Obj = $team1->find('td:nth-child(11)');
        while(fcl_utilities_is_html($overUnderOpen1Obj->html())){
            // has childs
            $overUnderOpen1Obj = pq($overUnderOpen1Obj->html());
        }
        $overUnderOpen1 = $overUnderOpen1Obj->text();
        $overUnderOpen1Color = trim($overUnderOpen1Obj->attr('color'));
        $overUnderOpen1 .= $overUnderOpen1Color === "#FF0000" ? 'R' : '';
        $overUnderOpen1 .= $overUnderOpen1Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $overUnderOpen2Obj = $team2->find('td:nth-child(8)');
        while(fcl_utilities_is_html($overUnderOpen2Obj->html())){
            // has childs
            $overUnderOpen2Obj = pq($overUnderOpen2Obj->html());
        }
        $overUnderOpen2 = $overUnderOpen2Obj->text();
        $overUnderOpen2Color = trim($overUnderOpen2Obj->attr('color'));
        $overUnderOpen2 .= $overUnderOpen2Color === "#FF0000" ? 'R' : '';
        $overUnderOpen2 .= $overUnderOpen2Color === "#0000FF" ? 'B' : '';
        //if anything else, will be ignored

        $overUnderOpen = "$overUnderOpen1:$overUnderOpen2";
        $record .= "|$overUnderOpen";




        $scrappedRecords[] = $record;
        // save to txt file
        file_put_contents($path . $fileName, $record. PHP_EOL, FILE_APPEND | LOCK_EX);

        // prints results
        echo $record. PHP_EOL;
    }

}



