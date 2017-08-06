<div class="col-lg-12 col-md-12">
	<ul class="nav nav-tabs">
		<li class="active"><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>"><big>СДЕЛКИ</big></a></li>
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/invoice") ?>"><big>ФИНАНСЫ</big></a></li>
	</ul>
	<br/>
</div>

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

<div class="col-lg-6 col-md-6">
	<div style="margin-left:1em">
		<br/>
		<div id="range-selector"></div>
	</div>
</div>

<script>
	(function() {
		var timeOrigin = <?php echo $sessionTimes[0] - strtotime(date('Y-m-d')) + $this->params['dto'] ?>;
		var timeRangeMax = <?php echo $sessionTimes[1] - $sessionTimes[0] ?>;
		var rateToTime = function(perc) {
			var sec = (perc / 100 * timeRangeMax + timeOrigin);
			var min = Math.round( (sec/60)%60 );
			var h = Math.floor(sec/3600);
			if (min < 10)
				min = '0' + min;
			return h + ':' + min;
		};
		var timePoints = [];
		$('#range-selector').slider({
			range: true,
			min: 0,
			max: 100,
			step: 2,
			slide: function(e, ui) {
				$('.ui-slider-tooltip').filter(':eq(' + ui.handleIndex +')').text(rateToTime(ui.value));
			},
			change: function(e, ui) {
				$('.ui-slider-tooltip').filter(':eq(' + ui.handleIndex +')').text(rateToTime(ui.value));
				if (timePoints.length == 2) {
					timePoints[ui.handleIndex] = ui.value;
					if (window.Dialog)
						window.Dialog.showProc();
					$.get('<?php echo "/{$this->context->module->id}/{$this->context->id}/?date=$date" ?>&t1=' + timePoints[0] + '&t2=' + timePoints[1],
						{}, function (data) {
							$('#charts-wrapper').nextAll().remove();
							$('#charts-wrapper').replaceWith(data);
							if (window.Dialog)
								window.Dialog.close();
						}
					);
				} else
					timePoints[ui.handleIndex] = (ui.handleIndex == 0 ? 0 : 100);
			},
			create: function(e, ui) {
				var tooltip = $('<div class="text-center ui-slider-tooltip" />').css({
					position: 'absolute',
					padding: '1px 2px',
					width: '48px',
					fontSize: '110%',
					color: '#333',
					top: -26,
					left: -17
				});			
				$(this).slider('values', 0, 0);
				$(this).slider('values', 1, 100);
				$(e.target).find('.ui-slider-handle').append(tooltip);
				$('.ui-slider-tooltip').first().text(rateToTime(0));
				$('.ui-slider-tooltip').last().text(rateToTime(100));
				$('.ui-slider-tooltip').show();
			}
		});
	})();
</script>

<!--<div class="col-lg-12 col-md-12">
	<div>
		<label class="radio-inline"><input name="group" type="radio" checked="checked">Открытие</label>
		<label class="radio-inline"><input name="group" type="radio">Закрытие</label>
	</div>
</div>-->

