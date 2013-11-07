<?
function generateSiteMap()
{
	//подключение модуля поиска 
	if(CModule::IncludeModule('search')) 
	{ 
	   //В этом массиве будут передаваться данные "прогресса". Он же послужит индикатором окончания исполнения. 
	   $NS = Array(); 
	   //Задаем максимальную длительность одной итерации равной "бесконечности". 
	   $sm_max_execution_time = 0; 
	   //Это максимальное количество ссылок обрабатываемых за один шаг. 
	   //Установка слишком большого значения приведет к значительным потерям производительности. 
	   $sm_record_limit = 5000; 
	   do { 
		  $cSiteMap = new CSiteMap; 
		  //Выполняем итерацию создания, 
		  $NS = $cSiteMap->Create("s1", array($sm_max_execution_time, $sm_record_limit), $NS); 
		  //Пока карта сайта не будет создана. 
	   } while(is_array($NS)); 
	}
	return "generateSiteMap();";
}
?>