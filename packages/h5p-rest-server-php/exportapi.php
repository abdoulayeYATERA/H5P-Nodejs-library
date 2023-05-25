<?php

header("Content-type: application/json; charset=utf-8");

//error_code= 1 ,error_msg= "h5p url was empty";
//error_code= 2 ,error_msg= "failed to copy"
//error_code= 3 ,error_msg= "failed to open h5p file";
//error_code= 4 ,error_msg=
//error_code= 5 ,error_msg=
//error_code= 6 ,error_msg=
//error_code= 7 ,error_msg=
//error_code= 8 ,error_msg=
//error_code= 9 ,error_msg=
//error_code= 10,error_msg=


$url = $_POST['url'];


if(!empty($url)){

  docopy($url);       


}else{

  $response = array("success"=>0,"error_msg"=>"h5p url was empty","error_code" => 1);
  exit_and_send_data_as_json($response);
}

function get_current_http_scheme() {
  if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
      $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $protocol = 'https';
  } else {
    $protocol = 'http';
  }
  return $protocol;
}

function exit_and_send_data_as_json($objet_or_array) {
  header('Content-Type: application/json; charset=utf-8');
  exit(json_encode($objet_or_array));
}

function recursiveCopy($source, $destination)
{
  if (!file_exists($destination)) {
    mkdir($destination);
  }

  $splFileInfoArr = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

  foreach ($splFileInfoArr as $fullPath => $splFileinfo) {
    //skip . ..
    if (in_array($splFileinfo->getBasename(), [".", ".."])) {
      continue;
    }
    //get relative path of source file or folder
    $path = str_replace($source, "", $splFileinfo->getPathname());

    if ($splFileinfo->isDir()) {
      mkdir($destination . "/" . $path);
    } else {
      copy($fullPath, $destination . "/" . $path);
    }
  }
}

function rrmdir($dir) { 
  if (is_dir($dir)) { 
    $objects = scandir($dir);
    foreach ($objects as $object) { 
      if ($object != "." && $object != "..") { 
        if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
        rrmdir($dir. DIRECTORY_SEPARATOR .$object);
        else
        unlink($dir. DIRECTORY_SEPARATOR .$object); 
      } 
    }
    rmdir($dir); 
  } 
}


function rmove($src, $dest){

  // If source is not a directory stop processing
  if(!is_dir($src)) return false;

  // If the destination directory does not exist create it
  if(!is_dir($dest)) { 
    if(!mkdir($dest)) {
      // If the destination directory could not be created stop processing
      return false;
    }    
  }

  // Open the source directory to read in files
  $i = new DirectoryIterator($src);
  foreach($i as $f) {
    if($f->isFile()) {
      rename($f->getRealPath(), "$dest/" . $f->getFilename());
    } else if(!$f->isDot() && $f->isDir()) {
      rmove($f->getRealPath(), "$dest/$f");
      rrmdir($f->getRealPath());
    }
  }
  rrmdir($src);
}


function docopy($url){


  /**
 * Transfer Files Server to Server using PHP Copy
 * @link https://shellcreeper.com/?p=1249
 */

  /* Source File URL */
  $remote_file_url = $url;

  /* New file name and path for this file */
  $local_file = "/opt/lumi/H5P-Nodejs-library/packages/h5p-rest-example-server/h5p/content/tmp/".basename($url);

  $tmp_folder = "/opt/lumi/H5P-Nodejs-library/packages/h5p-rest-example-server/h5p/content/tmp";

  if (!file_exists($tmp_folder)) {
    mkdir($tmp_folder);
  }

  /* Copy the file from source url to server */
  $copy = copy( $remote_file_url, $local_file );

  /* Add notice for success/failure */
  if( !$copy ) {
    error_log("Doh! failed to copy $url...\n");
    $response = array("success"=>0,"error_msg"=>"failed to copy","error_code" => 2);
    exit_and_send_data_as_json($response);
  }
  else{
    error_log("WOOT! success to copy $url...\n");


    $file = $local_file;
    $time = time();
    $path = "/opt/lumi/H5P-Nodejs-library/packages/h5p-rest-example-server/h5p/content/tmp/".$time;

    if (!file_exists($path)) {
      mkdir($path, 0777, true);
    }

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if ($res === TRUE) {
      $zip->extractTo( $path );
      $zip->close();
      error_log("WOOT! $file extracted to $path");

      //recursiveCopy($path."/content/", $path);
      //rrmdir($path."/content");
      //rmove($path,"/opt/lumi/H5P-Nodejs-library/packages/h5p-rest-example-server/h5p/content/".$time);

      rename($path."/h5p.json", $path."/content/h5p.json");


      $final_path = "/opt/lumi/H5P-Nodejs-library/packages/h5p-rest-example-server/h5p/content/".$time;
      /*if (!file_exists($final_path)) {
          mkdir($final_path, 0777, true);
        }*/
      recursiveCopy($path."/content/", $final_path);
      rrmdir($path."/content");
      $libraries_path = "/opt/lumi/H5P-Nodejs-library/packages/h5p-rest-example-server/h5p/libraries";
      recursiveCopy($path, $libraries_path);
      rrmdir($path);
      $filename = $time.".html";
      //downloadFile("https://dev-lumi.numericlasse.fr/h5p/html/".$time, $filename);
      $abc = file_get_contents(get_current_http_scheme() ."://" . $_SERVER["HTTP_HOST"] . "/h5p/html/".$time);
      $response = array("success"=>1,"data"=>$abc);
      exit_and_send_data_as_json($response);
    }
    else {


      error_log("Doh! I couldn't open $file");
      $response = array("success"=>0,"error_msg"=>"failed to open h5p file","error_code" => 3);
      exit_and_send_data_as_json($response);
    }
  }
}

?>
