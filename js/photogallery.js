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

/**
 * It compute number form fraction string, commonly used in jpeg exif fields 
 * instead decimal number
 * 
 * @param {string} fraction
 * @returns {number}
 */
fractionToNumber = function(fraction){
      var arr = (fraction+"").split("/");
      if (arr.length===2){
        var numerator = parseInt(arr[0]);
        var nominator = parseInt(arr[1]);
        return numerator / nominator;
      }
      return fraction;
};

Rectangle = function(width, height){
  this.width = width; 
  this.height = height;
};

/**
 * Scale specified rectangle with respect to aspect ratio to fit to this "box". 
 * All content of original rectangle is visible, it can create padding around 
 * inner rectangle.
 * 
 * @param {int} width
 * @param {int} height
 * @returns {Rectangle} resized rectangle that is boxed into this rectangle
 */
Rectangle.prototype.box = function(width, height){
  var ratio = Math.max(width / this.width, height / this.height);
  if (ratio <= 1)
    return new Rectangle(width, height);

  return new Rectangle(Math.round(width / ratio), Math.round(height / ratio));
};

/*
 * Scale specified rectangle with respect to aspect ratio to fill this "box". 
 * Some content of inner rectangle can be hidden.
 * 
 * @param {type} width
 * @param {type} height
 * @param {type} padding - needing padding around wrapped rectangle
 * @returns {Rectangle} resized rectangle that is "wrapped" into this rectangle
 */
Rectangle.prototype.wrapp = function(width, height, padding){
  if (typeof padding === 'undefined')
    padding = 0;
  
  var ratio = Math.min(width / (this.width - 2*padding), height / (this.height- 2*padding));

  return new Rectangle(Math.round(width / ratio), Math.round(height / ratio));
};

/*
 * l10n is acronym for localization. This object contains function to convert (SI) values to 
 * country-specific form (GPS coordinations, dates, numbers...)
 * 
 * It should be overriden if you need change form of displayed values.
 */ 
Photogallery_l10n = function(){
  this.i18n = new Photogallery_i18n();
};

/* 
 * http://en.wikipedia.org/wiki/Aspect_ratio_(image)
 * @type Number "standard" image ratio for landscape photos
 */
Photogallery_l10n.prototype.standardRatio = 3/2;

Photogallery_l10n.prototype.formatDateTime = function(time, day, month, year){
  return this.formatDate(day, month, year) + " " + time;
};

Photogallery_l10n.prototype.formatDate = function(day, month, year){
  return day + "." + month + "." + year;
};

Photogallery_l10n.prototype.formatNumber = function(number){
  if (typeof number === 'undefined')
    return number;
  return (number + "");
};

Photogallery_l10n.prototype.formatGps = function(coordinates, reference){  
  var degrees = fractionToNumber(coordinates['0']);
  var minutes = fractionToNumber(coordinates['1']);
  var seconds = Math.round(fractionToNumber(coordinates['2']) * 100) / 100;
  return degrees + "°" + this.formatNumber( minutes ) + "'" + (seconds > 0 ? this.formatNumber(seconds) + "\"" : "") + reference;
};

Photogallery_l10n.prototype.formatAltitude = function(altitude){  
  return this.formatNumber( fractionToNumber(altitude) ) + " " +this.i18n.METRES_ABOVE_SEA_LEVEL;
};

Photogallery_l10n.prototype.formatFocalLength = function(focalLength, focalLengthIn35mmFilm){
  var focalLengthStr  = "";
  if (typeof focalLength !== 'undefined'){
    focalLengthStr = this.formatNumber(fractionToNumber(focalLength)) + " mm";  
  }
  if (typeof focalLengthIn35mmFilm !== 'undefined'){
    if (focalLengthStr !== "")
      focalLengthStr = focalLengthStr + ", <br />";
    focalLengthStr = focalLengthStr + this.i18n.FOCAL_IN_35MM_FILM + " ";
    focalLengthStr = focalLengthStr + this.formatNumber(fractionToNumber(focalLengthIn35mmFilm));
  }
  return focalLengthStr;
};

