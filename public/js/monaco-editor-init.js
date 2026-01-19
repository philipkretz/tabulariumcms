// Monaco Editor Integration for Sonata Admin
(function() {
    'use strict';

    console.log('Monaco Editor Init: Script loaded');

    // Wait for DOM to be ready
    function initWhenReady() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadMonaco);
        } else {
            loadMonaco();
        }
    }

    function loadMonaco() {
        console.log('Monaco Editor Init: Loading Monaco from CDN');

        // Load Monaco Editor from CDN
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js';
        script.onload = function() {
            console.log('Monaco Editor Init: Loader loaded, configuring...');
            require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' }});

            require(['vs/editor/editor.main'], function() {
                console.log('Monaco Editor Init: Monaco loaded successfully');
                initializeMonacoEditors();
            });
        };
        script.onerror = function() {
            console.error('Monaco Editor Init: Failed to load Monaco from CDN');
        };
        document.head.appendChild(script);
    }

    initWhenReady();

    function initializeMonacoEditors() {
        // Find all textareas with Monaco Editor attribute
        const textareas = document.querySelectorAll('textarea[data-monaco="true"]');

        textareas.forEach(function(textarea) {
            // Skip if already initialized
            if (textarea.dataset.monacoInitialized) {
                return;
            }

            // Hide original textarea
            textarea.style.display = 'none';

            // Create Monaco Editor container
            const editorContainer = document.createElement('div');
            editorContainer.style.width = '100%';
            editorContainer.style.height = '600px';
            editorContainer.style.border = '2px solid #d1b05f';
            editorContainer.style.borderRadius = '8px';
            editorContainer.style.overflow = 'hidden';
            editorContainer.style.marginBottom = '10px';

            // Insert editor container after textarea
            textarea.parentNode.insertBefore(editorContainer, textarea.nextSibling);

            // Create Monaco Editor instance
            const editor = monaco.editor.create(editorContainer, {
                value: textarea.value || '',
                language: 'html',
                theme: 'vs',
                automaticLayout: true,
                minimap: { enabled: true },
                fontSize: 14,
                lineNumbers: 'on',
                roundedSelection: true,
                scrollBeyondLastLine: false,
                wordWrap: 'on',
                formatOnPaste: true,
                formatOnType: true,
                suggest: {
                    showKeywords: true,
                    showSnippets: true,
                },
                quickSuggestions: {
                    other: true,
                    comments: true,
                    strings: true
                }
            });

            // Sync editor content back to textarea on change
            editor.onDidChangeModelContent(function() {
                textarea.value = editor.getValue();
                // Trigger change event for form validation
                const event = new Event('change', { bubbles: true });
                textarea.dispatchEvent(event);
            });

            // Mark as initialized
            textarea.dataset.monacoInitialized = 'true';

            // Store editor instance for potential later use
            textarea.monacoEditor = editor;

            console.log('Monaco Editor initialized for:', textarea.id || textarea.name);
        });
    }

    // Re-initialize on dynamic content changes (for Sonata Admin)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            if (typeof monaco !== 'undefined') {
                initializeMonacoEditors();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
