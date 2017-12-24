/**
 * @file "Master clock" and dialog controller for SOTA cabinet
 * @copyright 2017 Vladimir Vyatkin <voldemarpro@hotmail.com>
 */
(function(appName, config) {
	var _STP_ = function() {
		this.config = config;
		this.currency = this.config.currency;
		this.allowTrade = this.config.allowTrade;
		this.timer = new (function(_parent) {
			this.currentTime = 0;
			this.parent = _parent;
			this.origin = this.parent.config.timeOrigin;
			this.offset = this.parent.config.timeOffset;
			this.counter = 0; // timer to count elapsed seconds 			
			this.resetTime = $.proxy(function() {
				var dt = this.origin;
				var currentTimeNow = Math.floor(Date.now()/1000);
				if (this.currentTime && (currentTimeNow - this.currentTime) > 1)
					this.origin.setSeconds(this.origin.getSeconds() + (currentTimeNow - this.currentTime) - 1);

				var h = dt.getUTCHours();
				var m = dt.getUTCMinutes();
				var s = dt.getUTCSeconds();							
				m = m < 10 ? ('0' + m) : m;
				s = s < 10 ? ('0' + s) : s;
				h = h < 10 ? ('0' + h) : h;
				$('#stp-time').text(h + ' : ' + m + ' : ' + s + ' МСК');
				window.setTimeout(this.resetTime, 1000);
				
				this.counter += 1;
				this.origin.setSeconds(this.origin.getSeconds() + 1);
				this.currentTime = currentTimeNow;
		
				if (this.counter == 5) {
					this.counter = 0;
					$.getJSON(
						'/thread/summary', {},
						$.proxy(function(resp) {
							try {
								this.parent.allowTrade = resp['session']['allowTrade'] == 1 ? 1 : 0;
								
								if (this.parent.allowTrade && $('.circle').hasClass('circle-red'))
									$('.circle').addClass('circle-green').removeClass('circle-red');
								else if (!this.parent.allowTrade)
									$('.circle').addClass('circle-red').removeClass('circle-green');
									
								if (resp.notices)
									this.parent.alert(resp['notices']);
								
								if (resp.stat) {
									resp.stat.perc = resp.stat.total ? Math.round(resp.stat.success/resp.stat.total * 100) : 0;
									$('.glyphicon-stats').next().html(
										resp.stat.success + ' <small class="gray">из</small> ' + resp.stat.total + ' [' + resp.stat.perc + '%]'
									);
								} else {
									$('.glyphicon-credit-card').next().html(resp.session.debit + ' ' + this.parent.currency);
								}
								
								if (this.parent.onServerResponse)
									this.parent.onServerResponse(resp);
							
						} catch(e) {
								console.log(e);
							}
						}, this)
					);
				}					
			}, this);
		})(this);
		
		this.timer.origin.setUTCHours(this.timer.origin.getUTCHours() + this.timer.offset/60);
		this.timer.resetTime(this.timer.origin);
		
		this.dialog = {
			fadeIn: function(sel, callback) {
				$(sel).fadeIn(300, function() {
					if (callback && typeof callback == 'function')
						callback.call(this);
				});						
			},
			open: function(sel, callback) {
				$(sel).parent().parent().parent().show();
				if ($(sel).siblings(':visible').length)
					$(sel).siblings(':visible').hide();
				$('.form-control-feedback').text('');
				$('.has-error').removeClass('has-error');
				$(sel).fadeIn(300, function() {
					if (callback && typeof callback == 'function')
						callback.call(this);
				});
			},
			close: function(callback) {
				$('.modal-box').filter(':visible').fadeOut(300, function() {
					$('.modal-cover').hide();
					if (callback && typeof callback == 'function')
						callback.call(this);
				});
			},
			stopEvent: function(e) {
				e.preventDefault();
				e.stopPropagation();
			}
		};	
		this.dialog.showStatus = $.proxy(function(message) {
			$('#popup-status').find('p').html(message);
			this.dialog.open('#popup-status');
		}, this);	
		this.dialog.showProc = $.proxy(function(message) {
			this.dialog.open('#popup-proc');
		}, this);					
		
		this.beforeBuy = $.proxy(function(callback) {
			this.dialog.open('#popup-buy', function() {
				$('#popup-buy').find('.btn-yes').click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					if (callback && typeof callback == 'function')
						callback.call(this);
				})
			});
		}, this);
		
		this.beforeSell = $.proxy(function(callback) {
			this.dialog.open('#popup-sell', function() {
				$('#popup-sell').find('.btn-yes').click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					if (callback && typeof callback == 'function')
						callback.call(this);
				})
			});
		}, this);
		
		this.alert = function(list) {
			$('.notices').children().remove();
			$('.notices').toggleClass('hidden', !Object.keys(list).length);
			for (var j in list) {
				$('.message').filter('.hidden').clone().children().first()
					.attr({name: 'id' + list[j].id})
					.next().text(list[j].text)
					.parent().removeClass('hidden').appendTo($('.notices'));
			}
		}
		$(document).on('click', '.close, .btn-close', function(e) {
			if ($(this).parent().hasClass('message'))
				return true;
			e.preventDefault();
		});						
	};
	window[appName] = new _STP_();
})('STP', config);