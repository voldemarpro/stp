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
		$profitabilityFormatted .= '<span class="icon-group">' . ($user->debit >= 0 ? '' : '&ndash; ') . number_format(abs($user->debit), 0, '.', ' ') . ' р</span>';
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
		<link rel="stylesheet" href="//sotabank.com/styles/common.css" type="text/css" /><?php

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
				timeOrigin: new Date('<?php echo \gmdate('Y-m-d\TH:i:sP') ?>'),
				timeOffset: <?php echo DTIME_OFFSET/60 ?>,
				allowTrade: <?php echo $summary['session']['allowTrade'] ?> 
			}
		</script>
		<script type="text/javascript" src="/js/stp.js?x=<?php echo filemtime(Yii::getAlias('@app/web/js/stp.js')) ?>"></script>

		<style>	
			input, select, button, textarea {
				border-radius: 2px!important;
				-moz-border-radius: 2px!important;
			}
			textarea.input-lg, .input-lg {
				font-size: 18px!important;
			}				
			
			h2 {
				font-size: 1.4em;
				font-weight: 400;
				text-transform: uppercase;		
			}
			h2 > span {
				display: inline-block;
				vertical-align: middle;				
			}
			h3 {
				font-family: 'PT Sans Caption';
				font-size: 20px;
				font-weight: normal;
			}			
			h3 + .grey {
				font-family: 'PT Sans Caption';
			}
			h3 + p {
				font-size: 1.3em;
			}

			hr + .small {
				margin-bottom: 0;
				font-size: 1.1em;
			}
			
			table {
				margin-top: 0;
			}
			table > tbody > tr.darkblue > td {
				padding-top: 4px;
				padding-bottom: 4px;
			}
			table > tbody > tr.darkblue + tr > td {
				padding-bottom: 20px;
			}
			
			dl + div > .btn {
				font-size: 1.2em;
				margin-right: 40px;
			}
			dl > dt, dl > dd {
				font-size: 1.3em;
			}			
			
			form p, label {
				font-size: 1.2em!important;
			}
			form .small {
				font-size: 85%!important;
			}
			
			label {
				color: #6d757b;
			}

			ul > li .glyphicon, .glyphicon {
				line-height: inherit;
			}
			
			.icon-buy::before {
				content: '▲';
			}
			.icon-sell::before {
				content: '▼';
			}
			.btn-buy, .btn-buy:active, .btn-buy:focus, .btn-buy:active:focus {
				color: #4CAF50;
				background-color: inherit;
				border: 1px solid #4CAF50;
				font-size: 16px;
				font-weight: bold;
				outline: none;
			}
			.btn-sell, .btn-sell:active, .btn-sell:focus, .btn-sell:active:focus {
				color: #d42c2c;
				background-color: inherit;
				border: 1px solid red;
				font-size: 16px;
				font-weight: bold;
				outline: none;
			}
			.btn-buy:hover, .btn-buy:active  {
				opacity: 0.7;
				color: #4CAF50;
			}
			.btn-sell:hover, .btn-sell:active {
				opacity: 0.7;
				color: #d42c2c;
			}
			.btn-buy:disabled, .btn-sell:disabled {
				opacity: 0.5;
				color: #333;
				border-color: #333;
			}			
			.red {
				color: #d42c2c;
			}
			.green {
				color: #4CAF50;
			}

			.mb0 {
				margin-bottom: 0;
			}
			
			.helper {
				position: fixed;
				top: 50%;
				right: -1px;
				margin-top: -30px;
				z-index: 1;
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
			
			.grey {
				color: grey;
			}
			
			.small > p:last-child {
				margin-bottom: 0;
			}
			
			.item-frame {
				padding: 10px 15px;
				font-size: .9em;
				background-color: #f0f5f5;
				border-top: 4px solid #dee1e1;
			}
			.dl-horizontal.item-frame > dt {
				width: 110px;
				font-weight: normal;
				color: #333;
				padding: 6px;
			}
			.dl-horizontal.item-frame > dd {
				margin-left: 120px;
				color: gray;
				padding: 6px;
			}
			
			.chart-box {
				display: block;
				position: relative;
				color: inherit;
				padding-bottom: 10px;
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
			.news h3 + p {
				margin-bottom: 0;
			}		
			.input-lg + .form-control-feedback {
				height: auto!important;
			}
			.input-group > .form-control {
				padding-right: 30px;
			}
			.form-control + .input-ok {
				position: absolute;
				right: 0;
				padding: 15px;
				z-index: 2;
			}
			.btn-md, .btn-group-md >.btn {
				padding: 10px 12px;
				font-size: 18px;
			}			
			.form-text:first-child {
				margin-top: 10px;
			}
			
			.toggle {
				line-height: 1.5;
			}
			.dl-horizontal.nobr dt {
				float: left;
				width: 160px;
				overflow: hidden;
				clear: left;
				white-space: nowrap;
			}
			
			.monosign {
				font-style: normal;
				width: 0.6em;
				text-align: right;
				display: inline-block;
			}
			
			.thumb:focus, .btn:focus {
				text-decoration: none;
				outline: none;
			}
			
			.modal-box.graph-box {
				background: none;
				border: none;
				box-shadow: none;
			}
			
			.stp-header {
				margin-bottom: 6px;
			}				
			.stp-header .h1 {
				margin: 0;
			}		
			.stp-header .text, .stp-header p {
				font-size: 1.4em;
			}
			.stp-header p {
				line-height: 1.5;
			}
			.stp-header td > .grey {
				display: block;
			}			

			.help-icon {
				display: inline-block;
				margin-left: 10px;
				vertical-align: middle;
				width: 24px;
				height: 24px;
				background: url('/ui/help.png') no-repeat center center;
				cursor: pointer;
				opacity: 0.3;
				filter: alpha(opacity=30);
			}
			.help-icon:hover, .help-icon:active, .help-icon:focus {
				opacity: 1;
				filter: alpha(opacity=100);
			}

			.darkblue {
				color: #aaa8a8;
				text-transform: uppercase;
				font-weight: 800;
			}
			.table-items td,
			.table-items th {
				border: 0!important;
			}
			.table-items tbody {
				border-top: 1px solid #ddd;
				border-bottom: 1px solid #ddd;
				border-collapse: separate;
			}
			
			.circle {
				font-size: 1.6em;
				line-height: 0.9em;
				position: relative;
				left: -4px;
				top: 1px;
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
			
			.nav-tabs {
				font-size: 18px;
			}
			
			.preview > li > a {
				border: 1px solid #9E9089;
				padding: 18px;
				margin-bottom: 12px;
				display: block;
			}
			.preview > li > div > .grey {
				margin: 6px 0;
			}			
			.preview > li h4 {
				margin-bottom: 0;
				font-weight: normal;
				height: 2.2em;
				overflow: hidden;
			}			
			.preview > li p {
				margin-bottom: 0;
			}			
			.preview > li p, .preview > li span {
				font-size: 90%;
			}
			.preview .grey + div {
				height: 3.8em;
				overflow: hidden;
				position: relative;
			}
			.preview .grey + div > p:first-child {
				height: 66%;
				overflow: hidden;
			}
			.preview .grey + div > p:only-child {
				height: 100%;
			}			
			.preview .blur {
				position: absolute;
				display: block;
				bottom: 20px;
				background: white;
				opacity: 0.6;
				height: 10px;
				z-index: 3;
				width: 100%;
			}
			.preview .blue {
				color: #428bca;
				font-size: 100%;
				font-weight: bold;
			}
			.preview .goto:hover .blue {
				color: #254b6c;
			}
			
			.card-info > li {
				vertical-align: middle;
			}

			.sota {
				font-size: 36px;
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
			
			.note {
				background: #e4e8e8;
				padding: 16px 16px 10px;
				min-width: 320px;
				font-size: 0.9em;
				width: 70%;
			}
			.note h4 {
				font-size: 1.1em;
				font-weight: normal;
			}			
			.note h4 + ul {
				padding-left: 20px;
				margin: 0;
			}
			.note li {
				list-style-type:square;
				list-style-position: inset;
				padding-bottom: 5px;
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
			
			.text-box {
				max-width: 96%;
				padding: 24px 30px;
			}			
			.text-box .small {
				max-height: 510px;
				overflow: auto;
				font-size: 94%;
			}
			.text-box p:last-child {
				margin-bottom: 0;
			}
			.text-box img {
				max-width: 100%;
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
			
			[type=radio] {
				margin-top: 0.4em!important;
			}
			
			.checkbox > label > *, .radio > label > * {
				vertical-align: middle;
				display: inline-block;
				position: static!important;
			}
			
						
			.spreadsheet {
				width: 98%;
			}
			.graph-box p {
				color: #b1afaf;
				font-size: 1.4em;
				font-weigth: bold;
				font-family: 'PT Sans Caption';
			}			
			.graph-box .btn-cover {
				padding: 6px 12px;
				background-color: transparent;
				border: 1px solid #b1afaf;
				color: #b1afaf;
				margin-bottom: 18px;
				font-size: 1.0em;
			}
			.graph-box .graph-outline {
				border-top: 2px dashed #b1afaf;
				border-bottom: 2px dashed #b1afaf;
				height: 450px;
				margin-bottom: 24px;
				padding: 10px 0;
				text-align: center;
				overflow-y: auto;
				max-width: 100%;
			}
			.graph-outline .img-xm {
				max-height: 100%;
				width: 1100px;
			}
			.graph-outline .img-xl {
				max-height: 100%;
				width: 1600px;
			}
			@media (min-height: 800px) {
				.graph-box .graph-outline {
					height: 600px;
				}
			}				
			@media (min-height: 960px) {
				.graph-box .graph-outline {
					height: 800px;
				}
			}			
			@media (max-height: 500px) {
				.graph-box .graph-outline {
					height: 360px;
				}
			}
			
			@media (max-width: 1400px) {
				body {
					font-size: 16px!important;
				}
				.sota {
					font-size: 28px;
				}
				.menu:first-child, .content, .stp-header {
					font-size: 14px!important;
				}
				.stp-header .text, .stp-header p {
					font-size: 1.3em;
				}				
				.input-lg {
					font-size: 16px!important;
					padding: 8px 12px;
					line-height: auto!important;
				}
				.btn-md, .btn-group-md >.btn {
					font-size: 16px;
				}
				.form-control + .input-ok {
					padding: 10px;
				}
				.nav-tabs .small {
					font-size: 80%!important;		
				}
				
				input.input-lg {
					height: 36px!important;
				}
				select.input-lg {
					height: 36px!important;
					padding: 6px 12px;
				}
				form p, label, input, select {
					font-size: 1.2em!important;
				}				
			}			
			@media (max-width: 991px) {
				.stp-header .auth {
					margin-top: 2em;
					border-top: 6px solid #e2e8e9;
					border-bottom: 6px solid #e2e8e9;
					padding-top: 12px;
					padding-bottom: 6px;				
				}
				.stp-header .auth > div {
					margin-right: 2em;
				}				
				.stp-header .auth > * {
					display: inline-block;
					vertical-align: middle;
					line-height: 20px;
					padding-bottom: 6px;
				}
				
				.stp-header .sota {
					margin: 0;
					font-size: 200%;
				}
				.stp-header .sota, .sota + span {
					display: inline-block;
					vertical-align: middle;	
				}
				
				.stp-header + .row {
					display: none;
				}
				
				.container {
					margin-top: 40px;
				}

				.mobile {
					display: block!important;
				}
				.menu {
					margin: 0 auto;
					background: none;
					position: static;
					display: block;
				}				
				.menu > li {
					padding: 0;
					display: block;
				}
				.menu a {
					display: block;
					margin: 12px auto;
					padding: 6px;
					font-size: 24px;
					font-weight: 200;
					border: none;
				}	
				.menu > li > a:hover {
					color: white;
					border-top: 1px solid white;
					border-bottom: 1px solid white;
					background: inherit;
					padding: 5px 6px;
				}
				.opener {
					display: block!important;
				}
			}
			@media (max-width: 640px) {
				.stp-header .text {
					font-size: 1.2em;
				}
				.stp-header > div > *, .stp-header > div > .auth {
					margin-top: 2em;
				}
				.stp-header > div > .sota {
					margin-top: 1.1em!important;
				}				
				.stp-header .auth > * {
					padding-bottom: 6px;
				}
				.stp-header .auth > div:first-child {
					width: 75%;
					margin-right: 0;
				}
				.stp-header .text.auth {
					font-size: 1.3em;
				}
				p {
					font-size: 125%;
				}				
			}
			@media (max-width: 400px) {
				body {
					font-size: 15px;
				}
				.section {
					padding-left: 0;
					padding-right: 0;
				}
				.stp-header .text {
					font-size: 1.0em;
				}
				.stp-header .sota {
					font-size: 160%;
				}				
				.stp-header .text.auth {
					font-size: 1.0em;
				}
				.stp-header > div > .sota {
					margin-top: 1.6em!important;
				}
				.stp-header > div > * {
					margin-top: 2.5em!important;
				}
				.circle {
					font-size: 12px;
					top: -1px;
				}
				.modal-box {
					width: 96%;
					padding: 12px;
				}
				.text-box .small {
					max-height: 480px;
				}
			}			
			@media (min-width: 800px) {
				.text-box {
					width: 800px!important;
				}
			}
			@media (min-width: 1400px) {
				.btn-md, .btn-group-md >.btn {
					font-size: 20px;
				}
				.nav .small {
					font-size: 95%;
				}
				.message p {
					font-size: 0.99em;
				}					
				.message > .close, .message > .close:focus  {
					font-size: 1.5em;
				}				
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
							<button class="btn btn-primary btn-lg btn-close">&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;</button>
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
							<button class="btn btn-info btn-md btn-buy">&nbsp;&nbsp;&nbsp;Да&nbsp;&nbsp;&nbsp;</button>
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
							<button class="btn btn-info btn-md btn-sell">&nbsp;&nbsp;&nbsp;Да&nbsp;&nbsp;&nbsp;</button>
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
							<button class="btn btn-primary btn-lg btn-close">&nbsp;Закрыть&nbsp;</button>
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
								<button class="btn btn-success btn-lg">Далее</button>
								<button class="btn btn-default btn-lg btn-close">Отмена</button>
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