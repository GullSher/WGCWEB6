document.addEventListener("DOMContentLoaded", function(){ var ssLinksParent = document.querySelector( '.uagb-block-cf816395' );
ssLinksParent?.addEventListener( 'keyup', function ( e ) {
var link = e.target.closest( '.uagb-ss__link' );
if ( link && e.keyCode === 13 ) {
	handleSocialLinkClick( link );
}
});

ssLinksParent?.addEventListener( 'click', function ( e ) {
var link = e.target.closest( '.uagb-ss__link' );
if ( link ) {
	handleSocialLinkClick( link );
}
});

function handleSocialLinkClick( link ) {
var social_url = link.dataset.href;
var target = "";
if ( social_url == "mailto:?body=" ) {
	target = "_self";
}
var request_url = "";
if ( social_url.indexOf("/pin/create/link/?url=") !== -1 ) {
	request_url = social_url + encodeURIComponent( window.location.href ) + "&media=" + 'http://localhost/wgcweb6/wp-content/uploads/2023/10/30years-1.png';
} else {
	request_url = social_url + encodeURIComponent( window.location.href );
}
window.open( request_url, target );
}
 });