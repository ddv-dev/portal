// resources/js/components/activeMenuExpander.js

export function initActiveMenuExpander() {
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

    // Раскрытие активных элементов
    document.querySelectorAll('.filter-link.active').forEach(function(activeLink) {
        expandActiveParents(activeLink);
    });

    // Раскрытие корпусов с активными этажами
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
}