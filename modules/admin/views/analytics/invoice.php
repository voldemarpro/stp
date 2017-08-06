<div class="col-lg-12 col-md-12">
	<ul class="nav nav-tabs">
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>"><big>СДЕЛКИ</big></a></li>
		<li class="active"><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/invoice") ?>"><big>ФИНАНСЫ</big></a></li>
	</ul>
	<br/>
</div>

<div class="col-lg-2 col-md-3">
	
	<form action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/invoice") ?>">
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

<div class="col-lg-12 col-md-12"></div>

<div class="col-lg-12 col-md-12">
	<div><?php
		
		if ($stat) {
			
			echo '
			<dl class="dl-horizontal tbl">
				<dt>Общий доход</dt>
				<dd>', \app\models\Position::formatSum(array_sum($stat)), ' ₽</dd>

				<dt>Всего к оплате</dt>
				<dd>', \app\models\Position::formatSum(array_sum($stat) + $stat[2] + $stat[3]), ' ₽</dd>
			</dl>';
			
			echo '
			<dl class="dl-horizontal tbl">
				<dt>Доход простых трейдеров</dt>
				<dd>', \app\models\Position::formatSum($stat[0]), ' ₽</dd>

				<dt>К оплате</dt>
				<dd>', \app\models\Position::formatSum($stat[0] + $stat[2]), ' ₽</dd>
			</dl>';

			echo '
			<dl class="dl-horizontal tbl">
				<dt>Доход премиум-трейдеров</dt>
				<dd>', \app\models\Position::formatSum($stat[1]), ' ₽</dd>

				<dt>К оплате</dt>
				<dd>', \app\models\Position::formatSum($stat[1] + $stat[3]), ' ₽</dd>
			</dl>';		
				
		}

			
		?> 
	</div>
</div>