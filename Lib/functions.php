<?
/**
 * Project:      PHP TMX-TO-CONSTRUCT 2 (TMX Map parser for Scirra Construct 2)
 * File:         index.php
 * @version      1.2
 * @copyright    Copyright (C) 2012 Sir_G
 * @link         http://tmx.petislands.ru
 * @link         http://danneomania.ru
 */
if (!defined('CREAD')) {
	exit();
}
/**
 * stripslashesall
 */
function stripslashesall(&$array) {
    reset($array);
    while (list($key, $val) = each($array)) {
        if (is_string($val)) {
        	$array[$key] = stripslashes($val);
        } elseif (is_array($val)) {
        	$array[$key] = stripslashesall($val);
        }
    }
    return $array;
}
/**
 * get_magic_quotes_gpc
 */
if (get_magic_quotes_gpc()) {
    if ($_POST) {
        $_POST = stripslashesall($_POST);
    }
    if ($_GET) {
    	$_GET = stripslashesall($_GET);
    }
    if ($_COOKIE) {
    	$_COOKIE = stripslashesall($_COOKIE);
    }
    if ($_REQUEST) {
    	$_REQUEST = stripslashesall($_REQUEST);
    }
}
/**
 * register_globals
 */
if(!ini_get('register_globals') || (@get_cfg_var('register_globals') == 1)){
    @extract($_COOKIE,EXTR_SKIP);
    @extract($_POST,EXTR_SKIP);
    @extract($_GET,EXTR_SKIP);
    @extract($_REQUEST,EXTR_SKIP);
}
$POST_MAX_SIZE = ini_get('post_max_size');
/**
 * function preparse
 */
function preparse($resursing, $type, $c = false){
    if ($type == THIS_INT) {
    	return (intval($resursing) > 0) ? intval($resursing) : 0;
    }
    if ($type == THIS_STRLEN) {
    	return strlen($resursing);
    }
    if ($type == THIS_TRIM) {
    	return trim($resursing);
    }
    if ($type == THIS_EMPTY) {
    	return (empty($resursing)) ? 1 : 0;
    }
    if ($type == THIS_SYMNUM) {
    	return $resursing = (!preg_match('/^[a-zA-Z0-9_-]+$/D',$resursing)) ? 1 : 0;
    }
}
    function sitedn($resursing)
    {
        return $resursing = (preg_match("/^[a-zA-Z0-9_]+$/D",$resursing)) ? substr($resursing,0,8) : '';
    }

/**
 * function make_layout
 */
function make_layout($width,$height,$layers){
  global $proj_time;
  $layout = '<?xml version="1.0" encoding="utf-8" ?>
<c2layout>
    <name>Layout 1</name>
    <event-sheet>Event sheet 1</event-sheet>
    <size>
        <width>'.$width.'</width>
        <height>'.$height.'</height>
    </size>
    <margins>
        <horizontal>100</horizontal>
        <vertical>100</vertical>
    </margins>
    <unbounded-scrolling>0</unbounded-scrolling>
    <layers>'.$layers.'
    </layers>
    <nonworld-instances />
</c2layout>';

  $html_write = @fopen(CBASE.'tmp/'.$proj_time.'/layout.txt', 'wb');
  fwrite($html_write,$layout);
  fclose($html_write);
}
/**
 * function make_project
 */
function make_project($instance,$frames){
  global $proj_time;
  $project = '<?xml version="1.0" encoding="utf-8" ?>
<c2project>
    <name>New project</name>
    <description></description>
    <version>1.0</version>
    <author></author>
    <unique-id>1lozo7z83yto1</unique-id>
    <saved-with-version>9000</saved-with-version>
    <used-plugins>
        <plugin id="Sprite" version="1">Sprite</plugin>
    </used-plugins>
    <used-behaviors />
    <configurations>
        <configuration exporter-descname="HTML5" exporter-id="html5" name="HTML5" />
    </configurations>
    <window-size>
        <width>640</width>
        <height>480</height>
    </window-size>
    <pixel-rounding>0</pixel-rounding>
    <configuration-settings>
        <prop name="Clear background">No</prop>
        <prop name="Enable WebGL">On</prop>
        <prop name="Fullscreen in browser">Off</prop>
        <prop name="Preview browser">(default)</prop>
        <prop name="Preview mode">HTTP</prop>
        <prop name="Sampling">Linear</prop>
    </configuration-settings>
    <object-folder expanded="1">
        <object-type name="'.$instance.'">
            <plugin id="Sprite" />
            <animation-folder expanded="1">'.$frames.'
            </animation-folder>
        </object-type>
    </object-folder>
    <families />
    <layout-folder expanded="1">
        <layout>Layout 1.xml</layout>
    </layout-folder>
    <event-folder expanded="1">
        <event-sheet>Event sheet 1.xml</event-sheet>
    </event-folder>
    <global-instances />
    <sounds-folder expanded="1" />
    <music-folder expanded="1" />
</c2project>';

  $html_write = @fopen(CBASE.'tmp/'.$proj_time.'/project.txt', 'wb');
  fwrite($html_write,$project);
  fclose($html_write);
}
//////////////////////////////////////////////////////////
# recursively remove a directory
function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file)){
            rrmdir($file);
        }else{
            unlink($file);
        }
    }
    rmdir($dir);
}


function template_header(){
  return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>TMX-TO-CONSTRUCT 2 Converter</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="/assets/css/bootstrap.css" rel="stylesheet">
<link href="/assets/css/bootstrap-responsive.css" rel="stylesheet">
<script src="/assets/js/jquery.js"></script></head>
<script src="/assets/js/bootstrap/bootstrap.js"></script>
<body>
<div class="container">
    <div class="page-header">
        <h1>TMX-TO-CONSTRUCT 2 converter</h1>
    </div>
    <blockquote>
        <p>Welcome to Tiled TMX Map converter for Scirra Construct 2<br />
        This converter for Scirra Construct 2 makes *.capx - blank project files with map from TMX Editor.<br />Current version: 1.1. This version:<br />
        <ul>
            <li>Support only JSON exported file for map</li>
            <li>Support only 1 tileset</li>
            <li>Converting only tileset layers (not objects!)</li>
            <li>Automatically converts tileset to a sprite with animation frames (automatically slice tiles)</li>
        </ul>
        Supports page: <a href="http://www.scirra.com/forum/app-tiled-tmx-map-converter_topic52905.html">here</a>.
    </blockquote>
    <br />';
}

function template_footer(){
	global $POST_MAX_SIZE;
  return '<div class="row">
    <div class="span6 well">
        <h3>Notes</h3>
        <ul>
            <li>The maximum file size for uploads is <strong>'.$POST_MAX_SIZE.' MB</strong>.</li>
            <li>Built with Twitter`s <a href="http://twitter.github.com/bootstrap/">Bootstrap</a> toolkit and Icons from <a href="http://glyphicons.com/">Glyphicons</a>.</li>
        </ul>
    </div>

    <div class="span9 well">
        <h3>Changelog</h3>
        <p>version 1.2:</p>
        <ul>
            <li>bugfix: margin and spacing of tiles</li>
            <li>bugfix: animation of tiles</li>
            <li>bugfix: only existing tiles placing on non-first layer</li>
        </ul>
        <p>version 1.1:</p>
        <ul>
            <li>Parsing all tileset layers</li>
            <li>Added layer opacity</li>
            <li>Layers has names from Tiled map</li>
        </ul>
    </div>

</div>
</div>
</body></html>';
}

?>