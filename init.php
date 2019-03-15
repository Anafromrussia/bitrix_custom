<?
/*
* Кастомизация для коробки битрикс24
* Подключение собственных стилей и скриптов
* Вариант 1 - с кешированием стилей
*/
AddEventHandler("main", "OnProlog", "MyOnProlog", 50);

function MyOnProlog()
{
		require_once(__DIR__ . '/custom.php');
		require_once(__DIR__ . '/js.php');

		CJSCore::RegisterExt('crm_custom',
			array(
				'css' => '/local/css/crm_custom.css',
				'js' => '/local/js/custom.js',
			)
		);
		CJSCore ::Init('crm_custom');
	}
}

/*
* Кастомизация для коробки битрикс24
* Подключение собственных стилей и скриптов
* Вариант 2 - без кеширования
*/

AddEventHandler("main", "OnProlog", "MyOnProlog", 50);

function MyOnProlog()
{
  //например нам нужно подключать наши стили и скрипты только для пользователей не являющимися админами
	global $USER;
	if (!$USER -> IsAdmin())
	{
		global $APPLICATION;
		$APPLICATION -> AddHeadString('<link href="/local/css/crm_custom.css";  type="text/css" rel="stylesheet" />', true);
	}
}

/*
* Кастомизация для коробки битрикс24
* Запрет на редактирование завершенных сделок пользователя не являющимися админами
* О событии "OnBeforeCrmDealUpdate" в документации битрикс ни слова!
* Поэтому пару слов от меня. Данное событие срабатывает после того, 
* как в пользователь попытался внести изменения в сделку, но до того, как эти изменения были применены.
*/
AddEventHandler("crm", "OnBeforeCrmDealUpdate", "OnBeforeCrmDealUpdateMy");

function OnBeforeCrmDealUpdateMy(&$arFields)
{
  //проверка на админа
	global $USER;
	if (!$USER -> IsAdmin())
	{
    //получение ID изменяемой сделки
    $idCrm = $arFields["ID"];
    //получение флага сделки (завершена или нет)
		$dbDocumentList = CCrmDeal::GetList(array(), array('ID' => $idCrm), array('CLOSED'));
		$arResult = $dbDocumentList->Fetch();
		$flag = $arResult['CLOSED'];
		//если завершена, то редактировать нельзя
		if ($flag == 'Y') {
      //текст всплывающего сообщения для пользователя
			$arFields['RESULT_MESSAGE'] = "Завершенную сделку нельзя изменить! Обратитесь к администратору портала.";
      //вызов отмены сохранения сделки
			global $APPLICATION;
			$APPLICATION->throwException($arFields['RESULT_MESSAGE']);
	    return false;
		}
	}
} 

/*
* Кастомизация для коробки битрикс24
* Запись чего либо в лог
*/
AddEventHandler("main", "OnProlog", "MyOnProlog", 50);

function MyOnProlog()
{
  //файл для логов
  define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/custom_crm_log.txt");
  //запись, где $flag - то, что вы хотите записать в лог, FarFields - ваша метка (можно писать все что угодно)
  AddMessage2Log("$flag", "FarFields");
}

