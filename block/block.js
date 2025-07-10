const { registerBlockType } = wp.blocks;
const { TextControl } = wp.components;
const { InspectorControls } = wp.blockEditor;

registerBlockType('gde/google-drive', {
    title: 'Google Drive Embed',
    icon: 'google', // or use a custom dashicon
    category: 'embed',
    attributes: {
        link: {
            type: 'string',
            default: ''
        },
        title: {
            type: 'string',
            default: 'Documento'
        }
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;

        return [
            <InspectorControls>
                <div className="gde-inspector-controls">
                    <TextControl
                        label="Google Drive Link"
                        value={attributes.link}
                        onChange={(link) => setAttributes({ link })}
                    />
                    <TextControl
                        label="Title"
                        value={attributes.title}
                        onChange={(title) => setAttributes({ title })}
                    />
                </div>
            </InspectorControls>,
            <div className="gde-preview">
                {attributes.link ?
                    <p>Google Drive Embed: {attributes.title}</p> :
                    <p>Insert Google Drive Link</p>
                }
            </div>
        ];
    },
    save: function() {
        return null; // Dynamic blocks return null in save
    }
});