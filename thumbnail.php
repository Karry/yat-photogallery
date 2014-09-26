<?php
/**
 * YAT (Yet Another Tiled) Photogallery
 * 
 * Copyright (C) 2014 Lukas Karas <lukas.karas@centrum.cz>
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA 
 */

error_reporting(0);

if ((!array_key_exists('path',$_GET)) || (!array_key_exists('size',$_GET)) ){
  header("HTTP/1.0 400 Bad Request");
  die("Not enough parameters!");
}

$baseDir = eregi_replace("/[^/]*$", "", $_SERVER["SCRIPT_FILENAME"]);
$webRootDir = $_SERVER["DOCUMENT_ROOT"];
$baseUrl = substr($baseDir, strlen($webRootDir));

$cacheDir = $baseDir .DIRECTORY_SEPARATOR. "cache/thumbnails";
require_once $baseDir .DIRECTORY_SEPARATOR. "photogallery.class.php";

define("ONE_HOUR", 3600);

$path = $_GET['path'];
$size = $_GET['size'];

if (!array_key_exists($size,$ZISE_RECTANGLES)){
  header("HTTP/1.0 400 Bad Request");
  die("Bad argument!");
}
//$originalFile = eregi_replace('//', '/', ROOT_DIR.$path);
$originalFile = realpath($webRootDir . DIRECTORY_SEPARATOR . $path);
if (!is_file($originalFile)){
  header("HTTP/1.0 404 Not Found");
  die("File not found!");
}
if (substr($originalFile, 0, strlen($baseDir)) !== $baseDir){
  header("HTTP/1.0 403 Forbidden");
  die("Forbidden!");  
}

$hash = sha1($originalFile);
//echo $originalFile."<br />".$hash;

$subdir = $cacheDir.DIRECTORY_SEPARATOR.$size.DIRECTORY_SEPARATOR.substr($hash, 0,1);
$thumbFile = $subdir.DIRECTORY_SEPARATOR.$hash;
if (is_file($thumbFile) && filectime($thumbFile) >= filectime($originalFile)){
  header("Content-Type: image/jpeg");
  header("Expires: ".GMDate("D, d M Y H:i:s", time() + ONE_HOUR)." GMT");

  //echo "convert http://www.chmi.cz/meteo/rad/data/".gmdate("ymdH",time())."00.gif jpg:-";
  //passthru("convert http://old.chmi.cz/meteo/rad/data/".gmdate("ymdH",time())."00.gif jpg:-");
  readfile($thumbFile);
  exit(0);
}

$rectangle = $ZISE_RECTANGLES[$size];

$img = ImageCreateFromJPEG( $originalFile );

// rotate image by exif
$orientation = 1;
$exif = exif_read_data( $originalFile );
if (!empty($exif['Orientation'])) {
    $orientation = $exif['Orientation'];
    switch ($exif['Orientation']) {
        case 3:
            $img = imagerotate($img, 180, 0);
            break;
        case 6:
            $img = imagerotate($img, -90, 0);
            break;
        case 8:
            $img = imagerotate($img, 90, 0);
            break;
    }
}
$fullWidth = ImageSX( $img );
$fullHeight = ImageSY( $img );

// It is not necessary to resize or save rotated image...
// But it is better store to the cache with original size instead read small image again and again for getting its size.
// Because ImageCreateFromJPEG eats memory :)
/*
if (($orientation == 1) && ($fullWidth <=  $rectangle->width) && ($fullHeight <=  $rectangle->height)) {
  ImageDestroy($img);
  // it is not necssary to resample
  header("Content-Type: image/jpeg");
  header("Expires: ".GMDate("D, d M Y H:i:s", time() + ONE_HOUR)." GMT");
  readfile($originalFile);
  exit(0);  
}
 * 
 */

if (!is_dir($subdir)){
  if (!mkdir($subdir, 0777, true) && (!is_dir($subdir))){
    header("500 Internal Server Error");
    die("Cache directory creation fails.");    
  }
}

$newSize = $rectangle->box($fullWidth, $fullHeight);
if ($newSize->width == $fullWidth && $newSize->height == $fullHeight){
  imagejpeg( $img, $thumbFile);  
}else{
  // create smaller size image
  $resizedImg = ImageCreateTrueColor($newSize->width, $newSize->height);
  imagecopyresampled( $resizedImg, $img, 0 , 0 , 0 , 0, $newSize->width , $newSize->height, $fullWidth, $fullHeight);

  imagejpeg( $resizedImg, $thumbFile);
  ImageDestroy($resizedImg);
}
ImageDestroy($img);

header("Content-Type: image/jpeg");
header("Expires: ".GMDate("D, d M Y H:i:s", time() + ONE_HOUR)." GMT");

readfile($thumbFile);
exit(0);
