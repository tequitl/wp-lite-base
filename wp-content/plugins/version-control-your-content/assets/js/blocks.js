import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';

// This code registers a new plugin for the WordPress editor sidebar
// It uses the @wordpress/plugins package to register the plugin
// The @wordpress/i18n package is used for internationalization

const VersionControlSidebar = () => {
    /*return (
        <div className="plugin-version-control-sidebar custom-sidebar">
            {wp.metaboxes.renderMetabox({
                id: 'vcyc_meta_box',
                postType: 'post', // Adjust this if you need to support other post types
            })}
        </div>
    );*/
    return '';
};

//Add comment here.
registerPlugin('version-control-sidebar', {
    render: VersionControlSidebar,
}); 