<?php
//=========================Configuration ===================
$loop=FALSE;  				              // ==== The script will run forever
if ($loop) set_time_limit(0);   	              // ~ No time execution limit
else error_reporting(0);                          // ~ No error displaying on web browser

$tag="timandcatherine";			              // ==== Tag to search with
$srcDir="Instagram_Pictures";		              // ==== Path to Folder which stores original pictures from Instagram
$archiveDir="archive";		                      // ==== Path of files which we will store the images in
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

$imgArr= getImages($imgArr, $content);

$imgArr = checkArchiveImages($imgArr, $archiveDir);

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
 
      //print the image
      printImage($newMd5.".jpg", $srcDir);

      //include a wait, so the image can get into the printer
      sleep(45);

      //store the image in the archive directory
      moveImage($newMd5.".jpg", $srcDir, $archiveDir);
    }

    if (!$loop) break;
    else sleep($interval);
}

/**
*  GetImages.
*
*  @param array $imgArr  The array of images passed in.
*  @param array $content The content object used to talk to instagram.
*
*  @return array Return the array of images which we're going to process.
*/
function getImages($imgArr, $content)
{

  foreach($content->data as $index => $image) 
  {
    $imgArr[$index] = $image->images->standard_resolution->url; 

  } 

  return $imgArr;
}

/**
*  CheckArchiveImages.
*
*  @param array  $imgArr     The array of images passed in.
*  @param string $archiveDir The archive image directory.

*  @return array Return the array of images which we're going to process.
*/
function checkArchiveImages($imgArr, $archiveDir)
{
  $return_array = array();

  //check if the file names exist int he archive folder, if they do, we remove them from our list of images
  foreach($imgArr as $index => $file_url) {

    $file_name = md5($file_url);

    //check the archive directory, if it exists in there, remove it from our list of files to get.
    if(file_exists($archiveDir . '/' . $file_name . '.jpg')) {
      //unset($imgArr[$index]);
      continue;
    }

   $return_array[] = $imgArr[$index];

  }

  return $return_array;
}

/**
*  PrintImage. Mehtod to run command line print.
*
*  @param string $image_name The name of the image to print.
*  @param string $srcDir     The source image directory.
*
*  @return void
*/
function printImage($image_name, $srcDir)
{
  $cur_dir = dirname ( __FILE__ );
  $full_path = $cur_dir . '/' . $srcDir . '/' . $image_name;

 exec('lp -o landscape ' . $full_path);
}

/**
*  MoveImage. Moves the image to the archive folder.
*
*  @param string $image_name The name of the image to move.
*  @param string $srcDir     The source image directory.
*  @param string $archiveDir The archive image directory.
*
*  @return void
*/
function moveImage($image_name, $srcDir, $archiveDir)
{
  $cur_dir = dirname ( __FILE__ );
  $full_path_from = $cur_dir . '/' . $srcDir . '/' . $image_name;
  $full_path_to = $cur_dir . '/' . $archiveDir . '/' . $image_name;

  exec('mv ' . $full_path_from . ' ' . $full_path_to);
}
