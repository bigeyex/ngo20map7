<?php


class Uploader{

    protected $_uploadClass = null;
    protected $_error = null;
    protected $_thumb_dimensions = null;
    
    public function __construct(){
        import('ORG.Net.UploadFile');
        import('ORG.Util.Image');
        $this->_uploadClass = new UploadFile();
        $this->_uploadClass->savePath = './Public/Uploaded/';
        $this->_uploadClass->maxSize = 3145728;
        $this->_uploadClass->saveRule = 'uniqid';
    }
    
    public function maxSize($size){
        if(substr($size, -1) == 'm'){
            $size = $size * 1024 * 1024;
        }
        if(substr($size, -1) == 'k'){
            $size = $size * 1024;
        }
        $this->_uploadClass->maxSize = $size;
        return $this;
    }
    
    public function savePath($path){
        $this->_uploadClass->savePath = $path;
        return $this;
    }
    
    public function imageOnly(){
        $this->_uploadClass->allowExts = array('jpg', 'gif', 'png', 'jpeg');
        return $this;
    }
    
    public function docOnly(){
        $this->_uploadClass->allowExts = array('xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx');
        return $this;
    }
    
    public function extOnly($ext){
        $this->_uploadClass->allowExts = $ext;
        return $this;
    }
    
    public function upload(){
        // process thumb data
        if($this->_thumb_dimensions !== null){
            $tp = $tw = $th = array();
            $this->_uploadClass->thumb = true;
            foreach($this->_thumb_dimensions as $dim){
                $tp[] = 'th'.$dim[0].'x'.$dim[1].'_';
                $tw[] = $dim[0];
                $th[] = $dim[1];
            }
            $this->_uploadClass->thumbPrefix = implode(',', $tp);
            $this->_uploadClass->thumbMaxWidth = implode(',', $tw);
            $this->_uploadClass->thumbMaxHeight = implode(',', $th);
        }

        if($this->_uploadClass->upload()){
            $this->_uploadInfo = $this->_uploadClass->getUploadFileInfo();
        }
        else{
            $this->_error = $this->_uploadClass->getErrorMsg();
        }
        return $this;
    }
    
    public function error(){
        return $this->_error;
    }
    
    public function url($id=0){
        if(is_numeric($id)){
            return $this->_uploadInfo[$id]["savename"];
        }
        else{
            foreach($this->_uploadInfo as $info){
                if($info['key'] == $id){
                    return $info["savename"];
                }
            }
        }
        return null;
    }
    
    public function thumb($dim){
        if(strpos($dim, 'x')!==false){
            if($this->_thumb_dimensions === null){
                $this->_thumb_dimensions = array();
            }
            $this->_thumb_dimensions[] = explode('x', $dim);
        }
        return $this;
    }
    
    
}