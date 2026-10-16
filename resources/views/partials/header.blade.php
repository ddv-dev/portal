<header class="header">
    <div class="container">

        {{-- ===== ВЕРХНЯЯ ЧАСТЬ ===== --}}
        <div class="header__top">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.svg') }}" alt="Корпоративный портал" class="logo__image" width="40" height="40">
                <span class="logo__text">@yield('title', "Корпоративный портал")</span>
            </a>

            <div class="col">
                <div class="header__actions">

                    {{-- ===== Колокольчик ===== --}}
                    <button type="button" class="bell-btn" id="openNotifications" aria-label="Уведомления">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span id="notif-badge"
                            class="bell-btn__badge {{ ($unreadCount ?? 0) > 0 ? '' : 'd-none' }}">
                            {{ $unreadCount ?? 0 }}
                        </span>
                    </button>

                    {{-- ===== Профиль ===== --}}
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
                </div>

                {{-- ===== Кнопка попапа телефонов ===== --}}
                <div class="user-phone-block" id="userPhoneBlock">
                    <button type="button" class="btn phone-edit-open" id="openPhoneModal">
                        Изменить мой номер
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== НАВИГАЦИЯ ===== --}}
        <nav class="nav">
            <ul class="nav__list">
                <li class="nav__item"><a href="{{ route('home') }}" class="nav__link {{ request()->routeIs('home') ? 'nav__link--active' : '' }}">Новости</a></li>
                <li class="nav__item"><a href="{{ route('directory') }}" class="nav__link {{ request()->routeIs('directory') ? 'nav__link--active' : '' }}">Телефонный справочник</a></li>
                <li class="nav__item"><a href="{{ route('info') }}" class="nav__link {{ request()->routeIs('info') ? 'nav__link--active' : '' }}">Информации</a></li>
                <li class="nav__item"><a href="{{ route('sop') }}" class="nav__link {{ request()->routeIs('sop') ? 'nav__link--active' : '' }}">СОП</a></li>
                <li class="nav__item"><a href="{{ route('orders') }}" class="nav__link {{ request()->routeIs('orders') ? 'nav__link--active' : '' }}">Приказы</a></li>
                <li class="nav__item"><a href="{{ route('birthdays') }}" class="nav__link {{ request()->routeIs('birthdays') ? 'nav__link--active' : '' }}">Дни рождения</a></li>
                <li class="nav__item"><a href="{{ route('redmine.appeals') }}" class="nav__link {{ request()->routeIs('appeals') ? 'nav__link--active' : '' }}">Обращения</a></li>
            </ul>
        </nav>

    </div>
</header>

{{-- ===== САЙДБАР УВЕДОМЛЕНИЙ ===== --}}
<div class="notif-sidebar" id="notifSidebar" aria-hidden="true">
    <div class="notif-sidebar__overlay" data-close-sidebar></div>

    <aside class="notif-sidebar__panel" role="dialog" aria-modal="true" aria-labelledby="notifTitle">
        <header class="notif-sidebar__head">
            <h3 class="notif-sidebar__title" id="notifTitle">
                Уведомления
                @if (($unreadCount ?? 0) > 0)
                <span class="notif-sidebar__count" id="notifSidebarCount">{{ $unreadCount }}</span>
                @endif
            </h3>
            <button type="button" class="notif-sidebar__close" data-close-sidebar aria-label="Закрыть">&times;</button>
        </header>

        <div class="notif-sidebar__body" id="notifSidebarBody">
            @forelse ($notifications ?? [] as $note)
            @php
            $isUnread = empty($note->read_at);
            $typeIcon = match ($note->type) {
            'issue_comment' => '💬',
            'issue_status_changed' => '🔄',
            'news_created' => '📰',
            default => '🔔',
            };
            @endphp

            <a href="{{ $note->url ?: '#' }}"
                class="notif-item js-notif-link {{ $isUnread ? 'notif-item--unread' : '' }}"
                data-id="{{ $note->id }}">
                <div class="notif-item__title">
                    <span class="notif-item__icon">{{ $typeIcon }}</span>
                    {{ $note->title }}
                </div>
                @if ($note->body)
                <div class="notif-item__text">{{ $note->body }}</div>
                @endif
                <div class="notif-item__date">
                    {{ \Illuminate\Support\Carbon::parse($note->created_at)->diffForHumans() }}
                </div>
            </a>
            @empty
            <div class="notif-empty" id="notifEmpty">Уведомлений пока нет</div>
            @endforelse
        </div>

        <footer class="notif-sidebar__foot">
            <button type="button" class="notif-sidebar__all" id="markAllReadBtn">
                Прочитать все
            </button>
        </footer>
    </aside>
</div>

