<?php 
namespace fox;

require_once 'mailAddress.php';
require_once 'mailAttachment.php';
require_once 'Html2Text/Html2Text.php';

require_once("PHPMailer/Exception.php");
require_once("PHPMailer/SMTP.php");
require_once("PHPMailer/PHPMailer.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as mException;

use Exception;

/**
 * Class fox\mailMessage
 * @copyright MX STAR LLC 2020
 * @version 3.0.0
 * @author Pavel Dmitriev
 * @desc MailMessage class
 * 
 * @property mixed $account
 * @property mixed $messageId
 * @property-read array $mailFrom
 * @property-write mixed $mailFrom
 * @property-read array $rcptTo
 * @property mixed $references
 * @property mixed $inReplyTo
 * @property-write mixed $udate
 * @property-read mixed $date  
 * @property mixed $bodyHTML
 * @property mixed $bodyPlain 
 * @property mixed $direction
 * @property mixed $subject
 * @property ?boolean $fromRobot
 * @property-read boolean $isHTML
 * @property-read array $attachments
 * 
 * 
 **/

class mailMessage extends baseClass {
    protected $id;
    protected $accountId;
    protected ?mailAccount $account =null;
    protected $messageId;
    protected array $mailFrom=[];
    protected array $rcptTo=[];
    protected array $cc=[];
    protected array $bcc=[];
    protected $direction=null;
    protected $subject;
    protected $date;
    protected $createDate;
    protected array $refIds=[];
    protected $inReptyTo;
    protected $bodyHTML;
    protected $bodyPlain;
    protected array $attachments=[]; // array of mailAttachment
    protected array $attachmentsID=[];
    protected $fromRobot=false;
    
    protected static $excludeProps=['sql','changelog','__sqlSelectTemplate','account','bodyHTML','bodyPlain'];
    public static $sqlTable="tblMailMessages";
    
    
    public $conn;
    public $refNum;
    
    protected function addAddressToIdx(array &$idx, $val) {
        $addr=null;
        
        if (gettype($val) == 'object' && get_class($val) == 'fox\mailAddress') { $addr = $val; }
        elseif (gettype($val) == 'object' && get_class($val) == 'fox\user') {
            if (!net::validateEMail($val->eMail)) { throw new \Exception("Invalid user eMail. Failed.", 1509);};
            $addr = new mailAddress($val->fullName, $val->eMail);
        }
        else {
            $addr = new mailAddress($val);
        }
        if (!$addr) { return false; }
        foreach ($idx as $rcpt) {
            if ($rcpt->address == $addr->address) { return; }
        }
        
        array_push($idx,$addr);
    }

    protected function getAttachments() {
        if (empty($this->attachments) && !empty($this->attachmentsID)) {
            foreach ($this->attachmentsID as $attId) {
                $att = new mailAttachment($attId);
                $att->message=&$this;
                array_push($this->attachments,$att);
            }
        }
        return $this->attachments;
    }
    
    public function addRecipient($rcptTo) {
        $this->addAddressToIdx($this->rcptTo, $rcptTo);
    }

    public function addSender($mailFrom) {
        $this->addAddressToIdx($this->mailFrom, $mailFrom);
    }

    public function addCC($mailFrom) {
        $this->addAddressToIdx($this->cc, $mailFrom);
    }

    public function addBCC($mailFrom) {
        $this->addAddressToIdx($this->bcc, $mailFrom);
    }
    
    public function addAttachment(mailAttachment $att) {
        array_push($this->attachments, $att);
    }
    
    public function __get($key) {
        switch ($key) {
            case "bodyPlain":
                if (empty($this->bodyPlain)) {
                    $this->bodyPlain = \Html2Text\Html2Text::convert($this->bodyHTML);
                };
                return $this->bodyPlain;
            case "bodyHTML":
                if (empty($this->bodyHTML)) {
                    return $this->bodyPlain;
                } else {
                    return $this->bodyHTML;
                }
            case "direction":
                return $this->direction;
                break;
            case "attachments":
                
                return $this->getAttachments();
                break;
            case "isHTML":
                return (!empty($this->bodyHTML));
                break;
            case "account":
                if (empty($this->account) and !empty($this->accountId)) {
                    $this->account = new mailAccount($this->accountId);
                }
                return $this->account;
            case "mailFrom":
                return $this->mailFrom;
            case "rcptTo":
                return $this->rcptTo;
            case "cc":
                return $this->cc;
            case "bcc":
                return $this->bcc;
            case "subject":
                return $this->subject;

            default: return parent::__get($key);
        }
    }
    
    public function __set($key, $val) {
        switch ($key) {
            case "account":
                if (empty($val)) {
                    $this->account=null;
                    $this->accountId=null;
                } elseif (gettype($val) == 'object' and get_class($val) == 'fox\mailAccount') {
                    $this->account = $val;
                    $this->accountId=$val->id;
                } elseif (gettype($val) == 'string' || gettype($val) == 'integer') {
                    $this->account = new mailAccount($val);
                    $this->accountId = $this->account->id;
                }
                break;
            case "references":
                $ref = explode(" ", $val);
                $this->refIds=[];
                foreach ($ref as $ref_item) {
                    $ref_item=preg_replace("!^\<|\>$!", '', $ref_item);
                    array_push($this->refIds, $ref_item);
                }
                break;
                
            case "inReplyTo":
                $this->inReptyTo=preg_replace("!^\<|\>$!", '', $val);
                break;

            case "messageId":
                $this->messageId=preg_replace("!^\<|\>$!", '', $val);
                break;
            case "subject":
                $this->subject=$val;
                break;
            case "date":
                $this->date=$val;
                break;
            case "udate":
                $this->date=time::stamp2iso_date($val);
                break;
                
            case "bodyHTML":
                $this->bodyHTML = $val;
                break;
                
            case "bodyPlain":
                $this->bodyPlain=$val;
                break;
                
            case "direction":
                $dir = strtoupper($val);
                if ($dir=='RX' || $dir=='TX') {
                    $this->direction=$dir;
                } else {
                    throw new \Exception("AccountID can't be empty");
                }
                break;
            case "fromRobot":
                $this->fromRobot=$val;
                break;
            case "mailFrom":
                $this->mailFrom=[];
                if (!empty($val)) {
                    $this->addSender($val);
                }
                break;
            default: parent::__set($key, $val);
        }
    }

    public function createMessageID($uid=null) {
        if (empty($uid))  ($uid="XXXX-0000-00");
        $this->messageId=common::getGUIDc()."-CHIMERA-".$uid."-FOX-".time();
    }
    
    protected function serializeAddresses(&$arr) {
        $rv=[];
        foreach ($arr as $addr) {
            array_push($rv, $addr->full);
        }
        return $rv;
    }
  
    protected function deSerealizeAddresses($arr) {
        $rv=[];
        foreach ($arr as $addr) {
            array_push($rv, new mailAddress($addr));
        }
        return $rv;
    }
    
    protected function validateSave() {
        if (empty($this->accountId)) { throw new \Exception("AccountID can't be empty"); }
        if (empty($this->direction) || ($this->direction !== 'RX' && $this->direction !== 'TX')) { throw new \Exception("direction must be in [RX:TX]");}
        return true;
    }
    
    protected function create() {
        $this->attachmentsID=[];
        foreach ($this->attachments as $att) {
            $att->writeAttachment();
            
            array_push($this->attachmentsID, $att->id);
        }
        parent::create();
        if (isset($this->conn)) { imap_setflag_full($this->conn, $this->refNum, "\Seen");}
    }
    
    protected function createAddParams() {
        $this->sql->paramAdd("accountId", $this->accountId);
        $this->sql->paramAdd("messageId", $this->messageId);
        $this->sql->paramAdd("mailFrom", json_encode($this->serializeAddresses($this->mailFrom)));
        $this->sql->paramAdd("rcptTo", json_encode($this->serializeAddresses($this->rcptTo)));
        $this->sql->paramAdd("cc", json_encode($this->serializeAddresses($this->cc)));
        $this->sql->paramAdd("bcc", json_encode($this->serializeAddresses($this->bcc)));
        $this->sql->paramAdd("subject", base64_encode($this->subject));
        $this->sql->paramAdd("date",$this->date);
        $this->sql->paramAdd("references", json_encode($this->refIds));
        $this->sql->paramAdd("inReplyTo", $this->inReptyTo);
        $this->sql->paramAdd("bodyPLAIN", base64_encode($this->bodyPlain));
        $this->sql->paramAdd("bodyHTML", base64_encode($this->bodyHTML));
        $this->sql->paramAdd("direction", $this->direction);
        $this->sql->paramAdd("fromRobot", $this->fromRobot?1:0);
        
        $this->sql->paramAdd("attachments", json_encode($this->attachmentsID));
        return true;
    }
    
    protected function fillFromRow($row) {
        $this->id = $row["id"];
        $this->accountId=$row["accountId"];
        $this->messageId=$row["messageId"];
        $this->mailFrom=$this->deSerealizeAddresses(json_decode($row["mailFrom"]));
        $this->rcptTo=$this->deSerealizeAddresses(json_decode($row["rcptTo"]));
        $this->cc=$this->deSerealizeAddresses(json_decode($row["cc"]));
        $this->bcc=$this->deSerealizeAddresses(json_decode($row["bcc"]));
        $this->subject=base64_decode($row["subject"]);
        $this->date = $row["date"];
        $this->createDate=$row["dateEntry"];
        $this->refIds=json_decode($row["references"]);
        $this->inReplyTo=$row["inReplyTo"];
        $this->bodyPlain=base64_decode($row["bodyPLAIN"]);
        $this->bodyHTML=base64_decode($row["bodyHTML"]);
        $this->direction=$row["direction"];
        $this->attachments=[];
        $this->attachmentsID=json_decode($row["attachments"]);
        $this->fromRobot=$row["fromRobot"]==1;
    }

    public function preSave() {
        if ($this->direction=='TX') {
            if (empty($this->accountId)) {
                $this->account=mailAccount::getDefaultAccount($this->sql);
                if (empty($this->account)) {
                    throw new \Exception("Default mail account absent!");
                }
                $this->accountId=$this->account->id;
            }
            if (empty($this->mailFrom)) { $this->addSender($this->account->address);}
            if (empty($this->messageId)) { $this->createMessageID();}
            if (empty($this->date=time::stamp2iso_date()));
            
        }
        
    }
    
    public function save() {
        $this->preSave();
        parent::save();
        
    }
    
    public function send($save=false) {
        
        if ($this->direction != 'TX') {
            throw new \Exception("Unable to send message with ".$this->direction." type!");
        }
        $this->preSave();
                
        $this->__get("account");
        
        if ($this->account->txProto!='smtp' || $this->account->txServer == null) {
            throw  new \Exception("This account can't send messages");
            return false;
        }
        $mail = new PHPMailer(true);
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->ContentType=PHPMailer::CHARSET_UTF8;
        try {
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = $this->account->txServer;                    // Set the SMTP server to send through
            $mail->SMTPAuth   = ($this->account->login!==null && $this->account->login != '');                                   // Enable SMTP authentication
            $mail->Username   = $this->account->login;                     // SMTP username
            $mail->Password   = $this->account->password;                               // SMTP password
            $mail->SMTPOptions = ['ssl'=> ['allow_self_signed' => true]];
            if ($this->account->txSSL) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            } else {
                $mail->SMTPSecure = false;
                if ($this->account->txPort==587) {
                    $mail->SMTPAutoTLS=true;
                } else {
                    $mail->SMTPAutoTLS=false;
                }
            }
            $mail->Port = $this->account->txPort;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            
            //Recipients
            $mail->setFrom($this->mailFrom[0]->address, $this->mailFrom[0]->name);
            foreach ($this->rcptTo as $addr) {
                $mail->addAddress($addr->address, '=?UTF-8?B?'.base64_encode($addr->name).'?=');
            }
            
            foreach ($this->cc as $addr) {
                $mail->addCC($addr->address, $addr->name);
            }
            
            foreach ($this->bcc as $addr) {
                $mail->addBCC($addr->address, $addr->name);
            }
            
            $this->getAttachments();

            foreach ($this->__get("attachments") as $att) {
                $mail->addAttachment($att->getPath(), $att->filename);         // Add attachments
            }
            
            // Content
            $mail->isHTML($this->isHTML);                                  // Set email format to HTML
            $mail->Subject =  '=?UTF-8?B?'.base64_encode($this->subject).'?=';
            $mail->Subject =  $this->subject;
            $mail->Body    = $this->bodyHTML;
            $mail->AltBody = $this->bodyPlain;
            
            $mail->addCustomHeader("Auto-Submitted", "auto-replied");
            
            $mail->send();
            
        } catch (mException $e) {
            throw new \Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
        
    }

}
?>