Photogallery_l10n.prototype.formatExposureTime = function(exposureTime){
    var arr = exposureTime.split("/");
    if (arr.length===2){
      var numerator = parseInt(arr[0]);
      var nominator = parseInt(arr[1]);
      if (nominator === 1)
        return this.formatNumber(numerator) +" s";

      if ((numerator / nominator) > 1)
        return this.formatNumber( numerator / nominator ) + " s" ;

      if (numerator > 1){
        nominator = nominator / numerator;
        numerator = 1;
      }
      
      return this.formatNumber(numerator) + "/" + this.formatNumber(nominator) + " s";
    }else{
      return this.formatNumber(exposureTime) + " s";
    }
};

/*
 * i18n is acronym for internationalization. This object contains constants with translations.
 * 
 * It should be overriden if you need translate texts.
 */
Photogallery_i18n = function(){};

Photogallery_i18n.prototype.DATE_AND_TIME = "Date and Time";
Photogallery_i18n.prototype.CAMERA = "Camera";
Photogallery_i18n.prototype.EXPOSURE_TIME = "Exposure time";
Photogallery_i18n.prototype.DIAPHRAGM = "Diaphragm";
Photogallery_i18n.prototype.FLASH = "Flash";
Photogallery_i18n.prototype.FLASH_YES = "Fired";
Photogallery_i18n.prototype.FLASH_NO = "No";
Photogallery_i18n.prototype.ALTITUDE = "Altitude";
Photogallery_i18n.prototype.METRES_ABOVE_SEA_LEVEL = "m.a.s.l."; // http://en.wikipedia.org/wiki/Metres_above_sea_level
Photogallery_i18n.prototype.FOCAL_LENGTH = "Focal Length";
Photogallery_i18n.prototype.FOCAL_IN_35MM_FILM = "35 mm film equivalent:";



Photogallery = function(elemId, style, relativeRoot, size, slider, sliderWrapper, name, 
  description, exif, animationSpeed, thumbnailWidth){
  
  if (typeof style === 'undefined')
    style = Photogallery.STYLE_NONE;
  if (typeof size === 'undefined')
    size = "m";
  if (typeof animationSpeed === 'undefined')
    animationSpeed = 200;
  if (typeof thumbnailWidth === 'undefined')
    thumbnailWidth = 300;
  if (typeof relativeRoot === 'undefined')
    relativeRoot = "";   
  
  this.l10n = new Photogallery_l10n();
  this.i18n = new Photogallery_i18n();

  this.padding = 2;
  
  this.size = size;
  this.photos = [];
  
  this.currentPhoto = null;
  this.currentAnimation = null;
  this.thumbnailWidth = thumbnailWidth;
  this.animationSpeed = animationSpeed;
  this.animationSteps = 10;
  this.relativeRoot = relativeRoot;
    
  var inst = this;
  $(document).ready(function(){
    inst.thumbnailsWrapper = $("#"+ elemId);
    if (typeof sliderWrapper !== 'undefined'){
      inst.sliderWrapper = $("#" + sliderWrapper); 
      inst.slider = $("#" + slider ); 
      inst.name = $("#" + name);
      inst.description = $("#" + description);
      inst.exif = $("#" + exif);
    }
    if (style == Photogallery.STYLE_TILING)
      inst.tilingRender(inst.thumbnailsWrapper);
    else if (style == Photogallery.STYLE_LINE)
      inst.renderLine(inst.thumbnailsWrapper);
    else
      inst.render(inst.thumbnailsWrapper);
    
    $(window).resize(function() {
      inst.windowResize();
    });
  });
};

Photogallery.STYLE_TILING = 'TILING';
Photogallery.STYLE_LINE = 'LINE';
Photogallery.STYLE_NONE = 'NONE';

