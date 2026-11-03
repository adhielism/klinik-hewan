<?php

function statusUsiaHewan(int $usia): string
{
    return $usia >= 3 ? 'Dewasa' : 'Muda';
}