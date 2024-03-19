<?php
use GuzzleHttp\Client;

header('Cache-Control: max-age=604800');

$tmpDir = sys_get_temp_dir();
$tmpFile = $tmpDir . '/logo_min.json';
$tmpFileContents = $tmpDir . '/logo_min.bin';
$getFromApi = false;
if(!is_file($tmpFile))
  $getFromApi = true;
if((time() - filemtime($tmpFile)) > (60 * 60 * 24))
  $getFromApi = true;

if($getFromApi){
  $api = \Config\IHM::$API_PWD['url'] . 'v1/ministere/';
  $client = new Client([
    'base_uri' => $api,
    'verify' => false,
  ]);
  $logoInfo = $client->get('logoInfo?logoid=SVG');
  file_put_contents($tmpFile, $logoInfo->getBody());
  $logoContents = $client->get('pureLogo?logoid=SVG');
  file_put_contents($tmpFileContents, $logoContents->getBody());
}

$logoInfo = json_decode(file_get_contents($tmpFile));
header('Content-type: ' . $logoInfo->imageData);
echo file_get_contents($tmpFileContents);
