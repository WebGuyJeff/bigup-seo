/**
 * Perform robots.txt file create/delete requests.
 */
const robotsFileRequests = () => {

	let wpInlinedVars,
		createButton,
		deleteButton

	/**
	 * Set button event listeners.
	 */
	const init = () => {
		if ( typeof bigupSeoWpInlinedScript === 'undefined' ) return
		wpInlinedVars = bigupSeoWpInlinedScript
		createButton = document.querySelector( '[data-action="create"]' )
		deleteButton = document.querySelector( '[data-action="delete"]' )
		if ( ! createButton || ! deleteButton ) return
		createButton.addEventListener( 'click', submitCreateDelete )
		deleteButton.addEventListener( 'click', submitCreateDelete )
	}


	/**
	 * Submit a request.
	 */
	async function submitCreateDelete( event ) {
		event.preventDefault()

		const action = event.target.getAttribute( 'data-action' )

		// Fetch params.
		const { restRobotsURL, restNonce } = wpInlinedVars
		const fetchOptions = {
			method: "POST",
			headers: {
				"X-WP-Nonce" : restNonce,
				"Accept"     : "application/json"
			},
			body: JSON.stringify( { action: action } ),
		}
		const controller = new AbortController()
		const abort = setTimeout( () => controller.abort(), 14000 )

		try {

			const response = await fetch( restRobotsURL, { ...fetchOptions, signal: controller.signal } )
			clearTimeout( abort )
			const result     = await response.json()
			const fileExists = result.exists
			if ( ! response.ok ) throw result
			createButton.disabled = fileExists
			deleteButton.disabled = ! fileExists

		} catch ( error ) {
			console.error( error )

		}
	}


	// Initialise on 'doc ready'.
	let docReady = setInterval( () => {
		if ( document.readyState === 'complete' ) {
			clearInterval( docReady )
			init()
		}
	}, 100 )
}

export { robotsFileRequests }
