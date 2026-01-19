import { Controller } from '@hotwired/stimulus';

/**
 * Profile Upload Controller
 *
 * Handles avatar and cover photo uploads with instant preview and AJAX upload
 *
 * Usage:
 * <div data-controller="profile-upload" data-profile-upload-endpoint-value="/profile/upload-avatar">
 *   <img data-profile-upload-target="preview" src="..." />
 *   <input type="file" data-profile-upload-target="input" data-action="change->profile-upload#handleUpload" />
 *   <div data-profile-upload-target="error"></div>
 *   <div data-profile-upload-target="loading"></div>
 * </div>
 */
export default class extends Controller {
    static targets = ['input', 'preview', 'error', 'loading'];
    static values = {
        endpoint: String
    };

    /**
     * Handle file selection and upload
     */
    async handleUpload(event) {
        const file = event.target.files[0];

        if (!file) {
            return;
        }

        // Validate file type on client side
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            this.showError('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
            event.target.value = '';
            return;
        }

        // Validate file size (5MB for avatar, 10MB for cover)
        const maxSize = this.endpointValue.includes('avatar') ? 5 * 1024 * 1024 : 10 * 1024 * 1024;
        if (file.size > maxSize) {
            const maxSizeMB = maxSize / 1024 / 1024;
            this.showError(`File size exceeds ${maxSizeMB}MB limit`);
            event.target.value = '';
            return;
        }

        // Clear any previous errors
        this.hideError();

        // Show loading state
        this.showLoading();

        try {
            // Upload file via AJAX
            const imageUrl = await this.uploadFile(file);

            // Update preview
            this.updatePreview(imageUrl);

            // Hide loading state
            this.hideLoading();

            // Reset file input
            event.target.value = '';

        } catch (error) {
            this.showError(error.message || 'Failed to upload image');
            this.hideLoading();
            event.target.value = '';
        }
    }

    /**
     * Upload file to server via AJAX
     */
    async uploadFile(file) {
        const formData = new FormData();

        // Determine field name based on endpoint
        const fieldName = this.endpointValue.includes('avatar') ? 'avatar' : 'cover_photo';
        formData.append(fieldName, file);

        const response = await fetch(this.endpointValue, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Upload failed');
        }

        return data.url;
    }

    /**
     * Update preview image
     */
    updatePreview(imageUrl) {
        if (this.hasPreviewTarget) {
            this.previewTarget.src = imageUrl;

            // Add a subtle animation
            this.previewTarget.style.opacity = '0';
            setTimeout(() => {
                this.previewTarget.style.transition = 'opacity 0.3s ease-in-out';
                this.previewTarget.style.opacity = '1';
            }, 50);
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        if (this.hasErrorTarget) {
            this.errorTarget.textContent = message;
            this.errorTarget.classList.remove('hidden');
        }
    }

    /**
     * Hide error message
     */
    hideError() {
        if (this.hasErrorTarget) {
            this.errorTarget.textContent = '';
            this.errorTarget.classList.add('hidden');
        }
    }

    /**
     * Show loading spinner
     */
    showLoading() {
        if (this.hasLoadingTarget) {
            const loadingText = this.loadingTarget.querySelector('span');
            if (loadingText) {
                loadingText.classList.remove('hidden');
            }
            this.loadingTarget.classList.remove('hidden');
        }

        // Disable input during upload
        if (this.hasInputTarget) {
            this.inputTarget.disabled = true;
        }
    }

    /**
     * Hide loading spinner
     */
    hideLoading() {
        if (this.hasLoadingTarget) {
            const loadingText = this.loadingTarget.querySelector('span');
            if (loadingText) {
                loadingText.classList.add('hidden');
            }
            this.loadingTarget.classList.add('hidden');
        }

        // Re-enable input after upload
        if (this.hasInputTarget) {
            this.inputTarget.disabled = false;
        }
    }
}
