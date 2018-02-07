<?php
namespace App\Controller\Component;

use Aws\S3\S3Client;
use Cake\Controller\Component;
use Aws\S3\Exception\S3Exception;

class CommonComponent extends Component
{
  /**
   * @var array
   */
  public $components = array(
    'Amazon',
    'Resize',
  );

  /**
   * @var string
   */
  public $SECRET_KEY = "8EdPFjGq5yRbBg8udkjd7Ksql3KJMKHE7s8PBS4r";
  /**
   * @var string
   */
  public $KEY = "AKIAI6PX7ANLVPQNYZSA";

  /**
   * Amazon Upload functionality.
   *
   * @param integer $localpath_reviews
   * @param string $reviews_bucket
   * @param string $alt
   * @param string $fileName
   * @return @void
   */
  public function amazonUpload($localpath, $bucket, $fileName, $alt)
  {
    $s3 = S3Client::factory([
      'credentials' => [
        'key' => AWS_KEY,
        'secret' => AWS_SECRET_KEY,
      ],
      'endpoint' => AWS_ENDPOINT,
    ]);
    try {
      $s3->putObject(array(
        'Bucket' => $bucket,
        'Key' => $fileName,
        'Secret' => $this->SECRET_KEY,
        'Body' => fopen(WWW_ROOT . $localpath . $fileName, 'r'),
        'ACL' => 'public-read-write',
        'ServerSideEncryption' => 'AES256',
        'Metadata' => array(
          'alt' => $alt,
        ),
      ));
      unlink(WWW_ROOT . $localpath . $fileName);
    } catch (S3Exception $e) {
      echo "There was an error uploading the file.\n" . $e . "<br/>";
    }
  }

  /**
   * Amazon Image Upload functionality.
   *
   * @param string $filetemp
   * @param string $file
   * @param integer $size
   * @param string $extension
   * @param string $localpath
   * @param string $alt
   * @return @void
   */
  public function imageFileUpload($filetemp, $file, $size, $extension, $localpath, $alt = '')
  {
    $move = move_uploaded_file($filetemp, IMAGE_PATH . $localpath . '/' . $file);
    if (!$move) {
      throw new Exception("File Didn't Upload");
    } else {
      $sourcefile = IMAGE_PATH . $localpath . '/' . $file;
      $targetfile = IMAGE_PATH . $localpath . '/' . THUMBS_FOURHUNDRED . '/' . $file;
      $size = THUMB_SIZE_FOURHUNDRED;
      $this->resize($sourcefile, $targetfile, $size);

      if ($localpath == 'restaurants' || $localpath == 'items' || $localpath == 'facts') {
        $this->resize($sourcefile, IMAGE_PATH . $localpath . '/' . THUMBS . '/' . $file, THUMB_SIZE);
        $this->amazonUpload(LOCALPATH_IMAGES . $localpath . '/' . THUMBS . '/', IMAGES_BUCKET . "/" . $localpath . '/' . THUMBS, $file, $alt);
      }
      $this->amazonUpload(LOCALPATH_IMAGES . $localpath . '/' . THUMBS_FOURHUNDRED . '/', IMAGES_BUCKET . "/" . $localpath . '/' . THUMBS_FOURHUNDRED, $file, $alt);
      $this->amazonUpload(LOCALPATH_IMAGES . $localpath . "/", IMAGES_BUCKET . "/" . $localpath, $file, $alt);

      return false;
    }
  }

  /**
   * Resize Image
   *
   * @param string $sourcefile
   * @param string $targetfile
   * @param int $size
   */
  public function resizeimagemagic($sourcefile, $size, $targetfile)
  {
    exec("convert $sourcefile -resize $size $targetfile");
  }

  /**
   * Upload image on amazon server
   *
   * @param string $fileName
   * @param string $fileTempName
   * @param int $width
   * @param int $height
   * @return boolean
   */
  public function imageUpload($fileName, $fileTempName, $alt, $width, $height)
  {
    $localpath_vehicles = Configure::read('localpath_vehicles');
    $vehicles_bucket = Configure::read('vehicles_bucket');
    move_uploaded_file($fileTempName, WWW_ROOT . $localpath_vehicles . $fileName);
    $this->Resize->resize(WWW_ROOT . $localpath_vehicles, $fileName, $width, $height, 'desktop');
    $this->Resize->resize(WWW_ROOT . $localpath_vehicles, $fileName, $width, $height, 'mobile');

    // Upload file to amazon
    $this->amazonUpload($localpath_vehicles . "/desktop/", $vehicles_bucket . "/desktop", $fileName, $alt);
    $this->amazonUpload($localpath_vehicles . "/mobile/", $vehicles_bucket . "/mobile", $fileName, $alt);
    unlink(WWW_ROOT . $localpath_vehicles . $fileName);

    return true;
  }

