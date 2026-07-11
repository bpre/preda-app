<?php

namespace App\Http\Controllers;

use mysqli;
use App\Models\Deal;
use App\Models\Stage;
use App\Models\Branch;
use App\Models\Matter;
use App\Models\CreditDeal;
use App\Models\ContactDeal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TemplateStage;

class Database2Controller extends Controller
{
    public function __invoke()
    {

        set_time_limit(300);

        // Przypisz datę rozpoczęcia (start) do sprawy (na podstawie zlecenia)

        // foreach(Deal::where('is_draft', 0)->get() as $deal) {

        //     echo $deal->matter_id . ' - '. $deal->date .' <br> ';

        //     Matter::where('id', $deal->matter_id)->update(['start' => $deal->date]);

        // }



        // Przypisz ID oddziału do sprawy

        $branch = [];

        foreach(Branch::all() as $b) {

            $branch[$b->label] = $b->id;

        }

        foreach(Matter::all() as $matter) {

            if(isset($branch[$matter->branch])) {

                $matter->branch_id = $branch[$matter->branch];
                $matter->save();

            }

        }



        // SPRAWDŹ, czy istnieją kontakty, do których odwołuje się opponent_lawfirm_id

        // foreach(Matter::all() as $matter) {

        //     echo $matter->label .'<br>';

        //     if($matter->opponent_lawfirm_id) {

        //         echo $matter->opponent_lawfirm_id .'<br>';
        //         echo ($matter->opponent_lawfirm ? $matter->opponent_lawfirm->label : 'BRAK') .'<br>';

        //     }




        //     echo '<br><br>';
        // }


        // UZUPEŁNIENIE `opponent_lawfirm_id` w tabeli `matters` na podstawie danych pełnomocnika (`opponent_lawyer_id`)

        /*

        foreach(Matter::all() as $matter) {

            echo $matter->label .'<br>';

            if($matter->opponent_lawyer) {

                echo 'PEŁNOMOCNIK: ' . $matter->opponent_lawyer?->label . '<br>';

                if($matter->opponent_lawfirm) {

                    echo 'AKTUALNA KANCELARIA: ' . $matter->opponent_lawfirm?->label . '<br>';

                } else {

                    echo 'KANCELARIA: ' . $matter->opponent_lawyer?->contact_lawfirm?->label . ' (' . $matter->opponent_lawyer?->contact_lawfirm?->id . ') <br>';

                    if($matter->opponent_lawyer->contact_lawfirm) {

                        $matter->opponent_lawfirm_id = $matter->opponent_lawyer?->contact_lawfirm?->id;
                        $matter->save();
                    }
                }
            }



            echo '<br><br>';
        }

        */


        // POPRAWKI UUID - w tabeli `deals`

        /*

        $servername = "localhost";
        $username = "mjp_crm";
        $password = "-)_]4OanNL+s4RK-";

        $mysqli = new mysqli($servername, $username, $password, 'mjp_p2');

        $deals = $mysqli->query("select * from deals");

        $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

        while($row = $deals->fetch_assoc()) {

            $id = Str::uuid();

            echo $row['id'].' - '.$id.'<br>';

            $mysqli->query("update credit_deal set deal_id='".$id."' where deal_id='".$row['id']."'");
            $mysqli->query("update contact_deal set deal_id='".$id."' where deal_id='".$row['id']."'");
            $mysqli->query("update deals set id='".$id."' where id='".$row['id']."'");

        }

        */



        // dodanie rekordów do tabel `stages` => NOWYCH puste etapy
        // if(true) {


        //     foreach(Matter::all() as $matter)
        //     {

        //         foreach(TemplateStage::orderBy('sort')->get() as $stage)
        //         {


        //             if($stage->label == 'Zlecono prowadzenie sprawy')
        //             {

        //                 Stage::create([
        //                     'label' => $stage->label,
        //                     'sort' => $stage->sort,
        //                     'parent' => $stage->parent,
        //                     'matter_id' => $matter->id,
        //                     'stage_id' => $stage->id,
        //                     'is_current' => $stage->is_lead_default,
        //                     'date' => $stage->is_lead_default ? now() : NULL
        //                 ]);

        //             }
        //             else{
        //                 Stage::where('matter_id', $matter->id)->where('stage_id', $stage->id)->update(['sort' => $stage->sort]);
        //             }

        //         }

        //     }


        // }


    }
}
