/**
 * GrapeJS WYSIWYG Editor for Sonata Admin
 * Simplified and reliable initialization
 */
(function() {
    'use strict';

    console.log('[GrapeJS] Initialization script loaded');

    let isGrapeJSLoaded = false;
    let isPluginsLoaded = false;

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('[GrapeJS] DOM ready, loading resources...');
        loadGrapeJSResources();
    }

    function loadGrapeJSResources() {
        // Load CSS
        const css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = 'https://unpkg.com/grapesjs/dist/css/grapes.min.css';
        css.onload = () => console.log('[GrapeJS] CSS loaded');
        document.head.appendChild(css);

        // Load GrapeJS core
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/grapesjs';
        script.onload = function() {
            console.log('[GrapeJS] Core library loaded');
            isGrapeJSLoaded = true;
            loadPlugins();
        };
        script.onerror = () => console.error('[GrapeJS] Failed to load core library');
        document.head.appendChild(script);
    }

    function loadPlugins() {
        console.log('[GrapeJS] Loading plugins...');

        const pluginScripts = [
            'https://unpkg.com/grapesjs-blocks-basic',
            'https://unpkg.com/grapesjs-plugin-forms'
        ];

        let loaded = 0;
        pluginScripts.forEach(url => {
            const script = document.createElement('script');
            script.src = url;
            script.onload = () => {
                loaded++;
                console.log(`[GrapeJS] Plugin loaded: ${url}`);
                if (loaded === pluginScripts.length) {
                    isPluginsLoaded = true;
                    initializeEditors();
                }
            };
            script.onerror = () => {
                loaded++;
                console.warn(`[GrapeJS] Failed to load plugin: ${url}`);
                if (loaded === pluginScripts.length) {
                    isPluginsLoaded = true;
                    initializeEditors();
                }
            };
            document.head.appendChild(script);
        });
    }

    function initializeEditors() {
        console.log('[GrapeJS] Initializing editors...');

        const textareas = document.querySelectorAll('textarea[data-grapesjs="true"]');
        console.log(`[GrapeJS] Found ${textareas.length} editor field(s)`);

        textareas.forEach((textarea, index) => {
            // Skip if already initialized
            if (textarea.dataset.grapejsInit) {
                console.log(`[GrapeJS] Editor ${index} already initialized, skipping`);
                return;
            }

            // Skip if inside a grapesjs-editor-wrapper (widget template handles it)
            if (textarea.closest('.grapesjs-editor-wrapper')) {
                console.log(`[GrapeJS] Editor ${index} is in widget wrapper, skipping`);
                return;
            }

            // Skip if next sibling is already a GrapeJS container
            const nextSibling = textarea.nextElementSibling;
            if (nextSibling && nextSibling.id && nextSibling.id.startsWith('gjs-')) {
                console.log(`[GrapeJS] Editor ${index} already has GrapeJS container, skipping`);
                return;
            }

            const editorId = 'gjs-editor-' + index + '-' + Date.now();
            const editorHeight = textarea.dataset.editorHeight || '650px';

            console.log(`[GrapeJS] Creating editor ${index} with ID: ${editorId}`);

            // Create container
            const container = document.createElement('div');
            container.id = editorId;
            container.style.marginBottom = '15px';
            container.style.border = '2px solid #d1b05f';
            container.style.borderRadius = '8px';
            container.style.overflow = 'hidden';

            // Insert before textarea
            textarea.parentNode.insertBefore(container, textarea);

            try {
                // Initialize GrapeJS
                const editor = grapesjs.init({
                    container: '#' + editorId,
                    height: editorHeight,
                    width: 'auto',
                    fromElement: false,
                    storageManager: false,
                    plugins: ['gjs-blocks-basic', 'gjs-plugin-forms'],
                    pluginsOpts: {
                        'gjs-blocks-basic': {},
                        'gjs-plugin-forms': {}
                    },
                    blockManager: {
                        appendTo: '#' + editorId,
                    },
                    styleManager: {
                        appendTo: '#' + editorId,
                    },
                    panels: {
                        defaults: [
                            {
                                id: 'basic-actions',
                                el: '.panel__basic-actions',
                                buttons: [
                                    {
                                        id: 'visibility',
                                        active: true,
                                        className: 'btn-toggle-borders',
                                        label: '<i class="fa fa-clone"></i>',
                                        command: 'sw-visibility',
                                    },
                                ]
                            },
                            {
                                id: 'panel-devices',
                                el: '.panel__devices',
                                buttons: [
                                    {
                                        id: 'device-desktop',
                                        label: '<i class="fa fa-desktop"></i>',
                                        command: 'set-device-desktop',
                                        active: true,
                                    },
                                    {
                                        id: 'device-mobile',
                                        label: '<i class="fa fa-mobile"></i>',
                                        command: 'set-device-mobile',
                                    },
                                ]
                            }
                        ]
                    },
                    deviceManager: {
                        devices: [
                            {
                                name: 'Desktop',
                                width: '',
                            },
                            {
                                name: 'Mobile',
                                width: '320px',
                                widthMedia: '480px',
                            }
                        ]
                    },
                    canvas: {
                        styles: [
                            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
                        ]
                    }
                });

                // Load initial content
                if (textarea.value) {
                    try {
                        const data = JSON.parse(textarea.value);
                        if (data.html) {
                            editor.setComponents(data.html);
                        }
                        if (data.css) {
                            editor.setStyle(data.css);
                        }
                    } catch (e) {
                        // Not JSON, treat as HTML
                        editor.setComponents(textarea.value);
                    }
                }

                // Save to textarea on update
                editor.on('update', () => {
                    const html = editor.getHtml();
                    const css = editor.getCss();
                    const json = JSON.stringify({ html, css });
                    textarea.value = json;
                });

                // Commands
                editor.Commands.add('set-device-desktop', {
                    run: editor => editor.setDevice('Desktop')
                });
                editor.Commands.add('set-device-mobile', {
                    run: editor => editor.setDevice('Mobile')
                });

                textarea.dataset.grapejsInit = 'true';
                textarea.grapesjsEditor = editor;

                console.log(`[GrapeJS] Editor ${index} initialized successfully`);
            } catch (error) {
                console.error(`[GrapeJS] Error initializing editor ${index}:`, error);
            }
        });
    }

    // Watch for new textareas (for dynamic content)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(() => {
            if (isGrapeJSLoaded && isPluginsLoaded) {
                initializeEditors();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
