@extends('_layouts.main')


@section('body')
    <div class="max-w-2xl mx-auto">
        @include('_components.profile')
        <div class="text-center mt-12">
            <p class="text-gray-400 text-sm my-2">{{ date('F j, Y', $page->date) }}</p>
            <h1 class="leading-none mb-8 text-4xl font-semibold">{{ $page->title }}</h1>
        </div>

        <div class="mb-10 py-4 text-justify" v-pre>
            @include('_components.article')
        </div>
        <div class="border-b border-b-gray-200 mb-8"></div>

        <p class="text-lg text-center text-gray-700">{{ $page->siteAuthor }}</p>
        @include('_components.about')
    </div>
@endsection
