<?php

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

?>
