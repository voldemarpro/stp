<?php
$formatter = \Yii::$app->formatter;
$debit = \Yii::$app->user->identity->debit;
$curr = STP_VRS == 1 ? '₽' : '$';

// форматирование номера сотакарты
$stpCardFormatted = \chunk_split(\Yii::$app->user->identity->sotacard, 4, ' ');
if (\strlen(\Yii::$app->user->identity->sotacard) == 17)
	$stpCardFormatted = \substr($stpCardFormatted, 0, \strlen($stpCardFormatted) - 3).\substr($stpCardFormatted, -2, 1);

// минимальная сумма на вывод
if ($debit >= \Yii::$app->params['payout_minimum'])
	$minStr = 'до ' .app\models\Position::formatSum( \floor(\Yii::$app->user->identity->debit < 10000 ? \Yii::$app->user->identity->debit : 10000) );
else	
	$minStr = 'от ' . \Yii::$app->params['payout_minimum'];
?>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<h1><?php echo \Yii::$app->thread->title ?></h1>
</div>
	
<div class="col-xs-12 col-lg-12">
	<ul class="text list-unstyled list-inline card-info">
		<li>
			<img src="/ui/sotacard.png" width="100" /><span></span>
		</li>
		<li>
			<b>SOTACARD</b><br/>
			<?php echo $stpCardFormatted ?>
		</li>
		<li>
			<?php echo number_format(\Yii::$app->user->identity->debit, 2, '.', ' ') . " $curr" ?><br/>
			<br class="hidden-xs" />
		</li>
	</ul>
</div>

<?php if (\Yii::$app->user->identity->tariff_id <= 1): ?>
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">	
	<h2><span>Заявка на <br class="visible-xs-inline-block" />перевод средств</span></h2>
	<form method="post" action="/<?php echo yii\helpers\Url::to("{$this->context->id}/post") ?>" role="form" id="f1">
		<div class="form-group row">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
				<div class="form-group">	
					<label for="f1_amount">Сумма</label>
					<input id="f1_amount" type="text" class="form-control input-lg" name="amount" value="" placeholder="" />
					<div class="form-control-feedback"></div>											
				</div>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
				<div class="form-group">
					<label for="f1_card">Счет-получатель</label>
					<select id="f1_card" class="form-control input-lg" name="aux">
						<option value="">Не выбран</option><?php
						
						foreach (app\models\MoneyTransfer::$recipients as $key=>$name) {
							if ($key > 1) {
								if ($key != 2 || !\Yii::$app->user->identity->deposit)
									continue;
							}	
							if ($key > 1 || (\Yii::$app->user->identity->deposit && $key == 1)) echo '
							<option value="', $key, '">', $name, '</option>';
						}

						?> 
					</select>
					<div class="form-control-feedback"></div>
				</div>
			</div>
			
			<input type="hidden" name="<?php echo \Yii::$app->getRequest()->csrfParam ?>" value="<?php echo \Yii::$app->getRequest()->getCsrfToken() ?>" />			
			
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<button type="submit" class="btn btn-md btn-primary">Отправить</button>										
			</div>
		</div>															
	</form>
</div>
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">	
	<br/><div class="note"><p><?php
	
	echo \str_replace(["\n\n", "\r\n\r\n", "\n", "\r\n"], ['</p><p>', '</p><p>', '<br/>', '<br/>'], \Yii::$app->params['req1_note']);
	
	?></p></div>	
</div>

<br/>

<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">	
	<h2><span>Заявка на <br class="visible-xs-inline-block" />увеличение счёта</span></h2>
	<form method="post" action="/<?php echo yii\helpers\Url::to("{$this->context->id}/post") ?>" role="form" id="f2">
		<div class="form-group row">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
				<div class="form-group">
					<label for="f1_amount">Сумма надбавки</label>
					<input id="f1_amount" type="text" class="form-control input-lg" name="amount" value="" />
					<div class="form-control-feedback"></div>								
				</div>
			</div>
			
			<input type="hidden" name="<?php echo \Yii::$app->getRequest()->csrfParam ?>" value="<?php echo \Yii::$app->getRequest()->getCsrfToken() ?>" />			
			
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<button type="submit" class="btn btn-md btn-primary">Отправить</button>										
			</div>
		</div>																	
	</form>
</div>
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">	
	<br/><div class="note"><p><?php
	
		echo \str_replace(["\n\n", "\r\n\r\n", "\n", "\r\n"], ['</p><p>', '</p><p>', '<br/>', '<br/>'], \Yii::$app->params['req2_note']);
	
	?></p></div>	
</div>

<?php
endif;

if (!empty($items))
{
	echo '
	<div class="col-xs-12 col-sm-12 col-md-12">
		<h2>Движение средств</h2>	
	</div>';
	
		foreach ($items as $it)
		{	
			echo '										
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
				<dl class="dl-horizontal item-frame">
					<dt>Дата</dt>
					<dd>', $formatter->asDate(date('Y-m-d', strtotime($it->{'date_time'}) + DTIME_OFFSET), "dd MMM ''YY"), '</dd>
					
					<dt>Сумма '.$curr.'</dt>
					<dd>', ('<span class="monosign">'.($it->sum >= 0 ? '+' : '-').'</span>'), number_format(abs($it->sum), 2, '.', ' '), '</dd>
					
					<dt><small>Назначение</small></dt>
					<dd><small>', app\models\MoneyTransfer::$grades[$it->grade], '</small></dd>
				</dl>
			</div>';
		
		}
}
?> 

<script>
	if (window.STP) {		
		$('form').find('input, select').bind('keyup change', function() {
			$(this).parents('.has-error').first().removeClass('has-error').removeClass('has-feedback');
			$(this).parent().find('.form-control-feedback').text('');			
		});
		$('form').submit(function(e) {
			var params = {};
			var formValid = true;
			var form = this;
			STP.dialog.stopEvent(e);
			$(this).find('.has-error').removeClass('has-error').removeClass('has-feedback');
			$(this).find('.form-control-feedback').text('');
			$(this).find('[type=text]').each(function() {
				params[this.name] = parseFloat(this.value);
				if (!params[this.name]) {
					formValid = false;
					$(this).next().text('Неправильная сумма').parent().addClass('has-error').addClass('has-feedback');
				}
			});
			$(this).find('select, [type=hidden]').each(function() {
				params[this.name] = this.value;
				if (params[this.name] == '' && $(this).is('select')) {
					formValid = false;
					$(this).next().text('Неправильный выбор').parent().addClass('has-error').addClass('has-feedback');
				}
			});
			if (formValid) {
				STP.dialog.showProc();
				$.post($(this).attr('action'), params)
				 .done(function(resp) {
					if (resp == 1) {
						STP.dialog.showStatus('Заявка отправлена');
						$(form).find('input').val('');
					} else {
						try {
							resp = $.parseJSON(resp);
							STP.dialog.close();
							for (var key in resp)
								$(form).find('[name=' + key + ']').next().text(resp[key].substr(0,25)).parent().addClass('has-error').addClass('has-feedback');
						} catch (e) {
							console.log(resp);
							STP.dialog.showStatus(resp ? resp : 'Произошла ошибка');
						}
					}
				 })
				 .fail(function() {
						STP.dialog.showStatus('Произошла ошибка');
				 });
			}
		});
	}
</script>