<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class FacilitatorReport extends Model
{
    protected $table = 'facilitator_report';

    protected $fillable = [
        'batch',
        'year',
        'facilitator_id',
        'menjelaskan_tujuan',
        'membangun_hubungan',
        'mengajak_berdiskusi',
        'memimpin_proses_diskusi',
        'mampu_menjawab_pertanyaan',
        'kedalaman_materi',
        'penampilan'
    ];
}
