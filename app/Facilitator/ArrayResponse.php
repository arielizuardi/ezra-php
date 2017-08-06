<?php
namespace App\Facilitator;

use App\GoogleArrayResponse;
use Illuminate\Database\Eloquent\Collection;

class ArrayResponse extends GoogleArrayResponse
{
    public function __construct(Collection $collection)
    {
        parent::__construct($collection);
    }

    public function toTable()
    {
        // TODO: Implement toTable() method.
    }

    public function toArray()
    {
        $this->response = [];
        $this->addColumns(
            0,
            'Indikator',
            'Mampu menjelaskan tujuan dan manfaat kelas ini dengan baik',
            'Membangun hubungan baik dengan saya',
            'Mampu mengajak peserta untuk berdiskusi',
            'Mampu membuat proses diskusi berjalan dengan baik',
            'Mampu menjawab pertanyaan concern yang ada selama diskusi kelompok',
            'Memiliki kedalaman materi yang dibutuhkan',
            'Bersikap profesional, berbusana rapi serta berperilaku & bertutur kata sopan'
        );

        foreach ($this->collection as $i => $item)
        {
            $this->addColumns(
                $i + 1,
                sprintf('Batch %s - %s', $item->batch, $item->year),
                floatval($item->menjelaskan_tujuan),
                floatval($item->membangun_hubungan),
                floatval($item->mengajak_berdiskusi),
                floatval($item->memimpin_proses_diskusi),
                floatval($item->mampu_menjawab_pertanyaan),
                floatval($item->kedalaman_materi),
                floatval($item->penampilan)
            );
        }

        return $this->response;
    }
}