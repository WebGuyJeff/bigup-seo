import { __ } from '@wordpress/i18n'
import { registerSERP } from './_google-serp'


/**
 * Handle inline editing of page meta options.
 */
const metaEditInline = () => {


	/**
	 * Set the controls up.
	 */
	const initialise = () => {

		// Edit buttons.
		if ( document.querySelector( '.metaOptions' ) === 'undefined' ) return
		[ ...document.querySelectorAll( '.inlineEditButton' ) ].forEach ( editButton => {
			editButton.addEventListener(
				'click',
				function () {
					resetTables()
					editButtonClick( this )
				}
			)
		} );

		// Reset buttons.
		[ ...document.querySelectorAll( '.inlineResetButton' ) ].forEach ( resetButton => {
			resetButton.addEventListener(
				'click',
				function () {
					resetButtonClick( this )
				}
			)
		} )
	}


	/**
	 * Handle edit button clicks.
	 * 
	 * @param {HTMLElement} editButton The event target button element.
	 */
	const editButtonClick = ( editButton ) => {
		doInlineEditRow( editButton )
	}


	/**
	 * Handle reset button clicks.
	 * 
	 * @param {HTMLElement} resetButton The event target button element.
	 * @returns 
	 */
	const resetButtonClick = ( resetButton ) => {
		const form = doInlineEditRow( resetButton )
		if ( ! form ) return
		const submitButton = form.querySelector( '.submitButton' )
		const resetFlag    = form.querySelector( '.resetFlag' )
		resetFlag.checked = true
		submitButton.innerHTML = __( 'Reset', 'bigup-seo' )
		submitButton.classList.add( 'reset' )
	}


	/**
	 * Display the edit form.
	 * 
	 * @param 	{HTMLElement} 	clickedButton 	The clicked button element.
	 * @returns {HTMLElement} 	The form element made visible by the button click.
	 */
	const doInlineEditRow = ( clickedButton ) => {
		const infoRow   = clickedButton.closest( 'tr' )
		const editRowId = infoRow.getAttribute( 'data-edit-id' )
		const editRow   = document.querySelector( '#' + editRowId )
		if ( undefined === editRow ) {
			console.error( 'Element with ID "' + editRowId + '" not found.' )
			return false
		}
		infoRow.style.display = 'none'
		editRow.style.display = 'table-row'
		clickedButton.setAttribute( 'aria-expanded', 'true' )
		readyEditRow( editRow )
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
		attachSubmitListener( tr.querySelector( '.submitButton' ) )
		attachCancelListener( tr.querySelector( '.cancelButton' ) );
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
	const attachCancelListener = ( button ) => {
		button.addEventListener(
			'click',
			function () {
				resetTables()
			}
		)
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


	const postValueExists = ( data, key, value ) => {
		if ( [ data, key, value ].includes( undefined ) ) return false
		let exists = false
		Object.keys( data ).forEach( post => {
			if ( value === data[ post ][ key ] ) {
				exists = true
			}
		} )
		return exists
	}


	const attachSubmitListener = ( button ) => {
		button.addEventListener(
			'click',
			function () {

				const form = button.closest( 'form' )

				if ( button.classList.contains( 'delete' ) ) {
					form.submit()
					return
				}

				const formType       = form.getAttribute( 'data-type-form' )
				const postName       = form.querySelector( '#name_singular' ).value
				const postsName      = form.querySelector( '#name_plural' ).value
				const hiddenInput    = form.querySelector( '#post_type' )
				let postType       = hiddenInput.value
				const inputsAreValid = form.reportValidity()
				const data           = JSON.parse( sessionStorage.getItem( 'bigupCPTOption' ) )
				let areDuplicates  = false

				if ( !! data === true ) {

					if ( inputsAreValid && formType === 'new' ) {
						let i = 1
						while ( postValueExists( data, 'post_type', postType ) ) {
							const noAppendedNum = postType.replace( /-\d$/g, '' )
							const croppedTo18   = noAppendedNum.substring( 0, 18 )
							postType = croppedTo18 + '-' + i
							if ( i === 10 ) {
								insertInputMessage(
									form.querySelector( '#name_singular' ),
									'Post key duplication. Please choose a unique name.'
								)
								areDuplicates = true
							}
							i++
						}
					}

					if ( postValueExists( data, 'name_singular', postName ) && formType !== 'edit' ) {
						insertInputMessage(
							form.querySelector( '#name_singular' ),
							'Post singular name already exists. Please choose a unique name.'
						)
						areDuplicates = true
					}

					if ( postValueExists( data, 'name_plural', postsName ) && formType !== 'edit' ) {
						insertInputMessage(
							form.querySelector( '#name_plural' ),
							'Post plural name already exists. Please choose a unique name.'
						)
						areDuplicates = true
					}

					if ( areDuplicates === true ) {
						return
					}
				}

				hiddenInput.value = postType
				form.submit()
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
 
