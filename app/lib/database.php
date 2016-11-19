<?
/**
 * Created by PhpStorm.
 * User: riordorian
 * Date: 16.11.16
 * Time: 10:41
 */

namespace Integration;


class Database
{
	private $dbhost = '88.198.5.36';
	private $dbuser = 'usermtask';
	private $dbpassword = '2R6Ndt57rhei3aB';
	private $dbname = 'dbmtask';
	public $db = null;
	public static $prefix = 'b24_';


	/**
	 * Подключение к бд.
	 * Создаем новое или возвращаем активное
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __construct()
	{
		if( is_null($this->db) ){
			$db = new \mysqli($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);

			if( $db->connect_error ){
				throw new \Exception('DB connection error');
				return false;
			}

			$this->db = $db;
			$this->db->query('SET NAMES UTF8');
		}

		return $this->db;
	}


	/**
	 * Формирование селекта для запроса
	 *
	 * @param $arSelect - массив с полями для выборки
	 *
	 * @return mixed
	 */
	protected static function getSelect($arSelect)
	{
		return implode(',', $arSelect);
	}


	/**
	 * Формирование фильтра для запроса
	 *
	 * @param $arFilter - массив с полями для фильтрации
	 *
	 * @return string
	 */
	protected static function getFilter($arFilter)
	{
		$filter = '';
		foreach( $arFilter as $filterKey => $filterVal ){
			$filter .= $filterKey . '=' . $filterVal . ' ';
		}

		return $filter;
	}

	/*public function getList($entity, $arParams)
	{
		if( array_key_exists('select', $arParams) && !empty($arParams['select']) ){
			$select = static::getSelect($arParams['select']);
		}
		if( array_key_exists('filter', $arParams) && !empty($arParams) ){
			$filter = static::getFilter($arParams['filter']);
		}
	}*/


	/**
	 * Добавление записи в таблицу
	 *
	 * @param $entity - название таблицы
	 * @param $arFields - добавляемые данные
	 *
	 * @return bool|\mysqli_result|null
	 * @throws \Exception
	 */
	public function add($entity, $arElement)
	{
		$arResult = $obQuery = array();

		if( empty($entity) || empty($arElement) ){
			throw new \Exception('Incorrect parameters');
		}
		if( is_null($this->db) ){
			throw new \Exception('Db connect error');
		}

		$fields = '`' . implode('`,`', array_keys($arElement)) . '`';
		$values = '\'' . implode('\',\'', array_values($arElement)) . '\'';
		$query = 'INSERT INTO `' . static::$prefix . $entity . '` (' . $fields . ') VALUES(' . $values . ');';
		$query = $this->db->prepare($query);
		if( $obQuery = $query->execute() ){
			$arResult[] = $obQuery;
		}
		else{
			$arResult['ERRORS'] = $arElement;
		}

		return $arResult;
	}
}