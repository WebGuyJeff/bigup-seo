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
	let title = input.value
	if ( title.length > 60 ) {
		title = input.value.substring( 0, 60 ) + ' ...'
	}
	output.innerHTML = title
}

const previewDescription = ( input, output ) => {
	let description = input.value
	if ( description.length > 158 ) {
		description = input.value.substring( 0, 158 ) + ' ...'
	}
	output.innerHTML = description
}

export { registerSERP }
