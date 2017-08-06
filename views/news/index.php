<?php
if (!empty($items)):
?> 
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<h1><?php echo \Yii::$app->thread->title ?></h1>
	</div><?php
	
	foreach (array_chunk($items, 2) as $itGroup)
	{
		echo '
		<div class="clearfix">';
		
		foreach ($itGroup as $it)
			echo '
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
				<h3>', $it['title'], '</h3>
				<span class="grey">', \Yii::$app->formatter->asDate($it['pub_date'], "dd MMM ''yy"), '</span>
				<p>', $it['preview'], '</p>
				<p><a href="', "/{$this->context->id}/{$it['id']}", '">Подробнее</a></p><br/>
			</div>';
		
		echo '
		</div>';
	}
	
	if (!empty($pagination) && $pagination->pageCount > 1)
	{
		echo '
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<ul class="pagination pagination-lg">';
				
				for ($i = 1; $i <= $pagination->pageCount; $i++) {
					echo '
					<li', ($pagination->page == ($i-1) ? ' class="active"' : ''), '><a href="', "/{$this->context->id}/page$i", '">', $i, '</a></li>';
				}
				
			echo '
			</ul>
		</div>';
	}
	
	?> 	

<?php elseif (!empty($item)): ?> 

	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<h1><?php echo \Yii::$app->thread->title ?></h1>
		<h2><em class="grey"><?php echo \Yii::$app->formatter->asDate($item['pub_date'], "dd MMM ''yy") ?></em>&nbsp;&nbsp;<?php echo $item['header'] ?></h2>
		<div>
			<?php echo $item['content'] ?> 
		</div>
		<br/>
		<p><a href="/<?php echo $this->context->id ?>">Все новости</a></p>
	</div>

<?php endif ?> 