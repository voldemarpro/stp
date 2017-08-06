<?php
	// Состояние кнопок "продать" и "купить"
	$disabledStateVars = [' disabled', ''];
	$btnDisabled = [
		$disabledStateVars[$state['allowOpen'] || $state['allowClose'] && $state['allowSell']],
		$disabledStateVars[$state['allowOpen'] || $state['allowClose'] && $state['allowBuy']]
	];
?>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<h1><?php echo \Yii::$app->thread->title ?></h1>
</div>

<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
	<ul class="nav nav-tabs group"><?php 

	$variants = $variants = \app\models\Quotation::$candles;
	
	foreach ($variants as $i=>$v)
		echo '
		<li', ($i == 1 ? ' class="active"' : ''), '><a class="small" href="', "time{$i}", '">', $v['title'], '</a></li>';

	?> 
	</ul>
	
	<a style="display:block; position:relative; color: inherit" class="thumb" href="<?php echo $variants['1']['usdrub'] ?>">
		<br/>
		<span class="graph-label">USD/RUB</span>
		<p id="usdrub">
			<img class="img-responsive" src="<?php echo $variants['1']['usdrub'] /*echo 'http://informers.forexpf.ru/php/graphics.php?sym=USDRUB&vsz=500&hsz=1100&tic=0&typ=2&sz=200&pass=923443&index='.md5(time())*/ ?>" /><img src="//:0" class="img-responsive hidden" />
		</p>
	</a>
	
	<a style="display:block; position:relative; color: inherit" class="thumb" href="<?php echo 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=0&typ=2&sz=200&pass=923443' ?>">
		<br/>
		<span class="graph-label">BRENT</span>
		<p id="brent">
			<img class="img-responsive" src="<?php echo 'http://informers.forexpf.ru/php/graphics.php?sym=CL_Brent&vsz=500&hsz=1100&tic=0&typ=2&sz=200&pass=923443&index='.md5(time()) ?>" /><img src="//:0" class="img-responsive hidden" />
		</p>
	</a>
</div>

<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
	
	<br/>
	
	<ul class="preview row"><?php
		
	if (!empty($news))
		foreach ($news as $it)
		{	
			
			$title = mb_strlen($it['title'], 'utf-8') > 80 ? mb_substr($it['title'], 0, 78, 'utf-8').'...' : $it['title'];
			//$dotPos = mb_strpos($title, '.', 0, 'utf-8'); // first position of dot, after country name (for short forecast news)
			
			echo '
			<li class="col-xs-12 col-sm-6 col-md-12 col-lg-12">
				<a class="goto" target="_blank" href="/news/', $it['id'], '">
					<h4>', $title, '</h4> 
					
					<p class="grey">', \Yii::$app->formatter->asDate($it['pub_date'], "dd MMM ''yy"), '</p>';
					
					/*if (!$it['src']) */echo '
					<div>
						<p>' . mb_substr($it['preview'], 0, 100, 'utf-8') . '</p><span class="blur"></span>	
						<p><span class="blue">Подробнее</span></p>
					</div>
					<div class="hidden">
						', $it['content'], '
					</div>';
	
					/*else echo '
					<div>
						<p>', mb_substr($it['preview'], ($dotPos + 2), null, 'utf-8'), '</p>
					</div>';*/					
			
			echo '
				</a>
			</li>';
		}
	
	?> 
	</ul>
</div>

<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6<?php if (!$state['currentPosition']['type']) echo ' inactive' ?>">
	<h2>Текущая сделка</h2>		
	<dl class="dl-horizontal nobr">
		<dt>Счет</dt><dd><span><?php echo app\models\Position::formatSum(\Yii::$app->user->identity->credit) ?></span>&nbsp;<span class="glyphicon glyphicon-ruble"></span></dd>
		<dt>Баланс</dt><dd><span><?php echo app\models\Position::formatSum(\round($state['balance'])) ?></span>&nbsp;<span class="glyphicon glyphicon-ruble"></span></dd>
		<dt>Позиция</dt><dd><span><?php echo $state['currentPosition']['type'] * $state['currentPosition']['openSum'] ?></span>&nbsp;$</dd>
		<dt>Результат</dt><dd><span id="result"><?php echo \str_replace(',', ' ', \number_format($state['currentPosition']['result'], 2)) ?></span>&nbsp;<span class="glyphicon glyphicon-ruble"></span></dd>									
		
		<?php if ($state['currentPosition']['type']) echo '
		
		<dt>Вход</dt><dd>', app\models\Position::$types[$state['currentPosition']['type']], ' по ', \number_format($state['currentPosition']['openQuot'], 2), ' в ', $state['currentPosition']['openTime'], '</dd>';
		
		if (!empty($state['currentPosition']['closeTime']))	echo '
		<dt>Выход</dt><dd>', app\models\Position::$types[-$state['currentPosition']['type']], ' по ', \number_format($state['currentPosition']['closeQuot'], 2), ' в ', $state['currentPosition']['closeTime'], '</dd>';
		
		?>
	</dl>
