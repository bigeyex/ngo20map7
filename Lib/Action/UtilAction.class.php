<?php

class UtilAction extends Action{

    public function captcha(){
        import('ORG.Util.ValidateCode');
        $_vc = new ValidateCode();      //实例化一个对象
        $_vc->doimg();
        $_SESSION['verify'] =strtolower($_vc->getCode());//验证码保存到SESSION中
    }

    function upload(){
        // $image = OO('Uploader')->imageOnly()->thumb('150x150')->thumb('628x326')->upload();
        if(isset($_GET['w'])){
            $image = OO('Uploader')->imageOnly()->thumb(intval($_GET['w']).'x'.intval($_GET['h']))->upload();
        }
        else{
            $image = OO('Uploader')->imageOnly()->upload();
        }
        
        if(!$image->error()){
            echo json_encode(array('url'=>$image->url()));
        }
        else{
            echo json_encode(array('error'=>$image->error()));
        }
    }

    function bm_upload(){
        // $image = OO('Uploader')->imageOnly()->thumb('150x150')->thumb('628x326')->upload();
        $image = OO('Uploader')->imageOnly()->upload();
        
        if(!$image->error()){
            $type = $_REQUEST['type'];
            $editorId=$_GET['editorid'];
            if($type == "ajax"){
                echo __APP__ . '/Public/Uploaded/' . $image->url();
            }else{
                echo "<script>parent.UM.getEditor('". $editorId ."').getWidgetCallback('image')('" .$_SERVER['SERVER_NAME']. __APP__ . '/Public/Uploaded/' . $image->url() . "','" . 'SUCCESS' . "')</script>";
            }
        }
        else{
            echo json_encode(array('error'=>$image->error()));
        }
    }

    function cropResize($src, $x, $y, $w, $h, $resizeW, $resizeH){
        import('ORG.Util.Image');
        // 获取原图信息
        $filename = APP_PATH.'Public/Uploaded/'.$src;
        $thumbname = 'th'.$resizeW.'x'.$resizeH.'_'.$src;
        $thumbpath = APP_PATH.'Public/Uploaded/'.$thumbname;
        // echo $filename;
        $info = Image::getImageInfo($filename);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
            $interlace = $interlace ? 1 : 0;
            unset($info);

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $srcImg = $createFun($filename);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($resizeW, $resizeH);
            else
                $thumbImg = imagecreate($resizeW, $resizeH);
            // 复制图片

            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, $x, $y, $resizeW, $resizeH, $w, $h);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, $x, $y, $resizeW, $resizeH, $w, $h);
            if ('gif' == $type || 'png' == $type) {
                imagealphablending($thumbImg, false);//取消默认的混色模式
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                $background_color = imagecolorallocate($thumbImg, 0, 255, 0);  //  指派一个绿色
                imagecolortransparent($thumbImg, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $thumbpath);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            echo $thumbname;
        }

    }









}