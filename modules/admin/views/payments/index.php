<div class="col-lg-8 col-md-8">
	<div class="row">
		<div class="col-lg-6 col-md-6">
			<form class="form-inline" action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>">
				<select class="form-control input-lg" name="f">
					<option value="-1">Все</option><?php
					
				foreach (\app\models\Payout::$types as $i=>$t)
					echo '
					<option value="', $i, '"', ($i == $filter ? ' selected="selected"' : ''), '>', $t, '</option>';
					
				if ($for > 0) { 
					echo '
					<option value="u', $for, '">По трейдеру</option>';
					
					$filter = 'u'.$for;
				}
				
				?> 
				</select>
			</form>
		</div>
	</div>
</div>

<div class="col-lg-4 col-md-4">
	<div class="actions text-uppercase">
		<a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/write") ?>" class="btn btn-success btn-lg">Новая запись</a>
	</div>
</div>

<div class="col-lg-12 col-md-12">
	<table class="table table-striped text-lead">
		<thead>
			<tr>
				<th width="100">Дата</th>
				<th class="name">ФИО</th>
				<th width="140">Сумма &#x20bd;</th>
				<th>Целевой счет</th>
				<th></th>
			</tr>
		</thead>
		<tbody><?php
		
		foreach ($items as $it) {
			
			$stat = $users[$it->user_id]->stat ? explode(',', $users[$it->user_id]->stat) : [0,0];
			
			echo '
				<tr>
					<td>', \Yii::$app->formatter->asDate($it->{'date'}, "dd MMM"), '</td>
					
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
					
					<td>', app\models\Position::formatSign($it->sum ? $it->sum : 1), ' ', number_format(abs($it->sum), 2, '.', ' '), '</td>
					<td>', \app\models\Payout::$types[$it->type], '</td>
					<td class="actions">',
					($it->type > 0 
						? '
						<a href="'.yii\helpers\Url::to(["/{$this->context->module->id}/{$this->context->id}/write", 'id'=>$it->id]).'" title="Обновить">
							<span class="glyphicon glyphicon-pencil"></span>
						</a>'
						: ''
					), '
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