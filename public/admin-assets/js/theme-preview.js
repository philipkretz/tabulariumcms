// Theme Preview Live Update
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.getElementById('preview-iframe');
    const applyButton = document.getElementById('applyPreview');
    const deviceButtons = document.querySelectorAll('.device-btn');

    // Color inputs
    const colorInputs = {
        primaryColor: document.getElementById('primaryColor'),
        secondaryColor: document.getElementById('secondaryColor'),
        accentColor: document.getElementById('accentColor'),
        backgroundColor: document.getElementById('backgroundColor'),
        textColor: document.getElementById('textColor')
    };

    // Typography inputs
    const headingFont = document.getElementById('headingFont');
    const bodyFont = document.getElementById('bodyFont');
    const fontSize = document.getElementById('fontSize');

    // Update hex values when color changes
    Object.keys(colorInputs).forEach(key => {
        const input = colorInputs[key];
        const hexInput = input.nextElementSibling;

        input.addEventListener('input', function() {
            hexInput.value = this.value;
        });
    });

    // Apply Preview Button
    applyButton.addEventListener('click', function() {
        updatePreview();

        // Visual feedback
        const icon = this.querySelector('i');
        icon.classList.add('fa-spin');
        setTimeout(() => {
            icon.classList.remove('fa-spin');
        }, 500);
    });

    // Device selector
    deviceButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            deviceButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const device = this.dataset.device;
            iframe.className = 'preview-frame ' + device;
        });
    });

    // Allow Enter key to apply preview
    document.getElementById('theme-editor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            updatePreview();
        }
    });

    function updatePreview() {
        const theme = {
            primaryColor: colorInputs.primaryColor.value,
            secondaryColor: colorInputs.secondaryColor.value,
            accentColor: colorInputs.accentColor.value,
            backgroundColor: colorInputs.backgroundColor.value,
            textColor: colorInputs.textColor.value,
            headingFont: headingFont.value,
            bodyFont: bodyFont.value,
            fontSize: fontSize.value
        };

        // Send message to iframe
        if (iframe.contentWindow) {
            iframe.contentWindow.postMessage({
                type: 'updateTheme',
                theme: theme
            }, '*');
        }
    }

    // Auto-apply on color change (real-time preview)
    Object.values(colorInputs).forEach(input => {
        input.addEventListener('change', function() {
            updatePreview();
        });
    });

    // Auto-apply on select change
    [headingFont, bodyFont, fontSize].forEach(input => {
        input.addEventListener('change', function() {
            updatePreview();
        });
    });
});
