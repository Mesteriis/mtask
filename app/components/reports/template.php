<table class="widget datatable table table-striped table-bordered table-hover table-responsive">
	<thead>
	<tr>
		<th>Задача</th>
		<th>Проект</th>
		<th>Исполнитель</th>
		<th>Затрачено времени</th>
		<th>ID списания</th>
		<th>Дата</th>
	</tr>
	</thead>
	<tbody><?
	foreach($arResult as $arRow){
			?><tr>
				<td>
					<a href="<?=MTask\MTask\DOMAIN?>/workgroups/group/<?=$arRow['GROUP_ID']?>/tasks/task/view/<?=$arRow['TASK_ID']?>/" target="_blank">
						<?=$arRow['TASK_NAME']?> (<?=$arRow['TASK_ID']?>)
					</a>
				</td>
				<td><?=$arRow['GROUP_NAME']?></td>
				<td><?=$arRow['USER_LAST_NAME'] . ' ' . $arRow['USER_NAME'] . ' ' . $arRow['USER_SECOND_NAME']?></td>
				<td><?=round($arRow['TIME_SECONDS'] / (60 * 60), 2)?></td>
				<td><?=$arRow['TIME_ID']?></td>
				<td><?=date('d.m.Y H:i:s', strtotime($arRow['CREATED_DATE']))?></td>
			</tr><?
		}
	?></tbody>
</table>