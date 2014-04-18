<?php 
function force_download($dir,$file)
{
    if ((isset($file))&&(file_exists($dir.$file))) 
    {
       header("Content-type: application/force-download");
       header('Content-Disposition: inline; filename="' . $dir.$file . '"');
       header("Content-Transfer-Encoding: Binary");
       header("Content-length: ".filesize($dir.$file));
       header('Content-Type: application/octet-stream');
       header('Content-Disposition: attachment; filename="' . $file . '"');
       readfile("$dir$file");
    }else {
       echo $dir.$file." No file selected";
    } //end if

}//end function

if($_GET["file"]) $file=$_GET["file"];
if($_GET["dir"]) $dir=$_GET["dir"];
 force_download($dir,$file);
?>
