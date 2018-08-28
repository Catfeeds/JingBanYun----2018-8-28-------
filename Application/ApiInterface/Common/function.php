<?php
define('TYPE_STRING',0);
define('TYPE_FIELD',1);
define('TYPE_PAIR',2);
  function fieldsCompose($data,$inputArray)
  {
     $inputLength = sizeof($inputArray);

     if( $inputLength% 2 == 1)
         return false;
     $outData = array();
     for($i=0;$i<sizeof($data);$i++)
     {
         $line = array();
         for($j=0;$j<$inputLength;$j+=2)
         {
           $fieldData = '';
           if(TYPE_PAIR == $inputArray[$j][array_keys($inputArray[$j])[0]])
           {
               $inputKey = array_keys($inputArray[$j])[0];
               foreach ($inputArray[$j] as $key => $val)
               {
                   if($key == $data[$i][$inputKey])
                       $fieldData = $val;
               }
           }
           else {
               foreach ($inputArray[$j] as $key => $val) {
                   switch ($val) {
                       case TYPE_STRING:
                           $fieldData .= $key;
                           break;
                       case TYPE_FIELD:
                           $fieldData .= $data[$i][$key];
                           break;
                       default:
                           return false;
                   }
               }
           }
           $fieldName = $inputArray[$j+1][0];
           $line[$fieldName] = $fieldData;
         }

         $outData[] = $line;
     }
      return $outData;
  }
