// resources/js/components/urlManager.js

export function initUrlManager() {
    // Функция для обновления URL с параметрами
    window.updateUrl = function(params) {
        const url = new URL(window.location.href);
        url.search = '';

        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== '' && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            }
        });

        window.location.href = url.toString();
    };

    // Функция для получения текущих параметров
    window.getCurrentParams = function() {
        const params = {};
        const searchParams = new URLSearchParams(window.location.search);
        for (let [key, value] of searchParams.entries()) {
            params[key] = value;
        }
        return params;
    };
}