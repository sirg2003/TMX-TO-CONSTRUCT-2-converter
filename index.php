<?
/**
 * Project:      PHP TMX-TO-CONSTRUCT 2 (TMX Map parser for Scirra Construct 2)
 * File:         index.php
 * @version      1.2
 * @copyright    Copyright (C) 2012 Sir_G
 * @link         http://tmx.petislands.ru
 * @link         http://danneomania.ru
 */

@ini_set('display_errors', 1);
//@ini_set('upload_max_filesize', '3M');
//@ini_set('post_max_size', '3M');
// for testing:
$POST_MAX_SIZE = ini_get('post_max_size');
//echo '$POST_MAX_SIZE: '.$POST_MAX_SIZE.'<br />';
//$memory_limit = ini_get('memory_limit');
//echo '$memory_limit: '.$memory_limit;
/**
 * Constants
 */
define('CBASE', dirname(__FILE__).'/');
define('CREAD', 1);
/**
 * require
 */
require_once(CBASE.'Lib/functions.php');
/**
 * $_REQUEST
 */
$act = (isset($todo) && sitedn($todo)=='generate') ? 'generate' : 'form';

echo template_header();

if($act=='form'){

echo '<form id="fileupload" action="/index.php" method="POST" enctype="multipart/form-data" class="form-horizontal">
        <div class="row">
    <div class="span9">
        <fieldset>
          <legend>Upload project files</legend>
          <div class="control-group">
            <label class="control-label">JSON-file</label>
            <div class="controls docs-input-sizes">
              <input class="span3" type="file" name="json" maxlength="255">
              <div class="alert alert-info">Please, load only *.json file exported from Tiled Editor</div>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Tileset file</label>
            <div class="controls docs-input-sizes">
              <input class="span3" type="file" name="image" maxlength="255">
              <!-- <div class="alert alert-info">Please, load only *.png file</div> -->
            </div>
          </div>
          <div class="form-actions">
            <input name="todo" value="generate" type="hidden" />
            <button type="submit" class="btn btn-success"><i class="icon-upload icon-white"></i> Start upload</button>
          </div>
        </fieldset>
    </div>

        </div>
    </form>

';
 echo template_footer();exit();
}

