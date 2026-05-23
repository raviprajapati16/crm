<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= $template_data->title ?></title>
    <link rel="stylesheet" href="<?= site_url() ?>assets/plugins/grapejs/css/grapes.min.css?v0.21.10">
    <link rel="stylesheet" href="<?= site_url() ?>assets/plugins/grapejs/css/material.css">
    <link rel="stylesheet" href="<?= site_url() ?>assets/plugins/grapejs/css/tooltip.css">
    <link rel="stylesheet" href="<?= site_url() ?>assets/plugins/grapejs/css/demos.css?v2">

    <script src="<?= site_url() ?>assets/plugins/grapejs/js/grapes.min.js?v0.21.10"></script>
    <!-- <script src="./js/ckeditor/ckeditor.js"></script> -->
    <!-- <script src="<?= site_url() ?>assets/plugins/grapejs/js/ckeditor/ckeditor.js"></script> -->
    <!-- <script src="<?= site_url() ?>assets/plugins/grapejs/js/grapesjs-plugin-ckeditor/index.js"></script> -->
    <!-- <script src="https://unpkg.com/grapesjs-plugin-ckeditor@0.0.10"></script> -->
    <script src="https://unpkg.com/grapesjs-preset-newsletter@1.0.1"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<style>
    .nl-link {
        color: inherit;
    }

    .gjs-logo-version {
        background-color: #5a606d;
    }

    .cke_toolbar.cke_toolbar {
        min-height: 33px;
    }

    .gjs-pn-logo {
        display: none !important;
    }
</style>

