<?
/**
 * Created by PhpStorm.
 * User: riordorian
 * Date: 15.09.16
 * Time: 12:20
 */

namespace MTask\Integration;

/**
 * Класc, предназначенный для работы с api Bitrix24 портала
 *
 * Class Integration
 * @package Integration
 */
class Integration
{
	/**
	 * Протокол для работы с порталом
	 */
	const PROTOCOL = 'https://';
	
	const DOMAIN = 'https://alxtest.bitrix24.ru';

	/**
	 *  Настройки подключения к порталу
	 *
	 * @var array
	 */
	public $arSettings = array(
		"client_id"     => 'app.5671abdac267b5.73772903',
		"client_secret" => '5f7d38ac17ef7a607f228026d2f60309',
		"title"         => 'Integration',
		"redirect_uri"  => 'http://mtask.p-w-d.ru/app/index.php',
		"scope"         => array('calendar,crm,disk,department,entity,im,log,mailservice,sonet_group,task,tasks_extended,telephony,user')
	);


	/**
	 * Процедура авторизации на портале
	 *
	 * @param string $code - Временный код для авторизации
	 */
	public function auth($code = '')
	{
		$bNewTokens = false;
		$obAuthInfo = new \stdClass();

		/*
		 * Проверяем наличие активного токена в файле
		 * */
		$arTokens = explode(';', file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/app/token.csv'));
		$arAccessToken = explode(':', $arTokens[0]);
		$accessToken = $arAccessToken[0];
		$refreshToken = $arTokens[1];

		/*
		 * Если время жизни access токена еще не истекло, то используем его для обращений к порталу
		 * */
		if( !empty($accessToken) && time() < $arAccessToken[1] ){
			$this->arSettings['access_token'] = $accessToken;
			$this->arSettings['refresh_token'] = $refreshToken;
		}
		/*
		 * Если время жизни access токена истекло, запрашиваем два новых токена access и refresh
		 * */
		elseif( time() >= $arAccessToken[1] && !empty($refreshToken) ){
			$bNewTokens = true;
			$this->arSettings['refresh_token'] = $refreshToken;
			$obAuthInfo = json_decode($this->query('POST', 'https://alxtest.bitrix24.ru/oauth/token/?grant_type=refresh_token', $this->arSettings));
		}
		/*
		 * Если предан код, то запрашиваем токены
		 * */
		elseif( !empty($code) ) {
			$bNewTokens = true;
			$this->arSettings['code'] = $code;
			$obAuthInfo = json_decode($this->query('GET', 'https://alxtest.bitrix24.ru/oauth/token/?grant_type=authorization_code', $this->arSettings));
		}
		
		/*
		 * При получении новых токенов, записываем их в файл
		 * */
		if( $bNewTokens && !empty($obAuthInfo->access_token) && !empty($obAuthInfo->expires_in) && !empty($obAuthInfo->refresh_token) ){
			$this->arSettings['access_token'] = $obAuthInfo->access_token;
			$this->arSettings['refresh_token'] = $obAuthInfo->refresh_token;

			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/app/token.csv', $obAuthInfo->access_token . ':' . (time() + $obAuthInfo->expires_in) . ';' . $obAuthInfo->refresh_token);
		}
	}


	/**
	 * Формирование запроса к api портала и его выполнение
	 *
	 * @param      $method - GET или POST
	 * @param      $url - адрес, к которому направляем запрос
	 * @param null $arData - Параметры запроса
	 * @param bool $bJsonDecode - Декодировать ли ответ от портала
	 *
	 * @return mixed
	 */
	public function query($method, $url, $arData = null, $bJsonDecode = false) // построение запроса с REST-методом к порталу
	{
		$arCurlOptions = array(
			CURLOPT_RETURNTRANSFER => true
		);

		if( $method == "POST" ) {
			$arCurlOptions[CURLOPT_POST] = true;
			$arCurlOptions[CURLOPT_POSTFIELDS] = $this->bildQuery($arData);
		}
		elseif( !empty( $arData ) ) {
			$url .= strpos($url, "?") > 0 ? "&" : "?";
//			$url .= $this->bildQuery($arData);
			$url .= http_build_query($arData);
		}
		
		$curl = curl_init($url);
		curl_setopt_array($curl, $arCurlOptions);
		$result = curl_exec($curl);
		return ( $bJsonDecode ? json_decode($result, 1) : $result );
	}


	/**
	 * Формирование строки запроса по параметрам
	 *
	 * @param $arParams - массив параметров запроса
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	protected function bildQuery($arParams)
	{
		$result = '';
		if( empty($arParams) ){
		    throw new \Exception('Incorrect parameters');
			return false;
		}

		foreach($arParams as $fieldName => $fieldVal){
			$result .= ( (empty($result)) ? '' : '&' );

			if( is_array($fieldVal) && !empty($fieldVal) ){
				foreach($fieldVal as $key => $val){
					$result .= (( $key != 0 ) ? '&' : '') . $fieldName .'[' . $key . ']=' . $val;
				}
			}
			elseif( empty($fieldVal) ){
				$result .= $fieldName . '[]=';
			}
			else{
				$result .= $fieldName . '=' . $fieldVal;
			}
		}

		return $result;
	}


	/**
	 * Вызов метода, отправляющего запрос к порталу
	 *
	 * @param $domain - Домен портала
	 * @param $method - Метод, вызываемый на портале
	 * @param $params - Параметры запроса
	 *
	 * @return mixed
	 */
	public function call($domain, $method, $params)
	{
		return $this->query("GET", self::PROTOCOL . $domain . "/rest/" . $method, $params, true);
	}
}