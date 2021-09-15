<?php 
namespace fox;
require_once 'coreAPI.php';


/**
 * Class fox\mailClient
 **/

class mailClient {

    protected ?mailAccount $acct;
    protected $conn;
    protected $stat;
    protected $list;
    
    public array $messages=[];
    
    
    public function __construct(?mailAccount $a) {
        $this->acct = $a;
    }
    
    
    public function getList($folder=null, $criteria="UNSEEN", $range=null) {
        if (strtolower($this->acct->rxProto) != 'imap') { throw new \Exception("Protocol '".$this->acct->rxProto."' not implemented yet");}
        
        if (empty($folder)) {
            $folder=$this->acct->rxFolder;
            if (empty($folder)) {$folder = 'INBOX';}
        }
        $this->conn = (\imap_open("{".$this->acct->rxServer.":".$this->acct->rxPort."/".strtolower($this->acct->rxProto).(($this->acct->rxSSL==false)?"/novalidate-cert":"/ssl")."}$folder",$this->acct->rxLogin,$this->acct->rxPassword));
        
        if (!$this->conn)
        {
            throw new \Exception("Connection to '".$this->acct->rxServer."' failed");
        }
        
        $this->stat = (array)imap_mailboxmsginfo($this->conn);
        
        $this->list = imap_search($this->conn, $criteria, SE_UID);
        $this->messages=[];
    }
    
    public function getMessages() {
        $this->messages = [];
        if (empty($this->list)) { return; }
        foreach ($this->list as $idx) {
            array_push($this->messages, $this->getMessage($idx));
        }
    }
    
    public function getMessage($uid) {
        $message = new mailMessage();
        
        $num = imap_msgno ( $this->conn , $uid );
        $msg = imap_fetch_overview ( $this->conn , $num)[0];
        
        if (property_exists($msg, "in_reply_to")) {
            try {
                $message->addRecipient(self::decodeHeader($msg->to));
            } catch (\Exception $e) {}
        }
            
        $message->account = $this->acct;
        $message->addSender(self::decodeHeader($msg->from));
        $message->messageId = $msg->message_id;
        $message->udate = $msg->udate;
        $message->subject = self::decodeHeader(imap_utf8($msg->subject));
        $message->refNum=$num;
        $message->conn=$this->conn;
        $message->direction='RX';
        
        if (property_exists($msg, "in_reply_to")) { $message->inReplyTo=$msg->in_reply_to;};
        if (property_exists($msg, "references")) { $message->references =$msg->references;}
        
        $pheaders = imap_rfc822_parse_headers(imap_fetchheader($this->conn, $num));
        $struct = imap_fetchstructure ($this->conn, $num);
        $parts_found = null;
        $this->search_parts($struct,null,$parts_found);
        
        try {
            if (property_exists($pheaders, "fromaddress")) { foreach (explode(",", $pheaders->fromaddress) as $item) { $message->addSender(self::decodeHeader($item)); }; }
        } catch(\Exception $e) {}
        
        try {
            if (property_exists($pheaders, "toaddress")) { foreach (explode(",", $pheaders->toaddress) as $item) { $message->addRecipient(self::decodeHeader($item)); }; }
        } catch(\Exception $e) {}
        try {
            if (property_exists($pheaders, "ccaddress")) { foreach (explode(",", $pheaders->ccaddress) as $item) { $message->addCC(self::decodeHeader($item)); }; }
        } catch(\Exception $e) {}
        
        if (isset($parts_found["type"]["PLAIN"]))
        {
            $text = imap_fetchbody ($this->conn, $num, $parts_found["type"]["PLAIN"],FT_PEEK );
            $encoding = $parts_found[$parts_found["type"]["PLAIN"]]["encoding"];
            $text_encoding = $parts_found[$parts_found["type"]["PLAIN"]]["text-encoding"];
            if ($encoding == 3)
            {
                $text = base64_decode($text);
            } elseif ($encoding == 4) {
                $text=quoted_printable_decode($text);
            }
            
            if ($text_encoding == 'default')
            {
                $msg_plain_text = $text;
            } else {
                $msg_plain_text = iconv($text_encoding, "utf-8", $text);
            }
            
            $message->bodyPlain = $msg_plain_text;
        }
        
        if (isset($parts_found["type"]["HTML"]))
        {
            $encoding = $parts_found[$parts_found["type"]["HTML"]]["encoding"];
            $html_text=imap_fetchbody ($this->conn, $num, $parts_found["type"]["HTML"],FT_PEEK );
            if ($encoding == 3)
            {
                $html_text = base64_decode($html_text);
            } elseif ($encoding == 4) {
                $html_text=quoted_printable_decode($html_text);
            }

            
            $message->bodyHTML = $html_text;            
        }

        
        foreach ($parts_found as $pkey=>$part)
        {
            if ($pkey != 'type') {
                if (isset($part["disposition"]) && (($part["disposition"] == 'attachment') || (($part["dparameters"]["0"]->value != ''))))
                {
                    $att = mailAttachment::createFromPart($part, $pkey, $message);
                    $message->addAttachment($att);
                }
            }
        }
        
        //imap_setflag_full($this->conn, $message->refNum, "\Seen");
        
        return $message;
        
    }
    
