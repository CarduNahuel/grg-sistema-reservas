// CSRF Token Helper
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf_token"]');
    return meta ? meta.getAttribute('content') : '';
}

// Add CSRF token to all AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss only alerts that explicitly opt-in with .alert-auto-dismiss
    const autoDismissAlerts = document.querySelectorAll('.alert.alert-auto-dismiss');
    autoDismissAlerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Format date inputs to local format
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            // Set min date to today
            const today = new Date().toISOString().split('T')[0];
            if (!input.min) {
                input.min = today;
            }
        }
    });
});

// Helper function for AJAX requests with CSRF
function fetchWithCsrf(url, options = {}) {
    options.headers = options.headers || {};
    options.headers['X-CSRF-TOKEN'] = getCsrfToken();
    
    if (options.body && !(options.body instanceof FormData)) {
        options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
    
    return fetch(url, options);
}

// Notification counter update
function updateNotificationCount() {
    fetchWithCsrf('/grg/api/notifications/count')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.navbar .bi-bell + .badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error updating notification count:', error));
}

// Update notification count every 30 seconds if user is logged in
if (document.querySelector('.navbar .bi-bell')) {
    setInterval(updateNotificationCount, 30000);
}

// Form validation helper
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const errors = [];

    for (const field in rules) {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) continue;

        const value = input.value.trim();
        const fieldRules = rules[field];

        if (fieldRules.required && !value) {
            errors.push(`${field} es obligatorio`);
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }

        if (fieldRules.minLength && value.length < fieldRules.minLength) {
            errors.push(`${field} debe tener al menos ${fieldRules.minLength} caracteres`);
            input.classList.add('is-invalid');
            isValid = false;
        }

        if (fieldRules.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            errors.push(`${field} debe ser un email válido`);
            input.classList.add('is-invalid');
            isValid = false;
        }
    }

    if (!isValid) {
        console.error('Validation errors:', errors);
    }

    return isValid;
}

// Toast notification helper
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}
