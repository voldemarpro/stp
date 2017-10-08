<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<h1>Торговая площадка</h1>
</div>

<?php
	$summary = $this->params['summary'];
	$curr = STP_VRS == 1 ? '<span class="glyphicon glyphicon-ruble"></span>' : '$';
	$chartData = [];
	foreach (\app\models\Contract::$variants as $k=>$v) {
		$k = (int)$k;
		$chartData[$k] = [];
		foreach (\app\models\Contract::$ticks as $i=>$t) {
			$chartData[$k][$i]['xM'] = str_replace(
													['{fx_sym}', '{tick}', '{vsz}', '{hsz}'],
													[$v['fx_sym'], $i, 500, 1100],
													\app\models\Contract::$candleChartSrc
												);
			$chartData[$k][$i]['xL'] = str_replace(
													['{fx_sym}', '{tick}', '{vsz}', '{hsz}'],
													[$v['fx_sym'], $i, 600, 1600],
													\app\models\Contract::$candleChartSrc
												);												
		}
	}

if ($summary['position']):
?>

<div class="m-position col-xs-12 col-sm-12 hidden-md hidden-lg">
	<div class="row">
		<div class="col-xs-5 col-sm-5 text-right">
			<?php 
				if (!$summary['position']['fclose_time'])
					echo ($summary['position']['type'] < 0 ? '<span class="icon-sell red"></span>' : '<span class="icon-buy green"></span>');
				else
					echo ($summary['position']['type'] < 0 ? '<span class="icon-sell"></span>' : '<span class="icon-buy"></span>');
			?>
			<span id="m-balance"><?php echo printSum($summary['position']['volume']) ?></span>&nbsp;<span><?php echo STP_VRS == 1 ? '$' : 'bbl' ?></span>
		</div>
		<div class="col-xs-1 col-sm-1"></div>
		<div class="col-xs-5 col-sm-5 text-left">
			<span id="m-result"><?php echo ($summary['position']['result'] < 0 ? '&ndash; ' : '+ ') . printSum(abs($summary['position']['result']), 2) ?></span>&nbsp;<?php echo $curr ?>
		</div>
	</div>
</div> 

<?php endif ?>

<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
	<ul class="nav nav-tabs group">
		<li class="active"><a class="small" href="#1min">1 мин</a></li>
		<li><a class="small" href="#5mins">5 мин</a></li>
		<li><a class="small" href="#15mins">15 мин</a></li>
		<li><a class="small" href="#1hour">1 час</a></li>
		<li><a class="small" href="#1day">1 день</a></li>
		<li><a class="small" href="#1week">1 нед</a></li> 
	</ul>
	
	<h3 class="mb0 text-right">
		<span><?php echo \app\models\Contract::$variants[0]['label'] ?></span>
	</h3>
	<a class="chart-box" href="<?php echo $chartData[0][0]['xM'] ?>">
		<br/>
		<p>
			<img class="img-responsive" src="<?php echo $chartData[0][0]['xM'] ?>" /><img src="//:0" class="img-responsive hidden" />
		</p>
	</a>
	
	<h3 class="mb0 text-right">
		<span><?php echo \app\models\Contract::$variants[1]['label'] ?></span>
	</h3>
	<a class="chart-box" href="<?php echo $chartData[1][0]['xM'] ?>">
		<br/>
		<p>
			<img class="img-responsive" src="<?php echo $chartData[1][0]['xM'] ?>" /><img src="//:0" class="img-responsive hidden" />
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

<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
	<div class="row">
		<div class="col-xs-6 col-lg-4">
			<h3>Продажа</h3>
			<p class="gray"><span id="quote-ask"><?php echo number_format($summary['quotes']['ask'], 2) ?></span>&nbsp;<?php echo $curr ?></p>
		</div>
		<div class="col-xs-6 col-lg-4">
			<h3>Покупка</h3>
			<p class="gray"><span id="quote-bid"><?php echo number_format($summary['quotes']['bid'], 2) ?></span>&nbsp;<?php echo $curr ?></p>
		</div>
		<div class="col-xs-12 col-sm-6 col-lg-4">
			<h3>Изменение к закрытию</h3>
			<p class="gray"><span id="quote-diff"><?php echo $summary['quotes']['diff'] ?></span> %</p>
		</div>
		
		<div class="col-xs-12 col-sm-6 col-lg-4">
			<br class="hidden-lg hidden-md hidden-sm" />
			<button class="btn btn-buy btn-block"<?php echo !$summary['session']['allowBuy'] ? ' disabled="disabled"' : '' ?>><span class="icon-buy"></span> Купить<span class="hidden-lg hidden-sm visible-xs-inline-block">&nbsp;&nbsp;&nbsp;</span></button>
		</div>
		<div class="col-xs-12 col-sm-6 col-lg-4">
			<br class="hidden-lg hidden-md hidden-sm" />
			<button class="btn btn-sell btn-block"<?php echo !$summary['session']['allowSell'] ? ' disabled="disabled"' : '' ?>"><span class="icon-sell"></span> Продать</button>
		</div>		
	</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-1 col-lg-1">
