<?php
namespace fox;
require_once 'coreAPI.php';
use Exception;
use DOMDocument;
use ZipArchive;
use DOMXPath;


class oasis
{
    protected $srcFileName;
    protected $srcFileType;
    protected $newFileName;
    protected $newFileIsTemporary=true;
    
    protected $dom;
    protected $dom2;
    protected $tmpath="/tmp";
    
    protected $parent;
    protected $parent_node;
    protected $oldnode;
    
    protected $odsParams=[];
    
    public function __construct($srcFilePath=null, $tmpath=null) {
        if (!empty($tmpath)) { $this->tmpath = $tmpath; }
        if (!empty($srcFilePath)) {
            $this->loadODF($srcFilePath);
        }
        
    }
    
    public function loadODF($srcFile) {
        $mimeType=file_get_contents("zip://".$srcFile."#mimetype");
        if (!$mimeType) { throw new Exception("Unable to determine file type of ".$srcFile); }
        
        switch ($mimeType) {
            case "application/vnd.oasis.opendocument.text":
                $this->srcFileType='odt';
                break;
            case "application/vnd.oasis.opendocument.spreadsheet":
                $this->srcFileType='ods';
                break;
            default:
                throw new Exception("Unknown file type: $mimeType");
        }
        
        $this->srcFileName=$srcFile;
        
        $this->dom = new DOMDocument();
        $this->dom->load("zip://".$this->srcFileName."#content.xml");
        
        
    }
    
    public function saveODF($newFileName=null) {
        
        if (empty($newFileName)) {
            
            $uuid = getGUIDc();
            
            if (file_exists($this->tmpath) && !is_dir($this->tmpath)) {
                throw new Exception("TMPatn not a directory!");
            }
            
            if (!file_exists($this->tmpath)) {
                mkdir($this->tmpath);
            }
            
            if (file_exists($this->tmpath."/".$uuid.".".$this->srcFileType)) {
                $uuid = getGUIDc();
            }
            
            $newFileName = $this->tmpath."/".$uuid.".".$this->srcFileType;
            $this->newFileIsTemporary=true;
        } else {
            $this->newFileIsTemporary=false;
        }
        
        $this->newFileName = $newFileName;
        copy($this->srcFileName, $this->newFileName);
       
        $this->commit();
        $xml= $this->dom->saveXML();
        //file_put_contents("$tmpath/$guid/content.xml", $xml);
        
        $zip = new ZipArchive();
        
        $res = $zip->open($this->newFileName);
        if ($res === TRUE) {
            $zip->addFromString('content.xml', $xml);
            $zip->close();
        } else {
            throw new Exception("Unable to save result");
        }
        
        return $newFileName;
    }
    
    public function export($type, $newFileName=null) {
        $ods=$this->saveODF();
        return fileConverter::convert($ods, $newFileName, $type);
    }
    
    public function commit() {
        if ($this->srcFileType=='ods') {
            $this->odsCommitParams();
        }
        
        if (!empty($this->parent) && !empty($this->parent->documentElement)) {
            // Импортируем созданый ранее элемент в текущее дерево
            $newnode = $this->dom->importNode($this->parent->documentElement, true);
            
            $this->oldnode->parentNode->replaceChild($newnode, $this->oldnode);
            
            
            $this->parent=null;
            $this->oldnode=null;
            $this->parent_node=null;
            $this->dom2 = null;
        }
    }

