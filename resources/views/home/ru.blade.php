@extends('layouts.app')

@section('content')
    <div class="row">
        <div id="showcase" class="col">
            <h1 class="mt-5">Купить все игры в стиме</h1>
            @include('layouts.github')
            <div class="mt-5">
                <p>Русский | <a href="{{ url('/en') }}">English</a> | <a href="{{ url('/zh') }}">中文</a></p>
            </div>
            <div class="mt-5">
                <p>Вы когда-нибудь задумывались, сколько стоит купить все игры в Steam?</p>
                <p>Так вот, сейчас это будет стоить примерно <span class="text-danger">{{ $record->sale }} руб.</span> с учетом скидок или <span
                            class="text-danger">{{ $record->original }} руб.</span> за полную стоимость.</p>
                <p>Страница обновлена {{ $record->created_at->diffForHumans() }}, цена актуальна для {{$record->cc}} региона и языка - {{$record->language}}.
                </p>
            </div>
            <h2 class="mt-5">История изменения</h2>
            <chart :records="{{ $records }}"></chart>

            <h2 class="mt-5 pt-5">Как это работает?</h2>
            <p>Вдохновлено статистикой с этой страницы <a
                        href="http://buyallofsteam.appspot.com/" target="_blank">http://buyallofsteam.appspot.com/</a>,
                которая не обновляется с 2014 года.</p>
            <p>Текущая страница работает на PHP и Laravel, и обновляется ежедневно.</p>
            <h2 class="mt-5">API</h2>
            <p>Стучите на <code>{{url('api')}}</code> чтобы получить эту статистику. Формат ответа:</p>
            <pre>
            [
                {
                    "original": 233448.27,
                    "sale": 229259.34,
                    "cc": "US"
                    "language": "English"
                    "created_at": "2017-04-18 14:10:59"
                },
                ...
            ]
            </pre>
        </div>
    </div>
@endsection
