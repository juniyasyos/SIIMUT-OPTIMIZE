<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $laporan = $this->getLaporan();

            $statusColors = [
                'process' => 'bg-blue-100 text-blue-800 ring-blue-300',
                'complete' => 'bg-green-100 text-green-800 ring-green-300',
                'coming_soon' => 'bg-gray text-white',
            ];
        @endphp

        @if (!$laporan)
            <div class="py-8 text-center text-gray-500">
                <p class="text-lg font-semibold">Belum ada laporan tersedia</p>
                <p class="mt-1 text-sm">Silakan buat laporan terlebih dahulu.</p>
            </div>
        @else
            <div class="flex flex-col items-start justify-between gap-4 p-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $laporan->name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Periode: <span class="font-medium">
                            {{ $laporan->assessment_period_start->translatedFormat('d M Y') }}
                            – {{ $laporan->assessment_period_end->translatedFormat('d M Y') }}
                        </span>
                    </p>
                </div>

                <span
                    class="inline-flex items-center gap-1 text-sm font-medium px-4 py-1.5 rounded-full ring-1
                    {{ $statusColors[$laporan->status] ?? 'bg-gray-100 text-gray-800 ring-gray-300' }}">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>

                    Status: {{ ucfirst($laporan->status) }}
                </span>

            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
