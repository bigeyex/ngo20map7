<?php

class UtilAction extends Action{

    public function zipimage(){
        import('ORG.Util.Image');
        $width = intval($_GET['width']);
        $height = intval($_GET['height']);
        $path = APP_PATH . 'Public/Uploaded/';
        $source = preg_replace('/[^\w_0-9\.]/', '', $_GET['source']);

        if(!$width || !$height || !$source || $width>1280 || $height>1280){
            Log::record("invalid argument in zipimage: $width | $height | $source");
            die('invalid argument');
        }

        Image::thumb2($path . $source, $path . 'th'.$width.'x'.$height.'_'.$source, '', $width, $height);
        $new_file_path = $path . 'th'.$width.'x'.$height.'_'.$source;
        $mime_type = mime_content_type($path . 'th'.$width.'x'.$height.'_'.$source);
        header("Content-type: $mime_type");
        readfile($new_file_path);
    }

    public function captcha(){
        import('ORG.Util.ValidateCode');
        $_vc = new ValidateCode();      //实例化一个对象
        $_vc->doimg();
        $_SESSION['verify'] =strtolower($_vc->getCode());//验证码保存到SESSION中
    }

    function upload(){
        // $image = OO('Uploader')->imageOnly()->thumb('150x150')->thumb('628x326')->upload();
        if(isset($_GET['w'])){
            $image = OO('Uploader')->maxSize('2m')->imageOnly()->thumb(intval($_GET['w']).'x'.intval($_GET['h']))->upload();
        }
        else{
            $image = OO('Uploader')->maxSize('2m')->imageOnly()->upload();
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

    function flashUpload(){
        header('Content-Type: text/html; charset=utf-8');
        $result = array();
        $result['success'] = false;
        $successNum = 0;
        //定义一个变量用以储存当前头像的序号
        $avatarNumber = 1;
        $i = 0;
        $msg = '';
        //上传目录
        $dir = APP_PATH . "Public/Uploaded";
        //遍历所有文件域
        $fileName = date("YmdHis").'_'.floor(microtime() * 1000).'_'.$this->createRandomCode(8);
        while (list($key, $val) = each($_FILES))
        {
            if ( $_FILES[$key]['error'] > 0)
            {
                $msg .= $_FILES[$key]['error'];
            }
            else
            {   
                //处理原始图片（默认的 file 域的名称是__source，可在插件配置参数中自定义。参数名：src_field_name）
                //如果在插件中定义可以上传原始图片的话，可在此处理，否则可以忽略。
                if ($key == '__source')
                {
                    //当前头像基于原图的初始化参数，用于修改头像时保证界面的视图跟保存头像时一致。帮助提升用户体验度。修改头像时设置默认加载的原图的url为此图片的url+该参数即可。
                    $initParams = $_POST["__initParams"];
                    $virtualPath = "$dir/$fileName.jpg";
                    // $result['sourceUrl'] = "$fileName.jpg";
                    move_uploaded_file($_FILES[$key]["tmp_name"], $virtualPath);
                    /*
                        可在此将 $result['sourceUrl'] 储存到数据库
                    */
                    $successNum++;
                }
                //处理头像图片(默认的 file 域的名称：__avatar1,2,3...，可在插件配置参数中自定义，参数名：avatar_field_names)
                else if (strpos($key, '__avatar') === 0)
                {
                    $virtualPath = "$dir/th" . str_replace('__avatar', '', $key) . "_$fileName.jpg";
                    // $result['avatarUrls'][$i] = '/' . $virtualPath;
                    move_uploaded_file($_FILES[$key]["tmp_name"], $virtualPath);
                    /*
                        可在此将 $result['avatarUrls'][$i] 储存到数据库
                    */
                    $successNum++;
                    $i++;
                }

                // save the filename of the first succeeded picture
                if($successNum == 1){
                    $result['sourceUrl'] = $fileName . '.jpg';
                    $originalVirtualPath = "$dir/$fileName.jpg";
                    copy($virtualPath, $originalVirtualPath);
                }
                /*
                else
                {
                    如下代码在上传接口upload.php中定义了一个user=xxx的参数：
                    var swf = new fullAvatarEditor("swf", {
                        id: "swf",
                        upload_url: "Upload.php?user=xxx"
                    });
                    在此即可用$_POST["user"]获取xxx。
                }
                */
            }
        }
        $result['msg'] = $msg;
        if ($successNum > 0)
        {
            $result['success'] = true;
        }
        //返回图片的保存结果（返回内容为json字符串）
        print json_encode($result);
    }

    private function createRandomCode($length)
    {
        $randomCode = "";
        $randomChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++)
        {
            $randomCode .= $randomChars { mt_rand(0, 35) };
        }
        return $randomCode;
    }







}