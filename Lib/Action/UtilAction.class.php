<?php

class UtilAction extends Action{

    function upload(){
        $image = OO('Uploader')->imageOnly()->thumb('150x150')->thumb('628x326')->upload();
        
        if(!$image->error()){
            echo json_encode(array('url'=>$image->url()));
        }
        else{
            echo json_encode(array('error'=>$image->error()));
        }
    }









}