
var UEFlipbox = function(flipboxID){
  
  var g_isWasHover = false;  
  var g_objFlipBox;
  var g_isHoverMode;
  var g_objTriggerFront, g_objTriggerBack;
  var g_backEventType;
  var g_dataCloseOnBody;

  /**
   * turn actually the box
   */
  function turnOverActually(isBack){
	  
	  if(!isBack)
		  var isBack = g_objFlipBox.hasClass("uc-show");
	  
	  flipbox = g_objFlipBox[0];
	  
	  //turn to front
     if( isBack ) {
    	flipbox.classList.remove('uc-show');
    	flipbox.classList.add('uc-hide');
    	
    	return(true);
     } 
    
     //turn to back
     flipbox.classList.remove('uc-hide');
     flipbox.classList.add('uc-show');
	  
  }
  
  /**
   * show the back panel
   */
  function turnOverFlipBox(event){
	  	  
	  var objClicked = jQuery(event.target);

	  var isHoverEvent = event.type != "click";
	  
	  //for mobile mode
	  if(isHoverEvent == true)
		  g_isWasHover = true;

	  var isFrontClicked = isHoverEvent == false && objClicked.hasClass("ue-flip-box__panel--front");
	  
	  //don't allow to click on front when trigger button exists
	  if(isFrontClicked == true && g_objTriggerFront.length == true)
		  return(true);
	  	  
	  var flipbox = g_objFlipBox[0];
	  
	  var isBack = flipbox.classList.contains('uc-show');
	  

	  //don't allow clicks on desktop
	  if(g_isHoverMode == true){
		  
		  if(isHoverEvent == false && g_isWasHover == true)
			  return(true);
		  
	  }else{
		  	//click only mode - don't allow hover
		  
		  if(isHoverEvent == true){
			  
			  //if no trigger button on back - turn by mouse out
			  if(isBack == true && g_backEventType == "hover" && event.type == "mouseleave" && g_dataCloseOnBody == false){
				  turnOverActually(isBack);
			  }
			  			  
			  return(true);		  
		  }else{		
			  	
			  //don't allow hover on click on trigger mode, on desktop only. on mobile allowed click
			  
			  if(isBack == true && g_backEventType == "hover" && g_isWasHover == false)
				  return(true);
		  }
		  
	  }

    //don't turn back on mouseleave
    if(isBack == false && event.type == "mouseleave")
    return(true);
      
          
    turnOverActually(isBack);
   	  
	 
  }
	  
	/**
	 * init the events
	 */
	function initFlipBox(id){
		
		const isTouchDevice = 'ontouchstart' in window ? true : false

		var objFlipboxWrapper = jQuery(id);
    g_dataCloseOnBody = jQuery(flipboxID).data('closeonbody')

		if(objFlipboxWrapper.length == 0){
			console.log("flipbox not found: "+id);
			return(false);
		}
		
		g_objFlipBox = objFlipboxWrapper.find(".ue-flip-box__container");
		
		g_isHoverMode = g_objFlipBox.hasClass("ue-flip-box__container--hover");
		
		g_backEventType = "hover";
		if(g_isHoverMode == false)
			g_backEventType = "click";
		
		g_objTriggerFront = g_objFlipBox.find(".ue-flip-box__front-trigger");
		g_objTriggerBack = g_objFlipBox.find(".ue-flip-box__back-trigger");
		
		if(g_objTriggerBack.length == false)
			g_backEventType = "hover";
		
		if(g_objTriggerFront.length && g_objTriggerBack.length){
			g_objTriggerBack.on("click", turnOverFlipBox);
			g_objTriggerFront.on("click", turnOverFlipBox);
		}else{
			if ( isTouchDevice ) {
				g_objFlipBox.on("click mouseenter mouseleave", turnOverFlipBox);
			} else {

				g_objFlipBox.on("click mouseenter mouseleave focusin focusout", turnOverFlipBox);

        if(g_dataCloseOnBody == true){

          jQuery('body').on('click', function(event){

            var objTarget = jQuery(event.target);
            var objParent = objTarget.parents('.ue-flip-box');
            
            var flipbox = g_objFlipBox[0];	  
            var isBack = flipbox.classList.contains('uc-show');

            if(!objParent.length && isBack == true){
               turnOverActually(isBack)
            }

          })
        }

			}
		}

	}

	initFlipBox(flipboxID);


	// turn back visibility of back panel
  	setTimeout( function() { jQuery('.ue-flip-box__panel--back').css('visibility', 'visible'); },1000);
}
