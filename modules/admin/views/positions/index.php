<?php
$cssAttr = ['', ' class="inactive"'];
?>

<div class="col-lg-12 col-md-12">
	<ul class="nav nav-tabs">
		<li class="active"><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>"><big>СПИСОК</big></a></li>
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/summary") ?>"><big>АНАЛИТИКА</big></a></li>
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/invoice") ?>"><big>ФИНАНСЫ</big></a></li>
	</ul>
	<br/>
</div>

<div class="col-lg-7 col-md-7">
	<form action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>">
		<div class="row">
			<div class="col-lg-4 col-md-4">
				<select class="form-control input-lg" name="for">
					<option value="0">Все</option>
					<option value="-1">Бесплатные счета</option>
					<option value="-2">Счета с депозитом</option>
					<option value="-3">Отмененные</option><?php
					
					if ($filter > 0) echo '
					<option value="'.$filter.'">По трейдеру</option>';
					
					?> 
				</select>
			</div>
			
			<div class="col-lg-4 col-md-4">
				<div class="input-group">
					<input type="text" class="form-control input-lg" name="date" value="<?php if ($date) echo date('d.m.Y', strtotime($date)) ?>" placeholder="По дате">
					<span class="input-group-addon"><a href="" class="times">×</a></span>
				</div>
			</div>	
		</div>
	</form>
	<script>
		$("[name=date]").datepicker({
			hideIfNoPrevNext: true,
			changeYear: true,
			changeMonth: true,
			showWeek: false,
			firstDay: 1,
			yearRange: "<?php echo date('Y'),':',(date('Y') + 1) ?>",
			dateFormat: "dd.mm.yy"
		});
		$("[name=date]").next().click(function() {
			return $(this).prev().val('').change() && false;
		});
		$("[name=date]").change(function() {
			$('form').first().submit();
		});
	</script>
</div>

<div class="col-lg-5 col-md-5">
	<div class="actions text-uppercase">
		<div class="input-group hidden">
			<input type="text" class="form-control input-lg" name="comment" placeholder="комментарий" value="" />
			<span class="input-group-addon"><a href="" class="btn btn-primary">Продолжить</a></span>
			<span class="input-group-addon"><a href="" class="times">×</a></span>
		</div>
		<a href="" class="btn btn-default btn-lg disabled">Отменить</a>
	</div>
</div>

