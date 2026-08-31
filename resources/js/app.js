// resources/js/app.js

// Импортируем функцию из directory.js
import { initPhoneBook } from './directory.js';

console.log('App.js loaded');

// Инициализируем при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing PhoneBook...');
    initPhoneBook();
});