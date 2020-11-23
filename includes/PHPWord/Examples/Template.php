<?php


require_once '../PHPWord.php';

$PHPWord = new PHPWord();

$document = $PHPWord->loadTemplate('Template.docx');

$document->setValue('Value1', 'Sun');
$document->setValue('Value2', 'Mercury');
$document->setValue('Value3', 'Venus');
$document->setValue('Value4', 'Earth');
$document->setValue('Value5', 'Mars');
$document->setValue('Value6', 'Jupiter');
$document->setValue('Value7', 'Saturn');
$document->setValue('Value8', 'Uranus');
$document->setValue('Value9', 'Neptun');
$document->setValue('Value10', 'Pluto');

$document->setValue('weekday', date('l'));
$document->setValue('time', date('H:i'));

$temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');
$document->save($temp_file);

// Your browser will name the file "myFile.docx"
// regardless of what it's named on the server 
header("Content-Disposition: attachment; filename=myFile.docx");
readfile($temp_file); // or echo file_get_contents($temp_file);
unlink($temp_file);  // remove temp file

?>