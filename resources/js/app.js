// resources/js/app.js

// Импортируем функцию из directory.js
import { initPhoneBook } from './directory.js';
import { initHomePage } from './home.js';

console.log('App.js loaded');

// Инициализируем при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    initPhoneBook();
    initHomePage();
});

// Экспортируем для возможного использования в других местах
export { initPhoneBook };
export { initHomePage };