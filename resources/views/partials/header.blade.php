{{-- resources/views/partials/header.blade.php --}}

<header class="header">
    <div class="container">
        
        {{-- ===== ВЕРХНЯЯ ЧАСТЬ: Логотип ===== --}}
        <div class="header__top">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.svg') }}" 
                     alt="Корпоративный портал" 
                     class="logo__image"
                     width="40" 
                     height="40">
                <span class="logo__text">Корпоративный портал</span>
            </a>
        </div>
        
        {{-- ===== НАВИГАЦИЯ (горизонтальное меню) ===== --}}
        <nav class="nav">
            <ul class="nav__list">
                <li class="nav__item">
<<<<<<< HEAD
                    <a href="{{route('home')}}" class="nav__link nav__link--active">Новости</a>
                </li>
                <li class="nav__item">
                    <a href="{{route('directory')}}" class="nav__link">Телефонный справочник</a>
                </li>
                <li class="nav__item">
                    <a href="{{route('info')}}" class="nav__link">Информации</a>
                </li>
                <li class="nav__item">
                    <a href="{{route('sop')}}" class="nav__link">СОП</a>
                </li>
                <li class="nav__item">
                    <a href="{{route('orders')}}" class="nav__link">Приказы</a>
                </li>
                <li class="nav__item">
                    <a href="{{route('birthdays')}}" class="nav__link">Дни рождения</a>
                </li>
                <li class="nav__item">
                    <a href="{{route('appeals')}}" class="nav__link">Заявки</a>
=======
                    <a href="/news" class="nav__link nav__link--active">Новости</a>
                </li>
                <li class="nav__item">
                    <a href="/phonebook" class="nav__link">Телефонный справочник</a>
                </li>
                <li class="nav__item">
                    <a href="/schedules" class="nav__link">Графики дежурств</a>
                </li>
                <li class="nav__item">
                    <a href="/info" class="nav__link">Информации</a>
                </li>
                <li class="nav__item">
                    <a href="/sop" class="nav__link">СОП</a>
                </li>
                <li class="nav__item">
                    <a href="/orders" class="nav__link">Приказы</a>
                </li>
                <li class="nav__item">
                    <a href="/birthdays" class="nav__link">Дни рождения</a>
                </li>
                <li class="nav__item">
                    <a href="/requests" class="nav__link">Заявки</a>
                </li>
                <li class="nav__item">
                    <a href="/incidents" class="nav__link">Регистрация инцидентов</a>
>>>>>>> 527adbd3def5b5a05c6b88b3685f79fb6cd11d58
                </li>
            </ul>
        </nav>
        
    </div>
</header>