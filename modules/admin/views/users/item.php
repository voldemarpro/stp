<dl><?php
	
	if ($item)
		echo '
		<dd>Счет</dd> <dt>', str_replace(',', ' ', number_format($item->credit, 0)), '</dt>
		<dd>Депозит</dd> <dt>', ($item->deposit ? str_replace(',', ' ', number_format($item->deposit, 0)) : '&ndash;'), '</dt>
		<dd>Комиссия</dd> <dt>', $item->fee, ' %</dt>
		<dd>Выкуп сделок</dd> <dt>', $item->opt ? 'eсть' : 'нет', '</dt>
		<dd>№ Договора</dd> <dt>', $item->contract, '</dt>
		<dd>Начало</dd> <dt>', \Yii::$app->formatter->asDate($item->start_date, "dd MMM ''YY"), '</dt>
		<dd>Окончание</dd> <dt>', \Yii::$app->formatter->asDate($item->end_date, "dd MMM ''YY"), '</dt>';
	
?></dl>