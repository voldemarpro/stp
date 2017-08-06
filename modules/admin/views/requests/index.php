<div class="col-lg-8 col-md-8">
	<div class="row">
		<div class="col-lg-6 col-md-6">
			<form class="form-inline" action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>">
				<select class="form-control input-lg" name="f">
					<option value="0">Все</option>
					<option value="1">Перевод средств</option>
					<option value="2">Увеличение счета</option><?php

					if (!is_numeric($filter)) { 
						echo '
						<option value="', $filter, '">По трейдеру</option>';
					}
					
					?> 
				</select>
			</form>
		</div>
	</div>
</div>

<div class="col-lg-4 col-md-4">
	<!--<div class="actions text-uppercase">
		<a href="/<php //echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/write") ?>" class="btn btn-success btn-lg">Новая запись</a>
	</div>-->
</div>

<div class="col-lg-12 col-md-12">
	<table class="table table-striped text-lead">
		<thead>
			<tr>
				<th width="160">Дата и время</th>
				<th class="name">ФИО</th>
				<th width="120">Сумма ₽</th>
				<th>Предмет</th>
				<th></th>
			</tr>
		</thead>
		<tbody><?php
		
		foreach ($items as $it) {
			
			$stat = $users[$it->user_id]->stat ? explode(',', $users[$it->user_id]->stat) : [0,0];
			$dt = date('Y-m-d H:i:s', strtotime($it->date_time) + $this->params['dto']);
			
			echo '
				<tr', ($it->status > 0 ? '' : ' class="inactive"'), '>
					<td>', \Yii::$app->formatter->asDate($dt, "dd MMM HH:mm"), '</td>
					<td>
						<a class="toggle-info" href="/', yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/?for=$it->user_id"), '">
							', $users[$it->user_id]->last_name, ' ', $users[$it->user_id]->first_name, ' ', $users[$it->user_id]->mid_name, '
							<div class="tooltip">
								<dl class="dl-horizontal text-left">
									<dt>Счет</dt> <dd>', str_replace(',', ' ', number_format($users[$it->user_id]->credit, 0)), '</dd>
									<dt>Депозит</dt> <dd>', ($users[$it->user_id]->deposit ? str_replace(',', ' ', number_format($users[$it->user_id]->deposit, 0)) : '&ndash;'), '</dd>
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
					<td>', str_replace(',', ' ', number_format($it->amount, 2)), '</td>
					<td>', ($it->aux ? 'перевод средств, '.\app\models\Payout::$types[$it->aux] : 'увеличение счета'), '</td>
					<td class="actions">
						', 
						($it->status > 0 ? '<span class="glyphicon glyphicon-thumbs-up"></span>' : '
						<a href="'.yii\helpers\Url::to(["/{$this->context->module->id}/{$this->context->id}/write", 'id'=>$it->id]).'" title="Обновить">
							<span class="glyphicon glyphicon-pencil"></span>
						</a>'), '
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
	$('select').children().filter('[value=<?php echo $filter ?>]').prop({selected:true});			
	$('select').change(function() {
		$(this).parent().submit();
	});
</script>