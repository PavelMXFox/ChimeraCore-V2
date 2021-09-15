<?php 
namespace fox;

class baseModule
{
	public static $title="Basic Module Template";
	public static $desc="Базовый класс модуля-заглушки";
	public static $version="0.0.0";
	public static $type="fake";
	
	public static $allowAlias="false";
	public static $authRequred=true;
	
    /* globalAccessKey:
     * Уровень доступа, необходимый для вызова функций mainPage и ajaxPage.
     * Если у пользователя нет необходимого уровня доступа для инстанса или all
     * то селектор не передает запросы модулю.
     * 
     * Если установлен в null - контроль доступа к модулю системой не осуществляется,
     * а осуществляется только средствами самого модуля.
     * 
     * isRoot - базовый ключ доступа, означающий запрет доступа для всех, кроме Администратора
     * 
     */
	
	public static $globalAccessKey="isRoot";
	/* Features:
	 * - page -    модуль отображает странцу web-интерфейса
	 * - auth -    модуль предоставляет интерфейс аутентификации 
	 *             (Используется как модуль для coreAuth)
	 * - cron -    модуль имеет функции обслуживания, которые нужно запускать по cron
	 *             (Используется как модуль для coreCron)
	 * - log -     модуль используется как интерфейс для coreLogger
	 * 
	 * Эти фичи все или частично записываются в таблицу при установке модуля. Если фичи нет
	 * в таблице - она использоваться не будет. Запись в таблицу фич, которых нет в модуле 
	 * недопустимо так как может привести к ошибкам в работе системы.
	 * 
   	 */
	public static $features=[];
	public static $menuItem=[];
	public static $sqlTables=[];
	public static $dependsOn=[];
	public static $ACLRules=[];
	
	public static $staticData=[];
	
	
	public $instance=null;
	protected $modPath=null;
	protected $breadcrumbs;
	protected $settings=[];
	protected $metadata=[];
    
	protected function logAdd(string $data, int $severity=2, string $subjClass=null, int $subjId=null, array $addFields=null)
	{
	    global $coreAPI, $session;
	    $coreAPI->logger->addEvent($this->instance, $data, $severity, $subjClass, $subjId,$addFields,$session->userId);
	}

	public static function findByInvCode($invCode) {
	    /* Return json-encoded object with props:
	     * class - class of the object
	     * id - id of the object of the class
	     * if object with this invCode not found - return false
	     */
	   
	    return false;
	}

	
	public function __construct($instance=null)
	{
	    global $coreAPI;
	    $this->core = &$coreAPI;
		if (empty($instance)) { $this->instance = preg_replace("/^mod_/","",get_class($this)); }		
		elseif ($instance != get_class($this)) { $this->instance=$instance; }
		$this->modPath=modules::getModulesPrefix()."/".get_class($this);
		
		$this->settings = config::getAll($this->instance);	
		$this->metadata = metadata::getAll($this->instance);
		
		if (!static::check()) { return false; }
	}
			
	public function install()
	{
		if ($this->check()) { return true; }
		// Устанавливает модуль в систему
		print "Global install of module ".static::$title."(".static::$version.")\n";
		return true;	
	}

	public function update() {
	    /* Базовая логика - 
	     * Обновления сборки 0.0.Х - ставятся автоматически
	     * через применение SQL скрипта.
	     * 
	     * Обновления релиза 0.X.0 - ставятся автоматически через 
	     * через применение SQL скрипта и проверку статических 
	     * параметров
	     * 
	     * Обновления версии X.0.0 - не устанавливаются,
	     * устанавливаются только вручную.
	     * 
	     */
	    $mod = modules::isModuleInstalled(substr(get_called_class(),4));
	    
	    $mod_v_i = explode('.', $mod->version);
	    $mod_v_a = explode('.', $this::$version);
	    $res=false;
	    print "Installed: ".$mod->version."\n";
	    print "Actual:    ".$this::$version."\n";
	    if ($mod_v_i[0] == $mod_v_a[0]) {
	        if ($mod_v_i[1] == $mod_v_a[1]) {
	            print "Build update started...\n";
	            $res=$this->doBuildUpdate();
	        } else {
	            print "Release update started...\n";
	            $res=$this->doReleaseUpdate();
	        }
	    } else {
	        print "Version update started...\n";
	        $res=$this->doVersionUpdate();
	    }
	    if ($res) {
	        $mod->updateVersion($this::$version);
	        return true;
	    } else {
	       return false;
	    }
	}
	
