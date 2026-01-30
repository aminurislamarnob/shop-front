document.addEventListener('DOMContentLoaded', function () {
	console.log(wp.blocks);
	const block = wp.blocks.getBlockType('storesuite/settings-page');
	// if (block) {
	wp.blocks.renderTo(
		wp.element.createElement(block.edit),
		document.getElementById('storesuite-settings')
	);
	// } else {
	//     console.error('Block type "storesuite/settings-page" not found.');
	// }
});
