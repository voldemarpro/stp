<div class="col-xs-12 col-sm-12 col-md-8 col-lg-7">

	<h1><?php echo \Yii::$app->thread->title ?></h1>

	<?php
	
	if (count($items))
	{
		foreach ($items as $it)
		{	
			echo '
			<div class="table-responsive">
				
				<table class="table table-hover table-striped">
					<caption class="text text-left"><i>', $this->params['formatter']->asDate(date('Y-m-d', strtotime($it->open_time) + $this->params['dto']), "dd MMMM ''yy"), '</i></caption>
					<thead>
						<tr>
							<th width="60">Время</th>
							<th width="10%">Операция</th>
							<th width="15%">Сумма $</th>
							<th width="13%">Цена <small class="glyphicon glyphicon-ruble"></small></th>
							<th width="20%">Итог <small class="glyphicon glyphicon-ruble"></small></th>
							<th>Комментарий</th>
						</tr>
					</thead>
					<tbody>								
						<tr>
							<td>', \date('H:i', \strtotime($it['open_time']) + $this->params['dto']), '</td>
							<td>П', \mb_substr(app\models\Position::$types[$it->type], 1, null, 'utf-8'), '</td>
							<td>', $it->open_sum, '</td>
							<td>', \number_format($it->open_quot, 2), '</td>
							<td></td>
							<td></td>
						</tr>';
						
						if ($it['close_time']) echo '
						<tr>
							<td>', \date('H:i', \strtotime($it['close_time']) + $this->params['dto']), '</td>
							<td>П', \mb_substr(app\models\Position::$types[-$it->type], 1, null, 'utf-8'), '</td>
							<td>', $it->open_sum, '</td>
							<td>', \number_format($it->close_quot, 2), '</td>
							<td>', (!$it->result ? '+ ' : \app\models\Position::formatSign($it->result)), \number_format(abs($it->result), 2, '.', ' '), '</td>
							<td>', $it->comment, '</td>
						</tr>';
						
			echo '
					</tbody>
				</table>
			
			</div>';
		
		}
	}
	?> 
</div>	