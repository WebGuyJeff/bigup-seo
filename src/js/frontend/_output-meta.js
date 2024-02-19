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

		const panel = document.createElement( 'div' )
		const print = document.createElement( 'div' )
		const close = document.createElement( 'button' )

		panel.classList.add( 'metaPanel' )
		panel.appendChild( close )
		panel.appendChild( print )
		close.setAttribute( 'style', 'margin-left: auto;' )
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

		let styles = 'position: fixed; '
			styles += 'bottom: 0; '
			styles += 'left: 0; '
			styles += 'right: 0; '
			styles += 'width: 100%; '
			styles += 'height: 50%; '
			styles += 'background: #fff; '
			styles += 'border-top: solid 0.15rem #333; '
			styles += 'padding: 1rem; '
			styles += 'z-index: 20; '

		panel.setAttribute( 'style', styles )
		document.body.appendChild( panel )



		console.log( panel )

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
