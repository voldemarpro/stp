<?php
$cssAttr = ['', ' class="inactive"'];
?>
<div class="col-lg-8 col-md-8">
	<div class="row">
		<div class="col-lg-6 col-md-6">
			<form class="form-inline" action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>">
				<select class="form-control input-lg" name="f">
					<option value="0">Все</option>
					<option value="-1">По букве</option>
					<option value="4">Новые</option>
					<option value="1">Бесплатные счета</option>
					<option value="2">Счета с депозитом</option>
					<option value="3">Заблокированные</option>
					<option value="5">Администраторы</option>
				</select>
				<!--<div class="form-group">
					<a href="" style="padding-left: 10px; display: inline-block; vertical-align: middle; font-weight:800;font-family:'Times'; font-size: 190%"><?php echo intval($filter) == $filter ? '<i>А</i>б' : $filter ?></a>
					<a class="times" style="display: inline-block; vertical-align: middle;">&times;</a>
					<input type="hidden" value="" name="f" disabled="disabled">
				</div>-->
			</form>
		</div>
	</div>
</div>

<div class="col-lg-4 col-md-4">
	<div class="actions text-uppercase">
		<a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/write") ?>" class="btn btn-success btn-lg">Новая запись</a>
	</div>
</div>

<div class="col-lg-12 col-md-12<?php echo is_numeric($filter) ? ' hidden' : '' ?>">
	<ul class="list-unstyled list-inline crumbs"><?php
		
	$abc = array();
	foreach (range(chr(0xC0),chr(0xDF)) as $i=>$v) 
		if (!in_array($i, [9, 26, 27, 28])) echo '	
		<li><a', ($filter === iconv('cp1251', 'utf-8', $v) ? ' class="active" ' : ''), ' href=".">', iconv('cp1251', 'utf-8', $v), '</a></li>';

	?> 
	</ul>
</div>

<div class="col-lg-12 col-md-12">
	<table class="table table-striped text-lead">
		<thead>
			<tr>
				<th width="240">ФИО</th>
				<th width="20">&#9733;</th>
				<th width="20">&#x20bd;</th>
				<th>Счет</th>
				<th>Депозит</th>
				<th>Результат</th>
				<th colspan="2">Доходность [<?php echo rtrim( mb_strtolower(\Yii::$app->formatter->asDate(date('Y-m-d'), "LLL"), 'utf-8'), '.') ?>]</th>
				<th>Договор</th>
				<th>Окончание</th>
				<th></th>
			</tr>
		</thead>
		<tbody><?php
		
		foreach ($items as $it) {
			
			$stat = $it->stat ? explode(',', $it->stat) : [0,0];
			
			echo '
				<tr', $cssAttr[$it->blocked], '>
					<td>
						<a title="', \app\models\Trader::formatName($it, false), '" href="/', yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/write/$it->id"), '">
							', ($it->last_name && $it->first_name ? (\app\models\Trader::formatName($it)) : ('+7 '.substr($it->phone, 0, 3).' '.substr($it->phone, 3))), '
						</a>
					</td>
					<td>', ($it->opt ? '&#9733;' :''), '</td>
					<td>&#x20bd;</td>
					<td>', str_replace(',', ' ', number_format($it->credit / 1000, 0)), 'k</td>
					<td>', ($it->deposit ? str_replace(',', ' ', number_format($it->deposit, 0)) : ''), '</td>
					<td>', \app\models\Position::formatSign($it->debit).'&nbsp;'.str_replace( ',', ' ', number_format(abs($it->debit), 2) ), '</td>
					
					<td>', $stat[1], '/', $stat[0], '</td>
					<td> (', ($stat[0] ? round($stat[1]/$stat[0]*100) : 0), '%)</td>
					
					<td>', $it->contract, '</td>
					<td>', \Yii::$app->formatter->asDate($it->end_date, "dd MMM "), "'", (intval($it->end_date) - 2000), '</td>
					<td class="actions">
						<a href="', yii\helpers\Url::to(["/{$this->context->module->id}/positions", 'for'=>$it->id]), '" title="Сделки">
							<span class="glyphicon glyphicon-briefcase"></span>
						</a>						
						<a href="', "/{$this->context->module->id}/{$this->context->id}/delete?id={$it->id}", '" title="Удалить навсегда" class="del">
							<span class="glyphicon glyphicon-trash"></span>
						</a>
					</td>
				</tr>';			
		}
		
		?> 
		</tbody>
	</table><?php

	
	if (!empty($pagination))
		echo $this->render('/paging', 
			['pageCount'=>$pagination->pageCount, 'page'=>$pagination->page]
		);
	
	?> 
</div>
<script>
	$('select').children().filter('[value=<?php echo is_numeric($filter) ? $filter : '-1' ?>]').prop({selected:true});			
	$('select').change(function() {
		if (this.value != '-1') {
			$(this).parent().submit();
		} else {
			$('.crumbs').parent().removeClass('hidden');
		}
	});
	$('.crumbs a').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('select').children().first().next().attr({value: $(this).text()}).prop({selected:true}).parent().change();
	});
</script>