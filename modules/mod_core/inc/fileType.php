<?php

namespace fox;


class fileType extends baseClass {
    public $ctype;
    public $name;
    public $icon;
    public $is_attachment;
    public $color;
    
    public function __construct($filename) {
        $f_arr = explode('.', $filename);
        $ext = strtoupper($f_arr[count($f_arr)-1]);
        $this->color="navy";
        $this->is_attachment = true;
        $this->ctype = "application/octet-stream";
        
        if ($ext == 'PDF') { $this->icon = 'far fa-file-pdf'; $this->name = 'PDF Document';$this->color="#ED0C0C"; $this->is_attachment = false; $this->ctype = "application/pdf";}
        elseif ($ext == 'ODT' || $ext == 'DOC' || $ext == 'DOCX') { $this->icon ='far fa-file-word' ;$this->name= 'Текстовый документ';$this->color="#070EC4";}
        elseif ($ext == 'TXT' || $ext == 'TEXT') { $this->icon ='far fa-file-word' ;$this->name= 'Текстовый документ';$color="#070EC4"; $this->is_attachment = true;}
        elseif ($ext == 'ODS' || $ext == 'XLS' || $ext == 'XLSX') { $this->icon ='far fa-file-excel' ;$this->name= 'Табличный документ'; $this->color="#079F00";}
        elseif ($ext == 'PNG' || $ext == 'JPG' || $ext == 'JPEG' || $ext == 'GIF' || $ext == 'TIFF') { $this->icon = 'far fa-file-image';$this->name= 'Изображение'; $this->color="#FF6600"; $this->is_attachment = false; $this->ctype = "image";}
        elseif ($ext == 'MP3' || $ext == 'WAV' ) { $this->icon = 'far fa-file-audio';$this->name= 'Аудиофайл';}
        elseif ($ext == 'AVI' || $ext == 'MP4') { $this->icon = 'far fa-file-video';$this->name= 'Видеофайл';}
        else { $icon = 'far fa-file';$alt= 'Файл';};
    }
}