<?php
namespace fox {
use \Exception;

require_once("coreAPI.php");

class sql
{
	
	var $mysqli;

	var $stmt;
	var $bind_names;
	var $ctr;
	var $sqlQueryString;
	var $sqlQueryStringL;
	var $param_type;
	var $queryType;	
	var bool $paramClosed=false;
	
	protected bool $connected=false;
	protected ?string $server=null;
	protected ?string $db=null;
	protected ?string $user=null;
	protected ?string $passwd=null;

	public const ERR_DUPLICATE=1062;
	public const ERR_NOT_NULL_COLUMN=1048;
	public const ERR_NO_DEFAULT=1364;
	
    function __construct($server=null, $db=null, $user=null, $passwd=null)
    {
        if (!isset($server))
        {
            $server = config::get("sqlServer");
            $user = config::get("sqlUser");
            $passwd = config::get("sqlPasswd");
            $db = config::get("sqlDB");
        }

        $this->server=$server;
        $this->db=$db;
        $this->user=$user;
        $this->passwd=$passwd;
        $this->connected=false;
		//$this->mysqli = $this->connect($server, $db, $user, $passwd);
    }

	function connect($server=null, $db=null, $user=null, $passwd=null)
	{
	    if (!$this->connected)
	    {
    	    if (isset($server))
    	    {
    	        $this->server=$server;
    	        $this->db=$db;
    	        $this->user=$user;
    	        $this->passwd=$passwd;
    	        $this->connected=false;
    	    }
    		
    		$this->mysqli = @mysqli_connect($this->server, $this->user, $this->passwd, $this->db);
    		mysqli_set_charset ($this->mysqli , "utf8" );
    		if (!$this->mysqli) // Если дескриптор равен 0 соединение не установлено
    		{
    			 
    		    throw new Exception("SQL Connection to $this->server failed");
    		    exit();
    		}
    		$this->connected=true;
	    }
		return $this->mysqli;
	}

	// Express Functions	
	
	static function sqlQuickExec($sqlQueryString)
	{
		$sql = new self();
		return $sql->quickExec($sqlQueryString);
	}	

	static function sqlQuickExec1Line($sqlQueryString)
	{
		$sql = new self();
		return $sql->quickExec1Line($sqlQueryString);
	}	
	
   function quickExec($sqlQueryString=null, &$result=null, $hideError=null)
	{
        $this->connect();
		if ($this->mysqli->connect_errno) {
		if (!isset($hideError)) { throw new Exception("SQL Connect error");};
	        return null;
	   }
	    
	   $result = $this->mysqli->query($sqlQueryString);
            
	   if (!$result) {
		if (!isset($hideError)) {throw new Exception("SQL Error: .".$this->mysqli->error);};
      	return null;
        	exit;
    	}
    	return $result;
	}

	function quickExec1Line($sqlQueryString, &$result=null,$hideError=null)
	{
	   $this->connect();
	   $result = $this->quickExec($sqlQueryString, $result, $hideError);
	   if (mysqli_num_rows($result) == 0)
	   {
			return null;
	   }
   
   	$retVal = mysqli_fetch_assoc($result);
   	return $retVal;
	}

	// General functions 
	
	function prepare()
	{
		$this->ctr=0;
		$this->bind_names =null;
		$this->param_type=null;
		$this->stmt = null;
		$this->sqlQueryString=null;
		$this->sqlQueryStringL=null;
		$this->paramClosed=false;
	}	
	
	function prepareUpdate($tableName)
	{
		$this->prepare();
		$this->queryType="update";
		$this->sqlQueryString = "UPDATE `$tableName` SET";	
		
	}	

	function prepareInsert($tableName)
	{
		$this->prepare();
		$this->queryType="insert";
		$this->sqlQueryString = "INSERT INTO `$tableName` (";	
	}	
	
	function execute()
	{
	    if (!$this->paramClosed && $this->queryType=="insert") { $this->paramClose(); }
	    if (!$this->paramClosed) { throw new Exception("Params not closed for ".$this->queryType."!"); }
	    
	    $this->connect();
		if ($this->ctr > 0)
		{
			$this->stmt = mysqli_prepare($this->mysqli, $this->sqlQueryString);
	
			if (!$this->stmt)
			{
				$err = 'ERR:EXEC 1P'.mysqli_errno($this->mysqli).' '. mysqli_error($this->mysqli);
				throw new Exception($err,mysqli_errno($this->mysqli));
				exit;
		
			}
			call_user_func_array(array($this->stmt,'bind_param'),$this->bind_names);
			if (!(mysqli_stmt_execute($this->stmt)))
			{
				$err = 'ERR:EXEC 2P'.mysqli_errno($this->mysqli).' '.$this->stmt->error. mysqli_error($this->mysqli);
				$errNo=mysqli_errno($this->mysqli);
				$this->stmt->close();
				throw new Exception($err, $errNo);
				exit;
			}

			return true;
		}
	}	
	
	function quickExecute()
	{
		if (!$this->execute())
		{

		    if ($this->stmt) { $this->stmt->close(); }
		    throw new \Exception('ERR:EXEC 3P'.mysqli_errno($this->mysqli).'  '. mysqli_error($this->mysqli), mysqli_errno($this->mysqli));
		    exit;	
		}
		$this->stmt->close();
		return true;
	}
	
	function getInsertId()
	{
		return mysqli_insert_id($this->mysqli);	
	}

