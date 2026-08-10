/* ───────────────────────────────────────────────────────────────
   Sistema Escolar · Main JavaScript
   ─────────────────────────────────────────────────────────────── */

const body = document.body;
const menuButton = document.querySelector('#menu-button');
const navBackdrop = document.querySelector('#nav-backdrop');
const primaryNav = document.querySelector('#primary-nav');
let menuPreviouslyFocused = null;

/* ── Mobile nav toggle ─────────────────────────────────────────── */

function setMenu(open, restoreFocus = false) {
    const wasOpen = body.classList.contains('menu-open');
    body.classList.toggle('menu-open', open);
    menuButton?.setAttribute('aria-expanded', String(open));

    if (open && !wasOpen) {
        menuPreviouslyFocused = document.activeElement instanceof HTMLElement
            ? document.activeElement
            : menuButton;

        requestAnimationFrame(() => {
            primaryNav?.querySelector('a[href], button:not([disabled])')?.focus();
        });
    }

    if (!open) {
        document.querySelectorAll('.nav-item-group.is-open').forEach((group) => {
            group.classList.remove('is-open');
            group.querySelector(':scope > .nav-link--toggle')?.setAttribute('aria-expanded', 'false');
        });

        if (wasOpen && restoreFocus) {
            (menuPreviouslyFocused?.isConnected ? menuPreviouslyFocused : menuButton)?.focus();
        }
    }
}

menuButton?.addEventListener('click', () => {
    const isOpen = body.classList.contains('menu-open');
    setMenu(!isOpen, isOpen);
});

navBackdrop?.addEventListener('click', () => setMenu(false, true));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setMenu(false, true);
        return;
    }

    if (event.key === 'Tab'
        && body.classList.contains('menu-open')
        && window.matchMedia('(max-width: 980px)').matches) {
        const focusable = Array.from(primaryNav?.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ) ?? []).filter((element) => element.getClientRects().length > 0);

        if (focusable.length === 0) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
});

document.querySelectorAll('.primary-nav > .nav-link, .nav-dropdown-item').forEach((link) => {
    link.addEventListener('click', () => setMenu(false));
});

/* ── Nav dropdown groups (tap-to-expand on mobile) ───────────────── */

