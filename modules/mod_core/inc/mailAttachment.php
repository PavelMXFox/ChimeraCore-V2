<?php

namespace fox;

class mailAttachment extends file
{
    protected $id;
    
    protected $disposition;
    protected $p_key;
    protected $encoding;
    public ?mailMessage $message;
    
    protected static $excludeProps=['sql','changelog','__sqlSelectTemplate', 'fillPrefix','message'];
    
    public static function createFromMessage($filename, $disposition, $p_key, $encoding, mailMessage $message)
    {
        $obj = new self();
        $obj->fillFromMessage($filename, $disposition, $p_key, $encoding, $message);
        return $obj;
    }
    
    public function fillFromMessage($filename, $disposition, $p_key, $encoding, mailMessage $message) {
        $this->filename = $filename;
        $this->disposition=$disposition;
        $this->p_key=$p_key;
        $this->encoding=$encoding;
        $this->message=$message;
    }
    
    public static function createFromPart(&$part, $pkey, &$message) {
        
        $att = new self();
        $att->message=$message;
        $att->p_key=$pkey;
        $att->filename = ($part["dparameters"]["0"]->value);
        $att->disposition=$part["disposition"];
        $att->encoding=$part["encoding"];
        
        
        if(strtolower(substr($part["dparameters"]["0"]->value,0,7)) == "utf-8''")
        {
            $att->filename = urldecode(substr($att->filename,7));
        } elseif (strtolower(substr($part["dparameters"]["0"]->value,0,10)) == "=?utf-8?b?") {
            $att->filename=mb_decode_mimeheader($att->filename);
        }
        
        return $att;
    }

    public function writeAttachment() {
        
        $f_data = imap_fetchbody ($this->message->conn, $this->message->refNum, $this->p_key,FT_PEEK);
        if ($this->encoding == 3)
        {
            $f_data = base64_decode($f_data);
        } elseif ($this->encoding == 4) {
            $f_data=quoted_printable_decode($f_data);
        }
        
        $this->write($f_data);
        
    }
    
    
    
    
}