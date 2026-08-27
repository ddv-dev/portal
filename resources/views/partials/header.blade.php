{{-- resources/views/partials/header.blade.php --}}

<header class="header">
    <div class="container">

        {{-- ===== ВЕРХНЯЯ ЧАСТЬ: Логотип ===== --}}
        <div class="header__top">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.svg') }}" alt="Корпоративный портал" class="logo__image" width="40"
                    height="40">
                <span class="logo__text">Корпоративный портал</span>
            </a>
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
                        Заявки
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</header>