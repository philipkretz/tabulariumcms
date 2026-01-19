// GrapeJS WYSIWYG Editor Integration for Sonata Admin
(function() {
    'use strict';

    console.log('GrapeJS Init: Script loaded');

    // Wait for DOM to be ready
    function initWhenReady() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadGrapeJS);
        } else {
            loadGrapeJS();
        }
    }

    function loadGrapeJS() {
        console.log('GrapeJS Init: Loading GrapeJS from CDN');

        // Load GrapeJS CSS
        const cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.href = 'https://unpkg.com/grapesjs/dist/css/grapes.min.css';
        document.head.appendChild(cssLink);

        // Load GrapeJS JS
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/grapesjs';
        script.onload = function() {
            console.log('GrapeJS Init: GrapeJS loaded successfully');

            // Load plugins
            loadPlugins();
        };
        script.onerror = function() {
            console.error('GrapeJS Init: Failed to load GrapeJS from CDN');
        };
        document.head.appendChild(script);
    }

    function loadPlugins() {
        // Load GrapeJS plugins
        const plugins = [
            'https://unpkg.com/grapesjs-blocks-basic',
            'https://unpkg.com/grapesjs-plugin-forms',
            'https://unpkg.com/grapesjs-component-countdown',
            'https://unpkg.com/grapesjs-plugin-export',
            'https://unpkg.com/grapesjs-tabs',
            'https://unpkg.com/grapesjs-custom-code',
            'https://unpkg.com/grapesjs-touch',
            'https://unpkg.com/grapesjs-parser-postcss',
            'https://unpkg.com/grapesjs-tooltip',
            'https://unpkg.com/grapesjs-style-bg'
        ];

        let loadedCount = 0;
        plugins.forEach(function(pluginUrl) {
            const script = document.createElement('script');
            script.src = pluginUrl;
            script.onload = function() {
                loadedCount++;
                if (loadedCount === plugins.length) {
                    console.log('GrapeJS Init: All plugins loaded');
                    initializeGrapeJSEditors();
                }
            };
            script.onerror = function() {
                console.warn('GrapeJS Init: Failed to load plugin:', pluginUrl);
                loadedCount++;
                if (loadedCount === plugins.length) {
                    initializeGrapeJSEditors();
                }
            };
            document.head.appendChild(script);
        });
    }

    initWhenReady();

    function initializeGrapeJSEditors() {
        console.log('GrapeJS Init: Initializing editors');

        // Find all fields with GrapeJS attribute
        const fields = document.querySelectorAll('[data-grapesjs="true"]');
        console.log('GrapeJS Init: Found', fields.length, 'editor fields');

        fields.forEach(function(field, index) {
            // Skip if already initialized
            if (field.dataset.grapejsInitialized) {
                return;
            }

            const editorId = 'grapesjs-editor-' + index;
            const editorHeight = field.dataset.editorHeight || '600px';
            const showBlocks = field.dataset.showBlocks === '1';
            const showLayers = field.dataset.showLayers === '1';
            const showStyles = field.dataset.showStyles === '1';

            // Create GrapeJS container
            const editorContainer = document.createElement('div');
            editorContainer.id = editorId;
            editorContainer.style.marginBottom = '20px';

            // Insert editor container before the field
            field.parentNode.insertBefore(editorContainer, field);

            // Initialize GrapeJS
            const editor = grapesjs.init({
                container: '#' + editorId,
                height: editorHeight,
                width: 'auto',
                storageManager: false,
                fromElement: false,
                canvas: {
                    styles: [
                        'https://cdn.tailwindcss.com'
                    ]
                },
                panels: {
                    defaults: [{
                        id: 'layers',
                        el: '.panel__right',
                        resizable: {
                            maxDim: 350,
                            minDim: 200,
                            tc: 0,
                            cl: 1,
                            cr: 0,
                            bc: 0,
                        },
                    }, {
                        id: 'panel-switcher',
                        el: '.panel__switcher',
                        buttons: [{
                            id: 'show-layers',
                            active: true,
                            label: '<i class="fa fa-bars"></i>',
                            command: 'show-layers',
                            togglable: false,
                        }, {
                            id: 'show-style',
                            active: true,
                            label: '<i class="fa fa-paint-brush"></i>',
                            command: 'show-styles',
                            togglable: false,
                        }],
                    }, {
                        id: 'panel-devices',
                        el: '.panel__devices',
                        buttons: [{
                            id: 'device-desktop',
                            label: '<i class="fa fa-desktop"></i>',
                            command: 'set-device-desktop',
                            active: true,
                            togglable: false,
                        }, {
                            id: 'device-tablet',
                            label: '<i class="fa fa-tablet"></i>',
                            command: 'set-device-tablet',
                            togglable: false,
                        }, {
                            id: 'device-mobile',
                            label: '<i class="fa fa-mobile"></i>',
                            command: 'set-device-mobile',
                            togglable: false,
                        }],
                    }]
                },
                plugins: [
                    'gjs-blocks-basic',
                    'gjs-plugin-forms',
                    'gjs-component-countdown',
                    'gjs-plugin-export',
                    'gjs-tabs',
                    'gjs-custom-code',
                    'gjs-touch',
                    'gjs-parser-postcss',
                    'gjs-tooltip',
                    'gjs-style-bg'
                ],
                pluginsOpts: {
                    'gjs-blocks-basic': {},
                    'gjs-plugin-forms': {},
                }
            });

            // Set initial content from hidden field
            if (field.value) {
                try {
                    const content = JSON.parse(field.value);
                    if (content.html || content.css) {
                        editor.setComponents(content.html || '');
                        editor.setStyle(content.css || '');
                    } else {
                        editor.setComponents(field.value);
                    }
                } catch (e) {
                    // If not JSON, treat as HTML
                    editor.setComponents(field.value);
                }
            }

            // Sync editor content back to hidden field on change
            editor.on('update', function() {
                const html = editor.getHtml();
                const css = editor.getCss();
                const content = JSON.stringify({
                    html: html,
                    css: css
                });
                field.value = content;

                // Trigger change event for form validation
                const event = new Event('change', { bubbles: true });
                field.dispatchEvent(event);
            });

            // Commands for device switching
            editor.Commands.add('set-device-desktop', {
                run: editor => editor.setDevice('Desktop')
            });
            editor.Commands.add('set-device-tablet', {
                run: editor => editor.setDevice('Tablet')
            });
            editor.Commands.add('set-device-mobile', {
                run: editor => editor.setDevice('Mobile portrait')
            });

            // Mark as initialized
            field.dataset.grapejsInitialized = 'true';
            field.grapesjsEditor = editor;

            console.log('GrapeJS Init: Editor initialized for field:', field.id || field.name);
        });
    }

    // Re-initialize on dynamic content changes (for Sonata Admin)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            if (typeof grapesjs !== 'undefined') {
                initializeGrapeJSEditors();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
