class ActivityTracker {
    constructor() {
        this.init();
    }
    
    init() {
        this.initEventListeners();
        this.initTooltips();
        this.initResponsive();
    }
    
    initEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('status-update-btn')) {
                this.handleStatusUpdate(e.target);
            }
        });
        
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', this.toggleSidebar);
        }
        document.addEventListener('click', (e) => {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 991.98 && 
                sidebar && 
                sidebar.classList.contains('show') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    
    handleStatusUpdate(button) {
        const activityId = button.dataset.activityId;
        const currentStatus = button.dataset.currentStatus;
        
        Swal.fire({
            title: 'Update Activity Status',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label">New Status:</label>
                    <select id="newStatus" class="form-select">
                        <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="done" ${currentStatus === 'done' ? 'selected' : ''}>Completed</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#667eea',
            preConfirm: () => {
                const newStatus = document.getElementById('newStatus').value;
                return { status: newStatus };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.updateActivityStatus(activityId, result.value);
            }
        });
    }
    
    async updateActivityStatus(activityId, data) {
        try {
            Swal.fire({
                title: 'Updating...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const response = await fetch('api/update-activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    activity_id: activityId,
                    status: data.status
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Activity updated successfully!',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: result.message || 'Failed to update activity'
                });
            }
        } catch (error) {
            console.error('Error updating activity:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating the activity'
            });
        }
    }
    
    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    }
    
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    initResponsive() {
        window.addEventListener('resize', () => {
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth > 991.98 && sidebar) {
                sidebar.classList.remove('show');
            }
        });
    }
    
    showToast(message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${this.getBootstrapColor(type)} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas ${this.getToastIcon(type)} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
    
    getBootstrapColor(type) {
        const colors = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };
        return colors[type] || 'info';
    }
    
    getToastIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || 'fa-info-circle';
    }
}

