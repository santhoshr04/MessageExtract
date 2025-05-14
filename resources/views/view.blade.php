<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp Group Chats</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex items-center justify-center py-10 px-4">

    <div class="w-full max-w-3xl bg-white shadow-sm rounded-2xl p-8">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">
            <i class="fa-solid fa-comments text-blue-600 mr-2"></i> Available Group Chats
        </h2>

        <ul class="divide-y divide-gray-200">
            @forelse ($messages->groupBy('group_name') as $groupName => $groupMessages)
                <li class="py-4 px-3 hover:bg-blue-50 rounded-lg transition-all duration-200 group">
                    <a href="{{ route('group.view', ['name' => urlencode($groupName)]) }}" class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-100 text-blue-600 rounded-full p-2">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-blue-700 group-hover:underline">{{ $groupName }}</h3>
                                <p class="text-sm text-gray-500">{{ count($groupMessages) }} messages</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-400 group-hover:text-blue-500"></i>
                    </a>
                </li>
            @empty
                <li class="text-center text-gray-500 py-6">No group chats available.</li>
            @endforelse
        </ul>
    </div>

</body>
</html>
