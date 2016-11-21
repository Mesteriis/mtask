<?
/**
 * Created by PhpStorm.
 * User: riordorian
 * Date: 16.11.16
 * Time: 10:41
 */

namespace MTask\Integration;


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


	/**
	 * Возвращает список элементов БД
	 *
	 * @param $entity - Таблица, из которой производим выборку
	 * @param $arParams - Параметры запроса
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getList($entity, $arParams)
	{
		if( empty($entity) ){
			throw new \Exception('Incorrect parameters');
		}
		if( is_null($this->db) ){
			throw new \Exception('Db connect error');
		}

		$arResult = $arFieldsParams = array();

		/*
		 * Формируем селект
		 * */
		$query = '';
		$arSelect = array();
		if( array_key_exists('select', $arParams) && !empty($arParams['select']) ){
			foreach($arParams['select'] as $k => $v){
				$arSelect[] = strtolower($v);
				if( is_numeric($k) ){
					$query .= ( empty($query) ? '' : ',' ) . $v;
				}
				elseif( is_string($k) ){
					$query .= ( empty($query) ? '' : ',' ) . $k . ' as ' . $v;
				}
			}
		}
		else{
			$query .= '*' . ' ';
		}

		$query = 'SELECT ' . $query;

		/*
		 * Формируем фильтр
		 * */
		/*if( array_key_exists('filter', $arParams) && !empty($arParams) ){
			$filter = static::getFilter($arParams['filter']);
		}*/

		$from = ' FROM ' . static::$prefix . $entity . ' ' . strtoupper($entity) . ' ';
		/*
		 * Формируем JOIN - ы
		 * */
		if( array_key_exists('runtime', $arParams) && !empty($arParams['runtime']) ){

			foreach( $arParams['runtime'] as $alias => $arReference ){
				$refKey = key($arReference['reference']);
				$refVal = reset($arReference['reference']);
				$reference = strtoupper($refKey) . '=' . strtoupper($refVal);

				$from .= strtoupper($arReference['join_type']) . ' JOIN ' . static::$prefix . $arReference['entity'] . ' ' . $alias . ' ON ' . $reference . ' ';
			}
		}
		$query .= $from;
		
		$query = $this->db->prepare($query);
		$query->execute();

		/*
		 * Хак для разбора строк запроса с неограниченным числом параметров селекта
		 * TODO: Записать хак в evernote
		 * */
		$meta = $query->result_metadata();
		while ($field = $meta->fetch_field()) {
			$var = $field->name;
			$$var = null;
			$arFieldsParams[$field->name] = &$$var;
		}

		call_user_func_array(array($query, 'bind_result'), $arFieldsParams);

		while($query->fetch()) {
			if( empty($arFieldsParams['TIME_SECONDS']) ){
			    continue;
			}

			$arRow = array();
			foreach ($arFieldsParams as $fieldName => $fieldVal) {
				$arRow[$fieldName] = $fieldVal;
			}
			
			$arResult[] = $arRow;
		}
		$query->close();
		
		return $arResult;
	}


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