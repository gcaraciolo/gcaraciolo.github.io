@extends('_layouts.main')

@section('body')
    <div class="max-w-2xl mx-auto">
        <div class="flex flex-col items-center justify-center mb-12">
            @include('_components.profile')
            @include('_components.about')
        </div>
        @foreach ($posts as $post)
            <a href="{{ $post->getUrl() }}" title="Read {{ $post->title }}">
                <article class="text-center w-full mx-auto mb-6 cursor-pointer border border-gray-200 rounded-lg px-8 py-6 shadow-md">
                    <p class="text-gray-400 text-sm my-2">
                        {{ $post->getDate()->format('F j, Y') }}
                    </p>
                    <h3 class="text-3xl mb-6 text-gray-700 font-bold">
                        {{ $post->title }}
                    </h3>
                    <p class="mt-0 text-gray-500 mb-4 text-justify">{!! $post->getExcerpt() !!}</p>
                    <span class="text-gray-500 text-xs font-semibold text-right">
                        Ler mais → 
                    </span>
                </article>
            </a>
        @endforeach
    </div>
@stop
