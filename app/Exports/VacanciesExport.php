<?php

namespace App\Exports;

use App\Models\Vacancy;
use Maatwebsite\Excel\Concerns\FromCollection;

class VacanciesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Vacancy::all();
    }
}
