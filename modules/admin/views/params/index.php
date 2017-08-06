<div class="col-lg-6 col-md-6">
	<form class="write" action="/<?php echo "{$this->context->module->id}/{$this->context->id}" ?>/save" method="post">
<?php	
if (!empty($items))
{
	$itemGroups = [];
	foreach ($items as $item)
		$itemGroups[$item->thread_id][$item->id] = $item;
	
	ksort($itemGroups);
	
	if (\Yii::$app->session->hasFlash('result')) {
		echo '
		<div class="alert alert-success">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			', \Yii::$app->session->getFlash('result'), '
		</div>';
		
		//\Yii::$app->getSession->setFlash('result', null);
	}

	foreach ($itemGroups as $gid=>$group)
	{
		if (!$gid) echo '
		<input type="hidden" name="', \Yii::$app->getRequest()->csrfParam, '" value="', \Yii::$app->getRequest()->getCsrfToken(), '" />';
		
		echo '
		<h2>
			<big>', 
				isset(\Yii::$app->thread->items[$gid]) ? \Yii::$app->thread->items[$gid]['title'] : 'Общие параметры', 
			'</big>
		</h2>
		<div class="form-row">';
		
		$fields = [];
		
		foreach ($group as $it)
			$fields[] = [
				'format'=>$it->type,
				'value'=>unserialize($it->value),
				'name'=>$it->name,
				'label'=>$it->title
			];
			
		if ($fields)
			echo $this->render(
				'/formfield',
				['fields'=>$fields]
			);
		
		echo '
		</div>
		
		<div class="form-group"><input class="btn btn-primary btn-sm" type="submit" value="Сохранить"/></div>
		<br/>';
	}
}

?> 
	</form>
</div>