<?php
/**
 * Ptitcaptcha : simple php captcha system
 * 
 * @author Jean-Pierre Morfin
 * @license Creative Commons By
 * @license http://creativecommons.org/licenses/by/2.0/fr/
 */
 
/* Change it to have a specific encoding ! 
define("PTITCAPTCHA_ENTROPY","ABCDEFGHJKLMNPRSTUVWXYZabcdefghklmnpqrstuvwxyz23456789");
*/

define("PTITCAPTCHA_ENTROPY","pionduitr");
 
/* Choose length (max 32) */
define("PTITCAPTCHA_LENGTH",5);
 
$GLOBALS["ptitcaptcha_akey"] = md5(uniqid(rand(), true));
 
/**
 * Helper to generate html form tags
 *
 */
class PtitCaptchaHelper
{
    /**
     * Generate IMG Tag
     *
     * @param string $baseuri : relative or absolute path to folder containing this file on web
     * @return string IMG Tag
     */
    public static function generateImgTags($baseuri)
    {
        return "<a href=\"#\"><img alt=\"???\" title=\"?\"" .
            " src=\"" . $baseuri . "scripts/ptitcaptcha.php?pck=" . $GLOBALS['ptitcaptcha_akey'] . "\"" .
            " id=\"ptitcaptcha\"" .
            " onclick=\"javascript:this.src='" . $baseuri . "scripts/ptitcaptcha.php?pck=" .
            $GLOBALS['ptitcaptcha_akey'] .
            "&z='+Math.random();return false;\" /></a>\n";
    }
 
    /**
     * Generate hidden tag (must be in a form)
     *
     * @return string input hidden tag
     */
    public static function generateHiddenTags()
    {
        return "<input type=\"hidden\" name=\"ptitcaptcha_key\" value=\"" . $GLOBALS['ptitcaptcha_akey'] . "\"/>";
    }
 
    /**
     * Generate input tag (must be in a form)
     *
     * @return string input tag
     */
    public static function generateInputTags()
    {
        return "<input type=\"text\" name=\"ptitcaptcha_entry\" value=\"\"/>";
    }
 
    /**
     * Check if user input is correct
     *
     * @return boolean (true=correct, false=incorrect)
     */
    public static function checkCaptcha()
    {
        $captcha_entre = isset($_POST['ptitcaptcha_entry']) ? htmlentities($_POST['ptitcaptcha_entry']) : '';
        $captcha_key = isset($_POST['ptitcaptcha_key']) ? htmlentities($_POST['ptitcaptcha_key']) : '';
        
        if (!empty($captcha_entre) && $captcha_entre == self::_getDisplayText($captcha_key)) {
            return true;
        }
        return false;
    }
 
    /**
     * Internal function
     *
     * @param string $pck
     * @return string
     */
    public static function _getDisplayText($pck)
    {
        $src = md5(PTITCAPTCHA_ENTROPY . $pck);
        $txt = "";
        for ($i = 0; $i < PTITCAPTCHA_LENGTH; $i++) {
            $txt .= substr($src, $i * 32 / PTITCAPTCHA_LENGTH, 1);
        }
        return $txt;
    }
}	
 
 
// If script called directly : generate image
$cle = isset($_GET["pck"]) ? htmlentities($_GET["pck"]) : '';
if (basename($_SERVER["SCRIPT_NAME"]) == "ptitcaptcha.php" && !empty($cle)) {
    $width = PTITCAPTCHA_LENGTH * 10 + 10;
    $height = 30;
 
    $image = imagecreatetruecolor($width, $height);
    $bgCol = imagecolorallocate($image, rand(128, 255), rand(128, 255), rand(128, 255));
    imagefilledrectangle($image, 0, 0, $width, $height, $bgCol);
 
    $txt = PtitCaptchaHelper::_getDisplayText($cle);
 
    for ($c = 0; $c < PTITCAPTCHA_LENGTH * 2; $c++) {
        $bgCol = imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(100, 255));
        $x = rand(0, $width);
        $y = rand(0, $height);
        $w = rand(5, $width / 2);
        $h = rand(5, $height / 2);
        imagefilledrectangle($image, $x, $y, $x + $w, $y + $h, $bgCol);
        imagecolordeallocate($image, $bgCol);
    }
    for ($c = 0; $c < PTITCAPTCHA_LENGTH; $c++) {
        $txtCol = imagecolorallocate($image, rand(0, 128), rand(0, 128), rand(0, 128));
        imagestring($image, 5, 5 + 10 * $c, rand(0, 10), substr($txt, $c, 1), $txtCol);
        imagecolordeallocate($image, $txtCol);
    }
 
    header("Content-type: image/png");
    imagepng($image);
    imagedestroy($image);
}
