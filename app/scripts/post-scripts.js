// Initiate LazyLoad
var lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy"
});



// Swipe gestures script - This is really poorly coded, but javascript isn't my strength, so...

var containeroverlay = document.querySelector('.overlay');
containeroverlay.addEventListener("touchstart", startTouch, false);
containeroverlay.addEventListener("touchmove", moveTouchOverlay, false);
var containerfilters = document.querySelector('#overlay_type_filters');
containerfilters.addEventListener("touchstart", startTouch, false);
containerfilters.addEventListener("touchmove", moveTouchOverlayFilters, false);
var containerinfoimage = document.querySelector('#overlay_info_type_image');
containerinfoimage.addEventListener("touchstart", startTouch, false);
containerinfoimage.addEventListener("touchmove", moveTouchOverlayInfo, false);
var containerinfovideo = document.querySelector('#overlay_info_type_video');
containerinfovideo.addEventListener("touchstart", startTouch, false);
containerinfovideo.addEventListener("touchmove", moveTouchOverlayInfo, false);

var initialX = null;
var initialY = null;

  function startTouch(e) {
    initialX = e.touches[0].clientX;
    initialY = e.touches[0].clientY;
  };

  // Swipe on media overlay
  function moveTouchOverlay(e) {
    if (initialX === null) {
      return;
    }

    if (initialY === null) {
      return;
    }

    var currentX = e.touches[0].clientX;
    var currentY = e.touches[0].clientY;

    var diffX = initialX - currentX;
    var diffY = initialY - currentY;

    if (Math.abs(diffX) > Math.abs(diffY)) {
      if (diffX > 0) {
        // swiped left
        nextmedia();
      } else {
        // swiped right
		prevmedia();
      }  
    } else {
      if (diffY > 0) {
        // swiped up
		overlay('openoverlayinfo','image');
      } else {
        // swiped down
        overlay('closeoverlay');
      }  
    }

    initialX = null;
    initialY = null;

	e.stopImmediatePropagation();
    e.preventDefault();
  };
  
  // Swipe on filters overlay
  function moveTouchOverlayFilters(e) {
    if (initialX === null) {
      return;
    }

    if (initialY === null) {
      return;
    }

    var currentX = e.touches[0].clientX;
    var currentY = e.touches[0].clientY;

    var diffX = initialX - currentX;
    var diffY = initialY - currentY;

    if (Math.abs(diffX) > Math.abs(diffY)) {
      // sliding horizontally
      if (diffX > 0) {
        // swiped left
        console.log("nothing to swipe here");
      } else {
        // swiped right
        console.log("nothing to swipe here");
      }  
    } else {
      // sliding vertically
      if (diffY > 0) {
        // swiped up
		overlay('closeoverlayfilters');
      } else {
        // swiped down
        overlay('closeoverlayfilters');
      }  
    }

    initialX = null;
    initialY = null;

	e.stopImmediatePropagation();
    e.preventDefault();
  };
  
  // Swipe on info overlay
  function moveTouchOverlayInfo(e) {
    if (initialX === null) {
      return;
    }

    if (initialY === null) {
      return;
    }

    var currentX = e.touches[0].clientX;
    var currentY = e.touches[0].clientY;

    var diffX = initialX - currentX;
    var diffY = initialY - currentY;

    if (Math.abs(diffX) > Math.abs(diffY)) {
      // sliding horizontally
      if (diffX > 0) {
        // swiped left
        console.log("nothing to swipe here");
      } else {
        // swiped right
        console.log("nothing to swipe here");
      }  
    } else {
      // sliding vertically
      if (diffY > 0) {
        // swiped up
		overlay('closeoverlayinfo');
      } else {
        // swiped down
        overlay('closeoverlayinfo');
      }  
    }

    initialX = null;
    initialY = null;

	e.stopImmediatePropagation();
    e.preventDefault();
  };