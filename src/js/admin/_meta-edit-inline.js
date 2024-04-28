import { __ } from '@wordpress/i18n'
import { registerSERP } from './_google-serp'

/**
 * Handle inline editing of page meta options.
 */
const metaEditInline = () => {

	let wpInlinedVars

	/**
	 * Set the controls up.
	 */
	const initialise = () => {

		wpInlinedVars = bigupSeoWpInlinedScript

		// Edit buttons.
		if ( document.querySelector( '.metaOptions' ) === 'undefined' ) return
		[ ...document.querySelectorAll( '.inlineEditButton' ) ].forEach ( editButton => {
			editButton.addEventListener( 'click', editButtonClick )
		} );

		// Reset buttons.
		[
			...document.querySelectorAll( '.inlineResetButton' ),
			...document.querySelectorAll( '.resetButton' )
		].forEach ( resetButton => {
			resetButton.addEventListener( 'click', resetButtonClick )
		} )
	}


	/**
	 * Handle edit button clicks.
	 * 
	 * @param {HTMLElement} editButton The event target button element.
	 */
	const editButtonClick = ( event ) => {
		resetTables()
		const editButton = event.currentTarget
		doInlineEditRow( editButton )
	}


	/**
	 * Handle reset button clicks.
	 * 
	 * @param {HTMLElement} resetButton The event target button element.
	 * @returns 
	 */
	const resetButtonClick = ( event ) => {
		const resetButton = event.currentTarget
		if ( resetButton.classList.contains( 'inlineResetButton' ) ) {
			resetTables()
		} 
		const form        = doInlineEditRow( resetButton )
		if ( ! form ) return
		const onFormResetButton = form.querySelector( '.resetButton' )
		const submitButton      = form.querySelector( '.submitButton' )
		onFormResetButton.style.display = 'none'
		submitButton.innerHTML = __( 'Reset', 'bigup-seo' )
		submitButton.classList.add( 'reset' )
		doFormNotice( form, 'notice-error', [ __( 'Reset SEO meta to defaults for this page?', 'bigup-seo' ) ] )
	}


	/**
	 * Display the edit form.
	 * 
	 * @param 	{HTMLElement} 	clickedButton 	The clicked button element.
	 * @returns {HTMLElement} 	The form element made visible by the button click.
	 */
	const doInlineEditRow = ( clickedButton ) => {
		let editRow
		if ( clickedButton.classList.contains( 'resetButton' ) ) {
			// On-form reset button.
			editRow = clickedButton.closest( 'tr' )

		} else {
			// All other buttons.
			const infoRow   = clickedButton.closest( 'tr' )
			const editRowId = infoRow.getAttribute( 'data-edit-id' )
			editRow = document.querySelector( '#' + editRowId )
			if ( undefined === editRow ) {
				console.error( 'Element with ID "' + editRowId + '" not found.' )
				return false
			}
			infoRow.style.display = 'none'
			editRow.style.display = 'table-row'
			clickedButton.setAttribute( 'aria-expanded', 'true' )
			readyEditRow( editRow )
		}
		const form = editRow.querySelector( 'form' )
		return form
	}


	/**
	 * Ready the edit controls when the row is toggled by the user.
	 *
	 * @param {HTMLElemement} tr The table row containing the edit form.
	 */
	const readyEditRow = ( tr ) => {
		colspanUpdate( tr )
		resizeObserver.observe( tr )
		tr.querySelector( '.submitButton' ).addEventListener( 'click', metaRequest )
		tr.querySelector( '.cancelButton' ).addEventListener( 'click', cancelButtonClick );
		[
			...tr.querySelectorAll( 'input' ),
			...tr.querySelectorAll( 'textarea' )
		].forEach ( input => {
			attachValidationListener( input )
		} )
		// Google SERP preview.
		registerSERP(
			tr.querySelector( '.serp' ),
			tr.querySelector( '.serp_titleIn' ),
			tr.querySelector( '.serp_descriptionIn' ),
		)
	}


	/**
	 * Attach a cancel function listener.
	 *
	 * @param {HTMLElement} button Element to attach the listener to.
	 */
	const cancelButtonClick = () => {
		resetTables()
	}


	/**
	 * Perform a fetch request to manipulate meta settings.
	 */
	const metaRequest = async ( event ) => {
		event.preventDefault()
		const submitButton = event.currentTarget
		const form         = submitButton.closest( 'form' )
		const resetFlag    = form.querySelector( '.resetFlag' )

		if ( submitButton.classList.contains( 'reset' ) ) {
			resetFlag.value = 1
		}

		// Get the form entries.
		const formData = new FormData( form )

		// Store the form entries.
		const entries = {}
		formData.forEach( ( value, key ) => entries[ key ] = value )

		// Fetch params.
		const { restSeoMetaURL, restNonce } = wpInlinedVars
		const fetchOptions = {
			method: "POST",
			headers: {
				"X-WP-Nonce"  : restNonce,
				"Accept"      : "application/json"
			},
			body: formData,
		}
		const controller = new AbortController()
		const abort = setTimeout( () => controller.abort(), 6000 )

		try {
			submitButton.disabled = true
			doFormNotice( form, 'notice-info', [ __( 'Please wait...', 'bigup-seo' ) ] )
			const response = await fetch( restSeoMetaURL, { ...fetchOptions, signal: controller.signal } )
			clearTimeout( abort )
			let result = {}
			if ( ! isValidJSON( response ) ) {


				console.log( '! isValidJSON' )
				console.log( response )



				// Catch errors caused by non-JSON response.
				result = {
					"ok": false,
					"messages": [ __( 'Unexpected response from server.', 'bigup-seo' ) ]
				}
			} else {
				result = await response.json()
			}

			// Display feedback.
			if ( result.ok ) {
				const messages = [
					__( 'Saved', 'bigup-seo' ),
					...result.messages
				]
				doFormNotice( form, 'notice-success', messages )

				// Update the inline table row data.
				const editRow         = form.closest( 'tr' )
				const infoRow         = document.querySelector( '[data-edit-id="' + editRow.id + '"]' )
				const inlineMetaTitle = infoRow.querySelector( '.inlineMetaTitle > span' )
				const inlineMetaDesc  = infoRow.querySelector( '.inlineMetaDesc > span' )
				if ( entries[ 'meta_title' ] ) {
					inlineMetaTitle.textContent = entries[ 'meta_title' ]
				}
				if ( entries[ 'meta_description' ] ) {
					inlineMetaDesc.textContent = entries[ 'meta_description' ]
				}
			} else {
				const messages = [
					__( 'Error', 'bigup-seo' ),
					...result.messages
				]
				doFormNotice( form, 'notice-error', messages )
			}
		} catch ( error ) {
			console.error( error )
		} finally {
			resetFlag.value = ''
			submitButton.disabled = false
		}
	}


	/**
	 * Test if a string is valid JSON.
	 */
	const isValidJSON = ( string ) => {
		try {
			const result = JSON.parse( string )
			if ( result && typeof result === "object" ) {
				return true
			}
		}
		catch ( e ) {
			return false
		}
	}


	/**
	 * Regex patterns to test for true/false format matching.
	 */
	const regexPatterns = {
		'seoTitle':       /^.{30,60}$/,
		'seoDescription': /^.{50,155}$/,
		'url':            /^(https):\/\/([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}(\/[-a-z0-9%_.~+]*)*(\?[;&a-z0-9%_.~+=-]*)?(#[-a-z0-9_]*)?$/,
	}


	/**
	 * Attach a listener to validate input and display recommendations.
	 *
	 * @param {HTMLElement} input The form input element.
	 */
	const attachValidationListener = ( input ) => {
		const type = input.getAttribute( 'data-validation-ref' )
		let regex
		let message
		switch ( type ) {
			case 'title':
				regex = new RegExp( regexPatterns.seoTitle, 'u' )
				message = __( 'To ensure titles are long enough for Google to consider using them, between 30 and 60 characters is recommended. Google starts to cut off the title tag after 50-60 characters.', 'bigup-seo' )
				break

			case 'description':
				regex = new RegExp( regexPatterns.seoDescription, 'u' )
				message = __( 'Google generally truncates snippets to ~155-160 characters. To keep descriptions sufficiently descriptive, between 50 and 160 characters is recommended.', 'bigup-seo' )
				break

			case 'canonical':
				regex = new RegExp( regexPatterns.url, 'u' )
				message = __( 'The canonical URL must be a valid URL with the protocol (https) specified. SSL (https) is strongly recommended over plain "http".', 'bigup-seo' )
				break
				
			default:
				regex = new RegExp( /.*/, 'u' )
				// Allow anything.
		}

		input.addEventListener(
			'keyup',
			function () {
				const string = input.value
				if ( regex.test( string ) || string === '' ) {
					removeInputMessage( input )
				} else {
					insertInputMessage( input, message )
				}
			}
		)
	}


	/**
	 * Insert an input element mesage.
	 *
	 * @param {HTMLElement} input 	The input element. 
	 * @param {string} 		text 	The text to display.
	 */
	const insertInputMessage = ( input, text ) => {
		const label = input.closest( 'label' )
		if ( label && label.querySelector( '.inputMessage' ) ) {
			return
		}
		const div     = document.createElement( 'div' )
		const message = document.createTextNode( text )
		div.appendChild( message )
		div.classList.add( 'inputMessage' )
		input.after( div )
		label.classList.add( 'messageActive' )
	}


	/**
	 * Remove an input element mesage.
	 *
	 * @param {HTMLElement} input The input element.
	 */
	const removeInputMessage = ( input ) => {
		const label = input.closest( 'label' )
		if ( ! label ) return
		const div = label.querySelector( '.inputMessage' )
		if ( div ) {
			div.remove()
			label.classList.remove( 'messageActive' )
		}
	}


	/**
	 * Reset tables to their initial state.
	 */
	const resetTables = () => {
		[ ...document.querySelectorAll( '.infoRow' ) ].forEach ( tr => {
			tr.style.display = 'table-row'
		} );
		[ ...document.querySelectorAll( '.editRow' ) ].forEach ( tr => {
			tr.style.display = 'none'
			resizeObserver.unobserve( tr )
		} );
		[
			...document.querySelectorAll( '.inlineEditButton' ),
			...document.querySelectorAll( '.inlineResetButton' )
		].forEach ( button => {
			button.setAttribute( 'aria-expanded', 'false' )
		} );
		[ ...document.querySelectorAll( '.submitButton' ) ].forEach ( button => {
			button.classList.remove( 'reset' )
		} );
		[ ...document.querySelectorAll( '.resetButton' ) ].forEach ( button => {
			button.style.display = 'block'
		} )
		
		removeNotices()
	}


	/**
	 * Display a notice to the user.
	 *
	 * @param {HTMLElement} form      The form element.
	 * @param {string}      className Class name to use.
	 * @param {array}       message   Messages to insert.
	 */
	const doFormNotice = ( form, className, messages ) => {
		removeNotices()
		const div    = document.createElement( 'div' )
		messages.forEach ( message => {
			const p      = document.createElement( 'p' )
			const strong = document.createElement( 'strong' )
			strong.innerText = message
			p.insertAdjacentElement( 'afterbegin', strong )
			div.insertAdjacentElement( 'beforeend', p )
		} )
		div.classList.add( 'notice', className )
		form.querySelector( '.notices' ).insertAdjacentElement( 'afterbegin', div )
	}


	/**
	 * Remove any existing notices.
	 */
	const removeNotices = () => {
		[ ...document.querySelectorAll( 'div.notice' ) ].forEach ( notice => {
			notice.remove()
		} )
	}


	/**
	 * Update the edit row cell to span all columns set by the table.
	 *
	 * @param {HTMLElement} tableRow The table row element.
	 */
	const colspanUpdate = ( tableRow ) => {
		const tableHeadings = tableRow.closest( 'table' ).querySelector( 'thead' ).querySelectorAll( 'th' )
		let colCount = 0;
		[ ...tableHeadings ].forEach ( th => {
			const style = getComputedStyle( th )
			if ( style.display !== 'none' ) {
				colCount += 1
			}
		} )
		if ( colCount === 1 ) {
			tableRow.classList.add( 'singleCol' )
		} else {
			tableRow.classList.remove( 'singleCol' )
		}
		tableRow.querySelector( 'td' ).setAttribute( 'colspan', colCount )
	}


	/**
	 * Watch for window resize and update colspan when detected.
	 *
	 * @param {HTMLCollection} entries The elements to update.
	 */
	const resizeObserver = new ResizeObserver( ( entries ) => {
		colspanUpdate( entries[ 0 ].target )
	} )


	/**
	 * Initialise.
	 */
	const docLoaded = setInterval( () => {
		if ( document.readyState === 'complete' ) {
			clearInterval( docLoaded )
			initialise()
		}
	}, 100 )
}
 
export { metaEditInline }
 
