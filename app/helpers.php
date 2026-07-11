<?php

use App\Models\Stage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

if (!function_exists('image_from_email')) {

    function image_from_email($email)
    {
        return substr($email, 0, strpos($email, 'preda.info')-1) . '.webp';
    }

}

if (!function_exists('ile_opinii')) {
    function ile_opinii($ile) {
        // Sprawdzamy czy argument jest liczbą
        if (!is_numeric($ile) || $ile < 0) {
            return '0 opinii';
        }

        // Konwertujemy na integer
        $ile = (int)$ile;

        if ($ile == 0) {
            return '0 opinii';
        } elseif ($ile == 1) {
            return '1 opinia';
        } elseif ($ile >= 2 && $ile <= 4) {
            return $ile . ' opinie';
        } else {
            return $ile . ' opinii';
        }
    }
}

if (!function_exists('flatten')) {
    function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'lata',
            'm' => 'miesiąc',
            'w' => 'tydzień',
            'd' => 'dzień',
            'h' => 'godzina',
            'i' => 'minuta',
            's' => 'sekunda',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
            } else {
                unset($string[$k]);
            }
        }

        if($diff->y < 1) {
            if($diff->m < 1) {
                if($diff->w < 1) {
                    return 'kilka dni temu';
                } else {
                    if($diff->w == 1) {
                        return 'tydzień temu';
                    }
                    if($diff->w > 1 && $diff->w < 5) {
                        return $diff->w . ' tygodnie temu';
                    } else {
                        return $diff->w . ' tygodni temu';
                    }
                }
            } else {
                if($diff->m == 1) {
                    return 'miesiąc temu';
                }
                if($diff->m > 1 && $diff->m < 5) {
                    return $diff->m . ' miesiące temu';
                } else {
                    return $diff->m . ' miesięcy temu';
                }
            }
        } else {
            if($diff->y == 1) {
                return 'rok temu';
            }
            if($diff->y > 1 && $diff->y < 5) {
                return $diff->y . ' lata temu';
            } else {
                return $diff->y . ' lat temu';
            }
        }
    }
}

if (!function_exists('mos')) {
    function mos($content) {

        $words = str_replace(array(' r.', ' - '), array(' r.', ' - '), $content);
        $words = explode(" ", $words);
        $wynik = '';
        foreach($words as $word) {
            if(strlen($word) > 1) {
                if (strlen($word) === 2 &&
                (substr($word,0,1) === '"' || substr($word,0,1) === '"' || substr($word,0,1) === '('))
                    $wynik.= $word.' ';
                else
                    $wynik.= $word.' ';
            } else {
                if(is_numeric(substr($word,0,1)))
                    $wynik.= $word.' ';
                else
                    $wynik.= $word.' ';
            }
        }

        // dd($wynik);

        $wynik = preg_replace('/(?:&nbsp;){2,}/', 'X', $wynik);

        return trim($wynik);
    }
}

if (!function_exists('pipe2br')) {
    function pipe2br($content) {

        return str_replace('|', '<br />', $content);
    }
}

if (!function_exists('hd')) {
    function hd($originalDate, $f = 'human', $comma = true) {
        if (DateTime::createFromFormat('Y-m-d', $originalDate) === false) {
            return null;
        }

        $date = date("j.m.Y", strtotime($originalDate));

        if ($f === 'human') {
            $date = str_replace(
                array('.01.', '.02.', '.03.', '.04.', '.05.', '.06.', '.07.', '.08.', '.09.', '.10.', '.11.', '.12.'),
                array(' stycznia, ', ' lutego, ', ' marca, ', ' kwietnia, ', ' maja, ', ' czerwca, ', ' lipca, ', ' sierpnia, ', ' września, ', ' października, ', ' listopada, ', ' grudnia, '),
                $date
            );

            if(!$comma) {
                $date = str_replace(',', ' ', $date);
            }
        }

        return $date;
    }
}

if (!function_exists('wyrok_slug')) {
    function wyrok_slug($sad, $sign) {
        $sign_array = explode(' ', $sign);
        if(count($sign_array) == 3) {
            return Str::slug($sad.' '.$sign_array[0].$sign_array[1].' '.str_replace('/','-',$sign_array[2]));
        } else {
            return Str::slug($sad.$sign);
        }
    }
}

