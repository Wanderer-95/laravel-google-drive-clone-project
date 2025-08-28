<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Shared Files Notification</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 p-6 font-sans text-gray-800">

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
    <!-- Заголовок -->
    <h1 class="text-2xl font-semibold text-blue-600 mb-4">Hello {{ $user->name }}.</h1>
    <p class="mb-4 text-gray-700">User {{ $author->name }} shared the following files to your:</p>

    <!-- Список файлов -->
    <div class="space-y-3">
        @foreach($files as $file)
            <div class="border border-gray-200 rounded-lg p-3 flex items-center space-x-3 hover:bg-gray-50 transition">
                <!-- Иконка для folder/file -->
                @if($file->is_folder)
                    <svg class="w-6 h-6 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 4a1 1 0 011-1h4l2 3h7a1 1 0 011 1v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" />
                    </svg>
                @else
                    <svg class="w-6 h-6 text-gray-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                    </svg>
                @endif
                <span class="text-gray-800 font-medium">{{ $file->name }}</span>
            </div>
        @endforeach
    </div>

    <!-- Подвал -->
    <div class="mt-6 text-center text-sm text-gray-500">
        If you have any questions, feel free to contact us.
    </div>
</div>

</body>
</html>
