$(document).ready(function() {
	mySettings.previewParserPath = Routing.generate('bbcode_parse');

	$('#news_content').markItUp(mySettings);

	$('#emoticons a').click(function(e) {
		e.preventDefault();
		emoticon = $(this).attr("title");
		$.markItUp( { replaceWith:emoticon } );
	});
});