Photogallery.prototype.addPhoto = function(url, name, description, sizes, metadata){
  this.photos.push({url: url, name: name, description: description, sizes: sizes, metadata: metadata});
};

Photogallery.prototype.sizeArgs = function(photo, size){
    var sizeArgs = ""; 
    if (typeof photo.sizes[size] !== 'undefined'){
      var sizeObj = photo.sizes[size];
      if ((typeof sizeObj['width'] === 'number') && (typeof sizeObj['height'] === 'number'))
        sizeArgs = "width=\""+sizeObj['width']+"\" height=\""+sizeObj['height']+"\"";
    }
    return sizeArgs;
};

Photogallery.prototype.showPresentation = function(photoIndex){
  if (typeof this.sliderWrapper !== 'undefined'){
    
    this.sliderWrapper
      .css("display", "block")
      .css("height", $( window ).height());
    
    this.slider
      .css("height", $( window ).height() - 60);
    
    if (this.currentPhoto !== null){
        this.currentPhoto.img
            .css("opacity",0)      
            .css("filter", "alpha(opacity=0)") /* For IE8 and earlier */
            .css("visibility", "hidden");
    }
    
    this.currentPhoto = this.photos[photoIndex];
    this.loadPhoto(this.currentPhoto);
        
    this.currentPhoto.img
            .css("visibility", "visible")
            .css("opacity",1)      
            .css("filter", "alpha(opacity=100)") /* For IE8 and earlier */
            .css("left", (this.slider.width() - this.currentPhoto.img.width()) / 2);
    
    this.showInfo(this.currentPhoto);
  }
};

Photogallery.prototype.windowResize = function(){
  if ((this.currentPhoto !== null) && (typeof this.sliderWrapper !== 'undefined')){
    
    this.sliderWrapper
      .css("height", $( window ).height());
    
    this.slider
      .css("height", $( window ).height() - 60);
      
    this.loadPhoto(this.currentPhoto, true);
    if (this.currentAnimation === null){
      this.currentPhoto.img
            .css("left", (this.slider.width() - this.currentPhoto.img.width()) / 2);
    }
  }  
};

Photogallery.prototype.loadPhoto = function(photo, preloadNearby){
  if (typeof preloadNearby === 'undefined')
    preloadNearby = true;
  
  if (typeof photo.img === 'undefined'){
      photo.img = $("<img src=\"" + this.relativeRoot + "/thumbnail.php?path=" + photo.url + "&size=xl\" alt=\"" + photo.name + "\"  />");
      photo.img
            .css("position", "absolute")
            .css("opacity",0)      
            .css("filter", "alpha(opacity=0)") /* For IE8 and earlier */
            .css("visibility", "hidden");
    
      this.slider.append(photo.img);
  }
  var sizeObj = photo.sizes["xl"];
  var shrinkedImgSize = (new Rectangle(this.slider.width(), this.slider.height()))
          .box(sizeObj.width, sizeObj.height);
  photo.img
          .width( shrinkedImgSize.width )
          .height(shrinkedImgSize.height )
          .css("top", (this.slider.height() - shrinkedImgSize.height) / 2);
  
  if (preloadNearby === true){
    // pre-load left
    var index = parseInt(photo.index) -1;
    index = index < 0? this.photos.length -1 : (index >= this.photos.length? 0: index);
    this.loadPhoto(this.photos[index], false);

    // preload right
    var index = parseInt(photo.index) +1;
    index = index < 0? this.photos.length -1 : (index >= this.photos.length? 0: index);
    this.loadPhoto(this.photos[index], false);    
  }
};

Photogallery.prototype.render = function(elem){
  for (var i in this.photos){
    var photo = this.photos[i];
    photo.index = i;
    var sizeArgs = this.sizeArgs(photo, this.size);
    var thumbnail = $("<img src=\"" + this.relativeRoot + "/thumbnail.php?path=" + photo.url + "&size="+this.size+"\" alt=\"" + photo.name + "\" "+sizeArgs+" />");
    elem.append(thumbnail);
  }
};

