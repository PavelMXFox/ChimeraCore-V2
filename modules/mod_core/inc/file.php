<?php

namespace fox;

/** Файлы хранятся в папке filesDir (default: ~/storage/files)
 * @property mixed $id
 * @property mixed $ownerId         # id владельца файла. Владельцу доступны все операции (чтение, удаление, переименование)
 * @property mixed $aclRule         # aclRule для доступа к файлу, если не указан - то прочитать сможет только владелец
 * @property 
 **/

class file extends baseClass {
    
    protected $id;
    protected $uid;
    protected $__ownerId;
    protected ?user $owner;
    protected $aclRule=null;
    protected $filename;
    protected $createStamp;
    protected $size;
    protected $type;
    protected $ext;
    protected $hash;
    protected $expireIn;    // after this days file will be erased.
    protected $deleted=false;
    protected $__path;
    protected $__prefix;
    protected ?file $aliasFor;
    protected $__aliasForId;
    protected $altTypes=[];
    
    
    
    public static $sqlTable="tblFiles";
    
    public function __get($key) {
       return parent::__getDef($key);
    }
    
    protected function getPR2() {
        return sprintf('%04s', floor($this->id/1000));
    }
    
    public function getPath() {
        $this->__prefix=self::getFilesPrefix();
        return $this->__prefix."/".$this->getPR2()."/".sprintf("%010s",$this->id).(empty($this->ext)?"":".".$this->ext);
    }
    
    public static function getFilesPrefix() {
        $prefix=config::get("filesDir");
        if (empty($prefix)) { $prefix = $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/storage/files";}
        return $prefix;
    }

    public static function getMaxFileSize() {
        $maxFileSize = config::get("maxFileSize");
        if (empty($maxFileSize)) { $maxFileSize=16777216; }
        return $maxFileSize;
    }
    
    public function write($content) {
        
        $f_arr = explode('.', $this->filename);
        if (count($f_arr)>1) {
            $this->ext = strtoupper($f_arr[count($f_arr)-1]);
        } else {
            $this->ext=null;
        }
        
        $this->size=strlen($content);
        if (empty($this->type)) { $this->type=new fileType($this->filename);}
        if (!empty($this->id)) { throw new \Exception("May not possible to rewrite file content. Please, create new file instead."); }

        $prefix=self::getFilesPrefix();
        if (file_exists($prefix)) {
          if (!is_dir($prefix)) { throw new \Exception("$prefix not a directory!");}
        } else {
          mkdir($prefix);
        }
        
        $this->hash=hash("SHA256",$content);
        $this->__prefix = $prefix;
        
        $this->save();
        
        $pr2 = $this->getPR2();
        
        if (file_exists($this->__prefix."/".$pr2)) {
            if (!is_dir($this->__prefix."/".$pr2)) { throw new \Exception($this->__prefix."/".$pr2." not a directory!");}
        } else {
            mkdir($this->__prefix."/".$pr2);
        }
        $this->__path = $this->getPath();
        file_put_contents($this->__path, $content);
        
        for ($i=0; $i<10; $i++) {
            try {
                $this->uid=preg_replace("!\.!","",uniqid(null,true));
                $this->sql->quickExec("update `".$this::$sqlTable."` set `uid` = '".$this->uid."' where `id` = '".$this->id."'");
                break;
            } catch (\Exception $e) {
                trigger_error("Set UID# ".$i." = ".$this->uid." Failed: ".$e->getMessage(), E_USER_WARNING);
            }
            if ($i>=9) {
                throw new \Exception("Unable to set UID");
            }
        }
    }
    
    public function read() {
        if (isset($this->id)) {
            return file_get_contents($this->getPath());
        } else {
            throw new \Exception("Unable to read empty file");
        }
    }
    
    public function delete() {
        # Проверить наличие файлов isAliasFor для этого файла. Если таковых нет - удалить.
        throw new \Exception("Not implemented yet");
    }
    
    protected function validateSave() {
        
        if (empty($this->filename)) { throw new \Exception('Filename may not be empty');}
        return true;
    }

    protected function create() {
        if (empty($this->__prefix)) {
            throw new \Exception("Can't create file by 'save' method. User 'write' instead");
        }
        
        parent::create();
    }
    
    protected function createAddParams() {

        $this->sql->paramAdd("aclRule", $this->aclRule);
        $this->sql->paramAdd("filename", $this->filename);
        $this->sql->paramAdd("size", $this->size);
        $this->sql->paramAdd("type", $this->type->ctype);
        $this->sql->paramAdd("hash", $this->hash);
        $this->sql->paramAdd("expireIn", $this->expireIn);
        $this->sql->paramAdd("aliasForId", $this->__aliasForId);
        $this->sql->paramAdd("ext", $this->ext);
        return true;
    }

    
    
}
?>