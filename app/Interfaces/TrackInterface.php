<?php


namespace App\Interfaces;

interface TrackInterface
{
    public function createMix():void;

    public function authorName():?string;

    public function url():?string;
}
