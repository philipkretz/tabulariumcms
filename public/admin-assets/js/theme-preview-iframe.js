// Theme Preview IFrame - Receive and apply theme updates
(function() {
    'use strict';

    // Listen for theme updates from parent window
    window.addEventListener('message', function(event) {
        // Security: You might want to add origin validation here in production
        // if (event.origin !== window.location.origin) return;

        if (event.data.type === 'updateTheme') {
            const theme = event.data.theme;
            applyThemeChanges(theme);
        }
    });

    function applyThemeChanges(theme) {
        const root = document.documentElement;

        // Apply CSS custom properties
        if (theme.primaryColor) {
            root.style.setProperty('--primary-color', theme.primaryColor);
        }
        if (theme.secondaryColor) {
            root.style.setProperty('--secondary-color', theme.secondaryColor);
        }
        if (theme.accentColor) {
            root.style.setProperty('--accent-color', theme.accentColor);
        }
        if (theme.backgroundColor) {
            root.style.setProperty('--background-color', theme.backgroundColor);
            document.body.style.backgroundColor = theme.backgroundColor;
        }
        if (theme.textColor) {
            root.style.setProperty('--text-color', theme.textColor);
            document.body.style.color = theme.textColor;
        }
        if (theme.headingFont) {
            root.style.setProperty('--heading-font', theme.headingFont);
        }
        if (theme.bodyFont) {
            root.style.setProperty('--body-font', theme.bodyFont);
            document.body.style.fontFamily = theme.bodyFont;
        }
        if (theme.fontSize) {
            root.style.setProperty('--font-size', theme.fontSize);
            document.body.style.fontSize = theme.fontSize;
        }

        // Add subtle animation to show the change
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(function() {
            document.body.style.transition = '';
        }, 300);

        // Optional: Log changes for debugging
        console.log('Theme updated:', theme);
    }

    // Notify parent that iframe is ready
    window.addEventListener('load', function() {
        window.parent.postMessage({
            type: 'iframeReady'
        }, '*');
    });
})();
