// resources/js/home.js

// Импортируем компоненты
import { initSearchHandler } from './components/searchHandler';
import { initUrlManager } from './components/urlManager';
import { initResetHandlers } from './components/resetHandlers';

// Экспортируем функцию инициализации
export function initHomePage() {
    console.log('Home page initialized');
    
    // Инициализация компонентов
    initUrlManager();
    initSearchHandler();
    initResetHandlers();
    
    console.log('Home page initialization complete');
}