<?php

namespace Tablda\DataReceiver;


use App;
use Illuminate\Support\ServiceProvider;

class TabldaDataServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton(TabldaDataInterface::class, TabldaDataReceiver::class);
    }
}