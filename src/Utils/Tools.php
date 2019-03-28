<?php
// src/Utils/Tools.php

namespace App\Utils;

class Tools
{
   // Generates and returns a token.
    public function generateToken()
    {
        return random_int(1000000000, 9999999999);
    }
}