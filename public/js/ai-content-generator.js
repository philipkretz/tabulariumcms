(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeAiButtons();
    });

    function initializeAiButtons() {
        // Add AI button for long description/content fields
        addAiButton('textarea[id*="longDescription"], textarea[id*="description"]:not([id*="short"]), textarea[id*="content"]', 'description', '✨ Generate with AI');
        
        // Add AI button for SEO title
        addAiButton('input[id*="seoTitle"], input[id*="metaTitle"]', 'seo-title', '✨ AI');
        
        // Add AI button for SEO description
        addAiButton('textarea[id*="seoDescription"], textarea[id*="metaDescription"]', 'seo-description', '✨ AI');
        
        // Add AI button for SEO keywords
        addAiButton('input[id*="seoKeywords"], input[id*="metaKeywords"], textarea[id*="keywords"]', 'seo-keywords', '✨ AI');
    }

    function addAiButton(selector, type, buttonText) {
        const fields = document.querySelectorAll(selector);

        fields.forEach(function(field) {
            // Skip if button already exists
            if (field.parentElement.querySelector('.ai-generate-btn')) {
                return;
            }

            // Skip GrapeJS editor fields (they have their own AI button)
            if (field.classList.contains('grapesjs-content-field') || field.closest('.grapesjs-editor-wrapper')) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm btn-info ai-generate-btn';
            button.innerHTML = '<i class="fas fa-magic"></i> ' + buttonText;
            button.style.marginTop = '5px';
            button.style.marginBottom = '5px';
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                generateContent(field, type, button);
            });

            // Insert button after the field
            field.parentElement.insertBefore(button, field.nextSibling);
        });
    }

    function generateContent(field, type, button) {
        // Get title from the form
        const titleField = document.querySelector('input[id*="title"]:not([id*="seo"]):not([id*="meta"])');
        if (!titleField || !titleField.value.trim()) {
            alert('Please enter a title first');
            return;
        }

        const title = titleField.value.trim();
        
        // Get short description if exists (for long description generation)
        let shortDescription = '';
        if (type === 'description') {
            const shortDescField = document.querySelector('textarea[id*="shortDescription"], input[id*="shortDescription"]');
            if (shortDescField) {
                shortDescription = shortDescField.value.trim();
            }
        }

        // Get content for SEO generation (if exists)
        let content = '';
        if (type !== 'description') {
            const contentField = document.querySelector('textarea[id*="longDescription"], textarea[id*="content"]:not([id*="seo"])');
            if (contentField) {
                content = contentField.value.trim();
            }
        }

        // Determine entity type from URL or form
        let entityType = 'product';
        const url = window.location.href;
        if (url.includes('/page/')) entityType = 'page';
        else if (url.includes('/post/')) entityType = 'post';
        else if (url.includes('/category/')) entityType = 'category';
        else if (url.includes('/article/')) entityType = 'product';

        // Show loading state
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        button.disabled = true;

        // Determine endpoint
        let endpoint;
        switch(type) {
            case 'description':
                endpoint = '/admin/ai/generate-description';
                break;
            case 'seo-title':
                endpoint = '/admin/ai/generate-seo-title';
                break;
            case 'seo-description':
                endpoint = '/admin/ai/generate-seo-description';
                break;
            case 'seo-keywords':
                endpoint = '/admin/ai/generate-seo-keywords';
                break;
            default:
                console.error('Unknown type:', type);
                return;
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('title', title);
        formData.append('type', entityType);
        if (shortDescription) formData.append('shortDescription', shortDescription);
        if (content) formData.append('content', content);

        // Make AJAX request
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Handle different field types
                if (field.tagName === 'TEXTAREA') {
                    // For textareas, check if it's a WYSIWYG editor
                    const fieldId = field.id;
                    
                    // Try CKEditor 5
                    if (window.CKEDITOR && window.CKEDITOR.instances && window.CKEDITOR.instances[fieldId]) {
                        window.CKEDITOR.instances[fieldId].setData(data.content);
                    }
                    // Try Summernote
                    else if (typeof jQuery !== 'undefined' && jQuery(field).data('summernote')) {
                        jQuery(field).summernote('code', data.content);
                    }
                    // Try TinyMCE
                    else if (window.tinymce && window.tinymce.get(fieldId)) {
                        window.tinymce.get(fieldId).setContent(data.content);
                    }
                    // Fallback to plain textarea
                    else {
                        field.value = data.content;
                    }
                } else {
                    // For input fields
                    field.value = data.content;
                }
                
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                field.dispatchEvent(event);
                
                // Show success message
                showNotification('Content generated successfully!', 'success');
            } else {
                showNotification('Error: ' + (data.message || 'Failed to generate content'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    function showNotification(message, type) {
        // Try Sonata's flash message system
        const flashContainer = document.querySelector('.sonata-ba-content');
        if (flashContainer) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show';
            alert.innerHTML = message + '<button type="button" class="close" data-dismiss="alert">&times;</button>';
            flashContainer.insertBefore(alert, flashContainer.firstChild);
            
            setTimeout(() => alert.remove(), 5000);
        } else {
            // Fallback to alert
            alert(message);
        }
    }
})();
