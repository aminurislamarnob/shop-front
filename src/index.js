import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

registerBlockType('shop-front/settings-page', {
    title: 'Settings Page',
    icon: 'admin-generic',
    category: 'widgets',
    edit: () => {
        const [setting, setSetting] = useState('');

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Custom Setting"
                            value={setting}
                            onChange={(value) => setSetting(value)}
                        />
                    </PanelBody>
                </InspectorControls>
                <div>Your Custom Setting: {setting}</div>
            </>
        );
    },
    save: () => {
        // Block is not saving anything to the content, only for settings page
        return null;
    }
});
