<?php

namespace App;

class Redirect {

    static function to($uri)
    {
        header('Location:' .  $uri);
        exit();
    }

}