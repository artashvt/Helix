<?php
namespace App\Exceptions;

class ArticleHrefNotFoundException extends \Exception
{
    protected $message = 'Article Link Not found';
}