var closeButtonKey = 99,
    nextStepButtonKey = 110;

if (locale == 'fr') {
  closeButtonKey = 102;
  nextStepButtonKey = 115;
}

$(document).ready(function()
{
  $('a.start-guide-tour-btn').on('click', function(event)
  {
    $('#champion-comparison').on('mouseover', 'li.champion:not(.champion-maxi)', function(){$(this).draggable('disable')});
    isGuideTourDisplay = true;
    $('.dropdown-toggle').addClass('disabled');
    $('body,html').animate({scrollTop:$('div.champion-benchmark-container').position().top - 50},500);
    guiders.show('first');

    $('body').unbind('keyup');
    $('body').bind('keypress', function(event)
    {
      event.preventDefault();
      switch(event.which) {
        case nextStepButtonKey: // touche 'n'
          guiders.next();
          break;
        case 112: // touche 'p'
          guiders.prev();
          break;
        case closeButtonKey: // touche 'c'
          isFirstActionAfterGT = true;
          isGuideTourDisplay = false;
         $('#champion-comparison').on('mouseover', 'li.champion:not(.champion-maxi)', function(){$(this).draggable('enable')});
          $('.dropdown-toggle').removeClass('disabled');
          onCloseCallBack();
        default:
          break;
      }
    });
  });

  $('a.btn-next').on('click', function()
  {
    $('body,html').animate({scrollTop:$('div.champions-handler-container').position().top},500);
  });

  $('a.btn-close').on('click', function()
  {
    isGuideTourDisplay = false;
    $('#champion-comparison').on('mouseover', 'li.champion:not(.champion-maxi)', function(){$(this).draggable('enable')});
    $('.dropdown-toggle').removeClass('disabled');
    $('body,html').animate({scrollTop:$('div.champion-benchmark-container').position().top - 50},500);
  });
});

function onCloseCallBack()
{
  guiders.hideAll();
  $('body').unbind('keypress');
  $('body').bind('keyup', function(event)
  {
    shortcutListener(event);
  });
}
