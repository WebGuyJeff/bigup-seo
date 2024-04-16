/**
 * Handle inline editing of page meta options.
 */
const metaEditInline = () => {

	const initialise = () => {

		if ( document.querySelector( '.metaOptions' ) === 'undefined' ) return

		[ ...document.querySelectorAll( '.inlineEditButton' ) ].forEach ( editButton => {
			editButton.addEventListener(
				'click',
				function () {
					// resetTable()
					displayInlineEditForm( this )
				}
			)
		} );
		[ ...document.querySelectorAll( '.inlineResetButton' ) ].forEach ( resetButton => {
			resetButton.addEventListener(
				'click',
				function () {
					insertInlineResetForm( this )
				}
			)
		} )


	}
	

	const displayInlineEditForm = ( button ) => {
		const editRowId = button.closest( 'tr' ).getAttribute( 'data-edit-id' )
		const editRow   = document.querySelector( '#' + editRowId )
		editRow.style.display = 'table-row'
		button.setAttribute( 'aria-expanded', 'true' )
		return editRow
	}


	const insertInlineResetForm = ( resetButton ) => {
		const form         = displayInlineEditForm( resetButton )
		const submitButton = form.querySelector( '#submitButton' )
		const legend       = form.querySelector( 'legend' )

		resetButton.setAttribute( 'aria-expanded', 'true' )
		// Need to set aria controls.

		legend.innerHTML = 'Are you sure you want to reset all custom meta for this page?'

		submitButton.innerHTML = 'Reset'
		submitButton.classList.add( 'reset' )
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


	const readyForm = ( formRow ) => {
		colspanUpdate( formRow )
		resizeObserver.observe( formRow )
		attachFormResetListener( formRow.querySelector( '#cancelButton' ) )
		attachNameValidationListener( formRow.querySelector( '#name_singular' ) )
		attachNameValidationListener( formRow.querySelector( '#name_plural' ) )
		attachSubmitListener( formRow.querySelector( '#submitButton' ) )
	}


	const attachFormResetListener = ( button ) => {
		button.addEventListener(
			'click',
			function () {
				resetTable()
			}
		)
	}


	const attachNameValidationListener = ( input ) => {
		input.addEventListener(
			'keyup',
			function () {

				const string   = input.value
				const regex    = new RegExp( input.getAttribute( 'pattern' ), 'ug' )
				let okChars    = string.match( regex )
				const validMsg = 'This field can only contain alphanumeric characters, spaces and hyphens.'

				if ( Array.isArray( okChars ) ) {
					const joined = okChars.join( '' )
					okChars = joined
				} else {
					input.value = ''
					doInputMessage(
						input,
						validMsg
					)
					return
				}

				if ( string.length !== okChars.length ) {
					doInputMessage(
						input,
						validMsg
					)
				}

				input.value = cleanSpacesAndHyphens( okChars )
			}
		)
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
								doInputMessage(
									form.querySelector( '#name_singular' ),
									'Post key duplication. Please choose a unique name.'
								)
								areDuplicates = true
							}
							i++
						}
					}

					if ( postValueExists( data, 'name_singular', postName ) && formType !== 'edit' ) {
						doInputMessage(
							form.querySelector( '#name_singular' ),
							'Post singular name already exists. Please choose a unique name.'
						)
						areDuplicates = true
					}

					if ( postValueExists( data, 'name_plural', postsName ) && formType !== 'edit' ) {
						doInputMessage(
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


	const cleanSpacesAndHyphens = ( string ) => {
		const singleHyphens       = string.replace( /--+/g, '-' )
		const singleSpaces        = singleHyphens.replace( /  +/g, ' ' )
		const noLeadSpaces        = singleSpaces.trimStart()
		const max1TrailingSpace   = noLeadSpaces.replace( /\s+$/g, ' ' )
		return max1TrailingSpace
	}


	const doInputMessage = ( input, text ) => {
		if ( !! input.closest( 'label' ).querySelector( '.inputMessage' ) === true ) {
			return
		}
		const div     = document.createElement( 'div' )
		const message = document.createTextNode( text )
		div.appendChild( message )
		div.classList.add( 'inputMessage' )
		input.after( div )
		const removeDiv = setTimeout( () => {
			div.remove()
			clearTimeout( removeDiv )
		}, 5000 )
	}


	const resetTable = () => {
		[ ...document.querySelectorAll( '.customPostTypeRow' ) ].forEach ( tr => {
			tr.style.display = 'table-row'
		} );
		[ ...document.querySelectorAll( '.inlineEditButton' ) ].forEach ( editButton => {
			editButton.setAttribute( 'aria-expanded', 'false' )
		} );
		[ ...document.querySelectorAll( '.inlineDeleteButton' ) ].forEach ( deleteButton => {
			deleteButton.setAttribute( 'aria-expanded', 'false' )
		} );
		[ ...document.querySelectorAll( '.editActive' ) ].forEach ( tr => {
			resizeObserver.unobserve( tr )
			tr.remove()
		} )
	}


	const colspanUpdate = ( tableRow ) => {
		const colCount = tableRow.closest( 'table' ).querySelector( 'tr' ).querySelectorAll( 'th' ).length
		tableRow.querySelector( 'td' ).setAttribute( 'colspan', colCount )
	}


	const resizeObserver = new ResizeObserver( ( entries ) => {
		colspanUpdate( entries[ 0 ].target )
	} )


	const docLoaded = setInterval( () => {
		if ( document.readyState === 'complete' ) {
			clearInterval( docLoaded )
			initialise()
		}
	}, 100 )
}
 
export { metaEditInline }
 