<div class="col-lg-12 col-md-12">
	<form method="post" action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/cancel") ?>">
		
		<input type="hidden" name="<?php echo \Yii::$app->getRequest()->csrfParam ?>" value="<?php echo \Yii::$app->getRequest()->getCsrfToken() ?>" />
		<input type="hidden" name="_referrer" value="<?php echo $_SERVER['REQUEST_URI'] ?>" />
		<input type="hidden" name="comment" value="" />
		
		<table class="table table-striped text-lead">
			<thead>
				<tr>
					<th width="30"><input type="checkbox" name="_items" value="1" /></th>
					<th>Дата и время</th>
					<th class="name">Трейдер</th>
					<th>L/S</th>
					<th>Цена &#x20bd;</th>
					<th>Сумма $</th>
					<th>Выход</th>
					<th>Цена &#x20bd;</th>
					<th>Результат &#x20bd;</th>
				</tr>
			</thead>
			
			<tbody><?php
			
			foreach ($items as $i=>$it) {
				$stat = $users[$it->user_id]->stat ? explode(',', $users[$it->user_id]->stat) : [0,0];
			
				echo '
					<tr', $cssAttr[$it->disabled], '>
						<td><input type="checkbox" name="item[', $i, ']" value="', $it->id, '" /></td>
						<td>', \Yii::$app->formatter->asDate(date('Y-m-d H:i:s', strtotime($it->open_time) + $this->params['dto']), "dd MMM HH:mm"), '</td>
						<td>
							<a class="toggle-info" href="/', yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/?for=$it->user_id"), '">
								', $users[$it->user_id]->last_name, ' ', $users[$it->user_id]->first_name, ' ', $users[$it->user_id]->mid_name, '
								<div class="tooltip">
									<dl class="dl-horizontal text-left">
										<dt>Счет</dt> <dd>', str_replace(',', ' ', number_format($users[$it->user_id]->credit, 0)), '</dd>
										<dt>Депозит</dt> <dd>', ($users[$it->user_id]->deposit ? str_replace(',', ' ', number_format($users[$it->user_id]->deposit, 0)) : '&mdash;'), '</dd>
										<dt>Прибыль</dt> <dd>', \app\models\Position::formatSign($users[$it->user_id]->debit). str_replace(',', ' ', number_format(abs($users[$it->user_id]->debit), 0)), ' ₽</dd>
										<dt>Комиссия</dt> <dd>', $users[$it->user_id]->fee, '%</dd>
										
										<dt>Выкуп сделок</dt> <dd>', $users[$it->user_id]->opt ? 'eсть' : 'нет', '</dd>
										<dt>Сделки за месяц</dt> <dd>', $stat[0], '</dd>
										<dt>Прибыльные</dt> <dd>', $stat[1], ' (', $stat[0] ? round($stat[1]/$stat[0]*100) : 0, '%)</dd>										
										
										<dt>Договор</dt> <dd>№', $users[$it->user_id]->contract, '</dd>
										<dt>Начало</dt> <dd>', \Yii::$app->formatter->asDate($users[$it->user_id]->start_date, "dd MMM ''YY"), '</dd>
										<dt>Окончание</dt> <dd>', \Yii::$app->formatter->asDate($users[$it->user_id]->end_date, "dd MMM ''YY"), '</dd>
									</dl>									
								</div>
							</a>
						</td>
						<td>', ($it->type > 0 ? '<span class="green">&#x25B2;</span>' : '<span class="red">&#x25BC;</span>'), '</td>
						<td>', number_format($it->open_quot, 3), '</td>
						<td>', str_replace(',', ' ', number_format($it->open_sum, 0)), '</td>
						<td>', ($it->close_time ? \Yii::$app->formatter->asDate(date('Y-m-d H:i:s', strtotime($it->close_time) + $this->params['dto']), "HH:mm") : ''), '</td>
						<td>', ($it->close_quot ? number_format($it->close_quot, 3) : ''), '</td>
						<td>', ($it->close_time ? \app\models\Position::formatSign($it->result).'&nbsp;'.str_replace(',', ' ', number_format(abs($it->result), 2)) : ''), '</td>
						<!--<td class="actions">					
							<a href="', "/{$this->context->module->id}/{$this->context->id}/write/$it->id", '" title="Править">
								<span class="glyphicon glyphicon-pencil"></span>
							</a>
						</td>-->					
					</tr>';			
			}
			
			?> 
			</tbody>
		</table>
	</form><?php 
	
	if (!empty($pagination) && $pagination->pageCount > 1)
	{
		$firstPage = $pagination->page ? floor(($pagination->page + 1) / 10) + 1 : 1;
		$lastPage = floor(($pagination->page + 1) / 10) + 10;
		if ($pagination->pageCount < $lastPage)
			$lastPage = $pagination->pageCount;
			
		if ($_SERVER['QUERY_STRING'])
			$queryStringSuffix = '?'.$_SERVER['QUERY_STRING'];
		else
			$queryStringSuffix = '';
		
		echo '
		<ul class="pagination pagination-md">';
			
			if (($firstPage - 1) > 0)
				echo '
				<li><a href="', "/{$this->context->module->id}/{$this->context->id}/page", ($firstPage - 1), $queryStringSuffix, '">&lt;&lt;</a></li>';			
			
			for ($i = $firstPage; $i <= $lastPage; $i++) {
				echo '
				<li', ($pagination->page == ($i-1) ? ' class="active"' : ''), '><a href="', "/{$this->context->module->id}/{$this->context->id}/page$i", $queryStringSuffix, '">', $i, '</a></li>';
			}
			
			if (($lastPage + 1) <= $pagination->pageCount)
				echo '
				<li><a href="', "/{$this->context->module->id}/{$this->context->id}/page", ($lastPage + 1), $queryStringSuffix, '">&gt;&gt;</a></li>';			
			
			
		echo '
		</ul>';
	}
	
	?> 
</div>
<script>
	$('select').children().filter('[value=<?php echo $filter ?>]').prop({selected:true});			
	$('select').change(function() {
		$('form').first().submit();
	});
	$('[name=_items]').click(function() {
		$('[name^=item]').prop({checked: this.checked});
		$('.btn-default').toggleClass('disabled', !this.checked);
		return true;
	});
	$('[name^=item]').click(function() {
		if ($('[name=_items]').prop('checked'))
			$('[name=_items]').prop({checked: false});
		$('.btn-default').toggleClass('disabled', !this.checked || !$('[name^=item]').filter(':checked').length);
		return true;
	});
	$('.btn-default').click(function(e) {
		Dialog.stopEvent(e);
		$(this).hide().prev().removeClass('hidden');
		//return $('table').parent().submit() && false;
	});
	$('.btn-default').prev().children().find('.times').click(function(e) {
		Dialog.stopEvent(e);
		$(this).parent().parent().addClass('hidden').next().show();
	}).prev().click(function(e) {
		Dialog.stopEvent(e);
		$('table')
			.parent('form')
			.children('[name=comment]').val($('[name=comment]').first().val())
			.parent().submit() && false;
	});
</script>