  /**
   * Method description
   *
   * File parameters return function
   *
   * @param array $file
   * @return array $file
   */
  public function fileValues($files = array())
  {
    $file = array();
    $file['name'] = '';

    if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $files['name'])) {
      $file['name'] = time() . '-' . preg_replace('/[^a-zA-Z0-9.]+/', '', $files['name']);
      $file['name'] = str_replace(' ', '_', $file['name']);
    } else {

      $file['name'] = str_replace(' ', '_', $files['name']);
      $file['name'] = time() . '-' . $file['name'];
    }

    $file['tmp_name'] = $files['tmp_name'];
    $file['size'] = $files['size'];
    $file['ext'] = pathinfo($file['name'], PATHINFO_EXTENSION);
    return $file;
  }

  public function siteUrl()
  {
    return sprintf(
      "%s://%s%s",
      isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
      $_SERVER['SERVER_NAME'],
      $_SERVER['REQUEST_URI']
    );
  }

  /**
   * Method description
   *
   * Method is used to generate Auth token
   *
   * @param length
   *
   * @return string
   */
  public function generateRandomString($length = 16, $password = '')
  {

    // initialize variables
    $i = 0;
    $possible = "0123456789abcdefghijklmnopqrstuvwxyz";
    while ($i < $length) {
      $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);

      if (!strstr($password, $char)) {
        $password .= $char;
        $i++;
      }
    }
    return $password;
  }

  /**
   * Get facebook access token
   *
   * @param  int $fb_access_token
   * @return string
   */
  public function facebookGraphApi($fb_access_token = '')
  {
    $graph_url = FACEBOOK_GRAPH_API_URL . $fb_access_token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $graph_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output);
  }

  /**
   * Get googleplus access token
   *
   * @param  int $gplus_access_token
   *
   * @return string
   */
  public function googleplusGraphApi($gplus_access_token = '')
  {
    $gplus_url = GOOGLE_PLUS_API_URL . $gplus_access_token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $gplus_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output);
  }

  /**
   * This function is used to resize images
   * @param string, string, int, int
   * @return string
   * @author
   */
  public function resize($imagePath, $targetfile, $size = 200)
  {
//$sourcefile, $size, $targetfile
    $destinationWidth = $size;
    $destinationHeight = $size;
    // The file has to exist to be resized
    if (file_exists($imagePath)) {

      // Gather some info about the image
      $imageInfo = getimagesize($imagePath);

      // Find the intial size of the image
      $sourceWidth = $imageInfo[0];
      $sourceHeight = $imageInfo[1];

      // Find the mime type of the image
      $mimeType = $imageInfo['mime'];

      $source_aspect_ratio = $sourceWidth / $sourceHeight;
      $thumbnail_aspect_ratio = $destinationWidth / $destinationHeight;
      if ($sourceWidth <= $destinationWidth && $sourceHeight <= $destinationHeight) {
        $thumbnail_image_width = $sourceWidth;
        $thumbnail_image_height = $sourceHeight;
      } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
        $thumbnail_image_width = (int) ($destinationHeight * $source_aspect_ratio);
        $thumbnail_image_height = $destinationHeight;
      } else {
        $thumbnail_image_width = $destinationWidth;
        $thumbnail_image_height = (int) ($destinationWidth / $source_aspect_ratio);
      }

      // Create the destination for the new image
      $destination = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);

      // Now determine what kind of image it is and resize it appropriately
      if ($mimeType == 'image/jpeg' || $mimeType == 'image/pjpeg') {
        $source = imagecreatefromjpeg($imagePath);
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $sourceWidth, $sourceHeight);
        imagejpeg($destination, $targetfile, 100);
      } else if ($mimeType == 'image/gif') {
        $source = imagecreatefromgif($imagePath);
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $sourceWidth, $sourceHeight);
        imagegif($destination, $targetfile, 100);
      } else if ($mimeType == 'image/png' || $mimeType == 'image/x-png') {
        $source = imagecreatefrompng($imagePath);
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $transparent);
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $sourceWidth, $sourceHeight);
        imagepng($destination, $targetfile, 9);
      } else {
        return false;
      }

      // Free up memory
      imagedestroy($source);
      imagedestroy($destination);
      return true;
    } else {
      return false;
    }

  }
  /**
   * Amazon Delete functionality.
   *
   * @param string $reviews_bucket
   * @param string $fileName
   * @return @void
   */
  public function amazonDelete($bucket, $fileName)
  {
    $s3 = S3Client::factory([
      'credentials' => [
        'key' => AWS_KEY,
        'secret' => AWS_SECRET_KEY,
      ],
      'endpoint' => AWS_ENDPOINT,
    ]);

    try {
      $s3->deleteObject(array(
        'Bucket' => $bucket,
        'Key' => $fileName,
      ));
    } catch (S3Exception $e) {
      echo "There was an error deleting the file.\n" . $e;
    }
  }
}
