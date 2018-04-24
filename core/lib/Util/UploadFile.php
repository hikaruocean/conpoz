<?php 
namespace Conpoz\Core\Lib\Util;

class UploadFile
{
    public $name;
    public $size;
    public $type;
    public $tmpName;
    public $error;
    public $otherInfo = array();
    public function __construct($fileInfo = array()) 
    {
        $this->name = $fileInfo['name'];
        $this->size = $fileInfo['size'];
        $this->type = $fileInfo['type'];
        $this->tmpName = $fileInfo['tmp_name'];
        $this->error = $fileInfo['error'];
    }

    public function __get($name)
    {
        if (isset($this->otherInfo[$name])) {
            return $this->otherInfo[$name];
        }
        switch($name) {
            case 'mime':
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (getType($finfo) != "resource") {
                    $this->otherInfo[$name] = '';
                } else {
                    $this->otherInfo[$name] = finfo_file($finfo, $this->tmpName);
                }
                finfo_close($finfo);
                break;
            case 'uploadedFile':
                $this->otherInfo[$name] = is_uploaded_file($this->tmpName);
                break;
            case 'errorMessage':
                $phpFileUploadErrors = array(
                    0 => 'There is no error, the file uploaded with success',
                    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    3 => 'The uploaded file was only partially uploaded',
                    4 => 'No file was uploaded',
                    6 => 'Missing a temporary folder',
                    7 => 'Failed to write file to disk.',
                    8 => 'A PHP extension stopped the file upload.',
                );
                $this->otherInfo[$name] = $phpFileUploadErrors[$this->error];
                break;
            default:
                return null;
        }
        return $this->otherInfo[$name];
    }

    public function move($destinationPath) {
        return (move_uploaded_file($this->tmpName, $destinationPath));
    }
}
