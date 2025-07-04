<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ImutPenilaian extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ImutPenilaianFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imut_profil_id',
        'laporan_unit_kerja_id',
        'analysis',
        'recommendations',
        'numerator_value',
        'denominator_value',
    ];

    /**
     * The attributes that are guarded.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->useDisk('public');
    }

    /**
     * Boot model events
     */
    protected static function booted(): void
    {
        static::saved(fn(self $penilaian) => $penilaian->clearCache());
        static::deleted(fn(self $penilaian) => $penilaian->clearCache());
    }

    public function clearCache()
    {
        $this->loadMissing(['profile', 'laporanUnitKerja.laporanImut']);

        $laporanUnitKerja = $this->laporanUnitKerja;

        if ($laporanUnitKerja) {
            $laporanId = $laporanUnitKerja->laporan_imut_id;
            $unitKerjaId = $laporanUnitKerja->unit_kerja_id;

            Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));
            Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
            Cache::forget(CacheKey::dashboardSiimutChartData());

            $laporanImut = $laporanUnitKerja->laporanImut;

            if ($laporanImut && $this->profile) {
                $imutDataId = $this->profile->imut_data_id;
                $year = \Carbon\Carbon::parse($laporanImut->assessment_period_start)->year;

                Cache::forget(CacheKey::imutPenilaianImutDataUnitKerja($imutDataId, $year, $unitKerjaId));
                Cache::forget(CacheKey::imutPenilaian($imutDataId, $year));

                $regionTypeIds = RegionType::query()->pluck('id');
                foreach ($regionTypeIds as $regionTypeId) {
                    Cache::forget(CacheKey::imutBenchmarking($year, $regionTypeId));
                }
            }
            Cache::forget(CacheKey::getPenilaianStats($laporanId, false));
            Cache::forget(CacheKey::getPenilaianStats($laporanId, true));
        }

        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
    }


    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Get the profile that owns the ImutPenilaian
     *
     * @return void
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(ImutProfile::class, 'imut_profil_id');
    }

    /**
     * Get the unit kerja that owns the ImutPenilaian
     *
     * @return void
     */
    public function laporanUnitKerja(): BelongsTo
    {
        return $this->belongsTo(LaporanUnitKerja::class);
    }

    public function profileById($profileId): HasOne
    {
        return $this->hasOne(ImutProfile::class)->where('id', $profileId);
    }
}