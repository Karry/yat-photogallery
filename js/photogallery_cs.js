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

Photogallery_l10n.prototype.formatNumber = function(number){
  if (typeof number === 'undefined')
    return number;
  return (number + "").replace(".", ",");
};

Photogallery_i18n.prototype.DATE_AND_TIME = "Datum a čas";
Photogallery_i18n.prototype.CAMERA = "Fotoaparát";
Photogallery_i18n.prototype.EXPOSURE_TIME = "Expozice";
Photogallery_i18n.prototype.DIAPHRAGM = "Clona";
Photogallery_i18n.prototype.FLASH = "Blesk";
Photogallery_i18n.prototype.FLASH_YES = "Ano";
Photogallery_i18n.prototype.FLASH_NO = "Ne";
Photogallery_i18n.prototype.ALTITUDE = "Výška";
Photogallery_i18n.prototype.METRES_ABOVE_SEA_LEVEL = "m n. m."; // http://cs.wikipedia.org/wiki/Nadmořská_výška
Photogallery_i18n.prototype.FOCAL_LENGTH = "Ohnisko";
Photogallery_i18n.prototype.FOCAL_IN_35MM_FILM = "přepočet na kino film:";
