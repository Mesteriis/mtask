<?
/**
 * Created by PhpStorm.
 * User: riordorian
 * Date: 19.11.16
 * Time: 14:02
 */

namespace MTask\Components;

use \MTask\Components\Component;
use \MTask\Integration\Database;

/**
 * Класс компонента отчетов по затратам времени на задачи
 *
 * Class Reports
 * @package MTask\Components
 */
class Reports extends Component
{
	/**
	 * Установка файла шаблона для компонента
	 */
	protected function setTemplatePage(){
		$this->page = dirname(__FILE__) . '/template.php';
	}


	/**
	 * Получение результата компонента
	 *
	 * @throws \Exception
	 */
	public function getResult()
	{
		$arResult = array();
		$obDatabase = new Database();
		$arResult = $obDatabase->getList('sonet_group', array(
			'select' => array(
				'SONET_GROUP.ID' => 'GROUP_ID',
				'SONET_GROUP.NAME' => 'GROUP_NAME',

				'TASK.ID' => 'TASK_ID',
				'TASK.TITLE' => 'TASK_NAME',

				'TIME.ID'  => 'TIME_ID',
				'TIME.SECONDS' => 'TIME_SECONDS',
				'TIME.CREATED_DATE' => 'CREATED_DATE',

				'USER.NAME'  => 'USER_NAME',
				'USER.LAST_NAME'  => 'USER_LAST_NAME',
				'USER.SECOND_NAME' => 'USER_SECOND_NAME',
			),
			'runtime' => array(
				'TASK' => array(
					'entity' => 'task',
					'reference' => array(
						'sonet_group.ID' => 'TASK.GROUP_ID'
					),
					'join_type' => 'left'
				),
				'TIME' => array(
					'entity' => 'time',
					'reference' => array(
						'TASK.ID' => 'TIME.TASK_ID'
					),
					'join_type' => 'left'
				),
				'USER' => array(
					'entity' => 'user',
					'reference' => array(
						'TIME.USER_ID' => 'USER.ID'
					),
					'join_type' => 'left'
				),
			),
		));
		
		$this->arResult = $arResult;
		$this->getComponentTemplate();
	}
}