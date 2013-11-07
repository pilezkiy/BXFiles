<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
?>
<html>
<head>
<title>Импорт каталога</title>
<script src="http://yandex.st/jquery/1.6.4/jquery.min.js"></script>
<style>
body{width:680px;margin:auto;padding:40px;}
#import_status{margin:10px 0; padding:10px; background:#FFF; border:#ccc;}
#import_proccess{margin:10px 0; padding:10px; background:#CCFFCC; border:#ccc;}
#import_log{margin:10px 0; padding:10px; background:#f0f0f0; border:#ccc;}
#import_log p{margin:0;padding:0;font-style:italic;}
</style>	
</head>
<body>
<?
global $USER;
if($USER->isAdmin())
{
	?><p><a href="/">Перейти на главную</a></p><?
	$xmlFiles = array();
	$xmlDir = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/";
	if ($handle = opendir($xmlDir)) {
		while (false !== ($file = readdir($handle))) { 
			if(preg_match("#\.xml$#",$file))
			{
				$xmlFiles[] = $file;
			}
		}
		closedir($handle); 
	}
	sort($xmlFiles);
	//$xmlFiles = array($xmlFiles[0]);
	?>
	<p><button id="start_import">Начать импорт!</button></p>
	<script>
	var xmlFiles = <?=json_encode($xmlFiles)?>;
	var KEY = 0;
	var xmlFilesLength = xmlFiles.length;
	$(function(){
		function importFile()
		{
			var fileName = xmlFiles[KEY];
			$("#import_status").html("Импорт файла "+fileName+" ");
			$("#import_status").append("<img src='bar120.gif'/>");
			var url = "/bitrix/admin/1c_exchange.php?type=catalog&mode=import&filename="+fileName;
			
			$("#import_proccess").load(url,function(){
				$("#import_status img").remove();
				var data = $("#import_proccess").text();
				var regexpProccess = /progress/;
				var regexpSuccess = /success/;
				if(regexpProccess.test(data))
				{	// процесс идет
					setTimeout(importFile(),1000);
				}
				else if(regexpSuccess.test(data))
				{	//импорт файла завершен
					$("#import_log").append("<p>Файл "+fileName+" импортирован.</p>");
					KEY++;
					if(KEY<xmlFilesLength)setTimeout(importFile(),1000);
					else
					{
						$("#import_status").html("Импорт завершен.");
						$("#import_log").append("<p>Импорт завершен.</p>");
					}
				}
				else
				{	//случилась какая-то пепяка, попробовать снова
					setTimeout(importFile(),1000);
				
				}
			});
		}
		
		$("#start_import").click(function(){
			$(this).parent("p").remove();
			importFile();
		});
		
	});
	</script>
	<div id="import_status"></div>
	<div id="import_proccess"></div>
	<div id="import_log"></div>
	<?
}
else
{
?>
<table width=100% height=100%>
<td valign=middle align=center>
	<div style="width:300px;"><?$APPLICATION->IncludeComponent("bitrix:system.auth.form","",array(),false);?></div>
</td>
</table>
<?
}
?>
</body>
</html>