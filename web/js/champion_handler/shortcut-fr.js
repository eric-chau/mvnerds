var $btnCompare = $('a#btn-compare'),
  $searchInputText = $('input[type="text"]#filter-value'),
  $btnComparisonList = $('a#drop-comparison-list'),
  $btnHelp = $('li.help-action'),
  $btnFilter = $('a#drop-filter-list'),
  isFirstActionAfterGT = false;

$(document).ready(function()
{
    $('body').bind('keyup', function(event) 
    {
      shortcutListener(event);
    });

    $('input[type="email"]#user_email, input[type="text"]#filter-value').on('focus', function()
    {
      $('body').unbind('keyup');
      $searchInputText.bind('keyup', function(event)
      {
        if (event.which == 27) {
          $(this).blur();
        }
      });
    });



    $('input[type="email"]#user_email, input[type="text"]#filter-value').on('blur', function()
    {
      $('body').bind('keyup', function(event)
      {
        shortcutListener(event);
      });
    });
});

function shortcutListener(event) {
  if (isFirstActionAfterGT) {
    isFirstActionAfterGT = false;
    
    return false;
  }

  switch(event.which) {
    case 67: // touche 'c'
      if ($btnCompare.hasClass('disabled')) {
        return false;
      }

      $btnCompare.trigger('click');
      break;
    case 82: // touche 'r'
      $('.dropdown.open .dropdown-toggle').dropdown('toggle');
      $searchInputText[0].selectionStart = $searchInputText[0].selectionEnd = $searchInputText.val().length;
      break;
    case 76:
      $btnComparisonList.trigger('click');
      break;
    case 70:
      $btnFilter.trigger('click');
      break;
    case 86:
      $('.dropdown.open .dropdown-toggle').dropdown('toggle');
      $btnHelp.trigger('click');
    default:
      break;

  }
}