</div>

<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
	<div class="row">
		<div class="col-xs-6 col-lg-6">
			<h3>Аккредитив</h3>
			<p class="gray">
				<span id="credit"><?php echo printSum($summary['session']['credit']) ?></span>&nbsp;<?php echo $curr ?>
			</p>
		</div>
		<div class="col-xs-6 col-lg-6">
			<h3>Баланс</h3>
			<p class="gray">
				<span id="balance"><?php echo printSum($summary['session']['balance'], ($summary['position'] && !$summary['position']['fclose_time'] ? 2 : 0)) ?></span>&nbsp;<?php echo $curr ?>
			</p>
		</div>
		<div class="col-xs-6 col-lg-6">
			<h3>Позиция</h3>
			<p class="gray mb6">
				<span id="volume"><?php echo $summary['position'] ? ( ($summary['position']['type'] < 0 ? '&ndash; ' : '') . printSum($summary['position']['volume']) ) : '&mdash;' ?></span>&nbsp;<span><?php echo STP_VRS == 1 ? '$' : 'bbl' ?></span>
			</p>
			<small class="lightgrey"><?php echo $summary['position'] ? $summary['position']['fopen_time'] : '' ?></small>
		</div>
		<div class="col-xs-6 col-lg-6">
			<h3>Результат</h3>
			<p class="gray mb6">
				<span id="result"><?php echo $summary['position'] ? ( ($summary['position']['result'] < 0 ? '&ndash; ' : '+ ') . printSum(abs($summary['position']['result']), 2) ) : '&mdash;' ?></span>&nbsp;<?php echo $curr ?>
			</p>
			<small class="lightgrey"><?php echo $summary['position'] ? $summary['position']['fclose_time'] : '' ?></small>
		</div>		
	</div>
</div>

<script>
	$(function() {
		var i = 0;
		var tick = 0;
		var suffix = <?php echo crc32(time()) ?>;
		var chartData = eval('<?php echo json_encode($chartData) ?>');
		var graphUpdate = function() {
			if (i == 30) {
				$('.modal-cover').fadeOut();
				window.location.reload();
			}
			$('.chart-box:first p').children('.hidden').clone().on('load', function() {
				$(this).prependTo('.chart-box:first p').removeClass('hidden').next().remove();
				//$(this).parent().parent().attr({href: $(this).attr('src')});
				$('.img-xm').first().attr({src: chartData[0][tick]['xM'] + '&index=' + i + suffix});
				$('.img-xl').first().attr({src: chartData[0][tick]['xL'] + '&index=' + i + suffix});
			}).attr({src: chartData[0][tick]['xM'] + '&index=' + i + suffix});
			$('.chart-box:last p').children('.hidden').clone().on('load', function() {
				$(this).prependTo('.chart-box:last p').removeClass('hidden').next().remove();
				//$(this).parent().parent().attr({href: $(this).attr('src')});
				$('.img-xm').last().attr({src: chartData[1][tick]['xM'] + '&index=' + i + suffix});
				$('.img-xl').last().attr({src: chartData[1][tick]['xL'] + '&index=' + i + suffix});				
			}).attr({src: chartData[1][tick]['xM'] + '&index=' + i + suffix});
			window.setTimeout(graphUpdate, 29500);
			i++;
		};
		$('.nav-tabs a').click(function() {
			i = (i >= 15) ? 15 : i;
			tick = $(this).parent().index();
			graphUpdate();
			$(this).parent().addClass('active')
				   .siblings().removeClass('active');
			return false;
		});
		$('.chart-box').first().click(function() {
			$('.modal-cover').fadeIn(300, function() {
				$('#popup-candles-1').show();
				$('#popup-candles-1 .graph-outline').parent().css({width:'auto'});
				$('#popup-candles-1 .graph-outline').parent().width( $('#popup-candles-1 .graph-outline').parent().width() );				
			});
			return false;
		});
		$('.chart-box').last().click(function() {
			$('.modal-cover').fadeIn(300, function() {
				$('#popup-candles-2').show();
				$('#popup-candles-2 .graph-outline').parent().css({width:'auto'});
				$('#popup-candles-2 .graph-outline').parent().width( $('#popup-candles-2 .graph-outline').parent().width() );
			});
			return false;
		});
		$('.btn-plus').click(function() {
			$('.img-xm').addClass('hidden');
			$('.img-xl').removeClass('hidden');
			$(this).prop({disabled:true})
			       .next().prop({disabled:false});
		});
		$('.btn-minus').click(function() {
			$('.img-xl').addClass('hidden');
			$('.img-xm').removeClass('hidden');
			$(this).prop({disabled:true})
			       .prev().prop({disabled:false});
		});
		$('.btn-minus').prop({disabled:true});
		$('.spreadsheet .btn-close').click(function() {
			$('.img-xl').addClass('hidden');
			$('.img-xm').removeClass('hidden');
		});		
		graphUpdate();	
	});
