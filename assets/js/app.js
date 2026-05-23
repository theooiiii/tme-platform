(function () {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-site-nav]');

    if (navToggle && nav) {
        navToggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

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
})();
