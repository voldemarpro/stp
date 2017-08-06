<div class="col-lg-8 col-md-8">
	<div class="row">
		<div class="col-lg-6 col-md-6">
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
				<th width="80%">Заголовок</th>
				<th></th>
			</tr>
		</thead>
		<tbody><?php
		
		foreach ($items as $it) {

			echo '
				<tr>
					<td>', \Yii::$app->formatter->asDate($it->{'pub_date'}, "dd MMM"), '</td>
					
					<td>';
						
						if ($it->link) echo '
						<a target="_blank" href="'.$it->link.'">'.$it->header.'</a></td>';
						else echo
						$it->header;
					
					echo '
					</td>
				
					<td class="actions">
						<a href="'.yii\helpers\Url::to(["/{$this->context->module->id}/{$this->context->id}/write", 'id'=>$it->id]).'" title="Править">
							<span class="glyphicon glyphicon-pencil"></span>
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