<?php

namespace Vortexgin\FileBrowserBundle\Utils;

/**
 * File utilization functions
 * 
 * @category Utils
 * @package  Vortexgin\FileBrowserBundle\Utils
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/file-browser
 */
class FileUtils
{

    public static $mimeType = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',


        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    /**
     * Function to get mime type from file.
     *
     * @param string $filename Filename
     *
     * @return string
     */
    public static function getMimeType($filename)
    {
        $idx = explode('.', $filename);
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode - 1]);

        if (array_key_exists($idx, self::$_mimeType)) {
            return self::$mimeType[$idx];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Function to upload file from base64data
     *
     * @param string $base64 Base64 encoded data
     * @param string $path   path to store file
     * 
     * @return mixed
     */
    static public function uploadBase64File($base64, $path)
    {
        try {
            $exp1 = explode(';', $base64);
            if(!array_key_exists('1', $exp1))
                return false;
            $exp2 = explode(':', $exp1[0]);
            if(!array_key_exists('1', $exp2))
                return false;
            $exp3 = explode('base64,', $exp1[1]);
            if(!array_key_exists('1', $exp3))
                return false;

            $raw = $exp3[1];
            $ext = array_search($exp2[1], self::$mimeType);
            if (!$ext) {
                return false;
            }

            $filename = date('YmdGis').str_replace(' ', '', microtime()).'.'.$ext;
            $targetFile = $path.$filename;

            if (!file_put_contents($targetFile, \base64_decode($raw), FILE_BINARY)) {
                return false;
            }

            return $filename;
        } catch(\Exception $e) {
            return false;
        }
    }
}
?>