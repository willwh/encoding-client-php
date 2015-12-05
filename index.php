<?php

require_once $path . 'lib/encoding.php';

const ENCODING_API_URL = "https://manage.encoding.com";


$client = new Encoding_Client('','');

try {
  $client->setFieldValue('codec','h264');
  $client->request->addAttribute('codec','h264');
  $client->sendRequest('POST');
}
catch (Exception $e) {
  print_r($e);
}
