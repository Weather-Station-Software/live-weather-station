/*!
 * This control is a modified version (by Pierre Lannoy) of the awesome EnzoJS library from Gerrit Grunwald
 *
 * **********************************************************************************************************
 *   ORIGINAL HEADER ->
 * **********************************************************************************************************
 *
 * Copyright (c) 2015 by Gerrit Grunwald
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at 
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

var lws_lcd = (function() {

  var lcdPanel = function(parameters) {
    var doc = document;
    var param                   = parameters || {};
    var id                      = param.id || 'control';
    var parentId                = param.parentId || 'body';
    var upperCenterText         = param.upperCenterText || '';
    var upperCenterTextVisible  = param.upperCenterTextVisible === undefined ? true : param.upperCenterTextVisible;
    var unit                    = param.unitString || '';
    var unitVisible             = param.unitVisible === undefined ? true : param.unitVisible;
    var lowerRightText          = param.lowerRightText || '';
    var lowerRightTextVisible   = param.lowerRightTextVisible === undefined ? false : param.lowerRightTextVisible;
    var minValue                = param.minValue || 0;
    var maxValue                = param.maxValue || 100;
    var formerValue             = param.formerValue || minValue;
    var value                   = param.value || minValue;
    var decimals                = param.decimals || 1;
    var upperLeftText           = param.upperLeftText || '';
    var upperLeftTextVisible    = param.upperLeftTextVisible === undefined ? false : param.upperLeftTextVisible;
    var upperRightText          = param.upperRightText || '';
    var upperRightTextVisible   = param.upperRightTextVisible === undefined ? false : param.upperRightTextVisible;
    var lowerCenterText         = param.lowerCenterText == undefined ? '' : param.lowerCenterText;
    var lowerCenterTextVisible  = param.lowerCenterTextVisible === undefined ? true : param.lowerCenterTextVisible;
    var formerValueVisible      = param.formerValueVisible === undefined ? false : param.formerValueVisible;
    var battery                 = param.battery || 'full';
    var batteryVisible          = param.batteryVisible === undefined ? true : param.batteryVisible;
    var trend                   = param.trend || '';
    var trendVisible            = param.trendVisible === undefined ? false : param.trendVisible;
    var alarmVisible            = param.alarmVisible === undefined ? false : param.alarmVisible;
    var signalVisible           = param.signalVisible === undefined ? true : clamp(0, 1, param.signalVisible);
    var signalStrength          = param.signalStrength === undefined ? 0 : param.signalStrength;
    var width                   = param.width || 364;
    var height                  = param.height || 140;
    var scalable                = param.scalable || false;
    var design                  = param.design || 'standard';
    var size                    = param.size || 'small';
    var animated                = param.animated === undefined ? true : param.animated;
    var duration                = clamp(0, 10, param.duration) || 0.4;
    var cycleSpeed              = param.cycleSpeed || 2000;
    var refreshSpeed            = param.refreshSpeed || 120000;
    var autoRefresh             = param.autoRefresh === undefined ? true : param.autoRefresh;
    var qDevice                 = param.qDevice || '';
    var qModule                 = param.qModule || '*';
    var qMeasure                = param.qMeasure || '*';
    var postUrl                 = param.qPostUrl || '/wp-admin/admin-ajax.php';

    var foregroundColor = 'rgb(53, 42, 52)';
    var backgroundColor = 'rgba(53, 42, 52, 0.1)';

    var LCD_FONT_NAME = 'digital-7mono';
    var lcdFontHeight = Math.floor(0.5833333333 * height);
    var lcdFont       = lcdFontHeight + 'px ' + LCD_FONT_NAME;

    var STD_FONT_NAME = 'Arial, sans-serif';
    var lcdUnitFont   = (0.26 * height) + 'px ' + LCD_FONT_NAME;
    var lcdTitleFont  = (0.16 * height) + 'px ' + STD_FONT_NAME;
    var lcdSmallFont  = (0.1666666667 * height) + 'px ' + LCD_FONT_NAME;

    var aspectRatio   = height / width;

    var batteryBlinking = false;
    var alarmBlinking = false;

    // Create <canvas> element
    var canvas = doc.createElement('canvas');
    canvas.id = id;
    if (parentId === 'body') {
      doc.body.appendChild(canvas);
    } else {
      doc.getElementById(parentId).appendChild(canvas);
    }

    var mainCtx       = doc.getElementById(id).getContext('2d');
    var lcdBuffer     = doc.createElement('canvas');
    var textBuffer    = doc.createElement('canvas');
    var iconsBuffer   = doc.createElement('canvas');

    var postAction = "lws_query_lcd_datas";
    var odatas;
    var cycleCounter = 0;
    var maxCycleCounter = -1;

    var batteryInterval;
    var alarmInterval;
    var cycleInterval;
    var refreshInterval;


      // ******************** private methods ************************************
    var drawLcd = function() {
      var ctx = lcdBuffer.getContext("2d");
      var width = lcdBuffer.width;
      var height = lcdBuffer.height;
      ctx.clearRect(0, 0, width, height);
      var main = ctx.createLinearGradient(0, 0, 0, 0.98 * height);

      if (design === 'lcd-beige') {
        main.addColorStop(0.0, 'rgb(200, 200, 177)');
        main.addColorStop(0.005, 'rgb(241, 237, 207)');
        main.addColorStop(0.5, 'rgb(234, 230, 194)');
        main.addColorStop(0.5, 'rgb(225, 220, 183)');
        main.addColorStop(1.0, 'rgb(237, 232, 191)');
        foregroundColor = 'rgb(0, 0, 0)';
        backgroundColor = 'rgba(0, 0, 0, 0.1)';
      } else if (design === 'blue') {
        main.addColorStop(0.0, 'rgb(255, 255, 255)');
        main.addColorStop(0.005, 'rgb(231, 246, 255)');
        main.addColorStop(0.5, 'rgb(170, 224, 255)');
        main.addColorStop(0.5, 'rgb(136, 212, 255)');
        main.addColorStop(1.0, 'rgb(192, 232, 255)');
        foregroundColor = 'rgb( 18, 69, 100)';
        backgroundColor = 'rgba(18, 69, 100, 0.1)';
      } else if (design === 'orange') {
        main.addColorStop(0.0, 'rgb(255, 255, 255)');
        main.addColorStop(0.005, 'rgb(255, 245, 225)');
        main.addColorStop(0.5, 'rgb(255, 217, 147)');
        main.addColorStop(0.5, 'rgb(255, 201, 104)');
        main.addColorStop(1.0, 'rgb(255, 227, 173)');
        foregroundColor = 'rgb( 80, 55, 0)';
        backgroundColor = 'rgba(80, 55, 0, 0.1)';
      } else if (design === 'red') {
        main.addColorStop(0.0, 'rgb(255, 255, 255)');
        main.addColorStop(0.005, 'rgb(255, 225, 225)');
        main.addColorStop(0.5, 'rgb(252, 114, 115)');
        main.addColorStop(0.5, 'rgb(252, 114, 115)');
        main.addColorStop(1.0, 'rgb(254, 178, 178)');
        foregroundColor = 'rgb( 79, 12, 14)';
        backgroundColor = 'rgba(79, 12, 14, 0.1)';
      } else if (design === 'yellow') {
        main.addColorStop(0.0, 'rgb(255, 255, 255)');
        main.addColorStop(0.005, 'rgb(245, 255, 186)');
        main.addColorStop(0.5, 'rgb(158, 205,   0)');
        main.addColorStop(0.5, 'rgb(158, 205,   0)');
        main.addColorStop(1.0, 'rgb(210, 255,   0)');
        foregroundColor = 'rgb( 64, 83, 0)';
        backgroundColor = 'rgba(64, 83, 0, 0.1)';
      } else if (design === 'white') {
        main.addColorStop(0.0, 'rgb(255, 255, 255)');
        main.addColorStop(0.005, 'rgb(255, 255, 255)');
        main.addColorStop(0.5, 'rgb(241, 246, 242)');
        main.addColorStop(0.5, 'rgb(229, 239, 244)');
        main.addColorStop(1.0, 'rgb(255, 255, 255)');
        foregroundColor = 'rgb(0, 0, 0)';
        backgroundColor = 'rgba(0, 0, 0, 0.1)';
      } else if (design === 'gray') {
        main.addColorStop(0.0, 'rgb( 65,  65,  65)');
        main.addColorStop(0.005, 'rgb(117, 117, 117)');
        main.addColorStop(0.5, 'rgb( 87,  87,  87)');
        main.addColorStop(0.5, 'rgb( 65,  65,  65)');
        main.addColorStop(1.0, 'rgb( 81,  81,  81)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'black') {
        main.addColorStop(0.0, 'rgb( 65,  65,  65)');
        main.addColorStop(0.005, 'rgb(102, 102, 102)');
        main.addColorStop(0.5, 'rgb( 51,  51,  51)');
        main.addColorStop(0.5, 'rgb(  0,   0,   0)');
        main.addColorStop(1.0, 'rgb( 51,  51,  51)');
        foregroundColor = 'rgb(204, 204, 204)';
        backgroundColor = 'rgba(204, 204, 204, 0.1)';
      } else if (design === 'green') {
        main.addColorStop(0.0, 'rgb( 33,  67,  67)');
        main.addColorStop(0.005, 'rgb( 33,  67,  67)');
        main.addColorStop(0.5, 'rgb( 29,  58,  58)');
        main.addColorStop(0.5, 'rgb( 28,  57,  57)');
        main.addColorStop(1.0, 'rgb( 23,  46,  46)');
        foregroundColor = 'rgb(  0, 185, 165)';
        backgroundColor = 'rgba(0,  185, 165, 0.1)';
      } else if (design === 'green-darkgreen') {
        main.addColorStop(0.0, 'rgb( 27,  41,  17)');
        main.addColorStop(0.005, 'rgb( 70,  84,  58)');
        main.addColorStop(0.5, 'rgb( 36,  60,  14)');
        main.addColorStop(0.5, 'rgb( 24,  50,   1)');
        main.addColorStop(1.0, 'rgb(  8,  10,   7)');
        foregroundColor = 'rgb(152,  255, 74)';
        backgroundColor = 'rgba(152, 255, 74, 0.1)';
      } else if (design === 'blue2') {
        main.addColorStop(0.0, 'rgb(  0,  68, 103)');
        main.addColorStop(0.005, 'rgb(  8, 109, 165)');
        main.addColorStop(0.5, 'rgb(  0,  72, 117)');
        main.addColorStop(0.5, 'rgb(  0,  72, 117)');
        main.addColorStop(1.0, 'rgb(  0,  68, 103)');
        foregroundColor = 'rgb(111, 182, 228)';
        backgroundColor = 'rgba(111, 182, 228, 0.1)';
      } else if (design === 'blue-black') {
        main.addColorStop(0.0, 'rgb( 22, 125, 212)');
        main.addColorStop(0.005, 'rgb(  3, 162, 254)');
        main.addColorStop(0.5, 'rgb(  3, 162, 254)');
        main.addColorStop(0.5, 'rgb(  3, 162, 254)');
        main.addColorStop(1.0, 'rgb( 11, 172, 244)');
        foregroundColor = 'rgb(  0,   0,   0)';
        backgroundColor = 'rgba( 0,   0,   0, 0.1)';
      } else if (design === 'blue-darkblue') {
        main.addColorStop(0.0, 'rgb( 18,  33,  88)');
        main.addColorStop(0.005, 'rgb( 18,  33,  88)');
        main.addColorStop(0.5, 'rgb( 19,  30,  90)');
        main.addColorStop(0.5, 'rgb( 17,  31,  94)');
        main.addColorStop(1.0, 'rgb( 21,  25,  90)');
        foregroundColor = 'rgb( 23,  99, 221)';
        backgroundColor = 'rgba(23,  99, 221, 0.1)';
      } else if (design === 'blue-lightblue') {
        main.addColorStop(0.0, 'rgb( 88, 107, 132)');
        main.addColorStop(0.005, 'rgb( 53,  74, 104)');
        main.addColorStop(0.5, 'rgb( 27,  37,  65)');
        main.addColorStop(0.5, 'rgb(  5,  12,  40)');
        main.addColorStop(1.0, 'rgb( 32,  47,  79)');
        foregroundColor = 'rgb( 71, 178, 254)';
        backgroundColor = 'rgba(71, 178, 254, 0.1)';
      } else if (design === 'blue-gray') {
        main.addColorStop(0.0, 'rgb(135, 174, 255)');
        main.addColorStop(0.005, 'rgb(101, 159, 255)');
        main.addColorStop(0.5, 'rgb( 44,  93, 255)');
        main.addColorStop(0.5, 'rgb( 27,  65, 254)');
        main.addColorStop(1.0, 'rgb( 12,  50, 255)');
        foregroundColor = 'rgb(178, 180, 237)';
        backgroundColor = 'rgba(178, 180, 237, 0.1)';
      } else if (design === 'standard') {
        main.addColorStop(0.0, 'rgb(131, 133, 119)');
        main.addColorStop(0.005, 'rgb(176, 183, 167)');
        main.addColorStop(0.5, 'rgb(165, 174, 153)');
        main.addColorStop(0.5, 'rgb(166, 175, 156)');
        main.addColorStop(1.0, 'rgb(175, 184, 165)');
        foregroundColor = 'rgb( 35,  42,  52)';
        backgroundColor = 'rgba(35,  42,  52, 0.1)';
      } else if (design === 'lightgreen') {
        main.addColorStop(0.0, 'rgb(194, 212, 188)');
        main.addColorStop(0.005, 'rgb(212, 234, 206)');
        main.addColorStop(0.5, 'rgb(205, 224, 194)');
        main.addColorStop(0.5, 'rgb(206, 225, 194)');
        main.addColorStop(1.0, 'rgb(214, 233, 206)');
        foregroundColor = 'rgb(  0,  12,   6)';
        backgroundColor = 'rgba(0,   12,   6, 0.1)';
      } else if (design === 'standard-green') {
        main.addColorStop(0.0, 'rgb(255, 255, 255)');
        main.addColorStop(0.005, 'rgb(219, 230, 220)');
        main.addColorStop(0.5, 'rgb(179, 194, 178)');
        main.addColorStop(0.5, 'rgb(153, 176, 151)');
        main.addColorStop(1.0, 'rgb(114, 138, 109)');
        foregroundColor = 'rgb(  0,  12,   6)';
        backgroundColor = 'rgba(0,   12,   6, 0.1)';
      } else if (design === 'blue-blue') {
        main.addColorStop(0.0, 'rgb(100, 168, 253)');
        main.addColorStop(0.005, 'rgb(100, 168, 253)');
        main.addColorStop(0.5, 'rgb( 95, 160, 250)');
        main.addColorStop(0.5, 'rgb( 80, 144, 252)');
        main.addColorStop(1.0, 'rgb( 74, 134, 255)');
        foregroundColor = 'rgb(  0,  44, 187)';
        backgroundColor = 'rgba( 0,  44, 187, 0.1)';
      } else if (design === 'red-darkred') {
        main.addColorStop(0.0, 'rgb( 72,  36,  50)');
        main.addColorStop(0.005, 'rgb(185, 111, 110)');
        main.addColorStop(0.5, 'rgb(148,  66,  72)');
        main.addColorStop(0.5, 'rgb( 83,  19,  20)');
        main.addColorStop(1.0, 'rgb(  7,   6,  14)');
        foregroundColor = 'rgb(254, 139, 146)';
        backgroundColor = 'rgba(254, 139, 146, 0.1)';
      } else if (design === 'darkblue') {
        main.addColorStop(0.0, 'rgb( 14,  24,  31)');
        main.addColorStop(0.005, 'rgb( 46, 105, 144)');
        main.addColorStop(0.5, 'rgb( 19,  64,  96)');
        main.addColorStop(0.5, 'rgb(  6,  20,  29)');
        main.addColorStop(1.0, 'rgb(  8,   9,  10)');
        foregroundColor = 'rgb( 61, 179, 255)';
        backgroundColor = 'rgba(61, 179, 255, 0.1)';
      } else if (design === 'purple') {
        main.addColorStop(0.0, 'rgb(175, 164, 255)');
        main.addColorStop(0.005, 'rgb(188, 168, 253)');
        main.addColorStop(0.5, 'rgb(176, 159, 255)');
        main.addColorStop(0.5, 'rgb(174, 147, 252)');
        main.addColorStop(1.0, 'rgb(168, 136, 233)');
        foregroundColor = 'rgb(  7,  97,  72)';
        backgroundColor = 'rgba( 7,  97,  72, 0.1)';
      } else if (design === 'black-red') {
        main.addColorStop(0.0, 'rgb(  8,  12,  11)');
        main.addColorStop(0.005, 'rgb( 10,  11,  13)');
        main.addColorStop(0.5, 'rgb( 11,  10,  15)');
        main.addColorStop(0.5, 'rgb(  7,  13,   9)');
        main.addColorStop(1.0, 'rgb(  9,  13,  14)');
        foregroundColor = 'rgb(181,   0,  38)';
        backgroundColor = 'rgba(181,  0,  38, 0.1)';
      } else if (design === 'darkgreen') {
        main.addColorStop(0.0, 'rgb( 25,  85,   0)');
        main.addColorStop(0.005, 'rgb( 47, 154,   0)');
        main.addColorStop(0.5, 'rgb( 30, 101,   0)');
        main.addColorStop(0.5, 'rgb( 30, 101,   0)');
        main.addColorStop(1.0, 'rgb( 25,  85,   0)');
        foregroundColor = 'rgb( 35,  49,  35)';
        backgroundColor = 'rgba(35,  49,  35, 0.1)';
      } else if (design === 'amber') {
        main.addColorStop(0.0, 'rgb(182,  71,   0)');
        main.addColorStop(0.005, 'rgb(236, 155,  25)');
        main.addColorStop(0.5, 'rgb(212,  93,   5)');
        main.addColorStop(0.5, 'rgb(212,  93,   5)');
        main.addColorStop(1.0, 'rgb(182,  71,   0)');
        foregroundColor = 'rgb( 89,  58,  10)';
        backgroundColor = 'rgba(89,  58,  10, 0.1)';
      } else if (design === 'lightblue') {
        main.addColorStop(0.0, 'rgb(125, 146, 184)');
        main.addColorStop(0.005, 'rgb(197, 212, 231)');
        main.addColorStop(0.5, 'rgb(138, 155, 194)');
        main.addColorStop(0.5, 'rgb(138, 155, 194)');
        main.addColorStop(1.0, 'rgb(125, 146, 184)');
        foregroundColor = 'rgb(  9,   0,  81)';
        backgroundColor = 'rgba( 9,   0,  81, 0.1)';
      } else if (design === 'green-black') {
        main.addColorStop(0.0, 'rgb(  1,  47,   0)');
        main.addColorStop(0.005, 'rgb( 20, 106,  61)');
        main.addColorStop(0.5, 'rgb( 33, 125,  84)');
        main.addColorStop(0.5, 'rgb( 33, 125,  84)');
        main.addColorStop(1.0, 'rgb( 33, 109,  63)');
        foregroundColor = 'rgb(  3,  15,  11)';
        backgroundColor = 'rgba(3, 15, 11, 0.1)';
      } else if (design === 'yellow-black') {
        main.addColorStop(0.0, 'rgb(223, 248,  86)');
        main.addColorStop(0.005, 'rgb(222, 255,  28)');
        main.addColorStop(0.5, 'rgb(213, 245,  24)');
        main.addColorStop(0.5, 'rgb(213, 245,  24)');
        main.addColorStop(1.0, 'rgb(224, 248,  88)');
        foregroundColor = 'rgb(  9,  19,   0)';
        backgroundColor = 'rgba( 9,  19,   0, 0.1)';
      } else if (design === 'black-yellow') {
        main.addColorStop(0.0, 'rgb( 43,   3,   3)');
        main.addColorStop(0.005, 'rgb( 29,   0,   0)');
        main.addColorStop(0.5, 'rgb( 26,   2,   2)');
        main.addColorStop(0.5, 'rgb( 31,   5,   8)');
        main.addColorStop(1.0, 'rgb( 30,   1,   3)');
        foregroundColor = 'rgb(255, 254,  24)';
        backgroundColor = 'rgba(255, 254, 24, 0.1)';
      } else if (design === 'lightgreen-black') {
        main.addColorStop(0.0, 'rgb( 79, 121,  19)');
        main.addColorStop(0.005, 'rgb( 96, 169,   0)');
        main.addColorStop(0.5, 'rgb(120, 201,   2)');
        main.addColorStop(0.5, 'rgb(118, 201,   0)');
        main.addColorStop(1.0, 'rgb(105, 179,   4)');
        foregroundColor = 'rgb(  0,  35,   0)';
        backgroundColor = 'rgba( 0,  35,   0, 0.1)';
      } else if (design === 'darkpurple') {
        main.addColorStop(0.0, 'rgb( 35,  24,  75)');
        main.addColorStop(0.005, 'rgb( 42,  20, 111)');
        main.addColorStop(0.5, 'rgb( 40,  22, 103)');
        main.addColorStop(0.5, 'rgb( 40,  22, 103)');
        main.addColorStop(1.0, 'rgb( 41,  21, 111)');
        foregroundColor = 'rgb(158, 167, 210)';
        backgroundColor = 'rgba(158, 167, 210, 0.1)';
      } else if (design === 'darkamber') {
        main.addColorStop(0.0, 'rgb(134,  39,  17)');
        main.addColorStop(0.005, 'rgb(120,  24,   0)');
        main.addColorStop(0.5, 'rgb( 83,  15,  12)');
        main.addColorStop(0.5, 'rgb( 83,  15,  12)');
        main.addColorStop(1.0, 'rgb(120,  24,   0)');
        foregroundColor = 'rgb(233, 140,  44)';
        backgroundColor = 'rgba(233, 140, 44, 0.1)';
      } else if (design === 'blue-lightblue2') {
        main.addColorStop(0.0, 'rgb( 15,  84, 151)');
        main.addColorStop(0.005, 'rgb( 60, 103, 198)');
        main.addColorStop(0.5, 'rgb( 67, 109, 209)');
        main.addColorStop(0.5, 'rgb( 67, 109, 209)');
        main.addColorStop(1.0, 'rgb( 64, 101, 190)');
        foregroundColor = 'rgb(193, 253, 254)';
        backgroundColor = 'rgba(193, 253, 254, 0.1)';
      } else if (design === 'gray-purple') {
        main.addColorStop(0.0, 'rgb(153, 164, 161)');
        main.addColorStop(0.005, 'rgb(203, 215, 213)');
        main.addColorStop(0.5, 'rgb(202, 212, 211)');
        main.addColorStop(0.5, 'rgb(202, 212, 211)');
        main.addColorStop(1.0, 'rgb(198, 209, 213)');
        foregroundColor = 'rgb( 99, 124, 204)';
        backgroundColor = 'rgba(99, 124, 204, 0.1)';
      } else if (design === 'sections') {
        main.addColorStop(0.0, 'rgb(178, 178, 178)');
        main.addColorStop(0.005, 'rgb(255, 255, 255)');
        main.addColorStop(0.5, 'rgb(196, 196, 196)');
        main.addColorStop(0.5, 'rgb(196, 196, 196)');
        main.addColorStop(1.0, 'rgb(178, 178, 178)');
        foregroundColor = 'rgb(0, 0, 0)';
        backgroundColor = 'rgba(0, 0, 0, 0.1)';
      } else if (design === 'yoctopuce') {
        main.addColorStop(0.0, 'rgb(14, 24, 31)');
        main.addColorStop(0.005, 'rgb(35, 35, 65)');
        main.addColorStop(0.5, 'rgb(30, 30, 60)');
        main.addColorStop(0.5, 'rgb(30, 30, 60)');
        main.addColorStop(1.0, 'rgb(25, 25, 55)');
        foregroundColor = 'rgb(153, 229, 255)';
        backgroundColor = 'rgba(153,229,255, 0.1)';
      } else if (design === 'flat-turqoise') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 31, 188, 156)');
        main.addColorStop(0.005, 'rgb( 31, 188, 156)');
        main.addColorStop(0.5, 'rgb( 31, 188, 156)');
        main.addColorStop(0.5, 'rgb( 31, 188, 156)');
        main.addColorStop(1.0, 'rgb( 31, 188, 156)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-green-sea') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 26, 188, 156)');
        main.addColorStop(0.005, 'rgb( 26, 188, 156)');
        main.addColorStop(0.5, 'rgb( 26, 188, 156)');
        main.addColorStop(0.5, 'rgb( 26, 188, 156)');
        main.addColorStop(1.0, 'rgb( 26, 188, 156)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-emerland') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 46, 204, 113)');
        main.addColorStop(0.005, 'rgb( 46, 204, 113)');
        main.addColorStop(0.5, 'rgb( 46, 204, 113)');
        main.addColorStop(0.5, 'rgb( 46, 204, 113)');
        main.addColorStop(1.0, 'rgb( 46, 204, 113)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-nephritis') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 39, 174,  96)');
        main.addColorStop(0.005, 'rgb( 39, 174,  96)');
        main.addColorStop(0.5, 'rgb( 39, 174,  96)');
        main.addColorStop(0.5, 'rgb( 39, 174,  96)');
        main.addColorStop(1.0, 'rgb( 39, 174,  96)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-peter-river') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 52, 152, 219)');
        main.addColorStop(0.005, 'rgb( 52, 152, 219)');
        main.addColorStop(0.5, 'rgb( 52, 152, 219)');
        main.addColorStop(0.5, 'rgb( 52, 152, 219)');
        main.addColorStop(1.0, 'rgb( 52, 152, 219)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-belize-hole') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 41, 128, 185)');
        main.addColorStop(0.005, 'rgb( 41, 128, 185)');
        main.addColorStop(0.5, 'rgb( 41, 128, 185)');
        main.addColorStop(0.5, 'rgb( 41, 128, 185)');
        main.addColorStop(1.0, 'rgb( 41, 128, 185)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-amythyst') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(155,  89, 182)');
        main.addColorStop(0.005, 'rgb(155,  89, 182)');
        main.addColorStop(0.5, 'rgb(155,  89, 182)');
        main.addColorStop(0.5, 'rgb(155,  89, 182)');
        main.addColorStop(1.0, 'rgb(155,  89, 182)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-wisteria') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(142,  68, 173)');
        main.addColorStop(0.005, 'rgb(142,  68, 173)');
        main.addColorStop(0.5, 'rgb(142,  68, 173)');
        main.addColorStop(0.5, 'rgb(142,  68, 173)');
        main.addColorStop(1.0, 'rgb(142,  68, 173)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-sunflower') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(241, 196,  15)');
        main.addColorStop(0.005, 'rgb(241, 196,  15)');
        main.addColorStop(0.5, 'rgb(241, 196,  15)');
        main.addColorStop(0.5, 'rgb(241, 196,  15)');
        main.addColorStop(1.0, 'rgb(241, 196,  15)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-orange') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(243, 156,  18)');
        main.addColorStop(0.005, 'rgb(243, 156,  18)');
        main.addColorStop(0.5, 'rgb(243, 156,  18)');
        main.addColorStop(0.5, 'rgb(243, 156,  18)');
        main.addColorStop(1.0, 'rgb(243, 156,  18)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-carrot') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(230, 126,  34)');
        main.addColorStop(0.005, 'rgb(230, 126,  34)');
        main.addColorStop(0.5, 'rgb(230, 126,  34)');
        main.addColorStop(0.5, 'rgb(230, 126,  34)');
        main.addColorStop(1.0, 'rgb(230, 126,  34)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-pumpkin') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(211,  84,   0)');
        main.addColorStop(0.005, 'rgb(211,  84,   0)');
        main.addColorStop(0.5, 'rgb(211,  84,   0)');
        main.addColorStop(0.5, 'rgb(211,  84,   0)');
        main.addColorStop(1.0, 'rgb(211,  84,   0)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-alizarin') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(231,  76,  60)');
        main.addColorStop(0.005, 'rgb(231,  76,  60)');
        main.addColorStop(0.5, 'rgb(231,  76,  60)');
        main.addColorStop(0.5, 'rgb(231,  76,  60)');
        main.addColorStop(1.0, 'rgb(231,  76,  60)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-pomegranate') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(192,  57,  43)');
        main.addColorStop(0.005, 'rgb(192,  57,  43)');
        main.addColorStop(0.5, 'rgb(192,  57,  43)');
        main.addColorStop(0.5, 'rgb(192,  57,  43)');
        main.addColorStop(1.0, 'rgb(192,  57,  43)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-clouds') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(236, 240, 241)');
        main.addColorStop(0.005, 'rgb(236, 240, 241)');
        main.addColorStop(0.5, 'rgb(236, 240, 241)');
        main.addColorStop(0.5, 'rgb(236, 240, 241)');
        main.addColorStop(1.0, 'rgb(236, 240, 241)');
        foregroundColor = 'rgb(  0,   0,   0)';
        backgroundColor = 'rgba(  0,   0,   0, 0.1)';
      } else if (design === 'flat-silver') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(189, 195, 199)');
        main.addColorStop(0.005, 'rgb(189, 195, 199)');
        main.addColorStop(0.5, 'rgb(189, 195, 199)');
        main.addColorStop(0.5, 'rgb(189, 195, 199)');
        main.addColorStop(1.0, 'rgb(189, 195, 199)');
        foregroundColor = 'rgb(  0,   0,   0)';
        backgroundColor = 'rgba(  0,   0,   0, 0.1)';
      } else if (design === 'flat-concrete') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(149, 165, 166)');
        main.addColorStop(0.005, 'rgb(149, 165, 166)');
        main.addColorStop(0.5, 'rgb(149, 165, 166)');
        main.addColorStop(0.5, 'rgb(149, 165, 166)');
        main.addColorStop(1.0, 'rgb(149, 165, 166)');
        foregroundColor = 'rgb(  0,   0,   0)';
        backgroundColor = 'rgba(  0,   0,   0, 0.1)';
      } else if (design === 'flat-asbestos') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb(127, 140, 141)');
        main.addColorStop(0.005, 'rgb(127, 140, 141)');
        main.addColorStop(0.5, 'rgb(127, 140, 141)');
        main.addColorStop(0.5, 'rgb(127, 140, 141)');
        main.addColorStop(1.0, 'rgb(127, 140, 141)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-wet-asphalt') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 52,  73,  94)');
        main.addColorStop(0.005, 'rgb( 52,  73,  94)');
        main.addColorStop(0.5, 'rgb( 52,  73,  94)');
        main.addColorStop(0.5, 'rgb( 52,  73,  94)');
        main.addColorStop(1.0, 'rgb( 52,  73,  94)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else if (design === 'flat-midnight-blue') {
        frame = 'rgb(255, 255, 255)';
        main.addColorStop(0.0, 'rgb( 44,  62,  80)');
        main.addColorStop(0.005, 'rgb( 44,  62,  80)');
        main.addColorStop(0.5, 'rgb( 44,  62,  80)');
        main.addColorStop(0.5, 'rgb( 44,  62,  80)');
        main.addColorStop(1.0, 'rgb( 44,  62,  80)');
        foregroundColor = 'rgb(255, 255, 255)';
        backgroundColor = 'rgba(255, 255, 255, 0.1)';
      } else {
        main.addColorStop(0.0, 'rgb(131, 133, 119)');
        main.addColorStop(0.005, 'rgb(176, 183, 167)');
        main.addColorStop(0.5, 'rgb(165, 174, 153)');
        main.addColorStop(0.5, 'rgb(166, 175, 156)');
        main.addColorStop(1.0, 'rgb(175, 184, 165)');
        foregroundColor = 'rgb( 35,  42,  52)';
        backgroundColor = 'rgba(35,  42,  52, 0.1)';
      }

      roundedRectangle(ctx, 1, 1, width - 2, height - 2, 0.0333333333 * height);
      ctx.fillStyle = main;
      ctx.strokeStyle = 'transparent';
      ctx.fill();
    };

    var drawText = function() {
      var ctx = textBuffer.getContext("2d");
      var width = textBuffer.width;
      var height = textBuffer.height;

      ctx.clearRect(0, 0, width, height);

      lcdFontHeight = Math.floor(0.5833333333 * height);
      lcdFont       = lcdFontHeight + 'px ' + LCD_FONT_NAME;

      lcdUnitFont  = Math.floor(0.26 * height) + 'px ' + LCD_FONT_NAME;
      lcdTitleFont = Math.floor(0.16 * height) + 'px ' + STD_FONT_NAME;
      lcdSmallFont = Math.floor(0.1666666667 * height) + 'px ' + LCD_FONT_NAME;

      ctx.font = lcdUnitFont;
      var unitWidth = ctx.measureText('0000').width;
      ctx.font = lcdFont;
      var textWidth = ctx.measureText(Number(value).toFixed(decimals)).width;

      // calculate background text
      var oneSegmentWidth = ctx.measureText('8').width;

      // Width of decimals
      var widthOfDecimals = decimals === 0 ? 0 : decimals * oneSegmentWidth + oneSegmentWidth;

      // Available width
      var availableWidth = width - 2 - (unitWidth + height * 0.0833333333) - widthOfDecimals - (signalVisible ? 0.0303 * width : 0);

      // Number of segments
      var noOfSegments = Math.floor(availableWidth / oneSegmentWidth);

      // Add segments to background text
      var backgroundText = '';
      for (var i = 0; i < noOfSegments; i++) {
        backgroundText += '8';
      }
      if (decimals !== 0) {
        backgroundText += ".";
        for (var i = 0; i < decimals; i++) {
          backgroundText += '8';
        }
      }
      var backgroundWidth = ctx.measureText(backgroundText).width;
      var backgroundValueText = '888.8';
      var backgroundValueWidth = ctx.measureText(backgroundValueText).width;
      var lowerRightTextBackground = '888888' ;
      var lowerRightTextBackgroundWidth = ctx.measureText(lowerRightTextBackground).width;
      var lowerCenterTextBackground = '88888888888888888' ;
      var lowerCenterTextBackgroundWidth = ctx.measureText(lowerCenterTextBackground).width;

      ctx.save();
      ctx.fillStyle    = backgroundColor;
      ctx.textBaseline = 'bottom';

      ctx.font         = lcdFont;
      ctx.fillText(backgroundText, width - 2 - backgroundWidth - (unitWidth + width * 0.03333), 0.77 * height);

      ctx.font = lcdUnitFont;
      ctx.fillText('8888', width - unitWidth - 0.027 * width, 0.52 * height);

      ctx.font         = lcdSmallFont;
      ctx.fillText(lowerCenterTextBackground, (lowerCenterTextBackgroundWidth - width) * 0.395, 0.92 * height);
      ctx.fillText(backgroundValueText, 0.03 * width, 0.23 * height);
      ctx.fillText(backgroundValueText, width - 0.03 * width - 0.27 * backgroundValueWidth, 0.23 * height);
      ctx.fillText(lowerRightTextBackground, width - 0.03 * width - 0.275 * lowerRightTextBackgroundWidth, 0.67 * height);

      ctx.fillStyle = foregroundColor;

      //valueText
      ctx.font = lcdFont;
      ctx.textBaseline = 'bottom';
      if (unitVisible) {
        ctx.fillText(Number(value).toFixed(decimals), width - 2 - textWidth - (unitWidth + width * 0.03333), 0.77 * height);
      } else {
        ctx.fillText(Number(value).toFixed(decimals), width - 2 - textWidth - width * 0.03333, 0.77 * height);
      }

      //unitText
      if (unitVisible) {
        ctx.fill();
        ctx.font = lcdUnitFont;
        ctx.textBaseline = 'bottom';
        ctx.fillText(unit, width - unitWidth - 0.027 * width, 0.52 * height);
      }

      //lowerCenterText
      if (formerValueVisible) { lowerCenterText = Number(formerValue).toFixed(decimals); }
      if (lowerCenterTextVisible) {
        var text = lowerCenterText.substring(0,lowerCenterTextBackground.length);
        var sl = lowerCenterTextBackground.length - ((lowerCenterTextBackground.length-text.length)/2)-(((lowerCenterTextBackground.length-text.length)%2));
        while (text.length < sl) {
          text = ' '+text;
        }
        ctx.font         = lcdSmallFont;
        ctx.textBaseline = 'bottom';
        ctx.fillText(text, (lowerCenterTextBackgroundWidth - width) * 0.395, 0.92 * height);
      }

      //upperLeftText
      if (upperLeftTextVisible) {
        ctx.font = lcdSmallFont;
        ctx.textBaseline = 'bottom';
        upperLeftText = Number(minValue).toFixed(1);
        while (upperLeftText.length < backgroundValueText.length) {
          upperLeftText = ' '+upperLeftText;
        }
        ctx.fillText(upperLeftText, 0.03 * width, 0.23 * height);
      }

      //upperRightText
      if (upperRightTextVisible) {
        ctx.font = lcdSmallFont;
        ctx.textBaseline = 'bottom';
        upperRightText = Number(maxValue).toFixed(1);
        while (upperRightText.length < backgroundValueText.length) {
          upperRightText = ' '+upperRightText;
        }
        ctx.fillText(upperRightText, width - 0.03 * width - 0.27 * backgroundValueWidth, 0.23 * height);
      }

      //upperCenterText
      if (upperCenterTextVisible) {
        ctx.font = 'bold ' + lcdTitleFont;
        ctx.textBaseline = 'bottom';
        ctx.fillText(upperCenterText, (width - ctx.measureText(upperCenterText).width) * 0.5, 0.23 * height);
      }

      //lowerRightText
      if (lowerRightTextVisible) {
        ctx.font = lcdSmallFont;
        ctx.textBaseline = 'bottom';
        ctx.fillText(lowerRightText, width - 0.03 * width - ctx.measureText(lowerRightText).width, 0.67 * height);
      }
    };

    var drawIcons = function() {
      var ctx    = iconsBuffer.getContext("2d");
      var width  = iconsBuffer.width;
      var height = iconsBuffer.height;

      ctx.clearRect(0, 0, width, height);

      ctx.fillStyle = foregroundColor;

      var sWidth = width;
      var sHeight = height;
      var shift = width;

      if (trendVisible) {
        width = 0.9*sWidth;
        height = 0.96*sHeight;
        shift = 0.032*sWidth;
        if (trend === 'down') {
          //trendDown
          ctx.beginPath();
          ctx.moveTo(0.18181818181818182 * width + shift, 0.8125 * height);
          ctx.lineTo(0.21212121212121213 * width + shift, 0.9375 * height);
          ctx.lineTo(0.24242424242424243 * width + shift, 0.8125 * height);
          ctx.lineTo(0.18181818181818182 * width + shift, 0.8125 * height);
          ctx.closePath();
          ctx.fill();
        } else if (trend === 'falling') {
          //trendFalling
          ctx.beginPath();
          ctx.moveTo(0.18181818181818182 * width + shift, 0.8958333333333334 * height);
          ctx.lineTo(0.24242424242424243 * width + shift, 0.9375 * height);
          ctx.lineTo(0.20454545454545456 * width + shift, 0.8125 * height);
          ctx.lineTo(0.18181818181818182 * width + shift, 0.8958333333333334 * height);
          ctx.closePath();
          ctx.fill();
        } else if (trend === 'steady') {
          //trendSteady
          ctx.beginPath();
          ctx.moveTo(0.18181818181818182 * width + shift, 0.8125 * height);
          ctx.lineTo(0.24242424242424243 * width + shift, 0.875 * height);
          ctx.lineTo(0.18181818181818182 * width + shift, 0.9375 * height);
          ctx.lineTo(0.18181818181818182 * width + shift, 0.8125 * height);
          ctx.closePath();
          ctx.fill();
        } else if (trend === 'rising') {
          //trendRising
          ctx.beginPath();
          ctx.moveTo(0.18181818181818182 * width + shift, 0.8541666666666666 * height);
          ctx.lineTo(0.24242424242424243 * width + shift, 0.8125 * height);
          ctx.lineTo(0.20454545454545456 * width + shift, 0.9375 * height);
          ctx.lineTo(0.18181818181818182 * width + shift, 0.8541666666666666 * height);
          ctx.closePath();
          ctx.fill();
        } else if (trend === 'up') {
          //trendUp
          ctx.beginPath();
          ctx.moveTo(0.18181818181818182 * width + shift, 0.9375 * height);
          ctx.lineTo(0.21212121212121213 * width + shift, 0.8125 * height);
          ctx.lineTo(0.24242424242424243 * width + shift, 0.9375 * height);
          ctx.lineTo(0.18181818181818182 * width + shift, 0.9375 * height);
          ctx.closePath();
          ctx.fill();
        }
        width = sWidth ;
        height = sHeight ;
      }

      if (batteryVisible && !batteryBlinking) {
        width = 1.21*sWidth;
        height = 0.97*sHeight;
        if (battery === 'empty') {
          //empty
          ctx.save();
          ctx.beginPath();
          ctx.moveTo(0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9375 * height, 0.803030303030303 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9583333333333334 * height, 0.7954545454545454 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9583333333333334 * height, 0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.8125 * height, 0.6666666666666666 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.8125 * height, 0.6742424242424242 * width, 0.7916666666666666 * height, 0.6742424242424242 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.7916666666666666 * height, 0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8541666666666666 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.8958333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.9166666666666666 * height, 0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.closePath();
          ctx.moveTo(0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.8125 * height, 0.7954545454545454 * width, 0.8125 * height, 0.7878787878787878 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.7878787878787878 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8333333333333334 * height, 0.6742424242424242 * width, 0.9166666666666666 * height, 0.6742424242424242 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9375 * height, 0.6742424242424242 * width, 0.9375 * height, 0.6818181818181818 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6818181818181818 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9166666666666666 * height, 0.7954545454545454 * width, 0.8333333333333334 * height, 0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.fill();
        } else if (battery === 'onethird') {
          // 30%
          ctx.beginPath();
          ctx.moveTo(0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9375 * height, 0.803030303030303 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9583333333333334 * height, 0.7954545454545454 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9583333333333334 * height, 0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.8125 * height, 0.6666666666666666 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.8125 * height, 0.6742424242424242 * width, 0.7916666666666666 * height, 0.6742424242424242 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.7916666666666666 * height, 0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8541666666666666 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.8958333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.9166666666666666 * height, 0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.closePath();
          ctx.moveTo(0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.8125 * height, 0.7954545454545454 * width, 0.8125 * height, 0.7878787878787878 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.7878787878787878 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8333333333333334 * height, 0.6742424242424242 * width, 0.9166666666666666 * height, 0.6742424242424242 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9375 * height, 0.6742424242424242 * width, 0.9375 * height, 0.6818181818181818 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6818181818181818 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9166666666666666 * height, 0.7954545454545454 * width, 0.8333333333333334 * height, 0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.moveTo(0.6818181818181818 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7121212121212122 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7121212121212122 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.6818181818181818 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.6818181818181818 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.fill();
        } else if (battery === 'twothirds') {
          // 60%
          ctx.beginPath();
          ctx.moveTo(0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9375 * height, 0.803030303030303 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9583333333333334 * height, 0.7954545454545454 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9583333333333334 * height, 0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.8125 * height, 0.6666666666666666 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.8125 * height, 0.6742424242424242 * width, 0.7916666666666666 * height, 0.6742424242424242 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.7916666666666666 * height, 0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8541666666666666 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.8958333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.9166666666666666 * height, 0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.closePath();
          ctx.moveTo(0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.8125 * height, 0.7954545454545454 * width, 0.8125 * height, 0.7878787878787878 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.7878787878787878 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8333333333333334 * height, 0.6742424242424242 * width, 0.9166666666666666 * height, 0.6742424242424242 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9375 * height, 0.6742424242424242 * width, 0.9375 * height, 0.6818181818181818 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6818181818181818 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9166666666666666 * height, 0.7954545454545454 * width, 0.8333333333333334 * height, 0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.moveTo(0.7196969696969697 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.75 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.75 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.7196969696969697 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.7196969696969697 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.moveTo(0.6818181818181818 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7121212121212122 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7121212121212122 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.6818181818181818 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.6818181818181818 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.fill();
        } else if (battery === 'full') {
          //battery_1
          ctx.beginPath();
          ctx.moveTo(0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9166666666666666 * height, 0.803030303030303 * width, 0.9375 * height, 0.803030303030303 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9583333333333334 * height, 0.7954545454545454 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height, 0.6742424242424242 * width, 0.9583333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9583333333333334 * height, 0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.9375 * height, 0.6666666666666666 * width, 0.8125 * height, 0.6666666666666666 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6666666666666666 * width, 0.8125 * height, 0.6742424242424242 * width, 0.7916666666666666 * height, 0.6742424242424242 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height, 0.7954545454545454 * width, 0.7916666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.7916666666666666 * height, 0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8125 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.803030303030303 * width, 0.8333333333333334 * height, 0.803030303030303 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8333333333333334 * height, 0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8541666666666666 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8541666666666666 * height, 0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.8958333333333334 * height);
          ctx.bezierCurveTo(0.8106060606060606 * width, 0.8958333333333334 * height, 0.8106060606060606 * width, 0.9166666666666666 * height, 0.8106060606060606 * width, 0.9166666666666666 * height);
          ctx.closePath();
          ctx.moveTo(0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.8125 * height, 0.7954545454545454 * width, 0.8125 * height, 0.7878787878787878 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.7878787878787878 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height, 0.6818181818181818 * width, 0.8125 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8125 * height, 0.6742424242424242 * width, 0.8333333333333334 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.8333333333333334 * height, 0.6742424242424242 * width, 0.9166666666666666 * height, 0.6742424242424242 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.6742424242424242 * width, 0.9375 * height, 0.6742424242424242 * width, 0.9375 * height, 0.6818181818181818 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.6818181818181818 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height, 0.7878787878787878 * width, 0.9375 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9375 * height, 0.7954545454545454 * width, 0.9166666666666666 * height);
          ctx.bezierCurveTo(0.7954545454545454 * width, 0.9166666666666666 * height, 0.7954545454545454 * width, 0.8333333333333334 * height, 0.7954545454545454 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.moveTo(0.7575757575757576 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7878787878787878 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7878787878787878 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.7575757575757576 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.7575757575757576 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.moveTo(0.7196969696969697 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.75 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.75 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.7196969696969697 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.7196969696969697 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.moveTo(0.6818181818181818 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7121212121212122 * width, 0.8333333333333334 * height);
          ctx.lineTo(0.7121212121212122 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.6818181818181818 * width, 0.9166666666666666 * height);
          ctx.lineTo(0.6818181818181818 * width, 0.8333333333333334 * height);
          ctx.closePath();
          ctx.fill();
        }
        width = sWidth ;
        height = sHeight ;
      }
      
      if (alarmVisible && !alarmBlinking) {
        shift = -0.2*sWidth;
        ctx.beginPath();
        ctx.moveTo(0.3333333333333333 * width + shift, 0.9166666666666666 * height);
        ctx.bezierCurveTo(0.3333333333333333 * width + shift, 0.9375 * height, 0.32575757575757575 * width + shift, 0.9375 * height, 0.32575757575757575 * width + shift, 0.9375 * height);
        ctx.bezierCurveTo(0.3181818181818182 * width + shift, 0.9375 * height, 0.3106060606060606 * width + shift, 0.9375 * height, 0.3106060606060606 * width + shift, 0.9166666666666666 * height);
        ctx.bezierCurveTo(0.3106060606060606 * width + shift, 0.9166666666666666 * height, 0.3333333333333333 * width + shift, 0.9166666666666666 * height, 0.3333333333333333 * width + shift, 0.9166666666666666 * height);
        ctx.closePath();
        ctx.moveTo(0.3560606060606061 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.3333333333333333 * width + shift, 0.8541666666666666 * height, 0.3484848484848485 * width + shift, 0.75 * height, 0.32575757575757575 * width + shift, 0.75 * height);
        ctx.bezierCurveTo(0.32575757575757575 * width + shift, 0.75 * height, 0.32575757575757575 * width + shift, 0.75 * height, 0.32575757575757575 * width + shift, 0.75 * height);
        ctx.bezierCurveTo(0.32575757575757575 * width + shift, 0.75 * height, 0.32575757575757575 * width + shift, 0.75 * height, 0.32575757575757575 * width + shift, 0.75 * height);
        ctx.bezierCurveTo(0.29545454545454547 * width + shift, 0.75 * height, 0.3106060606060606 * width + shift, 0.8541666666666666 * height, 0.2878787878787879 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.2878787878787879 * width + shift, 0.8958333333333334 * height, 0.2878787878787879 * width + shift, 0.8958333333333334 * height, 0.2878787878787879 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.2878787878787879 * width + shift, 0.8958333333333334 * height, 0.2878787878787879 * width + shift, 0.8958333333333334 * height, 0.2878787878787879 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.2878787878787879 * width + shift, 0.8958333333333334 * height, 0.2878787878787879 * width + shift, 0.8958333333333334 * height, 0.2878787878787879 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.2878787878787879 * width + shift, 0.8958333333333334 * height, 0.32575757575757575 * width + shift, 0.8958333333333334 * height, 0.32575757575757575 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.32575757575757575 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.3560606060606061 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.3560606060606061 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height);
        ctx.bezierCurveTo(0.3560606060606061 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height, 0.3560606060606061 * width + shift, 0.8958333333333334 * height);
        ctx.closePath();
        ctx.fill();
      }

      if (signalVisible) {
        shift = 0.01*sWidth;
        ctx.fillStyle = backgroundColor;
        ctx.beginPath();
        ctx.moveTo(0.015151515151515152 * width + shift, 0.22916666666666666 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.3541666666666667 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.3541666666666667 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.22916666666666666 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.22916666666666666 * height);
        ctx.closePath();
        ctx.moveTo(0.015151515151515152 * width + shift, 0.375 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.5 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.5 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.375 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.375 * height);
        ctx.closePath();
        ctx.moveTo(0.015151515151515152 * width + shift, 0.5208333333333334 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.6458333333333334 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.6458333333333334 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.5208333333333334 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.5208333333333334 * height);
        ctx.closePath();
        ctx.moveTo(0.015151515151515152 * width + shift, 0.6666666666666666 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.7916666666666666 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.7916666666666666 * height);
        ctx.lineTo(0.030303030303030304 * width + shift, 0.6666666666666666 * height);
        ctx.lineTo(0.015151515151515152 * width + shift, 0.6666666666666666 * height);
        ctx.closePath();
        ctx.fill();

        ctx.fillStyle = foregroundColor;
        if (signalStrength > 0.13) {
          ctx.beginPath();
          ctx.moveTo(0.015151515151515152 * width + shift, 0.6666666666666666 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.6666666666666666 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.7916666666666666 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.7916666666666666 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.6666666666666666 * height);
          ctx.closePath();
          ctx.fill();
        }
        if (signalStrength > 0.38) {
          ctx.beginPath();
          ctx.moveTo(0.015151515151515152 * width + shift, 0.5208333333333334 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.5208333333333334 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.6458333333333334 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.6458333333333334 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.5208333333333334 * height);
          ctx.closePath();
          ctx.fill();
        }
        if (signalStrength > 0.63) {
          ctx.beginPath();
          ctx.moveTo(0.015151515151515152 * width + shift, 0.375 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.375 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.5 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.5 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.375 * height);
          ctx.closePath();
          ctx.fill();
        }
        if (signalStrength > 0.88) {
          ctx.beginPath();
          ctx.moveTo(0.015151515151515152 * width + shift, 0.22916666666666666 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.22916666666666666 * height);
          ctx.lineTo(0.030303030303030304 * width + shift, 0.3541666666666667 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.3541666666666667 * height);
          ctx.lineTo(0.015151515151515152 * width + shift, 0.22916666666666666 * height);
          ctx.closePath();
          ctx.fill();
        }
      }
    };

    function clamp(min, max, value) {
      if (value < min)
        return min;
      if (value > max)
        return max;
      return value;
    }

    function roundedRectangle(ctx, x, y, w, h, radius) {
      var r = x + w,
          b = y + h;
      ctx.beginPath();
      ctx.moveTo(x + radius, y);
      ctx.lineTo(r - radius, y);
      ctx.quadraticCurveTo(r, y, r, y + radius);
      ctx.lineTo(r, y + h - radius);
      ctx.quadraticCurveTo(r, b, r - radius, b);
      ctx.lineTo(x + radius, b);
      ctx.quadraticCurveTo(x, b, x, b - radius);
      ctx.lineTo(x, y + radius);
      ctx.quadraticCurveTo(x, y, x + radius, y);
      ctx.closePath();
      ctx.stroke();
    }

    function onResize() {
      if (scalable) {
        width  = doc.getElementById(parentId).parentElement.clientWidth;
        height = width * aspectRatio;
      }
      canvas.width = width;
      canvas.height = height;
      lcdBuffer.width = width;
      lcdBuffer.height = height;
      textBuffer.width = width;
      textBuffer.height = height;
      iconsBuffer.width = width;
      iconsBuffer.height = height;
      drawLcd();
      drawText();
      drawIcons();
      repaint();
    }

    function repaint() {
      mainCtx.clearRect(0, 0, canvas.width, canvas.height);
      mainCtx.drawImage(lcdBuffer, 0, 0);
      mainCtx.drawImage(textBuffer, 0, 0);
      mainCtx.drawImage(iconsBuffer, 0, 0);
    }

    function setValues() {
      if (value < minValue) {
        minValue = value;
      }
      if (value > maxValue) {
        maxValue = value;
      }
    }

    function _blinkBattery() {
      if (battery == 'empty') {
        batteryBlinking = !batteryBlinking;
      }
      else {
        batteryBlinking = false;
      }
      drawIcons();
      repaint();
    }

    function _blinkAlarm() {
      if (alarmVisible) {
        alarmBlinking = !alarmBlinking;
      }
      drawIcons();
      repaint();
    }

    function _cycle() {
      cycleCounter += 1;
      if (cycleCounter > maxCycleCounter) {
        cycleCounter = 0;
      }
      refreshDatas();
    }

    function _setBattery(nBattery) {
      if (battery == 'empty') {
        if ( batteryInterval != null ) {
          clearInterval(batteryInterval);
        }
      }
      battery = nBattery;
      if (battery == 'empty') {
        batteryInterval = setInterval(_blinkBattery, 600);
      }
      drawIcons();
      repaint();
    }

    function _setAlarmVisible(nAlarmVisible) {
      if (alarmVisible) {
        if ( alarmInterval != null ) {
          clearInterval(alarmInterval);
        }
      }
      alarmVisible = nAlarmVisible;
      if (alarmVisible) {
        alarmInterval = setInterval(_blinkAlarm, 200);
      }
      drawIcons();
      repaint();
    }

    function _setTrendVisible(nTrendVisible) {
      trendVisible = nTrendVisible;
      drawIcons();
      repaint();
    }

    function _setCycleSpeed(nCycleSpeed) {
      if (cycleSpeed != nCycleSpeed) {
        if ( cycleInterval != null ) {
          clearInterval(cycleInterval);
        }
        cycleSpeed = nCycleSpeed;
        cycleInterval = setInterval(_cycle, cycleSpeed);
      }
    }

    function refreshDatas() {
      if ( typeof odatas != 'undefined' && odatas instanceof Array ) {
        resetDatas();
        if ((cycleCounter < odatas.length) && (odatas.length > 0)) {
          minValue = odatas[cycleCounter]['min'];
          maxValue = odatas[cycleCounter]['max'];
          formerValue = odatas[cycleCounter]['value'];
          value = odatas[cycleCounter]['value'];
          unit = odatas[cycleCounter]['unit'];
          lowerRightText = odatas[cycleCounter]['sub_unit'];
          lowerRightTextVisible = odatas[cycleCounter]['show_sub_unit'];
          upperLeftTextVisible = odatas[cycleCounter]['show_min_max'];
          upperRightTextVisible = odatas[cycleCounter]['show_min_max'];
          lowerCenterText = odatas[cycleCounter]['title'];
          _setBattery(odatas[cycleCounter]['battery']);
          trend = odatas[cycleCounter]['trend'];
          _setTrendVisible(odatas[cycleCounter]['show_trend']);
          _setAlarmVisible(odatas[cycleCounter]['show_alarm']);
          signalStrength = odatas[cycleCounter]['signal'];
          decimals = odatas[cycleCounter]['decimals'];
          batteryVisible = true;
          lowerCenterTextVisible = true;
          drawIcons();
          drawText();
          repaint();
        }
      }
    };

    function resetDatas() {
      minValue = 0;
      maxValue = 0;
      formerValue = 0;
      value = 0;
      unit = '';
      lowerRightText = '';
      lowerRightTextVisible = false;
      upperLeftText = '';
      upperLeftTextVisible = false;
      upperRightText = '';
      upperRightTextVisible = false;
      lowerCenterText = '';
      lowerCenterTextVisible = false;
      _setBattery('full');
      batteryVisible = false;
      trend = '';
      trendVisible = false;
      _setAlarmVisible(false);
      signalVisible = true;
      signalStrength = 1 ;
      decimals = 1;
    };

    function getDatas() {
      if (qDevice!='') {
        var http = new XMLHttpRequest();
        var params = 'action=' + postAction;
        params = params+'&device_id='+qDevice;
        params = params+'&module_id='+qModule;
        params = params+'&measure_type='+qMeasure;
        http.open('POST', postUrl, true);
        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        http.onreadystatechange = function () {
          if (http.readyState == 4 && http.status == 200) {
            odatas = JSON.parse(http.responseText);
            if ( typeof odatas != 'undefined' && odatas instanceof Array ) {
              maxCycleCounter = odatas.length-1;
              refreshDatas();
              if (autoRefresh) {
                if ( refreshInterval != null ) {
                  clearInterval(refreshInterval);
                }
                refreshInterval = setInterval(getDatas, refreshSpeed);
              }
            }
            else {
              maxCycleCounter = -1;
            }
          }
        }
        http.send(params);
      }
    };

    // ******************** public methods ************************************
    this.getUpperCenterText = function() { return upperCenterText; };
    this.setUpperCenterText = function(nUpperCenterText) {
      upperCenterText = nUpperCenterText;
      drawText();
      repaint();
    };

    this.isUpperCenterTextVisible = function() { return upperCenterTextVisible; };
    this.setUpperCenterTextVisible = function(nUpperCenterTextVisible) {
      upperCenterTextVisible = nUpperCenterTextVisible;
      drawText();
      repaint();
    };

    this.getUnit = function() { return unit; };
    this.setUnit = function(nUnit) {
      unit = nUnit;
      drawText();
      repaint();
    };

    this.isUnitVisible = function() { return unitVisible; };
    this.setUnitVisible = function(nUnitVisible) {
      unitVisible = nUnitVisible;
      drawText();
      repaint();
    };

    this.getLowerRightText = function() { return lowerRightText; };
    this.setLowerRightText = function(nLowerRightText) {
      lowerRightText = text;
      drawText();
      repaint();
    };

    this.isLowerRightTextVisible = function() { return lowerRightTextVisible; };
    this.setLowerRightTextVisible = function(nLowerRightTextVisible) {
      lowerRightTextVisible = nLowerRightTextVisible;
      drawText();
      repaint();
    };

    this.getMinValue = function() { return minValue; };
    this.setMinValue = function(nMinValue) {
      minValue = nMinValue;
      drawText();
      repaint();
    };

    this.getMaxValue = function() { return maxValue; };
    this.setMaxValue = function(nMaxValue) {
      maxValue = nMaxValue;
      drawText();
      repaint();
    };

    this.getFormerValue = function() { return formerValue; };
    this.setFormerValue = function(nFormerValue) {
      formerValue = nFormerValue;
      drawText();
      repaint();
    };

    this.getValue = function() {
      return value;
    };
    this.setValue = function(nValue) {
      formerValue = value;
      value = parseFloat(nValue);
      if (animated) {
        var tween = new Tween(new Object(), '', Tween.regularEaseInOut, formerValue, value, duration);
        tween.onMotionChanged = function(event) {
          value = event.target._pos;
          drawText();
          repaint();
        };
        tween.onMotionFinished = function(event) {
          value = event.target._pos;
          setValues();
          drawText();
          drawIcons();
          repaint();
        };
        tween.start();
      }
      else {
        setValues();
        drawText();
        drawIcons();
        repaint();
      }
    };

    this.getDecimals = function() { return decimals; };
    this.setDecimals = function(nDecimals) {
      decimals = clamp(0, 6, nDecimals);
      drawText();
      repaint();
    };

    this.getUpperLeftText = function() { return upperLeftText; };
    this.setUpperLeftText = function(nUpperLeftText) {
      upperLeftText = nUpperLeftText;
      drawText();
      repaint();
    };
    
    this.isUpperLeftTextVisible = function() { return upperLeftTextVisible; };
    this.setUpperLeftTextVisible = function(nUpperLeftTextVisible) {
      upperLeftTextVisible = nUpperLeftTextVisible;
      drawText();
      repaint();
    };

    this.getUpperRightText = function() { return upperRightText; };
    this.setUpperRightText = function(nUpperRightText) {
      upperRightText = nUpperRightText;
      drawText();
      repaint();
    };

    this.isUpperRightTextVisible = function() { return upperRightTextVisible; };
    this.setUpperRightTextVisible = function(nUpperRightTextVisible) {
      upperRightTextVisible = nUpperRightTextVisible;
      drawText();
      repaint();
    };

    this.getLowerCenterText = function() { return lowerCenterText; };
    this.setLowerCenterText = function(nLowerCenterText) {
      lowerCenterText = nLowerCenterText;
      drawText();
      repaint();
    };

    this.isLowerCenterTextVisible = function() { return lowerCenterTextVisible; };
    this.setLowerCenterTextVisible = function(nLowerCenterTextVisible) {
      lowerCenterTextVisible = nLowerCenterTextVisible;
      drawText();
      repaint();
    };

    this.isFormerValueVisible = function() { return formerValueVisible; };
    this.setFormerValueVisible = function(nFormerValueVisible) {
      formerValueVisible = nFormerValueVisible;
      drawText();
      repaint();
    };

    this.getBattery = function() { return battery; };
    this.setBattery = function(nBattery) {
      _setBattery(nBattery);
    };

    this.isBatteryVisible = function() { return batteryVisible; };
    this.setBatteryVisible = function(nBatteryVisible) {
      batteryVisible = nBatteryVisible;
      drawIcons();
      repaint();
    };

    this.getCycleSpeed = function() { return cycleSpeed; };
    this.setCycleSpeed = function(nCycleSpeed) {
      _setCycleSpeed(nCycleSpeed);
    };

    this.getTrend = function() { return trend; };
    this.setTrend = function(nTrend) {
      trend = nTrend;
      drawIcons();
      repaint();
    };

    this.isTrendVisible = function() { return trendVisible; };
    this.setTrendVisible = function(nTrendVisible) {
      trendVisible = nTrendVisible;
      drawIcons();
      repaint();
    };

    this.isAlarmVisible = function() { return alarmVisible; };
    this.setAlarmVisible = function(nAlarmVisible) {
      _setAlarmVisible(nAlarmVisible);
    };

    this.isSignalVisible = function() { return signalVisible; };
    this.setSignalVisible = function(nSignalVisible) {
      signalVisible = nSignalVisible;
      drawIcons();
      repaint();
    };

    this.getSignalStrength = function() { return signalStrength; };
    this.setSignalStrength = function(nSignalStrength) {
      signalStrength = clamp(0, 1, nSignalStrength);
      drawIcons();
      repaint();
    };

    this.getWidth = function() { return width; };
    this.setWidth = function(nWidth) {
      width       = nWidth;
      aspectRatio = height / width;
      onResize();
    };

    this.getHeight = function() { return height; };
    this.setHeight = function(nHeight) {
      height      = nHeight;
      aspectRatio = height / width;
      onResize();
    };

    this.isScalable = function() { return scalable; };
    this.setScalable = function(nScalable) {
      scalable = nScalable;
      if (scalable) {
        window.addEventListener("resize", onResize, false);
      }
      else {
        window.removeEventListener("resize", onResize, false);
      }
    };

    this.getSize = function() { return size; };
    this.setSize = function(nSize, nScalable) {
      size = nSize;
      if (size === 'medium') {
        width = 364;
        height = 140;
      }
      else if (size === 'large') {
        width = 480;
        height = 185;
      }
      else {
        width = 260;
        height = 100;
        size = 'small';
      }
      aspectRatio = height / width;
      this.setScalable(nScalable);
      onResize();
    };

    this.getDesign = function() { return design; };
    this.setDesign = function(nDesign) {
      design = nDesign;
      onResize();
    };

    this.isAnimated = function() { return animated; };
    this.setAnimated = function(nAnimated) { animated = nAnimated; };

    this.getDuration = function() { return duration; };
    this.setDuration = function(nDuration) {
      duration = clamp(0, 10, nDuration);
    };

    this.getDevice = function() { return qDevice; };
    this.setDevice = function(nDevice) {
      qDevice = nDevice;
      getDatas();
    };

    this.getModule = function() { return qModule; };
    this.setModule = function(nModule) {
      qModule = nModule;
      getDatas();
    };

    this.getMeasure = function() { return qMeasure; };
    this.setMeasure = function(nMeasure) {
      qMeasure = nMeasure;
      getDatas();
    };

    this.resetMinmaxValue = function() {
      minValue = value;
      maxValue = value;
      drawText();
      repaint();
    };

    this.initValues = function(newValue, newMinValue, newMaxValue) {
      minValue = newMinValue;
      maxValue = newMaxValue;
      value = newMinValue;
      this.setValue(newValue);
      drawText();
      drawIcons();
      repaint();
    };
    this.setSize(size, scalable);
    getDatas();
    cycleInterval = setInterval(_cycle, cycleSpeed);
    return this;
  };

  // Tools
  var point = function(parameters) {
    var param  = parameters || {};
    this.start = param.x || 0;
    this.stop  = param.y || 0;
  };
  point.prototype = {
    getX       : function() { return this.x; },
    setX       : function(x) { this.x = x; },
    getY       : function() { return this.y; },
    setY       : function(y) { this.y = y; },
    distanceTo : function(point) {
      return Math.sqrt(((this.x - point.getX()) * (this.x - point.getX())) + ((this.y - point.getY()) * (this.y - point.getY())));
    }
  };

  var section = function(parameters) {
    var param  = parameters || {};
    this.start = param.start || 0;
    this.stop  = param.stop || 0;
    this.text  = param.text || '';
    this.color = param.color || 'rgb(200, 100, 0)';
    this.image = param.image || '';
  };
  section.prototype = {
    getStart : function() {
      return this.start;
    },
    setStart : function(start) {
      this.start = start;
    },
    getStop  : function() {
      return this.stop;
    },
    setStop  : function(stop) {
      this.stop = stop;
    },
    getColor : function() {
      return this.color;
    },
    setColor : function(color) {
      this.color = color;
    },
    getImage : function() {
      return this.image;
    },
    setImage : function(image) {
      this.image = image;
    },
    contains : function(value) {
      return (value >= this.start && value <= this.stop);
    }
  };

  var marker = function(parameters) {
    var param = parameters || {};
    this.value = param.value || 0;
    this.text = param.text || '';
    this.color = param.color || 'rgb(255, 0, 0)';
    this.exceeded = false;
  };
  marker.prototype = {
    getValue: function() {
      return this.value;
    },
    setValue: function(value) {
      this.value = value;
    },
    getText: function() {
      return this.text;
    },
    setText: function(text) {
      this.text = text;
    },
    getColor: function() {
      return this.color;
    },
    setColor: function(color) {
      this.color = color;
    }
  };

  var color = function(parameters) {
    var param = parameters || {};
    this.red = param.red || 0;
    this.green = param.green || 0;
    this.blue = param.blue || 0;
    this.opacity = param.opacity || 1;
  };
  color.prototype = {
    getRed: function() {
      return this.red;
    },
    setRed: function(red) {
      this.red = red;
    },
    getGreen: function() {
      return this.green;
    },
    setGreen: function(green) {
      this.green = green;
    },
    getBlue: function() {
      return this.blue;
    },
    setBlue: function(blue) {
      this.blue = blue;
    },
    getOpacity: function() {
      return this.opacity;
    },
    setOpacity: function(opacity) {
      this.opacity = opacity;
    },
    get: function() {
      return this;
    },
    getRgb: function() {
      return 'rgb(' + this.red + ',' + this.green + ',' + this.blue + ')'
    },
    getArgb: function() {
      return 'argb(' + this.opacity + ',' + this.red + ',' + this.green + ',' + this.blue + ')'
    }
  };

  var stop = function(parameters) {
    var param = parameters || {};
    this.offset = param.offset || 0;
    this.color = param.color || new enzo.Color();
  };
  stop.prototype = {
    getOffset: function() {
      return this.offset;
    },
    setOffset: function(offset) {
      this.offset = offset;
    },
    getColor: function() {
      return this.color;
    },
    setColor: function(color) {
      this.color = color;
    }
  };

  var gradientLookup = function(stops) {
    this.stops = stops;
  };
  gradientLookup.prototype = {
    getColorAt: function(positionOfColor) {
      var position = positionOfColor < 0 ? 0 : (positionOfColor > 1 ? 1 : positionOfColor);
      var color;
      if (this.stops.length === 1) {
        if (this.stops[0].stop === undefined)
          return new enzo.Color();
        color = this.stops[0].stop.getColor().get();
      } else {
        var lowerBound = this.stops[0].stop;
        var upperBound = this.stops[this.stops.length - 1].stop;
        for (var i = 0; i < this.stops.length; i++) {
          var offset = this.stops[i].stop.getOffset();
          if (offset < position) {
            lowerBound = this.stops[i].stop;
          }
          if (offset > position) {
            upperBound = this.stops[i].stop;
            break;
          }
        }
        color = this.interpolateColor(lowerBound, upperBound, position);
      }
      return color;
    },
    interpolateColor: function(lowerBound, upperBound, position) {
      var pos = (position - lowerBound.getOffset()) / (upperBound.getOffset() - lowerBound.getOffset());

      var deltaRed = (upperBound.getColor().getRed() - lowerBound.getColor().getRed()) * 0.00392 * pos;
      var deltaGreen = (upperBound.getColor().getGreen() - lowerBound.getColor().getGreen()) * 0.00392 * pos;
      var deltaBlue = (upperBound.getColor().getBlue() - lowerBound.getColor().getBlue()) * 0.00392 * pos;
      var deltaOpacity = (upperBound.getColor().getOpacity() - lowerBound.getColor().getOpacity()) * pos;

      var red = parseInt((lowerBound.getColor().getRed() * 0.00392 + deltaRed) * 255);
      var green = parseInt((lowerBound.getColor().getGreen() * 0.00392 + deltaGreen) * 255);
      var blue = parseInt((lowerBound.getColor().getBlue() * 0.00392 + deltaBlue) * 255);
      var opacity = lowerBound.getColor().getOpacity() + deltaOpacity;

      red = red < 0 ? 0 : (red > 255 ? 255 : red);
      green = green < 0 ? 0 : (green > 255 ? 255 : green);
      blue = blue < 0 ? 0 : (blue > 255 ? 255 : blue);
      opacity = opacity < 0 ? 0 : (opacity > 255 ? 255 : opacity);

      return new enzo.Color({red: red, green: green, blue: blue, opacity: opacity});
    }
  };

  function deriveColor(color, offset) {
    if (offset < 0)
      offset = 0;
    if (offset > 100)
      offset = 100;
  }

  function deriveColor(color, percent) {
    var num;
    if (color.indexOf('#') > -1) {
      num = parseInt(color.slice(1), 16);
    } else {
      num = parseInt(color, 16);
    }
    var amt = Math.round(2.55 * percent);
    var R = (num >> 16) + amt;
    var G = (num >> 8 & 0x00FF) + amt;
    var B = (num & 0x0000FF) + amt;

    return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 + (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 + (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
  }

  function deriveHexColor(hex, percent) {
    // validate hex string
    hex = String(hex).replace(/[^0-9a-f]/gi, '');
    if (hex.length < 6) {
      hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    percent = percent || 0;

    // convert to decimal and change luminosity
    var rgb = "#", c, i;
    for (i = 0; i < 3; i++) {
      c = parseInt(hex.substr(i * 2, 2), 16);
      c = Math.round(Math.min(Math.max(0, c + (c * percent)), 255)).toString(16);
      rgb += ("00" + c).substr(c.length);
    }
    return rgb;
  }

  function getSmoothingOffset(calculatedLineWidth) {
    var translate = (size * 0.0055 % 2) / 2;
    // To get crisp drawings do
    // ctx.translate(translate, translate);
    // before drawing lines
    // and
    // ctx.translate(-translate, -translate);
    // when finished drawing
    return translate;
  }

  Math.radians = function(degrees) { return degrees * Math.PI / 180; };
  Math.degrees = function(radians) { return radians * 180 / Math.PI; };

  // Tweening functionality
  function Delegate() {}
  Delegate.create = function(o, f) {
    var a = new Array();
    var l = arguments.length;
    for (var i = 2; i < l; i++)
      a[i - 2] = arguments[i];
    return function() {
      var aP = [ ].concat(arguments, a);
      f.apply(o, aP);
    };
  };

  var Tween = function(obj, prop, func, begin, finish, duration, suffixe) {
    this.init(obj, prop, func, begin, finish, duration, suffixe);
  };
  var t = Tween.prototype;

  t.obj = new Object();
  t.prop = '';
  t.func = function(t, b, c, d) {
    return c * t / d + b;
  };
  t.begin = 0;
  t.change = 0;
  t.prevTime = 0;
  t.prevPos = 0;
  t.looping = false;
  t._duration = 0;
  t._time = 0;
  t._pos = 0;
  t._position = 0;
  t._startTime = 0;
  t._finish = 0;
  t.name = '';
  t.suffixe = '';
  t._listeners = new Array();
  t.setTime = function(t) {
    this.prevTime = this._time;
    if (t > this.getDuration()) {
      if (this.looping) {
        this.rewind(t - this._duration);
        this.update();
        this.broadcastMessage('onMotionLooped', {target: this, type: 'onMotionLooped'});
      } else {
        this._time = this._duration;
        this.update();
        this.stop();
        this.broadcastMessage('onMotionFinished', {target: this, type: 'onMotionFinished'});
      }
    } else if (t < 0) {
      this.rewind();
      this.update();
    } else {
      this._time = t;
      this.update();
    }
  };
  t.getTime = function() {
    return this._time;
  };
  t.setDuration = function(d) {
    this._duration = (d === null || d <= 0) ? 100000 : d;
  };
  t.getDuration = function() {
    return this._duration;
  };
  t.setPosition = function(p) {
    this.prevPos = this._pos;
    var a = this.suffixe !== '' ? this.suffixe : '';
    this.obj[this.prop] = Math.round(p) + a;
    this._pos = p;
    this.broadcastMessage('onMotionChanged', {target: this, type: 'onMotionChanged'});
  };
  t.getPosition = function(t) {
    if (t === undefined)
      t = this._time;
    return this.func(t, this.begin, this.change, this._duration);
  };
  t.setFinish = function(f) {
    this.change = f - this.begin;
  };
  t.getFinish = function() {
    return this.begin + this.change;
  };
  t.init = function(obj, prop, func, begin, finish, duration, suffixe) {
    if (!arguments.length)
      return;
    this._listeners = new Array();
    this.addListener(this);
    if (suffixe)
      this.suffixe = suffixe;
    this.obj = obj;
    this.prop = prop;
    this.begin = begin;
    this._pos = begin;
    this.setDuration(duration);
    if (func !== null && func !== '') {
      this.func = func;
    }
    this.setFinish(finish);
  };
  t.start = function() {
    this.rewind();
    this.startEnterFrame();
    this.broadcastMessage('onMotionStarted', {target: this, type: 'onMotionStarted'});
    //alert('in');
  };
  t.rewind = function(t) {
    this.stop();
    this._time = (t === undefined) ? 0 : t;
    this.fixTime();
    this.update();
  };
  t.fforward = function() {
    this._time = this._duration;
    this.fixTime();
    this.update();
  };
  t.update = function() {
    this.setPosition(this.getPosition(this._time));
  };
  t.startEnterFrame = function() {
    this.stopEnterFrame();
    this.isPlaying = true;
    this.onEnterFrame();
  };
  t.onEnterFrame = function() {
    if (this.isPlaying) {
      this.nextFrame();
      // To get real smooth movement you have to set the timeout to 0 instead of 25
      setTimeout(Delegate.create(this, this.onEnterFrame), 25);
    }
  };
  t.nextFrame = function() {
    this.setTime((this.getTimer() - this._startTime) / 1000);
  };
  t.stop = function() {
    this.stopEnterFrame();
    this.broadcastMessage('onMotionStopped', {target: this, type: 'onMotionStopped'});
  };
  t.stopEnterFrame = function() {
    this.isPlaying = false;
  };

  t.playing = function() {
    return isPlaying();
  };

  t.continueTo = function(finish, duration) {
    this.begin = this._pos;
    this.setFinish(finish);
    if (this._duration !== undefined)
      this.setDuration(duration);
    this.start();
  };
  t.resume = function() {
    this.fixTime();
    this.startEnterFrame();
    this.broadcastMessage('onMotionResumed', {target: this, type: 'onMotionResumed'});
  };
  t.yoyo = function() {
    this.continueTo(this.begin, this._time);
  };

  t.addListener = function(o) {
    this.removeListener(o);
    return this._listeners.push(o);
  };
  t.removeListener = function(o) {
    var a = this._listeners;
    var i = a.length;
    while (i--) {
      if (a[i] === o) {
        a.splice(i, 1);
        return true;
      }
    }
    return false;
  };
  t.broadcastMessage = function() {
    var arr = new Array();
    for (var i = 0; i < arguments.length; i++) {
      arr.push(arguments[i]);
    }
    var e = arr.shift();
    var a = this._listeners;
    var l = a.length;
    for (var i = 0; i < l; i++) {
      if (a[i][e])
        a[i][e].apply(a[i], arr);
    }
  };
  t.fixTime = function() {
    this._startTime = this.getTimer() - this._time * 1000;
  };
  t.getTimer = function() {
    return new Date().getTime() - this._time;
  };
  Tween.backEaseIn = function(t, b, c, d, a, p) {
    if (s === undefined)
      var s = 1.70158;
    return c * (t /= d) * t * ((s + 1) * t - s) + b;
  };
  Tween.backEaseOut = function(t, b, c, d, a, p) {
    if (s === undefined)
      var s = 1.70158;
    return c * ((t = t / d - 1) * t * ((s + 1) * t + s) + 1) + b;
  };
  Tween.backEaseInOut = function(t, b, c, d, a, p) {
    if (s === undefined)
      var s = 1.70158;
    if ((t /= d / 2) < 1)
      return c / 2 * (t * t * (((s *= (1.525)) + 1) * t - s)) + b;
    return c / 2 * ((t -= 2) * t * (((s *= (1.525)) + 1) * t + s) + 2) + b;
  };
  Tween.elasticEaseIn = function(t, b, c, d, a, p) {
    if (t === 0)
      return b;
    if ((t /= d) === 1)
      return b + c;
    if (!p)
      p = d * .3;
    if (!a || a < Math.abs(c)) {
      a = c;
      var s = p / 4;
    } else
      var s = p / (2 * Math.PI) * Math.asin(c / a);

    return -(a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;

  };
  Tween.elasticEaseOut = function(t, b, c, d, a, p) {
    if (t === 0)
      return b;
    if ((t /= d) === 1)
      return b + c;
    if (!p)
      p = d * .3;
    if (!a || a < Math.abs(c)) {
      a = c;
      var s = p / 4;
    } else
      var s = p / (2 * Math.PI) * Math.asin(c / a);
    return (a * Math.pow(2, -10 * t) * Math.sin((t * d - s) * (2 * Math.PI) / p) + c + b);
  };
  Tween.elasticEaseInOut = function(t, b, c, d, a, p) {
    if (t === 0)
      return b;
    if ((t /= d / 2) === 2)
      return b + c;
    if (!p)
      var p = d * (.3 * 1.5);
    if (!a || a < Math.abs(c)) {
      var a = c;
      var s = p / 4;
    } else
      var s = p / (2 * Math.PI) * Math.asin(c / a);
    if (t < 1)
      return -.5 * (a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;
    return a * Math.pow(2, -10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p) * .5 + c + b;
  };

  Tween.bounceEaseOut = function(t, b, c, d) {
    if ((t /= d) < (1 / 2.75)) {
      return c * (7.5625 * t * t) + b;
    } else if (t < (2 / 2.75)) {
      return c * (7.5625 * (t -= (1.5 / 2.75)) * t + .75) + b;
    } else if (t < (2.5 / 2.75)) {
      return c * (7.5625 * (t -= (2.25 / 2.75)) * t + .9375) + b;
    } else {
      return c * (7.5625 * (t -= (2.625 / 2.75)) * t + .984375) + b;
    }
  };
  Tween.bounceEaseIn = function(t, b, c, d) {
    return c - Tween.bounceEaseOut(d - t, 0, c, d) + b;
  };
  Tween.bounceEaseInOut = function(t, b, c, d) {
    if (t < d / 2)
      return Tween.bounceEaseIn(t * 2, 0, c, d) * .5 + b;
    else
      return Tween.bounceEaseOut(t * 2 - d, 0, c, d) * .5 + c * .5 + b;
  };

  Tween.strongEaseInOut = function(t, b, c, d) {
    return c * (t /= d) * t * t * t * t + b;
  };

  Tween.regularEaseIn = function(t, b, c, d) {
    return c * (t /= d) * t + b;
  };
  Tween.regularEaseOut = function(t, b, c, d) {
    return -c * (t /= d) * (t - 2) + b;
  };

  Tween.regularEaseInOut = function(t, b, c, d) {
    if ((t /= d / 2) < 1)
      return c / 2 * t * t + b;
    return -c / 2 * ((--t) * (t - 2) - 1) + b;
  };
  Tween.strongEaseIn = function(t, b, c, d) {
    return c * (t /= d) * t * t * t * t + b;
  };
  Tween.strongEaseOut = function(t, b, c, d) {
    return c * ((t = t / d - 1) * t * t * t * t + 1) + b;
  };

  Tween.strongEaseInOut = function(t, b, c, d) {
    if ((t /= d / 2) < 1)
      return c / 2 * t * t * t * t * t + b;
    return c / 2 * ((t -= 2) * t * t * t * t + 2) + b;
  };


  return {
    LCDPanel : lcdPanel
  };

}());
