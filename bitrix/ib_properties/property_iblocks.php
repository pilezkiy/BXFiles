<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("CIBlockPropertyIBlock", "GetUserGroupsDescription"));

class CIBlockPropertyIBlock
{
	function GetUserGroupsDescription()
	{
		return array(
			"PROPERTY_TYPE"		=>"N",
			"USER_TYPE"		=>"Iblocks",
			"DESCRIPTION"		=>"Привязка к инфоблоку",
			"GetPublicViewHTML"	=>array("CIBlockPropertyIBlock","GetGroupsPublicViewHTML"),
			"GetAdminListViewHTML"	=>array("CIBlockPropertyIBlock","GetGroupsAdminListViewHTML"),
			"GetPropertyFieldHtml"	=>array("CIBlockPropertyIBlock","GetGroupsPropertyFieldHtml"),
			"ConvertToDB"		=>array("CIBlockPropertyIBlock","GroupsConvertToDB"),
			"ConvertFromDB"		=>array("CIBlockPropertyIBlock","GroupsConvertFromDB"),
		);
	}

	// Показ в публичной части (DISPLAY_VALUE)
	function GetGroupsPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		/*
		if(is_array($value["VALUE"]))
		{
			$rsGroups = CGroup::GetList(($by="c_sort"), ($order="asc"), Array());
			$str = '';
			while($arGroup = $rsGroups->Fetch())
			{
				if(in_array($arGroup["ID"], $value["VALUE"]))
					$str .= '['.$arGroup["ID"].'] '.$arGroup["NAME"].'<br>';
			}
			return $str;
		}
		*/
		return '';
	}

	// Показ списка в админке
	function GetGroupsAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		$iblock_id = intval($value["VALUE"]);
		if($iblock = CIBlock::GetByID($iblock_id)->fetch())
		{
			return $iblock["NAME"]." [".$iblock["ID"]."]";
		}
		return '';
	}

	// Редактирование в админке
	function GetGroupsPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$strHTMLControlName["VALUE"] = htmlspecialcharsEx($strHTMLControlName["VALUE"]);
		ob_start();
		?>
		<select name="<?=$strHTMLControlName["VALUE"]?>" >
			<option value="" <?=("" == $value['VALUE']) ? 'selected' : ''?>>Не выбрано</option>
			<?
			$rsIBlocks = CIBlock::GetList(array('iblock_type' => 'asc',"sort"=>"asc","name"=>"asc"), array());
			$type = '';
			while($arIBlock = $rsIBlocks->Fetch())
			{
				if($type != $arIBlock['IBLOCK_TYPE_ID'])
				{
					$arIBType = CIBlockType::GetByIDLang($arIBlock['IBLOCK_TYPE_ID'], LANG);
					?><optgroup label="<?=$arIBType['NAME']?>"><?
				}
				?><option value="<?=$arIBlock["ID"]?>" <?=($arIBlock["ID"] == $value['VALUE']) ? 'selected' : ''?>><?=$arIBlock["NAME"]?> [<?=$arIBlock["ID"]?>]</option><?
				$type = $arIBlock['IBLOCK_TYPE_ID'];
			}
		?></select><?
		$return = ob_get_contents();
		ob_end_clean();
		return  $return;
	}

	// Преобразование перед сохранением
	function GroupsConvertToDB($arProperty, $value)
	{
		if(is_array($value["VALUE"]))
		{
			$value["VALUE"] = $value["VALUE"];
		}
		return $value;
	}

	// Преобразование после чтения
	function GroupsConvertFromDB($arProperty, $value)
	{
		$value["VALUE"] = $value["VALUE"];
		return $value;
	}
}
?>
