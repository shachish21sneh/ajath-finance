/**
 * AJATH CLOUD ERP & ACCOUNTING - DESKTOP KEYBOARD ENGINE
 * Shortcuts: F2-F9, Ctrl+S, Ctrl+P, Ctrl+F/K, Enter-to-next-field
 */

(function () {
    'use strict';

    // 1. Keyboard Shortcut Listener
    document.addEventListener('keydown', function (e) {
        // F2: Company Switcher
        if (e.key === 'F2') {
            e.preventDefault();
            const modalEl = document.getElementById('companySelectModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
            return;
        }

        // F3: Financial Year Switcher
        if (e.key === 'F3') {
            e.preventDefault();
            const modalEl = document.getElementById('fySelectModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
            return;
        }

        // F4: Contra Voucher
        if (e.key === 'F4') {
            e.preventDefault();
            window.location.href = '/vouchers/create?type=CONTRA';
            return;
        }

        // F5: Payment Voucher
        if (e.key === 'F5' && !e.ctrlKey) {
            e.preventDefault();
            window.location.href = '/vouchers/create?type=PAYMENT';
            return;
        }

        // F6: Receipt Voucher
        if (e.key === 'F6') {
            e.preventDefault();
            window.location.href = '/vouchers/create?type=RECEIPT';
            return;
        }

        // F7: Journal Voucher
        if (e.key === 'F7') {
            e.preventDefault();
            window.location.href = '/vouchers/create?type=JOURNAL';
            return;
        }

        // F8: Sales Voucher / Invoice
        if (e.key === 'F8') {
            e.preventDefault();
            window.location.href = '/sales/create';
            return;
        }

        // F9: Purchase Voucher / Invoice
        if (e.key === 'F9') {
            e.preventDefault();
            window.location.href = '/purchases/create';
            return;
        }

        // Ctrl + S: Submit active form
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            const activeForm = document.querySelector('form.keyboard-save-form') || document.querySelector('form:not(.no-auto-save)');
            if (activeForm) {
                const submitBtn = activeForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.click();
                } else {
                    activeForm.submit();
                }
            }
            return;
        }

        // Ctrl + P: Print
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
            const printBtn = document.querySelector('.btn-trigger-print');
            if (printBtn) {
                e.preventDefault();
                printBtn.click();
            }
            return;
        }

        // Ctrl + F or Ctrl + K: Spotlight Global Search
        if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'f' || e.key.toLowerCase() === 'k')) {
            e.preventDefault();
            const spotlightModalEl = document.getElementById('spotlightSearchModal');
            if (spotlightModalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(spotlightModalEl);
                modal.show();
                setTimeout(() => {
                    const input = document.getElementById('spotlightSearchInput');
                    if (input) input.focus();
                }, 150);
            }
            return;
        }

        // Esc: Close Spotlight or open modals
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(m => {
                const instance = bootstrap.Modal.getInstance(m);
                if (instance) instance.hide();
            });
        }
    });

    // 2. Enter-to-Next-Field inside Accounting Grids
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            const target = e.target;
            if (target && target.classList.contains('grid-nav-input')) {
                e.preventDefault();
                const inputs = Array.from(document.querySelectorAll('.grid-nav-input:not([disabled]):not([readonly])'));
                const currentIndex = inputs.indexOf(target);
                if (currentIndex >= 0 && currentIndex < inputs.length - 1) {
                    inputs[currentIndex + 1].focus();
                    if (inputs[currentIndex + 1].select) {
                        inputs[currentIndex + 1].select();
                    }
                } else if (currentIndex === inputs.length - 1) {
                    // Try to trigger add row button if available
                    const addRowBtn = document.querySelector('.btn-add-grid-row');
                    if (addRowBtn) {
                        addRowBtn.click();
                    }
                }
            }
        }
    });

    // 3. Theme Toggle Engine (Dark / Light)
    window.toggleTheme = function () {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-bs-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('ajath_theme', newTheme);

        const icon = document.getElementById('themeToggleIcon');
        if (icon) {
            icon.className = newTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
    };

    // Load saved theme on boot
    const savedTheme = localStorage.getItem('ajath_theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    document.addEventListener('DOMContentLoaded', () => {
        const icon = document.getElementById('themeToggleIcon');
        if (icon) {
            icon.className = savedTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
    });

    // 4. Spotlight Global Search Live Filter Engine
    let searchDebounce = null;
    window.handleSpotlightSearch = function (val) {
        clearTimeout(searchDebounce);
        const resultsContainer = document.getElementById('spotlightResultsContainer');
        if (!resultsContainer) return;

        if (val.trim().length === 0) {
            resultsContainer.innerHTML = '<div class="text-center text-muted py-4"><i class="fa-solid fa-keyboard fa-2x mb-2 opacity-50"></i><p class="small mb-0">Type to search vouchers, invoices, customers, suppliers, products...</p></div>';
            return;
        }

        resultsContainer.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="small mt-2 mb-0">Searching...</p></div>';

        searchDebounce = setTimeout(() => {
            fetch(`/api/search?q=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        resultsContainer.innerHTML = '<div class="text-center text-muted py-4"><p class="small mb-0">No matching records found.</p></div>';
                        return;
                    }

                    let html = '';
                    const grouped = {};
                    data.forEach(item => {
                        if (!grouped[item.category]) grouped[item.category] = [];
                        grouped[item.category].push(item);
                    });

                    for (const cat in grouped) {
                        html += `<div class="spotlight-category-header">${cat}</div>`;
                        grouped[cat].forEach(item => {
                            html += `
                                <a href="${item.url}" class="spotlight-item">
                                    <i class="fa-solid ${item.icon} text-primary"></i>
                                    <div>
                                        <div class="fw-semibold small">${item.title}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">${item.subtitle}</div>
                                    </div>
                                    <i class="fa-solid fa-arrow-right ms-auto text-muted small opacity-50"></i>
                                </a>
                            `;
                        });
                    }

                    resultsContainer.innerHTML = html;
                })
                .catch(err => {
                    resultsContainer.innerHTML = '<div class="text-center text-danger py-4"><p class="small mb-0">Search error. Please try again.</p></div>';
                });
        }, 250);
    };
})();
