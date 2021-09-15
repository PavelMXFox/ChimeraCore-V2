<?php

namespace fox;

class APIMessage implements \JsonSerializable {
    protected ?APITokenIface $token=null;
    public ?APIPacket $packet = null;
    protected $__signature=null;
    
    public function __construct(APITokenIface $token, $command, $data=null, $msgid=null) {
        $this->token = $token;
        $this->packet = new APIPacket();
        $this->packet->token=$this->token->getUUID();
        $this->packet->command=$command;
        $this->packet->data=$data;
        if (empty($msgid)) {
            $this->packet->messageId=common::getGUIDc();
        } else {
            $this->packet->messageId = $msgid;
        }
    }
    
    public function sign() {
        $this->__signature = $this->token->sign(json_encode($this->packet));
        return $this;
    }
    
    public function validate() {
        return $this->token->validate(json_encode($this->packet), $this->sign);
    }
    
    public function toBase64() {
        return "CFX".base64_encode($this->toJSON())."XFC";
    }
    
    public function toJSON() {
        return json_encode($this->jsonSerialize());
    }
    
    public function jsonSerialize()
    {
        $this->sign();
        return ["packet"=>$this->packet, "sign"=>$this->__signature];
    }
    
    public static function encode($token, $command, $data=null, $b64=false) {
        $msg = new self($token, $command, $data);
        if ($b64) {
            return $msg->toBase64();
        } else {
            return $msg->toJSON();
        }
    }
    
    public static function decode($data,$token=null) {
        $jsd=null;
        if (preg_match("/^CFX(.*)XFC$/", $data, $rv)) {
            $jsd=base64_decode($rv[1]);
        } else {
            $jsd = $data;
        }
     
        
        
        $msg = json_decode($jsd,false,512,JSON_THROW_ON_ERROR);
        
        if (empty($msg) || empty($msg->packet) || empty($msg->packet->token) || empty($msg->sign)) {
            throw new \Exception('Invalid message format');
        }
        if (empty($token) && class_exists("fox\APIToken")) {
            $token = APIToken::getByUUID($msg->packet->token);
        }
        
       if (empty($token)) {
            throw new \Exception("Empty token at non-host system");
        }
        
        if ($msg->packet->token != $token->getUUID()) {
            throw new \Exception("Token mismatch Host: ".$token->getUUID()." remote ".$msg->packet->token);
        }
        
        if ($token->validate(json_encode($msg->packet), $msg->sign)) {
            $rmsg = new APIMessage($token, $msg->packet->command, $msg->packet->data, $msg->packet->messageId);
            $rmsg->packet->inReplyTo=$msg->packet->inReplyTo;
            return $rmsg;
        } else {
            throw new \Exception("Invalid message signature");
        }
        
    }
    
    public function reply($data) {
        $msg = new self($this->token, $this->packet->command,$data);
        $msg->packet->inReplyTo=$this->packet->messageId;
        return $msg;
    }
    
    public function getUser() {
        return $this->token->user;
    }
}