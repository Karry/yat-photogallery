<?php
/**
 * YAT (yet another tiled) Photogallery
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
?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />    

    <title>YAT (Yet Another Tiled) Photogallery</title>

    <link href="<?php echo $gallery->relativeRoot() ?>/css/main.css"  rel="StyleSheet" type="text/css" media="Screen" title="Default" />            
    <link href="<?php echo $gallery->relativeRoot() ?>/css/photogallery.css"  rel="StyleSheet" type="text/css" media="Screen" title="Default" />            

    <script type="text/javascript" src="<?php echo $gallery->relativeRoot() ?>/js/jquery-1.10.2.min.js" ></script>
    <script type="text/javascript" src="<?php echo $gallery->relativeRoot() ?>/js/photogallery.js" ></script>
    
</head>
<body>  
<div id="page-wrapper">
  
  <div style="padding: 30px; text-align: justify;">
    <h1>YAT (Yet Another Tiled) Photogallery</h1>

    <p>
      I was looking for some simple photogallery for my website, but don't found 
      any sufficient that supports albums, nice thumbnails displaying and has powerfull viewer. 
      So I decide to write my own photogallery library. YAT is a result. It uses <strong>php</strong> 
      on server side and <strong>jQuery</strong> on client. <strong>No database</strong> is needed, 
      photo name, description and other properties are loaded directly from <strong>XMP</strong> and <strong>exif</strong> metatadata
      and displayed in viewer! Gallery creates thumbnails from photos automaticly and cache it on the server. 
      If photo is rotated (detected from exif), thumbnails are rotated automaticly. 
    </p>
    
    <p>
      You can get source code <a href="https://github.com/Karry/yat-photogallery">from Github</a>, 
      it is distributed under terms of GNU <strong>LGPL license</strong>.
    </p>
    
    <pre>https://github.com/Karry/yat-photogallery.git</pre>
    
  </div>
  
  <div style="position: relative; margin-top: 60px; min-height: 400px;">
    <h2 style=" width: 390px; height: 120px; opacity: 0.2; position: absolute; font-size: 120px; left: 0px; top:0px; z-index: -1; margin: 0px; padding: 0px; transform: translate(-130px,+120px) rotate(270deg) ;">Demo</h2>

    <div style="margin-left: 130px;">
      
    <?php $albumIndex = 0;
    foreach ($gallery->albums() as $album){ ?>
        <div class="folder">
            <h2><a href="<?php echo $album->url() ?>" ><?php echo $album->name() ?></a></h2>
            <p class="comment"><?php echo $album->description() ?></p>
            
            <div class="thumbnailLineWrapper clickable" onclick="window.location = '<?php echo $album->url() ?>';">
              <div class="photos thumbnailLine" id="album<?php echo $albumIndex ?>" onclick="window.location = '<?php echo $album->url() ?>';">
                <script type="text/javascript">
                  var gallery = new Photogallery("album<?php echo $albumIndex ?>", Photogallery.STYLE_LINE, 
                                                 "<?php echo $gallery->relativeRoot()?>", "s");

                  <?php
                  $photos = $album->photos();
                  for ($i = 0; $i < sizeof($photos) && $i < 11; $i++){
                    $photo = $photos[$i];
                    ?>
                    gallery.addPhoto("<?php echo $photo->url() ?>", 
                        "<?php echo htmlspecialchars(trim($photo->name()))?>", 
                        "<?php htmlspecialchars(trim($photo->description()))?>", 
                        <?php echo $photo->jsonSizes()?>, 
                        {}); 
                    <?php /*<img src="{$photo->url()}" 
                           alt="{$photo->name()|escape}" 
                           width="200" 
                           height="150" />
                           */ ?>
                  <?php } ?>
                </script>
              </div>
              <div class="gradient"></div>
            </div>
            <div class="summary"><?php echo sizeof($photos) ?> photos in total</div>
        </div>

      <?php 
      $albumIndex ++ ; 
    } ?>
    </div>

  </div>
  
  <div style="padding: 30px; text-align: justify;">
    <h3>Installation (Apache web server)</h3>
    
    <p>
      You need <strong><a href="http://php.net/manual/en/book.image.php">php5-gd</a></strong> and 
      <strong><a href="http://httpd.apache.org/docs/current/mod/mod_rewrite.html">mod_rewrite</a></strong> extensions 
      enabled and configured on your server.
      For installation, just upload photogallery files to some directory on your web server and create <strong>cache</strong>
      subdirectory with write permissions for apache user. Repository includes 
      <strong>.htaccess</strong> configuration, so everything should work out of the box.
    </p>
    
    <pre>RewriteEngine on

# Is the request for a non-existent file?
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# If so, use next RewriteRule
RewriteRule ^([a-zA-Z0-9\-\/\_\.()!]*)$	index.php [L]</pre>
    
    <p>
      For creating new photo album, create a new sub-directory in <code>photos</code> and upload <strong>jpeg</strong> 
      images into it. Title and description are read from <strong>XMP</strong> metadata stored in photo. 
      For editing these field can be used some photo editor/manager - for example <a href="https://www.digikam.org/">DigiKam</a>.
      Album is named by its subdirectory. But you can override this default value by uploading some <code>album.xml</code> file into album dir.
    </p>
    
    <pre><b>&lt;?xml</b> version=&quot;1.0&quot; encoding=&quot;utf-8&quot;<b>?&gt;</b>
<b>&lt;album</b><b>&gt;</b>
  <b>&lt;name</b><b>&gt;</b>Some Album name<b>&lt;/name</b><b>&gt;</b>
  <b>&lt;datetime</b><b>&gt;</b>2008-03-15<b>&lt;/datetime</b><b>&gt;</b>
  <b>&lt;description</b><b>&gt;</b>
    <b>&lt;strong&gt;</b>Html<b>&lt;/strong&gt;</b> formated description of album.
  <b>&lt;/description</b><b>&gt;</b>
<b>&lt;/album</b><b>&gt;</b></pre>

  </div>
  
</div>
<?php if (file_exists("templates/visitcounter.php")){ require_once 'templates/visitcounter.php'; } ?>
</body>
</html>
