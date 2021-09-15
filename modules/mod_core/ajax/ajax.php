<?php 

switch ($request->function) {
    case "test":
        //var_dump($_SERVER);
        print json_encode(["status"=>"OK","message"=>"test"]);
        break;
        
    case "genpassw":
        $len=getVal("l");
        if (empty($len)) { $len=16; }
        switch (getVal("t")) {
            case "num":
                $ref=[0,1,2,3,4,5,6,7,8,9,0];
                break;
            default:
                $ref=null;
                break;
        }
        print json_encode(["status"=>"OK","passwd"=>fox\common::genPasswd($len,$ref)]);
        break;
    case "getmyprofile":
        global $session;
        print json_encode(["status"=>"OK","data"=>$session->auth->user]);
        break;
        
    case "getmydocs":
        global $session;
        print json_encode(["status"=>"OK","data"=>fox\legalDoc::getDocuments($session->auth->user)]);
        break;

    case "getmynackdocs":
        global $session;
        $d=fox\legalDoc::getNACKDocs($session->auth->user);
        print json_encode(["status"=>"OK","data"=>$d, "count"=>count($d)]);
        break;
        
    case "ackmydoc":
        global $session;
        print json_encode(["status"=>(fox\legalDoc::qACK($session->auth->user, getVal("id","0-9"))?"OK":"ERR")]);
        break;
        
        
    default:
        global $coreAPI;
        $coreAPI->logger->addEvent("core", "Invalid request from '".$request::getClientIP()."'",coreLogger::sevWarning,"invalidRequest",inet_aton($request::getClientIP()),(array)$request,0);
        coreErrorPage::show("404.21", "Not found",true);
        break;
}


?>