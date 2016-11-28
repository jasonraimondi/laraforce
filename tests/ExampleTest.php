<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RandomLib\Mixer\McryptRijndael128;

class ExampleTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        error_reporting(E_ALL ^ E_DEPRECATED);
        $mcrypt = new McryptRijndael128();
    }
}
