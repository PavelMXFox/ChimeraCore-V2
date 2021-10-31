<?php namespace fox;

class cache {
    public ?\Memcached $mcd=null;
    protected $prefix=null;
    
    public static function connect($host=null, $port=11211) {
        $mcd = new self($host, $port);
        if ($mcd->connCheck()) {
            return $mcd;
        } else {
            return false;
        }
    }
    
    public function __construct($host=null, $port=11211) {
        
        if (empty($host) && !empty(config::get("cacheHost"))) {
            $this->mcd = new \Memcached();
            $host = config::get("cacheHost");
            if (!empty($port)) { $port = config::get("cachePort"); };
        }
        
        if (gettype($host) == 'array') {
            $this->mcd = new \Memcached();
            $this->mcd->addServers($host);
        } elseif (gettype($host) == "string") {
            $this->mcd = new \Memcached();
            $this->mcd->addServer($host, $port);
        } else {
            $this->mcd = null;
            return;
        }
        $this->prefix = str_pad(strtolower(dechex(crc32(config::get("sitePrefix")))),8,"0",STR_PAD_LEFT);
    }
    
    protected function pConnCheck() {
        if (!$this->connCheck()) { throw new \Exception("MEMCACHED Not connected!"); }
    }
    
    public function connCheck() {
        if (empty($this->mcd)) { return false; }
        return $this->mcd->getVersion() !==false;        
    }
    
    public function set($key, $val, $TTL=300) {
        $this->pConnCheck();
        $this->mcd->set($this->prefix.".".$key, json_encode($val), $TTL);
    }

    public function get($key) {
        $this->pConnCheck();
        return json_decode($this->mcd->get($this->prefix.".".$key));
    }
    
}

?>