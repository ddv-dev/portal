// resources/js/components/resetHandlers.js

export function initResetHandlers() {
    // Обработка сброса фильтров
    document.querySelectorAll('[data-reset]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const filterName = this.dataset.reset;
            const params = window.getCurrentParams();

            delete params[filterName];

            if (filterName === 'corpus_id') {
                delete params.floor;
            }

            const search = document.getElementById('searchInput')?.value;
            if (search) {
                params.search = search;
            } else {
                delete params.search;
            }

            window.updateUrl(params);
        });
    });

    // Обработка кнопки "Сбросить все"
    document.querySelector('[data-reset-all]')?.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.href;
    });
}