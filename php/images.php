<?php

class ImageInfo
{
  public readonly ?int $width;
  public readonly ?int $height;
  public readonly ?int $type;
  public readonly ?string $attribute;
  public readonly ?int $bit;
  public readonly ?int $channels;
  public readonly ?string $mime;

  public function __construct(string $filename)
  {
    $this->__set_values($filename);
  }

  /**
   * Undocumented function
   *
   * @param string $filename
   * @return void
   * @ignore description
   */
  private function __set_values($filename)
  {
    $value = @getimagesize($filename);

    if (!$value) {
      $this->{"width"}      = null;
      $this->{"height"}     = null;
      $this->{"type"}       = null;
      $this->{"attribute"}  = null;
      $this->{"bit"}        = null;
      $this->{"channels"}   = null;
      $this->{"mime"}       = null;
    } else {
      $this->{"width"}      = $value[0] ?: null;
      $this->{"height"}     = $value[1] ?: null;
      $this->{"type"}       = $value[2] ?: null;
      $this->{"attribute"}  = $value[3] ?: null;
      $this->{"bit"}        = $value['bits'] ?: null;
      $this->{"channels"}   = $value['channels'] ?: null;
      $this->{"mime"}       = $value['mime'] ?: null;
    }
    unset($value);
  }
}

final class ImageResizer
{
  private $allFileTypes = ['avif', 'bmp', 'gd2', 'gd2part', 'gd', 'gif', 'jpeg', 'jpg', 'png', 'string', 'tga', 'wbmp', 'webp', 'xbm', 'xpm'];
  /** @var ImageResizer */
  private static $instance = null;

  private function _getImageFunctions($image_type)
  {
    $fn_name = 'imagecreatefrom' . $image_type;
    $fn_name1 = 'image' . $image_type;

    if (!function_exists($fn_name)) {
      throw new Exception('Function: ' . $fn_name . ' not exists');
    }

    if (!function_exists($fn_name1)) {
      throw new Exception('Function: ' . $fn_name . ' not exists');
    }

    return [$fn_name, $fn_name1];
  }

  /**
   *
   *
   * @param string $filename
   * @param integer $width
   * @param integer $height
   * @param boolean $fullpath
   * @return string
   */
  public function resizeImage($filename, $width = 1024, $height = 0, $fullpath = false)
  {
    $path_info = pathinfo($filename);

    if (!in_array($path_info['extension'], $this->allFileTypes)) {
      throw new Exception('File Extention: ' . $path_info['extension'] . ' not supported.');
    }

    $imageInfo = new ImageInfo($filename);
    $type = explode('/', $imageInfo->mime)[1];

    $functions = $this->_getImageFunctions($type);

    $new_image = $functions[0]($filename);
    $new_height = $height;

    if ($height === 0) {
      $new_image = imagescale($new_image, $width);
      $new_height = imagesy($new_image);
    } else {
      $new_image = imagescale($new_image, $width, $height);
    }

    $new_name = "{$path_info['filename']}_{$width}x{$new_height}.{$path_info['extension']}";
    $path = dirname($filename);

    $saved_file = $path . DIRECTORY_SEPARATOR . $new_name;
    $functions[1]($new_image, $saved_file);

    if ($fullpath === false) {
      $saved_file = $new_name;
    }

    imagedestroy($new_image);
    unset($functions, $path_info, $new_name);

    return $saved_file;
    // ob_start();
    // // Output the image
    // imagejpeg($new_image); # , __DIR__ . DIRECTORY_SEPARATOR . $new_name);
    // $img = ob_get_clean();

    // imagedestroy($new_image);
    // imagedestroy($new_image);
    // imagedestroy($new_image);

    // echo sprintf('<img src="data:%s;base64, %s" />', $imageInfo->mime, base64_encode($img));

  }

  private function __construct()
  {
  }
  private function __clone()
  {
  }

  /**
   * prevent from being unserialized (which would create a second instance of it)
   */
  public function __wakeup()
  {
    throw new Exception("Cannot unserialize singleton");
  }

  /**
   *
   * @return ImageResizer
   */
  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }
}

$file = __DIR__ . DIRECTORY_SEPARATOR . "EWy-zNpUcAAa3mG.jpg";
$info =  new ImageInfo($file);
$ir = ImageResizer::getInstance();

echo $ir->resizeImage($file, 150) . PHP_EOL;
var_dump($info);

echo $info->mime;

// echo image_type_to_mime_type($info->type);
// # exit();
// $type = explode('/', $info->mime)[1];
// $fn = $ir->resizeImage($file);   # call_user_func('imagecreatefrom'.$type, $file);
// $im_php = $fn($file);   # call_user_func('imagecreatefrom'.$type, $file);
// $im_php = imagescale($im_php, 1024);
// $new_height = imagesy($im_php);
// $new_name = str_replace('-zNpUcAAa3mG', '-640x' . $new_height, basename( $file));

// ob_start();
// // Output the image
// imagejpeg($im_php); # , __DIR__ . DIRECTORY_SEPARATOR . $new_name);
// $img = ob_get_clean();

// imagedestroy($im_php);

// echo sprintf('<img src="data:%s;base64, %s" />', $info->mime, base64_encode($img));
/*
'gd'
'xpm'
'xbm'
'gd2'
'gif'
'png'
'tga'
'bmp'
'jpeg'
'wbmp'
'webp'
'avif'
'string'
'gd2part'

*/