<div id="charts-wrapper">
	<?php
	/*	
		$maxL = max($stat['LONG_QUOT'][0]);
		$maxS = max($stat['SHORT_QUOT'][0]);
		
		$ref = max($maxL, $maxS);
		
		$ticks = [];
		for ($i = ($ref - 0.4);  $i <= ($ref + 0.4); $i = ($i + 0.025))
			$ticks[] = number_format($i, 3);
	*/	
		if ($stat['COUNT']):
	?> 	
	<div class="col-lg-12 col-md-12">
		<h2>Открытие</h2>
	</div>
	<div class="col-lg-8 col-md-10">
		<div id="pos-chart0"></div>
	</div>

	<script>
		(function() {
			var data = {
				labels: ["<?php echo implode('", "', array_slice($times, 0, 300)) ?>"],
				series: [
					[<?php echo implode(', ', array_slice($stat['LONG_QUOT'][0], 0, 300)) ?>],
					[<?php echo implode(', ', array_slice($stat['SHORT_QUOT'][0], 0, 300)) ?>]
				]
			};		
			var options = {
				showLine: false,
				height: '400px',
				axisX: {
					//stretch: true,
					labelInterpolationFnc: function(value, index) {
						return !(index%60) ? value : null;
					}
				},
				axisY: {
					stretch: false,
					scaleMinSpace: 40,
					divisor: 5
				}			
			};
			
			var chart = new Chartist.Line('#pos-chart0', data, options, []);		
			chart.on('draw', function(data) {
				// If the draw event was triggered from drawing a point on the line chart
				if (data.type === 'point') {
					// We are creating a new path SVG element that draws a triangle around the point coordinates
					if (data.seriesIndex == 0)
						data.element.replace(
							new Chartist.Svg('path', {
								d: ['M',
								data.x,
								data.y - 10,
								'L',
								data.x - 10,
								data.y + 6,
								'L',
								data.x + 10,
								data.y + 6,
								'z'].join(' '),
								style: 'fill-opacity: 1; fill: green',
							}, 'ct-area')				
						);
					else
						data.element.replace(
							new Chartist.Svg('path', {
								d: ['M',
								data.x,
								data.y + 10,
								'L',
								data.x + 10,
								data.y - 6,
								'L',
								data.x - 10,
								data.y - 6,
								'z'].join(' '),
								style: 'fill-opacity: 1',
							}, 'ct-area')				
						);					
				}
			});	
		})();
	</script><?php
		
	endif;
		
	if ($stat['COUNT'] - $stat['OPEN']):
	
	?>
	<div class="col-lg-12 col-md-12">
		<h2>Закрытие</h2>
	</div>
	<div class="col-lg-8 col-md-10">
		<div id="pos-chart1"></div>
	</div>
	<script>
		(function() {		
			var data = {
				labels: ["<?php echo implode('", "', $times) ?>"],
				series: [
					[<?php echo implode(', ', $stat['SHORT_QUOT'][1]) ?>],
					[<?php echo implode(', ', $stat['LONG_QUOT'][1]) ?>]
				]
			};
			var options = {
				showLine: false,
				height: '400px',
				axisX: {
					labelInterpolationFnc: function(value, index) {
						return !(index%60) ? value : null;
					}
				},
				axisY: {
					stretch: false,
					scaleMinSpace: 40,
					divisor: 5
				}			
			};
			
			var chart = new Chartist.Line('#pos-chart1', data, options, []);		
			chart.on('draw', function(data) {
				// If the draw event was triggered from drawing a point on the line chart
				if (data.type === 'point') {
					// We are creating a new path SVG element that draws a triangle around the point coordinates
					if (data.seriesIndex == 0)
						data.element.replace(
							new Chartist.Svg('path', {
								d: ['M',
								data.x,
								data.y - 10,
								'L',
								data.x - 10,
								data.y + 6,
								'L',
								data.x + 10,
								data.y + 6,
								'z'].join(' '),
								style: 'fill-opacity: 1; fill: red',
							}, 'ct-area')				
						);
					else
						data.element.replace(
							new Chartist.Svg('path', {
								d: ['M',
								data.x,
								data.y + 10,
								'L',
								data.x + 10,
								data.y - 6,
								'L',
								data.x - 10,
								data.y - 6,
								'z'].join(' '),
								style: 'fill-opacity: 1; fill: green',
							}, 'ct-area')				
						);					
				}
			});
		})();
	</script>
	<?php
		endif
	?> 
</div>
	
	<div class="col-lg-12 col-md-12"></div>

	<div class="col-lg-6 col-md-6">
		<table class="table table-striped text-lead">
			<tbody><?php			
			
			echo '
				<tr>
					<td>Всего позиций</td>
					<td>', $stat['COUNT'], '</td>
					
					<td>&nbsp;&nbsp;</td>
					
					<td>Закрыто</td>
					<td>', $stat['COUNT'] - $stat['OPEN'], '</td>					

				</tr>
				
				<tr>
					<td>LONG</td>
					<td>', $stat['LONG'], ($stat['LONG'] ? " ({$stat['LONG_RATE']}%)": ''), '</td>
					
					<td>&nbsp;&nbsp;</td>
					
					<td>Убыток LONG</td>
					<td>', $stat['LONG_FAILURE'], ($stat['LONG'] ? " (".round($stat['LONG_FAILURE']/$stat['LONG'] * 100)."%)": ''), '</td>					
				</tr>

				<tr>
					<td>SHORT</td>
					<td>', $stat['SHORT'], ($stat['SHORT'] ? " ({$stat['SHORT_RATE']}%)": ''), '</td>					
					
					<td>&nbsp;&nbsp;</td>
					
					<td>Убыток SHORT</td>
					<td>', $stat['SHORT_FAILURE'], ($stat['SHORT'] ? " (".round($stat['SHORT_FAILURE']/$stat['SHORT'] * 100)."%)": ''), '</td>					
				</tr>';

				
			?> 
			</tbody>
		</table>
	</div>
<!--<script>
	/*$(':radio').click(function(e) {
		e.stopPropagation();
		if (this.checked) {
			$('#pos-chart' + $(this).parent().index()).parent().removeClass('hidden');
			$('#pos-chart' + (1 - $(this).parent().index())).parent().addClass('hidden');
		}	
	}).first().click();*/
</script>-->