<?php
namespace App\Exceptions;

class ArticlesNotFoundException extends \Exception
{
    protected $message = 'Articles Not found';
}