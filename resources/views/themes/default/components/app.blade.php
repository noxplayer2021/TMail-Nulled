<main x-data="{ id: 0 }" class="lg:flex">
    @if($error)
    <div id="imap-error" class="flex items-center w-full h-full fixed top-0 left-0 bg-red-900 opacity-75 z-50">
        <div class="flex flex-col mx-auto">
            <div class="w-36 mx-auto text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="text-3xl text-center text-white my-5">{{ __('IMAP Broken') }}</div>
            <div class="p-2 mx-auto bg-red-800 text-white leading-none lg:rounded-full flex lg:inline-flex" role="alert">
                <span class="flex rounded-full bg-red-500 uppercase px-2 py-1 text-xs font-bold mr-3">{{ __('Error') }}</span>
                <span class="font-semibold mr-2 text-left flex-auto">{{ $error }}</span>
            </div>
        </div>
    </div>
    @endif
    <div class="w-full lg:w-1/3 bg-white lg:min-h-tm-default">
        @if($messages)
        <div class="messages flex flex-col-reverse divide-y divide-y-reverse divide-gray-200">
            @foreach($messages as $i => $message)
            @if($i % 2 == 0 && config('app.settings.ads.four'))
            <div class="flex justify-center items-center max-w-full p-4">{!! config('app.settings.ads.four') !!}</div>
            @endif
            <div x-on:click="id = {{ $message['id'] }}; document.querySelector('.message-content').scrollIntoView({behavior: 'smooth'});" class="w-full p-5 cursor-pointer hover:bg-gray-50" data-id="{{ $message['id'] }}">
                <div class="flex justify-between">
                    <div>
                        <div class="text-gray-800">{{ $message['sender_name'] }}</div>
                        <div class="text-xs text-gray-400">{{ $message['sender_email'] }}</div>
                    </div>
                    <div>
                        <div class="text-gray-800 text-sm">{{ $message['datediff'] }}</div>
                    </div>
                </div>
                <div class="text-gray-600 mt-5 text-sm truncate">{{ $message['subject'] }}</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex justify-center items-center h-40 lg:min-h-tm-default text-gray-400 text-2xl">
            {{ $initial ? __('Empty Inbox') : __('Fetching') . '...' }}
        </div>
        @endif
    </div>
    <div class="message-content w-full lg:w-2/3 bg-white min-h-screen lg:min-h-tm-default border-1 border-l border-gray-200">
        <div x-show="id === 0" class="hidden lg:flex lg:min-h-tm-default">
            <div class="w-2/3 m-auto">
                <img class="m-auto max-w-full" src="{{ asset('images/sample.jpg') }}" alt="mails">
                <a class="block text-center text-xs text-gray-400 pt-4" href="https://www.freepik.com" target="_blank" rel="noopener noreferrer">{{ __('Above Graphic by') }} <strong>{{ __('Freepik') }}</strong></a>
            </div>
        </div>
        @foreach($messages as $message)
        <div x-show="id === {{ $message['id'] }}" id="message-{{ $message['id'] }}" class="lg:flex flex-col lg:min-h-tm-default">
            <textarea class="hidden">To: {{ $this->email }}
From: "{{ $message['sender_name'] }}" <{{ $message['sender_email'] }}>
Subject: {{ $message['subject'] }}
Date: {{ $message['date'] }}
Content-Type: text/html

{{ $message['content'] }}
            </textarea>
            @if(config('app.settings.ads.two'))
            <div class="flex justify-center items-center max-w-full px-4 pt-4">{!! config('app.settings.ads.two') !!}</div>
            @endif
            <div class="flex flex-col flex-none py-5 px-6">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-gray-900 text-lg">{{ $message['subject'] }}</div>
                        <div class="text-xs text-gray-400">{{ $message['sender_name'] }} - {{ $message['sender_email'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">{{ $message['date'] }}</div>
                    </div>
                </div>
                <div class="flex mt-5">
                    <a class="download text-xs font-semibold bg-blue-700 py-1 px-3 rounded-md text-white" href="#" data-id="{{ $message['id'] }}">{{ __('Download') }}</a>
                    <span class="mr-2"></span>
                    <button x-on:click="id = 0" wire:click="delete({{ $message['id'] }})" class="text-xs font-semibold bg-red-700 py-1 px-3 rounded-md text-white">{{ __('Delete') }}</button>
                </div>
            </div>
            <iframe class="w-full flex flex-grow min-h-tm-iframe lg:min-h-0 px-5" srcdoc="{{ $message['content'] }}" frameborder="0"></iframe>
            @if(config('app.settings.ads.three'))
            <div class="flex justify-center items-center max-w-full p-4">{!! config('app.settings.ads.three') !!}</div>
            @endif
            @if(count($message['attachments']) > 0)
            <span class="pt-5 pb-3 px-6 text-xs">{{ __('Attachments') }}</span>
            <div class="flex pb-5 px-6">
                @foreach($message['attachments'] as $attachment)
                <a class="py-2 px-3 mr-3 text-sm border-2 border-black rounded-md hover:bg-black hover:text-white" href="{{ $attachment['url'] }}" download><i class="fas fa-chevron-circle-down"></i><span class="ml-2">{{ $attachment['file'] }}</span></a>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>