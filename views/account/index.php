<?php
$dtz = new \DateTimeZone('Europe/Moscow');
$dt = new \DateTime('now', $dtz);
$dto = $dt->getOffset();
$formatter = \Yii::$app->formatter;
$debit = \Yii::$app->user->identity->debit;

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
	
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
	<ul class="text list-unstyled list-inline card-info">
		<li>
			<img src="/ui/sotacard.png" width="100" /><span></span>
		</li>
		<li>
			<b>SOTACARD</b><br/>
			<?php echo $stpCardFormatted ?>
		</li>
		<li>
			<?php echo number_format(\Yii::$app->user->identity->debit, 2, '.', ' ') ?> RUB<br/><br class="hidden-xs" />
		</li>
	</ul>
	
	<h2><span>Заявка на <br class="visible-xs-inline-block" />перевод средств</span><a class="help-icon" href=""></a></h2>
	<form method="post" action="/<?php echo yii\helpers\Url::to("{$this->context->id}/post") ?>" role="form" id="f1">
		
		<div class="form-group row">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
				<div class="form-group">	
					<label for="f1_amount">Сумма</label>
					<input id="f1_amount" type="text" class="form-control input-lg" name="amount" value="" placeholder="<?php echo $minStr . ' руб' ?>" />
					<div class="form-control-feedback"></div>											
				</div>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
				<div class="form-group">
					<label for="f1_card">Счет-получатель</label>
					<select id="f1_card" class="form-control input-lg" name="aux">
						<option value="">Не выбран</option><?php
						
						foreach (app\models\Payout::$types as $key=>$name) {
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
	
	<br/>
	
	<h2><span>Заявка на <br class="visible-xs-inline-block" />увеличение счёта</span><a class="help-icon" href=""></a></h2>
	<form method="post" action="/<?php echo yii\helpers\Url::to("{$this->context->id}/post") ?>" role="form" id="f2">
		<div class="form-group row">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
				<div class="form-group">
					<label for="f1_amount">Сумма надбавки</label>
					<input id="f1_amount" type="text" class="form-control input-lg" name="amount" value="" placeholder="до <?php echo number_format(round(\Yii::$app->user->identity->credit/10000) * 10000, 0, '.', ' ') ?> руб" />
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

<?php

if (!empty($items))
{
	echo '
	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-5">
		<h2>История движения средств</h2>
		<div class="table-responsive">	
			<table class="table table-hover table-striped">
				<thead>
					<tr>
						<th width="130">Дата</th>
						<th width="130">Сумма <small class="glyphicon glyphicon-rub"></small></th>
						<th>Счет</th>
					</tr>
				</thead>
				<tbody>';
	
				foreach ($items as $it)
				{	
					echo '										
					<tr>
						<td>', $formatter->asDate(date('Y-m-d', strtotime($it->{'date'}) + $this->params['dto']), "dd MMM ''YY"), '</td>
						<td>', app\models\Position::formatSign($it->sum), number_format(abs($it->sum), 2, '.', ' '), '</td>
						<td>', app\models\Payout::$types[$it->type], '</td>
					</tr>';
				
				}
	
	echo '
				</tbody>
			</table>
		</div>
	</div>';
}
?> 

<div class="help-note hidden">
	<p><?php
	
	echo \str_replace(["\n\n", "\r\n\r\n", "\n", "\r\n"], ['</p><p>', '</p><p>', '<br/>', '<br/>'], \Yii::$app->params['req1_note']);
	
	?></p>
</div>

<div class="help-note hidden">
	<p><?php
	
	echo \str_replace(["\n\n", "\r\n\r\n", "\n", "\r\n"], ['</p><p>', '</p><p>', '<br/>', '<br/>'], \Yii::$app->params['req2_note']);
	
	?></p>	
</div>


<script type="text/javascript" src="/js/tipped.js"></script>
<script>
	if (window.STP) {			
		$('.help-icon').each(function(index) {
			var span = $('.help-note').filter(':eq(' + index + ')');
			$(this).bind('click', function(e) {
				e.stopPropagation();
				if (e.type == 'click')
					e.preventDefault();
			});
			window.Tipped.create(this, span.html(), {
				skin: 'white',
				maxWidth: ($(window).width() > 768 ? 460 : 360),
				position: ($(window).width() > 768 ? 'right' : 'top'),
				close: true
			});
		});
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
					//$('form').find(':submit').prop({disabled:true});
				 })
				 .fail(function() {
						STP.dialog.showStatus('Произошла ошибка');
				 });
			}
		});
	}
</script>