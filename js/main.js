(function(){
	$(document).ready(function($) {
		$('body').removeClass('loader');
	});

	window.sprite = $('#zipit').sprite({cellSize: [131,54],cells: [1, 15],initCell: [0,0],interval: 50});

	window.App = {
		Models:{},
		Views:{},
		Router:{}	
	}
		
	window.showErr = function(text){
		var el =  document.getElementById('err');
		if(!el){
			$('#inputs').append('<div id="err">'+text+'</div>');
		}else{
			$('#err').html(text);
			$('#err').fadeIn(200);
		}
		$('#err').delay(5000).fadeOut(200);
	}
	
	App.Models.Sub = Backbone.Model.extend({
		validate:function(attr){
			if(!$.trim(attr.lyndaUrl) ){
				return 'please enter download link.'	
			}
		}
	});
	
	App.Views.subs = Backbone.View.extend({
		el:'#back',
		
		initialize:function(){
			this.model.on('change:lyndaUrl',this.changedurl,this);
		},
		events:{
			'click #retry':'retry'	
		},
		
		retry:function(){
			this.model.set('lyndaUrl','#');
			sprite.stop();
			sprite.col(1);
			$('#downlodit').attr('href','');
			$('#lyndaURL').find('input[type=text]').val('');
			$('#retry').animate({height:0,top:-7},20);
			if (Modernizr.csstransforms3d) {
				$('#inputs').removeClass('flipped');
			} else {
				$('#back').fadeOut(200);
				$('#front').fadeIn(500);
			}
			
		},
		
		changedurl:function(){
			if( this.model.get('lyndaUrl') == '#') return
			sprite.go();
			$.ajax({ 
				type: 'get', 
				url: 'index.php',
				data:{ url: $.trim(this.model.get('lyndaUrl')),api:1},
				success: function(data) {
					if(data.success){
					  	$('#downlodit').attr('href',data.success);
					    if (Modernizr.csstransforms3d) {
					    	$('#back').css('display', 'block');
							$('#inputs').addClass('flipped');
						} else {
							$('#front').fadeOut(200);
							$('#back').fadeIn(500);
						}
						$('#retry').delay(500).animate({height:20,top:-27},100);
					}else{
						showErr(data.error);
					}
					sprite.stop();
					sprite.col(1);
				},
				error: function(xhr, ajaxOptions, thrownError) {
					console.log(xhr.toSource() +' - '+thrownError);
					showErr('Check URL please!');
					sprite.stop();
					sprite.col(1);
				}
			});
		}
		
	})
	
	App.Views.submitURL = Backbone.View.extend({
		el:'#lyndaURL',
		events:{
			'submit':'submit'	
		},
		submit:function(e){
			e.preventDefault();
			var newURL = $(e.currentTarget).find('input[type=text]').val();
			var pat = /lynda.com/i;
			if(pat.test(newURL)){
				this.model.set('lyndaUrl',newURL);
			}else{
				showErr('Oops, check your url please!');	
			}
		}
	});

	window.submodel = new App.Models.Sub();
	new App.Views.submitURL({model:submodel});
	new App.Views.subs({model:submodel});

	// Add fitText
	$(".slogan").fitText(1.2, { minFontSize: '20px', maxFontSize: '52px' });

	// Sync the Begining and ending section
	var redElmArr = ['#head','#footer'], redHeight=0;
	for(x in redElmArr){
	    redHeight+=$(redElmArr[x]).height();
	}

	var _obj = $('div.content');

	(syncHeight = function(){
	    scrHeight = $(window).height();
	    ncsHeight = _obj.height()+redHeight;
	    vspace =(scrHeight>ncsHeight)?fit2parent.vspace(_obj,window)-redHeight/2:0;
	    _obj.css({
	        'margin-top':vspace+'px',
	        'margin-bottom':vspace+'px'
	    });
	})();

	$(window).resize(syncHeight);
})();