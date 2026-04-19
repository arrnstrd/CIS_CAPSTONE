<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Card extends Component
{
    public string $title;
    public string $value;
    public string $icon;
    public string $textColor;
    public string $bgColor;
    public function __construct(
        string $title = '',
        string $value = '',
        string $icon = '',
        string $variants ='primary'
    )
    {
        $this->title = $title;
        $this->value =$value;
        $this->icon = $icon;
        



        $colors =[
            'primary' => ['text-primary' , 'bg-primary' , ],
            'success' => ['text-success' , 'bg-success'],
            'danger' => ['text-danger' , 'bg-danger'],
            'warning' => ['text-warning' , 'bg-warning']
        ];

        $selected = $colors[$variants] ?? $colors['primary'];

        $this->textColor = $selected[0];
        $this->bgColor = $selected[1];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.card');
    }
}