{{-- ===== МОДАЛЬНОЕ ОКНО ТЕЛЕФОНОВ ===== --}}
<div class="phone-modal" id="phoneModal" aria-hidden="true">
    <div class="phone-modal__overlay" data-close-modal></div>
    <div class="phone-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="phoneModalTitle">
        <button type="button" class="phone-modal__close" data-close-modal aria-label="Закрыть">&times;</button>

        <h3 class="phone-modal__title" id="phoneModalTitle">Изменить номера телефонов</h3>

        <form method="POST" action="{{ route('profile.phone.update') }}" class="phone-modal__form" id="phoneModalForm">
            @csrf

            @if ($errors->any())
            <div class="phone-modal__errors">
                @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <div class="phone-modal__field">
                <label for="modalCityInput">Городской номер</label>
                <input type="text" name="city" id="modalCityInput"
                    value="{{ old('city', $profile['city_phone'] ?: '') }}"
                    placeholder="например, 4955322333" inputmode="numeric" autocomplete="off">
                <small class="phone-modal__hint">10 цифр, без пробелов и дефисов. Оставьте пустым, чтобы удалить.</small>
            </div>

            <div class="phone-modal__field">
                <label for="modalMobileInput">Внутренний номер</label>
                <input type="text" name="mobile" id="modalMobileInput"
                    value="{{ old('mobile', $profile['mobile_phone'] ?: '') }}"
                    placeholder="от 4 до 6 цифр" inputmode="numeric" autocomplete="off">
                <small class="phone-modal__hint">от 4 до 6 цифр. Оставьте пустым, чтобы удалить.</small>
            </div>

            <div class="phone-modal__actions">
                <button type="button" class="btn btn-secondary" data-close-modal>Отмена</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* ================= Попап телефонов ================= */
        const modal = document.getElementById('phoneModal');
        const openBtn = document.getElementById('openPhoneModal');
        const form = document.getElementById('phoneModalForm');
        const cityInput = document.getElementById('modalCityInput');
        const mobileInput = document.getElementById('modalMobileInput');

        function openModal() {
            modal.classList.add('phone-modal--open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            cityInput.focus();
        }

        function closeModal() {
            modal.classList.remove('phone-modal--open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        modal.querySelectorAll('[data-close-modal]').forEach(el => el.addEventListener('click', closeModal));

        form.addEventListener('submit', function(e) {
            const city = cityInput.value.trim();
            const mobile = mobileInput.value.trim();

            if (city !== '' && !/^\d{10}$/.test(city)) {
                e.preventDefault();
                alert('Городской номер должен содержать ровно 10 цифр (например, 4955322333).');
                cityInput.focus();
                return;
            }
            if (mobile !== '' && !/^\d{4,6}$/.test(mobile)) {
                e.preventDefault();
                alert('Внутренний номер должен содержать от 4 до 6 цифр.');
                mobileInput.focus();
                return;
            }
        });

        @if($errors->any())
        openModal();
        @endif

        /* ================= Сайдбар уведомлений ================= */
        const sidebar = document.getElementById('notifSidebar');
        const bellBtn = document.getElementById('openNotifications');

        function openSidebar() {
            sidebar.classList.add('notif-sidebar--open');
            sidebar.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('notif-sidebar--open');
            sidebar.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (bellBtn) bellBtn.addEventListener('click', openSidebar);
        sidebar.querySelectorAll('[data-close-sidebar]').forEach(el => el.addEventListener('click', closeSidebar));

        /* ================= Esc ================= */
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            if (sidebar.classList.contains('notif-sidebar--open')) {
                closeSidebar();
                return;
            }
            if (modal.classList.contains('phone-modal--open')) {
                closeModal();
            }
        });

        /* ================= Пометить прочитанным при клике ================= */
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        document.querySelectorAll('.js-notif-link').forEach(link => {
            link.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!id || !csrf) return;

                // не await — уходим по ссылке, запрос уйдёт фоном
                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    keepalive: true,
                }).catch(() => {});
            });
        });

        /* ================= Прочитать все ================= */
        const markAllBtn = document.getElementById('markAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function() {
                fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).then(() => {
                    document.querySelectorAll('.notif-item--unread')
                        .forEach(el => el.classList.remove('notif-item--unread'));
                    const badge = document.getElementById('notif-badge');
                    const count = document.getElementById('notifSidebarCount');
                    if (badge) {
                        badge.textContent = '0';
                        badge.classList.add('d-none');
                    }
                    if (count) {
                        count.textContent = '0';
                    }
                });
            });
        }

        /* ================= Polling счётчика ================= */
        function refreshUnread() {
            if (!csrf) return;
            fetch('/notifications/unread-count', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                })
                .then(r => r.json())
                .then(({
                    count
                }) => {
                    const badge = document.getElementById('notif-badge');
                    const sideCount = document.getElementById('notifSidebarCount');
                    if (badge) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.classList.toggle('d-none', count === 0);
                    }
                    if (sideCount) {
                        sideCount.textContent = count;
                        sideCount.style.display = count > 0 ? '' : 'none';
                    }
                })
                .catch(() => {});
        }
        setInterval(refreshUnread, 30000);
    });
</script>

