<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DateFilterDropdown extends Component
{
    public $action;
    public $startDate;
    public $endDate;
    public $formId; // ID dari form utama di halaman induk

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($action, $startDate, $endDate, $formId = 'filterForm')
    {
        $this->action = $action;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->formId = $formId;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.date-filter-dropdown');
    }
}