	function paramAdd($sqlParamName, $paramValue,$valName=null, $setNull=false, $oldVal = null,$varTitleName=null)
	{
	    if ($this->queryType=="insert") {
	       return $this->paramAddInsert($sqlParamName, $paramValue, $valName, $setNull);
	    } elseif ($this->queryType=="update") {
	       return $this->paramAddUpdate($sqlParamName, $paramValue,$valName, $setNull, $oldVal,$varTitleName);
	    }
	}
	
	function paramAddInsert($sqlParamName,$paramValue=null,$valName=null,$setNull=null)
	{
		if (isset($paramValue)) { $var=$paramValue; }
		elseif(isset($valName)) { $var = getVal($valName,null,true);}
		else {$var=null; }
		
		if (($var!==null) || $setNull)
		{		    
		    
			if ($setNull) {$var = null;}
			if($this->ctr != 0) {$this->sqlQueryString .= ', '; $this->sqlQueryStringL .= ', ';}	
			$this->sqlQueryString .= "`$sqlParamName` ";
			$this->sqlQueryStringL .= "? ";
				
			$this->ctr++;


			if (!isset($this->bind_names))
			{
				$x='XX';
				$bind_name = 'bind' . $this->ctr;
				$$bind_name = $x;
		      $this->bind_names[] = &$$bind_name;
		
			}		
			$bind_name = 'bind' . $this->ctr;
			$$bind_name = $var;
	      $this->bind_names[] = &$$bind_name;
	
	      $this->param_type .= 's';
		}
	
	}

	function paramAddUpdate($sqlParamName, $paramValue=null,$valName=null, $setNull=null, $oldVal = null,$varTitleName=null)
	{
		$var = getVal($valName,null,true);
		if (isset($paramValue))
		{
			$var=$paramValue;
		}
					
		if ($var!==null || $setNull)
		{
			if ($setNull) {$var = null;}
	
			if($this->ctr != 0) {$this->sqlQueryString .= ', ';}
				
			$this->sqlQueryString .= "`$sqlParamName`=? ";	
			$this->ctr++;
		
	
			if (!isset($this->bind_names))
			{
				$x='XX';
				$bind_name = 'bind' . $this->ctr;
				$$bind_name = $x;
		      $this->bind_names[] = &$$bind_name;	
			}		
		
		
		
			$bind_name = 'bind' . $this->ctr;
			$$bind_name = $var;
	      $this->bind_names[] = &$$bind_name;

	      $this->param_type .= 's';
     
		   return $varTitleName." changed from ".$oldVal." to ".$var;
      
		}
	
	}

	function paramClose($sqlQueryStringWhere=null) 
	{
			$x='XX';
			$bind_name = 'bind';
			$$bind_name = $this->param_type;
		   $this->bind_names[0] = &$$bind_name;
		   
		   if ($this->queryType == "insert")
		   {
		   	$this->sqlQueryString =  $this->sqlQueryString.") VALUES (".$this->sqlQueryStringL.")";
		   } elseif (isset($sqlQueryStringWhere)) {
		   	$this->sqlQueryString .= " where ".$sqlQueryStringWhere;
		   }
		   
		   $this->paramClosed=true;
	}

	function export($tlist) {
	    $retval="";
	    if (gettype($tlist) == "string") {
	        $tlist=array($tlist);
	    } elseif (gettype($tlist) != "array") {
	        throw new Exception("Incorrect type ".gettype($tlist)." for tables. Expecting 'string' or 'array'");
	    }
	    
	    foreach ($tlist as $table) {
	        $res = $this->quickExec1Line("SHOW CREATE TABLE `$table`");
	        
	        if (array_key_exists("Create Table",$res))
	        {
	            $t_create = $res["Create Table"];
	            
	            //$t_create = "`authSecret` varchar(255) DEFAULT NULL,";
	            
	            //print $t_create;
	            
	            $retval .= "CREATE TABLE IF NOT EXISTS $table (zzz int);\n";
	            
	            $columns = preg_split("/[\n\r]/", $t_create);
	            
	            // Определяем столбцы
	            foreach ($columns as $col)
	            {
	                if (preg_match("/^[ ]*(`(.*)`\ [a-z\(0-9\)]*\ [A-Z _'0-9a-z]*)/", $col, $matches))
	                {
	                    //var_dump($matches);
	                    if (preg_match("/AUTO_INCREMENT/",$col))
	                    {
	                        $retval .=  "ALTER TABLE `$table` ADD COLUMN IF NOT EXISTS ".$matches[1]." PRIMARY KEY;\n";
	                    } else {
	                        $retval .=  "ALTER TABLE `$table` ADD COLUMN IF NOT EXISTS ".$matches[1].";\n";
	                    }
	                }
	            }
	            
	            $retval .=  "ALTER TABLE `$table` DROP COLUMN IF EXISTS zzz;\n";
	            
	            // Определяем индексы
	            foreach ($columns as $col)
	            {
	                if (preg_match("/^[ ]*(([A-Z ]*)KEY\ `(.*)`\ \((.*)\))/", $col, $matches))
	                {
	                    //var_dump($matches);
	                    //print "ALTER TABLE `$table` ADD COLUMN ".$matches[1].";\n";
	                    $retval .=  "DROP INDEX IF EXISTS`".$matches[3]."` ON `$table`;\n";
	                    $retval .=  "CREATE ".$matches[2]."INDEX `".$matches[3]."` ON `$table` (".$matches[4].");\n";
	                }
	            }
	        } elseif (array_key_exists("Create View",$res))
	        {
	            $retval .=  "DROP VIEW IF EXISTS `".$table."`;\n";
	            $retval .=  $res["Create View"].";\n";
	        }
	        
	        //$retval .=  "\n";
	    }
	    return $retval;
	}

	function getAffectedRows() {
	    return $this->mysqli->affected_rows;
	}
}
}

?>