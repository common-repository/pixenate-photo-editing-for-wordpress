<?php

if ( defined('ABSPATH') )
    require_once( ABSPATH . 'wp-config.php');
else
    require_once('../../../wp-config.php');

require_once('../../../wp-admin/admin.php');
require_once('../../../wp-admin/includes/image.php');
require_once('../../../wp-includes/post.php');
require_once('includes/functions.php');

$pixenate_mode = "";

if (isset($_GET["mode"])){
    $pixenate_mode = $_GET["mode"];
}

$pixenate_image_url = $_GET["image"];

$pixenate_image_filepath = pixenate_url2filepath($pixenate_image_url);

if (strpos($pixenate_image_url,'localhost') && $pixenate_mode === "")
{
    if (function_exists('curl_init')){
        $ch = curl_init("http://pixenate.com/pixenate/upload.pl");
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    array('filename'=>"@$pixenate_image_filepath",
                          'pxn8_root' => '/pixenate',
                          'mode' => 'api'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pixenate_image_url = curl_exec($ch);
        curl_close($ch);
    }
}

$err = 'OK';

if ($pixenate_mode === "save")
{
    $image = pixenate_get_remote_url($pixenate_image_url);

    $attachment_id = $_GET["id"];

    if ($image){

        $filename = $_GET["filename"];

        if (!function_exists('file_put_contents')){
            $f = fopen($filename,'w');
            if ($f === false){
                $err = 'Could not save file.';
            }else{
                fwrite($f,$image);
                fclose($f);
            }
        }else{
            file_put_contents($filename, $image);
        }
        //
        // wph 20090115
        // must update thumbnails too !!!
        //
        $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $filename );

        wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
        
    }else{
        $err = 'The PHP Curl extension is required by this plugin or allow_url_fopen must be On. Please contact the site administrator.';
    }
}

?>
<html>
<head>

<link rel="stylesheet" href="css/flora/flora.all.css" type="text/css" media="screen" title="Flora (Default)">
<link rel="stylesheet" href="css/pixenate.css" type="text/css" media="screen" >


  <script type="text/javascript" src="js/jquery-1.2.1.js"></script>
  <script type="text/javascript" src="js/jquery.dimensions.js"></script>
  <script type="text/javascript" src="js/ui.mouse.js"></script>
  <script type="text/javascript" src="js/ui.draggable.js"></script>
  <script type="text/javascript" src="js/ui.resizable.js"></script>



  <script type="text/javascript" src="http://pixenate.com/h/wpjs"></script>
  <script type="text/javascript" src="bubbles.js"></script>
<script type="text/javascript">
function saveChanges()
{
       var changedImage = PXN8.getUncompressedImage();
       if (changedImage){
           var status = document.getElementById("pxn8_status");
           status.innerHTML = "Saving Image. Please wait...";

           var changedImageURL = PXN8.server + PXN8.root + "/" + changedImage;
           document.location ="editor.php?mode=save&id=<?php echo $_GET["id"]?>" + "&image=" + changedImageURL + "&filename=<?php echo $pixenate_image_filepath;?>";

       }else{
           window.close();
       }
}
</script>

<style type="text/css">
</style>
</head>
<body>
<?php
if ($pixenate_mode === "save") {
    //
    // close the current window - we've saved the changes
    //
   if ($err !== "OK"){ echo "<script type='text/javascript'>alert(\"An error occurred while saving: $err\");window.close();</script>"; }
   else { echo "<script type='text/javascript'>window.close();</script>"; }
}
?>
  <div id="header">
    <h1>Image Editing Plugin for WordPress (version 0.2)<a href="http://pixenate.com/productinfo/"><img border="0" src="poweredby.jpg"/></a>
    </h1>
  </div>

  <div id="editor">
    <ul id="toolbar">
      <li><button onclick="PXN8.tools.undo();return false;">Undo</button></li>
      <li><button onclick="PXN8.tools.redo();return false;">Redo</button></li>
      <li><button onclick="PXN8.tools.crop();return false;">Crop</button></li>
      <li><button onclick="PXN8.tools.rotate({angle:270});return false;">Rotate 270&deg;</button></li>
      <li><button onclick="PXN8.tools.rotate({angle:90});return false;">Rotate 90&deg;</button></li>
      <li><button onclick="PXN8.tools.rotate({fliphz:true});return false;">Flip</button></li>
      <li><button onclick="PXN8.tools.normalize();return false;">Fix Colors</button></li>
      <li><button onclick="PXN8.tools.fill_flash();return false;">Fill Flash</button></li>
      <li><button onclick="PXN8.tools.enhance();return false;">Enhance</button></li>
      <li><button onclick="PXN8.tools.sepia('#a28a65');return false;">Sepia</button></li>
      <li><button onclick="PXN8.tools.grayscale();return false;">B & W</button></li>
      <li><button onclick="bubble_mode();return false;">Speech Bubbles</button></li>
      <li><button class="next" onclick="saveChanges(); return false;">Save</button></li>
      <li><button class="next" onclick="window.close();">Cancel</button></li>
    </ul>

    <div id="pxn8_status"></div>

    <div id="bubble_div" style="display: none;">
      <ul class="horizontal">
        <li><img src="http://pixenate.com/pixenate/images/overlays/right_speech_bubble.gif" width="150" height="100"/></li>
        <li><img src="http://pixenate.com/pixenate/images/overlays/left_speech_bubble.gif" width="150" height="100"/></li>
        <li><img src="http://pixenate.com/pixenate/images/overlays/right_thought_bubble.gif" width="150" height="100"/></li>
        <li><img src="http://pixenate.com/pixenate/images/overlays/left_thought_bubble.gif" width="150" height="100"/></li>
      </ul>
      <div id="hint" style="clear: left;">
        <ol>
          <li>Drag and drop one of the above bubbles on to the photo.</li>
          <li>Click on the text in each bubble to change it.</li>
          <li>Add as many bubbles as you like.</li>
          <li>Click the Remove Icon <img src="delete.png"/> on each bubble to remove it.</li>
          <li>Click the Apply button when done.</li>
        </ol>
        <ul class="horizontal">
          <li><button onclick="add_bubbles();return false;">Apply</button></li>
          <li><button onclick="PXN8.tools.undo();return false;">Undo</button></li>
          <li><button onclick="add_bubbles();exit_bubble_mode();return false;">Done</button></li>
        </ul>
      </div>
    </div> 

    <div id="pxn8_canvas"></div>
  </div>


  <script type="text/javascript">
    PXN8.initialize("<?php echo $pixenate_image_url ; ?>?" + new Date().getTime());
  </script>

</body>
</html>


