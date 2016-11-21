<?
namespace MTask;

/**
 * Класс модуля MTask
 *
 * Class Autoloader
 * @package MTask
 */
class MTask
{
	/**
	 * Определение констант класса
	 */
	public function defineConstatnts()
	{
		define(__CLASS__ . '\DOMAIN', 'https://alxtest.bitrix24.ru');
	}
	

	/**
	 * Автолоадер классов
	 *
	 * @param string $dir - Каталог, классы которого мы подключаем
	 */
	public function autoload($dir = '')
	{
		if( empty($dir) ){
		    $dir = $_SERVER['DOCUMENT_ROOT'] . '/app/lib';
		}
		$arFiles = scandir($dir);
		foreach($arFiles as $fileName){
			if( in_array($fileName, array('.', '..')) ){
				continue;
			}

			$filePath = $dir . '/' . $fileName;
			if( is_file($filePath) ){
				require_once($filePath);
			}
			elseif( is_dir($filePath) ){
				$this->autoload($filePath);
			}
		}
	}


	/**
	 * Метод подключает компонент на страницу
	 *
	 * @param       $componentName - Название компонента
	 * @param array $arParams - Параметры компонента
	 */
	public function IncludeComponent($componentName, $arParams = array())
	{
		require_once($_SERVER['DOCUMENT_ROOT'] . '/app/components/' . $componentName . '/class.php');
		$arComponentName = explode('.', $componentName);
		$componentClassName = '';
		foreach($arComponentName as $namePart){
			$componentClassName .= ( empty($componentClassName) ? '' : '_' ) . ucfirst($namePart);
		}

		$componentClassName = '\\' . __NAMESPACE__ . '\Components\\' . $componentClassName;
		$obComponent = new $componentClassName();
		$obComponent->executeComponent();
	}
}

global $MTask;
$MTask = new MTask();
$MTask->autoload();
$MTask->defineConstatnts();