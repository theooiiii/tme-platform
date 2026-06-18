(function () {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-site-nav]');

    if (navToggle && nav) {
        navToggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    const notificationMenu = document.querySelector('[data-notification-menu]');
    const notificationToggle = document.querySelector('[data-notification-toggle]');

    if (notificationMenu && notificationToggle) {
        notificationToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            notificationMenu.classList.toggle('open');
        });

        document.addEventListener('click', function (event) {
            if (!notificationMenu.contains(event.target)) {
                notificationMenu.classList.remove('open');
            }
        });
    }

    document.querySelectorAll('[data-dropdown]').forEach(function (dropdown) {
        const toggle = dropdown.querySelector('[data-dropdown-toggle]');

        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            const isOpen = dropdown.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            document.querySelectorAll('[data-dropdown]').forEach(function (otherDropdown) {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('open');
                    const otherToggle = otherDropdown.querySelector('[data-dropdown-toggle]');
                    if (otherToggle) {
                        otherToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('[data-dropdown]').forEach(function (dropdown) {
            dropdown.classList.remove('open');
            const toggle = dropdown.querySelector('[data-dropdown-toggle]');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    const themeForm = document.querySelector('.theme-form');

    if (themeForm) {
        const themeSelect = themeForm.querySelector('select[name="theme"]');
        const colorInput = themeForm.querySelector('input[name="primary_color"]');

        if (themeSelect) {
            themeSelect.addEventListener('change', function () {
                document.body.dataset.theme = themeSelect.value;
            });
        }

        if (colorInput) {
            colorInput.addEventListener('input', function () {
                document.body.style.setProperty('--accent', colorInput.value);
            });
        }
    }

    const profileTheme = document.querySelector('[data-profile-theme]');
    const profileColor = document.querySelector('[data-profile-color]');

    if (profileTheme) {
        profileTheme.addEventListener('change', function () {
            document.body.dataset.theme = profileTheme.value;
        });
    }

    if (profileColor) {
        profileColor.addEventListener('input', function () {
            document.body.style.setProperty('--accent', profileColor.value);
        });
    }

    const independentToggle = document.querySelector('[data-independent-toggle]');
    const institutionField = document.querySelector('[data-institution-field]');
    const institutionInput = document.querySelector('[data-institution-search]');
    const suggestions = document.querySelector('#institution-suggestions');

    function syncInstitutionField() {
        if (!independentToggle || !institutionField || !institutionInput) {
            return;
        }

        const disabled = independentToggle.checked;
        institutionInput.disabled = disabled;
        institutionInput.required = !disabled;
        institutionField.classList.toggle('muted', disabled);
    }

    if (independentToggle) {
        independentToggle.addEventListener('change', syncInstitutionField);
        syncInstitutionField();
    }

    if (institutionInput && suggestions) {
        let timer = null;

        institutionInput.addEventListener('input', function () {
            const term = institutionInput.value.trim();
            clearTimeout(timer);

            if (term.length < 2) {
                suggestions.innerHTML = '';
                return;
            }

            timer = setTimeout(function () {
                const base = document.body.dataset.baseUrl || '';

                fetch(base + '/instituicoes/buscar?q=' + encodeURIComponent(term), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (response) {
                        return response.ok ? response.json() : { data: [] };
                    })
                    .then(function (payload) {
                        suggestions.innerHTML = '';

                        payload.data.forEach(function (institution) {
                            const option = document.createElement('option');
                            const place = [institution.city, institution.state].filter(Boolean).join(' / ');
                            option.value = institution.name;
                            option.label = place ? institution.name + ' - ' + place : institution.name;
                            suggestions.appendChild(option);
                        });
                    })
                    .catch(function () {
                        suggestions.innerHTML = '';
                    });
            }, 280);
        });
    }

    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm || 'Confirmar ação?')) {
                event.preventDefault();
            }
        });
    });
    const examTimer = document.querySelector('[data-exam-timer]');
    const examTimerOutput = document.querySelector('[data-exam-timer-output]');

    if (examTimer && examTimerOutput) {
        let remaining = parseInt(examTimer.dataset.examTimer || '0', 10);

        const renderTimer = function () {
            const minutes = Math.floor(Math.max(0, remaining) / 60);
            const seconds = Math.max(0, remaining) % 60;
            examTimerOutput.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            examTimer.classList.toggle('danger', remaining <= 60);

            if (remaining > 0) {
                remaining -= 1;
            }
        };

        renderTimer();
        window.setInterval(renderTimer, 1000);
    }

    const chatShell = document.querySelector('[data-chat-refresh]');

    if (chatShell) {
        const interval = parseInt(chatShell.dataset.chatRefresh || '45000', 10);

        window.setInterval(function () {
            const active = document.activeElement;
            const isTyping = active && ['TEXTAREA', 'INPUT', 'SELECT'].includes(active.tagName);

            if (!isTyping) {
                window.location.reload();
            }
        }, Math.max(interval, 15000));
    }

    document.querySelectorAll('[data-lesson-notes]').forEach(function (textarea) {
        const key = 'tme:lesson-notes:' + textarea.dataset.lessonNotes;
        textarea.value = window.localStorage.getItem(key) || '';
        textarea.addEventListener('input', function () {
            window.localStorage.setItem(key, textarea.value);
        });
    });

    function chartColors() {
        const styles = window.getComputedStyle(document.body);
        return {
            accent: styles.getPropertyValue('--accent').trim() || '#1f6feb',
            text: styles.getPropertyValue('--text').trim() || '#101214',
            border: styles.getPropertyValue('--border').trim() || '#d9dee7'
        };
    }

    function drawCanvasFallback(canvas, payload) {
        const context = canvas.getContext('2d');

        if (!context) {
            return;
        }

        const colors = chartColors();
        const rect = canvas.getBoundingClientRect();
        const width = Math.max(320, Math.floor(rect.width || canvas.width || 640));
        const height = Math.max(220, parseInt(canvas.getAttribute('height') || '260', 10));
        canvas.width = width;
        canvas.height = height;
        context.clearRect(0, 0, width, height);
        context.strokeStyle = colors.border;
        context.fillStyle = colors.text;
        context.lineWidth = 1;
        context.font = '12px system-ui, sans-serif';

        const values = (payload.datasets && payload.datasets[0] && payload.datasets[0].data || []).map(Number);
        const labels = payload.labels || [];
        const max = Math.max(1, ...values);
        const left = 34;
        const top = 24;
        const bottom = height - 34;
        const usableWidth = width - left - 18;
        const usableHeight = bottom - top;

        context.beginPath();
        context.moveTo(left, top);
        context.lineTo(left, bottom);
        context.lineTo(width - 12, bottom);
        context.stroke();

        if (!values.length) {
            context.fillText('Sem dados para o periodo.', left + 12, top + 26);
            return;
        }

        if (payload.type === 'line') {
            context.strokeStyle = colors.accent;
            context.lineWidth = 3;
            context.beginPath();
            values.forEach(function (value, index) {
                const x = left + (values.length === 1 ? usableWidth / 2 : (usableWidth / (values.length - 1)) * index);
                const y = bottom - (value / max) * usableHeight;
                if (index === 0) {
                    context.moveTo(x, y);
                } else {
                    context.lineTo(x, y);
                }
            });
            context.stroke();
        } else {
            const gap = 8;
            const barWidth = Math.max(12, (usableWidth - gap * values.length) / values.length);
            context.fillStyle = colors.accent;
            values.forEach(function (value, index) {
                const x = left + index * (barWidth + gap) + gap / 2;
                const barHeight = (value / max) * usableHeight;
                context.fillRect(x, bottom - barHeight, barWidth, barHeight);
            });
        }

        context.fillStyle = colors.text;
        context.fillText(labels[0] || '', left, height - 12);
        context.fillText(labels[labels.length - 1] || '', Math.max(left, width - 120), height - 12);
    }

    document.querySelectorAll('canvas[data-chart]').forEach(function (canvas) {
        const source = document.querySelector(canvas.dataset.chart);

        if (!source) {
            return;
        }

        let payload = null;

        try {
            payload = JSON.parse(source.textContent || '{}');
        } catch (error) {
            return;
        }

        if (window.Chart) {
            const colors = chartColors();
            const dataset = payload.datasets && payload.datasets[0] ? payload.datasets[0] : {};
            dataset.borderColor = colors.accent;
            dataset.backgroundColor = payload.type === 'line' ? colors.accent + '33' : colors.accent;
            dataset.tension = 0.35;
            payload.datasets = [dataset];

            new window.Chart(canvas, {
                type: payload.type || 'bar',
                data: {
                    labels: payload.labels || [],
                    datasets: payload.datasets || []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: colors.text } }
                    },
                    scales: {
                        x: { ticks: { color: colors.text }, grid: { color: colors.border } },
                        y: { beginAtZero: true, ticks: { color: colors.text }, grid: { color: colors.border } }
                    }
                }
            });
        } else {
            drawCanvasFallback(canvas, payload);
        }
    });
})();
