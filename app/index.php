<?
require_once( $_SERVER['DOCUMENT_ROOT'] . '/header.php' );

use \MTask\Integration\Database;
use \MTask\Integration\Integration;


$obApp = new Integration();
$obApp->auth($_REQUEST['code']);

if( empty($_REQUEST['code']) && empty($obApp->arSettings['access_token']) && empty($obApp->arSettings['refresh_token']) ){
	?>Перейдите по ссылке для получения <a target="_blank" href="https://alxtest.bitrix24.ru/oauth/authorize/?client_id=<?=$obApp->arSettings['client_id']?>&response_type=code">кода</a><?
}
if( !empty($_REQUEST['code']) ){
	?><form action="">
		<input type="text" name="code" placeholder="Введите полученный код">
		<input type="submit">
	</form><?
}

try{
	$db = new Database();
}
catch(\Exception $e){
	?><pre><?print_r($e->getMessage())?></pre><?
}

if( !empty($obApp->arSettings['access_token']) ){
	$arUsers = array();
	$arResult = array();

	/*
	 *  Собираем информацию о группах
	 * */
	$arGroups = array();
	$obGroups = $obTasks = $obApp->call('alxtest.bitrix24.ru', 'sonet_group.get',
		array(
			'auth' => $obApp->arSettings['access_token'],
		)
	);
	
	$arAvailableSFields = array('ID', 'SITE_ID', 'NAME', 'ACTIVE');
	$arRecordableRows = array();
	foreach($obGroups['result'] as $arGroup){
		/*
		 * Записываем в базу полученныую информацию по группам
		 * */
		if( !is_null($db) ){
			try{
				$arRow = array();
				foreach($arAvailableSFields as $fieldName){
					$arRow[$fieldName] = $arGroup[$fieldName];
				}
				$db->add('sonet_group', $arRow);
			}
			catch(Exception $e){
				?><pre><?print_r($e->getMessage())?></pre><?
			}
		}
		$arGroups[$arGroup['ID']] = $arGroup;
	}
	
	/*
	 * Собираем информацию о задачах
	 * */

	//	TODO: Не работает ограничение выборки. Нужно разобраться
	$arTasks = $arRecordableRows = array();
	$obTasks = $obApp->call('alxtest.bitrix24.ru', 'task.item.list',
		array(
			'ORDER' => array('TITLE' => 'asc'),
			'FILTER' => array(0 => ''),
			'PARAMS' => array(0 => ''),
			'SELECT' => array('ID', 'TITLE', 'GROUP_ID', 'RESPONSIBLE_ID', 'DURATION_FACT'),
			'auth' => $obApp->arSettings['access_token']
		)
	);

	// Набираем информацию о затраченном времени по каждой задаче, по которой было списание
	$arAvailableTFields = array('ID', 'TITLE', 'GROUP_ID', 'RESPONSIBLE_ID', 'DURATION_FACT');
	$arAvailableTimeFields = array('ID', 'TASK_ID', 'SECONDS');
	foreach( $obTasks['result'] as $arTask ){
		if( !empty($arTask['DURATION_FACT']) ){
			if( !is_null($db) ){
				try{
					$arRow = array();
					foreach($arAvailableTFields as $fieldName){
						$arRow[$fieldName] = $arTask[$fieldName];
					}
					$db->add('task', $arRow);
				}
				catch(Exception $e){
					?><pre><?print_r($e->getMessage())?></pre><?
				}
			}

			$arTasks[$arTask['ID']] = $arTask;

			$obDurationTime = $obApp->call('alxtest.bitrix24.ru', 'task.elapseditem.getlist',
				array(
					'auth' => $obApp->arSettings['access_token'],
					'TASK_ID' => $arTask['ID']
				)
			);

			foreach($obDurationTime['result'] as $arTime){
				if( !is_null($db) ){
					try{
						$arRow = array();
						foreach($arAvailableTimeFields as $fieldName){
							$arRow[$fieldName] = $arTime[$fieldName];
						}
						$db->add('time', $arRow);
					}
					catch(Exception $e){
						?><pre><?print_r($e->getMessage())?></pre><?
					}
				}

				$uid = $arTime['USER_ID'];
				$arResult['USERS_INFO'][$uid] = array();

				// Формируем массив проектов пользователя
				$projectId = $arTask['GROUP_ID'];
				$projectName = $arGroups[$projectId]['NAME'];
				$arResult['USERS'][$uid]['PROJECTS'][$projectId]['NAME'] = $projectName;

				// Формируем массив задач пользователя
				$taskId = $arTime['TASK_ID'];
				$taskName = $arTask['TITLE'];
				$arResult['USERS'][$uid]['PROJECTS'][$projectId]['TASKS'][$taskId]['NAME'] = $taskName;

				// Формируем список потраченного времени
				$arTime['DURATION_FACT'] = floatval($arTime['MINUTES'] / 60);
				$arResult['USERS'][$uid]['PROJECTS'][$projectId]['TASKS'][$taskId]['TIMES'][$arTime['ID']] = $arTime;
			}
		}
	}
}
if( !empty($arResult['USERS_INFO']) ){
	$obUsersInfo = $obApp->call('alxtest.bitrix24.ru', 'user.get.json',
		array(
			'auth' => $obApp->arSettings['access_token'],
			'ID' => array_keys($arResult['USERS_INFO'])
		)
	);

	$arAvailableUFields = array('ID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME');
	foreach($obUsersInfo['result'] as $arUser){
		if( !is_null($db) ){
			try{
				$arRow = array();
				foreach($arAvailableUFields as $fieldName){
					$arRow[$fieldName] = $arUser[$fieldName];
				}
				$db->add('user', $arRow);
			}
			catch(Exception $e){
				?><pre><?print_r($e->getMessage())?></pre><?
			}
		}
		$arResult['USERS_INFO'][$arUser['ID']] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
	}
}

