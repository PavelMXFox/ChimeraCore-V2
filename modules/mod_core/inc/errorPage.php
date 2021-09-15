<?php
namespace fox;
class errorPage
{
    protected const defaultErrorDesc = [
        401=>'Unauthorized',
        402=>'Unauthorized`',
        403=>"Forbidden",
        404=>'Not found',
        500=>'Internal server error'
        
    ];
    
    public static function show($error_code=null, $error_desc=null, $light=null, $json=false) {
        
        ob_clean();
        $error_code_clean = explode(".", $error_code)[0];
        if (empty($error_code)) { $error_code = "404";}
        if (empty($error_desc)) { $error_desc=static::defaultErrorDesc[$error_code_clean]; }
        
        
        if ($light) {
            if ($json) {
                print json_encode(["status"=>"ERR","message"=>$error_code.' '.$error_desc]);
            } else {
                print 'Error: '.$error_code.' '.$error_desc;
            }
            header('HTTP/1.0 '.$error_code.' '.$error_desc, true,$error_code_clean);
            exit;
        }
        $mod_name = config::get("defaultLoginModule");
        if (!$mod_name)
        {
            $mod_name="core";
        }
        
        $sql = new sql();
        $modules = new modules($sql);
        $modules->loadModules($sql);	
        
        if (!$modules->isLoaded($mod_name)) {
            static::show($error_code,$error_desc,true);
        }
        
        $module = $modules->getClass($mod_name);
        $module->errorPage($error_code,$error_desc,$error_code_clean);
        exit;	
    }
    
}
?>