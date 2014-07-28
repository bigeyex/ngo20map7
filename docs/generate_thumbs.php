<?php 
require_once '../ThinkPHP/Extend/Library/ORG/Util/Image.class.php';
ini_set('memory_limit', '512M');
$imagefolder='../Public/Uploaded/';
$thumbsfolder='../Public/Uploaded/';
$pics=directory($imagefolder,"jpg,JPG,JPEG,jpeg,png,PNG");
//$pics=ditchtn($pics,"tn_");
if ($pics[0]!="")
{
	foreach ($pics as $p)
	{
       if(substr($p, 0, 2)!='th'){
            exec("php thumb_one.php '".$imagefolder."' ".$p);
//            Image::thumb2($imagefolder.$p,$thumbsfolder."th150x150_".$p,'',150,150 );
//            Image::thumb2($imagefolder.$p,$thumbsfolder."th350x350_".$p,'',350,350 );
//            Image::thumb2($imagefolder.$p,$thumbsfolder."th650x650_".$p,'',650,650 );
//        		Image::thumb($p,"th150x150_".$p,'',150,150);
//        		Image::thumb($p,"th350x350_".$p,'',350,350);
//        		Image::thumb($p,"th650x650_".$p,'',650,650);
        }
	}
}

function directory($dir,$filters)
{
	$handle=opendir($dir);
	$files=array();
	if ($filters == "all"){while(($file = readdir($handle))!==false){$files[] = $file;}}
	if ($filters != "all")
	{
		$filters=explode(",",$filters);
		while (($file = readdir($handle))!==false)
		{
			for ($f=0;$f<sizeof($filters);$f++):
				$system=explode(".",$file);
				if ($system[1] == $filters[$f]){$files[] = $file;}
			endfor;
		}
	}
	closedir($handle);
	return $files;
}
?>
