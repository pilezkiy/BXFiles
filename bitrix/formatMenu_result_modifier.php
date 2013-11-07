<?
/*
* result_modifier.php для компонента bitrix:menu
* Преобразует массив меню в удобную иерархическую структуру.
*
*
*/
$formatMenuTree = function ($listmenu,$depth,&$i,&$numBreak) use (&$formatMenuTree)
{
	$menu = array();
	$cnt = count($listmenu);
	for(;$i<$cnt;$i++)
	{
		$mItem = $listmenu[$i];
		$menu[] = $mItem;
		if(isset($listmenu[$i+1]) && $listmenu[$i+1]["DEPTH_LEVEL"] < $mItem["DEPTH_LEVEL"])
		{
			$numBreak = ($mItem["DEPTH_LEVEL"] - $listmenu[$i+1]["DEPTH_LEVEL"])-1;
			break;
		}
		$nextIsChild = (isset($listmenu[$i+1]) && $listmenu[$i+1]["DEPTH_LEVEL"] > $mItem["DEPTH_LEVEL"]);
		if($mItem["IS_PARENT"] && $nextIsChild)
		{
			$lastIndex = count($menu)-1;
			$i++;
			$menu[$lastIndex]["ITEMS"] = $formatMenuTree($listmenu,$depth+1,$i,$numBreak);
			if($numBreak>0)
			{
				$numBreak--;
				break;
			}
		}
	}
	return $menu;
};

$i = 0; $numBreak = 0;
$arResult = $formatMenuTree($arResult,1,$i,$numBreak);


$setSelectedLabels = function(&$menu) use (&$setSelectedLabels)
{
	foreach($menu as $i=>$mItem)
	{
		if($mItem["SELECTED"])return 1;
		if(count($mItem["ITEMS"])>0)$menu[$i]["SELECTED"] = $setSelectedLabels($menu[$i]["ITEMS"]);
	}
	return 0;
};

$setSelectedLabels($arResult);
?>
