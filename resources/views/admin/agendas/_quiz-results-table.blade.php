{{-- Reusable quiz results table partial --}}
{{-- @param Collection $results - quiz results collection --}}
{{-- @param string $colorClass - 'blue' or 'amber' for theming --}}
@php
    $failClass = $colorClass === 'blue' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600';
@endphp
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-primary-700 text-white">
            <tr class="border-b border-gray-100">
                <th class="pr-4 py-3 text-left text-xs font-semibold uppercase tracking-wider w-10">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jabatan</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Benar</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Nilai</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Waktu</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($results as $index => $result)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="pr-4 py-3 text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-gray-800">{{ $result['employee']->full_name }}</div>
                        <div class="text-xs text-gray-400">{{ $result['employee']->unit->name ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $result['employee']->job_position }}</td>
                    <td class="px-4 py-3 text-center text-sm font-medium text-gray-700">{{ $result['correct'] }}/{{ $result['total'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $result['score'] >= 70 ? 'bg-green-50 text-green-600' : $failClass }}">
                            {{ $result['score'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($result['answered_at'])->format('d M Y, H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
