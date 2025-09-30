<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Database Viewer</h1>
                <a href="/dashboard" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        @foreach($tableData as $tableName => $data)
            <div class="mb-8 bg-white shadow rounded-lg overflow-hidden">
                <div class="bg-gray-800 text-white px-6 py-4">
                    <h2 class="text-xl font-semibold">{{ $tableName }}</h2>
                    <p class="text-gray-300 text-sm">{{ count($data['data']) }} rows</p>
                </div>
                
                @if(count($data['data']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    @foreach($data['columns'] as $column)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            {{ $column }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($data['data'] as $row)
                                    <tr class="hover:bg-gray-50">
                                        @foreach($data['columns'] as $column)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @php
                                                    $value = $row->$column ?? '';
                                                    if (strlen($value) > 50) {
                                                        echo substr($value, 0, 50) . '...';
                                                    } else {
                                                        echo $value;
                                                    }
                                                @endphp
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-4 text-gray-500 text-center">
                        No data in this table
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <script src="/js/app.js"></script>
</body>
</html>
