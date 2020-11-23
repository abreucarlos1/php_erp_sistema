<?php
//Proteção contra tentativa de abertura direta desse arquivo
if(substr($_SERVER['HTTP_REFERER'],0,15)=="http://srvdbs01")
//if($_SERVER['HTTP_REFERER']=="http://srvdbs01/dvmsys/testes/dhtml.php")
//if(true)
{
?>
document.write("<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0\" id=\"mvfch\" width=\"1\" height=\"1\" style=\"position:absolute;\">");
document.write("<param name=\"movie\" value=\"/dvmsys/includes/DVMfechamento.swf\" />");
document.write("<param name=\"quality\" value=\"high\" />");
document.write("<embed src=\"/dvmsys/includes/DVMfechamento.swf\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"1\" height=\"1\"></embed></object>");
<?php
}
?>