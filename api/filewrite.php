<?php

$docDir = "../resources/lessonplanning/";
$strfilename = $_GET['filename'];
//echo($docDir.$strfilename);
readfile($docDir . $strfilename);
?>