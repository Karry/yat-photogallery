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

$ZISE_RECTANGLES = array(
    's' => new Rectangle(120, 120), 
    'm' => new Rectangle(300, 300), 
    'l' => new Rectangle(900,900), 
    'xl' => new Rectangle(1920,1200), 
    'full' => new Rectangle(100000, 100000) // I don't expect that some image will be bigger
    );

$XMP_NS = "http://purl.org/dc/elements/1.1/";

function jsonEncode($array, $indent = 0){
  $ascii = function ($str){
     // throw out non-ascii characters
     $strAscii = preg_replace('/[^\x20-\x7f].*/', "", $str);
     return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $strAscii);
  };
  $pairs = array();
  foreach ($array as $key => $value){
    if (is_object($value) || is_array($value)) {             
        $value = jsonEncode($value, $indent + 1);
    } elseif (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    } elseif (is_null($value)) {
        $value = 'null';
    } elseif (is_string($value)) {
        $value = "\"" .  $ascii((string)$value) ."\"";
    }
    $pairs[] = "\"" . $ascii((string)$key) . "\": " . $value;
  }
  
  return "{\n" . join(",\n".str_repeat("\t", $indent + 1), $pairs) . "\n}";
}

class Rectangle{
  var $width; 
  var $height;
  
  function __construct($width, $height) {
    $this->width = $width; 
    $this->height = $height;
  }
  function box($width, $height){
    $ratio = max($width / $this->width, $height / $this->height);
    if ($ratio <= 1)
      return new Rectangle($width, $height);
    
    return new Rectangle(round($width / $ratio), round($height / $ratio));
  }
  function toJson(){
    return "{\"width\": ".$this->width.", \"height\":". $this->height."}";
  }
}

class Photo{
  
  private $album;
  private $file;
  private $jsonSizes = null;
  private $exif = null;
  private $jsonExif = null;
  private $xmpLoaded = false;
  private $name = null;
  private $description = null;
  
  function __construct($album, $file){
    $this->album = $album;
    $this->file = $file;
  }
  
  function url(){
    return substr($this->absolutePath(), strlen($this->album->gallery()->webRootDir()));
  }
  
  function absolutePath(){
    return $this->album->baseDir() . DIRECTORY_SEPARATOR . $this->file;
  }
  
  function ctime(){
    return filectime($this->absolutePath());
  }
  
  function loadXmp(){
    if ($this->xmpLoaded)
      return false;
    
    $this->xmpLoaded = true;
    
    $xmp = null;
    $xmpCache = $this->metadataCachePrefix()."-xmp.xml";
    if (is_file($xmpCache) && filectime($xmpCache) >= $this->ctime()){
      // load xmp from cache
      $xmlDoc = new DOMDocument();
      if ($xmlDoc->load($xmpCache))
        $xmp = $xmlDoc;
    }else{
      // load xmp from original image if it is present
      $content        = file_get_contents($this->absolutePath());
      $xmp_data_start = strpos($content, '<x:xmpmeta');
      $xmp_data_end   = strpos($content, '</x:xmpmeta>');
      if ($xmp_data_start !== false && $xmp_data_end !== false){
        $xmp_length     = $xmp_data_end - $xmp_data_start;
        $xmp_data       = substr($content, $xmp_data_start, $xmp_length + 12);
        //$xmp            = simplexml_load_string($xmp_data);
        $xmlDoc = new DOMDocument();
        if ($xmlDoc->loadXML($xmp_data)){
          $xmp = $xmlDoc;
          $fp = fopen($xmpCache, 'w');
          fwrite($fp, $xmp_data);
          fclose($fp);          
        }
      }
    }
    
    if ($xmp !== null){
      global $XMP_NS;
      
      $titleElem = $xmp->getElementsByTagNameNS($XMP_NS, "title"); 
      $description = $xmp->getElementsByTagNameNS($XMP_NS, "description");
      
      // TODO: read rdf content more sophistic
      if ( $titleElem->length >= 1){
        $this->name = $titleElem->item(0)->nodeValue;
      }
      if ( $description->length >= 1 ) {
        $this->description = $description->item(0)->nodeValue;
      }
      return true;
    }
    
    return false;
  }
    
  
  function name(){
    $this->loadXmp();
    if ($this->name != null)
      return trim(preg_replace('/[\n\r]+/', " ", $this->name));
    return $this->file; // TODO: load name from xml if exists
  }
  
