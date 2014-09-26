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
?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />    

    <title><?php echo $pageTitle ?></title>

    <link href="<?php echo $gallery->relativeRoot() ?>/css/main.css"  rel="StyleSheet" type="text/css" media="Screen" title="Default" />            
    <link href="<?php echo $gallery->relativeRoot() ?>/css/photogallery.css"  rel="StyleSheet" type="text/css" media="Screen" title="Default" />            
    
    <script type="text/javascript" src="<?php echo $gallery->relativeRoot() ?>/js/jquery-1.10.2.min.js" ></script>
    <script type="text/javascript" src="<?php echo $gallery->relativeRoot() ?>/js/photogallery.js" ></script>
    
    <!--
    <script type="text/javascript" src="<?php echo $gallery->relativeRoot() ?>/js/photogallery_cs.js" ></script>
    -->
</head>
<body>  
<div id="page-wrapper">
  
  <h1><?php echo $album->name() ?></h1>


    <?php if ($album->description() != null){ ?><div><?php echo $album->description() ?></div><?php } ?>

    <?php if ($displayBack){ ?><div style="margin:10px;"><a href="<?php echo $gallery->relativeRoot() ?>">back to list of albums</a></div><?php } ?>


    <div class="photogallery" id="album">
      <!-- content for browsers with disabled javascript -->
       <?php foreach ($album->photos() as $photo){ ?>
        <a href="<?php echo $photo->url()?>"><img src="<?php echo $gallery->relativeRoot() ?>/thumbnail.php?path=<?php echo $photo->url()?>&size=m" alt="<?php echo htmlspecialchars(trim($photo->name()))?>" /></a>
       <?php } ?>

      <!-- clear album content immediately, keep javascript to display it -->
      <script type="text/javascript">
        var myNode = document.getElementById("album");
        while (myNode.firstChild) {
          myNode.removeChild(myNode.firstChild);
        }
      </script>
      
      <!-- javascript photogallery -->
      <script type="text/javascript">

        var gallery = new Photogallery("album", true, "<?php echo $gallery->relativeRoot() ?>", "m", "slider", "photoViewer", "name", "description", "exiftable");
        <?php foreach ($album->photos() as $photo){ ?>
          gallery.addPhoto("<?php echo $photo->url()?>", 
                    "<?php echo htmlspecialchars(trim($photo->name()))?>", 
                    "<?php htmlspecialchars(trim($photo->description()))?>", 
                    <?php echo $photo->jsonSizes()?>, 
                    <?php echo $photo->jsonExif()?>
                    );              
        <?php } ?>

        function previousPhoto(){
          gallery.previousPhoto();
        }

        function nextPhoto(){
          gallery.nextPhoto();
        }

        function hidePresentation(){
          gallery.hidePresentation();
        }

        function fullPhoto(){
          gallery.fullPhoto();
        }
        function hideExif(){
          $("#exif").css("display", "none");
          $("#exifSwitch").css("display", "block");
        }
        function showExif(){
          $("#exif").css("display", "block");
          $("#exifSwitch").css("display", "none");
        }
      </script>
    </div>


<div id="photoViewer">
    <div class="frame" style="position:relative;">
        
        <div style="height: 60px; position:relative; background-color: rgba(0,0,0, 0.5);">
            <div style="position:absolute;top:4px; left:20px;" onclick="previousPhoto();"><img src="<?php echo $gallery->relativeRoot() ?>/img/left_white.png" alt="Previous" width="50" height="40" style="cursor:pointer;.cursor:Hand;" /></div>
            <div style="position:absolute;top:4px; left:100px;" onclick="nextPhoto();"><img src="<?php echo $gallery->relativeRoot() ?>/img/right_white.png" alt="Next" width="50" height="40" style="cursor:pointer;.cursor:Hand;" /></div>          
            
            <div style="position:absolute;top:10px; left:200px;" onclick="fullPhoto();" id="photoViewer-fullPhoto"><img src="<?php echo $gallery->relativeRoot() ?>/img/full_white_en.png" alt="Full resolution" width="100" height="40" style="cursor:pointer;.cursor:Hand;" /></div>
            
            <div style="float:right; margin: 8px;" onclick="hidePresentation();"><img src="<?php echo $gallery->relativeRoot() ?>/img/cross2_white.png" alt="Close photo viewer"  width="50" height="40" style="cursor:pointer;.cursor:Hand;" /></div>

            <div style="float:right; margin: 8px; margin-right: 60px; display: none;" onclick="showExif();" id="exifSwitch"><img src="<?php echo $gallery->relativeRoot() ?>/img/exif_white.png" alt="Show Exif"  width="100" height="40" style="cursor:pointer;.cursor:Hand;" /></div>

            <div style="margin: 6px 310px 0px 310px">
              <div style="min-width: 200px; margin: 0px auto 0px auto; color:white">
                <div><strong id="name"></strong></div>
                <div id="description"></div>
              </div>
            </div>
            
        </div>
              
        <div id="slider" style="margin:0px; position:relative;"></div>

          <div class="exif" style="position: absolute; top: 60px; right: 0px;" id="exif" >
            <div style="text-align:right"><img src="<?php echo $gallery->relativeRoot() ?>/img/cross2_white.png" 
                                               alt="Hide Exif" width="20" height="18" 
                                               style="cursor:pointer;.cursor:Hand;margin:4px"  
                                               onclick="hideExif();" /></div>
            <div id="exiftable" ></div>
          </div> 
        
    </div>
</div>  
  
</div>
</body>
</html>
