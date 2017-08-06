<div class="col-lg-2 col-md-3">
	<form action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>">
		<div class="input-group">
			<input type="text" class="form-control input-lg" name="date" value="<?php if ($date) echo date('d.m.Y', strtotime($date)) ?>" placeholder="По дате">
			<span class="input-group-addon"><a href="" class="times">×</a></span>
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

<div class="col-lg-10 col-md-9">
	<div class="actions text-uppercase">
		<a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/write") ?>" class="btn btn-success btn-lg">Новая запись</a>
	</div>
</div>

<div class="col-lg-12 col-md-12">
		<table class="table table-striped text-lead">
			<thead>
				<tr>
					<th width="160">Дата и время</th>
					<th>Сообщение</th>
					<th width="100"></th>
				</tr>
			</thead>
			
			<tbody><?php
			
			foreach ($items as $i=>$it) {
				$dt = date('Y-m-d H:i:s', strtotime($it->date_time) + $this->params['dto']);
				
				echo '
					<tr>
						<td>', \Yii::$app->formatter->asDate($dt, "dd MMM HH:mm"), '</td>
						<td>', $it->text, '</td>
						<td class="actions">					
							<a href="', "/{$this->context->module->id}/{$this->context->id}/copy/$it->id", '" title="Клонировать">
								<span class="glyphicon glyphicon-tags"></span>
							</a>
							<a href="', "/{$this->context->module->id}/{$this->context->id}/delete/$it->id", '" title="Удалить" class="del">
								<span class="glyphicon glyphicon-trash"></span>
							</a>
						</td>					
					</tr>';			
			}
			
			?> 
			</tbody>
		</table><?php

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
			<ul class="pagination pagination-lg">';
				
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