  function description(){
    $this->loadXmp();
    if ($this->description != null)
      return trim(preg_replace('/[\n\r]+/', " ", $this->description));
    return "";
  }
  
  function metadataCachePrefix(){
      $hash = sha1($this->absolutePath());

      $cacheDir = ($this->album->gallery()->metadataCacheDir()).DIRECTORY_SEPARATOR.substr($hash, 0,1);
      if (!is_dir($cacheDir)){
        mkdir($cacheDir, 0777, true);
      }      
      return $cacheDir.DIRECTORY_SEPARATOR.$hash;    
  }
  
  function exif(){
    if ($this->exif == null){
      $this->exif = @exif_read_data($this->absolutePath());
      if ($this->exif === false){
        $this->exif = array(); // loading exif fails...
      }
    }
    return $this->exif;
  }
  
  function jsonExif(){
    if ($this->jsonExif == null){
      $exifCache = $this->metadataCachePrefix()."-exif.json";
      if (is_file($exifCache)  && filectime($exifCache) >= $this->ctime()){
        $this->jsonExif = file_get_contents($exifCache);
      }else{

        $this->jsonExif = jsonEncode($this->exif());        

        $fp = fopen($exifCache, 'w');
        fwrite($fp, $this->jsonExif);
        fclose($fp);
      }
    }
    return $this->jsonExif;
  }
  
  function jsonSizes(){
    if ($this->jsonSizes == null){
      $sizeCache = $this->metadataCachePrefix()."-sizes.json";
      if (is_file($sizeCache)  && filectime($sizeCache) >= $this->ctime()){
        $this->jsonSizes = file_get_contents($sizeCache);
      }else{
        @ $img = ImageCreateFromJPEG( $this->absolutePath() );
        if ($img === false){
          // better bad output than none...
          return '{"s":{"width": 120, "height":90},"m":{"width": 300, "height":225},"l":{"width": 900, "height":675},"xl":{"width": 1600, "height":1200},"full":{"width": 2592, "height":1944}}';
        }
        $fullWidth = ImageSX( $img );
        $fullHeight = ImageSY( $img );
        ImageDestroy($img);

        // rotate image by exif
        $exif = $this->exif();        
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 6: // -90
                case 8: //  90
                    $tmpW = $fullWidth;
                    $fullWidth = $fullHeight;
                    $fullHeight = $tmpW;
                    break;
            }
        }        
        
        $sizes = array();
        global $ZISE_RECTANGLES;
        foreach ($ZISE_RECTANGLES as $size => $rectangle){
          array_push($sizes, "\"".$size."\":".($rectangle->box($fullWidth, $fullHeight)->toJson())); 
        }
        $this->jsonSizes = "{".implode(",", $sizes)."}";
        
        $fp = fopen($sizeCache, 'w');
        fwrite($fp, $this->jsonSizes);
        fclose($fp);
      }
    }
    return $this->jsonSizes;
  }
}

class Album {
  const META_FILE = "album.xml"; 
  
  private $dir;
  private $name = null;
  private $metaLoaded = false;
  private $gallery;
  private $description = null;
  private $timestamp = null;
  private $baseDir;
  
  private $photos = null; 
  
  function __construct($gallery, $dir){
    $this->dir = $dir;
    $this->gallery = $gallery;
    $this->timestamp = time();
    
    // remove last "/" if present
    $this->baseDir = eregi_replace("/$", "", trim($this->gallery->baseDir() . DIRECTORY_SEPARATOR . $this->dir));  
  }
  
