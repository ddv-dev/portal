// resources/js/directory.js

import { initSubMenuToggle } from './components/subMenuToggle';
import { initUrlManager } from './components/urlManager';
import { initSearchHandler } from './components/searchHandler';
import { initFilterHandlers } from './components/filterHandlers';
import { initResetHandlers } from './components/resetHandlers';
import { initActiveMenuExpander } from './components/activeMenuExpander';

// Экспортируем функцию для инициализации
export function initPhoneBook() {
    console.log('PhoneBook initialized');

    // Инициализация всех компонентов
    initSubMenuToggle();
    initUrlManager();
    initSearchHandler();
    initFilterHandlers();
    initResetHandlers();
    initActiveMenuExpander();

    console.log('PhoneBook initialization complete');
}