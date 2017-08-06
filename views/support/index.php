<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

	<h1><?php echo \Yii::$app->thread->title ?></h1>

	<div class="row clearfix">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<h2>История обращений</h2>
			</div>
		<?php

		if (!empty($items) && count($items))
		{
			echo '
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">';

				foreach ($items as $it)
				{	
					echo '
					<br/>
					<p>
						<small class="darkblue">', $this->params['formatter']->asDate(date('Y-m-d H:i:s', strtotime($it->{'date_time'}) + $this->params['dto']), "dd MMMM в HH:mm"), '</small><br/>
						', $it['message'], '
						', ($it['response']
							? '<br/><small style="padding-left:10px"><span class="lightgrey">Ответ '.$this->params['formatter']->asDate(date('Y-m-d H:i:s', strtotime($it->updated) + $this->params['dto']), "dd MMMM в HH:mm").' </span>'.$it['response'].'</small><br/>'
						: '<br/>'), '
					</p>';
				}
									
			echo '
			</div>';
			
		}
		
		?> 
		
		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
			<br/>
			<form role="form" id="f1" action="/<?php echo yii\helpers\Url::to("{$this->context->id}/post") ?>">
				
				<input type="hidden" name="<?php echo \Yii::$app->getRequest()->csrfParam ?>" value="<?php echo \Yii::$app->getRequest()->getCsrfToken() ?>" />
				
				<div class="form-group">
					<textarea rows="5" type="text" class="text form-control input-lg" name="message" placeholder="Новое обращение"></textarea>
					<div class="form-control-feedback"></div>
				</div>											
				
				<div class="form-group">
					<button type="submit" class="btn btn-md btn-primary">Отправить</button>										
				</div>
				
			</form>
		</div>
		
	</div>
</div>

<script>
	if (window.STP) {
		$('textarea').bind('keyup', function() {
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
			
			$(this).find('textarea, [type=hidden]').each(function() {
				if (!$.trim(this.value)) {
					formValid = false;
					$(this).parents('.form-group').addClass('has-error').addClass('has-feedback').find('.form-control-feedback').text('Обязательное поле');
				
				} else {	
					params[this.name] = $.trim(this.value);
				}
			});

			if (formValid) {
				STP.dialog.showProc();
				$.post($(this).attr('action'), params)
				 .done(function(resp) {
					try {
						resp = $.parseJSON(resp);
						if (resp.error) {
							STP.dialog.showStatus(resp.error);
							$(form).find('textarea').val('');
						} else {
							for (var key in resp)
								$(form).find('[name=' + key + ']').parents('.form-group').addClass('has-error').addClass('has-feedback').find('.form-control-feedback').text(resp[key].substr(0,25));
							STP.dialog.close();
						}
					} catch (e) {
						if (resp) {
							$('.content').html(resp);
							STP.dialog.showStatus('Сообщение отправлено');
						} else {
							STP.dialog.showStatus($.trim(resp) ? resp : 'Произошла ошибка');
							$(form).find(':submit').prop({disabled: true});
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