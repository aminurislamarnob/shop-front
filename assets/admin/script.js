document.addEventListener('DOMContentLoaded', function() {
    console.log(wp.blocks);
    const block = wp.blocks.getBlockType('shop-front/settings-page');
    // if (block) {
        wp.blocks.renderTo(
            wp.element.createElement(block.edit),
            document.getElementById('shop-front-settings')
        );
    // } else {
    //     console.error('Block type "shop-front/settings-page" not found.');
    // }
});