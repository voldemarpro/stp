<div class="col-xs-12 col-sm-12 col-md-12">

	<h1><?php echo \Yii::$app->thread->title ?></h1>
	
</div>


	<?php
	
	$symArrOpen = [-1=>'icon-buy green', 1=>'icon-sell red'];
	$symArrClose = [-1=>'icon-sell', 1=>'icon-buy'];
	$curr = STP_VRS == 1 ? '₽' : '$';
	
	if (count($items))
	{
		foreach ($items as $it)
		{	
			echo '
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">	
				<dl class="dl-horizontal item-frame">
					<dt>Дата</dt>
					<dd>'.$it->fdate.'</dd>
					
					<dt>Объем</dt>
					<dd>'.$it->volume.'</dd>
					
					<dt>Открытие</dt>
					<dd><i class="' . $symArrOpen[$it->type] . '"></i> в '.$it->fopen_time.' по ' . number_format($it->open_quot, 2) . '</dd>
					
					<dt>Закрытие</dt>
					<dd>'; 
						if ($it->close_time) echo '<i class="' . $symArrClose[$it->type] . '"></i> в '.$it->fclose_time.' по ' . number_format($it->close_quot, 2);
						else echo '&ndash;'; 
					echo '
					</dd>
					
					<dt>Результат '.$curr.'</dt>
					<dd>'; 
						if ($it->close_time) echo '<span class="monosign">'.($it->result >= 0 ? '+' : '-').'</span>' . number_format(abs($it->result), 2, '.', ' ');
						else echo '&ndash;'; 
					echo '
					</dd>';
						
			echo '
				</dl>
			</div>';
		
		}
	}
	?> 
</div>