<body>


    <div id="gjs" style="height:0px; overflow:hidden">


    </div>




    <div id="info-panel" style="display:none">
        <br />
        <svg class="info-panel-logo" xmlns="//www.w3.org/2000/svg" version="1">
            <g id="gjs-logo">
                <path d="M40 5l-12.9 7.4 -12.9 7.4c-1.4 0.8-2.7 2.3-3.7 3.9 -0.9 1.6-1.5 3.5-1.5 5.1v14.9 14.9c0 1.7 0.6 3.5 1.5 5.1 0.9 1.6 2.2 3.1 3.7 3.9l12.9 7.4 12.9 7.4c1.4 0.8 3.3 1.2 5.2 1.2 1.9 0 3.8-0.4 5.2-1.2l12.9-7.4 12.9-7.4c1.4-0.8 2.7-2.2 3.7-3.9 0.9-1.6 1.5-3.5 1.5-5.1v-14.9 -12.7c0-4.6-3.8-6-6.8-4.2l-28 16.2" style="fill:none;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-width:10;stroke:#fff" />
            </g>
        </svg>
        <br />
        <div class="info-panel-label">
            <b>GrapesJS Newsletter Builder</b> is a showcase of what/how is possible to build an editor using the
            <a class="info-panel-link gjs-four-color" target="_blank" href="https://grapesjs.com/">GrapesJS</a>
            core library
            <br /><br />
            For any tip about this demo (or newsletters construction in general) check the
            <a class="info-panel-link gjs-four-color" target="_blank" href="https://github.com/grapesjs/preset-newsletter">Newsletter Preset repository</a>
            and open an issue. For any problem with the builder itself, open an issue on the main
            <a class="info-panel-link gjs-four-color" target="_blank" href="https://github.com/grapesjs/grapesjs">GrapesJS repository</a>
            <br /><br />
            Being a free and open source project contributors and supporters are extremely welcome.
            If you like the project support it with a donation of your choice or become a backer/sponsor via
            <a class="info-panel-link gjs-four-color" target="_blank" href="https://opencollective.com/grapesjs">Open Collective</a>
        </div>
    </div>

    <div style="display: none">
        <div class="gjs-logo-cont">
            <a href="//grapesjs.com"><img class="gjs-logo" src="img/grapesjs-logo-cl.png"></a>
            <div class="gjs-logo-version"></div>
        </div>
    </div>


    <div class="ad-cont">
        <div id="native-carbon"></div>
        <script async type="text/javascript" src="<?= site_url() ?>assets/plugins/grapejs/js/carbon-v2.js"></script>

    </div>

    <?php
    $CI = &get_instance();
    ?>
    <script>
        var auto_logout_minutes = <?= get_option('auto_logout_minutes') ?>;
    </script>
    <script src="<?= site_url('assets/js/auto-logout.js?v=' . time()) ?>"></script>
    <script src="<?= site_url('assets/js/file-upload-validation.js?v=' . time()) ?>"></script>
    <script type="text/javascript">
        var lastSaveTime = Date.now();
        var host = 'https://grapesjs.com/';
        let isDirty = false;

        const dynamicVariables = [{
                label: 'Name',
                value: '{name}'
            },
            {
                label: 'Date',
                value: '{date}'
            },
        ];

        var editor = grapesjs.init({
            selectorManager: {
                componentFirst: true
            },
            clearOnRender: true,
            height: '100%',
            storageManager: {
                options: {
                    local: {
                        key: 'gjsProjectNl'
                    }
                }
            },
            assetManager: {
                upload: '<?= admin_url("email_campaign_templates/upload_image") ?>',
                uploadName: 'files',
                multiUpload: true,
                credentials: 'include',
                uploadFile: function(e) {
                    return new Promise((resolve, reject) => {
                        var files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
                        var formData = new FormData();
                        formData.append('<?php echo $this->security->get_csrf_token_name(); ?>',
                            '<?php echo $this->security->get_csrf_hash(); ?>');
                        for (var i in files) {
                            if (files[i] instanceof File) {
                                formData.append('files[]', files[i]);
                            }
                        }
                        formData.append('id', <?= $template_data->id ?>);
                        $.ajax({
                            url: '<?= admin_url("email_campaign_templates/upload_image") ?>',
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    const assets = response.urls.map(url => ({
                                        src: url,
                                        type: 'image'
                                    }));
                                    editor.AssetManager.add(assets);
                                    resolve(assets);
                                } else {
                                    editor.Modal.setContent('Error uploading image!');
                                    editor.Modal.open();
                                    setTimeout(() => editor.Modal.close(), 2000);
                                    reject('Upload error');
                                }
                            },
                            error: function(xhr, status, error) {
                                editor.Modal.setContent('Server error while uploading image!');
                                editor.Modal.open();
                                setTimeout(() => editor.Modal.close(), 2000);
                                reject(error);
                            }
                        });
                    });
                },
                params: {
                    template_id: "<?= $template_data->id ?>"
                }
            },
            container: '#gjs',
            fromElement: true,
            plugins: ['grapesjs-preset-newsletter'],
            pluginsOpts: {
                'grapesjs-preset-newsletter': {
                    modalLabelImport: 'Paste all your code here below and click import',
                    modalLabelExport: 'Copy the code and use it wherever you want',
                    codeViewerTheme: 'material',
                    importPlaceholder: '<table class="table"><tr><td class="cell">Hello world!</td></tr></table>',
                    cellStyle: {
                        'font-size': '12px',
                        'font-weight': 300,
                        'vertical-align': 'top',
                        color: 'rgb(111, 119, 125)',
                        margin: 0,
                        padding: 0,
                    }
                },
            },
        });

        // Add Variables Button to Panel

        // editor.Panels.addButton('options', {
        //     id: 'insert-variable',
        //     className: 'fa fa-tags',
        //     command: 'open-variables',
        //     attributes: {
        //         title: 'Insert Variable',
        //         'data-tooltip-pos': 'bottom',
        //     }
        // });

        editor.Commands.add('open-variables', {
            run: function(editor) {
                const modal = editor.Modal;
                let container = document.createElement('div');
                container.style.padding = '20px';
                container.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    ${dynamicVariables.map(variable => `
                        <button
                            style="padding: 8px; margin: 5px; cursor: pointer; background: #4b91e2; color: white; border: none; border-radius: 3px;"
                            data-variable="${variable.value}">
                            ${variable.label}
                        </button>
                    `).join('')}
                </div>
            `;
                container.addEventListener('click', (e) => {
                    if (e.target.tagName == 'BUTTON') {
                        const variable = e.target.getAttribute('data-variable');
                        const selectedComponent = editor.getSelected();
                        if (selectedComponent && selectedComponent.get('type') == 'text') {
                            const currentContent = selectedComponent.toHTML();
                            selectedComponent.append(variable);
                            modal.close();
                        } else {
                            editor.addComponents(`<div>${variable}</div>`);
                            modal.close();
                        }
                    }
                });

                modal.setTitle('Insert Variable');
                modal.setContent(container);
                modal.open();
            }
        });


        // Test Email Functionality
        editor.Panels.addButton('options', {
            id: 'send-preview-template-email',
            className: 'fa fa-send',
            command: 'send-preview-template-email',
            attributes: {
                title: 'Send Preview Email',
                'data-tooltip-pos': 'bottom',
            }
        });

        editor.Commands.add('send-preview-template-email', {
            run: function(editor) {
                const modal = editor.Modal;
                let container = document.createElement('div');
                container.style.padding = '20px';

                // Add email input section
                container.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <input type="email"
                        id="preview-email"
                        placeholder="Enter email address"
                        style="width: 100%; padding: 8px; margin-bottom: 5px; border: 1px solid #ddd; border-radius: 3px;">
                    <span id="email-validation" style="color: red; font-size: 12px; display: none;">Please enter a valid email address</span>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button id="send-preview-btn"
                        style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">
                        Send Preview
                    </button>
                </div>
            `;

                // Email validation function
                function validateEmail(email) {
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return re.test(email);
                }

                // Add event listeners after the modal is opened
                container.addEventListener('click', (e) => {
                    // Handle variable buttons
                    if (e.target.hasAttribute('data-variable')) {
                        const variable = e.target.getAttribute('data-variable');
                        const selectedComponent = editor.getSelected();
                        if (selectedComponent && selectedComponent.get('type') == 'text') {
                            const currentContent = selectedComponent.toHTML();
                            selectedComponent.append(variable);
                        } else {
                            editor.addComponents(`<div>${variable}</div>`);
                        }
                    }

                    // Handle send preview button
                    if (e.target.id === 'send-preview-btn') {
                        const emailInput = container.querySelector('#preview-email');
                        const validationMessage = container.querySelector('#email-validation');
                        const email = emailInput.value;

                        if (!validateEmail(email)) {
                            validationMessage.style.display = 'block';
                            return;
                        }

                        validationMessage.style.display = 'none';
                        const sendButton = e.target;
                        const originalText = sendButton.textContent;
                        sendButton.textContent = 'Sending...';
                        sendButton.disabled = true;
                        var formData = new FormData();
                        formData.append("email", email);
                        formData.append("template_id", <?= $template_data->id ?>);
                        $.ajax({
                            url: "<?= site_url('email_track/testEmailSend') ?>",
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                editor.Modal.setContent('Test email successfully send.');
                                setTimeout(() => editor.Modal.close(), 2000);
                            },
                            error: function(xhr, status, error) {
                                editor.Modal.setContent('Error test mail not send!');
                            }
                        });
                    }
                });

                // Add input validation
                const emailInput = container.querySelector('#preview-email');
                emailInput.addEventListener('input', (e) => {
                    const validationMessage = container.querySelector('#email-validation');
                    const sendButton = container.querySelector('#send-preview-btn');

                    if (!validateEmail(e.target.value)) {
                        validationMessage.style.display = 'block';
                        sendButton.disabled = true;
                        sendButton.style.opacity = '0.7';
                    } else {
                        validationMessage.style.display = 'none';
                        sendButton.disabled = false;
                        sendButton.style.opacity = '1';
                    }
                });
                modal.setTitle('Send Preview Email');
                modal.setContent(container);
                modal.open();
            }
        });

        // Add file upload button to panel
        editor.Panels.addButton('options', {
            id: 'import-template-file',
            className: 'fa fa-upload',
            command: 'import-template-file',
            attributes: {
                title: 'Import Template File',
                'data-tooltip-pos': 'bottom',
            }
        });

        // Add the import template file command
        editor.Commands.add('import-template-file', {
            run: function(editor) {
                const modal = editor.Modal;
                let container = document.createElement('div');
                container.style.padding = '20px';

                // Create file input container
                container.innerHTML = `
                        <div style="text-align: center;">
                            <div style="margin-bottom: 20px;">
                                <input type="file"
                                    id="template-file-input"
                                    accept=".html,.htm"
                                    style="display: none;">
                                <button id="upload-btn"
                                    style="padding: 12px 24px; background: #4b91e2; color: white; border: none; border-radius: 3px; cursor: pointer; margin-bottom: 10px;">
                                    Select HTML File
                                </button>
                                <div id="file-name" style="margin-top: 10px; font-size: 14px;"></div>
                            </div>
                            <div id="upload-message" style="color: #666; margin-top: 10px;"></div>
                            <button id="import-btn"
                                style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; display: none;">
                                Import Template
                            </button>
                        </div>
                    `;

                // Add event listeners after the modal is opened
                container.addEventListener('click', (e) => {
                    // Handle upload button click
                    if (e.target.id === 'upload-btn') {
                        const fileInput = container.querySelector('#template-file-input');
                        fileInput.click();
                    }

                    // Handle import button click
                    if (e.target.id === 'import-btn') {
                        const fileInput = container.querySelector('#template-file-input');
                        const file = fileInput.files[0];
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            try {
                                const htmlContent = e.target.result;

                                // Clear the current canvas
                                editor.Commands.run('core:canvas-clear');

                                // Load the HTML template
                                editor.setComponents(htmlContent);

                                // Show success message
                                const messageDiv = container.querySelector('#upload-message');
                                messageDiv.style.color = '#28a745';
                                messageDiv.textContent = 'Template imported successfully!';

                                // Close modal after short delay
                                setTimeout(() => modal.close(), 1500);

                                // Set dirty flag to indicate unsaved changes
                                isDirty = true;

                            } catch (error) {
                                const messageDiv = container.querySelector('#upload-message');
                                messageDiv.style.color = '#dc3545';
                                messageDiv.textContent = 'Error importing template. Please check the file format.';
                            }
                        };

                        reader.readAsText(file);
                    }
                });

                // Handle file selection
                container.querySelector('#template-file-input').addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    const fileNameDiv = container.querySelector('#file-name');
                    const importBtn = container.querySelector('#import-btn');
                    const messageDiv = container.querySelector('#upload-message');

                    if (file) {
                        if (file.type === 'text/html' || file.name.endsWith('.html') || file.name.endsWith('.htm')) {
                            fileNameDiv.textContent = `Selected file: ${file.name}`;
                            importBtn.style.display = 'inline-block';
                            messageDiv.textContent = '';
                            messageDiv.style.color = '#666';
                        } else {
                            fileNameDiv.textContent = '';
                            importBtn.style.display = 'none';
                            messageDiv.style.color = '#dc3545';
                            messageDiv.textContent = 'Please select a valid HTML file.';
                        }
                    }
                });

                modal.setTitle('Import Template File');
                modal.setContent(container);
                modal.open();
            }
        });



        // Let's add in this demo the possibility to test our newsletters
        var pnm = editor.Panels;
        var cmdm = editor.Commands;
        var md = editor.Modal;

        // Add info command
        var infoContainer = document.getElementById("info-panel");
        cmdm.add('open-info', {
            run: function(editor, sender) {
                var mdlClass = 'gjs-mdl-dialog-sm';
                sender.set('active', 0);
                var mdlDialog = document.querySelector('.gjs-mdl-dialog');
                mdlDialog.className += ' ' + mdlClass;
                infoContainer.style.display = 'block';
                md.open({
                    title: 'About this demo',
                    content: infoContainer,
                });
                md.getModel().once('change:open', function() {
                    mdlDialog.className = mdlDialog.className.replace(mdlClass, '');
                })
            }
        });

        // Add info button
        const iconStyle = 'style="display: block; max-width: 22px"';
        pnm.addButton('options', [{
            id: 'view-info',
            label: `<svg ${iconStyle} viewBox="0 0 24 24">
            <path fill="currentColor" d="M15.07,11.25L14.17,12.17C13.45,12.89 13,13.5 13,15H11V14.5C11,13.39 11.45,12.39 12.17,11.67L13.41,10.41C13.78,10.05 14,9.55 14,9C14,7.89 13.1,7 12,7A2,2 0 0,0 10,9H8A4,4 0 0,1 12,5A4,4 0 0,1 16,9C16,9.88 15.64,10.67 15.07,11.25M13,19H11V17H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z" />
        </svg>`,
            command: 'open-info',
            attributes: {
                'title': 'About',
                'data-tooltip-pos': 'bottom',
            },
        }]);

        // Beautify tooltips
        [
            ['sw-visibility', 'Show Borders'],
            ['preview', 'Preview'],
            ['fullscreen', 'Fullscreen'],
            ['export-template', 'Export'],
            ['undo', 'Undo'],
            ['redo', 'Redo'],
            ['gjs-open-import-template', 'Import'],
            ['gjs-toggle-images', 'Toggle images'],
            ['canvas-clear', 'Clear canvas']
        ].forEach(function(item) {
            pnm.getButton('options', item[0]).set('attributes', {
                title: item[1],
                'data-tooltip-pos': 'bottom'
            });
        });
        // [
        //   ['open-sm', 'Style Manager'],
        //   ['open-layers', 'Layers'],
        //   ['open-blocks', 'Blocks']
        // ].forEach(function(item) {
        //   pnm.getButton('views', item[0]).set('attributes', { title: item[1], 'data-tooltip-pos': 'bottom', title2: item[1] });
        //   console.log('views', item[0], pnm.getButton('views', item[0]).get('attributes'))
        // });

        var titles = document.querySelectorAll('*[title]');
        for (var i = 0; i < titles.length; i++) {
            var el = titles[i];
            var title = el.getAttribute('title');
            title = title ? title.trim() : '';
            if (!title)
                break;
            el.setAttribute('data-tooltip', title);
            el.setAttribute('title', '');
        }

        // Update canvas-clear command
        cmdm.add('canvas-clear', function() {
            if (confirm('Are you sure to clean the canvas?')) {
                editor.runCommand('core:canvas-clear')
                setTimeout(function() {
                    localStorage.clear()
                }, 0)
            }
        });

        editor.on('component:update', () => {
            isDirty = true;
        });

        editor.on('component:add', () => {
            isDirty = true;
        });

        editor.on('component:remove', () => {
            isDirty = true;
        });

        editor.on('style:update', () => {
            isDirty = true;
        });

        editor.on('component:update:content', () => {
            isDirty = true;
        });

        editor.on('component:input', (component) => {
            isDirty = true;
        });

        editor.onReady(function() {
            pnm.getButton('options', 'sw-visibility').set('active', 1);
            var logoCont = document.querySelector('.gjs-logo-cont');
            document.querySelector('.gjs-logo-version').innerHTML = 'v' + grapesjs.version;
            var logoPanel = document.querySelector('.gjs-pn-commands');
            logoPanel.appendChild(logoCont);
        });

        window.editor.on('destroy', () => {

        });

        window.editor.Commands.add('load-template', {
            run: function(editor, sender, options = {}) {
                const templateUrl = options.templateUrl || '';

                if (!templateUrl) {
                    console.error('No template URL provided');
                    return;
                }

                editor.Modal.setContent('Loading template...');
                editor.Modal.open();

                // Sync text components before loading a new template
                editor.getComponents().forEach(component => {
                    if (component.get('type') === 'text') {
                        const el = component.view.el;
                        if (el) {
                            component.set('content', el.innerHTML);
                            component.view.render();
                        }
                    }
                });

                fetch(templateUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text(); // Fetch HTML as text
                    })
                    .then(htmlContent => {
                        editor.Commands.run('core:canvas-clear'); // Clear current canvas
                        editor.setComponents(htmlContent); // Load the HTML template

                        editor.Modal.setContent('Template loaded successfully!');
                        setTimeout(() => editor.Modal.close(), 1000);
                    })
                    .catch(error => {
                        console.error('Error loading template:', error);
                        editor.Modal.setContent('Error loading template!');
                        setTimeout(() => editor.Modal.close(), 2000);
                    });
            }
        });

        window.editor.Commands.add('save-template', {
            run: function(editor, sender, options = {}) {
                return new Promise((resolve) => {
                    editor.trigger('change');
                    editor.refresh();

                    // Get HTML and CSS
                    const htmlContent = editor.getHtml();
                    const cssContent = `<style>${editor.getCss()}</style>`;

                    // Combine HTML and CSS into one file
                    const fullHtml = `<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            ${cssContent}
                        </head>
                        <body>
                            ${htmlContent}
                        </body>
                        </html>`;

                    const formData = new FormData();
                    formData.append('template', new Blob([fullHtml], {
                        type: 'text/html'
                    })); // Save as HTML
                    formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');
                    formData.append('id', "<?= $template_data->id ?>");

                    editor.Modal.setContent('Saving changes...');
                    editor.Modal.open();

                    $.ajax({
                        url: "<?= admin_url('email_campaign_templates/save') ?>",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            lastSaveTime = Date.now();
                            isDirty = false;
                            editor.Modal.setContent('Template saved successfully!');
                            setTimeout(() => editor.Modal.close(), 2000);
                            resolve();
                        },
                        error: function(xhr, status, error) {
                            console.error('Error saving template:', error);
                            editor.Modal.setContent('Error saving template!');
                            setTimeout(() => editor.Modal.close(), 2000);
                            resolve();
                        }
                    });
                });
            }
        });


        window.editor.on('load', () => {
            setTimeout(() => {
                window.editor.Commands.run('load-template', {
                    templateUrl: '<?= site_url('uploads/email_campaign_templates/' . $template_data->id . "/index.html?v=" . time()) ?>'
                });
                isDirty = false;
            }, 100);
        });

        window.editor.Panels.addButton('options', {
            id: 'save-template',
            className: 'fa fa-floppy-o',
            command: 'save-template',
            attributes: {
                title: 'Save Template',
                'data-tooltip-pos': 'bottom',
            }
        });

        window.addEventListener('beforeunload', function(e) {
            if (isDirty) {
                const message = 'You have unsaved changes. Are you sure you want to leave?';
                e.preventDefault();
                e.returnValue = message;
                return message;
            }
        });

        window.onpopstate = function(e) {
            if (isDirty) {
                if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                    history.pushState(null, null, window.location.href);
                    return false;
                }
            }
        };

        document.addEventListener('keydown', function(e) {
            if ((e.key === 'F5' || (e.ctrlKey && e.key === 'r')) && isDirty) {
                if (!confirm('You have unsaved changes. Are you sure you want to refresh?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });



        (function() {
            function preventSaveEvent(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.keyCode === 83)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    if (window.editor) {
                        window.editor.Commands.run('save-template');
                    }
                    return false;
                }
            }

            function applyPreventSave(win, doc) {
                win.onbeforeunload = null;

                win.addEventListener('keydown', preventSaveEvent, true);
                win.addEventListener('keydown', preventSaveEvent, false);
                doc.addEventListener('keydown', preventSaveEvent, true);
                doc.addEventListener('keydown', preventSaveEvent, false);

                win.addEventListener('beforeunload', function(e) {
                    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.keyCode === 83)) {
                        e.preventDefault();
                        return false;
                    }
                });

                doc.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.keyCode === 83)) {
                        e.preventDefault();
                        return false;
                    }
                }, true);
            }

            applyPreventSave(window, document);

            function handleIframes() {
                var iframes = document.getElementsByTagName('iframe');
                for (var i = 0; i < iframes.length; i++) {
                    try {
                        var iframeDoc = iframes[i].contentDocument || iframes[i].contentWindow.document;
                        var iframeWin = iframes[i].contentWindow;
                        applyPreventSave(iframeWin, iframeDoc);

                        iframeWin.addEventListener('load', function() {
                            handleIframes();
                        });
                    } catch (e) {
                        console.log('Cannot access iframe:', e);
                    }
                }
            }

            handleIframes();

            new MutationObserver(function(mutations) {
                handleIframes();
            }).observe(document.body, {
                childList: true,
                subtree: true
            });

            window.addEventListener('beforeunload', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.keyCode === 83)) {
                    e.preventDefault();
                    return false;
                }
            });

            window.save = function() {
                return false;
            };
            document.save = function() {
                return false;
            };
        })();
    </script>

</body>

</html>