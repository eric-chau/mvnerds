var $title, $category, $link, $description, errorMsgs = [];

//Permet de vérifier si le formlaire de publication de vidéo est valide
//Lance une exception s il y a un problème
function isVideoValid() {
	if ($title == undefined || $title.val() == '') {
		throw errorMsgs['title'][locale];
	}
	if ($category == undefined || $category.val() == '') {
		throw errorMsgs['category'][locale];
	}
	if ($link == undefined || $link.val() == '' || !isVideoLinkValid($link.val())) {
		throw errorMsgs['link'][locale];
	}
}

//Permet de vérifier si le lien passé en paramètres est valide (youtube ou dailymotion)
function isVideoLinkValid(link) {
	if (	link.indexOf('youtube.com/watch?v=', 0) >= 0 || 
		link.indexOf('youtu.be/', 0) >= 0 ||
		link.indexOf('dailymotion.com', 0) >= 0) {
		return true;
	}
	return false;
}

//Permet de publier la vidéo
//Peut déclencher une exception
function publishVideo() {
	//On vérifie la validité des champs
	isVideoValid();
	
	//On prépare les données à envoyer
	var data = {title: $title.val(), category: $category.val(), link: $link.val(), description: $description.val()};
	
	//On demande la création de la vidéo en AJAX
	$.ajax({
		type: 'POST',
		url:  Routing.generate('videos_publish_ajax', {_locale: locale}),
		data: data,
		dataType: 'json'
	}).done(function(slug){
		window.location = Routing.generate('videos_detail', {_locale: locale, slug: slug});
	}).fail(function(){
		console.log('fail');
	});
}

$(document).ready(function() {
	//Récupération des champs du formulaire
	$title = $('#video-publish-title')
	$category = $('#video-publish-category')
	$link = $('#video-publish-link')
	$description = $('#video-publish-description')
	
	//Initialisation des messages d'erreurs
	errorMsgs['title'] = [];
	errorMsgs['title']['fr'] = 'Le titre de la vidéo n\'est pas valide.';
	errorMsgs['title']['en'] = 'The video title is not valid.';
	errorMsgs['category'] = [];
	errorMsgs['category']['fr'] = 'La catégorie de la vidéo n\'est pas valide.';
	errorMsgs['category']['en'] = 'The video category is not valid.';
	errorMsgs['link'] = [];
	errorMsgs['link']['fr'] = 'La lien de la vidéo fourni n\'est pas valide.';
	errorMsgs['link']['en'] = 'The video link is not valid.';
	
	//Clic sur le bouton publish de la page de listing des vidéos
	$('#video-publish-action').click(function() {
		$('#modal-video-publish').modal('show');
		return false;
	});
	
	//Clic sur le bouton publish de la modal
	$('#modal-btn-publish').click(function(e) {
		e.preventDefault();
		$('#modal-video-publish').modal('hide');
		try {
			publishVideo()
		} catch (err) {
			console.log(err);
		}
	});
});