Photogallery.prototype.renderLine = function(elem){
  // elem have to setup fixed height
  var height = elem.height();
  for (var i in this.photos){
    var photo = this.photos[i];
    photo.index = i;
    var size = this.size;
    var sizeArgs = "height=" + height;
    for (var si in photo.sizes){
      var sizeObj = photo.sizes[si];
      if (sizeObj.height >= (0.8 * height)){
        size = si;
        var scale = height / sizeObj.height;
        sizeArgs = "height=\"" + Math.round(height) + "\" width=\"" + Math.round(scale * sizeObj.width) + "\"";
        break;
      }
    }
    //var sizeArgs = this.sizeArgs(photo, this.size);

    var thumbnail = $("<img src=\"" + this.relativeRoot + "/thumbnail.php?path=" + photo.url + "&size="+si+"\" alt=\"" + photo.name + "\" "+sizeArgs+" />");
    elem.append(thumbnail);
  }
};

Photogallery.prototype.occupyCells = function(usedCells, column, line, horizCells, vertCells, columns){
  // test if requiered space is empty
  for (var l = line; l< line + vertCells; l ++){
    for (var c= column; c< column + horizCells; c++){
      if (c >= columns)
        return false;
      
      if (typeof usedCells[l] !== 'undefined'){
        if ((typeof usedCells[l][c] !== 'undefined') && usedCells[l][c] === true){
          return false;
        }
      }
    }
  }
  // it is empty, mark this space as occupied
  for (var l = line; l< line + vertCells; l ++){
    for (var c= column; c< column + horizCells; c++){
      if (typeof usedCells[l] === 'undefined'){
        usedCells[l] = [];
      }
      usedCells[l][c] = true;
    }
  }
  return true;
};

