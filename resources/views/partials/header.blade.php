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
                    {{ $profile?->name_full ?? session('windows_fio') ?? 'Гость' }}
                    <span class="user-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </span>
                </button>


                <div class="user-tooltip" id="userTooltip">
                    {{ $profile['department'] }}{{ $profile['position'] ? ': '.$profile['position'] : '' }}
                    <br>
                    Почта: {{ $profile['email'] ?: '—' }}
                    <br>
                    Логин: {{ $profile['username'] ?: '—' }}
                </div>


            </div>

            <div class="user-phone-block" id="userPhoneBlock">

                {{-- Городской --}}
                <form method="POST" action="{{ route('profile.phone.update') }}" class="phone-row">
                    @csrf
                    <span class="phone-label">Городской:</span>
                    <input type="text"
                        name="city"
                        id="cityInput"
                        value="{{ $profile['city_phone'] ?: '' }}"
                        placeholder="например, 4955322333"
                        class="phone-input"
                        readonly>
                    <button type="button" class="phone-edit-btn" data-target="cityInput" aria-label="Редактировать">
                        <svg class="icon-edit">

                            <rect width="24" height="24" fill="white" />
                            <path d="M15.6287 5.12132L4.31497 16.435M15.6287 5.12132L19.1642 8.65685M15.6287 5.12132L17.0429 3.70711C17.4334 3.31658 18.0666 3.31658 18.4571 3.70711L20.5784 5.82843C20.969 6.21895 20.969 6.85212 20.5784 7.24264L19.1642 8.65685M7.85051 19.9706L4.31497 16.435M7.85051 19.9706L19.1642 8.65685M7.85051 19.9706L3.25431 21.0312L4.31497 16.435" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg class="icon-save icon-save_sity" style="display:none;">...</svg>
                    </button>
                </form>

                {{-- Внутренний --}}
                <form method="POST" action="{{ route('profile.phone.update') }}" class="phone-row">
                    @csrf
                    <span class="phone-label">Внутренний:</span>
                    <input type="text"
                        name="mobile"
                        id="mobileInput"
                        value="{{ $profile['mobile_phone'] ?: '' }}"
                        placeholder="от 4 до 6 цифр"
                        class="phone-input"
                        readonly>
                    <button type="button" class="phone-edit-btn" data-target="mobileInput" aria-label="Редактировать">
                        <svg class="icon-edit icon-save_mob">
                            <rect width="24" height="24" fill="white" />
                            <path d="M15.6287 5.12132L4.31497 16.435M15.6287 5.12132L19.1642 8.65685M15.6287 5.12132L17.0429 3.70711C17.4334 3.31658 18.0666 3.31658 18.4571 3.70711L20.5784 5.82843C20.969 6.21895 20.969 6.85212 20.5784 7.24264L19.1642 8.65685M7.85051 19.9706L4.31497 16.435M7.85051 19.9706L19.1642 8.65685M7.85051 19.9706L3.25431 21.0312L4.31497 16.435" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </form>

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
                    <a href="{{ route('redmine.appeals') }}"
                        class="nav__link {{ request()->routeIs('appeals') ? 'nav__link--active' : '' }}">
                        Обращения
                    </a>
                </li>
            </ul>
        </nav>

    </div>

</header>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.phone-edit-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                const input = document.getElementById(this.dataset.target);
                const iconEdit = this.querySelector('.icon-edit');
                const iconSave = this.querySelector('.icon-save');
                const form = input.closest('form');

                // Ещё не редактируем — включаем режим
                if (input.hasAttribute('readonly')) {
                    input.removeAttribute('readonly');
                    input.focus();
                    iconEdit.style.display = 'none';
                    iconSave.style.display = 'inline';
                    return;
                }

                // Валидация на клиенте
                const type = input.name; // 'city' | 'mobile'
                const value = input.value.trim();

                if (value !== '') {
                    if (type === 'city' && !/^\d{11}$/.test(value)) {
                        alert('Городской номер должен содержать ровно 11 цифр (например, 4955322333).');
                        input.focus();
                        return;
                    }
                    if (type === 'mobile' && !/^\d{4,6}$/.test(value)) {
                        alert('Внутренний номер должен содержать от 4 до 6 цифр.');
                        input.focus();
                        return;
                    }
                }

                // Отправляем форму обычным POST — страница перезагрузится
                form.submit();
            });
        });
    });
</script>

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





    .phone-row {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
    }

    .phone-label {
        font-size: 14px;
        color: #555;
        white-space: nowrap;
    }

    .phone-input {
        flex: 1;
        min-width: 0;
        padding: 4px 8px;
        border: 1px solid transparent;
        border-radius: 4px;
        background: transparent;
        font-size: 14px;
        color: #222;
        transition: border-color 0.2s, background 0.2s;
    }

    .phone-input[readonly] {
        cursor: default;
        border-color: transparent;
    }

    .phone-input:not([readonly]) {
        border-color: var(--color-component, #4a90d9);
        background: #fff;
        cursor: text;
    }

    .phone-edit-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
        border: none;
        border-radius: 4px;
        background: transparent;
        cursor: pointer;
        transition: background 0.2s;
    }

    .phone-edit-btn:hover {
        background: #f0f0f0;
    }

    .phone-edit-btn svg {
        display: block;
    }
</style>