const { registerBlockType } = wp.blocks;
const { TextControl } = wp.components;
const { Fragment } = wp.element;

registerBlockType('gde/google-drive', {
    title: 'Google Drive Embed',
    icon: 'admin-media',
    category: 'embed',
    attributes: {
        link: { type: 'string' },
        title: { type: 'string' },
    },
    edit: ({ attributes, setAttributes }) => {
        const { link, title } = attributes;
        return (
            <Fragment>
                <TextControl
                    label="Google Drive Link"
                    value={link}
                    onChange={(val) => setAttributes({ link: val })}
                />
                <TextControl
                    label="Title"
                    value={title}
                    onChange={(val) => setAttributes({ title: val })}
                />
            </Fragment>
        );
    },
    save: () => null, // Rendered via PHP
});
