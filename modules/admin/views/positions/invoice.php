<div class="col-lg-12 col-md-12">
	<ul class="nav nav-tabs">
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/") ?>"><big>СПИСОК</big></a></li>
		<li><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/summary") ?>"><big>АНАЛИТИКА</big></a></li>
		<li class="active"><a href="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/invoice") ?>"><big>ФИНАНСЫ</big></a></li>
	</ul>
	<br/>
</div>

<div class="col-lg-12 col-md-12">
	<form class="row" action="/<?php echo yii\helpers\Url::to("{$this->context->module->id}/{$this->context->id}/invoice") ?>">
		<div class="col-lg-3 col-md-4">

			<div class="input-group">
				<input type="text" class="form-control input-lg" name="date" value="<?php if ($date) echo date('d.m.Y', strtotime($date)) ?>" placeholder="По дате">
				<span class="input-group-addon"><a href="" class="times">×</a></span>
			</div>
		</div><?php
		
		echo '
		<div class="col-lg-4 col-md-5">		
			<div class="input-group">
				<input type="text" placeholder="трейдер" class="autocomplete form-control input-lg" value="', (!$user ? '' : ( $user->last_name . ' ' . $user->first_name . rtrim(' '. $user->mid_name) )), '" />
				<div class="input-group-addon">';
					
					echo !$user ? '<span class="glyphicon glyphicon-search"></span>' : '<a href="" class="times">×</a>';
				
		echo '
				</div>
			</div>
			<input type="hidden" name="uid" value="', ($user ? $user->id : ''), '" />
		</div>';	
			
		?> 

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
			$("[name=date], .autocomplete").change(function() {
				$('form').first().submit();
			});
			
			$('.autocomplete').each(function() {
				$(this).autocomplete({
					source: '/secret/users/search',
					select: function(e, ui) {
						if (ui.item) {
							$('[name=uid]').val(ui.item.id);
							$('.autocomplete').val(ui.item.label).blur().change();
						}
						return false;
					}			
				});
			});
			$('.autocomplete').next().children().first().click(function() {
				$('[name=uid]').val('');
				$('form').first().submit();
				return false;
			});			
		</script>
			
	</form>
</div>

<div class="col-lg-12 col-md-12"></div>

<div class="col-lg-12 col-md-12">
	<div><?php
		
		if ($stat) {
			
			echo '
			<dl class="dl-horizontal tbl">
				<dt>Общий доход</dt>
				<dd>', \app\models\Position::formatSum($stat[0] + $stat[1]), ' ₽</dd>

				<dt>Всего к оплате</dt>
				<dd>', \app\models\Position::formatSum(array_sum($stat)), ' ₽</dd>
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

			if ($user) {
				$userStat = explode(',', $user->stat);
				echo '
				<dl class="dl-horizontal tbl">
					<dt>Сделки в текущем месяце</dt>
					<dd>', ($userStat[0] .'/'. $userStat[1]), ' (', ($userStat[1] ? round($userStat[0] / $userStat[1] * 100) : 0), '%)</dd>
				</dl>';
			}
		}

			
		?> 
	</div>
</div>