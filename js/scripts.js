$(document).ready(function() {

	$('.pagination').jqPagination({
		link_string	: '/?page={page_number}',
		max_page	: 40,
		paged		: function(page) {
			//TODO. Ajax call to refresh the page
			get_files(page);
		}
	});
	
});