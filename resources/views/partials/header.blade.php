{{-- resources/views/partials/header.blade.php --}}

<header class="header">
    <div class="container">

        {{-- ===== ВЕРХНЯЯ ЧАСТЬ: Логотип ===== --}}
        <div class="header__top">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.svg') }}" alt="Корпоративный портал" class="logo__image" width="40"
                    height="40">
                <span class="logo__text">@yield('title', "Корпоративный портал")</span>
            </a>

            <div class="user-menu" id="userMenu">
                <button class="button button-white" id="userIconBtn" aria-label="Профиль">
                    {{ auth('sso')->user()->fio}}
                    <span class="user-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </span>

                </button>

                @auth('sso')
                <div class="user-tooltip" id="userTooltip">
                    {{ auth('sso')->user()->department .': '.auth('sso')->user()->position}}
                    <br>
                    Почта: {{ auth('sso')->user()->email}}
                    <br>
                    Логин: {{ auth('sso')->user()->username }}
                    <br>
                </div>
                @endauth

            </div>
        </div>
        {{-- ===== НАВИГАЦИЯ (горизонтальное меню) ===== --}}
        <nav class="nav">
            <ul class="nav__list">
                <li class="nav__item">
                    <a href="{{ route('home') }}"
                        class="nav__link {{ request()->routeIs('home') ? 'nav__link--active' : '' }}">
                        Новости
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('directory') }}"
                        class="nav__link {{ request()->routeIs('directory') ? 'nav__link--active' : '' }}">
                        Телефонный справочник
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('info') }}"
                        class="nav__link {{ request()->routeIs('info') ? 'nav__link--active' : '' }}">
                        Информации
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('sop') }}"
                        class="nav__link {{ request()->routeIs('sop') ? 'nav__link--active' : '' }}">
                        СОП
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('orders') }}"
                        class="nav__link {{ request()->routeIs('orders') ? 'nav__link--active' : '' }}">
                        Приказы
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('birthdays') }}"
                        class="nav__link {{ request()->routeIs('birthdays') ? 'nav__link--active' : '' }}">
                        Дни рождения
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('appeals') }}"
                        class="nav__link {{ request()->routeIs('appeals') ? 'nav__link--active' : '' }}">
                        Обращения
                    </a>
                </li>
                <li class="nav__item">
                    <a href="{{ route('new_worker') }}"
                        class="nav__link {{ request()->routeIs('new_worker') ? 'nav__link--active' : '' }}">
                        Новому сотруднику
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</header>

<style>
    .site-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 24px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }

    .user-menu {
        position: relative;
        display: inline-block;
        margin-left: auto;
    }


    .user-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 50%;
        background: var(--color-component);
        color: white;
        cursor: pointer;
        transition: background 0.2s;
    }

    .user-icon:hover {
        background: var(--color-primary-light);
    }

    .user-tooltip {
        position: absolute;
        top: calc(100% + 8px);
        /* снизу от иконки */
        right: 0;
        /* выравнивание по правому краю */
        background: var(--color-primary-light);
        color: white;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 14px;
        white-space: nowrap;
        box-shadow: 0 2px 6px var(--color-primary-light);

        opacity: 0;
        visibility: hidden;
        transform: translateY(-4px);
        transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
        pointer-events: none;
        z-index: 100;
    }

    .user-tooltip::before {
        content: "";
        position: absolute;
        bottom: 100%;
        right: 43px;
        border: 6px solid transparent;
        border-bottom-color: var(--color-primary);
    }

    .user-menu:hover .user-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
</style>