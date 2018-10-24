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

////////////////////////////////////////////////////////////////////////////////
// Load libs
////////////////////////////////////////////////////////////////////////////////

function loadOrDie($extension){
  if (!extension_loaded($extension)){
    if (!(function_exists("dl") && dl($extension))){
      header("Content-Type: text/plain; charset=UTF-8;");
      echo "Can't load php extension ".$extension."\n";
      die();
    }
  }
}
loadOrDie("gd");
loadOrDie("json");
loadOrDie("xml");

$baseDir = preg_replace('/[^\/]*$/i', "", $_SERVER["SCRIPT_FILENAME"]);
$webRootDir = $_SERVER["DOCUMENT_ROOT"];
$baseUrl = substr($baseDir, strlen($webRootDir));
// remove last "/" if present
$path = preg_replace('/\/$/i', "", array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : "");

require_once $baseDir . DIRECTORY_SEPARATOR . "photogallery.class.php";

$gallery = new Photogallery(
        $baseDir . DIRECTORY_SEPARATOR . "photos" , 
        $baseUrl,
        $baseUrl,
        $webRootDir, 
        $baseDir . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "photoMetadata"
        );

$album = $gallery->getAlbumForUrl($path); 
if ($album == null){
  if ($path == $baseUrl){
    $pageTitle = "YAT Photogallery";
    require 'templates/folders.php';
  }else{
    $pageTitle = "Page Not Found | YAT Photogallery";
    header("HTTP/1.0 404 Not Found");
    require 'templates/404.php';    
  }
}else{
  $pageTitle = $album->name() . " | YAT Photogallery";
  $displayBack = true;
  require 'templates/folder.php';    
}
