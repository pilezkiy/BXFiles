<?
IncludeModuleLangFile(__FILE__);
global $APPLICATION;
$APPLICATION->AddHeadScript("/js/jquery-1.6.1.min.js");

class ITCUserTypeIBlockProperty// extends CUserTypeEnum
{
	public static $firstVal = true;
	public static $valCnt = 0;
	
	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID"	=> "ut_iblock_property",
			"CLASS_NAME"	=> "ITCUserTypeIBlockProperty",
			"DESCRIPTION"	=> "Свойство инфоблока",
			"BASE_TYPE"		=> "string",
			"ConvertToDB"	=>	array("ITCUserTypeIBlockProperty","ConvertToDB"),
		);
	}
	
	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "text";
			case "oracle":
				return "varchar2(2000 char)";
			case "mssql":
				return "varchar(2000)";
		}
	}

	function PrepareSettings($arUserField)
	{
		$height = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		$disp = $arUserField["SETTINGS"]["DISPLAY"];
		if($disp!="CHECKBOX" && $disp!="LIST")$disp = "LIST";
		$iblock_type = $arUserField["SETTINGS"]["IBLOCK_TYPE_ID"];
		$iblock_id = intval($arUserField["SETTINGS"]["IBLOCK_ID"]);
		if($iblock_id <= 0)$iblock_id = "";
		$section_id = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		if($section_id <= 0)$section_id = "";
		$active_filter = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y"? "Y": "N";

		return array(
			"DISPLAY" => $disp,
			"LIST_HEIGHT" => ($height < 2? 5: $height),
			"IBLOCK_TYPE" => $iblock_type,
			"IBLOCK_ID" => $iblock_id,
			"DEFAULT_VALUE" => $section_id,
			"ACTIVE_FILTER" => $active_filter,
		);
	}
	
	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		ob_start();
		//PR($arUserField);
		$IBLOCK_ID = intval($arUserField["SETTINGS"]["IBLOCK_ID"]);
		if($IBLOCK_ID>0)
		{
			$VALUE = false;
			if(strlen($arHtmlControl["VALUE"])>0)$VALUE = unserialize(htmlspecialchars_decode($arHtmlControl["VALUE"]));
			
			CModule::IncludeModule("iblock");
			$rs = CIBlock::GetProperties($IBLOCK_ID, Array("SORT"=>"ASC"), Array());
			if($rs->SelectedRowsCount()>0)
			{
				?>
				<div style='border:1px solid #ccc; background:#fefefe; padding:20px;margin-bottom:30px;'>
					<div><label>Название группы свойств:</label>&nbsp;<input name="<?=$arHtmlControl["NAME"]?>[GROUP_NAME]" value="<?=$VALUE["GROUP_NAME"]?>" style='width:300px;'/></div>
					<br/>
					<select style='width:650px; font-family:Consolas;font-size:9pt;' name="<?=$arHtmlControl["NAME"]?>[]" <?if($arUserField["MULTIPLE"]=="Y"){?>multiple size="<?=$arUserField["MULTIPLE_CNT"]?>"<?}?> >
						<option value="">(не выбрано)</option>
						<?
						while($prop = $rs->Fetch())
						{
							$selected = (in_array($prop["ID"],$VALUE)?"selected='selected'":"");
							$prop["NAME"] = $prop["NAME"].str_repeat(".",65-mb_strlen($prop["NAME"]));
							?><option value="<?=$prop["ID"]?>" <?=$selected?>><?=$prop["NAME"]?>[<?=$prop["ID"]?>] <?=$prop["CODE"]?></option><?
						}
						?>
					</select>
					<br/>
					<div><input type='checkbox' value="1" name="<?=$arHtmlControl["NAME"]?>[DELETE_GROUP]"/><label>удалить группу свойств</label></div>
				</div>
				<?
			}
			else
			{
				?><b>У инфоблока не заданы свойства.</b><?
			}
		}
		else {?><b>В настройках пользовательского поля не задан ID инфоблока.</b><?}
		
		$return = ob_get_contents();
		ob_end_clean();
		ITCUserTypeIBlockProperty::$firstVal = false;
		ITCUserTypeIBlockProperty::$valCnt++;
		return  $return;
	}
	
	function OnBeforeSave($arProperty, $value)
	{
		if(intval($value["DELETE_GROUP"]) == 1)return FALSE;
		
		$valCnt = 0;
		foreach($value as $i=>$v)
		{
			if($i=="GROUP_NAME")continue;
			if(intval($v)==0)unset($value[$i]);
			else $valCnt++;
		}
		
		if($valCnt>0)
		{
			$value = serialize($value);
			return $value;
		}
		
		return FALSE;
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';

		if($bVarsFromForm)
			$iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
		elseif(is_array($arUserField))
			$iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
		else
			$iblock_id = "";
		
		if($arUserField["SETTINGS"]["IBLOCK_TYPE"])$result.="<tr><td>Сохраненный тип инфоблока:</td><td>".$arUserField["SETTINGS"]["IBLOCK_TYPE"]."</td></tr>";
		
		if(CModule::IncludeModule('iblock'))
		{
			$result .= '
			<tr valign="top">
				<td>'.GetMessage("USER_TYPE_IBSEC_DISPLAY").':</td>
				<td>
					'.GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"].'[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"].'[IBLOCK_ID]').'
				</td>
			</tr>
			';
		}
		else
		{
			$result .= '
			<tr valign="top">
				<td>'.GetMessage("USER_TYPE_IBSEC_DISPLAY").':</td>
				<td>
					<input type="text" size="6" name="'.$arHtmlControl["NAME"].'[IBLOCK_ID]" value="'.htmlspecialchars($value).'">
				</td>
			</tr>
			';
		}
		return $result;
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		return $aMsg;
	}

	
}
//if(CModule::IncludeModule('iblock'))
//{
	
	AddEventHandler("main", "OnUserTypeBuildList", array("ITCUserTypeIBlockProperty", "GetUserTypeDescription"));
//}
?>