    public function odsCommitParams($erase_notfound_params=true) {
        if ($this->srcFileType != 'ods' ) { return false; }
        if (empty($this->odsParams)) { return true; }
        
        $xpath = new DOMXpath($this->dom);
        $nodelist = $xpath->query("/office:document-content/office:body/office:spreadsheet/table:table");
        $this->oldnode = $nodelist->item(0);
        
        $this->parent = new DomDocument;
        $this->parent_node=$this->parent->importNode($this->oldnode);

        foreach ($this->oldnode->childNodes as $t_node) {
            if ($t_node->nodeName=='table:table-row') {
                foreach($t_node->getElementsByTagname("*") as $t2_node) {
                    $res=null;
                    if ($t2_node->nodeName == 'text:p' && preg_match("/^<t:(.*)>$/", $t2_node->nodeValue,$res)) {
                        if ((array_key_exists($res[1], $this->odsParams))) {
                            
                            $this->odsUpdateCellValue($this->odsParams[$res[1]], $t2_cell = $t2_node->parentNode);
                        } elseif ($erase_notfound_params) {
                            $this->odsUpdateCellValue(null, $t2_node->parentNode);
                        }
                    }
                }
            }
        }
        $this->odsParams=null;
    
    }
    
    public function addParam($paramName, $paramValue)
    {
        if ($this->srcFileType == 'odt') {
        
            if (is_null($this->parent)) {
                $this->prepareODFParam();
            }
            // Создаем дочерний элемент в структуре
            $child_node=$this->parent->createElement('text:user-field-decl');
            
            $attribute=$this->parent->createAttribute("office:value-type");
            $attribute->value = "string";
            $child_node->appendChild($attribute);
            
            $attribute=$this->parent->createAttribute("office:string-value");
            $attribute->value = $paramValue;
            $child_node->appendChild($attribute);
            
            $attribute=$this->parent->createAttribute("text:name");
            $attribute->value = $paramName;
            $child_node->appendChild($attribute);
            
            $this->parent_node->appendChild($child_node);
            
            $this->parent->appendChild($this->parent_node);
            // закончили создавать дочерний элемент
        } elseif ($this->srcFileType=='ods') {
            if (empty($this->odsParams)) { $this->odsParams = []; };
            $this->odsParams[$paramName]=$paramValue;
        }
        
    }
      
    public function odtTableRowAdd($tag, $row_count)
    {
        if ($this->srcFileType != 'odt') { return false; }
        if (is_null($this->dom2)) {
            $this->odtPrepareTableRowAdd();
        }
        
        $nodelist = $elements = $this->dom->getElementsByTagname("user-field-get");
        $table_row_node = null;
        
        foreach ($nodelist as $node) {
            
            
            if (!is_null($table_row_node)) { break; };
            
            $tag_class =  explode('.',$node->getAttribute('text:name'),2 )[0];
            
            if ($tag_class==$tag) {
                $n = $node;
                while (($n = $n -> parentNode) && (is_null($table_row_node)))
                {
                    if ($n -> nodeName == 'table:table-row') {
                        $table_row_node = $n;
                        break;
                    }
                }
            }
        }
        
        if (is_null($table_row_node))
        {
            return -1;
        }
        
        $table_node = $table_row_node -> parentNode;
        
        $element = $this->dom2->importNode($table_node,false);
        
        foreach ($table_node->childNodes as $node)
        {
            $nn= $this->dom2->importNode($node, true);
            
            $nodelistS = $node->getElementsByTagname("user-field-get");
            
            $nodeV = false;
            
            foreach ($nodelistS as $nodeS) {
                $tag_class =  explode('.',$nodeS->getAttribute('text:name'),2 )[0];
                
                if ($tag_class==$tag) {
                    $nodeV = true;
                    break;
                }
            }
            
            if ($nodeV) {
                //    	    print "NODEV ".$nodeS->getAttribute('text:name')."\n";
                for ($i=0; $i < $row_count; $i++) {
                    $element->appendChild($this->odtTableNodeAddIdx($table_row_node,$tag,$i));
                }
                
            } else {
                //	    print "General Node\n";
                $element->appendChild($nn);
            }
        }
        
        
        $newnode = $this->dom->importNode($element,true);
        $table_node->parentNode->replaceChild($newnode, $table_node);
        return 1;
    }
    
