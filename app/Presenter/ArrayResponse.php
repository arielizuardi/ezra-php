<?php
namespace App\Presenter;

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
            'Penguasaan Materi',
            'Sistematika Penyajian',
            'Metode Penyajian',
            'Pengaturan Waktu',
            'Penggunaan Alat Bantu'
        );

        foreach ($this->collection as $i => $item)
        {
            $this->addColumns(
                $i + 1,
                sprintf('Batch %s - %s', $item->batch, $item->year),
                floatval($item->penguasaan_materi),
                floatval($item->sistematika_penyajian),
                floatval($item->metode_penyajian),
                floatval($item->pengaturan_waktu),
                floatval($item->alat_bantu)
            );
        }

        return $this->response;
    }
}