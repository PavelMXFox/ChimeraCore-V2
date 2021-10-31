<?php
/*
 * require_once 'auth.php';
require_once 'baseModule.php';
require_once 'baseModule_Auth.php';
require_once 'baseModule_Logger.php';
require_once 'baseModule_Messenger.php';
require_once 'baseModule_Payment.php';
require_once 'baseClass.php';
require_once 'common.php';
require_once 'companyType.php';
require_once 'company.php';
require_once 'config.php';
require_once 'errorPage.php';
require_once 'file.php';
require_once 'fileType.php';
require_once 'fileConverter.php';
require_once 'session.php';
require_once 'session_Web.php';
require_once 'session_API.php';
require_once 'logger.php';
require_once 'metadata.php';
require_once 'moduleInfo.php';
require_once 'modules.php';
require_once 'net.php';
require_once 'pid.php';
require_once 'page.php';
require_once 'sql.php';
require_once 'oasis.php';
require_once 'time.php';
require_once 'UID.php';
require_once 'urlParcer.php';
require_once 'user.php';
require_once 'mailAccount.php';
require_once 'mailClient.php';
require_once 'mailMessage.php';
require_once 'xcrypt.php';
require_once 'fail2ban.php';
require_once 'authTicket.php';
require_once 'foxException.php';
require_once 'searchResult.php';
require_once 'APITokenIface.php';
require_once 'APIToken.php';
require_once 'APIPacket.php';
require_once 'APIMessage.php';
require_once 'notifier.php';
*/
require_once 'Autoloader.php';

# API v2 back-compatibility
# remove in next version
require_once 'v2_common.php';
require_once 'v2_pid.php';
require_once 'v2_foxApi.php';


class coreAPI {
    public coreSql $SQL;
    public coreLogger $logger;
    
    function __construct()
    {
        $this->SQL = new coreSql();
        $this->logger=new coreLogger();
    }
}

$coreAPI = new coreAPI();


?>