</div>

<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
	<h2>Котировки рынка (USD/RUB)</h2>
	
	<dl class="quot-stat dl-horizontal nobr">
		<dt>Покупка</dt><dd><?php echo \number_format($state['quot']['bid'], 2) ?></dd>
		<dt>Продажа</dt><dd><?php echo \number_format($state['quot']['ask'], 2) ?></dd>
		<dt>Изменение</dt><dd><?php echo \number_format($state['quot']['diff'], 2) ?>%</dd>									
	</dl>
	
	<div>
		<a href="<?php echo "/{$this->context->id}/sell" ?>" class="pos-ctrl btn btn-primary text-uppercase<?php echo $btnDisabled[0] ?>">Продать</a>
		<a href="<?php echo "/{$this->context->id}/buy" ?>" class="pos-ctrl btn btn-success text-uppercase<?php echo $btnDisabled[1] ?>">&nbsp;Купить&nbsp;</a>
	</div>
	
	<br/>
	
	<div class="note">
		<h4 class="text-uppercase">Условия торговли</h4>
		<ul><?php 
			
			echo '
			<li>График торговли с ', date('H:i', \Yii::$app->params['open_time'] + $this->params['dto']), ' до ', date('H:i', \Yii::$app->params['close_time'] + $this->params['dto']), ' МСК</li>		
			<li>Открытие позиции до ', date('H:i', \Yii::$app->params['input_before'] + $this->params['dto']), ' МСК</li>	
			<li>Одна сделка в день</li>';

			$minTime = \Yii::$app->params['pos_min_time'];
			$minTimeCond = preg_replace('/\d+/u', $minTime, app\models\Traider::$terms[2][0]);
					
			if (!\Yii::$app->user->identity->opt) {
				echo '
				<!--<li>', app\models\Traider::$terms[0][0], '</li>-->
				<li>', app\models\Traider::$terms[1][0], '</li>
				<li>', $minTimeCond, '</li>
				<!--<li>', app\models\Traider::$terms[3][0], '</li>-->';
			} else {
				if (\Yii::$app->user->identity->deposit) {
					echo '
					<li>', app\models\Traider::$terms[0][1], '</li>
					<li>', app\models\Traider::$terms[1][0], '</li>
					<li>', $minTimeCond, '</li>';					
				
				} else echo '
					<li>', app\models\Traider::$terms[0][1], '</li>
					<li>', app\models\Traider::$terms[1][1], '</li>';
			
			}
			
			echo '
				<li>Комиссия с прибыльных сделок &ndash; ', \Yii::$app->user->identity->fee, '%</li>';			
		
		?> 
		</ul>
	</div>	
</div>

<script>
	(function() {
		var i = 0;
		var variants = $.parseJSON('<?php echo json_encode($variants) ?>');
		var variant = variants[1];
		var graphUpdate = function() {
			if (i == 30)
				window.location.reload();
			for (var item in variant) {
				$('#' + item).children('.hidden').first().clone().attr({alt:item}).on('load', function() {
					$(this).prependTo('#' + $(this).attr('alt')).removeClass('hidden').next().remove();
					$(this).parent().parent().attr({href: $(this).attr('src')});
					window.setTimeout(graphUpdate, 29500);
				}).attr({src: variant[item] + '&index=' + i + '<?php echo md5(time()) ?>'});
			}
			i++;
		};
		window.setTimeout(graphUpdate, 29500);
		$('.nav-tabs a').click(function() {
			i = (i >= 15) ? 15 : i;
			variant = variants[parseInt($(this).attr('href').replace('time', ''))];
			graphUpdate();
			$(this).parent().addClass('active').siblings().removeClass('active');
			return false;
		});
	})();
