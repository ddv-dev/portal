// resources/js/components/subMenuToggle.js

export function initSubMenuToggle() {
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

    // Обработка кликов по toggle-link
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
}