document.querySelectorAll('.nav-item-group > .nav-link--toggle').forEach((toggle) => {
    const group = toggle.closest('.nav-item-group');

    toggle.addEventListener('click', () => {
        const isMobile = window.matchMedia('(max-width: 980px)').matches;

        if (isMobile) {
            const willOpen = !group.classList.contains('is-open');
            document.querySelectorAll('.nav-item-group.is-open').forEach((other) => {
                if (other !== group) {
                    other.classList.remove('is-open');
                    other.querySelector(':scope > .nav-link--toggle')?.setAttribute('aria-expanded', 'false');
                }
            });
            group.classList.toggle('is-open', willOpen);
            toggle.setAttribute('aria-expanded', String(willOpen));
        } else {
            toggle.setAttribute('aria-expanded', 'true');
        }
    });

    group?.addEventListener('mouseenter', () => {
        if (!window.matchMedia('(max-width: 980px)').matches) {
            toggle.setAttribute('aria-expanded', 'true');
        }
    });

    group?.addEventListener('mouseleave', () => {
        if (!window.matchMedia('(max-width: 980px)').matches && !group.matches(':focus-within')) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    group?.addEventListener('focusin', () => {
        if (!window.matchMedia('(max-width: 980px)').matches) {
            toggle.setAttribute('aria-expanded', 'true');
        }
    });

    group?.addEventListener('focusout', (event) => {
        if (!window.matchMedia('(max-width: 980px)').matches
            && !group.contains(event.relatedTarget)) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
});

/* ── Profile menu (desktop dropdown with logout) ─────────────────── */

const profileContainer = document.querySelector('.topbar-profile');
const profileTrigger = document.querySelector('.topbar-profile-trigger') ?? profileContainer;

function setProfileMenu(open, restoreFocus = false) {
    profileContainer?.classList.toggle('is-open', open);
    profileTrigger?.setAttribute('aria-expanded', String(open));

    if (!open && restoreFocus) {
        profileTrigger?.focus();
    }
}

setProfileMenu(profileContainer?.classList.contains('is-open') ?? false);

profileTrigger?.addEventListener('click', (event) => {
    if (event.target.closest('.profile-dropdown')) {
        return;
    }

    event.stopPropagation();
    setProfileMenu(!profileContainer?.classList.contains('is-open'));
});

document.addEventListener('click', (event) => {
    if (!profileContainer?.contains(event.target)) {
        setProfileMenu(false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && profileContainer?.classList.contains('is-open')) {
        setProfileMenu(false, true);
    }
});

profileContainer?.addEventListener('focusout', (event) => {
    if (!profileContainer.contains(event.relatedTarget)) {
        setProfileMenu(false);
    }
});

/* ── Password visibility toggle (auth forms) ─────────────────────── */

document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-toggle-password');
        const input = document.getElementById(targetId);
        if (!input) return;

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.setAttribute('aria-label', isHidden ? 'Ocultar senha' : 'Mostrar senha');
    });
});

/* ── Login credential by user type ────────────────────────────── */

const loginUserType = document.querySelector('[data-login-user-type]');
const loginPasswordField = document.querySelector('[data-login-password-field]');
const loginCpfField = document.querySelector('[data-login-cpf-field]');
const loginPasswordInput = loginPasswordField?.querySelector('input');
const loginCpfInput = loginCpfField?.querySelector('input');
const adminPasswordLink = document.querySelector('[data-admin-password-link]');
const loginPasswordToggle = loginPasswordField?.querySelector('[data-toggle-password]');

if (loginUserType && loginPasswordField && loginCpfField && loginPasswordInput && loginCpfInput) {
    const syncLoginCredential = (clearCredentials = false) => {
        const usesCpf = ['professor', 'aluno'].includes(loginUserType.value);

        if (clearCredentials) {
            loginPasswordInput.value = '';
            loginPasswordInput.type = 'password';
            loginCpfInput.value = '';
            loginPasswordToggle?.setAttribute('aria-label', 'Mostrar senha');
        }

        loginPasswordField.hidden = usesCpf;
        loginPasswordInput.disabled = usesCpf;
        loginPasswordInput.required = !usesCpf;

        loginCpfField.hidden = !usesCpf;
        loginCpfInput.disabled = !usesCpf;
        loginCpfInput.required = usesCpf;

        if (adminPasswordLink) {
            adminPasswordLink.hidden = usesCpf;
        }
    };

    loginUserType.addEventListener('change', () => {
        syncLoginCredential(true);
        (['professor', 'aluno'].includes(loginUserType.value) ? loginCpfInput : loginPasswordInput).focus();
    });
    syncLoginCredential();
}

/* ── Custom confirm modal ──────────────────────────────────────── */

let confirmModalSequence = 0;

function createConfirmModal(message) {
    return new Promise((resolve) => {
        const previouslyFocused = document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;
        const titleId = `confirm-modal-title-${++confirmModalSequence}`;
        const messageId = `confirm-modal-message-${confirmModalSequence}`;
        const overlay = document.createElement('div');
        overlay.className = 'confirm-modal-overlay';
        overlay.innerHTML = `
            <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="${titleId}" aria-describedby="${messageId}" tabindex="-1">
                <div class="confirm-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28">
                        <path d="M12 9v4m0 4h.01M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </div>
                <h3 class="confirm-modal-title" id="${titleId}">Confirmar exclusão</h3>
                <p class="confirm-modal-message" id="${messageId}"></p>
                <div class="confirm-modal-actions">
                    <button class="confirm-btn-cancel" type="button">Cancelar</button>
                    <button class="confirm-btn-danger" type="button">Excluir</button>
                </div>
            </div>
        `;

        const dialog = overlay.querySelector('.confirm-modal');
        const messageElement = overlay.querySelector('.confirm-modal-message');
        const cancelButton = overlay.querySelector('.confirm-btn-cancel');
        const dangerButton = overlay.querySelector('.confirm-btn-danger');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let isClosing = false;

        // The confirmation text can come from markup, so it must never be parsed as HTML.
        messageElement.textContent = message;

        const focusableSelector = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])',
        ].join(',');

        const close = (result) => {
            if (isClosing) {
                return;
            }

            isClosing = true;
            document.removeEventListener('keydown', onKeydown);
            overlay.classList.add('is-closing');
            dialog.classList.add('is-closing');
            setTimeout(() => {
                overlay.remove();
                if (previouslyFocused?.isConnected) {
                    previouslyFocused.focus();
                }
                resolve(result);
            }, prefersReducedMotion ? 0 : 200);
        };

        const onKeydown = (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                close(false);
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            const focusableElements = Array.from(dialog.querySelectorAll(focusableSelector))
                .filter((element) => element.getClientRects().length > 0);

            if (focusableElements.length === 0) {
                event.preventDefault();
                dialog.focus();
                return;
            }

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (event.shiftKey && document.activeElement === firstElement) {
                event.preventDefault();
                lastElement.focus();
            } else if (!event.shiftKey && document.activeElement === lastElement) {
                event.preventDefault();
                firstElement.focus();
            }
        };

        cancelButton.addEventListener('click', () => close(false));
        dangerButton.addEventListener('click', () => close(true));
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) close(false);
        });

        document.body.appendChild(overlay);
        document.addEventListener('keydown', onKeydown);

        requestAnimationFrame(() => {
            cancelButton.focus();
        });
    });
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const message = form.dataset.confirm || 'Deseja excluir este registro?';
        createConfirmModal(message).then((confirmed) => {
            if (confirmed) {
                form.removeAttribute('data-confirm');
                form.submit();
            }
        });
    });
});