</script>

<script>
	var refQuot = <?php echo (float)$state['quot']['ref'] ?>;
	var statCells = $('.quot-stat dd').toArray();

	if (window.STP) {
		$.ajaxSetup({
			cache: false
		});
		$('.goto').click(function(e) {
			STP.dialog.stopEvent(e);
			$('#stp-text').find('div.small').html($(this).children('.hidden').html());
			STP.dialog.open('#stp-text');
		});		
		STP.position = {
			sum: <?php echo $state['currentPosition']['openSum'] ?>,
			quot: <?php echo \number_format($state['currentPosition']['openQuot'], 3) ?>,
			type: <?php echo $state['currentPosition']['type'] ?>,
			result: <?php echo \number_format($state['currentPosition']['result'], 2) ?>,
			closed: <?php echo !empty($state['currentPosition']['closeTime']) ? 1 : 0 ?>
		};
		// обновление котировок и состояния торгов
		STP.updateContent = function() {
			
			if (!STP.open && (Math.abs(STP.position.type) == STP.position.closed))
				return false;

			$.getJSON('<?php echo \Yii::$app->params['stp1_info'] ?>', function(data) {
				try {
					var avg = 0;
					var quot = 0;
					avg = (parseFloat(data.bid) + parseFloat(data.ask)) / 2;
					$(statCells[0]).text(parseFloat(data.bid).toFixed(2));
					$(statCells[1]).text(parseFloat(data.ask).toFixed(2));
					$(statCells[2]).text( ((avg/refQuot - 1)*100).toFixed(2) + '%');
				} catch(e) {
					console.log(data);
					console.log(e);
				}
			});
			
			if (STP.position.sum && !STP.position.closed) {
				if (STP.position.type > 0)
					STP.position.result = ($(statCells[0]).text() - STP.position.quot) * STP.position.sum;
				else
					STP.position.result = ($(statCells[1]).text() - STP.position.quot) * STP.position.sum * (-1);
				
				$('#result').text(STP.position.result.toFixed(2));
			}	

			if (Math.floor(Date.now() / 1000) > <?php echo \Yii::$app->params['close_time'] ?>) {
				$.get(
					'/<?php echo $this->context->id ?>/update', {},
					function(htmlResp) {
						STP.open = 0;
						try {
							if (parseInt(htmlResp.substr(0, 2)) == ' 1')
								htmlResp = htmlResp.substr(2);
							$('.content').children().not('.notices').remove();
							$('.content').append(htmlResp);
						} catch(e) {
							console.log(e);
						}
					}
				);
			} else if ($('.pos-ctrl').hasClass('disabled')) {
				$.getJSON(
					'/<?php echo $this->context->id ?>/getstate', {},
					function(resp) {
						try {
							if (resp['allowOpen'])
								$('.pos-ctrl').removeClass('disabled');
							else if (resp['allowClose']) {
								$('.pos-ctrl').each(function() {
									if (resp['allowSell'])
										$('.pos-ctrl').first().removeClass('disabled');
									else
										$('.pos-ctrl').last().removeClass('disabled');
								});
							}
						} catch(e) {
							console.log(e);
						}
					}
				);			
			}
		};
		
		// открытие-закрытие сделки
		STP.setPosition = function(btn) {	
			STP.dialog.showProc();
			$.get(
				$(btn).attr('href'), {},
				function(htmlResp) {
					console.log(htmlResp);
					if (!htmlResp) {
						console.log(0);
						STP.dialog.close();
					}
					try {
						if (parseInt(htmlResp.substr(0, 2)) == ' 1')
							htmlResp = htmlResp.substr(2);
						$('.content').children().not('.notices').remove();
						$('.content').append(htmlResp);
						STP.dialog.close();
					} catch(e) {
						console.log(e);
						STP.dialog.close();
					}
				},
				'html'
			);			
		};

		// открытие-закрытие сделки (управление)
		$('.pos-ctrl').click(function(e) {
			STP.dialog.stopEvent(e);
			STP.dialog.confirm(function() {
				STP.setPosition(e.target || e.srcElement);
			});
		});
	};
</script>