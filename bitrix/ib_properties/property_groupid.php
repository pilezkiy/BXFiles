<?
IncludeModuleLangFile(__FILE__);
/**
 * Класс для создания пользовательского свойства
 * элемента инфоблока с привязкой к группе пользователей
 * @author Sergey Ponomarev <ponomarevsa@yandex.ru>
 */
class ponamaru_CIBlockPropertyGroupID {

    function GetUserTypeDescription() {
        return array(
            "PROPERTY_TYPE" => "E",
            "USER_TYPE" => "ponamaru_IBLinkByGroupID",
            "DESCRIPTION" => GetMessage("PONAMARU_PROP_NAME"),
            "GetAdminListViewHTML" => array("ponamaru_CIBlockPropertyGroupID", "GetAdminListViewHTML"),
            "GetPropertyFieldHtml" => array("ponamaru_CIBlockPropertyGroupID", "GetPropertyFieldHtml"),
            "ConvertToDB" => array("ponamaru_CIBlockPropertyGroupID", "ConvertToDB"),
            "ConvertFromDB" => array("ponamaru_CIBlockPropertyGroupID", "ConvertFromDB")
        );
    }

    function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName) {
        if (intval($value["VALUE"]) == 0) {
            return "0";
        }
        $rsGroup = CGroup::GetByID(intval($value["VALUE"]));
        $arGroup = $rsGroup->Fetch();
        return '<a href="/bitrix/admin/group_edit.php?ID=' . $arGroup["ID"] . '">' . $arGroup["NAME"] . "(" . $arGroup["ID"] . ")</a>";
    }

    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {
        $mode = explode("_", $strHTMLControlName["FORM_NAME"]);
        if ($mode[1] != "element") {
            return;
        }
        ?>
        <select id="<?= htmlspecialchars($strHTMLControlName["VALUE"]) ?>" name="<?= htmlspecialchars($strHTMLControlName["VALUE"]) ?>" >
            ?> <option value='["VALUE",[0]]'>не установлено(0)</option>   <?
        $res = CGroup::GetList();
        $groups = array();
        while ($group = $res->GetNext()) {
            ?>
                <option <?
            if ($group["ID"] == $value["VALUE"]) {
                echo " selected";
            }
            ?> value='["VALUE",[<?= $group["ID"] ?>]]'><?= $group["NAME"] ?>(<?= $group["ID"] ?>)</option>
                    <?
                }
                ?>
        </select>
        <?
    }

    function ConvertToDB($arProperty, $value) {
        $return = array();
        $value = explode(",", $value["VALUE"]);
        $value = $value[1];
        $value = str_replace("[", "", $value);
        $value = str_replace("]", "", $value);
        if (intVal($value) > 0) {
            $return['VALUE'] = intVal($value);
        } else {
            $return['VALUE'] = '';
        }
        return $return;
    }

    function ConvertFromDB($arProperty, $value) {
        $return = array();
        if (intVal($value['VALUE']) > 0)
            $return['VALUE'] = intVal($value['VALUE']);
        else
            $return['VALUE'] = '';

        return $return;
    }

}
?>
