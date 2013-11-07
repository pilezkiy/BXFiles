<?
function PR($o,$toString = false,$ltf=false)
{
  if($toString)ob_start();
  $bt_src =  debug_backtrace();
  if($ltf)
	$bt = $bt_src[1];
  else
	$bt = $bt_src[0];
  $dRoot = $_SERVER["DOCUMENT_ROOT"];
  $dRoot = str_replace("/","\\",$dRoot);
  $bt["file"] = str_replace($dRoot,"",$bt["file"]);
  $dRoot = str_replace("\\","/",$dRoot);
  $bt["file"] = str_replace($dRoot,"",$bt["file"]);
  ?>
  <div style='font-size:9pt; color:#000; background:#fff; border:1px dashed #000;'>
  <div style='padding:3px 5px; background:#99CCFF; font-weight:bold;'>File: <?=$bt["file"]?> [<?=$bt["line"]?>] <?=date("d.m.Y H:i:s")?></div>
  <pre style='padding:10px;'><?print_r($o)?></pre>
  </div>
  <?  
  if($toString)return ob_get_clean(); 
}

function LTF($var,$unlink = false)
{
	$logFile = $_SERVER["DOCUMENT_ROOT"]."/ltf_log.html";
	if($unlink)
	{
		unlink($logFile);
	}
	file_put_contents($logFile,PR($var,true,true)."\n",FILE_APPEND);
}
?>