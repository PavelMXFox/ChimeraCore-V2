<?php 
namespace fox;

class foxException extends \Exception {
    
    
    protected $status="ERR";
    
    public const STATUS_ERR="ERR";
    public const STATUS_WARN="WARN";
    public const STATUS_ALERT="ALERT";
    public const STATUS_INFO="INFO";
    
    public static function throw($status, $message, $code, $prev=null) {
        $e = new self($message, $code, $prev);
        $e->setStatus($status);
        throw $e;
    }
    
    public function setStatus($status) {
        $this->status=$status;
    }
    
    public function getStatus() {
        return $this->status;
    }
}

?>