Photogallery.prototype.tilingRender = function(elem){
  // clear content for browsers with disabled javascript
  elem.html("");
  
  // thumbnails area is splited to virtual table with cell dimensions for "standard" lanscape photo
  // this method tries to pave whole area by photos
  var columns = Math.max(1, Math.floor(elem.width() / this.thumbnailWidth));
  var width = Math.floor(elem.width() / columns);
  var cell = new Rectangle(width, Math.floor(width / this.l10n.standardRatio)); 
  var usedCells = [];
  
  for (var photoIndex in this.photos){
    var photo = this.photos[photoIndex];
    photo.index = photoIndex;
    
    // compute how many cells photo needs
    var horizCells = 1;
    var vertCells = 1;
    if (typeof photo.sizes[this.size] !== 'undefined'){
      var sizeObj = photo.sizes[this.size];
      var ratio = sizeObj.width / sizeObj.height;
      
      // for landscape panorama      
      for (var i = horizCells; i < columns; i++){
        if (ratio > this.l10n.standardRatio *(i + 0.5))
          horizCells = i + 1;
      }      
      // for portraits or vertical panorama
      for (var i = vertCells; i < 2; i++){
        if (ratio < this.l10n.standardRatio / (i + 0.5))
          vertCells = i + 1;
      }      
    }
    // now, we know how big should photo be... try find some free cells for it
    var left = 0;
    var top = 0;
    var placed = false;
    for (var line = 0; (!placed) && line <= 10000000; line++){
      for (var column = 0; (!placed) && column < columns; column++){
        if (this.occupyCells(usedCells, column, line, horizCells, vertCells, columns)){
          top = line;
          left = column;
          placed = true;
        }
      }
    }
    var div = $("<div class=\"photo\"></div>");
    div.css("top", (top * cell.height)+ "px")
            .css("left", (left * cell.width)+ "px")
            .css("width", (horizCells * cell.width - 2*this.padding)+ "px")
            .css("height", (vertCells * cell.height - 2*this.padding)+ "px");
    elem.append(div);
    
    // now we need optimal size of thumbnail
    var thumbSize = this.size;
    var sizeArgs = "";
    var wrapper = new Rectangle(cell.width * horizCells - 2*this.padding, cell.height * vertCells - 2*this.padding);
    var thumbSizeObj = wrapper;
    if (typeof photo.sizes !== 'undefined'){
      // setup default size of thumbnail
      if (typeof photo.sizes[thumbSize] !== 'undefined')
        thumbSizeObj = photo.sizes[thumbSize];
      
      for (var size in photo.sizes){
        var sizeObj = photo.sizes[size];
        // if this size is not big too much and it is bigger than current size
        if ( (sizeObj.width < (wrapper.width *3)) && (sizeObj.height < (wrapper.height *3))
                && (sizeObj.width > thumbSizeObj.width) && (sizeObj.height > thumbSizeObj.height) ){
          
          thumbSizeObj = sizeObj;
          thumbSize = size;
          // if we found size that is bigger than wrapper, break from loop
          if ((sizeObj.width > wrapper.width) && (sizeObj.height > wrapper.height)){
            break;
          }
        }
      }
      // wrapp thumbnail...
      var thumbSizeObj = wrapper.wrapp(thumbSizeObj.width, thumbSizeObj.height);
      sizeArgs = "width=\"" + thumbSizeObj.width + "\" height=\""+ thumbSizeObj.height +"\"";
    }
    var thumbnail = $("<img src=\"" + this.relativeRoot + "/thumbnail.php?path=" + photo.url + "&size="+thumbSize+"\" alt=\"" + photo.name + "\" "+sizeArgs+" />");
    thumbnail.css("position", "relative")
            .css("left", ((thumbSizeObj.width - wrapper.width) / -2)  + "px" )
            .css("top", ((thumbSizeObj.height - wrapper.height) / -2)+ "px");
    div.append(thumbnail);
    
    // title for thumbnail
    var titleDiv = $("<div class=\"thumbnailTitle\"><div>" + photo.name + "</div></div>");
      titleDiv.css("position", "absolute")
            .css("left", 0 + "px")
            .css("top", 0 + "px")
            .css("opacity",0)      
            .css("filter", "alpha(opacity=0)"); /* For IE8 and earlier */;
    
    div.append(titleDiv);
    photo.titleDiv = titleDiv;
    
    // description for thumbnail
    var descriptionDiv = $("<div class=\"thumbnailDescription\"><div>" + photo.description + "</div></div>");
    descriptionDiv.css("position", "absolute")
            .css("left", 0 + "px")
            .css("bottom", 0 + "px")
            .css("opacity",0)      
            .css("filter", "alpha(opacity=0)"); /* For IE8 and earlier */;
    if (photo.description === "")
      descriptionDiv.css("display", "none");

    div.append(descriptionDiv);
    photo.descriptionDiv = descriptionDiv;
    
    if (typeof this.slider !== 'undefined'){      
      var inst = this;
      var clickCallback = function(){
        inst.showPresentation(arguments.callee.index);
      };
      var hoverInCallback = function(){
        inst.thumbnailHoverIn(arguments.callee.index);
      };
      var hoverOutCallback = function(){
        inst.thumbnailHoverOut(arguments.callee.index);        
      };
      clickCallback.index    = photoIndex; 
      hoverInCallback.index  = photoIndex; 
      hoverOutCallback.index = photoIndex; 
      div.click(clickCallback)
              .hover(hoverInCallback, hoverOutCallback);      
    }
  }

  if (typeof this.slider !== 'undefined'){
    var inst = this;
    this.slider.click(function(e){
      inst.switchPhoto(e.clientX > (inst.slider.width() / 2) ? 1: -1);
    });    
  }
  
  elem.css("height", usedCells.length * cell.height);
};

