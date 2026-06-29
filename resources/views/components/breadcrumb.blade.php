@props([
    'items' => [], // array of ['label' => '', 'href' => '']
    'class' => '',
])

<nav class="flex items-center {{ $class }}" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        @foreach ($items as $key => $item)
            <li class="flex items-center">
                @if (isset($item['href']))
                    <a href="{{ $item['href'] }}"
                        class="text-sm font-medium text-slate-600 dark:text-slate-400
                            hover:text-slate-900 dark:hover:text-slate-200
                            transition-colors duration-200">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                        {{ $item['label'] }}
                    </span>
                @endif

                @if (!$loop->last)
                    <svg class="w-4 h-4 mx-2 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                @endif
            </li>
        @endforeach

        @if ($slot->isNotEmpty())
            {{ $slot }}
        @endif
    </ol>
</nav>
