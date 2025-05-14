<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group: {{ $name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen p-6">

    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Group Name Header -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-blue-700 mb-2">📢 {{ $name }}</h1>
            <p class="text-gray-600 text-sm">{{ count($messages) }} messages in this group</p>
        </div>

        <form method="GET" class="flex flex-wrap gap-4 justify-center items-center mb-6">
            <input type="date" name="start_date" value="{{ $start ?? '' }}"
                class="px-4 py-2 border border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:outline-none">
            <input type="date" name="end_date" value="{{ $end ?? '' }}"
                class="px-4 py-2 border border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:outline-none">
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">Filter by Date</button>
        </form>

        <!-- Table Section -->
        <div>
            <input type="text" id="searchInput" onkeyup="filterTable()"
                   placeholder="🔍 Search messages..."
                   class="w-full max-w-md mx-auto block px-4 py-2 border border-gray-300 rounded mb-6 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            @if ($start && $end)
                <p class="text-sm text-gray-500">Showing messages from <strong>{{ $start }}</strong> to <strong>{{ $end }}</strong></p>
            @endif
            <div class="overflow-x-auto p-4">
                <table id="chatTable" data-sort-dir="asc" class="min-w-[1000px] text-sm text-left border border-gray-200">
                    <thead class="bg-gray-50 sticky top-0 text-gray-700 uppercase text-xs">
                        <tr>
                            <th onclick="sortTable(0)" class="cursor-pointer px-4 py-3 w-[200px] hover:bg-gray-100">Date ⬍</th>
                            <th onclick="sortTable(1)" class="cursor-pointer px-4 py-3 hover:bg-gray-100">Sender ⬍</th>
                            <th onclick="sortTable(2)" class="cursor-pointer px-4 py-3 w-[600px] hover:bg-gray-100">Message ⬍</th>
                            <th class="px-4 py-3">Image</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($messages as $message)
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-4 py-3">{{ $message->timestamp }}</td>
                                <td class="px-4 py-3">{{ $message->sender }}</td>
                                <td class="px-4 py-3 break-words max-w-[500px]">{{ $message->message }}</td>
                                <td class="px-4 py-3">
                                    @if(!empty($message->media_path))
                                        <a href="{{ asset('storage/' . $message->media_path) }}" target="_blank"
                                           class="text-blue-500 hover:underline">View</a>
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

    <!-- JavaScript for sorting and filtering -->
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

</body>
</html>