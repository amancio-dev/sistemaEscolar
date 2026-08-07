/* ───────────────────────────────────────────────────────────────
   Sistema Escolar · Main JavaScript
   ─────────────────────────────────────────────────────────────── */

const body = document.body;
const menuButton = document.querySelector('#menu-button');
const navBackdrop = document.querySelector('#nav-backdrop');

/* ── Mobile nav toggle ─────────────────────────────────────────── */

function setMenu(open) {
    body.classList.toggle('menu-open', open);
    menuButton?.setAttribute('aria-expanded', String(open));
    if (!open) {
        document.querySelectorAll('.nav-item-group.is-open').forEach((group) => {
            group.classList.remove('is-open');
        });
    }
}

menuButton?.addEventListener('click', () => {
    setMenu(!body.classList.contains('menu-open'));
});

navBackdrop?.addEventListener('click', () => setMenu(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setMenu(false);
        // Also close any open confirm modal
        const modal = document.querySelector('.confirm-modal-overlay');
        if (modal) modal.remove();
    }
});

document.querySelectorAll('.primary-nav > .nav-link, .nav-dropdown-item').forEach((link) => {
    link.addEventListener('click', () => setMenu(false));
});

/* ── Nav dropdown groups (tap-to-expand on mobile) ───────────────── */

document.querySelectorAll('.nav-item-group > .nav-link--toggle').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const group = toggle.closest('.nav-item-group');
        const isMobile = window.matchMedia('(max-width: 980px)').matches;

        if (isMobile) {
            const willOpen = !group.classList.contains('is-open');
            document.querySelectorAll('.nav-item-group.is-open').forEach((other) => {
                if (other !== group) other.classList.remove('is-open');
            });
            group.classList.toggle('is-open', willOpen);
            toggle.setAttribute('aria-expanded', String(willOpen));
        }
    });
});

/* ── Profile menu (desktop dropdown with logout) ─────────────────── */

const profileTrigger = document.querySelector('.topbar-profile');

profileTrigger?.addEventListener('click', (event) => {
    event.stopPropagation();
    profileTrigger.classList.toggle('is-open');
});

document.addEventListener('click', () => {
    profileTrigger?.classList.remove('is-open');
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

/* ── Custom confirm modal ──────────────────────────────────────── */

function createConfirmModal(message) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-modal-overlay';
        overlay.innerHTML = `
            <div class="confirm-modal">
                <div class="confirm-modal-icon">
                    <svg viewBox="0 0 24 24" width="28" height="28">
                        <path d="M12 9v4m0 4h.01M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </div>
                <h3 class="confirm-modal-title">Confirmar exclusão</h3>
                <p class="confirm-modal-message">${message}</p>
                <div class="confirm-modal-actions">
                    <button class="confirm-btn-cancel" type="button">Cancelar</button>
                    <button class="confirm-btn-danger" type="button">Excluir</button>
                </div>
            </div>
        `;

        const close = (result) => {
            overlay.classList.add('is-closing');
            overlay.querySelector('.confirm-modal').classList.add('is-closing');
            setTimeout(() => {
                overlay.remove();
                resolve(result);
            }, 200);
        };

        overlay.querySelector('.confirm-btn-cancel').addEventListener('click', () => close(false));
        overlay.querySelector('.confirm-btn-danger').addEventListener('click', () => close(true));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close(false);
        });

        document.body.appendChild(overlay);

        // Focus the cancel button for accessibility
        requestAnimationFrame(() => {
            overlay.querySelector('.confirm-btn-cancel').focus();
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

/* ── Counter animation ─────────────────────────────────────────── */

function animateCounter(element) {
    const target = parseInt(element.dataset.target, 10);
    if (isNaN(target) || target === 0) {
        element.textContent = '0';
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
