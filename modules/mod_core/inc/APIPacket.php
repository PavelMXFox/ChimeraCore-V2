<?php

namespace fox;

class APIPacket {
    public $messageId;
    public $stamp;
    public $inReplyTo=null;
    public $token;
    public $command;
    public $data;
    
    function __construct() {
        $this->stamp=time();
    }
}