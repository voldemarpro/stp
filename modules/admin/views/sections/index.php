<div class="col-lg-8 col-md-8">
	<form class="write" action="/<?php echo "{$this->context->module->id}/{$this->context->id}" ?>/save" method="post">
	<?php	
	if (\Yii::$app->session->hasFlash('result')) {
		echo '
		<div class="alert alert-success">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			', \Yii::$app->session->getFlash('result'), '
		</div>';
	}
	echo '
	<input type="hidden" name="', \Yii::$app->getRequest()->csrfParam, '" value="', \Yii::$app->getRequest()->getCsrfToken(), '" />
	<div class="row">';
	
	foreach ($items as $it)
	{
		echo '
		<div class="col-lg-6 col-md-6">
		<h2>
			', $it['title'], '
		</h2>';

		$fields = [
			[
				'format'=>13,
				'value'=>$it->active,
				'name'=>$it->vname.'[active]',
				'label'=>'Активность'
			],
			
			[
				'format'=>0,
				'value'=>$it->precedence,
				'name'=>$it->vname.'[precedence]',
				'label'=>'Порядок'
			]	
		];
			
		echo $this->render(
			'/formfield',
			['fields'=>$fields]
		);
	
		echo '
		</div>';
	}	
		
	echo '
	</div>
	
	<br/>
	<div class="form-group"><input class="btn btn-primary btn-sm" type="submit" value="Сохранить"/></div>';

	?> 
	</form>
</div>