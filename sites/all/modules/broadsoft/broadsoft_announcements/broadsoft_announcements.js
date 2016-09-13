(function ($){
	$(document).ready(function(){

		setInterval(function () {
			moveRight();
		}, 5000);
		$(".show-announcement").on("click",function(){
			$('.announcement-list').toggle();
			if ($('.announcement-list').is(':visible')){
				$(this).removeClass('fa-plus').addClass('fa-minus');
			}else{
				$(this).removeClass('fa-minus').addClass('fa-plus');
			}

		});


		var slideCount = $('#slider ul li').length;
		var slideWidth = $('#slider ul li').width();
		var slideHeight = $('#slider ul li').height();
		var sliderUlWidth = slideCount * slideWidth;

		$('#slider').css({ width: slideWidth, height: slideHeight });

		$('#slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });

		$('#slider ul li:last-child').prependTo('#slider ul');    

		function moveRight() {
			$('#slider ul').animate({
				left: - slideWidth
			}, 500, function () {
				$('#slider ul li:first-child').appendTo('#slider ul');
				$('#slider ul').css('left', '');
			});
		};
	});
})(jQuery);

