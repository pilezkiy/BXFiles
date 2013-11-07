<?
/*
* Функция получения значения пользовательского свойства раздела с наследованием.
*
*/
$getSectionUF = function($IBLOCK_ID,$SECTION_ID,$FIELD_NAME)
{
	$IBLOCK_ID = intval($IBLOCK_ID);
	$SECTION_ID = intval($SECTION_ID);
	if(
		CModule::IncludeModule("iblock") && 
		$SECTION_ID > 0 && 
		$IBLOCK_ID > 0 && 
		!empty($FIELD_NAME) && 
		$section = CIBlockSection::GetList(false,array("ID"=>$SECTION_ID,"IBLOCK_ID"=>$IBLOCK_ID),false,array("ID","IBLOCK_ID",$FIELD_NAME,"LEFT_MARGIN","RIGHT_MARGIN"))->Fetch()
	)
	{
		if(!empty($section[$FIELD_NAME]))
		{
		 	return $section[$FIELD_NAME];
		}
		else
		{
	 		$chainFilter = array(
				"IBLOCK_ID"=>$IBLOCK_ID,
				"<LEFT_BORDER"=>$section["LEFT_MARGIN"],
				">RIGHT_BORDER"=>$section["RIGHT_MARGIN"],
			);
	 		$rsSec = CIBlockSection::GetList(array("LEFT_MARGIN"=>"DESC"),$chainFilter,false,array("ID","DEPTH_LEVEL","NAME","IBLOCK_ID",$FIELD_NAME));
			while($sec = $rsSec->fetch())
			{
				$fieldValue = $sec[$FIELD_NAME];
				if(!empty($fieldValue))
				{
					return $fieldValue;
				}
			}
		}
	}
	return false;
};
?>