Photogallery.prototype.thumbnailHoverIn = function(photoIndex){
  var photo = this.photos[photoIndex];
  if ((typeof photo === 'undefined')
          || (typeof photo.titleDiv === 'undefined')
          || (typeof photo.descriptionDiv === 'undefined'))
    return;
  
  if ((typeof photo.hoverOutAnimation !== 'undefined') && photo.hoverOutAnimation !== null){
    photo.hoverOutAnimation.stop();
    photo.hoverOutAnimation = null;
  }
  
  photo.titleDiv.css("opacity",1)      
      .css("filter", "alpha(opacity=100)"); /* For IE8 and earlier */
  photo.descriptionDiv.css("opacity",1)      
      .css("filter", "alpha(opacity=100)"); /* For IE8 and earlier */

};

Photogallery.prototype.thumbnailHoverOut = function(photoIndex){
  var photo = this.photos[photoIndex];
  if ((typeof photo === 'undefined')
          || (typeof photo.titleDiv=== 'undefined')
          || (typeof photo.descriptionDiv=== 'undefined'))
    return;
  
  var callback = {};
  var inst = this;
  callback.step = function(step){
    photo.titleDiv
            .css("opacity",1 - (step/inst.animationSteps))      
            .css("filter", "alpha(opacity="+(100 - 100*(step/inst.animationSteps))+")"); /* For IE8 and earlier */
    photo.descriptionDiv
            .css("opacity",1 - (step/inst.animationSteps))      
            .css("filter", "alpha(opacity="+(100 - 100*(step/inst.animationSteps))+")"); /* For IE8 and earlier */    
  };
  
  photo.hoverOutAnimation = new Animation(this.animationSpeed, this.animationSteps);
  photo.hoverOutAnimation.addCallback(callback);
  photo.hoverOutAnimation.start();

};

Photogallery.prototype.previousPhoto = function(){
  this.switchPhoto(-1);
};

Photogallery.prototype.nextPhoto = function(){
  this.switchPhoto(1);
};

Photogallery.prototype.switchPhoto= function(direction){
  if (this.currentAnimation !== null || this.photos.length === 1)
    return;
  var inst = this;
  var newIndex = parseInt(this.currentPhoto.index) + direction;
  if (newIndex > this.photos.length -1)
    newIndex = 0;
  if (newIndex < 0)
    newIndex = this.photos.length -1;
  var newPhoto = this.photos[newIndex];
  this.loadPhoto(newPhoto);
  
  var callback = {};
  callback.end = function(){
    inst.currentPhoto.img
            .css("opacity",0)      
            .css("filter", "alpha(opacity=0)") /* For IE8 and earlier */
            .css("visibility", "hidden");
    inst.currentPhoto = newPhoto;
    inst.currentPhoto.img
            .css("visibility", "visible")
            .css("opacity",1)      
            .css("filter", "alpha(opacity=100)") /* For IE8 and earlier */
            .css("left", (inst.slider.width() - inst.currentPhoto.img.width()) / 2);

    inst.currentAnimation = null;

    inst.showInfo(inst.currentPhoto);
  };
  
  var distance = inst.currentPhoto.img.width()/2 + newPhoto.img.width()/2;
  callback.step = function(step){
    var move = direction * (distance * (step/inst.animationSteps));
    inst.currentPhoto.img
            .css("visibility", "visible")
            .css("opacity",1 - (step/inst.animationSteps))      
            .css("filter", "alpha(opacity="+(100 - 100*(step/inst.animationSteps))+")") /* For IE8 and earlier */
            .css("left", ((inst.slider.width() - inst.currentPhoto.img.width()) / 2) - move)
            ;
    newPhoto.img
            .css("visibility", "visible")
            .css("opacity", (step/inst.animationSteps))      
            .css("filter", "alpha(opacity="+(100*(step/inst.animationSteps))+")") /* For IE8 and earlier */
            .css("left", ((inst.slider.width() - inst.currentPhoto.img.width()) / 2) + (direction>0 ? inst.currentPhoto.img.width() : newPhoto.img.width() * -1) - move)
            ;
  };
  
  this.currentAnimation = new Animation(this.animationSpeed, this.animationSteps);
  this.currentAnimation.addCallback(callback);
  this.currentAnimation.start();
};