	protected function doReleaseUpdate($curr_version=null){
	    $sql = new sql();
	    if ($this->doBuildUpdate()) {
	        if (file_exists($this->getPath()."/install/static.json")) {
	            $insql = json_decode(file_get_contents($this->getPath()."/install/static.json"));
	            if (empty($insql)) {return false;}
	            foreach ($insql as $table=>$tdata) {
	                print "$table: key: ".$tdata->key."\n";
	                foreach ($tdata->data as $rdata) {
	                    print $rdata->{$tdata->key}.":";
	                    $res = $sql->quickExec1Line("select `".$tdata->key."` from `$table` where `".$tdata->key."` = '".$rdata->{$tdata->key}."'");
	                    if ($res) {
	                        print "Exists\n";
	                    } else {
	                        $sql->prepareInsert($table);
	                        foreach ($rdata as $field=>$val) {
    	                        $sql->paramAddInsert($field, $val);
	                        }
	                        $sql->paramClose();
	                        $sql->execute();
	                        print "Created\n";
	                    }
	                }
	                
	            }
	           return true;   
	        } else {
	            return true;
	        }
	    } else {
	        return false;
	    }
	}
	
	protected function doBuildUpdate($curr_version=null) {
	    $sql = new sql();
	    if (file_exists($this->getPath()."/install/install.sql")) {
	        $insql = explode("\n",file_get_contents($this->getPath()."/install/install.sql"));
	        if (empty($insql)) { return false;}
	        
	        foreach ($insql as $sqlQueryString) {
	            if (empty($sqlQueryString)) {continue;}
	            if (!$sql->quickExec($sqlQueryString)) { print "failed at: $sqlQueryString"; return false;}
	        }
	        return true;
	    } else {
	        return true;
	    }
	}
	
	protected function doVersionUpdate($curr_version=null) {
	    return $this->doReleaseUpdate();
	}
	
	public function check()
	{
		/* Проверяет, что модуль установлен в системе и установленная версия соответствует текущей.
			Если возможно быстрого фонового обновления - обновляет системные данные. Если нет - возвращает false.
			В случае успеха = возвращает true;
		*/

		$module = modules::isModuleInstalled(substr(get_called_class(),4));
		return (isset($module) && ($module->version == $this::$version));
	}
	
	public function dbExport() {
	    return true;
	}
	
	public function getPath() {
	    return $this->modPath;
	}
	
	public function uninstall()
	{
	
		return false;
	}	
	
	public function loginPage()
	{
		return false;
	}
	
	public function mainPage()
	{
		global $request;
		define ("currPage",$this->instance.(empty($request->function)?"_main":"_".$request->function));
		define ("breadcrumbs",$this->instance.(empty($request->function)?"":" / ".$request->function));


		$page = new page();		

		$page->getTemplate("leader");

		$page->getTemplate("header");
		
		// include($this->modPath."/inc/core_test_body.php");
		$page->getTemplate("footer");

	}

	public function ajaxPage()
	{
		print "404: Not implemented "; header('HTTP/1.0 403 Not found', true,404); exit; 
		return false;
	}

	public function cron($period=null)
	{
        return false;
	}

	public function mailHook(mailMessage $message) {
	    return false;
	}
	
    public function apiCall($request=null)
    {
        return false;
    }
    public function webHook($request=null) {
        return false;
    }
}
