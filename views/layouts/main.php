<?php 
	$this->title = \Yii::$app->name . ' - '.(\Yii::$app->thread->title ? \Yii::$app->thread->title : $this->title);
	
	$summary = $this->params['summary'];
	$user = \Yii::$app->user->identity;
	$formatter = \Yii::$app->formatter;
	$dtz = new \DateTimeZone('Europe/Moscow');
	$date = new \DateTime('now', $dtz);

	// форматирование номера сотакарты
	$stpCardFormatted = \chunk_split($user->sotacard, 4, ' ');
	if (\strlen(\Yii::$app->user->identity->sotacard) == 17)
		$stpCardFormatted = \substr($stpCardFormatted, 0, \strlen($stpCardFormatted) - 3).\substr($stpCardFormatted, -2, 1);
	
	if (empty($summary['stat'])) {
		$profitabilityFormatted = '<span class="glyphicon glyphicon-credit-card icon-group"></span>';
		$profitabilityFormatted .= '<span class="icon-group">' . ($user->debit >= 0 ? '' : '&ndash; ') . round(abs($user->debit), 2) . ' <span class="glyphicon glyphicon-ruble"></span></span>';
	} else {
		$stat_perc = $summary['stat']['total'] ? round($summary['stat']['success'] / $summary['stat']['total'] * 100) : 0;
		$profitabilityFormatted = '<span class="glyphicon glyphicon-stats icon-group"></span>';
		$profitabilityFormatted .= '<span class="icon-group">' . $summary['stat']['success'] . ' <small class="gray">из</small> ' . $summary['stat']['total'] . " [$stat_perc%]" . '</span>';		
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		
		<meta name="description" content="" />
		<meta name="keywords" content="" />	
		
		<title><?php echo $this->title ?></title>
		
		<link rel="shortcut icon" href="/favicon.png" type="image/png" />
		<link rel="apple-touch-icon" href="/favicon.png" type="image/png" />

		<link rel="stylesheet" href="/styles/bootstrap.min.css" type="text/css" />
		<link rel="stylesheet" href="/styles/jquery-ui.min.css" type="text/css" />
		<link rel="stylesheet" href="/styles/stp.css?x=<?php echo filemtime(Yii::getAlias('@app/web/styles/stp.css')) ?>" type="text/css" /><?php

		?> 

		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;lang=en" />
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic|PT+Sans+Caption:400,700&subset=latin,cyrillic" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:200,400,700&subset=latin,cyrillic" type="text/css" />

		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script type="text/javascript" src="/js/jquery-ui.min.js?ru"></script>
		
		<!--[if lt IE 9]>
		<script type="text/javascript" src="/js/html5shiv.js"></script>
		<script type="text/javascript" src="/js/respond.js"></script>
		<![endif]-->
		<script>
			var config = {
				currency: '<?php echo STP_VRS == 1 ? '<span class="glyphicon glyphicon-ruble"></span>' : '$' ?>',
				timeOrigin: new Date('<?php echo \gmdate('Y-m-d\TH:i:sP') ?>'),
				timeOffset: <?php echo DTIME_OFFSET/60 ?>,
				allowTrade: <?php echo $summary['session']['allowTrade'] ?> 
			}
		</script>
		<script type="text/javascript" src="/js/stp.min.js?x=<?php echo filemtime(Yii::getAlias('@app/web/js/stp.js')) ?>"></script>

		<style>	
			.helper {
				position: fixed;
				top: 50%;
				right: -1px;
				margin-top: -30px;
				z-index: 10;
				box-shadow: 2px 5px 5px grey;
			}
			.helper * {
				color: white;
				font-weight: bold;
				text-transform: uppercase;
				font-size: 100%;
			}
			.helper big {
				font-size: 120%;
			}			
			.helper span {
				font-family: 'PT Sans', sans-serif;
			}
			@media (max-width: 760px) {
				.helper {
					position: fixed;
					width: 100%;
					top: 100%;
					margin-top: -40px;
					z-index: 10;
					box-shadow: 2px 5px 5px grey;
					opacity: 0.8;
				}
				.helper > .btn {
					width: 100%;
					text-align: center;
					border: none!important;
				}
				.helper > .btn > * {
					display: inline-block;
					vertical-align: middle;
				}					
			}			

			.ticket {
				margin-bottom: 24px;
			}
			.ticket > .item-header {
				font-weight: 600;
			}
			.ticket > .answer {
				text-align: right;
				font-size: 90%;
			}
			.ticket p {
				margin-bottom: 0;
			}		
			
			.icon-group {
				vertical-align: middle;
			}
			.glyphicon + .icon-group {
				margin: 0 0 0 10px;
			}

			.small > p:last-child {
				margin-bottom: 0;
			}
			
			.message {
				padding: 20px 30px 10px 20px;
				border: 1px solid #da7e0e;
				background: #f6e9da;
				position: relative;
			}
			.message * {	
				color: #a6671a;
			}
			.message > .close, .message > .close:focus  {
				position: absolute;
				top: 0.8em;
				right: 15px;
				line-height: 0.8;
				text-decoration: none;
				float: none;
				z-index: 1;
				font-size: 1.8em;
				opacity: 0.7;
			}
			.message > .close:hover {
				color: #9E9089;
				opacity: 1.0;
			}
			.message + div {	
				margin-top: 1em;
			}
			.message.hidden + div {	
				margin-top: 0;
			}
			
			.toggle {
				line-height: 1.5;
			}

			.stp-header {
				margin-bottom: 6px;
			}				
			.stp-header .h1 {
				margin: 0;
			}
			.stp-header p {
				line-height: 1.5;
			}
			.stp-header td > .grey {
				display: block;
			}
			
			.circle-green {
				color: #68b19a;
			}
			.circle-red {
				color: #b16868;
			}
			
			.content > div {
				margin-bottom: 1em;
			}
						
			.sota img {
				width: 2em;
			}
			.sota > *, #logout > span {
				display: inline-block;
				vertical-align: middle;
			}
			
			.nav > li > a {
				padding: 8px 12px;
			}
			
			.graph-label {
				position: absolute;
				width: 6em;
				text-align: center;
				display: block;
				padding: 4px 6px;
				background: #f3f4f4;
				border: 1px solid lightgray;
			}
			 
			.inactive {
				opacity: 0.6;
				filter: alpha(opacity=60);
			}

			span.glyphicon-ruble {
				font-size: 75%;
			}
			
			#logout {
				line-height: 20px;
			}
			#logout:hover {
				opacity: 0.8;
				filter: alpha(opacity=80);
			}
		</style>
	</head>

	<body>
		<div class="container-fluid">
			<div class="row mobile">
				<div class="col-xs-4 col-sm-4 col-md-2 col-lg-2">
					<ul class="text-right list-unstyled list-inline">
						<li class="icon"><a href="http://<?php echo \str_replace(['stp.', 'stp1.', 'stp2.'], '', $_SERVER['HTTP_HOST']) ?>" title="На главную"><img src="/ui/home.png" /><img src="/ui/home-over.png" /></a></li>
					</ul>
				</div>				
				
				<div class="col-xs-4 col-sm-4 col-md-8 col-lg-8"></div>
				
				<div class="col-xs-4 col-sm-4 col-md-2 col-lg-2">
					<ul class="text-left list-unstyled list-inline opener">
						<li class="icon"><a href="/" title="Разделы"><img src="/ui/menu.png" /><img src="/ui/menu-over.png" /></a></li>
					</ul>
				</div>				
			</div>
		</div> 
		
		<div class="container">
			<div class="row section">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">					

					<div class="row stp-header"><?php
						
						echo '
						<div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
							<div class="sota"> 
								<a href="/"><img class="logo" src="/ui/logo.png" alt="SOTA (STP)" />
								<span style="color: black; font-size: 110%">SOTA</span></a>
							</div><span></span>
						</div>';

						$circleClass = 'circle-green';
						if (time() >= \Yii::$app->params['close_time'] || time() < \Yii::$app->params['open_time'])
							$circleClass = 'circle-red';
						
						echo '
						<div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
							<div class="text">
								<span class="lightgrey">Сегодня</span> ', $formatter->asDate(date('Y-m-d', time() + 5*60*60), 'dd MMMM'), '</i><br/>
								
								<big id="stp-time">', $date->format('H : i : s'), ' МСК</big>
								<span class="circle ', $circleClass, '">&#x25CF;</span>
							</div>
						</div>';
					
						echo '
						<div class="hidden-xs col-sm-4 col-md-3 col-lg-3">
							<p>
								<span class="grey">', $stpCardFormatted, '</span><br/>
								', $profitabilityFormatted, '<br/>
							</p>
						</div>';
					
						echo '
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="text auth">
								<div>
									<span class="text-uppercase">', ($user->last_name && $user->first_name) ? ($user->last_name.' '.mb_substr($user->first_name, 0, 1, 'utf-8').'. ' . ($user->mid_name ? mb_substr($user->mid_name, 0, 1, 'utf-8').'.' : '') ) : 'Без имени', '</span>
								</div>
								<a id="logout" class="grey" href="/logout"><img src="/ui/logout.png" /> <span class="hidden-lg hidden-md hidden-sm">Выйти</span><span class="hidden-xs">Выйти из системы</span></a>
							</div>
						</div>';

					?> 
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">				
							<ul class="text-center list-unstyled list-inline menu"><?php

							foreach (\Yii::$app->thread->items as $_id=>$_it)
								if ($_it['active'] == 1)
									echo '
									<li', ($_id == \Yii::$app->thread->id ? ' class="active"' : ''), '>
										<a href="', ($_it['vname'] ? "/{$_it['vname']}/" : $_it['redirect']), '">', ($_it['name'] ? $_it['name'] : '<span class="glyphicon glyphicon-'.$_it['icon'].'"></span>'), '</a>
									</li>';						
							
							
							?> 							
							</ul>
						</div>
					</div> 	 
					
					<div class="row content"><?php

						if ($notices = $user->getNotices()->all()) {
							echo '
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 notices">';							
							
							foreach ($notices as $not) {
								echo '
								<div class="message">
									<a name="id', $not->id, '" href="" class="close">×</a>
									<p>', $not->message, '</p>
								</div>'	;					
							}
							
							echo '
							</div>';
						
						} else {
							echo '
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 hidden notices"></div>';						
						}
						
						echo $content;
							 
						?> 	
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<hr style="color:#8c949a; border-width: 3px" />
							<p class="text-center small">© <?php echo \Yii::$app->params['company_name'] ?></p>
						</div>							
					</div>
					
					<div class="helper">
						<button class="btn btn-success" id="open-chat">
							<p class="text-center mb0"><big style="color:white" class="glyphicon glyphicon-comment"></big></p>
							<span>Поддержка</span>
						</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="message hidden">
			<a name="" href="" class="close">×</a>
			<p></p>
		</div>		
		
		<div class="modal-cover">
			<div class="modal-wrapper">
				<div class="modal-cell">

					<div class="modal-box" id="popup-status">
						<div class="group clearfix">
							<h3 class="text-uppercase">&nbsp;</h3><a href="" class="close">&times;</a>
						</div>
						<div class="group">
							<p class="text-center"></p>
						</div>
						<div class="group ctrl text-center">
							<button class="btn btn-primary btn-md btn-close">&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;</button>
						</div>
					</div>
					
					<div class="modal-box" id="popup-buy">
						<div class="group clearfix">
							<h3 class="text-uppercase">&nbsp;</h3><a href="" class="close">&times;</a>
						</div>
						<div class="group">
							<p class="text-center">Вы уверены, что хотите купить?</p>
						</div>
						<div class="group ctrl text-center">
							<button class="btn btn-md btn-buy btn-yes">&nbsp;&nbsp;&nbsp;Да&nbsp;&nbsp;&nbsp;</button>
							<button class="btn btn-default btn-md btn-close">&nbsp;&nbsp;Нет&nbsp;&nbsp;</button>
						</div>
					</div>
					
					<div class="modal-box" id="popup-sell">
						<div class="group clearfix">
							<h3 class="text-uppercase">&nbsp;</h3><a href="" class="close">&times;</a>
						</div>
						<div class="group">
							<p class="text-center">Вы уверены, что хотите продать?</p>
						</div>
						<div class="group ctrl text-center">
							<button class="btn btn-md btn-sell btn-yes">&nbsp;&nbsp;&nbsp;Да&nbsp;&nbsp;&nbsp;</button>
							<button class="btn btn-default btn-md btn-close">&nbsp;&nbsp;Нет&nbsp;&nbsp;</button>
						</div>
					</div>
					
					<div class="modal-box graph-box" id="popup-proc">
						<div class="text-center">
							<img src="/ui/delay1.gif" />
						</div>
					</div>
					
					<div class="modal-box text-box" id="popup-text">
						<div class="group clearfix">
							<h3 class="text-uppercase">Новости</h3>
							<a href="" class="close">&times;</a>
						</div>
						<br/>
						
						<div class="small"></div>
						
						<br/>
						
						<div class="group ctrl text-center">
							<button class="btn btn-primary btn-md btn-close">&nbsp;Закрыть&nbsp;</button>
						</div>
					</div>
					
					<div class="modal-box graph-box" id="popup-menu">
						<div class="text-uppercase"><?php

							echo '					
							<ul class="text-center list-unstyled list-inline menu">';

							foreach (\Yii::$app->thread->items as $_id=>$_it)
								if ($_it['active'] == 1)
									echo '
									<li', ($_id == \Yii::$app->thread->id ? ' class="active"' : ''), '>
										<a href="', ($_it['vname'] ? "/{$_it['vname']}/" : $_it['redirect']), '">', ($_it['name'] ? $_it['name'] : '<span class="glyphicon glyphicon-'.$_it['icon'].'"></span>'), '</a>
									</li>';						
							
							echo '
								<li style="margin-top:2.5em"><a href="#">Закрыть</a></li>';							
							
							echo '
							</ul>';					

							?> 
						</div>
					</div>	
					
					<div class="modal-box" id="popup-chat">
						<div class="group clearfix">
							<h3 class="text-uppercase">Обращения</h3><a href="" class="close">&times;</a>
						</div>
						
						<br/>
						
						<div>
							<div class="ticket hidden">
								<div class="item-header"></div>
								<span class="lightgray lightgrey"></span>
								<div class="answer">
									<p></p>
									<span class="lightgrey"></span>
								</div>
							</div>
						</div>

						<form action="">
							<div class="form-group">
								<textarea rows="3" class="form-control" placeholder="Новое обращение"></textarea>
								<div class="form-control-feedback"></div>
							</div>
							<div class="group ctrl text-right">
								<button type="submit" class="btn btn-md btn-success">Отправить</button>
								<button class="btn btn-md btn-default btn-close">Закрыть</button>
							</div>
						</form>
					</div>
					
					<div class="modal-box" id="popup-number">
						<div class="group clearfix">
							<h3 class="text-uppercase">Проверка номера</h3><a href="" class="close">&times;</a>
						</div>
						<br/>
						<form action="" role="form">	
							<div class="form-group">
								<p>Код подтверждения</p>
								<input id="sms_crc" type="text" class="form-control" name="sms_crc" value="" />
								<div class="form-control-feedback"></div>
							</div>
							
							<div class="group ctrl">
								<button class="btn btn-success btn-md">Далее</button>
								<button class="btn btn-default btn-md btn-close">Отмена</button>
							</div>
						</form>
					</div>

					<div class="modal-box graph-box spreadsheet" id="popup-candles-1">
						<div>					
							<div class="graph-outline">
								<img src="//:0" class="img-xm" />
								<img src="//:0" class="img-xl hidden" />
							</div>
							
							<p class="text-center">USD / RUB</p>
							
							<div style="max-width:320px; margin: 0 auto">
								<button class="btn btn-cover btn-plus">
									<span class="icon-group glyphicon glyphicon-plus-sign"></span>
									<span>увеличить</span>
								</button>
								
								<button class="btn btn-cover btn-minus pull-right">
									<span class="icon-group glyphicon glyphicon-minus-sign"></span>
									<span>уменьшить</span>
								</button>
							</div>

							<div style="max-width:320px; margin: 0 auto">
								<button class="btn btn-cover btn-block btn-close">
									<span class="icon-group glyphicon glyphicon-remove-circle"></span>
									<span>закрыть</span>
								</button>
							</div>
						</div>
					</div>

					<div class="modal-box graph-box spreadsheet" id="popup-candles-2">
						<div>				
							<div class="graph-outline">
								<img src="//:0" class="img-xm" />
								<img src="//:0" class="img-xl hidden" />
							</div>
							
							<p class="text-center">BRENT / USD</p>
							
							<div style="max-width:320px; margin: 0 auto">
								<button class="btn btn-cover btn-plus">
									<span class="icon-group glyphicon glyphicon-plus-sign"></span>
									<span>увеличить</span>
								</button>
								
								<button class="btn btn-cover btn-minus pull-right">
									<span class="icon-group glyphicon glyphicon-minus-sign"></span>
									<span>уменьшить</span>
								</button>
							</div>

							<div style="max-width:320px; margin: 0 auto">
								<button class="btn btn-cover btn-block btn-close">
									<span class="icon-group glyphicon glyphicon-remove-circle"></span>
									<span>закрыть</span>
								</button>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		
		<script>
			(function() {
				var getContentHeight = function() {
					var sumH = 0;
					$('.section').children().children().each(function(i) {
						if (i != 2 && $(this).is(':visible'))
							sumH += $(this).outerHeight(true);
					});
					return $(window).height() - 110 - sumH - ($(window).width() <= 991 ? 40 : 0);
				};		
				if ($(window).height() > 600)
					$('.content').css({minHeight: (getContentHeight() + 'px')});
				$(window).resize(function() {
					if ($(window).width() > 991)
						$('#popup-menu a').last().click();
					if ($(window).height() > 600)
						$('.content').css({minHeight: (getContentHeight() + 'px')});
				});
				$('#popup-menu a').last().click(function() {
					$('#popup-menu').hide();
					$('.modal-cover').fadeOut(300);
					return false;
				});			
				$('#open-chat').click(function() {
					$('.modal-cover').fadeIn(300, function() {
						$('#popup-chat').show()
					});
					return false;
				});
				$('.opener').click(function() {
					$('.modal-cover').fadeIn(300, function() {
						$('#popup-menu').show()
					});
					return false;
				});					
				$('.btn-close, .close').click(function() {
					$('.modal-box').hide();
					$('.modal-cover').fadeOut(300);
					return false;
				});				
			})();
		</script>
		<script>
			var _0xf2ca=['ready','.ticket.hidden','siblings','filter','.ticket','remove','clone','children','text','message','.lightgray','date_time','updated','.answer','first','response','next','removeClass','hidden','prepend','setInterval','getJSON','/support/list','length','clearInterval','#popup-chat\x20:submit','click','parent','find','has-error','.form-control-feedback','textarea',':hidden','val','/support/send','.form-group','addClass','has-feedback','У\x20вас\x20есть\x20незакрытое\x20обращение'];var _0xaf2c=function(_0x1c06f4,_0x2feba6){_0x1c06f4=_0x1c06f4-0x0;var _0x1a727e=_0xf2ca[_0x1c06f4];return _0x1a727e;};$(document)[_0xaf2c('0x0')](function(){var _0x3290a5=![];var _0x46da33=![];var _0x26a8a7=function(_0x598667){$(_0xaf2c('0x1'))[_0xaf2c('0x2')]()[_0xaf2c('0x3')](_0xaf2c('0x4'))[_0xaf2c('0x5')]();for(var _0x10c910=0x0;_0x10c910<_0x598667['length'];_0x10c910++){var _0x1fd101=$(_0xaf2c('0x1'))[_0xaf2c('0x6')]();_0x1fd101[_0xaf2c('0x7')]('.item-header')[_0xaf2c('0x8')](_0x598667[_0x10c910][_0xaf2c('0x9')]);_0x1fd101[_0xaf2c('0x7')](_0xaf2c('0xa'))[_0xaf2c('0x8')](_0x598667[_0x10c910][_0xaf2c('0xb')]);if(_0x598667[_0x10c910][_0xaf2c('0xc')]!=null){_0x1fd101[_0xaf2c('0x7')](_0xaf2c('0xd'))[_0xaf2c('0x7')]()[_0xaf2c('0xe')]()['text'](_0x598667[_0x10c910][_0xaf2c('0xf')])[_0xaf2c('0x10')]()[_0xaf2c('0x8')](_0x598667[_0x10c910][_0xaf2c('0xc')]);}else if(!_0x3290a5)_0x3290a5=!![];_0x1fd101[_0xaf2c('0x11')](_0xaf2c('0x12'));$('.ticket.hidden')['parent']()[_0xaf2c('0x13')](_0x1fd101);}if(_0x3290a5&&!_0x46da33){_0x46da33=window[_0xaf2c('0x14')](function(){$[_0xaf2c('0x15')](_0xaf2c('0x16'),{},function(_0x293ca6){if(_0x293ca6&&_0x293ca6[_0xaf2c('0x17')]){_0x26a8a7(_0x293ca6);}});},0x3*0xe484);_0x3290a5=![];}else if(!_0x3290a5&&_0x46da33)window[_0xaf2c('0x18')](_0x46da33);};$[_0xaf2c('0x15')](_0xaf2c('0x16'),{},function(_0x1d0633){if(_0x1d0633&&_0x1d0633[_0xaf2c('0x17')]){_0x26a8a7(_0x1d0633);};});$(_0xaf2c('0x19'))[_0xaf2c('0x1a')](function(){var _0x3432e4=$(this)[_0xaf2c('0x1b')]()['parent']();_0x3432e4[_0xaf2c('0x1c')]('.has-error')[_0xaf2c('0x11')](_0xaf2c('0x1d'))[_0xaf2c('0x11')]('has-feedback');_0x3432e4[_0xaf2c('0x1c')](_0xaf2c('0x1e'))[_0xaf2c('0x8')]('');var _0x20000b=_0x3432e4[_0xaf2c('0x1c')](_0xaf2c('0x1f'))['val']();var _0x35e712=_0x3432e4[_0xaf2c('0x1c')](_0xaf2c('0x20'))[_0xaf2c('0x21')]();if(_0x20000b[_0xaf2c('0x17')]){$[_0xaf2c('0x15')](_0xaf2c('0x22'),{'message':_0x20000b},function(_0xbf6f62){if(_0xbf6f62[_0xaf2c('0x17')]){_0x3432e4[_0xaf2c('0x1c')]('textarea')[_0xaf2c('0x21')]('');_0x26a8a7(_0xbf6f62);}else if(_0xbf6f62==-0x1){_0x3432e4[_0xaf2c('0x1c')](_0xaf2c('0x23'))[_0xaf2c('0xe')]()['addClass'](_0xaf2c('0x1d'))[_0xaf2c('0x24')](_0xaf2c('0x25'))[_0xaf2c('0x7')](_0xaf2c('0x1e'))[_0xaf2c('0x8')](_0xaf2c('0x26'));}});}return![];});});
		</script>
	</body>
</html>