if (!function_exists('pln_format')) {
    function pln_format($str) {

        return number_format($str, 0, ',', '.') .' zł';

    }
}

if (! function_exists('bp_pl_url')) {
    function bp_pl_url($string) {

        return str_replace(
            array(' ', 'ą', 'ć', 'ę', 'ó', 'ś', 'ł', 'ż', 'ź', 'ć', 'ń'),
            array('-', 'a', 'c', 'e', 'o', 's', 'l', 'z', 'z', 'c', 'n'),
            strtolower($string)
        );

    }
}

if (! function_exists('bp_flatten')) {
    function bp_flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }
}

if (! function_exists('bp_human_date')) {
    function bp_human_date( $originalDate, $f = 'human' )
    {

        if (DateTime::createFromFormat('Y-m-d', $originalDate) === false)
        {
            return null;
        }

        $date = date("j.m.Y", strtotime($originalDate));

        if ($f === 'human')
        {
            $date = str_replace(
                array('.01.', '.02.', '.03.', '.04.', '.05.', '.06.', '.07.', '.08.', '.09.', '.10.', '.11.', '.12.'),
                array(' stycznia, ', ' lutego, ', ' marca, ', ' kwietnia, ', ' maja, ', ' czerwca, ', ' lipca, ', ' sierpnia, ', ' września, ', ' października, ', ' listopada, ', ' grudnia, '),
                $date
            );
        }

        return $date;
    }
}

if (! function_exists('bp_currency')) {
    function bp_currency($value, $po_przecinku = 0, $waluta = ' zł')
    {
        return number_format($value, $po_przecinku, ',', '.') . $waluta;
    }
}

if (! function_exists('bp_findJSON')) {
    function bp_findJSON($dane, $klucz, $default = '')
    {
        if(is_array($dane)) {
            foreach($dane as $datagroup) {
                foreach($datagroup['data'] as $key => $data) {
                    if($key == $klucz) {
                        return $data;
                    }
                }
            }
        }

        return $default;
    }
}

if (! function_exists('bp_non_breaking_spaces')) {
    function bp_non_breaking_spaces( $content ) {
        $words = str_replace(' r.', ' r.', $content);
        $words = explode(" ", $words);
        $wynik = '';
        foreach($words as $word) {
        if(strlen($word) > 1) {
            if (strlen($word) === 2 &&
            (substr($word,0,1) === '“' || substr($word,0,1) === '"'))
            $wynik.= $word.' ';
            else
            $wynik.= $word.' ';
        } else {
            if(substr($word,0,1) === '1' || substr($word,0,1) === '2' || substr($word,0,1) === '3' || substr($word,0,1) === '4' || substr($word,0,1) === '5' || substr($word,0,1) === '6' || substr($word,0,1) === '7' || substr($word,0,1) === '8' || substr($word,0,1) === '9' || substr($word,0,1) === '0')
                $wynik.= $word.' ';
            else
                $wynik.= $word.' ';
        }
        };
        return $wynik;
    }
}

if (! function_exists('bp_makeChart')) {
    function bp_makeChart($model = Stage::class, $stage_id = null, $field = 'stage_id') {

        $start = date("Y-m-d", strtotime("first day of january previous year"));
        $end = date("Y-m-d", strtotime("last day of december this year"));

        $this_year = date("Y");
        $prev_year = date("Y", strtotime("previous year"));

        $query = $model::query()
            ->when($stage_id, fn (Builder $query) => $query->where($field, $stage_id))
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $data = [];

        $data[$this_year] = array('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0);
        $data[$prev_year] = array('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0);

        $count[$this_year] = 0;
        $count[$prev_year] = 0;

        foreach($query as $key => $value) {

            $rok = substr($value->date, 0, 4);
            $miesiac = substr($value->date, 5, 2);

            $data[$rok][$miesiac]++;

            $count[$rok]++;

        }

        return [
            'datasets' => [
                [
                    'label' => $prev_year . ' (' .$count[$prev_year]. ')',
                    'data' => isset($data[$prev_year]) ? bp_flatten($data[$prev_year]) : []
                ],
                [
                    'label' => $this_year . ' (' .$count[$this_year]. ')',
                    'data' => isset($data[$this_year]) ? bp_flatten($data[$this_year]) : [],
                    'borderColor' => '#D60A52'
                ],
            ],
            'labels' => ['Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'],
        ];

    }
}
