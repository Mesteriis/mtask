<?
namespace MTask;

/**
 * Класс подключения классов модуля
 *
 * Class Autoloader
 * @package MTask
 */
class Autoloader
{
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
}

$obAutoloader = new Autoloader();
$obAutoloader->autoload();