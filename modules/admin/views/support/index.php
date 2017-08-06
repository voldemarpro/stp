<?php
$cssAttr = [0=>'', 1=>' class="done"'];
?>

<div class="col-lg-8 col-md-8"></div>
<div class="col-lg-4 col-md-4"></div>

<div class="col-lg-12 col-md-12">
	<table class="table table-striped text-lead">
		<thead>
			<tr>
				<th width="100">Дата</th>
				<th class="name">ФИО</th>
				<th>Сообщение</th>
				<th></th>
			</tr>
		</thead>
		<tbody><?php
		
		foreach ($items as $it) {
			
			$stat = $users[$it->user_id]->stat ? explode(',', $users[$it->user_id]->stat) : [0,0];
			
			echo '
				<tr>
					<td>', \Yii::$app->formatter->asDate($it->date_time, "dd MMM"), '</td>
					<td>
						<a class="toggle-info" href="/', yii\helpers\Url::to("{$this->context->module->id}/positions/?for=$it->user_id"), '">
							', $users[$it->user_id]->last_name, ' ', $users[$it->user_id]->first_name, ' ', $users[$it->user_id]->mid_name, '
							<div class="tooltip">
								<dl class="dl-horizontal text-left">
									<dt>Счет</dt> <dd>', str_replace(',', ' ', number_format($users[$it->user_id]->credit, 0)), '</dd>
									<dt>Депозит</dt> <dd>', ($users[$it->user_id]->deposit ? str_replace(',', ' ', number_format($users[$it->user_id]->deposit, 0)) : '&ndash;'), '</dd>
									<dt>Комиссия</dt> <dd>', $users[$it->user_id]->fee, '%</dd>
									
									<dt>Сделки за месяц</dt> <dd>', $users[$it->user_id]->opt ? 'eсть' : 'нет', '</dd>
									<dt>Всего за месяц</dt> <dd>', $stat[0], '</dd>
									<dt>Прибыльные</dt> <dd>', $stat[1], ' (', $stat[0] ? round($stat[1]/$stat[0]*100) : 0, '%)</dd>								
									
									<dt>Договор</dt> <dd>№', $users[$it->user_id]->contract, '</dd>
									<dt>Начало</dt> <dd>', \Yii::$app->formatter->asDate($users[$it->user_id]->start_date, "dd MMM ''YY"), '</dd>
									<dt>Окончание</dt> <dd>', \Yii::$app->formatter->asDate($users[$it->user_id]->end_date, "dd MMM ''YY"), '</dd>
								</dl>									
							</div>
						</a>
					</td>
					<td>', mb_substr($it->message, 0, 100, 'utf-8'), ($it->response ? '<br/><small class="grey">'.mb_substr($it->response, 0, 100, 'utf-8').'</small>' : ''), '</td>
					<td class="actions">
						', 
						($it->response ? '<span class="glyphicon glyphicon-thumbs-up"></span>&nbsp;&nbsp;' : '
						<a href="'.yii\helpers\Url::to(["/{$this->context->module->id}/{$this->context->id}/write", 'id'=>$it->id]).'" title="Обновить">
							<span class="glyphicon glyphicon-pencil"></span>
						</a>'), '
						<a href="'.yii\helpers\Url::to(["/{$this->context->module->id}/{$this->context->id}/delete", 'id'=>$it->id]).'" title="Удалить" class="del">
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