    protected function search_parts($struct, $path=null, &$parts_found=null)
    {
        if (!isset($path))
        {
            $path='';
            $parts_found = null;
            $parts_found["type"]["HTML"] = null;
            $parts_found["type"]["PLAIN"] = null;
            
        }
        $retval = $this->parce_struct($struct);
        $path_rv = $path;
        if ($path_rv == '') { $path_rv = 1;}
        $parts_found[$path_rv] = $retval;
        
        
        
        if (($retval["type"] == 0) && ($retval["subtype"] == "PLAIN"))
        {
            $parts_found["type"]["PLAIN"] = $path_rv;
        } elseif (($retval["type"] == 0) && ($retval["subtype"] == "HTML"))
        {
            $parts_found["type"]["HTML"] = $path_rv;
        }
        
        if ($retval["parts_count"] > 0)
        {
            foreach ($struct->parts as $pkey=>$part)
            {
                $path_t = $path;
                if ($path_t != '') {$path_t .= '.';};
                //	    $path_t=$path.".".($pkey+1);
                $path_t.=($pkey+1);
                $this->search_parts($part, $path_t, $parts_found);
            }
        }
        return $retval;
    }
    
    protected function parce_struct($struct, $path="")
    {
        $retval["type"] = $struct->type;
        $retval["encoding"] = $struct->encoding;
        $retval["subtype"] = ($struct->ifsubtype==1)?$struct->subtype:"-1";
        $retval["disposition"] = ($struct->ifdisposition==1)?$struct->disposition:null;
        $retval["dparameters"] = ($struct->ifdparameters==1)?$struct->dparameters:null;
        $retval["parts_count"] = ($retval["type"] == TYPEMULTIPART)?count($struct->parts):0;
        $retval["text-encoding"] = "default";
        if (isset($struct->parameters))
        {
            foreach ($struct->parameters as $p_stk)
            {
                if ($p_stk->attribute=="charset")
                {
                    $retval["text-encoding"] = $p_stk->value;
                }
            }
        }
        
        //    $retval["parts"] = $struct->parts;
        return $retval;
    }
    
    /* workaround to make most of headers to parse properly */
    protected static function decodeHeader($hdr, $cset = 'UTF8')
    {
        // Copied nearly intact from PEAR's Mail_mimeDecode.
        $hdr = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $hdr);
        $m = array();
        if(is_array($hdr))
            $hdr = $hdr[0];
            while(preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $hdr, $m))
            {
                $encoded  = $m[1];
                $charset  = strtoupper($m[2]);
                $encoding = strtolower($m[3]);
                $text     = $m[4];
                
                switch($encoding)
                {
                    case 'b':
                        $text = base64_decode($text);
                        break;
                    case 'q':
                        $text = str_replace('_', ' ', $text);
                        preg_match_all('/=([a-f0-9]{2})/i', $text, $m);
                        foreach($m[1] as $value)
                            $text = str_replace('=' . $value, chr(hexdec($value)), $text);
                            break;
                }
                if($charset !== $cset)
                    $text = self::charconv($charset, $cset, $text);
                    $hdr = str_replace($encoded, $text, $hdr);
            }
            return $hdr;
    }
    
    /* workaround to make most of headers to parse properly */
    protected function charconv($enc_from, $enc_to, $text)
    {
        if(function_exists('iconv'))
            return iconv($enc_from, $enc_to, $text);
            elseif(function_exists('recode_string'))
            return recode_string("$enc_from..$enc_to", $text);
            elseif(function_exists('mb_convert_encoding'))
            return mb_convert_encoding($text, $enc_to, $enc_from);
            return $text;
    }
}


?>