  function loadMeta(){
    if ($this->metaLoaded)
      return false;
    
    $this->metaLoaded = true;
    $metaFile = $this->gallery->baseDir() . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . Album::META_FILE;
    if (file_exists($metaFile)){
      $xmlDoc = new DOMDocument();
      $xmlDoc->load($metaFile);
      
      if ($xmlDoc->getElementsByTagName("name")->length >= 1)
        $this->name = $xmlDoc->getElementsByTagName("name")->item (0)->nodeValue;
      if ($xmlDoc->getElementsByTagName("datetime")->length >= 1)
        $this->timestamp = strtotime($xmlDoc->getElementsByTagName("datetime")->item (0)->nodeValue);
      $descriptionElement = $xmlDoc->getElementsByTagName("description");
      if ($descriptionElement->length >= 1)
        $this->description = preg_replace ('/<\/?description>/', "", $xmlDoc->saveHtml($descriptionElement->item(0)) );
              
      return true;
    }
    return false;
  }
  
  function name(){
    $this->loadMeta();
    if ($this->name == null){
      $this->name = $this->dir;
    }
    return $this->name;
  }
  
  function description(){
    $this->loadMeta();
    return $this->description;
  }
  
  function timestamp(){
    $this->loadMeta();
    return $this->timestamp;
  }
  
  function url(){
    return $this->gallery->albumUrlPrefix() . "/" . $this->dir;
  }
  
  function photos(){
    if ($this->photos == null){
      $this->photos = array();
      $files = scandir($this->baseDir, SCANDIR_SORT_ASCENDING);
      foreach ($files as $file){        
        if ( $this->gallery->isValidPhoto($this->baseDir . DIRECTORY_SEPARATOR . $file)) {
          $this->photos[] = new Photo($this, $file);
        }      
      }      
    }
    return $this->photos;
  }
  
  function gallery(){
    return $this->gallery;
  }
  
  function baseDir(){
    return $this->baseDir;
  }
}

class Photogallery {

  public $VALID_PHOTO_EXTENSIONS = array("jpg", "jpeg");
  
  private $albums = array();
  private $baseDir;
  private $baseUrl; 
  private $webRootDir;

  /**
   * 
   * @param String $baseDir directory on the server with albums ("/var/www/domain/gallery")
   * @param String $baseUrlPrefix prefix for url (default "") that will be used for thumbnail.php script
   * @param String $albumUrlPrefix prefix for url (default "") that will be used for building album url
   * @param String $webRootDir document root of site ("/var/www/domain/")
   */
  function __construct($baseDir, $baseUrlPrefix, $albumUrlPrefix, $webRootDir, $metadataCacheDir) {
      $this->baseUrl = $baseUrlPrefix;
      $this->albumUrlPrefix = $albumUrlPrefix;
      $this->baseDir = $baseDir;
      $this->webRootDir = $webRootDir; 
      $this->metadataCacheDir = $metadataCacheDir;
      $dirs = scandir($baseDir, SCANDIR_SORT_DESCENDING);
      foreach ($dirs as $dir){
        if ($dir != '.' && $dir != '..' && is_dir($baseDir . DIRECTORY_SEPARATOR . $dir)) {
          $this->albums[] = new Album($this, $dir);
        }      
      }
  }
  
  function albums(){
    return $this->albums;
  }
  
  function baseDir(){
    return $this->baseDir;
  }
  
  function baseUrl(){
    return $this->baseUrl;
  }
  
  function albumUrlPrefix(){
    return $this->albumUrlPrefix;
  }
  
  function isValidPhoto($file){
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    return in_array($ext, $this->VALID_PHOTO_EXTENSIONS) && is_file($file);
  }
  
  function webRootDir(){
    return $this->webRootDir;
  }
  
  function relativeRoot(){
    return $this->baseUrl;
  }
  
  function metadataCacheDir(){
    return $this->metadataCacheDir;
  }
  
  function getAlbumForUrl($url){
    $url = eregi_replace("\\?[^/]*$", "", $url);
    foreach($this->albums as $album){
      if ($album->url() == $url)
        return $album;
    }
    return null;
  }
  
  function getRootAsAlbum(){
    return new Album($this, "");
  }
}
