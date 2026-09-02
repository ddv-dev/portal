// resources/js/components/filterHandlers.js

export function initFilterHandlers() {
    // Обработка кликов по фильтрам
    document.querySelectorAll('[data-filter]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const filterName = this.dataset.filter;
            const filterValue = this.dataset.value;
            const corpusId = this.dataset.corpusId;
            const params = window.getCurrentParams();

            const search = document.getElementById('searchInput')?.value;
            if (search) {
                params.search = search;
            } else {
                delete params.search;
            }

            if (filterName === 'floor' && corpusId) {
                params.corpus_id = corpusId;
            }

            if (filterValue) {
                params[filterName] = filterValue;
            } else {
                delete params[filterName];
            }

            window.updateUrl(params);
        });
    });
}