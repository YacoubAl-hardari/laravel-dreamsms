<?php 

namespace DreamSms\LaravelDreamSms\Facades;

use Illuminate\Support\Facades\Facade;

class DreamSms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dreamsms';
    }
}