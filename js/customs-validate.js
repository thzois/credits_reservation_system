$(document).ready(function() {
	
	
	
	$("#contactForm").validate({
		rules: {
			yourname: "required",
			contactEmail: {
				required: true,
				email: true
			},
			contactMessage: {
				required: true,
				minlength: 50
			}
		},
		messages: {
			yourname: "Please enter your firstname",
			contactMessage: {
				required: "Please write your messgae",
				minlength: "Your messgae must consist of at least 50 characters"
			},
			contactEmail: "Please enter a valid email address"
		}
	});
	
	
	
});