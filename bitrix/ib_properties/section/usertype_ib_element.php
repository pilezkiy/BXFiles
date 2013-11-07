<?
global $APPLICATION;
CUtil::InitJSCore(array('jquery'));

class CYetiUserTypeIBlockElement// extends CUserTypeEnum
{
	public static $firstVal = true;
	public static $valCnt = 0;
	
	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID"	=> "yeti_iblock_el",
			"CLASS_NAME"	=> "CYetiUserTypeIBlockElement",
			"DESCRIPTION"	=> "Привязка к элементам инфоблока (с выбором инфоблока)",
			"BASE_TYPE"		=> "int",
			"ConvertToDB"	=>	array("CYetiUserTypeIBlockElement","ConvertToDB"),
		);
	}
	
	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql": return "int(18)";
			case "oracle": return "number(18)";
			case "mssql": return "int";
		}
	}

	function PrepareSettings($arUserField)
	{
		$count = intval($arUserField["SETTINGS"]["COUNT"]);
		if($count == 0) $count = 3;
		$iblock_type = $arUserField["SETTINGS"]["IBLOCK_TYPE_ID"];
		$iblock_id = intval($arUserField["SETTINGS"]["IBLOCK_ID"]);
		if($iblock_id <= 0)$iblock_id = "";
		$section_id = intval($arUserField["SETTINGS"]["SECTION_ID"]);
		if($section_id <= 0)$section_id = "";
		
		return array(
			"COUNT" => $count,
			"IBLOCK_TYPE" => $iblock_type,
			"IBLOCK_ID" => $iblock_id,
			"SECTION_ID" => $section_id,
		);
	}
	
	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		ob_start();
		global $APPLICATION;
		$ar_res = false;
		if(strlen($arHtmlControl["VALUE"]))
		{
			$ar_res = CIBlockElement::GetList(array(),array("=ID"=>$arHtmlControl["VALUE"]),false,false,array("ID", "IBLOCK_ID", "NAME"))->fetch();
		}
		
		if(!$ar_res) $ar_res = array("NAME" => "");
		
		$n = $arHtmlControl["NAME"];
		/*$sTableID = "tbl_iblock_el_search".md5($n);
		$oSort = new CAdminSorting($sTableID, "NAME", "asc");
		$lAdmin = new CAdminList($sTableID, $oSort);
		InitFilterEx(array("filter_section","filter_subsections"), $sTableID, "set");
		*/
		
		
		?>
		<div class="yeti-select-element-wr">
			<input name="<?=htmlspecialcharsbx($arHtmlControl["NAME"])?>" 
				id="<?=htmlspecialcharsbx($arHtmlControl["NAME"])?>" 
				value="<?=htmlspecialcharsex($arHtmlControl["VALUE"])?>"
				size="5" type="text">
			<?
			$lookupUrl = "/bitrix/admin/iblock_element_search.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".$arUserField["SETTINGS"]["IBLOCK_ID"]."&filter_section=".$arUserField["SETTINGS"]["SECTION_ID"]."&filter_subsections=Y&n=".$n.'&a=b&set_filter=Y';
			$lookupUrl = CUtil::JSEscape($lookupUrl);
			?>
			<input type="button" value="..." onClick="jsUtils.OpenWindow('<?=$lookupUrl?>', 600, 500);">&nbsp;
			<span id="sp_<?=htmlspecialcharsbx($arHtmlControl["NAME"])?>" ><?=$ar_res['NAME']?></span>
		</div>
		<?
		$return = ob_get_clean();
		return $return;
	}
	
	function OnBeforeSave($arProperty, $value)
	{
		if(intval($value) > 0)
		{
			return intval($value);
		}
		return FALSE;
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		ob_start();

		if($bVarsFromForm)
		{
			$iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
			$section_id = $GLOBALS[$arHtmlControl["NAME"]]["SECTION_ID"];
		}
		elseif(is_array($arUserField))
		{
			$iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
			$section_id = $arUserField["SETTINGS"]["SECTION_ID"];
		}
		else
		{
			$iblock_id = "";
			$section_id = "";
		}
		
		if($arUserField["SETTINGS"]["IBLOCK_TYPE"])$result.="<tr><td>Сохраненный тип инфоблока:</td><td>".$arUserField["SETTINGS"]["IBLOCK_TYPE"]."</td></tr>";
		
		if(CModule::IncludeModule('iblock'))
		{
			?>
			<tr valign="top">
				<td>Iblock:</td>
				<td>
					<?=GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"].'[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"].'[IBLOCK_ID]')?>
				</td>
			</tr>
			<tr valign="top">
				<td>Section ID:</td>
				<td>
					<?
					if(intval($iblock_id) > 0)
					{
						$rs = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$iblock_id));
						?>
						<select name="<?=$arHtmlControl["NAME"]?>[SECTION_ID]">
							<option value="">-- Выберите раздел --</option>
							<?
							while($s = $rs->Fetch())
							{
								$sel = ($section_id==$s["ID"]?"selected='selected'":"");
								?>
								<option <?=$sel?> value="<?=$s["ID"]?>"><?=str_repeat("&nbsp;&nbsp;.&nbsp;&nbsp;", $s["DEPTH_LEVEL"]-1)?><?=$s["NAME"]?></option>
								<?
							}
							?>
						</select>
						<?
					}
					?>
				</td>
			</tr>
			<?
		}
		else
		{
			?>
			<tr valign="top">
				<td>Iblock:</td>
				<td>
					<input type="text" size="6" name="<?=$arHtmlControl["NAME"]?>[IBLOCK_ID]" value="<?=intval($iblock_id)?>">
				</td>
			</tr>
			<tr valign="top">
				<td>Section ID:</td>
				<td>
					<input type="text" size="6" name="<?=$arHtmlControl["NAME"]?>[SECTION_ID]" value="<?=intval($section_id)?>">
				</td>
			</tr>
			<?
		}
		
		$result = ob_get_clean();
		return $result;
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		return $aMsg;
	}
}

AddEventHandler("main", "OnUserTypeBuildList", array("CYetiUserTypeIBlockElement", "GetUserTypeDescription"));

?>