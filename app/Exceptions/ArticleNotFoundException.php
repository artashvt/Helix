<?php
namespace App\Exceptions;

class ArticleNotFoundException extends \Exception
{
    protected $message = 'Article Not found';
}