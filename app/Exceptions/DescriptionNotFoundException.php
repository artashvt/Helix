<?php
namespace App\Exceptions;

class DescriptionNotFoundException extends \Exception
{
    protected $message = 'Description Not found';
}