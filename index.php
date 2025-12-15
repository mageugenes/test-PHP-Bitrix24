<?php

require_once 'crest/src/crest.php';

set_time_limit(0); 
ini_set('memory_limit', '1024M'); 

$allCompanies = [];
$start = 0;
$pageSize = 50;
$maxCompanies = 10000; 
$batchSize = 50; 

echo "Start...<br>\n";

while (count($allCompanies) < $maxCompanies) {
    
    $batchCommands = [];

    for ($i = 0; $i < $batchSize; $i++) {

        $batchCommands["page_{$start}"] = [
            'method' => 'crm.company.list',
            'params' => [
                'start' => $start,
                'filter' => [], 
                'select' => ['ID', 'TITLE', 'PHONE', 'EMAIL', 'ASSIGNED_BY_ID'], 
            ]
        ];

        $start += $pageSize;
    }

    $batchResult = CRest::call('batch', [
        'halt' => 0,
        'cmd' => $batchCommands
    ]);

    if (isset($batchResult['error'])) {

        die(print_r($batchResult['error'], true));
    }

    foreach ($batchResult['result']['result'] as $key => $page) {

        if (isset($page['error'])) {

            echo "Error {$key}: " . $page['error']['error_description'] . "<br>\n";
            continue;
        }

        $allCompanies = array_merge($allCompanies, $page);
    }

    $lastPage = end($batchResult['result']['result']);

    if (count($lastPage) < $pageSize) {

        break;
    }

    sleep(1);
}

$allCompanies = array_slice($allCompanies, 0, $maxCompanies);

file_put_contents('companies.json', json_encode($allCompanies, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "Done. " . count($allCompanies) . " companies. The result is saved in the companies.json file.<br>\n";

echo "Companies:<pre>";
print_r($allCompanies);
echo "</pre>";