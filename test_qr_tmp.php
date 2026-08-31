<?php
require __DIR__.'/vendor/autoload.php';
$o = new chillerlan\QRCode\QROptions(['outputType' => 'png', 'scale' => 5, 'imageBase64' => true]);
$q = new chillerlan\QRCode\QRCode($o);
$img = $q->render('TEST');
echo strlen($img) > 100 ? 'OK len='.strlen($img) : 'FAIL';
