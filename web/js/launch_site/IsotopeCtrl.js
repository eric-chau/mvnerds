function IsotopeCtrl ($isotope, options) {
	this.options = options;
	
	this.filters = [
		
	];
	
	this.filter = function(filterValue) {
		if(filterValue != '') {
			setFilterValue("[data-name*='" + filterValue.toLowerCase()+"']");
		}
	}
}