<?php
/**
 * @todo: Watermark support (no watermark, on-the-fly, on-save)
 */
class Image {
    /**
     * The path to the image
     * @var string
     */
    private $image;

    /**
     * Valid file extensions for images
     * @var array
     */
    private $extensions = array('jpg', 'png', 'gif', 'jpeg');

    /**
     * Holds the response of getimagesize
     * @var array
     */
    private $imageData = null;

    /**
     * 1 = Copy Image, 2 = Copy Image Resampled (better quality)
     * @var integer
     */
    private $copyMode = 1;

    /**
     * Holds the path to the default watermark image
     * @var String
     */
    private $waterMarkImage = 'watermark.png';

    /**
     * Holds the position of the watermark
     * @var array
     */
    private $waterMarkPosition = array('bottom', 'right');

    /**
     * Class constructor method
     * @param string $img
     * @return
     */
    public function __construct($img){
        $this->image = $img;
    }

    /**
     * Set the Watermark image
     * @param string $img
     * @return boolean
     */
    public function setWaterMarkImage($img) {
        if(file_exists($img) || true) {
            $info = getimagesize($img);
            if($info[2] == IMAGETYPE_PNG || $info[2] == IMAGETYPE_GIF) {
                $this->watermarkImage = $img;
                return true;
            }
            else
                throw new Exception("IMAGE ERROR: Watermark needs to be png of gif");
        }
        else
            throw new Exception("IMAGE ERROR: Watermark image doesn't exist");
    }

    public function setWaterMarkPosition($vPos, $hPos){
        if($vPos) {
            $vPos = strtolower($vPos);
            $vPos_a = array("top", "middle", "bottom");
            if(in_array($vPos, $vPos_a))
                $this->waterMarkPosition[0] = $vPos;
            else
                throw new Exception("IMAGE ERROR: Watermark position needs to be ".implode(" or ", $vPos_a));
        }
        if($hPos) {
            $hPos = strtolower($hPos);
            $hPos_a = array("left", "middle", "right");
            if(in_array($hPos, $hPos_a))
                $this->waterMarkPosition[1] = $hPos;
            else
                throw new Exception("IMAGE ERROR: Watermark position needs to be ".implode(" or ", $hPos_a));
        }
    }

    /**
     * Set the image
     * @param string $img
     * @return
     */
    public function setImage($img){
        $this->image = $img;
        $this->imageData = null;
    }

    /**
     * Set the valid file extensions for an image
     * Used for validateImage(1)
     * @param array $array
     * @return
     */
    public function setExtensions($array){
        $this->extensions = $array;
    }

    /**
     * Set the type of coping images
     * 1 = resized
     * 2 = resampled
     * @param integer $mode
     * @return
     */
    public function setCopyMode($mode){
        $this->copyMode = $mode;
    }

    /**
     * Load the getimagesize result
     * @return array
     */
    private function imageData(){
        if($this->imageData == null)
            $this->imageData = getimagesize($this->image);
        return $this->imageData;
    }

    /**
     * Check if it is a real picture
     * 1 = Check extension only (fast)
     * 2 = Check mimetype (maybe slow for remote files)
     * @param integer $type [optional]
     * @return boolean
     */
    public function validateImage($type = 1){
        if($type == 1) {
            $ext = FileSystem::getExtension($this->image);
            return in_array($ext, $this->extensions);
        }
        else {
            $info = $this->imageData();
            if($info) {
                switch($info[2]) {
                    case IMAGETYPE_GIF:
                    case IMAGETYPE_JPEG:
                    case IMAGETYPE_PNG:
                        return true;
                    default:
                        return false;
                }
            }
            else
                return false;
        }
    }

    /**
     * Create a thumb of the image
     * @param string $newName The filename of the thumb
     * @param integer $maxX Maximum width of the image
     * @param integer $maxY Maximum height of the image
     * @return boolean
     */
    public function createThumb($newName, $maxX, $maxY, $watermark = false ) {
        $res = $this->resize($maxX, $maxY);
        if($watermark)
            $res = $this->addWatermark($res);
        return imagepng($res, $newName);
    }

    /**
     * Get the image Resource
     * @return resource
     */
    public function getImageResource(){
        $info = $this->imageData();
        if($info) {
            switch($info[2]) {
                case IMAGETYPE_GIF:
                    $res = imagecreatefromgif($this->image);
                    $new = imagecreatetruecolor(imagesx($res), imagesy($res));
                    imagecopy($new, $res, 0, 0, 0, 0, imagesx($res), imagesy($res));
                    return $new;
                    break;
                case IMAGETYPE_JPEG:
                    return imagecreatefromjpeg($this->image);
                    break;
                case IMAGETYPE_PNG:
                    return imagecreatefrompng($this->image);
                    break;
                default:
                    throw new Exception("IMAGE ERROR: No supported image Type");
            }
        }
        else
            throw new Exception("IMAGE ERROR: No image");
    }

    /**
     * Resize an image and get the new image Resource
     * @param integer $maxX Maximum width of the image
     * @param integer $maxY Maximum height of the image
     * @return resource
     */
    public function resize($maxX, $maxY){
        $res = $this->getImageResource();
        $data = $this->imageData();
        $x = $data[0];
        $y = $data[1];
        if (($maxX/$maxY) < ($x/$y)) {
            $newX = round($x/($x/$maxX), 0);
            $newY = round($y/($x/$maxX), 0);
        }
        else {
            $newX = round($x/($y/$maxY), 0);
            $newY = round($y/($y/$maxY), 0);
        }
        $newRes = imagecreatetruecolor($newX, $newY);
        /*$background = imagecolorallocatealpha($newRes, 0, 0, 0,127);
         imagecolortransparent($newRes,$background);*/
        imagealphablending($newRes, false);
        imagesavealpha($newRes, true);
        $transparent = imagecolorallocatealpha($newRes, 255, 255, 255, 127);
        imagefilledrectangle($newRes, 0, 0, $newX, $newY, $transparent);

        $function = ($this->copyMode == 1) ? "imagecopyresized" : "imagecopyresampled";
        $function($newRes, $res, 0, 0, 0, 0, $newX, $newY, $x, $y);
        imagedestroy($res);
        return $newRes;
    }

    public function addWatermark() {
        $res = $this->getImageResource();
        $x = imagesx($res);
        $y = imagesy($res);
        $Watermark = new Image($this->watermarkImage);
        $w_res = $Watermark->getImageResource();
        $w_x = imagesx($w_res);
        $w_y = imagesy($w_res);
        if($w_x > $x || $w_y > $y){
            $w_res = $Watermark->resize($x, $y);
            $w_x = imagesx($w_res);
            $w_y = imagesy($w_res);
        }
        $d_h = $w_y;
        $d_w = $w_x;
        unset($Watermark);
        switch($this->waterMarkPosition[0]) {
            case 'top':
                $d_y = 0;
                break;
            case 'middle':
                $d_y = ($y-$w_y)/2;
                break;
            case 'bottom':
                $d_y = $y - $w_y;
                break;
        }
        switch($this->waterMarkPosition[1]) {
            case 'left':
                $d_x = 0;
                break;
            case 'middle':
                $d_x = ($x-$w_x)/2;
                break;
            case 'right':
                $d_x = $x-$w_x;
                break;
        }
        $function = ($this->copyMode == 1) ? "imagecopyresized" : "imagecopyresampled";
        imagealphablending($res, true);
        imagesavealpha($res, true);
        $function($res, $w_res, $d_x, $d_y, 0, 0, $d_w, $d_h, $w_x, $w_y);
        return $res;
    }
}
?>
