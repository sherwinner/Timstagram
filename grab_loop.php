<?php
//=========================Configuration ===================
$loop=TRUE;  				              // ==== The script will run forever
if ($loop) set_time_limit(0);   	              // ~ No time execution limit
else error_reporting(0);                          // ~ No error displaying on web browser

$tag="timandcatherine";			              // ==== Tag to search with															
$srcDir="Instagram_Pictures";		              // ==== Path to Folder which stores original pictures from Instagram
$interval=60;           		              //==== Interval time (in seconds) - should be >= 20
$clientId="4ac8a964c23a4230af9196064559e703";         // ==== Instagram Client ID (need to register with Instagram to get it)

while (TRUE)
   {
   $ignoredImageArray=array(0,0,0,0);    
// GET THE URL
   $url="https://api.instagram.com/v1/tags/".$tag."/media/recent?client_id=".$clientId; // SEARCH BY TAGS - Instagram API tags endpoint

// GET 6 IMAGES FROM ABOVE URL:
   $jsonContent=file_get_contents($url);
   $content = json_decode($jsonContent);
   $imgArr=array();
   $realImageArray=array();
   $userArr=array();
   $avatarArr=array();
   $realAvatarArray=array();
   $timeArr=array();

   for ($i=0;$i<6;$i++)
      {
      $imgArr[]=$content->data[$i]->images->standard_resolution->url; 
      }

// CHECK IF IMAGE EXISTENT -- AND CREATE IMAGE OBJECT OF INSTAGRAM PICTURES
   $oldImageNameArray = glob($srcDir . "*.jpg");
   for ($i=0;$i<count($imgArr);$i++)
      {
      $existent=FALSE;
      $newMd5=md5($imgArr[$i]);
      foreach($oldImageNameArray as $oldMd5)
         {
         $oldMd5=substr($oldMd5,strpos($oldMd5,"/")+1);
         $oldMd5=substr($oldMd5,0,strpos($oldMd5,"."));
         if ($oldMd5==$newMd5)
            {
            $existent=TRUE;
            break;
            }
        }

     if ($existent)
        {
        $ignoredImageArray[$i]=TRUE;				                //create if not originally downloaded in source folder
        $realImageArray[$i] = imagecreatefromjpeg($srcDir."/".$newMd5.".jpg");  //create if not in processed folder
        }
     else
        {
        $realImageArray[$i] = imagecreatefromstring(file_get_contents($imgArr[$i]));
	    imagejpeg($realImageArray[$i], $srcDir."/".$newMd5.'.jpg');
        }
    }

    if (!$loop) break;
    else sleep($interval);
}