/* ── Input masks ───────────────────────────────────────────────── */

function formatCpf(value) {
    return value
        .replace(/\D/g, '')
        .slice(0, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

function formatPhone(value) {
    const digits = value.replace(/\D/g, '').slice(0, 11);

    if (digits.length <= 10) {
        return digits
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    }

    return digits
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d)/, '$1-$2');
}

document.querySelectorAll('[data-mask]').forEach((input) => {
    const formatter = input.dataset.mask === 'cpf' ? formatCpf : formatPhone;

    input.addEventListener('input', () => {
        input.value = formatter(input.value);
    });
});

/* ── Attendance justification ─────────────────────────────────── */

const attendanceStatus = document.querySelector('#attendance-status');
const justificationField = document.querySelector('[data-justification-field]');
const justificationInput = document.querySelector('#attendance-justification');

if (attendanceStatus && justificationField && justificationInput) {
    const syncJustification = () => {
        const acceptsJustification = ['ausente', 'justificada'].includes(attendanceStatus.value);
        justificationField.hidden = !acceptsJustification;
        justificationInput.disabled = !acceptsJustification;
        justificationInput.required = attendanceStatus.value === 'justificada';
    };

    attendanceStatus.addEventListener('change', syncJustification);
    syncJustification();
}

/* ── Batch attendance rows ────────────────────────────────────── */

const batchAttendance = document.querySelector('[data-batch-attendance]');

if (batchAttendance) {
    const rows = Array.from(batchAttendance.querySelectorAll('[data-attendance-row]'));

    const syncRow = (row, clearWhenDisabled = false) => {
        const status = row.querySelector('[data-row-status]');
        const justification = row.querySelector('[data-row-justification]');
        if (!status || !justification) return;

        const acceptsJustification = ['ausente', 'justificada'].includes(status.value);
        justification.disabled = !acceptsJustification;
        justification.required = status.value === 'justificada';

        if (!acceptsJustification && clearWhenDisabled) {
            justification.value = '';
        }
    };

    rows.forEach((row) => {
        const status = row.querySelector('[data-row-status]');
        status?.addEventListener('change', () => syncRow(row, true));
        syncRow(row);
    });

    batchAttendance.querySelector('[data-mark-all-present]')?.addEventListener('click', () => {
        rows.forEach((row) => {
            const status = row.querySelector('[data-row-status]');
            if (status) status.value = 'presente';
            syncRow(row, true);
        });

        rows[0]?.querySelector('[data-row-status]')?.focus();
    });
}

/* ── Counter animation ─────────────────────────────────────────── */

function animateCounter(element) {
    const target = parseInt(element.dataset.target, 10);
    if (isNaN(target) || target === 0) {
        element.textContent = '0';
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        element.textContent = target.toLocaleString('pt-BR');
        return;
    }

    const duration = 1200;
    const startTime = performance.now();

    function easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    }

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutQuart(progress);
        const current = Math.round(target * easedProgress);

        element.textContent = current.toLocaleString('pt-BR');

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    requestAnimationFrame(update);
}

/* ── Intersection Observer for animations ──────────────────────── */

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -30px 0px',
};

const animationObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');

            // Trigger counter animations inside this element
            entry.target.querySelectorAll('.counter-value').forEach((counter) => {
                if (!counter.dataset.animated) {
                    counter.dataset.animated = 'true';
                    animateCounter(counter);
                }
            });

            animationObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.animate-fade-in-up').forEach((el) => {
    animationObserver.observe(el);
});

/* ── Alert auto-dismiss ────────────────────────────────────────── */

document.querySelectorAll('.alert').forEach((alert) => {
    // Auto-dismiss success alerts after 6 seconds
    if (alert.classList.contains('alert-success')) {
        setTimeout(() => {
            alert.classList.add('is-dismissing');
            setTimeout(() => alert.remove(), 300);
        }, 6000);
    }
});
