function expand(obj){
		
	if(jQuery(obj).children().first().html()=="+"){
	
		jQuery(obj).children().first().html("-");
		jQuery(obj)
		.next()
		.slideDown(250);
	
	}else{
	
		jQuery(obj).children().first().html("+");
		jQuery(obj)
		.next()
		.slideUp(250);
	
	}

}

function expand_all(obj){	
	
	if(jQuery(obj).children().first().html()=="+"){
	
		jQuery(obj).children().first().html("-");

		jQuery(obj)
			.next()
			.slideDown(250);
		
		
		jQuery(obj)
			.next()
			.next()
			.slideDown(250);
		
	}else{
	
		jQuery(obj).children().first().html("+");
		
		jQuery(obj)
			.next()
			.slideUp(250);
		
		
		jQuery(obj)
			.next()
			.next()
			.slideUp(250);
		
	
	}

}