// Таблица пользователей
if( !empty($arResult['USERS_INFO']) ){
	?><table class="widget datatable table table-striped table-bordered table-hover table-responsive">
		<thead>
		<tr>
			<td>ФИО</td>
			<td>ID</td>
		</tr>
		</thead><?
		foreach($arResult['USERS_INFO'] as $uid => $userName){
			?><tr>
				<td><?=$userName?></td>
				<td><?=$uid?></td>
			</tr><?
		}
	?></table><?
}

// Таблица затрат по задачам
if( !empty($arResult['USERS']) ){
    ?><br><br><table class="widget datatable table table-striped table-bordered table-hover table-responsive">
		<thead>
			<tr>
				<th>Задача</th>
				<th>Проект</th>
				<th>Исполнитель</th>
				<th>Затрачено времени</th>
				<th>Дата</th>
			</tr>
		</thead>
		<tbody><?
			foreach($arResult['USERS'] as $uid => $arUser){
				foreach($arUser['PROJECTS'] as $projectId => $arProject){
					foreach($arProject['TASKS'] as $taskId => $arTask){
						foreach($arTask['TIMES'] as $arTime){
							?><tr>
								<td>
									<a href="<?=Integration::DOMAIN?>/workgroups/group/<?=$projectId?>/tasks/task/view/<?=$taskId?>/" target="_blank">
									<?=$arTask['NAME']?> (<?=$taskId?>)
									</a>
								</td>
								<td><?=$arProject['NAME']?></td>
								<td><?=$arResult['USERS_INFO'][$uid]?></td>
								<td><?=$arTime['DURATION_FACT']?></td>
								<td><?=date('d.m.Y H:i:s', strtotime($arTime['CREATED_DATE']))?></td>
							</tr><?
						}
					}
				}
			}
		?></tbody>
	</table><?
}
?>

<?require_once( $_SERVER['DOCUMENT_ROOT'] . '/footer.php' );?>




