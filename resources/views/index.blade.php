<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WhatsApp Chat Upload</title>
    <script>
        function sortTable(colIndex) {
            const table = document.getElementById("chatTable");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.rows);
            const dir = table.getAttribute("data-sort-dir") === "asc" ? "desc" : "asc";
            table.setAttribute("data-sort-dir", dir);

            rows.sort((a, b) => {
                const valA = a.cells[colIndex].innerText.toLowerCase();
                const valB = b.cells[colIndex].innerText.toLowerCase();
                return (valA < valB ? -1 : valA > valB ? 1 : 0) * (dir === "asc" ? 1 : -1);
            });

            tbody.innerHTML = "";
            rows.forEach(row => tbody.appendChild(row));
        }

        function filterTable() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("#chatTable tbody tr");
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
            });
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 p-6">

    <div class="max-w-6xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold text-gray-700">Upload WhatsApp Chat (.zip)</h2>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-md">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded-md">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-md">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Upload Form --}}
        <form action="{{ route('chat.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-medium mb-1">Select ZIP File</label>
                <input type="file" name="chat_zip" accept=".zip" required class="border border-gray-300 p-2 rounded-md w-full">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload</button>
        </form>

        <div class="mt-10">
            <h3 class="text-xl font-semibold mb-4">Messages (Search & Sort)</h3>
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search messages..." class="w-full max-w-sm p-2 border border-gray-300 rounded mb-4">

            <div class="overflow-x-auto">
                <table id="chatTable" data-sort-dir="asc" class="min-w-[1000px] text-sm text-left">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th onclick="sortTable(0)" class="cursor-pointer px-4 py-3 w-[250px]">Group Name ⬍</th>
                            <th onclick="sortTable(1)" class="cursor-pointer px-4 py-3">Date ⬍</th>
                            <th onclick="sortTable(2)" class="cursor-pointer px-4 py-3">Sender ⬍</th>
                            <th onclick="sortTable(3)" class="cursor-pointer px-4 py-3 w-[500px]">Message ⬍</th>
                            <th class="px-4 py-3">Image</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($messages as $message)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $message->group_name }}</td>
                                <td class="px-4 py-3">{{ $message->timestamp }}</td>
                                <td class="px-4 py-3">{{ $message->sender }}</td>
                                <td id="message" class="px-4 py-3 break-words max-w-[300px]">{{ $message->message }}</td>
                                <td class="px-4 py-3">
                                    @if(!empty($message->media_path))
                                        <a href="{{ asset('storage/' . $message->media_path) }}" target="_blank" class="text-blue-500 underline">View</a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
