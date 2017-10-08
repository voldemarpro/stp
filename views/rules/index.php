<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

	<h1><?php echo \Yii::$app->thread->title ?></h1>

	<div class="row clearfix">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h2>Общие правила</h2>
		</div>
		<?php

		if (!empty($tariff))
		{
			echo '
			<div class="col-xs-12 col-sm-6 col-md-6">';

				$terms = explode("\n", $tariff->terms);
				
				foreach ($terms as $i=>$it)
				{	
					echo '
					<p>
						', ($i+1), '. ', trim($it), '
					</p>';
					
					if ($i == (count($terms) - 4)) break;
				}
									
			echo '
			</div>';
			
			echo '
			<div class="col-xs-12 col-sm-6 col-md-6">';

				foreach ($terms as $i=>$it)
				{	
					if ($i <= (count($terms) - 4)) continue;
					
					echo '
					<p>
						', ($i+1), '. ', trim($it), '
					</p>';
				}
									
			echo '
			</div>';
			
		}
		
		?> 
		
		<div class="col-xs-12 col-sm-12 col-md-12">
			<h2>Инструкции по торговле</h2>
			<?php echo \Yii::$app->params['instructions'] ?>
		</div>
	</div>
</div>