Photogallery.prototype.exifField = function(field, name, append){
  return (typeof field !== 'undefined') ? this.exifField2(name, field, append): "";
};
Photogallery.prototype.exifField2 = function( name, value, append){
  return "<tr><th class=\"fright\">" + name + ": </td><td>" + value + (typeof append !== 'undefined'? append: "")+"</td></tr>";
};

Photogallery.prototype.fractionToNumber = fractionToNumber;

Photogallery.prototype.gpsMapLink = function(latCoordinates, latReference, lonCoordinates, lonReference){  
  var degrees = this.fractionToNumber(latCoordinates['0']);
  var minutes = this.fractionToNumber(latCoordinates['1']);
  var seconds = this.fractionToNumber(latCoordinates['2']);
  var lat = degrees + (minutes + seconds / 60) / 60;
  if (latReference === 'S')
    lat = lat * -1;
  
  var degrees = this.fractionToNumber(lonCoordinates['0']);
  var minutes = this.fractionToNumber(lonCoordinates['1']);
  var seconds = this.fractionToNumber(lonCoordinates['2']);
  var lon = degrees + (minutes + seconds / 60) / 60;
  if (lonReference === 'W')
    lon = lon * -1;
  
  // http://www.openstreetmap.org/?mlat=50.0856&mlon=14.4841#map=12/50.0857/14.4841&layers=Q
  return "http://www.openstreetmap.org/?mlat=" + lat + "&mlon=" + lon + "#map=12/" + lat + "/" + lon + "&layers=Q";
};
  
