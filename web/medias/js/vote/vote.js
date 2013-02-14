var $likeBtn, $dislikeBtn, $voteBtns;

$(document).ready(function() {
	
	$('div.vote-block').on('click', '.btn-vote:not(.disabled)', function() {
		console.log('like : ' + $(this).data('like'));
		data = {object_slug: objectSlug, object_type: objectType, like: $(this).data('like')};
		
		$.ajax({
			type: 'POST',
			url:  Routing.generate('vote_vote', {'_locale': locale}),
			data: data,
			dataType: 'json'
		}).done(function(data){
			console.log(data);
			$('.like_count').html(data.likeCount);
			$('.dislike_count').html(data.dislikeCount);
			$('.vote_count').html(data.likeCount + data.dislikeCount);
			
			var rating = data.likeCount / (data.likeCount + data.dislikeCount) * 100;
			var rating_css_class = '';
			if (rating < 40) {
				rating_css_class = 'rating red';
			} else if (rating >= 40 && rating < 70) {
					rating_css_class = 'rating orange';
			} else {
			   rating_css_class = 'rating green';
			}
			$('div.vote-block div.rating').attr('class', rating_css_class).html(Math.round(rating) + '%');
			
			if (data.canLike) {
				$('.btn-vote-like').removeClass('disabled');
			} else {
				$('.btn-vote-like').removeClass('disabled');
				$('.btn-vote-like').addClass('disabled');
			}
			if (data.canDislike) {
				$('.btn-vote-dislike').removeClass('disabled');
			} else {
				$('.btn-vote-dislike').removeClass('disabled');
				$('.btn-vote-dislike').addClass('disabled');
			}
		}).fail(function(data){
			console.log(data);
		});
	});
});