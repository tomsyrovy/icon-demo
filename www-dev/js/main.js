
$(document).ready(function() {


	/** 
	 * Inicializace unveil.min.js
	 */
	$(".partneri .partner-group img").unveil(0, function() {
		$(this).load(function() {
			this.style.opacity = 1;
		});
	});





	// Na stránce Detailní program prolínej obrázky
	$('.avatarSlideshow').fadeSlideShow({
		width: 70,
		height: 70
	});





	// Zobrazuj/skrývej více o přednášce na straně Detailní program
	$(".seminar-more").click(function(){
		var el = $(this).parent().find(".seminar-more-info");
		if($(el).css("display") == "none"){
			$(el).slideDown();
			$(this).text("Méně o přednášce");
		}else{
			$(el).slideUp();
			$(this).text("Více o přednášce");
		}
	});





	// Poslední přednášece na straně Detailní program přidej třídu .last
	$('.seminar_12_2015-04-25').last().addClass('last');
	$('.seminar_11_2015-04-24').last().addClass('last');
	$('.seminar_11_2015-04-25').last().addClass('last');
	$('.seminar_11_2015-04-26').last().addClass('last');





	/** 
	 * Inicializace swiper slideru
	 */
	var mySwiper = new Swiper('.swiper-container',{
		pagination: '.swiper-pagination',
		autoplay: 10000,
		speed: 800,
		paginationClickable: true
	});





	/** 
	 * Přepínač mobilní navigace
	 */
	var par = $('.nav-mobile');
	//$(par).hide();
	
	$('.nav-mobile-toggle').click(function(e) {
		$(par).slideToggle();
		e.preventDefault();
	});





	/** 
	 * Inicializace FastClick.js
	 */
	FastClick.attach(document.body);




	/** 
	 * Fancybox
	 */
	// Fotogalerie 2013
	$("a[rel=gallery]").fancybox({
		'transitionIn' : 'elastic',
		'transitionOut' : 'elastic',
		//'overlayOpacity' : 0,
		//'padding' : 3,
		'title' : false,
		helpers: {
			overlay: {
				locked: false
			}
		}
	});
	
	// Pop-up "Program bude doplněn" na stránce Předprogram
	$('.pop-up-button').fancybox({
		'title' : false,
		helpers: {
			overlay: {
				locked: false
			}
		}
	});



});



/**	
 * Retina.js
 */
/*Retina = function() {
	return {
		init: function(){
			// Get pixel ratio and perform retina replacement
			// Optionally, you may also check a cookie to see if the user has opted out of (or in to) retina support
			var pixelRatio = !!window.devicePixelRatio ? window.devicePixelRatio : 1;
			if (pixelRatio > 1) {
				$("img").each(function(idx, el){
					el = $(el);
					if (el.attr("data-src2x")) {
						el.attr("data-src-orig", el.attr("src"));
						el.attr("src", el.attr("data-src2x"));
					}
				});
			}
		}
	};
}();*/

// Inicializace retina.js
/*$(document).ready(function() {
	 Retina.init();
});*/





/**	
 * Animovaný scroll k anchoru (třída .scroll_to)
 */
anchor = {
	init : function()  {
		$("a.scroll_to").click(function () {	
			elementClick = $(this).attr("href")
			destination = $(elementClick).offset().top;
			$("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, 800 );
			return false;
		})
	}
}

$(document).ready(function() {
	anchor.init();
});