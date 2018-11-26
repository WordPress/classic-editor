( function() {
	const { get } = lodash;
	const { createElement } = wp.element;
	const { PluginMoreMenuItem } = wp.editPost;
	const { addQueryArgs } = wp.url;
	const { registerPlugin } = wp.plugins;

	registerPlugin( 'classic-editor-add-submenu', {
		render() {
			const url = addQueryArgs( document.location.href, { 'classic-editor': null } );
			const linkText = get( window, [ 'classicEditorPluginL10n', 'linkText' ] ) || 'Switch to Classic Editor';

			return createElement(
				PluginMoreMenuItem,
				{
					icon: 'editor-kitchensink',
					url: url,
				},
				linkText
			);
		},
	} );
} )();
