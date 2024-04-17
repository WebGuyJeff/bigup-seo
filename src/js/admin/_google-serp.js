const registerSERP = ( serp, titleInput, descriptionInput ) => {
	titleInput.addEventListener(
		'keyup',
		function() { previewTitle( this, serp.querySelector( '.serp_title' ) ) }
	)
	descriptionInput.addEventListener(
		'keyup',
		function() { previewDescription( this, serp.querySelector( '.serp_description' ) ) }
	)
}

const previewTitle = ( input, output ) => {
	const title = input.value
	if ( title.length > 60 ) {
		title = input.value.substring( 0, 60 ) + '...'
	}
	output.innerHTML = title
}

const previewDescription = ( input, output ) => {
	const description = input.value
	if ( description.length > 150 ) {
		description = input.value.substring( 0, 150 ) + '...'
	}
	output.innerHTML = description
}

export { registerSERP }
