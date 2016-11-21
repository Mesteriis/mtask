<?
/**
 * Created by PhpStorm.
 * User: riordorian
 * Date: 19.11.16
 * Time: 14:05
 */

namespace MTask\Components;

/**
 * Класс по работе с компонентами
 *
 * Class Component
 * @package MTask\Components
 */
class Component
{
	/**
	 * @var - Файл шаблона выполняемого компонента
	 */
	public $page;

	/**
	 * @var - Результирующий массив
	 */
	public $arResult;


	/**
	 * Запуск выполнения компонента
	 */
	public function executeComponent()
	{
		$this->setTemplatePage();
		$this->getResult();
	}


	/**
	 * Подключение шаблона компонента
	 */
	public function getComponentTemplate()
	{
		if( file_exists($this->page) ){
			$arResult = &$this->arResult;
			require_once($this->page);
		}
	}
}