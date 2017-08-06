<div class="col-lg-12 col-md-12">
	<ul class="nav nav-tabs">
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>"><big>СПИСОК</big></a></li>
		<li class="active"><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/summary") ?>"><big>АНАЛИТИКА</big></a></li>
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/invoice") ?>"><big>ФИНАНСЫ</big></a></li>
	</ul>
	<br/>
</div>
	
<div class="col-lg-7 col-md-7 col-sm-12">
	<form class="form-inline" action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/summary/") ?>">
		<div class="form-group">
			<div class="input-group">
				<input type="text" class="form-control input-lg" name="date_from" value="<?php if ($date_from) echo date('d.m.Y', strtotime($date_from)) ?>" placeholder="От даты">
				<span class="input-group-addon"><a href="" class="times">×</a></span>
			</div>
		</div>
		<div class="form-group">
			<span>
				&nbsp;&mdash;&nbsp;
			</span>
		</div>		
		<div class="form-group">
			<div class="input-group">
				<input type="text" class="form-control input-lg" name="date_to" value="<?php if ($date_to) echo date('d.m.Y', strtotime($date_to)) ?>" placeholder="На дату">
				<span class="input-group-addon"><a href="" class="times">×</a></span>
			</div>
		</div>
		
		<script>
			$("[name^=date]").datepicker({
				hideIfNoPrevNext: true,
				changeYear: true,
				changeMonth: true,
				showWeek: false,
				firstDay: 1,
				yearRange: "<?php echo date('Y'),':',(date('Y') + 1) ?>",
				dateFormat: "dd.mm.yy"
			});
			$("[name^=date]").next().click(function() {
				return $(this).prev().val('').change() && false;
			});
			$("[name^=date]").change(function() {
				$('form').first().submit();
			});
		</script>	
	</form>
</div>

<div class="col-lg-5 col-md-5"></div>


<div id="charts-wrapper">
	<?php
		if ($stat['COUNT']):
	
	?> 	
	<div class="col-lg-12 col-md-12">
		<h2>Открытие</h2>
	</div>
	<div class="col-lg-11 col-md-11">
		<div id="pos-chart0"></div>
	</div>

	<script>
		(function() {
			var data = {
				labels: ["<?php echo implode('", "', $times) ?>"],
				series: [
					[<?php echo implode(', ', $stat['LONG_QUOT'][0]) ?>],
					[<?php echo implode(', ', $stat['SHORT_QUOT'][0]) ?>]
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
								data.y - 6,
								'L',
								data.x - 6,
								data.y + 3,
								'L',
								data.x + 6,
								data.y + 3,
								'z'].join(' '),
								style: 'fill-opacity: 1; fill: green',
							}, 'ct-area')				
						);
					else
						data.element.replace(
							new Chartist.Svg('path', {
								d: ['M',
								data.x,
								data.y + 6,
								'L',
								data.x + 6,
								data.y - 3,
								'L',
								data.x - 6,
								data.y - 3,
								'z'].join(' '),
								style: 'fill-opacity: 1',
							}, 'ct-area')				
						);					
				}
			});	
		})();
	</script><?php
		
	echo '
	<div class="col-lg-1 col-md-1">
		<div class="pull-left" style="height:100px; background:#4db973">
			<div style="height:'.(100 - $stat['LONG_RATE']).'px; width: 20px; background: white"></div></td>
		</div>
		<div class="pull-right" style="height:100px; background:red">
			<div style="height:'.(100 - $stat['SHORT_RATE']).'px; width: 20px; background: white"></div>
		</div>		
	</div>';
	
	endif;
		
	if ($stat['COUNT'] - $stat['OPEN']):
	
	?>
	<div class="col-lg-12 col-md-12">
		<h2>Закрытие</h2>
	</div>
	<div class="col-lg-11 col-md-11">
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
								data.y - 6,
								'L',
								data.x - 6,
								data.y + 3,
								'L',
								data.x + 6,
								data.y + 3,
								'z'].join(' '),
								style: 'fill-opacity: 1; fill: red',
							}, 'ct-area')				
						);
					else
						data.element.replace(
							new Chartist.Svg('path', {
								d: ['M',
								data.x,
								data.y + 6,
								'L',
								data.x + 6,
								data.y - 3,
								'L',
								data.x - 6,
								data.y - 3,
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

				</tr>';
		
			?> 
			</tbody>
		</table>
	</div>