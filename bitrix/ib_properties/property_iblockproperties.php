<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;
$APPLICATION->AddHeadScript("/js/jquery-1.6.1.min.js");
class CIBlockPropertyPropsList
{
    function GetUserTypeDescription()
    {
        return array(
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" => "iblock_properties",
                "DESCRIPTION" => 'Список свойств инфоблока',
                "GetPublicViewHTML" => array("CIBlockPropertyPropsList","GetPublicViewHTML"),
                "GetAdminListViewHTML" => array("CIBlockPropertyPropsList","GetAdminListViewHTML"),
                "GetPropertyFieldHtml" => array("CIBlockPropertyPropsList","GetPropertyFieldHtml"),
                "ConvertToDB" => array("CIBlockPropertyPropsList","ConvertToDB"),
                "ConvertFromDB" => array("CIBlockPropertyPropsList","ConvertFromDB"),
                );
    }

    function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return $value['VALUE'];
    }

    function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {    
        if (!CModule::IncludeModule('iblock')) {
            return $value['VALUE'];
        }

        //$arPs = CSalePropsList::GetByID($value['VALUE']);

        return '[' . $arPs['ID'] . '] ' . $arPs['NAME'];
    }

    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {      
      $IBLOCK_TYPES = array();
      $IBLOCK_IDS = array();
      $IBLOCK_PROPERTIES = array();

      
      $rsIblockTypes = CIBlockType::GetList();  
      while($arIblockType = $rsIblockTypes->Fetch()){
        $arIBType = CIBlockType::GetByIDLang($arIblockType["ID"], LANG);
        $IBLOCK_TYPES[$arIblockType["ID"]] = array('id' => $arIblockType["ID"], 'name' => htmlspecialcharsex($arIBType["NAME"]));
        
        // Получаем инфоблоки для указанного типа
        $rsIBlock = CIBlock::GetList(array('sort' => 'asc', 'name' => 'asc'), array('TYPE' => $arIblockType["ID"]));
        while($arIBlock = $rsIBlock->Fetch())
		{
          $IBLOCK_IDS[$arIblockType["ID"]][$arIBlock['ID']] = array('id' => $arIBlock['ID'], 'name' => $arIBlock['NAME']." [".$arIBlock['ID']."]");
          
          // Получаем свойства для каждого инфоблока
          $rsIBlockProperties = CIBlockProperty::GetList(array('name' => 'asc'), array('IBLOCK_ID' => $arIBlock['ID']));
          while($arIBlockProperty = $rsIBlockProperties->Fetch()){
            $IBLOCK_PROPERTIES[$arIBlock['ID']]["a".$arIBlockProperty['ID']] = array('id' => $arIBlockProperty['ID'], 'name' => $arIBlockProperty['NAME']); 
          }
        }
      }
?>

<script language="javascript">
  
  var iblock_types = <?=json_encode($IBLOCK_TYPES)?>;
  var iblock_ids = <?=json_encode($IBLOCK_IDS)?>;
  var iblock_properties = <?=json_encode($IBLOCK_PROPERTIES)?>;
  var seqSelects = {};
  
  var prop_value = <?=json_encode($value['VALUE'])?>;
  
  
  Array.prototype.in_array = function(p_val) {
    for(var i = 0, l = this.length; i < l; i++)  {
      if(this[i] == p_val) {
        return true;
      }
    }
    return false;
  }
  
  $(document).ready(function(){
  
    var seqSelects = {
        iblock_type_select: {
            control: '#iblock_select',
            data: iblock_ids
        },
        iblock_select: {
            control: '#iblock_properties_select',
            data: iblock_properties
        }
    };  
  
    // Если было сохраненное значение, то надо селекты подсветить и установить сохраненные значения

    if(typeof(prop_value) != 'undefined'){
      
      var select_id = 'iblock_type_select';
      var type_val = prop_value['IBLOCK_TYPE'];
      var iblock_val = prop_value['IBLOCK_ID'];
      var properties_val = {};
      properties_val = prop_value['IBLOCK_PROPERTIES'];
      
      var html = '<option>(Не выбрано)</option>';
    
      // Если ид инфоблока был сохранен
      if(iblock_val != ''){
        for (var i in seqSelects[select_id].data[type_val]) {
        
            selected = seqSelects[select_id].data[type_val][i].id == iblock_val ? 'selected' : '';
            html += '<option value="' + seqSelects[select_id].data[type_val][i].id + '" ' + selected + '>' + seqSelects[select_id].data[type_val][i].name + '</option>';
        }      
        
        $(seqSelects[select_id].control).html(html).removeAttr('disabled').trigger('change');
        $(seqSelects[select_id].control).parents('div:eq(0)').removeClass('disable');
       
        // Если свойства инфоблока были сохранены
        //if(properties_val.length > 0)
        {
          var html = '<option>(Не выбрано)</option>';
          var select_iblock_id = 'iblock_select';
          for (var i in seqSelects[select_iblock_id].data[iblock_val]) {
          
              selected = properties_val.in_array(seqSelects[select_iblock_id].data[iblock_val][i].id) ? 'selected' : '';
              html += '<option value="' + seqSelects[select_iblock_id].data[iblock_val][i].id + '" ' + selected + '>' + seqSelects[select_iblock_id].data[iblock_val][i].name + '</option>';
          }      
          
          $(seqSelects[select_iblock_id].control).html(html).removeAttr('disabled').trigger('change');
          $(seqSelects[select_iblock_id].control).parents('div:eq(0)').removeClass('disable');          
        }     
      }
    }   
    
  
    $('#iblock_type_select, #iblock_select').change(function () {
        var id = $(this).attr('id');
        var html = '<option>(Не выбрано)</option>';
        
        
        if (typeof(seqSelects[id].data[$(this).val()]) == 'undefined' || seqSelects[id].data[$(this).val()].length == 0) {
            $(seqSelects[id].control).html(html).attr('disabled', 'disabled').trigger('change');
            $(seqSelects[id].control).parents('div:eq(0)').addClass('disable');
            return;
        }

        for (var i in seqSelects[id].data[$(this).val()]) {
            html += '<option value="' + seqSelects[id].data[$(this).val()][i].id + '">' + seqSelects[id].data[$(this).val()][i].name + '</option>';
        }

        $(seqSelects[id].control).html(html).removeAttr('disabled').trigger('change');
        $(seqSelects[id].control).parents('div:eq(0)').removeClass('disable');
    });
  });
</script>
  <div style="border: 2px solid #CED6EC; padding: 5px; width: 310px; margin: 5px;">
<?
  
        // Получаем список типов инфоблоков
        if(count($IBLOCK_TYPES) > 0){?>        
            Выберите тип инфоблока<br />
            <select id="iblock_type_select" style="width: 300px;" name="<?=$strHTMLControlName["VALUE"]?>[IBLOCK_TYPE]">
              <option value="">(Не выбрано)</option>
          <?
            foreach($IBLOCK_TYPES as $iblock_type){
              $arIBType = CIBlockType::GetByIDLang($arIblockType["ID"], LANG);
              ?><option <?=($iblock_type['id'] == $value['VALUE']['IBLOCK_TYPE']) ? 'selected' : ''?> value="<?=$iblock_type['id']?>"><?=$iblock_type['name']?></option><?
            }
         ?>
          </select><br /><br />
          Выберите инфоблок<br />
          <select id="iblock_select" style="width: 300px;" name="<?=$strHTMLControlName["VALUE"]?>[IBLOCK_ID]" disabled>
            <option value="">(Не выбрано)</option>
          </select>
          <br /><br />
          Выберите свойство инфоблока<br />
          <select id="iblock_properties_select" style="width: 300px;" name="<?=$strHTMLControlName["VALUE"]?>[IBLOCK_PROPERTIES][]" disabled>
            <option value="">(Не выбрано)</option>
          </select>          
          <?
        }
?></div><?        
    }

    function ConvertToDB($arProperty, $value)
    {
      if(empty($value['VALUE']['IBLOCK_PROPERTIES'])) return;
      
      $value['VALUE'] = serialize($value['VALUE']);
      return $value;
    }

    function ConvertFromDB($arProperty, $value)
    {   
		//PR($value);
        $value['VALUE'] = unserialize($value['VALUE']);        
        return $value;
    }
}

AddEventHandler('iblock', 'OnIBlockPropertyBuildList', array('CIBlockPropertyPropsList', 'GetUserTypeDescription'));

?>
