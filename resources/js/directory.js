// resources/js/directory.js

// Экспортируем функцию для Vite
export function initPhoneBook() {
    console.log('PhoneBook initialized'); // Для отладки

    // Функция для переключения видимости подменю
    function toggleSubMenu(toggleLink) {
        const targetId = toggleLink.dataset.toggle;
        if (!targetId) return;
        
        const subMenu = document.getElementById(targetId);
        if (!subMenu) return;
        
        const arrow = toggleLink.querySelector('.arrow');
        
        subMenu.classList.toggle('open');
        
        if (arrow) {
            arrow.classList.toggle('open');
        }
    }

    // Функция для обновления URL с параметрами
    function updateUrl(params) {
        const url = new URL(window.location.href);
        url.search = '';

        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== '' && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            }
        });

        window.location.href = url.toString();
    }

    // Функция для получения текущих параметров
    function getCurrentParams() {
        const params = {};
        const searchParams = new URLSearchParams(window.location.search);
        for (let [key, value] of searchParams.entries()) {
            params[key] = value;
        }
        return params;
    }

    // Функция для раскрытия активных элементов
    function expandActiveParents(element) {
        let parent = element.parentElement;
        while (parent) {
            if (parent.classList.contains('sub-menu') || parent.classList.contains('floor-sub-menu')) {
                parent.classList.add('open');
                
                const toggleLink = document.querySelector(`[data-toggle="${parent.id}"]`);
                if (toggleLink) {
                    const arrow = toggleLink.querySelector('.arrow');
                    if (arrow) {
                        arrow.classList.add('open');
                    }
                }
            }
            parent = parent.parentElement;
        }
    }

    // 1. Обработка кликов по toggle-link
    document.querySelectorAll('.toggle-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Если клик по стрелке или toggle
            if (e.target.closest('.arrow') || !e.target.closest('[data-filter]')) {
                e.preventDefault();
                toggleSubMenu(this);
                return;
            }
        });
    });

    // 2. Автоматический поиск при вводе
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let timeoutId;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                const params = getCurrentParams();
                if (this.value) {
                    params.search = this.value;
                } else {
                    delete params.search;
                }
                updateUrl(params);
            }, 500);
        });
    }

    // 3. Обработка кликов по фильтрам
    document.querySelectorAll('[data-filter]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const filterName = this.dataset.filter;
            const filterValue = this.dataset.value;
            const corpusId = this.dataset.corpusId;
            const params = getCurrentParams();

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

            updateUrl(params);
        });
    });

    // 4. Обработка сброса фильтров
    document.querySelectorAll('[data-reset]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const filterName = this.dataset.reset;
            const params = getCurrentParams();

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

            updateUrl(params);
        });
    });

    // 5. Обработка кнопки "Сбросить все"
    document.querySelector('[data-reset-all]')?.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.href;
    });

    // 6. Раскрытие активных элементов
    document.querySelectorAll('.filter-link.active').forEach(function(activeLink) {
        expandActiveParents(activeLink);
    });

    // 7. Раскрытие корпусов с активными этажами
    document.querySelectorAll('.floor-link.active').forEach(function(floorLink) {
        const floorMenu = floorLink.closest('.floor-sub-menu');
        if (floorMenu) {
            floorMenu.classList.add('open');
            
            const corpusMenu = floorMenu.closest('.sub-menu');
            if (corpusMenu) {
                corpusMenu.classList.add('open');
                
                const toggleLink = document.querySelector(`[data-toggle="${corpusMenu.id}"]`);
                if (toggleLink) {
                    const arrow = toggleLink.querySelector('.arrow');
                    if (arrow) {
                        arrow.classList.add('open');
                    }
                }
                
                const buildingMenu = corpusMenu.closest('.sub-menu');
                if (buildingMenu) {
                    buildingMenu.classList.add('open');
                    
                    const buildingToggle = document.querySelector(`[data-toggle="${buildingMenu.id}"]`);
                    if (buildingToggle) {
                        const arrow = buildingToggle.querySelector('.arrow');
                        if (arrow) {
                            arrow.classList.add('open');
                        }
                    }
                }
            }
        }
    });

    console.log('PhoneBook initialization complete');
}