function confirmDelete(activityId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`delete-form-${activityId}`).submit();
        }
    });
}
document.addEventListener('DOMContentLoaded', function() {
    window.activityTracker = new ActivityTracker();   
    // adding fade-in animation to cards
    const cards = document.querySelectorAll('.activity-card, .stats-card, .card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
});

window.utils = {
        formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    formatRelativeTime: function(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} day${days > 1 ? 's' : ''} ago`;
        }
    },
    
    isOverdue: function(dueDateString, status) {
        if (!dueDateString || status === 'done') return false;
        const dueDate = new Date(dueDateString);
        const now = new Date();
        return dueDate < now;
    },
    
    getPriorityColor: function(priority) {
        const colors = {
            'High': 'danger',
            'Medium': 'warning',
            'Low': 'info'
        };
        return colors[priority] || 'secondary';
    },
    
    getStatusColor: function(status) {
        const colors = {
            'done': 'success',
            'pending': 'secondary'
        };
        return colors[status] || 'secondary';
    },
    
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};
        this.init();
    }
    
    init() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) {
                e.preventDefault();
                this.showErrors();
            }
        });
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            
            input.addEventListener('input', utils.debounce(() => {
                this.validateField(input);
            }, 300));
        });
    }
    
    validate() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            this.validateField(input);
        });
        
        return Object.keys(this.errors).length === 0;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const name = field.name;
        const type = field.type;
        
        delete this.errors[name];
        this.clearFieldError(field);
        if (field.hasAttribute('required') && !value) {
            this.errors[name] = 'This field is required';
            this.showFieldError(field, this.errors[name]);
            return false;
        }
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.errors[name] = 'Please enter a valid email address';
                this.showFieldError(field, this.errors[name]);
                return false;
            }
        }
        
        if (type === 'password' && value) {
            if (value.length < 8) {
                this.errors[name] = 'Password must be at least 8 characters long';
                this.showFieldError(field, this.errors[name]);
                return false;
            }
        }
        
        if (type === 'datetime-local' && value) {
            const selectedDate = new Date(value);
            const now = new Date();
            if (selectedDate < now) {
                this.errors[name] = 'Due date cannot be in the past';
                this.showFieldError(field, this.errors[name]);
                return false;
            }
        }
        
        return true;
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    showErrors() {
        const firstErrorField = this.form.querySelector('.is-invalid');
        if (firstErrorField) {
            firstErrorField.focus();
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        new FormValidator(form);
    });
    const modalForms = document.querySelectorAll('.modal form');
    modalForms.forEach(form => {
        form.setAttribute('data-validate', 'true');
        new FormValidator(form);
    });
});
class ActivityFilter {
    constructor() {
        this.activities = [];
        this.filteredActivities = [];
        this.currentFilters = {
            search: '',
            status: 'all',
            priority: 'all',
            assignee: 'all'
        };
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadActivities();
    }
    
    bindEvents() {
        const searchInput = document.getElementById('searchActivities');
        if (searchInput) {
            searchInput.addEventListener('input', utils.debounce((e) => {
                this.currentFilters.search = e.target.value;
                this.applyFilters();
            }, 300));
        }
        
        const filterSelects = document.querySelectorAll('.activity-filter');
        filterSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                this.currentFilters[e.target.dataset.filter] = e.target.value;
                this.applyFilters();
            });
        });
    }
    
    loadActivities() {
        const activityCards = document.querySelectorAll('.activity-card');
        this.activities = Array.from(activityCards).map(card => ({
            element: card,
            title: card.querySelector('.activity-title').textContent.toLowerCase(),
            description: (card.querySelector('.activity-description')?.textContent || '').toLowerCase(),
            status: card.querySelector('.badge').textContent.toLowerCase().trim(),
            priority: card.querySelectorAll('.badge')[0]?.textContent.toLowerCase().trim() || '',
            assignee: card.querySelector('.detail-item span')?.textContent.toLowerCase() || ''
        }));
        
        this.filteredActivities = [...this.activities];
    }
    
    applyFilters() {
        this.filteredActivities = this.activities.filter(activity => {
            const matchesSearch = !this.currentFilters.search || 
                activity.title.includes(this.currentFilters.search.toLowerCase()) ||
                activity.description.includes(this.currentFilters.search.toLowerCase());
            
            const matchesStatus = this.currentFilters.status === 'all' || 
                activity.status === this.currentFilters.status;
            
            const matchesPriority = this.currentFilters.priority === 'all' || 
                activity.priority === this.currentFilters.priority.toLowerCase();
            
            const matchesAssignee = this.currentFilters.assignee === 'all' || 
                activity.assignee.includes(this.currentFilters.assignee.toLowerCase());
            
            return matchesSearch && matchesStatus && matchesPriority && matchesAssignee;
        });
        
        this.renderFilteredActivities();
    }
    
    renderFilteredActivities() {
        this.activities.forEach(activity => {
            activity.element.style.display = 'none';
        });
        
        this.filteredActivities.forEach(activity => {
            activity.element.style.display = 'block';
        });
        
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = `${this.filteredActivities.length} of ${this.activities.length} activities`;
        }
        
        this.toggleEmptyState(this.filteredActivities.length === 0);
    }
    
    toggleEmptyState(show) {
        let emptyState = document.getElementById('emptyState');
        
        if (show && !emptyState) {
            emptyState = document.createElement('div');
            emptyState.id = 'emptyState';
            emptyState.className = 'col-12 empty-state';
            emptyState.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>No activities found</h3>
                <p>Try adjusting your search or filter criteria</p>
                <button class="btn btn-outline-primary" onclick="activityFilter.clearFilters()">
                    Clear Filters
                </button>
            `;
            
            const activitiesContainer = document.querySelector('.activities-page .row');
            if (activitiesContainer) {
                activitiesContainer.appendChild(emptyState);
            }
        } else if (!show && emptyState) {
            emptyState.remove();
        }
    }
    
    clearFilters() {
        this.currentFilters = {
            search: '',
            status: 'all',
            priority: 'all',
            assignee: 'all'
        };
        
        const searchInput = document.getElementById('searchActivities');
        if (searchInput) searchInput.value = '';
        
        const filterSelects = document.querySelectorAll('.activity-filter');
        filterSelects.forEach(select => {
            select.value = 'all';
        });
        
        this.applyFilters();
    }
}
if (window.location.pathname.includes('activities.php')) {
    document.addEventListener('DOMContentLoaded', function() {
        window.activityFilter = new ActivityFilter();
    });
}

class AutoSave {
    constructor(form, key) {
        this.form = form;
        this.storageKey = `autosave_${key}`;
        this.init();
    }
    
    init() {
        this.loadSavedData();
        this.bindEvents();
    }
    
    bindEvents() {
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', utils.debounce(() => {
                this.saveData();
            }, 1000));
        });
        
        this.form.addEventListener('submit', () => {
            this.clearSavedData();
        });
    }
    
    saveData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        localStorage.setItem(this.storageKey, JSON.stringify(data));
    }
    
    loadSavedData() {
        const savedData = localStorage.getItem(this.storageKey);
        if (savedData) {
            const data = JSON.parse(savedData);
            
            Object.keys(data).forEach(key => {
                const input = this.form.querySelector(`[name="${key}"]`);
                if (input && data[key]) {
                    input.value = data[key];
                }
            });
        }
    }
    
    clearSavedData() {
        localStorage.removeItem(this.storageKey);
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const newActivityForm = document.querySelector('#newActivityModal form');
    if (newActivityForm) {
        new AutoSave(newActivityForm, 'new_activity');
    }
});

