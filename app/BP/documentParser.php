<?php

namespace App\BP;

use App\Models\Credit;

class documentParser {

    public function parseData($template, $credit_id)
    {
        $credit = Credit::where('id', $credit_id)->first();

        $ilu = 2;

        $template = preg_replace_callback('#\[(.*?)\]#', function($replacement) use ($ilu) {
            $n = explode('|', $replacement[1]);
            return $n[$ilu == 1 ? '0' : '1'];
        }, $template);


        $template = str_replace(
            array(
                '@umowaNumer',
                '@umowaData'
            ),
            array(
                $credit ? $credit->number : '@umowaNumer',
                $credit ? bp_human_date($credit->date, 'n').' r.' : '@umowaData'
            ),
            $template
        );

        return $template;
    }

}

?>
