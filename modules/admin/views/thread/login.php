<?php
	$this->title = \Yii::$app->getModule('secret')->name.' - '.'авторизация';
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
		
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;lang=en" />
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic|PT+Sans+Caption:400,700&subset=latin,cyrillic" />

		<script type="text/javascript" src="/js/jquery.min.js"></script>
		
		<!--[if lt IE 9]>
		<script type="text/javascript" src="/js/html5shiv.js"></script>
		<script type="text/javascript" src="/js/respond.js"></script>
		<![endif]-->
		
		<style>
			body {
				margin-top: 0;
				background: white;
				font-family: 'Open Sans', sans-serif;
				font-size: 16px;
			}
			form {
				padding: 1.5em;
				background: white;
			}
			label {
				font-size: 1.1em;
				font-weight: normal;
				text-transform: lowercase;
			}
			input {
				font-size: 1.1em!important;
				border-radius: 2px!important;
				-moz-border-radius: 2px!important;
			}
		
			
			.form-box {
				background: #dee3e4;
				padding: 1em;
			}
			.form-control {
				height: auto!important;
			}			
			.btn {
				padding-top: 4px;
				padding-bottom: 4px;
			}
			.cover {
				top: 0;
				left: 0;
				z-index: 2;
				position: fixed;
				width: 100%;
				height: 100%;
			}
			.cell-wrapper {
				display: table;
				height: 100%;
				width: 100%;
			}
			.cell {
				display: table-cell;
				vertical-align: middle;
				text-align: center;
			}
			.content {
				display: inline-block;
				width: 400px;
				max-width: 96%;
				position: relative;
			}
			.content > div:first-child {
				width: 100%;
				position: absolute;
				top: -160px;
			}
			.alert {
				margin-bottom: 0!important;
			}

			.h1 span {
				color: #5b6771;
				font-size: 110%;
			}
			
			.logo {
				width: 1.68em;
			}
			
			@media (max-width: 1400px) {
				body {
					font-size: 14px;
				}				
			}
			
			@media (max-width: 400px) {
				.h1 span {
					color: #5b6771;
					font-size: 90%;
				}
				.logo {
					width: 1.4em;
				}				
				.content {
					max-width: 360px;
				}
				.content > div:first-child {
					top: -140px;
				}		
			}
		</style>
	</head>
	
	<body>
		
		<div class="cover">
			<div class="cell-wrapper">
				<div class="cell">

					<div class="content">
						<div class="text-center">
							<div class="h1">
								<p><img class="logo" src="/ui/logo2.png" alt="SOTA (STP)" /></p>
								<span>STP-1 ADMIN</span>
							</div>
						</div>
						
						<div class="form-box">					
							<form action="" class="form text-center" method="post">
									
								<div class="form-group">
									<label for="login">Логин</label>
									<input id="login" type="text" class="form-control" name="login" value="" />
								</div>
								
								<div class="form-group">
									<label for="pwd">Пароль</label>
									<input id="pwd" type="password" class="form-control" name="pwd" value="" />
								</div>

								<input id="form-token" type="hidden" name="<?php echo \Yii::$app->getRequest()->csrfParam ?>" value="<?php echo \Yii::$app->getRequest()->getCsrfToken() ?>" />
								
								<div>
									<input type="submit" class="btn btn-success btn-close" value="Войти" />
								</div>
							</form><?php
							
							if ($errors) {
								$error = reset($errors);
								echo '
								<div class="alert alert-danger">
									', $error, '
								</div>';
							}
							
							?> 
						</div>
					</div>
					
				</div>
			</div>
		</div>
		
	</body>
	<script>
		if ($('.alert').length && $('.alert').is(':visible'))
			$('input').keyup(function() {
				$('.alert').hide();
			});
	</script>
	
</html>