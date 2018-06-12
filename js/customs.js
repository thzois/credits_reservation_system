
(function($){



	"use strict";
	
	
	
	/**
	 * Page Preloader
	 */
	$("#introLoader").introLoader({
			animation: {
					name: 'counterLoader',
					options: {
							ease: "easeOutSine",
							style: 'style-01',
							animationTime: 1000
					}
			}
	});

	

	/**
	 * Sticky Header
	 *
	$(".container-wrapper").waypoint(function() {
		$(".navbar.navbar-fixed-top").toggleClass("navbar-sticky");
		return false;
	}, { offset: "-20px" }); /
	

	
	/**
	 * Main Menu Slide Down Effect
	 */
	// Mouse-enter dropdown
	$('#navbar li').on("mouseenter", function() {
			$(this).find('ul').first().stop(true, true).delay(350).slideDown(500, 'easeInOutQuad');
	});
	// Mouse-leave dropdown
	$('#navbar li').on("mouseleave", function() {
			$(this).find('ul').first().stop(true, true).delay(100).slideUp(150, 'easeInOutQuad');
	});
	
	
	
	/**
	 * Slicknav - a Mobile Menu
	 */
	var $slicknav_label;
	$('#responsive-menu').slicknav({
		duration: 500,
		easingOpen: 'easeInExpo',
		easingClose: 'easeOutExpo',
		closedSymbol: '<i class="fa fa-plus"></i>',
		openedSymbol: '<i class="fa fa-minus"></i>',
		prependTo: '#slicknav-mobile',
		allowParentLinks: true,
		label:"" 
	});
	
	
	
	/**
	 * Smooth scroll to anchor
	 */
	$(function() {
		$('a.anchor[href*=#]:not([href=#])').on("click",function() {
			if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
				var target = $(this.hash);
				target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
				if (target.length) {
					$('html,body').animate({
						scrollTop: (target.offset().top - 70) // 70px offset for navbar menu
					}, 1000);
					return false;
				}
			}
		});
	});
	
	
	
	/**
	 * Effect Bootstrap Dropdown
	 */
	$('.bt-dropdown-click').on('show.bs.dropdown', function(e) {   
		$(this).find('.dropdown-menu').first().stop(true, true).slideToggle(350); 
	}); 
	$('.bt-dropdown-click').on('hide.bs.dropdown', function(e) { 
		$(this).find('.dropdown-menu').first().stop(true, true).slideToggle(350); 
	});
	
	$(document).on('click', '.dropdown-menu-form', function (e){
		e.stopPropagation();
	});
	 
	$(document).on('click', '.dropdown-menu-form button', function (e){
		closeDropDownForm(e);
	});
	 

	
	/**
	 * Another Bootstrap Toggle
	 */
	$('.another-toggle').each(function(){
		if( $('h4',this).hasClass('active') ){
			$(this).find('.another-toggle-content').show();
		}
	});
	$('.another-toggle h4').on("click",function() {
		if( $(this).hasClass('active') ){
			$(this).removeClass('active');
			$(this).next('.another-toggle-content').slideUp();
		} else {
			$(this).addClass('active');
			$(this).next('.another-toggle-content').slideDown();
		}
	});
	

	
	/**
	 *  Arrow for Menu has sub-menu
	 */
	if ($(window).width() > 992) {
		$(".navbar-arrow ul ul > li").has("ul").children("a").append("<i class='arrow-indicator fa fa-angle-right'></i>");
	}

	
	
	/**
	 * Back To Top
	 */
	$(window).scroll(function(){
		if($(window).scrollTop() > 500){
			$("#back-to-top").fadeIn(200);
		} else{
			$("#back-to-top").fadeOut(200);
		}
	});
	$('#back-to-top, .back-to-top').on("click",function() {
			$('html, body').animate({ scrollTop:0 }, '800');
			return false;
	});
	
	
	
	/**
	 * Bootstrap Tooltip
	 */
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	
	
	
	/**
	 * Fancy - Custom Select
	 */
	$('.custom-select').fancySelect(); // Custom select
	
	
	
	/**
	 * Placeholder
	 */
	$("input, textarea").placeholder();

	
	
	/**
	 * Input Spinner
	 */
	$(".form-spin").TouchSpin({
		buttondown_class: 'btn btn-spinner-down',
		buttonup_class: 'btn btn-spinner-up',
		buttondown_txt: '<i class="ion-minus"></i>',
		buttonup_txt: '<i class="ion-plus"></i>'
	});
	
	
	
	/**
	 * Typeahead - autocomplete form
	 */
	var data = {
	countries: ["Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda",
			"Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh",
			"Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia",
			"Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burma",
			"Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad",
			"Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic", "Congo, Republic of the",
			"Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti",
			"Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador",
			"Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon",
			"Gambia", "Georgia", "Germany", "Ghana", "Greece", "Greenland", "Grenada", "Guatemala", "Guinea",
			"Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hong Kong", "Hungary", "Iceland", "India",
			"Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan",
			"Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos",
			"Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
			"Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands",
			"Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Mongolia", "Morocco", "Monaco",
			"Mozambique", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger",
			"Nigeria", "Norway", "Oman", "Pakistan", "Panama", "Papua New Guinea", "Paraguay", "Peru",
			"Philippines", "Poland", "Portugal", "Romania", "Russia", "Rwanda", "Samoa", "San Marino",
			"Sao Tome", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone",
			"Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain",
			"Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan",
			"Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey",
			"Turkmenistan", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States",
			"Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"],
	capitals: ["Abu Dhabi", "Abuja", "Accra", "Adamstown", "Addis Ababa", "Algiers", "Alofi", "Amman", "Amsterdam",
			"Andorra la Vella", "Ankara", "Antananarivo", "Apia", "Ashgabat", "Asmara", "Astana", "Asunción", "Athens",
			"Avarua", "Baghdad", "Baku", "Bamako", "Bandar Seri Begawan", "Bangkok", "Bangui", "Banjul", "Basseterre",
			"Beijing", "Beirut", "Belgrade", "Belmopan", "Berlin", "Bern", "Bishkek", "Bissau", "Bogotá", "Brasília",
			"Bratislava", "Brazzaville", "Bridgetown", "Brussels", "Bucharest", "Budapest", "Buenos Aires", "Bujumbura",
			"Cairo", "Canberra", "Caracas", "Castries", "Cayenne", "Charlotte Amalie", "Chisinau", "Cockburn Town",
			"Conakry", "Copenhagen", "Dakar", "Damascus", "Dhaka", "Dili", "Djibouti", "Dodoma", "Doha", "Douglas",
			"Dublin", "Dushanbe", "Edinburgh of the Seven Seas", "El Aaiún", "Episkopi Cantonment", "Flying Fish Cove",
			"Freetown", "Funafuti", "Gaborone", "George Town", "Georgetown", "Georgetown", "Gibraltar", "King Edward Point",
			"Guatemala City", "Gustavia", "Hagåtña", "Hamilton", "Hanga Roa", "Hanoi", "Harare", "Hargeisa", "Havana",
			"Helsinki", "Honiara", "Islamabad", "Jakarta", "Jamestown", "Jerusalem", "Juba", "Kabul", "Kampala",
			"Kathmandu", "Khartoum", "Kiev", "Kigali", "Kingston", "Kingston", "Kingstown", "Kinshasa", "Kuala Lumpur",
			"Kuwait City", "Libreville", "Lilongwe", "Lima", "Lisbon", "Ljubljana", "Lomé", "London", "Luanda", "Lusaka",
			"Luxembourg", "Madrid", "Majuro", "Malabo", "Malé", "Managua", "Manama", "Manila", "Maputo", "Marigot",
			"Maseru", "Mata-Utu", "Mbabane Lobamba", "Melekeok Ngerulmud", "Mexico City", "Minsk", "Mogadishu", "Monaco",
			"Monrovia", "Montevideo", "Moroni", "Moscow", "Muscat", "Nairobi", "Nassau", "Naypyidaw", "N'Djamena",
			"New Delhi", "Niamey", "Nicosia", "Nicosia", "Nouakchott", "Nouméa", "Nukuʻalofa", "Nuuk", "Oranjestad",
			"Oslo", "Ottawa", "Ouagadougou", "Pago Pago", "Palikir", "Panama City", "Papeete", "Paramaribo", "Paris",
			"Philipsburg", "Phnom Penh", "Plymouth Brades Estate", "Podgorica Cetinje", "Port Louis", "Port Moresby",
			"Port Vila", "Port-au-Prince", "Port of Spain", "Porto-Novo Cotonou", "Prague", "Praia", "Cape Town",
			"Pristina", "Pyongyang", "Quito", "Rabat", "Reykjavík", "Riga", "Riyadh", "Road Town", "Rome", "Roseau",
			"Saipan", "San José", "San Juan", "San Marino", "San Salvador", "Sana'a", "Santiago", "Santo Domingo",
			"São Tomé", "Sarajevo", "Seoul", "Singapore", "Skopje", "Sofia", "Sri Jayawardenepura Kotte", "St. George's",
			"St. Helier", "St. John's", "St. Peter Port", "St. Pierre", "Stanley", "Stepanakert", "Stockholm", "Sucre",
			"Sukhumi", "Suva", "Taipei", "Tallinn", "Tarawa Atoll", "Tashkent", "Tbilisi", "Tegucigalpa", "Tehran",
			"Thimphu", "Tirana", "Tiraspol", "Tokyo", "Tórshavn", "Tripoli", "Tskhinvali", "Tunis", "Ulan Bator", "Vaduz",
			"Valletta", "The Valley", "Vatican City", "Victoria", "Vienna", "Vientiane", "Vilnius", "Warsaw",
			"Washington, D.C.", "Wellington", "West Island", "Willemstad", "Windhoek", "Yamoussoukro", "Yaoundé", "Yaren",
			"Yerevan", "Zagreb"]
	};

	$('#destination-search-3').typeahead({
			minLength: 2,
			order: "asc",
			group: true,
			maxItemPerGroup: 3,
			groupOrder: function () {

					var scope = this,
							sortGroup = [];

					for (var i in this.result) {
							sortGroup.push({
									group: i,
									length: this.result[i].length
							});
					}

					sortGroup.sort(
							scope.helper.sort(
									["length"],
									false, // false = desc, the most results on top
									function (a) {
											return a.toString().toUpperCase()
									}
							)
					);

					return $.map(sortGroup, function (val, i) {
							return val.group
					});
			},
			hint: true,
			emptyTemplate: 'No result for "{{query}}"',
			source: {
					capital: {
							data: data.capitals
					}
			},
			debug: true
	});

	
	
	/**
	 * Star rating
	 */
	$('.tripadvisor-by-attr').raty({
		path: 'images/raty',
		starHalf: 'tripadvisor-half.png',
		starOff: 'tripadvisor-off.png',
		starOn: 'tripadvisor-on.png',
		readOnly: true,
		round : { down: .2, full: .6, up: .8 },
		half: true,
		space: false,
		score: function() {
			return $(this).attr('data-rating-score');
		}
	});
	
	$('.star-rating-12px').raty({
		path: 'images/raty',
		starHalf: 'star-half-sm.png',
		starOff: 'star-off-sm.png',
		starOn: 'star-on-sm.png',
		readOnly: true,
		round : { down: .2, full: .6, up: .8 },
		half: true,
		space: false,
		score: function() {
			return $(this).attr('data-rating-score');
		}
	});
	
	

	/**
	 * Slider and Carousel by slick.js
	 */
	$('.slick-banner-slider').slick({
		dots: true,
		infinite: true,
		speed: 500,
		slidesToShow: 1,
		slidesToScroll: 1,
		centerMode: true,
		infinite: true,
		centerPadding: '0',
		focusOnSelect: true,
		adaptiveHeight: true,
    });
	$('.slick-testimonial').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			speed: 500,
			arrows: false,
			fade: false,
			asNavFor: '.slick-testimonial-nav',
			adaptiveHeight: true,
	});
	$('.slick-testimonial-nav').slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			speed: 500,
			centerPadding: '0',
			asNavFor: '.slick-testimonial',
			dots: false,
			centerMode: true,
			focusOnSelect: true,
			infinite: true,
			responsive: [
				{
				breakpoint: 1199,
				settings: {
					slidesToShow: 3,
					}
				}, 
				{
				breakpoint: 991,
				settings: {
					slidesToShow: 3,
					}
				}, 
				{
				breakpoint: 767,
				settings: {
					slidesToShow: 3,
					}
				}, 
				{
				breakpoint: 480,
				settings: {
					slidesToShow: 3,
					}
				}
			]
	});
	

	
	/**
	 * ion Range Slider for price and star rating range slider
	 */
	$("#price_range").ionRangeSlider({
		grid: true, 
		type: "double", 
		min: 0, 
		max: 2000,
		from: 200, 
		to: 800, 
		prefix: "$",
	});
	$("#star_rating_range").ionRangeSlider({
		type: "double",
		min: 0,
		max: 5,
		step: 1,
		grid: true,
		grid_snap: true,
	});

	
	
	/**
	 * Show more-less content
	 */
	$('.btn-more-less').on("click",function() {
		$(this).text(function(i,old){
			return old=='Show more' ?  'Show less' : 'Show more';
		});
	});
	
	
	
	/**
	 * Sidebar sticky by Stickit 
	 */
	if ($(window).width() > 991) {
		 $('#sticky-sidebar').stickit({
			top: 95,
			bottom: 200,
		});
	}
	
	

	/**
	 * Responsive Tab by tabCollapse
	 */
	$('#detailTab').tabCollapse({
		tabsClass: 'hidden-sm hidden-xs',
		accordionClass: 'visible-sm visible-xs'
	});
	
	$('#detailTab').on('shown.bs.tab', function () {
		google.maps.event.trigger(map, "resize");
		map.setCenter(var_location);
	});
	$('#detailTab').on('shown-accordion.bs.tabcollapse', function(){
		google.maps.event.trigger(map, "resize");
		map.setCenter(var_location);
	});
	$('#detailTab').on('shown-tabs.bs.tabcollapse', function(){
		google.maps.event.trigger(map, "resize");
		map.setCenter(var_location);
	});
	
	
	
	/**
	 * Sign-in Modal
	 */
	var $formLogin = $('#login-form');
	var $formLost = $('#lost-form');
	var $formRegister = $('#register-form');
	var $divForms = $('#modal-login-form-wrapper');
	var $modalAnimateTime = 300;
	
	$('#login_register_btn').on("click", function () { modalAnimate($formLogin, $formRegister) });
	$('#register_login_btn').on("click", function () { modalAnimate($formRegister, $formLogin); });
	$('#login_lost_btn').on("click", function () { modalAnimate($formLogin, $formLost); });
	$('#lost_login_btn').on("click", function () { modalAnimate($formLost, $formLogin); });
	$('#lost_register_btn').on("click", function () { modalAnimate($formLost, $formRegister); });
	
	function modalAnimate ($oldForm, $newForm) {
		var $oldH = $oldForm.height();
		var $newH = $newForm.height();
		$divForms.css("height",$oldH);
		$oldForm.fadeToggle($modalAnimateTime, function(){
			$divForms.animate({height: $newH}, $modalAnimateTime, function(){
				$newForm.fadeToggle($modalAnimateTime);
			});
		});
	}
	
	
	
	/**
	 * Payment Method
	 */

	$("div.payment-option-form").hide();
	
	$("input[name$='payments']").click(function() {
			var test = $(this).val();
			$("div.payment-option-form").hide();
			$("#" + test).show();
	});
	
	
	
	/**
	 * Instagram Feed
	 */
	function createPhotoElement(photo) {
	  var innerHtml = $('<img>')
		.addClass('instagram-image')
		.attr('src', photo.images.thumbnail.url);

	  innerHtml = $('<a>')
		.attr('target', '_blank')
		.attr('href', photo.link)
		.append(innerHtml);

	  return $('<div>')
		.addClass('instagram-placeholder')
		.attr('id', photo.id)
		.append(innerHtml);
	}

	function didLoadInstagram(event, response) {
			
		var that = this;

	  $.each(response.data, function(i, photo) {
			$(that).append(createPhotoElement(photo));
			});
	}

	$(document).ready(function() {
	  
	  $('#instagram').on('didLoadInstagram', didLoadInstagram);
	  $('#instagram').instagram({
			count: 20,
			userId: 278242867,
			accessToken: '735306460.4814dd1.03c1d131c1df4bfea491b3d7006be5e0'
	  });

	});

	
})(jQuery);


