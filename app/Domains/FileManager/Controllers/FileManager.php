<?php

namespace App\Domains\FileManager\Controllers;

use UniSharp\LaravelFilemanager\Controllers\LfmController;

class FileManager extends LfmController
{
    /**
     * Show the filemanager embedded.
     *
     * @return mixed
     */
    public function embedded()
    {
        return view('laravel-filemanager::embedded')
            ->withHelper($this->helper);
    }
}
