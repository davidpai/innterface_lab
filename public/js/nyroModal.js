$(function() {
		$('.nyroModal').nyroModal({
			callbacks: {
				afterShowCont: function(nm) {
					$('.resizeLink', nm.elts.cont).click(function(e) {
						e.preventDefault();
						nm.sizes.initW = Math.random()*1000+200;
						nm.sizes.initH = Math.random()*1000+200;
						nm.resize();
					});
				}
			}
		});
		
		var validForm = $('#myValidForm').submit(function(e) {
			e.preventDefault();
			if (validForm.find(':text').val() != '') {
				validForm.nyroModal().nmCall();
			} else {
				alert("Enter a value before going to " + validForm.attr("action"));
			}
		});
		
		function preloadImg(image) {
		var img = new Image();
		img.src = image;
		}
		preloadImg('');
		preloadImg('');
		preloadImg('');
	});