Photogallery.prototype.showInfo = function(photo){
  this.name.html(photo.name);
  this.description.html(photo.description);

  if (typeof photo.metadata !== 'undefined'){
    var table = "<table>";
    if (typeof photo.metadata.DateTime !== 'undefined'){
      var arr = photo.metadata.DateTime.split(" ");
      if (arr.length===2){
        var date = arr[0];
        var time = arr[1];
        arr = date.split(":");
        if (arr.length===3){
          var year =  parseInt(arr[0], 10);
          var month = parseInt(arr[1], 10);
          var day =   parseInt(arr[2], 10);
          table = table + this.exifField2( this.i18n.DATE_AND_TIME , this.l10n.formatDateTime(time, day, month, year) );
        }
      }
    }
    if (typeof photo.metadata.Model!== 'undefined'){
      var make = "";
      // small hack for my Nikon cameras that contains word "NIKON" in Model field...
      if (photo.metadata.Model.search(/^NIKON .*/i) === -1 && (typeof photo.metadata.Make !== 'undefined')){
        make = photo.metadata.Make +" ";
      }
      table = table + this.exifField2(this.i18n.CAMERA , make + photo.metadata.Model  );
    }
    table = table + this.exifField( photo.metadata.ISOSpeedRatings, "ISO" );
    if (typeof photo.metadata.ExposureTime !== 'undefined'){    
      table = table + this.exifField2( this.i18n.EXPOSURE_TIME, this.l10n.formatExposureTime(photo.metadata.ExposureTime));
    }
    if (typeof photo.metadata.COMPUTED !== 'undefined')
      table = table + this.exifField( this.l10n.formatNumber(photo.metadata.COMPUTED.ApertureFNumber), this.i18n.DIAPHRAGM);
    
    if (typeof photo.metadata.Flash !== 'undefined'){
      /*
       * "... This field (Flash) can have several different values and is made 
       * up of a set of flags where different bits in the number indicate 
       * the status of the flash. Bit 0 indicates the flash firing status 
       * (1 means fired), bits 1 and 2 indicate if there was any strobe return 
       * light detected, bits 3 and 4 indicate the flash mode, bit 5 indicates 
       * whether the flash function is present, and bit 6 indicates 
       * “red eye” mode. 16 in binary form is 001000 which means flash didn’t 
       * fire + strobe return detection not available + flash suppressed. 
       * Or in other words, the flash didn’t fire and couldn’t anyway since 
       * it was closed/switched off."
       * 
       * http://www.maketecheasier.com/managing-exif-data-from-command-line/
       */
      table = table + this.exifField( parseInt(photo.metadata.Flash) & 1 === 1? this.i18n.FLASH_YES: this.i18n.FLASH_NO, this.i18n.FLASH);
    }
    
    
    if ((typeof photo.metadata.GPSLatitudeRef !== 'undefined') 
            && (typeof photo.metadata.GPSLatitude !== 'undefined')
            && (typeof photo.metadata.GPSLongitudeRef !== 'undefined')
            && (typeof photo.metadata.GPSLongitude !== 'undefined')
            ){
      var lat = photo.metadata.GPSLatitude; 
      var latRef = photo.metadata.GPSLatitudeRef;
      var lon = photo.metadata.GPSLongitude;
      var lonRef = photo.metadata.GPSLongitudeRef;
      
      table = table + this.exifField2( "GPS", "<a href=" + this.gpsMapLink(lat, latRef, lon, lonRef) + ">" 
              + this.l10n.formatGps(lat, latRef) + ", " + this.l10n.formatGps(lon, lonRef) + "</a>");
      // "GPSLatitudeRef":"N","GPSLatitude":{"0":"37/1","1":"49085678/1000000","2":"0/1"},"GPSLongitudeRef":"W","GPSLongitude":{"0":"122/1","1":"28682237/1000000","2":"0/1"},
    }
    if (typeof photo.metadata.GPSAltitude !== 'undefined'){
      //GPSAltitude "615625/1250"
      table = table + this.exifField2(this.i18n.ALTITUDE, this.l10n.formatAltitude(photo.metadata.GPSAltitude) );      
    }    
    if ((typeof photo.metadata.FocalLength !== 'undefined') || (typeof photo.metadata.FocalLengthIn35mmFilm !== 'undefined')){
      var focalLength = this.l10n.formatFocalLength(photo.metadata.FocalLength, photo.metadata.FocalLengthIn35mmFilm);

      table = table + this.exifField2( this.i18n.FOCAL_LENGTH,focalLength);
    }
    
    //table = table + this.exifField( photo.metadata.FocalLengthIn35mmFilm, "Ohnisko", " (přepočet na 35mm)");
    
    table = table + "</table>";
    this.exif.html(table);
  }
};
  
Photogallery.prototype.hidePresentation = function(){
  this.sliderWrapper
        .css("display", "none");
};

Photogallery.prototype.fullPhoto = function(){
  if (this.currentPhoto !== null)
    window.open(this.currentPhoto.url, this.currentPhoto.name);
};


Animation = function(duration, steps){
  this.duration = duration;
  this.steps = steps;
  this.running = false;
  this.callbacks = [];
};

Animation.prototype.addCallback = function(callback){
  this.callbacks.push(callback);
};

Animation.prototype.start = function(){
  this.running = true;
  this.step(0);
};

Animation.prototype.stop = function(){
  this.running = false;
};

Animation.prototype.step = function(step){
  for (var i in this.callbacks){
    var callback = this.callbacks[i];
    if (typeof callback.step === 'function')
      callback.step(step);
  }
  
  if (step === this.steps){
    this.running = false;
    for (var i in this.callbacks){
      var callback = this.callbacks[i];
      if (typeof callback.end === 'function')
        callback.end();
    }
  }
  
  // schedule next step
  if (this.running){
    var time = Math.max(1,  this.duration / this.steps ); 
    var inst = this;
    setTimeout(function(){
      if (inst.running)
        inst.step(step+1);
    },time);
  }
};
