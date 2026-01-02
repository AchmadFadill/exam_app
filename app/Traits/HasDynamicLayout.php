<?php

namespace App\Traits;

trait HasDynamicLayout
{
    public function getLayout()
    {
        if (request()->is('admin/*')) {
            return 'layouts.admin';
        }
        
        return 'layouts.teacher';
    }

    public function applyLayout($view, $data = [])
    {
        return view($view, $data)
            ->extends($this->getLayout())
            ->section('content');
    }
}
