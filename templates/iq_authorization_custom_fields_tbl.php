<?php
if(defined("ABSPATH")) {
	$AjaxRequest = false;
	if(!defined("iq_authorization_CORE_DIR")) { die(); }
} else {
	$AjaxRequest = true;
	if(!isset($_SERVER['HTTP_REFERER'])) { die(); }
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	
	if (!function_exists('is_plugin_active')) {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
}

if(!isset($cIQAuthCustomFields)) {
	require_once iq_authorization_CORE_DIR.'/includes/inc_custom_fields.php';
	$cIQAuthCustomFields = new IQAuthCustomFields();
}

$number = 9999999;
$paged = 1;
if(isset($_GET['paged']) && (int)$_GET['paged']) {
    $paged = (int)$_GET['paged'];
} 

$offset = ($paged - 1) * $number;
$users = $cIQAuthCustomFields->getCustomFields();
$total_users = count($users);
$args = [
    'offset' =>  $offset,
    'number' =>  $number,
];
$query = $cIQAuthCustomFields->getCustomFields($args);
$total_users = count($users);
$total_query = count($query);
$total_pages = intval($total_users / $number) + 1;

$iCollumsCount = 0;
?>
<table class="iq_ref_tbl">
	<thead>
		<tr>
			<th scope="col">
				Название поля
				<?php $iCollumsCount++ ?>
			</th>
			<th scope="col">
				Тип поля
				<?php $iCollumsCount++ ?>
			</th>
			<th scope="col">
				Иконка
				<?php $iCollumsCount++ ?>
			</th>
			<th scope="col">
				Обязательно
				<?php $iCollumsCount++ ?>
			</th>
			<th scope="col">
				Статус
				<?php $iCollumsCount++ ?>
			</th>
			<th scope="col">
				Создано
				<?php $iCollumsCount++ ?>
			</th>
			<th scope="col">
				Действие
				<?php $iCollumsCount++ ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
		if(!$query) {
			?>
			<tr>
				<td colspan="<?=$iCollumsCount;?>">
					<?php
					if($paged <= 1) {
						?>
						<div class="iq_ref_table_no_results">
							Произвольные поля отсутствуют
						</div>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
		} else {
			foreach($query AS $q) {
				include iq_authorization_CORE_DIR.'data/custom_field/data_custom_field_item_reload.php';
			}
		} ?>
	</tbody>
</table>