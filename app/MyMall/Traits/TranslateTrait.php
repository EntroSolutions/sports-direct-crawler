<?php

namespace App\MyMall\Traits;

use App\Models\Translate;
use Illuminate\Support\Str;

trait TranslateTrait
{
    /**
     * @param $string
     * @param $returnLang array one of en,bg,el,ro
     * @return mixed
     */
    public function translateString($string, $returnLang)
    {
        $translation = Translate::whereRaw('LOWER(en) = "' . Str::lower($string).'"')->first();

        if( !$translation ){
            Translate::create(['en' => $string]);
            return $string;
        }

        // Has translation row but no data for the selected language
        if( !$translation->{$returnLang} || $translation->{$returnLang} == '' ){
            return $string;
        }

        return $translation->{$returnLang};

    }

    public static function translateStringStatic($string, $returnLang)
    {
        $translation = Translate::whereRaw('LOWER(en) = "' . Str::lower($string).'"')->first();

        if( !$translation ){
            Translate::create(['en' => $string]);
            return $string;
        }

        // Has translation row but no data for the selected language
        if( !$translation->{$returnLang} || $translation->{$returnLang} == '' ){
            return $string;
        }

        return $translation->{$returnLang};

    }
}
