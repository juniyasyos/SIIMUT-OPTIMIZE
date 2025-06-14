<?php

namespace App\Filament\Forms;

use App\Filament\Resources\ImutDataResource;
use App\Models\RegionType;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ImutProfileForm
{
    /**
     * Summary of make
     *
     * @return Forms\Components\Tabs[]
     */
    public static function make(): array
    {
        return [
            \Filament\Forms\Components\Hidden::make('imut_data_id'),
            Tabs::make('Form Profil Indikator')
                ->tabs([
                    Tab::make('ℹ️ Informasi Dasar')
                        ->schema(self::basicInformationSchema()),

                    Tab::make('🧮 Perhitungan')
                        ->schema(self::operationalDefinitionSchema()),

                    Tab::make('📋 Data Resource')
                        ->schema(self::dataResourceSchema()),

                    Tab::make('🎯 Analisis')
                        ->schema(self::AnalysisSchema()),

                    // Tab::make('📍 Benchmarking')
                    //     ->schema(self::benchmarkingSchema())
                    //     ->visible(fn (?Model $record) => $record !== null
                    //         && request()->is('imut-datas/*/profile/edit=*')
                    //         && $record->imutData->categories->is_benchmark_category === 1),
                ])
                ->columnSpan(['lg' => 2]),
        ];
    }

    protected static function basicInformationSchema(): array
    {
        return [
            Section::make('Informasi Dasar')
                ->description('Isi data umum indikator mutu profil.')
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('version')
                            ->label('Versi')
                            ->helperText('Contoh: v1, v2.1')
                            ->required()
                            ->reactive()
                            ->maxLength(50),

                        TextInput::make('slug')
                            ->label(__('filament-forms::imut-data.fields.slug'))
                            ->readOnly()
                            ->extraAttributes(['class' => 'bg-gray-100 text-gray-500'])
                            ->columnSpan(1),

                        TextInput::make('responsible_person')
                            ->label('Penanggung Jawab')
                            ->placeholder('Peran yang benanggung jawab')
                            ->required()
                            ->maxLength(255),

                        ToggleButtons::make('indicator_type')
                            ->label('Tipe Indikator')
                            ->options([
                                'process' => 'Proses',
                                'output' => 'Output',
                                'outcome' => 'Outcome',
                            ])
                            ->default('process')
                            ->icons([
                                'process' => 'heroicon-o-cog',
                                'output' => 'heroicon-o-chart-bar',
                                'outcome' => 'heroicon-o-academic-cap',
                            ])
                            ->colors([
                                'process' => 'warning',
                                'output' => 'info',
                                'outcome' => 'success',
                            ])
                            ->inline()
                            ->required()
                            ->columnSpan(2)
                            ->helperText('Pilih jenis indikator yang sesuai.'),
                    ]),
                ]),

            Section::make('Deskripsi Indikator')
                ->description('Uraikan latar belakang, tujuan, dan makna indikator.')
                ->schema([
                    RichEditor::make('rationale')
                        ->label('Rasional')
                        ->hint('Mengapa indikator ini penting untuk diukur.'),

                    RichEditor::make('objective')
                        ->label('Tujuan')
                        ->hint('Apa yang ingin dicapai melalui indikator ini.'),

                    RichEditor::make('operational_definition')
                        ->label('Definisi Operasional')
                        ->hint('Penjelasan rinci istilah dalam indikator.'),

                    TextInput::make('quality_dimension')
                        ->label('Dimensi Mutu')
                        ->hint('Contoh: Efektivitas, Efisiensi, Aksesibilitas.'),
                ]),
        ];
    }

    protected static function operationalDefinitionSchema(): array
    {
        return [
            Section::make('💡 Perhitungan Indikator')
                ->description('Masukkan rumus dan kriteria yang digunakan untuk menghitung indikator mutu.')
                ->schema([

                    Fieldset::make('🧮 Rumus Perhitungan')
                        ->columns(1)
                        ->schema([
                            Textarea::make('numerator_formula')
                                ->label('Rumus Pembilang')
                                ->rows(3)
                                ->required()
                                ->placeholder('Contoh: Jumlah pasien yang menerima layanan X...')
                                ->helperText('Rumus untuk bagian atas (numerator) dari indikator.'),

                            Textarea::make('denominator_formula')
                                ->label('Rumus Penyebut')
                                ->rows(3)
                                ->required()
                                ->placeholder('Contoh: Jumlah total pasien yang memenuhi syarat...')
                                ->helperText('Rumus untuk bagian bawah (denominator) dari indikator.'),
                        ]),

                    Fieldset::make('📋 Kriteria Data')
                        ->columns(2)
                        ->schema([
                            TextInput::make('inclusion_criteria')
                                ->label('Kriteria Inklusi')
                                ->required()
                                ->placeholder('Contoh: Pasien usia ≥ 18 tahun...')
                                ->helperText('Data yang harus disertakan.'),

                            TextInput::make('exclusion_criteria')
                                ->label('Kriteria Eksklusi')
                                ->required()
                                ->placeholder('Contoh: Pasien tanpa rekam medis lengkap...')
                                ->helperText('Data yang harus dikecualikan dari penghitungan.'),
                        ]),
                ]),
        ];
    }

    protected static function dataResourceSchema(): array
    {
        return [
            Section::make('📥 Pengumpulan')
                ->description('Detail proses pengumpulan data, dan metode')
                ->schema([

                    // === Fieldset: Pengumpulan Data ===
                    Fieldset::make('📋 Informasi Pengumpulan')
                        ->columns(2)
                        ->schema([
                            TextInput::make('data_source')
                                ->label('Sumber Data')
                                ->placeholder('Contoh: EMR, Audit Form, Survey')
                                ->helperText('Sumber utama data indikator ini berasal dari mana.')
                                ->prefixIcon('heroicon-o-server'),

                            TextInput::make('data_collection_frequency')
                                ->label('Frekuensi Pengumpulan')
                                ->placeholder('Contoh: Bulanan, Mingguan')
                                ->helperText('Berapa sering data dikumpulkan.')
                                ->prefixIcon('heroicon-o-calendar-days'),

                            TextInput::make('data_collection_method')
                                ->label('Metode Pengumpulan')
                                ->placeholder('Contoh: Elektronik, Manual, Observasi')
                                ->helperText('Bagaimana proses pengumpulan data dilakukan.')
                                ->prefixIcon('heroicon-o-finger-print'),

                            TextInput::make('sampling_method')
                                ->label('Metode Sampling')
                                ->placeholder('Contoh: Total sampling, Random sampling')
                                ->helperText('Metode pemilihan sampel data untuk dianalisis.')
                                ->prefixIcon('heroicon-o-beaker'),
                        ]),
                ]),
        ];
    }

    protected static function AnalysisSchema(): array
    {
        return [
            Section::make('🔍 Analisis Data')
                ->description('Detail perencanaan analisis indikator mutu.')
                ->schema([
                    // === Fieldset: Detail Analisis ===
                    Fieldset::make('📈 Detail Analisis')
                        ->columns(2)
                        ->schema([
                            ToggleButtons::make('target_operator')
                                ->label('Operator Target')
                                ->options([
                                    '>=' => '≥',
                                    '<=' => '≤',
                                    '=' => '=',
                                ])
                                ->default('=')
                                ->inline()
                                ->helperText('Pilih operator pembanding untuk nilai target.'),

                            TextInput::make('target_value')
                                ->label('🎯 Nilai Target')
                                ->numeric()
                                ->placeholder('Contoh: 90, 95, 100')
                                ->helperText('Target pencapaian kinerja indikator.')
                                ->prefixIcon('heroicon-o-arrow-trending-up'),
                        ]),

                    // === Periode Analisis ===
                    Fieldset::make('🗓️ Periode Analisis')
                        ->columns(2)
                        ->schema([

                            Select::make('analysis_period_type')
                                ->label('Tipe Periode Analisis')
                                ->options([
                                    'mingguan' => 'Mingguan',
                                    'bulanan' => 'Bulanan',
                                ])
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                                    self::calculateEndPeriod($set, $get);
                                })
                                ->helperText('Jenis periode yang digunakan dalam analisis.')
                                ->prefixIcon('heroicon-o-clock'),

                            TextInput::make('analysis_period_value')
                                ->label('Nilai Periode')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                                    self::calculateEndPeriod($set, $get);
                                })
                                ->placeholder('Contoh: 1, 3, 6')
                                ->helperText('Angka yang menunjukkan rentang waktu.')
                                ->prefixIcon('heroicon-o-adjustments-horizontal'),

                            DatePicker::make('start_period')
                                ->label('Periode Mulai')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                                    self::calculateEndPeriod($set, $get);
                                }),

                            DatePicker::make('end_period')
                                ->label('Periode Selesai')
                                ->readOnly()
                                ->required(),
                        ]),

                    // === Alat & Rencana Analisis ===
                    Fieldset::make('🛠️ Alat & Strategi Analisis')
                        ->columns(1)
                        ->schema([
                            Textarea::make('data_collection_tool')
                                ->label('Alat Kumpul Data')
                                ->placeholder('Contoh: Kuesioner, Google Form, EMR, Form Audit')
                                ->rows(2)
                                ->helperText('Alat bantu atau instrumen yang digunakan dalam proses pengumpulan.'),

                            Textarea::make('analysis_plan')
                                ->label('Rencana Analisis')
                                ->placeholder('Langkah-langkah bagaimana data akan dianalisis untuk mengevaluasi indikator.')
                                ->rows(3)
                                ->helperText('Ceritakan secara ringkas bagaimana analisis dilakukan.'),
                        ]),
                ]),
        ];
    }

    protected static function calculateEndPeriod(\Filament\Forms\Set $set, \Filament\Forms\Get $get): void
    {
        $start = $get('start_period');
        $type = $get('analysis_period_type');
        $value = (int) $get('analysis_period_value');

        if (! $start || ! $type || ! $value) {
            return;
        }

        try {
            $startDate = \Illuminate\Support\Carbon::parse($start);
            $endDate = match ($type) {
                'mingguan' => $startDate->copy()->addWeeks($value),
                'bulanan' => $startDate->copy()->addMonths($value),
                default => $startDate,
            };

            $set('end_period', $endDate->format('Y-m-d'));
        } catch (\Exception $e) {
            logger()->error('Perhitungan end_period gagal:', [
                'start' => $start,
                'type' => $type,
                'value' => $value,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
