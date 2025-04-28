jQuery(document).ready(function($){
  var $fab = $('.waply-fab');
  var $popup = $('.waply-popup');
  var $close = $('.waply-popup-close');

  // Open popup
  $fab.on('click keypress', function(e){
    if(e.type==='click' || (e.type==='keypress' && (e.key==='Enter'||e.key===' '))) {
      $popup.fadeIn(200);
      $fab.attr('aria-expanded', 'true');
      $popup.find('a,button,input,select,textarea,[tabindex]:not([tabindex="-1"])').first().focus();
    }
  });
  // Close popup
  $close.on('click keypress', function(e){
    if(e.type==='click' || (e.type==='keypress' && (e.key==='Enter'||e.key===' '))) {
      $popup.fadeOut(150);
      $fab.attr('aria-expanded', 'false');
      $fab.focus();
    }
  });
  // Hide popup when clicking outside
  $(document).on('mousedown', function(e){
    if($popup.is(':visible') && !$popup.is(e.target) && $popup.has(e.target).length===0 && !$fab.is(e.target)){
      $popup.fadeOut(150);
      $fab.attr('aria-expanded', 'false');
    }
  });
  // Keyboard: ESC closes popup
  $(document).on('keydown', function(e){
    if(e.key==='Escape' && $popup.is(':visible')){
      $popup.fadeOut(150);
      $fab.attr('aria-expanded', 'false');
      $fab.focus();
    }
  });
  // Accessibility: trap focus inside popup
  $popup.on('keydown', function(e){
    if(e.key==='Tab'){
      var focusable = $popup.find('a,button,input,select,textarea,[tabindex]:not([tabindex="-1"])').filter(':visible');
      var first = focusable.first()[0], last = focusable.last()[0];
      if(e.shiftKey && document.activeElement===first){ e.preventDefault(); last.focus(); }
      else if(!e.shiftKey && document.activeElement===last){ e.preventDefault(); first.focus(); }
    }
  });
  // WhatsApp button click
  $popup.on('click', '.waply-popup-account-btn a', function(e){
    // Let default link open WhatsApp
    $popup.fadeOut(150);
    $fab.attr('aria-expanded', 'false');
  });
});
