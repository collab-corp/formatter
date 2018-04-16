<?php

namespace CollabCorp\Formatter;

use Illuminate\Support\ServiceProvider;

class FormatterServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('collab-corp.formatter', \CollabCorp\Formatter\Formatter::class);
    }
}