</script>

<script>
	if (window.STP) {
		function formatSum(s) {
			var arr = s.toString().split('').reverse();
			var arr2 = [];
			var k = -1;
			for (var i = 0; i < arr.length; i++) {
				if (arr[i] == '.')
					k = -1;
				else
					k++;
				
				if (k > 2 && !(k%3))
					arr2.push(' ');
				arr2.push(arr[i]);
			}
			return arr2.reverse().join('');
		};
		$('.btn-buy').click(function(e) {
			e.preventDefault();
			STP.beforeBuy(function() {
				$.get('/trading/buy').done(function(resp) {
					if (resp == 1) {
						STP.dialog.showProc();
						window.setTimeout(function() {
							STP.dialog.close();
						}, 2300);
					} else {
						STP.dialog.showStatus('Произошла ошибка');
						console.log(resp);
					}
				});
			});
		});
		$('.btn-sell').click(function(e) {
			e.preventDefault();			
			STP.beforeSell(function() {
				$.get('/trading/sell').done(function(resp) {
					if (resp == 1) {
						STP.dialog.showProc();
						window.setTimeout(function() {
							STP.dialog.close();
						}, 2300);
					} else {
						STP.dialog.showStatus('Произошла ошибка');
						console.log(resp);
					}
				});
			});				
		});
		$('.goto').click(function(e) {
			$('#popup-text').find('div.small').html($(this).children('.hidden').html());
			e.preventDefault();
			STP.dialog.open('#popup-text');
		});			
		STP.onServerResponse = function(resp) {
			$('#quote-bid').text( (resp.quotes.bid).toFixed(2) );
			$('#quote-ask').text( (resp.quotes.ask).toFixed(2) );
			$('#quote-diff').text( Math.round(resp.quotes.diff * 100) / 100 );
			$('#credit').text( formatSum(resp.session.credit) );
			$('#balance').text( formatSum(resp.session.balance) );
			$('.btn-buy').prop('disabled', !resp.session.allowBuy);
			$('.btn-sell').prop('disabled', !resp.session.allowSell);

			if (resp.position == null || !resp.position.fopen_time) {
				$('#volume').html('&mdash;').parent().next().text('');
				$('#result').html('&mdash;').parent().next().text('');
				if ($('.m-position').length)
					$('.m-position').hide();
			} else {
				if (resp.position.fopen_time) {
					$('#volume').html( (resp.position.type < 0 ? '&ndash; ' : '') + formatSum(resp.position.volume))
								.parent().next().text(resp.position.fopen_time);
					$('#m-volume').html( (resp.position.type < 0 ? '&ndash; ' : '') + formatSum(resp.position.volume));
				}
				
				if (resp.position.fclose_time)
					$('#result').html( (resp.position.result < 0 ? '&ndash; ' : '+ ') + formatSum(Math.abs(resp.position.result))).parent().next().text(resp.position.fclose_time);
				else
					$('#result').html( (resp.position.result < 0 ? '&ndash; ' : '+ ') + formatSum(Math.abs(resp.position.result)))
								.parent().next().text('');	
				
				$('#m-result').html( (resp.position.result < 0 ? '&ndash; ' : '+ ') + formatSum(Math.abs(resp.position.result)));
			}
		};
	}
</script>