    public function odsInsertData($arr, $marker='<d:start>') {
        if ($this->srcFileType != 'ods') { return false; }
        if (empty($this->parent)) {
            $xpath = new DOMXpath($this->dom);
            $nodelist = $xpath->query("/office:document-content/office:body/office:spreadsheet/table:table");
            $this->oldnode = $nodelist->item(0);
            
            $this->parent = new DomDocument;
            //$this->parent_node = $this->parent->createElement('table:table');
            $this->parent_node=$this->parent->importNode($this->oldnode);
        }
       
        $marker_found=false;
        foreach ($this->oldnode->childNodes as $t_node) {
            if (!$marker_found && $t_node->nodeName=='table:table-row') {
                foreach($t_node->getElementsByTagname("*") as $t2_node) {
                    if ($t2_node->nodeName == 'text:p' && $t2_node->nodeValue == $marker) {
                        $marker_found=true;
                        $t2_row = $t2_node->parentNode->parentNode;
                        for ($i=0; $i<count($arr); $i++) {
                            $v_arr=$arr[$i];
                            $v_idx=0;
                            foreach ($t2_row->childNodes as $t2_cell) {
                                $v_idx++;
                                if ($v_idx-1 > array_key_last($v_arr)) { $val=null;} elseif (array_key_exists($v_idx-1, $v_arr)) {$val=$v_arr[$v_idx-1];} else { $val=null; }
                                
                                $this->odsUpdateCellValue($val, $t2_cell);
                                
                            }
                            $this->parent_node->appendChild($this->parent->importNode($t_node,true));
                        }
                        continue(2);
                    }
                }
            }
            $this->parent_node->appendChild($this->parent->importNode($t_node,true));
        }
        $this->parent->appendChild($this->parent_node);
    }
    
    protected function odsUpdateCellValue($val, $t2_cell) {
        
        switch (gettype($val)) {
            case "string":
                $val_type = 'string';
                break;
            case "integer":
                $val_type='float';
                break;
            case "float":
                $val_type='float';
                break;
            case "double":
                $val_type='float';
                break;
            case "NULL":
                $val_type='null';
                break;
            default:
                $val_type='string';
                break;
        }
        
        
        foreach ($t2_cell->childNodes as $t2_child) {
            $t2_cell->removeChild($t2_child);
        }
        
        switch ($val_type) {
            case "string":
                $t2_cell->setAttribute("office:value-type", $val_type);
                $t2_cell->setAttribute("calcext:value-type", $val_type);
                $t2_cell->appendChild($this->dom->createElement('text:p',$val));
                break;
                
            case "null":
                $t2_cell->removeAttribute("office:value-type");
                $t2_cell->removeAttribute("calcext:value-type");
                $t2_cell->removeAttribute("office:value");
                break;
                
            case "float":
                $t2_cell->setAttribute("office:value-type", $val_type);
                $t2_cell->setAttribute("calcext:value-type", $val_type);
                $t2_cell->setAttribute("office:value", $val);
                break;
        }
    }
    
    protected function prepareODFParam()
    {
        
        $xpath = new DOMXpath($this->dom);
        $nodelist = $xpath->query("/office:document-content/office:body/office:text/text:user-field-decls");
        
        $this->oldnode = $nodelist->item(0);
        
        $this->parent = new DomDocument;
        $this->parent_node = $this->parent->createElement('text:user-field-decls');
    }
           
    protected function odtPrepareTableRowAdd()
    {
        $this->dom2 = new DomDocument;
        
    }
    
    protected function odtTableNodeAddIdx($row_node, $key, $idx)
    {
        
        $d2node = $this->dom2->importNode($row_node,true);
        
        $nodelist = $elements = $d2node->getElementsByTagname("user-field-get");
        
        foreach ($nodelist as $node) {
            
            $tag_class =  explode('.',$node->getAttribute('text:name'),2 )[0];
            
            if ($tag_class==$key) {
                $node -> setAttribute('text:name',$node->getAttribute('text:name').$idx);
            }
        }
        
        return $d2node;
    }
    
    public function __destruct() {
        if ($this->newFileIsTemporary && file_exists($this->newFileName)) {
            unlink($this->newFileName);
        }
    }
}

?>