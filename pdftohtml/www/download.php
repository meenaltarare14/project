<?php
   if(array_key_exists("file", $_GET)) {
       $file = $_GET['file'];
       header('Content-Type: application/pdf');
       header('Content-disposition: attachment;filename='.$file);
       readfile('../ReleaseDocs_R21/'.$file);
   }
