<?php

namespace App\MyMall\Traits;

use Prologue\Alerts\Facades\Alert;

trait BackpackAlert
{

    public function alert($title, $text = '', $type = 'info', $shouldFlash = false)
    {
        $alert = Alert::add($type, '<strong>'.$title.'</strong></br>' . $text);

        if($shouldFlash)
            $alert->flash();
    }

}
