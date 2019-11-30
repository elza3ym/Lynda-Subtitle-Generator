// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());


// Place any jQuery/helper plugins in here.

// jquery sprite - http://www.lukelutman.com

(function($){$.fn.sprite=function(options){var base=this,opts=$.extend(true,{},$.fn.sprite.defaults,options||{}),w=opts.cellSize[0],h=opts.cellSize[1],ys=opts.cells[0],xs=opts.cells[1],row=opts.initCell[0],col=opts.initCell[1],offx=opts.offset[0],offy=opts.offset[1],timer=null;this.next=function(){var lookup=col+1;if(lookup>xs-1){if(!opts.wrap){base.stop();return;}lookup=0;}col=lookup;_setSprite(base,row,col);};this.prev=function(){var lookup=col-1;if(lookup<0){if(!opts.wrap){base.stop();return;}lookup=xs-1;}col=lookup;_setSprite(base,row,col);};this.go=function(){if(timer){base.stop();}if(!timer){timer=setInterval(this.next,opts.interval);}};this.revert=function(){if(timer){base.stop();}if(!timer){timer=setInterval(this.prev,opts.interval);}};this.stop=function(){if(timer){clearTimeout(timer);timer=null;}};this.cell=function(r,c){row=r;col=c;_setSprite(base,row,col);};this.row=function(r){if(r>ys-1){r=(opts.wrap)?0:ys-1;}if(r<0){r=(opts.wrap)?ys-1:0;}this.cell(r,0);};this.col=function(c){if(c>xs-1){c=(opts.wrap)?0:xs-1;}if(c<0){c=(opts.wrap)?xs-1:0;}this.cell(row,c);};this.offset=function(x,y){offx=x;offy=y;_setSprite(0,0);};return this.each(function(index,el){var $this=$(this);$this.css({width:w,height:h});if($this.css("display")=="inline"){$this.css("display","inline-block");}_setSprite(this,row,col);});function _setSprite(el,row,col){var x=(-1*((w*col)+offx)),y=(-1*((h*row)+offy));/*console.log(x+"px "+y+" px")*/;$(el).css("background-position",x+"px "+y+"px");}};$.fn.sprite.defaults={cellSize:[0,0],cells:[1,1],initCell:[0,0],offset:[0,0],interval:50,wrap:true};})(jQuery);

$('a[href="#"]').click(function(e){
	e.preventDefault();
})

/*! 
* FitText.js 1.1
*
* Copyright 2011, Dave Rupert http://daverupert.com
* Released under the WTFPL license 
* http://sam.zoy.org/wtfpl/
*
* Date: Thu May 05 14:23:00 2011 -0600
*/
$.fn.fitText=function(d,e){var f=d||1,a=$.extend({minFontSize:Number.NEGATIVE_INFINITY,maxFontSize:Number.POSITIVE_INFINITY},e);return this.each(function(){var b=$(this),c=function(){b.css("font-size",Math.max(Math.min(b.width()/(10*f),parseFloat(a.maxFontSize)),parseFloat(a.minFontSize)))};c();$(window).on("resize",c)})};

/**
 * Fit elements' height/width to their parent
 * Copyright 2013 Hashem Qolami <hashem@qolami.com>
 * Released under the WTFPL license
 */
var fit2parent={vspace:function(b,a){a=void 0===a?$(b).parent():a;return Math.round(($(a).height()-$(b).height())/2)},hspace:function(b,a){a=void 0===a?$(b).parent():a;return Math.round(($(a).width()-$(b).width())/2)}};