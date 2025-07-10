const { registerBlockType } = wp.blocks;
const { TextControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('gde/google-drive', {
    title: 'Google Drive Embed',
    icon: 'media-document',
    category: 'embed',
    attributes: {
        link: { type: 'string' },
        title: { type: 'string' },
    },
    edit: ({ attributes, setAttributes }) => {
        return (
            <Fragment>
                <TextControl
                    label="Google Drive Link"
                    value={attributes.link}
                    onChange={(val) => setAttributes({ link: val })}
                />
                <TextControl
                    label="Title"
                    value={attributes.title}
                    onChange={(val) => setAttributes({ title: val })}
                />
            </Fragment>
        );
    },
    save: () => null, // Rendered server-side
});
