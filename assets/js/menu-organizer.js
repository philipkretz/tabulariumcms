class MenuOrganizer {
    constructor() {
        this.menuItemsContainer = document.getElementById('menu-items-container');
        this.availableItemsContainer = document.getElementById('available-items-container');
        this.trashZone = document.getElementById('trash-zone');
        this.saveButton = document.getElementById('save-menu-order');
        this.menuId = {{ menu.id }};
        
        this.draggedElement = null;
        this.draggedFrom = null;
        
        this.init();
    }
    
    init() {
        this.setupDragAndDrop();
        this.setupSaveButton();
        this.setupKeyboardShortcuts();
    }
    
    setupDragAndDrop() {
        // Setup menu items
        this.setupContainer(this.menuItemsContainer, 'menu');
        
        // Setup available items
        this.setupContainer(this.availableItemsContainer, 'available');
        
        // Setup trash zone
        this.setupTrashZone();
    }
    
    setupContainer(container, type) {
        const items = container.querySelectorAll('.menu-item, .available-item');
        
        items.forEach(item => {
            item.addEventListener('dragstart', (e) => this.handleDragStart(e, type));
            item.addEventListener('dragend', (e) => this.handleDragEnd(e));
        });
        
        container.addEventListener('dragover', (e) => this.handleDragOver(e));
        container.addEventListener('drop', (e) => this.handleDrop(e, type));
        container.addEventListener('dragleave', (e) => this.handleDragLeave(e));
    }
    
    setupTrashZone() {
        this.trashZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.trashZone.classList.add('active');
        });
        
        this.trashZone.addEventListener('dragleave', () => {
            this.trashZone.classList.remove('active');
        });
        
        this.trashZone.addEventListener('drop', (e) => {
            e.preventDefault();
            this.trashZone.classList.remove('active');
            this.handleTrashDrop(e);
        });
    }
    
    handleDragStart(e, from) {
        this.draggedElement = e.target.closest('.menu-item, .available-item');
        this.draggedFrom = from;
        
        this.draggedElement.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.draggedElement.innerHTML);
        
        this.trashZone.style.display = 'flex';
    }
    
    handleDragEnd(e) {
        const item = e.target.closest('.menu-item, .available-item');
        if (item) {
            item.classList.remove('dragging');
        }
        
        this.draggedElement = null;
        this.draggedFrom = null;
        this.trashZone.style.display = 'none';
        
        // Remove drop zone classes
        document.querySelectorAll('.drop-zone').forEach(zone => {
            zone.classList.remove('drop-zone');
        });
    }
    
    handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        const container = e.currentTarget;
        const afterElement = this.getDragAfterElement(container, e.clientY);
        
        if (afterElement == null) {
            container.appendChild(this.draggedElement);
        } else {
            container.insertBefore(this.draggedElement, afterElement);
        }
        
        // Check if dropping on another item (for sub-menus)
        const targetItem = e.target.closest('.menu-item');
        if (targetItem && targetItem !== this.draggedElement && this.draggedFrom === 'menu') {
            this.highlightDropTarget(targetItem, true);
        }
    }
    
    handleDragLeave(e) {
        const targetItem = e.target.closest('.menu-item');
        if (targetItem) {
            this.highlightDropTarget(targetItem, false);
        }
    }
    
    handleDrop(e, to) {
        e.preventDefault();
        
        if (!this.draggedElement) return;
        
        const container = e.currentTarget;
        const targetItem = e.target.closest('.menu-item');
        
        // Handle dropping on another menu item (create sub-menu)
        if (targetItem && targetItem !== this.draggedElement && to === 'menu' && this.draggedFrom === 'menu') {
            this.createSubMenuItem(targetItem, this.draggedElement);
        }
        
        // Convert available item to menu item if moving to menu
        if (to === 'menu' && this.draggedFrom === 'available') {
            this.convertAvailableToMenuItem(this.draggedElement);
        }
        
        // Convert menu item to available item if moving back
        if (to === 'available' && this.draggedFrom === 'menu') {
            this.convertMenuItemToAvailable(this.draggedElement);
        }
        
        this.updateDropTargets();
    }
    
    handleTrashDrop(e) {
        if (this.draggedElement && this.draggedFrom === 'menu') {
            this.convertMenuItemToAvailable(this.draggedElement);
        }
    }
    
    createSubMenuItem(parentItem, childItem) {
        let childrenContainer = parentItem.querySelector('.children');
        if (!childrenContainer) {
            childrenContainer = document.createElement('div');
            childrenContainer.className = 'children ml-3 mt-2';
            parentItem.appendChild(childrenContainer);
        }
        
        childrenContainer.appendChild(childItem);
        childItem.classList.add('child-item');
    }
    
    convertAvailableToMenuItem(element) {
        element.classList.remove('available-item');
        element.classList.add('menu-item');
        
        // Update the icon
        const icon = element.querySelector('.handle');
        if (icon) {
            icon.className = 'fas fa-arrows-alt handle';
        }
        
        // Remove current menu info
        const menuInfo = element.querySelector('.text-muted');
        if (menuInfo) {
            menuInfo.remove();
        }
        
        // Re-add event listeners
        element.addEventListener('dragstart', (e) => this.handleDragStart(e, 'menu'));
    }
    
    convertMenuItemToAvailable(element) {
        element.classList.remove('menu-item');
        element.classList.add('available-item');
        
        // Update the icon
        const icon = element.querySelector('.handle');
        if (icon) {
            icon.className = 'fas fa-plus-circle handle text-success';
        }
        
        // Add menu info back
        const itemContent = element.querySelector('.item-content');
        if (itemContent && !itemContent.querySelector('.text-muted')) {
            const menuInfo = document.createElement('span');
            menuInfo.className = 'text-muted ml-2';
            menuInfo.textContent = '(Will be removed from current menu)';
            itemContent.appendChild(menuInfo);
        }
        
        // Move to available container
        this.availableItemsContainer.appendChild(element);
        
        // Re-add event listeners
        element.addEventListener('dragstart', (e) => this.handleDragStart(e, 'available'));
    }
    
    highlightDropTarget(element, highlight) {
        if (highlight) {
            element.style.backgroundColor = '#e3f2fd';
            element.style.borderColor = '#2196f3';
        } else {
            element.style.backgroundColor = '';
            element.style.borderColor = '';
        }
    }
    
    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.menu-item:not(.dragging), .available-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    updateDropTargets() {
        // Re-setup drag and drop for all containers after changes
        this.setupContainer(this.menuItemsContainer, 'menu');
        this.setupContainer(this.availableItemsContainer, 'available');
    }
    
    setupSaveButton() {
        this.saveButton.addEventListener('click', () => this.saveMenuOrder());
    }
    
    async saveMenuOrder() {
        const items = this.serializeMenuStructure();
        
        try {
            this.saveButton.disabled = true;
            this.saveButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
            
            const response = await fetch(`/admin/menu/${this.menuId}/update-order`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ items })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showToast('success-toast');
            } else {
                this.showToast('error-toast');
                console.error('Error saving menu order:', result.message);
            }
        } catch (error) {
            console.error('Error saving menu order:', error);
            this.showToast('error-toast');
        } finally {
            this.saveButton.disabled = false;
            this.saveButton.innerHTML = '<i class="fa fa-save"></i> Save Changes';
        }
    }
    
    serializeMenuStructure() {
        const menuItems = this.menuItemsContainer.querySelectorAll('.menu-item');
        const items = [];
        
        menuItems.forEach(item => {
            const itemId = parseInt(item.dataset.itemId);
            const children = item.querySelectorAll(':scope > .children > .menu-item');
            const childrenData = [];
            
            children.forEach(child => {
                const childId = parseInt(child.dataset.itemId);
                if (!isNaN(childId)) {
                    childrenData.push({ id: childId });
                }
            });
            
            const itemData = { id: itemId };
            if (childrenData.length > 0) {
                itemData.children = childrenData;
            }
            
            items.push(itemData);
        });
        
        return items;
    }
    
    showToast(toastId) {
        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+S to save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                this.saveMenuOrder();
            }
            
            // Escape to cancel current drag
            if (e.key === 'Escape' && this.draggedElement) {
                this.handleDragEnd(e);
            }
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MenuOrganizer();
});