<style>
    /* ===== Шапка ===== */
    .header__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .header__top .col {
        margin-left: auto;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .header__actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* ===== Колокольчик ===== */
    .bell-btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 50%;
        background: var(--color-component);
        color: #fff;
        cursor: pointer;
        transition: background 0.2s;
    }

    .bell-btn:hover {
        background: var(--color-primary-light);
    }

    .bell-btn__badge {
        position: absolute;
        top: -2px;
        right: -2px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 9px;
        background: #ef4444;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        line-height: 18px;
        text-align: center;
        box-shadow: 0 0 0 2px #fff;
    }

    .bell-btn__badge.d-none {
        display: none;
    }

    /* ===== Профиль / тултип ===== */
    .user-menu {
        position: relative;
        display: inline-block;
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
        right: 0;
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

    /* ===== Блок кнопки в шапке ===== */
    .user-phone-block {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        padding: 8px 0 0;
    }

    .phone-edit-open {
        margin-top: 4px;
        padding: 4px 10px;
        font-size: 12px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        background: #fff;
        color: var(--color-primary-light);
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;
    }

    .phone-edit-open:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    /* ===== Сайдбар уведомлений ===== */
    .notif-sidebar {
        position: fixed;
        inset: 0;
        z-index: 1100;
        display: none;
    }

    .notif-sidebar.notif-sidebar--open {
        display: block;
    }

    .notif-sidebar__overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(2px);
        animation: notifFade 0.2s ease-out;
    }

    @keyframes notifFade {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .notif-sidebar__panel {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: 380px;
        max-width: 92vw;
        background: #fff;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        animation: notifSlideIn 0.25s ease-out forwards;
    }

    @keyframes notifSlideIn {
        from {
            transform: translateX(100%);
        }

        to {
            transform: translateX(0);
        }
    }

    .notif-sidebar__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .notif-sidebar__title {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: var(--color-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notif-sidebar__count {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        background: #ef4444;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }

    .notif-sidebar__close {
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        font-size: 24px;
        line-height: 1;
        color: #666;
        cursor: pointer;
        border-radius: 4px;
        transition: background 0.2s, color 0.2s;
    }

    .notif-sidebar__close:hover {
        background: #f3f4f6;
        color: #111;
    }

    .notif-sidebar__body {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
    }

    .notif-item {
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        color: #222;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
    }

    .notif-item:hover {
        background: #f9fafb;
    }

    .notif-item--unread {
        background: #eff6ff;
        border-left: 3px solid var(--color-component);
    }

    .notif-item--unread:hover {
        background: #dbeafe;
    }

    .notif-item__title {
        font-size: 14px;
        font-weight: 600;
        color: #111;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .notif-item__icon {
        font-size: 16px;
    }

    .notif-item__text {
        font-size: 13px;
        color: #555;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    .notif-item__date {
        font-size: 11px;
        color: #9ca3af;
    }

    .notif-empty {
        padding: 40px 20px;
        text-align: center;
        color: #9ca3af;
        font-size: 14px;
    }

    .notif-sidebar__foot {
        padding: 12px 20px;
        border-top: 1px solid #e5e7eb;
    }

    .notif-sidebar__all {
        display: block;
        width: 100%;
        text-align: center;
        font-size: 14px;
        color: var(--color-component);
        background: transparent;
        border: none;
        cursor: pointer;
        font-weight: 500;
        padding: 6px 0;
    }

    .notif-sidebar__all:hover {
        text-decoration: underline;
    }

    /* ===== Модалка телефонов ===== */
    .phone-modal {
        position: fixed;
        inset: 0;
        display: none;
        z-index: 1000;
    }

    .phone-modal.phone-modal--open {
        display: block;
    }

    .phone-modal__overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(2px);
    }

    .phone-modal__dialog {
        position: relative;
        max-width: 460px;
        width: calc(100% - 32px);
        margin: 8vh auto 0;
        padding: 24px 24px 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        animation: phoneModalIn 0.18s ease-out;
    }

    @keyframes phoneModalIn {
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .phone-modal__close {
        position: absolute;
        top: 8px;
        right: 12px;
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        font-size: 24px;
        line-height: 1;
        color: #666;
        cursor: pointer;
        border-radius: 4px;
        transition: background 0.2s, color 0.2s;
    }

    .phone-modal__close:hover {
        background: #f3f4f6;
        color: #111;
    }

    .phone-modal__title {
        margin: 0 0 16px;
        font-size: 18px;
        font-weight: 600;
        color: var(--color-primary);
    }

    .phone-modal__form {
        margin: 0;
    }

    .phone-modal__errors {
        margin-bottom: 14px;
        padding: 10px 12px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
        border-radius: 6px;
        font-size: 13px;
        line-height: 1.4;
    }

    .phone-modal__field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 14px;
    }

    .phone-modal__field label {
        font-size: 13px;
        color: #444;
        font-weight: 500;
    }

    .phone-modal__field input {
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        color: #111;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .phone-modal__field input:focus {
        outline: none;
        border-color: var(--color-component, #4a90d9);
        box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.15);
    }

    .phone-modal__hint {
        font-size: 12px;
        color: #888;
    }

    .phone-modal__actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 18px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: background 0.2s, border-color 0.2s, color 0.2s;
    }

    .btn-primary {
        background: var(--color-component, #4a90d9);
        color: #fff;
    }

    .btn-primary:hover {
        filter: brightness(0.95);
    }

    .btn-secondary {
        background: #fff;
        color: #333;
        border-color: #d1d5db;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
    }
</style>