if($act=='generate'){
  $proj_time = mktime();
  mkdir('tmp/'.$proj_time); // temp directory

    if(isset($_FILES['json']) && !empty($_FILES['json']['tmp_name'])){
        $filename_old = strtolower(trim($_FILES['json']['name']));
        $filename = $proj_time+8800;
        $tmpname = $_FILES['json']['tmp_name'];
        $fileinfo = pathinfo($filename_old);
            $obj = strtolower($fileinfo['extension']);
            if (preg_match("#^(json)$#i",$obj)) {
                if (file_exists(CBASE.'tmp/'.$proj_time.'/'.$filename.'.json')) {
                    $filename = 'copy_'.$filename.'.json';
                }
                move_uploaded_file($tmpname,CBASE.'tmp/'.$proj_time.'/'.$filename.'.json');
            } else {
              rrmdir('tmp/'.$proj_time);
              echo '<div class="alert alert-error"><h4 class="alert-heading">Error!</h4>This is not a JSON-file!<div class="pagination-centered"><button onclick="javascript:history.go(-1)" class="btn btn-success">Go back</button></div></div>';
              echo template_footer();exit();
            }
    } else {
      rrmdir('tmp/'.$proj_time);
      echo '<div class="alert alert-error"><h4 class="alert-heading">Error!</h4>Choose JSON-file!<div class="pagination-centered"><button onclick="javascript:history.go(-1)" class="btn btn-success">Go back</button></div></div>';
      echo template_footer();exit();
    }


 $f_tmp = $_FILES['image']['tmp_name']; // временный файл изображения
 if($f_tmp){
  try {
   include(CBASE.'Lib/phpthumb/ThumbLib.inc.php');
   $thumb = PhpThumbFactory::create($f_tmp); // обработка временного файла изображения
  }
  catch (Exception $e){ // в случае ошибки получаем текст ошибки и выводим страницу с ошибкой
      rrmdir(CBASE.'tmp/'.$proj_time);
      echo '<div class="alert alert-error"><h4 class="alert-heading">Error!</h4>Error: '.$e->getMessage().'<div class="pagination-centered"><button onclick="javascript:history.go(-1)" class="btn btn-success">Go back</button></div></div>';
      echo template_footer();exit();
  }
 } else {
      rrmdir(CBASE.'tmp/'.$proj_time);
      echo '<div class="alert alert-error"><h4 class="alert-heading">Error!</h4>Choose Tileset-file!<div class="pagination-centered"><button onclick="javascript:history.go(-1)" class="btn btn-success">Go back</button></div></div>';
      echo template_footer();exit();
 }

 



  echo '<div class="alert alert-success">TMX export start =)</div>';

// JSON loaded
$json_file = CBASE.'tmp/'.$proj_time.'/'.$filename.'.json';
$decoded = json_decode(file_get_contents($json_file), true);


// todo: support transparency layers

require_once(CBASE.'Lib/dZip.inc.php');

$column=$row=$count_x=$count_y=0;
$frames='';

$tileset_id=0; // td of tileset. currently only 1 tileset imported (1 image)

// Make .capx file fith name (current timestamp)
$newzip = new dZip(CBASE.'result/project_'.$proj_time.'.capx');



  // tile width and height (in current tileset)
  $tile_width = $decoded["tilewidth"];
  $tile_height = $decoded["tileheight"];

// current layer margin and spacing
$margin = $decoded["tilesets"][$tileset_id]["margin"];
$spacing = $decoded["tilesets"][$tileset_id]["spacing"];

  // catcth info from JSON
  // layer width and height
  $layer_width = $decoded["width"] * $decoded["tilewidth"];
  $layer_height = $decoded["height"] * $decoded["tileheight"];


// layer start at x and y
//$layer_x = $decoded["layers"][0]["x"];
//$layer_y = $decoded["layers"][0]["y"];


// instance name
  $instance = 'tileset_'.$decoded["tilesets"][$tileset_id]["firstgid"];

  // Create a folder in the ZIP, to store dZip files
  echo '<div class="alert alert-success">Creating folder for project</div>';
  $newzip->addDir('Event sheets');
  $newzip->addDir('Layouts');
  $newzip->addDir('Animations/'.$instance.'/Default');

  // нарезать тайлы с помощью редактирования изображения

  $tile_column = round($decoded["tilesets"][$tileset_id]["imagewidth"]/$tile_width);
  $tile_strok = round($decoded["tilesets"][$tileset_id]["imageheight"]/$tile_height);
  $count_tiles = $tile_column * $tile_strok;
  //echo 'columnov: '.$tile_column.';strok: '.$tile_strok.'<br />';

  mkdir(CBASE.'tmp/'.$proj_time.'/'.$instance);
  echo '<div class="alert alert-success">Tileset directory created</div>';
  $frames.='
                <animation framecount="'.$count_tiles.'" loop="0" name="Default" pingpong="0" repeatcount="0" repeatto="0" speed="0">';


  for($tileid=0;$tileid<$count_tiles;$tileid++){

    $left  = ($margin * $column) + ($tile_width * $column) + $spacing;
    $top = ($margin * $row) + ($tile_height * $row) + $spacing;

    $n=str_pad($tileid,'3',"0",STR_PAD_LEFT);

    // Make image
    $thumb = PhpThumbFactory::create($f_tmp); // обработка временного файла изображения
    $thumb->crop($left,$top,$tile_width,$tile_height)->save(CBASE.'tmp/'.$proj_time.'/'.$instance.'/'.$n.'.png', 'png');
    // Add image to zip
    $newzip->addFile(CBASE.'tmp/'.$proj_time.'/'.$instance.'/'.$n.'.png','Animations/'.$instance.'/Default/'.$n.'.png');

    $frames.='
                    <frame duration="1" hotspotX="0" hotspotY="0">
                        <collision-poly>
                            <point x="0" y="0" />
                            <point x="0" y="0" />
                            <point x="0" y="0" />
                            <point x="0" y="0" />
                            <point x="0" y="0" />
                            <point x="0" y="0" />
                        </collision-poly>
                    </frame>';
    if($column==$tile_column-1){$column=0;$row=$row+1;}else{$column = $column + 1;}
  }
  $frames.='</animation>';

$layer_id = 0;
$layers='';
foreach($decoded["layers"] as $layer_key => $layer_value){
  if($decoded["layers"][$layer_id]["type"]=='tilelayer'){
  // Adding tiles
  $column=$row=0;
  $column_all = $decoded["layers"][$layer_id]["width"];
  $transparent = ($layer_id==0) ? 0 : 1;
  $layers.='
        <layer name="'.$decoded["layers"][$layer_id]["name"].'">
            <initially-visible>1</initially-visible>
            <background-color>255,255,255</background-color>
            <transparent>'.$transparent.'</transparent>
            <parallax>
                <x>1</x>
                <y>1</y>
            </parallax>
            <zoom-rate>1</zoom-rate>
            <opacity>'.$decoded["layers"][$layer_id]["opacity"].'</opacity>
            <force-own-texture>0</force-own-texture>
            <instances>';

    foreach($decoded["layers"][$layer_id]["data"] as $key => $value){
      $left  = $tile_width * $column;
      $top = $tile_width * $row;
      $value = $value - 1;

      // если слой 0 - то создавать нулевые тайлы
      if($layer_id==0){
        $layers.='
                <instance type="'.$instance.'">
                    <properties>
                        <initial-visibility>Visible</initial-visibility>
                        <initial-frame>'.$value.'</initial-frame>
                        <effect>(none)</effect>
                    </properties>
                    <world>
                        <x>'.$left.'</x>
                        <y>'.$top.'</y>
                        <z>0</z>
                        <width>'.$tile_width.'</width>
                        <height>'.$tile_height.'</height>
                        <depth>0</depth>
                        <hotspotX>0</hotspotX>
                        <hotspotY>0</hotspotY>
                        <angle>0</angle>
                        <opacity>1</opacity>
                    </world>
                </instance>';
      } else {
      // parse only existing tiles
      if($value>0){
        $layers.='
                <instance type="'.$instance.'">
                    <properties>
                        <initial-visibility>Visible</initial-visibility>
                        <initial-frame>'.$value.'</initial-frame>
                        <effect>(none)</effect>
                    </properties>
                    <world>
                        <x>'.$left.'</x>
                        <y>'.$top.'</y>
                        <z>0</z>
                        <width>'.$tile_width.'</width>
                        <height>'.$tile_height.'</height>
                        <depth>0</depth>
                        <hotspotX>0</hotspotX>
                        <hotspotY>0</hotspotY>
                        <angle>0</angle>
                        <opacity>1</opacity>
                    </world>
                </instance>';
      } // end if
      }
      // если слой больше 0 - не создавать нулевые тайлы


      if($column==$column_all-1){$column=0;$row=$row+1;}else{$column = $column + 1;}
    } // end for each tile
  } // end if layertype = tilelayer
  $layers.= '
            </instances>
        </layer>';
  $layer_id++;
} // end for each layer

  // Make Layout file
  make_layout($layer_width,$layer_height,$layers);

  // Make Project file
  make_project($instance,$frames);
  echo '<div class="alert alert-success">Layouts created.Adding files to the zip</div>';

  $newzip->addFile(CBASE.'project/Event sheet 1.xml','Event sheets/Event sheet 1.xml');
  $newzip->addFile(CBASE.'project/Event sheet 1.uistate.xml','Event sheets/Event sheet 1.uistate.xml');
  $newzip->addFile(CBASE.'project/New project.uistate.xml','New project.uistate.xml');
  $newzip->addFile(CBASE.'project/Layout 1.uistate.xml','Layouts/Layout 1.uistate.xml');
  $newzip->addFile(CBASE.'tmp/'.$proj_time.'/layout.txt','Layouts/Layout 1.xml');
  $newzip->addFile(CBASE.'tmp/'.$proj_time.'/project.txt','New project.caproj');
// }
//}

  echo '<div class="alert alert-success">Finalizing the created file</div>';
 $newzip->save();

  echo '<div class="alert alert-success">Deleting temp folder</div>';
  // remove temp folder
  rrmdir(CBASE.'tmp/'.$proj_time);

  echo '<div class="alert alert-success">Done =) You can download your new project file <a class="btn btn-primary" href="/result/project_'.$proj_time.'.capx"><i class="icon-download-alt icon-white"></i>  by this link</a></div>';
  echo '<div class="alert alert-success">Do you want to <a class="btn btn-primary" href="/index.php">upload another files</a> ?</div>';
  echo template_footer();exit();
}
/**
 * exit
 */
exit();

?>