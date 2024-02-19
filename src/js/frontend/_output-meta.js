/**
 * Bigup SEO Output Meta
 *
 * Scrapes head meta and displays in-page for inspection.
 *
 * @package lonewolf
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright 2023 Jefferson Real
 */

const outputMeta = () => {

	function showOverlay() {

		const titleTags = document.querySelectorAll( 'title' )
		const metaTags  = document.querySelectorAll( 'meta' )

		const panel  = document.createElement( 'div' )
		const scroll = document.createElement( 'div' )
		const print  = document.createElement( 'div' )
		const header = document.createElement( 'header' )
		const title  = document.createElement( 'h3' )
		const close  = document.createElement( 'button' )

		title.innerText = 'Bigup SEO - Meta Tag Viewer'
		header.appendChild( title )
		header.appendChild( close )
		panel.appendChild( header )
		panel.appendChild( print )
		panel.classList.add( 'metaPanel' )
		print.classList.add( 'metaPanel_print' )
		close.innerText = 'Close'
		close.addEventListener( 'click', () => panel.remove() )

		titleTags.forEach( ( title ) => {
			const p = document.createElement( 'p' )
			const nodeString = title.outerHTML
			p.innerText = nodeString
			print.appendChild( p )
		} )

		metaTags.forEach( ( meta ) => {
			const p = document.createElement( 'p' )
			const nodeString = meta.outerHTML
			p.innerText = nodeString
			print.appendChild( p )
		} )

		document.body.appendChild( panel )
	}


	const escapeHTML = ( markup ) => {
    return markup
         .replace( /&/g, "&amp;" )
         .replace( /</g, "&lt;" )
         .replace( />/g, "&gt;" )
         .replace( /"/g, "&quot;" )
         .replace( /'/g, "&#039;" )
 	}


	// Poll for doc ready state
	const docLoaded = setInterval( function () {
		if ( document.readyState === 'complete' ) {
			clearInterval( docLoaded )
			showOverlay()
		}
	}, 100 )
}

export { outputMeta }
