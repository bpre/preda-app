<?php

namespace App\Http\Controllers;

use mysqli;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    public function __invoke()
    {


        set_time_limit(300);

        $servername = "127.0.0.1";
        $username = "root";
        $password = "";

        $mysqli = new mysqli($servername, $username, $password, 'mjp_crm');

        $sprawy = $mysqli->query("select * from matters");
        $sprawy2 = $mysqli->query("select * from matters");
        $kontakty = $mysqli->query("select * from contacts");
        $umowy = $mysqli->query("select * from credits");
        // $kontakty2 = $mysqli->query("select * from contacts");
        // $files = $mysqli->query("select * from files");
        $letters = $mysqli->query("select * from letters");
        $payments = $mysqli->query("select * from payments");
        $lawsuits = $mysqli->query("select * from lawsuits");
        $orders = $mysqli->query("select * from orders");
        $users = $mysqli->query("select * from users");
        $files = $mysqli->query("select * from files");
        $stages_1 = $mysqli->query("select * from stages where parent_id='1' order by `order`");
        $stages_2 = $mysqli->query("select * from stages where parent_id='2' order by `order`");
        $stages_3 = $mysqli->query("select * from stages where parent_id='3' order by `order`");
        $stages_4 = $mysqli->query("select * from stages where parent_id='8' order by `order`");
        $stages2 = $mysqli->query("select * from stages");
        $matter_stage = $mysqli->query("select * from matter_stage");
        $contact_letter = $mysqli->query("select * from contact_letter");
        $contact_credit = $mysqli->query("select * from contact_credit");
        $contact_credit2 = $mysqli->query("select * from contact_credit");
        $contact_order = $mysqli->query("select * from contact_order");
        $contact_order2 = $mysqli->query("select * from contact_order");
        $credit_order = $mysqli->query("select * from credit_order");
        $clauses = $mysqli->query("select * from clauses");
        $clauses2 = $mysqli->query("select * from clauses");
        $clauses3 = $mysqli->query("select * from clauses");

        $mysqli = new mysqli($servername, $username, $password, 'p2');

        $mysqli->query('SET foreign_key_checks = 0');

        $mysqli->query("truncate matters");
        $mysqli->query("truncate credits");
        $mysqli->query("truncate contact_credit");
        $mysqli->query("truncate contacts");
        // $mysqli->query("truncate clauses");
        // $mysqli->query("truncate files");
        $mysqli->query("truncate letters");
        $mysqli->query("truncate payments");
        $mysqli->query("truncate lawsuits");
        $mysqli->query("truncate deals");
        $mysqli->query("truncate users");
        $mysqli->query("truncate stages");
        $mysqli->query("truncate template_stages");
        // $mysqli->query("truncate matter_stage");
        // $mysqli->query("truncate matter_details");
        $mysqli->query("truncate contact_letter");
        $mysqli->query("truncate contact_credit");
        $mysqli->query("truncate contact_deal");
        $mysqli->query("truncate credit_deal");
        // $mysqli->query("truncate credit_details");


        // dodanie rekordów do tabeli `users`
        if ($users->num_rows > 0) {

            while($row = $users->fetch_assoc()) {

                if(in_array($row["email"], array(
                    'bartosz.preda@preda.info',
                    'wiktoria.rajzynger@preda.info',
                    'mateusz.wilk@preda.info'
                ))) {
                    $row['is_lawyer'] = 1;
                } else {
                    $row['is_lawyer'] = 0;
                }

                if($row["email"] == "bartosz.preda@preda.info") {
                    $row["name_genitive"] = "Bartosza Prędę";
                }
                elseif($row["email"] == "wiktoria.rajzynger@preda.info") {
                    $row["name_genitive"] = "Wiktorię Rajzynger";
                }
                else {
                    $row["name_genitive"] = "";
                }

                $mysqli->query("insert into users (id, name, email, password, role, is_lawyer, name_genitive) values (
                    '".$row["id"]."',
                    '".$row["name"]."',
                    '".$row["email"]."',
                    '".$row["password"]."',
                    '".$row['role']."',
                    '".$row['is_lawyer']."',
                    '".$row['name_genitive']."'
                )");

            }
        }

        // dodanie rekokordów do tabeli `matters`
        if ($sprawy->num_rows > 0) {
            while($row = $sprawy->fetch_assoc()) {

                $mysqli->query("insert into matters(
                    `id`, `old_id`, `label`, `lawyer_id`, `category`, `gdrive`, `opponent_lawyer_id`, `created_at`, `updated_at`, `status`, `is_matter`, `branch`
                    ) values (
                    '".$row["uuid"]."',
                    '".$row["id"]."',
                    '".$row["label"]."',
                    '".$row["opiekun_id"]."',
                    '".$row["kategoria"]."',
                    '".$row["gdrive"]."',
                    '".$row["pelnomocnik_id"]."',
                    '".$row["created_at"]."',
                    '".$row["updated_at"]."',
                    '".strtolower($row["status"])."',
                    '1',
                    'Głogów'
                )");

                $sprawy_ZG = "
                    'e2f7400f-88ed-499e-9a25-ab9b57dafc6d', 'b0108be0-1745-4264-a7c4-1bb4034c016e', '1b841c82-db02-472e-bc62-f63bb187cfd1', '93888dba-3015-4858-a65d-6945e013cbe4',
                    '099649d3-076e-469c-bf20-5f4f172540b7', 'd4add462-d9cf-45f8-83fd-dab65e8519f7', '2c05a691-74de-47b6-9c64-c39a3184a721', 'b0c993d7-ad9e-4358-8981-43242d816b6e',
                    'ad0ae699-8706-41e8-85e0-addf9febe031', '7b12b581-2f23-43ad-a25a-ce258ebe991c', 'e55bfe2b-dedd-45c2-8b1f-f4492e63eff7', 'ab70859b-5993-4075-8303-7ef510516f64',
                    'dd0a5289-bf1a-48fd-ae8d-c86b9c060551', 'f36787f7-5e2a-4549-8cfb-43d5715ffb23'
                ";

                $mysqli->query("update matters set branch='Zielona Góra' where id IN ($sprawy_ZG)");

            }
        }

        // dodanie rekokordów do tabeli `payments`
        if ($payments->num_rows > 0) {

            $nowe_sprawy = $mysqli->query("select * from matters");

            if ($nowe_sprawy->num_rows > 0) {
                while($row = $nowe_sprawy->fetch_assoc()) {
                    $sprawa[$row['old_id']] = $row['id'];

                }
            }


            while($row = $payments->fetch_assoc()) {

                if($row['data'] == '') {
                    $date = '0001-01-01';
                } else {
                    $date = $row['data'];
                }

                $sql = "insert into payments(
                    `id`, `old_id`, `label`, `matter_id`, `deadline`, `date`, `amount`,
                    `is_paid`, `created_at`, `updated_at`) values (
                    '".Str::uuid()."',
                    '".$row["id"]."',
                    '".$row["label"]."',
                    '".$sprawa[$row["matter_id"]]."',
                    '".$row["termin"]."',
                    '".$date."',
                    '".$row["kwota"]."',
                    '".$row["paid"]."',
                    '".substr($row["created_at"], 0, 19)."',
                    '".substr($row["updated_at"], 0, 19)."'
                )";

                $mysqli->query($sql);

                $mysqli->query("update payments set date=NULL where date='0001-01-01'");

            }
        }

        // dodanie rekokordów do tabeli `contacts`
        if ($kontakty->num_rows > 0) {
            while($row = $kontakty->fetch_assoc()) {
                $mysqli->query("insert into contacts(
                    id, old_id, type, category, first_name, last_name, label, sort_name, organization, organization_short, pesel, status, sex, email, phone, address, zip_code, city, krs, profession, lawfirm_id, created_at, updated_at
                    ) values (
                    '".Str::uuid()."',
                    '".$row["id"]."',
                    '".$row["typ"]."',
                    '".$row["kategoria"]."',
                    '".$row["imie"]."',
                    '".$row["nazwisko"]."',
                    '".$row["label"]."',
                    '".$row["sort"]."',
                    '".$row["organizacja"]."',
                    '".$row["org"]."',
                    '".$row["pesel"]."',
                    '".$row["status"]."',
                    '".$row["plec"]."',
                    '".$row["email"]."',
                    '".$row["telefon"]."',
                    '".$row["adres"]."',
                    '".$row["kod"]."',
                    '".$row["miasto"]."',
                    '".$row["krs"]."',
                    '".$row["tytul_zawodowy"]."',
                    '".$row["kancelaria_id"]."',
                    '".$row["created_at"]."',
                    '".$row["updated_at"]."'
                )");
            }
        }

        // aktualizacja `opponent_lawyer_id` w tabeli `matters` i `lawfirm_id` w `contacts`
        if(true) {

            $nowe_kontakty = $mysqli->query("select * from contacts");

            if ($nowe_kontakty->num_rows > 0) {
                while($row = $nowe_kontakty->fetch_assoc()) {
                    $pelnomocnik[$row['old_id']] = $row['id'];

                }
            }

            $nowe_sprawy = $mysqli->query("select * from matters");
            if ($nowe_sprawy->num_rows > 0) {
                while($row = $nowe_sprawy->fetch_assoc()) {
                    if(isset($pelnomocnik[$row['opponent_lawyer_id']])) {
                        $mysqli->query("update matters set `opponent_lawyer_id` = '".$pelnomocnik[$row['opponent_lawyer_id']]."' where id='".$row['id']."'");
                    }
                }
            }

            $nowe_kontakty = $mysqli->query("select * from contacts");
            if ($nowe_kontakty->num_rows > 0) {
                while($row = $nowe_kontakty->fetch_assoc()) {
                    if(isset($pelnomocnik[$row['lawfirm_id']])) {
                        $mysqli->query("update contacts set `lawfirm_id` = '".$pelnomocnik[$row['lawfirm_id']]."' where id='".$row['id']."'");
                    }
                }
            }

        }

        // dodanie rekordów do tabeli `credits`
        if ($umowy->num_rows > 0) {
            while($row = $umowy->fetch_assoc()) {
                $mysqli->query("insert into credits(
                    id, old_id, former_bank, current_bank, number, date, matter_id, created_at, updated_at
                    ) values (
                    '".Str::uuid()."',
                    '".$row["id"]."',
                    '".$row["bank_umowa"]."',
                    '".$row["bank_obecnie"]."',
                    '".$row["numer"]."',
                    '".$row["data"]."',
                    '".$row["matter_id"]."',
                    '".$row["created_at"]."',
                    '".$row["updated_at"]."'
                )");
            }
        }

        // aktualizacja `matter_id` w tabeli `credits`
        if(true) {

            $nowe_sprawy = $mysqli->query("select * from matters");

            if ($nowe_sprawy->num_rows > 0) {
                while($row = $nowe_sprawy->fetch_assoc()) {
                    $sprawa[$row['old_id']] = $row['id'];
                }
            }

            $nowe_umowy = $mysqli->query("select * from credits");
            if ($nowe_umowy->num_rows > 0) {
                while($row = $nowe_umowy->fetch_assoc()) {

                    if(isset($sprawa[$row['matter_id']])) {

                        // echo $sprawa[$row['matter_id']].'<br>';

                        $mysqli->query("update credits set `matter_id` = '".$sprawa[$row['matter_id']]."' where id='".$row['id']."'");
                    }
                }
            }
        }

        // aktualizacja `former_bank` oraz `current_bank` w tabeli `credits`
        if(true) {

            $nowe_kontakty = $mysqli->query("select * from contacts");

            if ($nowe_kontakty->num_rows > 0) {
                while($row = $nowe_kontakty->fetch_assoc()) {
                    $kontakt[$row['old_id']] = $row['id'];
                }
            }

            $nowe_umowy = $mysqli->query("select * from credits");
            if ($nowe_umowy->num_rows > 0) {
                while($row = $nowe_umowy->fetch_assoc()) {

                    if(isset($kontakt[$row['former_bank']])) {

                        // echo 'BANK UMOWA: '.$kontakt[$row['bank_umowa']].'<br>';

                        $mysqli->query("update credits set `former_bank` = '".$kontakt[$row['former_bank']]."' where id='".$row['id']."'");
                    }

                    if(isset($kontakt[$row['current_bank']])) {

                        // echo 'BANK OBECNIE: '.$kontakt[$row['bank_obecnie']].'<br><br>';

                        $mysqli->query("update credits set `current_bank` = '".$kontakt[$row['current_bank']]."' where id='".$row['id']."'");
                    }
                }
            }
        }

        // dodanie rekordów do tabeli `letters`
        if ($letters->num_rows > 0) {
            while($row = $letters->fetch_assoc()) {
                $mysqli->query("insert into letters(
                    id, old_id, label, date, type, matter_id, sender_id, created_at, updated_at
                    ) values (
                    '".Str::uuid()."',
                    '".$row["id"]."',
                    '".$row["label"]."',
                    '".$row["data"]."',
                    '".$row["typ"]."',
                    '".$row["matter_id"]."',
                    '".$row["od"]."',
                    '".$row["created_at"]."',
                    '".$row["updated_at"]."'
                )");
            }
        }

        // aktualizacja `matter_id` w tabeli `letters`
        if(true) {

            $nowe_sprawy = $mysqli->query("select * from matters");

            if ($nowe_sprawy->num_rows > 0) {
                while($row = $nowe_sprawy->fetch_assoc()) {
                    $sprawa[$row['old_id']] = $row['id'];
                }
            }

            $new_letters = $mysqli->query("select * from letters");
            if ($new_letters->num_rows > 0) {
                while($row = $new_letters->fetch_assoc()) {

                    if(isset($sprawa[$row['matter_id']])) {

                        // echo $sprawa[$row['matter_id']].'<br>';

                        $mysqli->query("update letters set `matter_id` = '".$sprawa[$row['matter_id']]."' where id='".$row['id']."'");
                    }
                }
            }
        }

        // aktualizacja `sender_id` w tabeli `letters`
        if(true) {

            $nowe_kontakty = $mysqli->query("select * from contacts");

            if ($nowe_kontakty->num_rows > 0) {
                while($row = $nowe_kontakty->fetch_assoc()) {
                    $kontakt[$row['old_id']] = $row['id'];
                }
            }

            $nowe_umowy = $mysqli->query("select * from letters");
            if ($nowe_umowy->num_rows > 0) {
                while($row = $nowe_umowy->fetch_assoc()) {

                    if(isset($kontakt[$row['sender_id']])) {

                        $mysqli->query("update letters set `sender_id` = '".$kontakt[$row['sender_id']]."' where id='".$row['id']."'");
                    }
                }
            }

        }

        // dodanie rekordów do tabeli `contact_letter`
        if ($contact_letter->num_rows > 0) {

            $nowe_kontakty = $mysqli->query("select * from contacts");

            if ($nowe_kontakty->num_rows > 0) {
                while($row = $nowe_kontakty->fetch_assoc()) {
                    $kontakt[$row['old_id']] = $row['id'];
                }
            }

            $new_letters = $mysqli->query("select * from letters");

            if ($new_letters->num_rows > 0) {
                while($row = $new_letters->fetch_assoc()) {
                    $letter[$row['old_id']] = $row['id'];
                }
            }

            while($row = $contact_letter->fetch_assoc()) {
                $mysqli->query("insert into contact_letter(
                    contact_id, letter_id
                    ) values (
                    '".$kontakt[$row["contact_id"]]."',
                    '".$letter[$row["letter_id"]]."'
                )");
            }
        }

        // dodanie rekordów do tabeli `lawsuits`
        if (true) {

            $new_matters = $mysqli->query("select * from matters");

            if ($new_matters->num_rows > 0) {
                while($row = $new_matters->fetch_assoc()) {
                    $matter[$row['old_id']] = $row['id'];
                }
            }

            $new_contacts = $mysqli->query("select * from contacts");

            if ($new_contacts->num_rows > 0) {
                while($row = $new_contacts->fetch_assoc()) {
                    $contact[$row['old_id']] = $row['id'];
                }
            }

            while($row = $lawsuits->fetch_assoc()) {

                if($row['data_zakonczenia'] == null) {
                    $row['data_zakonczenia'] = '2999-01-01';
                }

                $mysqli->query("insert into lawsuits(
                    id, old_id, instance, signature, matter_id, court_id, judge_id, start_date, end_date, created_at, updated_at
                    ) values (
                    '".Str::uuid()."',
                    '".$row["id"]."',
                    '".$row["instancja"]."',
                    '".$row["sign"]."',
                    '".$matter[$row["matter_id"]]."',
                    '".$contact[$row["sad_id"]]."',
                    '".$contact[$row["sedzia_id"]]."',
                    '".$row["data_rozpoczecia"]."',
                    '".$row["data_zakonczenia"]."',
                    '".$row["created_at"]."',
                    '".$row["updated_at"]."'
                )");
            }

            $mysqli->query("update lawsuits set end_date=NULL where end_date='2999-01-01'");

        }

        // dodanie rekordów do tabeli `orders`
        if ($orders->num_rows > 0) {

            $new_matters = $mysqli->query("select * from matters");

            if ($new_matters->num_rows > 0) {
                while($row = $new_matters->fetch_assoc()) {
                    $matter[$row['old_id']] = $row['id'];
                }
            }

            while($row = $orders->fetch_assoc()) {

                if($row['data'] == null) {
                    $row['data'] = '0001-01-01';
                }
                if($row['pierwsza_rata'] == null) {
                    $row['pierwsza_rata'] = '0001-01-01';
                }
                if($row['oferta_data'] == null) {
                    $row['oferta_data'] = '0001-01-01';
                }
                if($row['premia_kwota'] == '') {
                    $row['premia_kwota'] = 0.0;
                }

                if($row["matter_id"]) {

                    $mysqli->query("insert into deals(
                        id, old_id, label, date, entry_fee, stage_one_fee, stage_two_fee, re_recogniction_fee, supreme_court_fee, bank_lawsuit_fee, hearing_fee, hearing_online_fee, is_bonus, bonus_percent, bonus_minimum, installments, first_installment_date, bonus_fee, matter_id, created_at, updated_at
                        ) values (
                        '".uniqid()."',
                        '".$row["id"]."',
                        '".$row["label"]."',
                        '".$row["data"]."',
                        '".$row["wstepna"]."',
                        '".$row["pierwsza"]."',
                        '".$row["druga"]."',
                        '".$row["ponowna"]."',
                        '".$row["sn"]."',
                        '".$row["korzystanie"]."',
                        '".$row["rozprawa"]."',
                        '".$row["rozprawa_online"]."',
                        '".($row["premia"] > 0 ? 1 : 0)."',
                        '".$row["premia"]."',
                        '".$row["premia_min"]."',
                        '".$row["ile_rat"]."',
                        '".$row["pierwsza_rata"]."',
                        '".$row["premia_kwota"]."',
                        '".$matter[$row["matter_id"]]."',
                        '".$row["created_at"]."',
                        '".$row["updated_at"]."'
                    )");

                }

                $mysqli->query("update deals set date=NULL where date='0001-01-01'");
                $mysqli->query("update deals set first_installment_date=NULL where first_installment_date='0001-01-01'");

            }
        }

        // dodanie ścieżek do plików w tabeli `letters`
        if ($files->num_rows > 0) {

            while($row = $files->fetch_assoc()) {

                $pliki[$row['letter_id']][] = array(
                    'file' => str_replace('/', '\/', str_replace('pliki', 'k1', $row['path'])),
                    'file_name' => str_replace('/', '\/', $row['name'])
                );
                // echo $row['name'].'<br>';

            }

            foreach ($pliki as $letter_id => $file) {

                $f[$letter_id] = [];
                $f_names[$letter_id] = [];

                foreach ($file as $key => $row) {
                    array_push($f[$letter_id], $row['file']);
                    array_push($f_names[$letter_id], array($row['file'] => $row['file_name']));
                }
            }

            foreach($f as $letter_id => $file) {

                $sql = "update letters set files='".json_encode($file)."' where old_id='".$letter_id."'";
                $mysqli->query($sql);

                // $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($f_names[$letter_id]));
                $arr = $f_names[$letter_id];
                $it = array_merge(...$arr);

                // var_dump($it);
                // echo json_encode($it, JSON_UNESCAPED_UNICODE).'<br>';

                $sql2 = "update letters set files_names='".json_encode($it, JSON_UNESCAPED_UNICODE)."' where old_id='".$letter_id."'";
                $mysqli->query($sql2);

            }
        }

        // dodanie rekordów do tabeli `template_stages`
        if (true) {

            $n = 1;

            while($row_1 = $stages_1->fetch_assoc()) {
                $sql = "insert into template_stages(`id`, `label`, `sort`, `parent`, `is_lead_default`, `is_chf_default`) values (
                    '".intval($row_1["id"])."', '".$row_1["label"]."', '".$n."',
                    'Pozyskanie klienta',
                    '".($row_1['id'] == 4 ? 1 : 0)."', '".($row_1['id'] == 6 ? 1 : 0)."'
                )";
                $mysqli->query($sql);
                $n++;
            }

            while($row_2 = $stages_2->fetch_assoc()) {
                $sql = "insert into template_stages(`id`, `label`, `sort`, `parent`, `is_lead_default`, `is_chf_default`) values (
                    '".intval($row_2["id"])."', '".$row_2["label"]."', '".$n."',
                    'Etap przedsądowy',
                    '".($row_2['id'] == 4 ? 1 : 0)."', '".($row_2['id'] == 6 ? 1 : 0)."'
                )";
                $mysqli->query($sql);
                $n++;
            }

            while($row_3 = $stages_3->fetch_assoc()) {
                $sql = "insert into template_stages(`id`, `label`, `sort`, `parent`, `is_lead_default`, `is_chf_default`) values (
                    '".intval($row_3["id"])."', '".$row_3["label"]."', '".$n."',
                    'I instancja',
                    '".($row_3['id'] == 4 ? 1 : 0)."', '".($row_3['id'] == 6 ? 1 : 0)."'
                )";
                $mysqli->query($sql);
                $n++;
            }

            while($row_4 = $stages_4->fetch_assoc()) {
                $sql = "insert into template_stages(`id`, `label`, `sort`, `parent`, `is_lead_default`, `is_chf_default`) values (
                    '".intval($row_4["id"])."', '".$row_4["label"]."', '".$n."',
                    'II instancja',
                    '".($row_4['id'] == 4 ? 1 : 0)."', '".($row_4['id'] == 6 ? 1 : 0)."'
                )";
                $mysqli->query($sql);
                $n++;
            }
            // while($row2 = $stages2->fetch_assoc()) {

            //     if(isset($row2["parent_id"]) AND $row2["parent_id"] != '' AND is_numeric($row2["parent_id"])) {
            //         $par_id = intval($row2["parent_id"]);
            //     } else {
            //         $par_id = NULL;
            //     }

            //     if($par_id) {
            //         $mysqli->query("update stages set `parent_id`='".$par_id."' where id=".$row2['id']);
            //     }

            // }
        }

        // dodanie rekordów do tabel `stages` => puste etapy
        if(true) {

            $stages_sql = $mysqli->query("select * from template_stages order by sort");

            while($stages = $stages_sql->fetch_assoc()) {

                $stage[]=$stages;

            }

            $new_matters = $mysqli->query("select * from matters");

            while($matter = $new_matters->fetch_assoc()) {

                foreach ($stage as $key => $val) {
                    // echo $key.'<br>';

                    if($val['parent'] == "Pozyskanie klienta") { $parent_order = 1; }
                    elseif($val['parent'] == "Etap przedsądowy") { $parent_order = 2; }
                    elseif($val['parent'] == "I instancja") { $parent_order = 3; }
                    elseif($val['parent'] == "II instancja") { $parent_order = 4; }
                    else { $parent_order = 0; }

                    if(isset($parent_order)) {

                        $sql = "insert into stages (`label`, `sort`, `parent`, `matter_id`, `stage_id`)
                        values ('".$val['label']."', '".$val['sort']."', '".$val['parent']."', '".$matter['id']."', '".$val['id']."')";

                        $mysqli->query($sql);

                    }

                }

            }

        }

        // dodanie rekordów do tabeli `matter_stage`
        if ($matter_stage->num_rows > 0) {

            while($rowx = $sprawy2->fetch_assoc()) {
                $sprawa[$rowx['id']] = $rowx['stage_id'];
            }

            $new_matters = $mysqli->query("select * from matters");

            while($rowi = $new_matters->fetch_assoc()) {
                $new_matter[$rowi['old_id']] = $rowi['id'];
                $st[$rowi['old_id']] = $sprawa[$rowi['old_id']];
            }

            while($row = $matter_stage->fetch_assoc()) {

                $is_current = 0;

                if(isset($st[$row['matter_id']])) {
                    if($st[$row['matter_id']] == $row['stage_id']) {
                        $is_current = 1;
                    }
                }

                $sql = "update stages set date='".$row['data']."', is_current='".$is_current."' where matter_id='".$new_matter[$row['matter_id']]."' AND stage_id='".$row["stage_id"]."'";

                $mysqli->query($sql);

            }

        }

        // dodanie rekordów do tabeli `contact_credit`
        if ($contact_credit->num_rows > 0) {

            $nowe_kontakty = $mysqli->query("select * from contacts");

            if ($nowe_kontakty->num_rows > 0) {
                while($row = $nowe_kontakty->fetch_assoc()) {
                    $kontakt[$row['old_id']] = $row['id'];
                }
            }

            $new_credits = $mysqli->query("select * from credits");

            if ($new_credits->num_rows > 0) {
                while($row = $new_credits->fetch_assoc()) {
                    $credit[$row['old_id']] = $row['id'];
                }
            }

            while($row = $contact_credit->fetch_assoc()) {
                $mysqli->query("insert into contact_credit(
                    contact_id, credit_id
                    ) values (
                    '".$kontakt[$row["contact_id"]]."',
                    '".$credit[$row["credit_id"]]."'
                )");
            }
        }

        // dodanie rekordów do tabeli `contact_deal`
        if ($contact_order->num_rows > 0) {

            $nowe_kontakty = $mysqli->query("select * from contacts");

            if ($nowe_kontakty->num_rows > 0) {
                while($row = $nowe_kontakty->fetch_assoc()) {
                    $kontakt[$row['old_id']] = $row['id'];
                }
            }

            $new_orders = $mysqli->query("select * from deals");

            if ($new_orders->num_rows > 0) {
                while($row = $new_orders->fetch_assoc()) {
                    $deal[$row['old_id']] = $row['id'];
                }
            }

            while($row = $contact_order->fetch_assoc()) {

                if(isset($deal[$row["order_id"]]) AND isset($kontakt[$row["contact_id"]])) {

                    $mysqli->query("insert into contact_deal(
                        contact_id, deal_id
                        ) values (
                        '".$kontakt[$row["contact_id"]]."',
                        '".$deal[$row["order_id"]]."'
                    )");

                }

            }
        }

        // dodanie rekordów do tabeli `credit_deal`
        if ($credit_order->num_rows > 0) {

            $new_credits = $mysqli->query("select * from credits");

            if ($new_credits->num_rows > 0) {
                while($row = $new_credits->fetch_assoc()) {
                    $credit[$row['old_id']] = $row['id'];
                }
            }

            $new_orders = $mysqli->query("select * from deals");

            if ($new_orders->num_rows > 0) {
                while($row = $new_orders->fetch_assoc()) {
                    $order[$row['old_id']] = $row['id'];
                }
            }

            while($row = $credit_order->fetch_assoc()) {

                if(isset($credit[$row["credit_id"]]) AND isset($order[$row["order_id"]])) {

                    $mysqli->query("insert into credit_deal(
                        credit_id, deal_id
                        ) values (
                        '".$credit[$row["credit_id"]]."',
                        '".$order[$row["order_id"]]."'
                    )");

                }

            }
        }

        // dodanie rekordów do tabeli `credit_details`
        if ($clauses2->num_rows > 0) {

            $credits = $mysqli->query("select * from credits");

            if ($credits->num_rows > 0) {
                while($row = $credits->fetch_assoc()) {
                    $credit_uuid[$row['old_id']] = $row['id'];
                }
            }

            $allowed = array(
                'kwota-kredytu',
                'rodzaj-kredytu',
                'cel-kredytu',
                'klauzula-przeliczeniowa',
                'klauzule-niedozwolone',
                'oprocentowanie',
                'liczba-rat',
                'rodzaj-rat',
                'klauzula-ryzyka',
                'inne',
                'klauzule-pouczenia',
                'zmienne-oprocentowanie',
                'unww',
                'ocena-prawna', 'analiza-uwagi', 'analiza-uwagi-klient'
            );

            $arr = array(
                'kwota-kredytu' => 'kwota',
                'rodzaj-kredytu' => 'rodzaj-kredytu',
                'cel-kredytu' => 'cel',
                'klauzule-niedozwolone' => 'klauzule-zbiorczo',
                'klauzula-przeliczeniowa' => 'klauzula-spreadowa',
                'oprocentowanie' => 'oprocentowanie',
                'liczba-rat' => 'liczba-rat',
                'rodzaj-rat' => 'rodzaj-rat',
                'klauzula-ryzyka' => 'klauzula-ryzyka',
                'inne' => 'inne-klauzule',
                'klauzule-pouczenia' => 'pouczenie',
                'zmienne-oprocentowanie' => 'zmienne-oprocentowanie',
                'unww' => 'unww',
                'ocena-prawna' => 'analiza',
                'analiza-uwagi' => 'analiza-uwagi',
                'analiza-uwagi-klient' => 'analiza-uwagi-klient'
            );

            $details = [];

            while($row = $clauses2->fetch_assoc()) {

                if(in_array($row['typ'], $allowed)) {
                    // echo $row['credit_id']. ' - ' . $arr[$row['typ']] . ' - ' .$row['klauzula'].'<br>';
                    if($arr[$row['typ']]) {
                        $details[$row['credit_id']][$arr[$row['typ']]] = array(
                            'klauzula' => $row['klauzula'],
                            'jednostka' => $row['jednostka'],
                        );
                    }

                }
            }

            function cl($string) {

                $pattern = '/<p(.*?)>((.*?)+)\<\/p>/';
                $replacement = '${2}

                ';


                return $string ? strip_tags(str_replace(array('&nbsp;'), array(''), preg_replace($pattern, $replacement, $string))) : null;

            }

            function clb($string) {

                return strip_tags(addslashes(str_replace(array("\r\n", "\n"),"", $string)));
            }

            function clb2($string) {

                return addslashes(str_replace(array("\r\n", "\n"),"", $string));
            }

            foreach($details as $key => $credet) {



                $unww = (isset($credet['unww']) && $credet['unww']['klauzula']!== null) ? clb($credet['unww']['klauzula']) : null;
                $pouczenie = (isset($credet['pouczenie']) && $credet['pouczenie']['klauzula']!== null) ? clb($credet['pouczenie']['klauzula']) : null;
                $inne = (isset($credet['inne-postanowienia']) && $credet['inne-postanowienia']['klauzula']!== null) ? clb($credet['inne-postanowienia']['klauzula']) : null;
                $klauzula_ryzyka = (isset($credet['klauzula-ryzyka']) && $credet['klauzula-ryzyka']['klauzula']!== null) ? clb($credet['klauzula-ryzyka']['klauzula']) : null;
                $klauzule_zbiorczo = (isset($credet['klauzule-zbiorczo']) && $credet['klauzule-zbiorczo']['klauzula']!== null) ? clb($credet['klauzule-zbiorczo']['klauzula']) : null;
                $klauzula_spreadowa = (isset($credet['klauzula-spreadowa']) && $credet['klauzula-spreadowa']['klauzula']!== null) ? clb($credet['klauzula-spreadowa']['klauzula']) : null;
                $zmienne_oprocentowanie = (isset($credet['zmienne-oprocentowanie']) && $credet['zmienne-oprocentowanie']['klauzula']!== null) ? clb($credet['zmienne-oprocentowanie']['klauzula']) : null;
                $analiza = (isset($credet['analiza']) && $credet['analiza']['klauzula']!== null) ? clb2($credet['analiza']['klauzula']) : null;
                $analiza_uwagi = (isset($credet['analiza-uwagi']) && $credet['analiza-uwagi']['klauzula']!== null) ? clb($credet['analiza-uwagi']['klauzula']) : null;
                $analiza_uwagi_klient = (isset($credet['analiza-uwagi-klient']) && $credet['analiza-uwagi-klient']['klauzula']!== null) ? clb($credet['analiza-uwagi-klient']['klauzula']) : null;

                // $klauzula_ryzyka = null;
                // $klauzule_zbiorczo = null;
                // $klauzula_spreadowa = null;
                // $zmienne_oprocentowanie = null;
                // $analiza = null;
                // $analiza_uwagi = null;
                // $analiza_uwagi_klient = null;

                $new_details[$key] = [
                    [
                        "data" => [

                            "kwota" => (isset($credet['kwota']) && $credet['kwota']['klauzula']!== null) ? clb($credet['kwota']['klauzula']) : null,
                            "waluta" => null,
                            "rodzaj-kredytu" => null,

                            "cel" => (isset($credet['cel']) && $credet['cel']['klauzula']!== null) ? clb($credet['cel']['klauzula']) : null,
                            "cel-um" => isset($credet['cel']) ? $credet['cel']['jednostka'] : null,

                            "liczba-rat" => isset($credet['liczba-rat']) ? $credet['liczba-rat']['klauzula'] : null,
                            "liczba-rat-um" => isset($credet['liczba-rat']) ? $credet['liczba-rat']['jednostka'] : null,

                            "rodzaj-rat" => isset($credet['rodzaj-rat']) ? $credet['rodzaj-rat']['klauzula'] : null,
                            "rodzaj-rat-um" => isset($credet['rodzaj-rat']) ? $credet['rodzaj-rat']['jednostka'] : null,

                            "oprocentowanie" => isset($credet['oprocentowanie']) ? $credet['oprocentowanie']['klauzula'] : null,
                            "oprocentowanie-um" => isset($credet['oprocentowanie']) ? $credet['oprocentowanie']['jednostka'] : null
                        ],
                        "type" => "Parametry umowy"
                    ],
                    [
                        "data" => [
                            "unww" => $unww,
                            "pouczenie" => $pouczenie,
                            "inne-klauzule" => $inne,
                            "klauzula-ryzyka" => $klauzula_ryzyka,
                            "klauzule-zbiorczo" => $klauzule_zbiorczo,
                            "klauzula-spreadowa" => $klauzula_spreadowa,
                            "zmienne-oprocentowanie" => $zmienne_oprocentowanie
                        ],
                        "type" => "Klauzule"
                    ],
                    [
                        "data" => [
                            "analiza" => $analiza,
                            "analiza-uwagi-klient" => $analiza_uwagi_klient,
                            "analiza-uwagi" => $analiza_uwagi
                        ],
                        "type" => "Ocena prawna"
                    ]
                ];
            }

            // print_r($new_details);

            $credits = $mysqli->query("select * from credits");

            while($row = $credits->fetch_assoc()) {

                if(isset($new_details[$row['old_id']])) {

                    // print_r($new_details[$row['old_id']]);

                    $sql = "update credits set details = '".json_encode($new_details[$row['old_id']], JSON_UNESCAPED_UNICODE)."' where old_id='".$row['old_id']."'";
                    // echo $sql . "<br><br>";
                    $mysqli->query($sql);
                }

            }





            // foreach($credit_details as $key => $credit) {

            //     foreach($credit as $k => $v) {

            //             // $pattern = '/<p(.*?)>((.*?)+)\<\/p>/';
            //             // $replacement = '${2}

            //             // ';
            //             // $out = str_replace('&nbsp;', '', preg_replace($pattern, $replacement, $v));

            //         if($v['jednostka']) {
            //             $value = $v['klauzula'] ." (".$v['jednostka'].")";
            //         } else {
            //             $value = $v['klauzula'];
            //         }

            //         $sql = "update credit_details set `".$k."` = '".$value."' where credit_id='".$credit_uuid[$key]."'";
            //         $mysqli->query($sql);;
            //     }

            // }


        }

        // dodanie rekordów do tabeli `matters` => userinfo
        if ($clauses2->num_rows > 0) {

            $credits = $mysqli->query("select * from credits");

            if ($credits->num_rows > 0) {
                while($row = $credits->fetch_assoc()) {
                    $credit_uuid[$row['old_id']] = $row['id'];
                }
            }

            $allowed = array(
                'kwota-kredytu',
                'rodzaj-kredytu',
                'cel-kredytu',
                'klauzula-przeliczeniowa',
                'klauzule-niedozwolone',
                'oprocentowanie',
                'liczba-rat',
                'rodzaj-rat',
                'klauzula-ryzyka',
                'inne',
                'klauzule-pouczenia',
                'zmienne-oprocentowanie',
                'unww',
                'ocena-prawna', 'analiza-uwagi', 'analiza-uwagi-klient'
            );

            $arr = array(
                'kwota-kredytu' => 'kwota',
                'rodzaj-kredytu' => 'rodzaj-kredytu',
                'cel-kredytu' => 'cel',
                'klauzule-niedozwolone' => 'klauzule-zbiorczo',
                'klauzula-przeliczeniowa' => 'klauzula-spreadowa',
                'oprocentowanie' => 'oprocentowanie',
                'liczba-rat' => 'liczba-rat',
                'rodzaj-rat' => 'rodzaj-rat',
                'klauzula-ryzyka' => 'klauzula-ryzyka',
                'inne' => 'inne-klauzule',
                'klauzule-pouczenia' => 'pouczenie',
                'zmienne-oprocentowanie' => 'zmienne-oprocentowanie',
                'unww' => 'unww',
                'ocena-prawna' => 'analiza',
                'analiza-uwagi' => 'analiza-uwagi',
                'analiza-uwagi-klient' => 'analiza-uwagi-klient'
            );

            $details = [];

            while($row = $clauses2->fetch_assoc()) {

                if(in_array($row['typ'], $allowed)) {
                    // echo $row['credit_id']. ' - ' . $arr[$row['typ']] . ' - ' .$row['klauzula'].'<br>';
                    if($arr[$row['typ']]) {
                        $details[$row['credit_id']][$arr[$row['typ']]] = array(
                            'klauzula' => $row['klauzula'],
                            'jednostka' => $row['jednostka'],
                        );
                    }

                }
            }

            // function cl($string) {

            //     $pattern = '/<p(.*?)>((.*?)+)\<\/p>/';
            //     $replacement = '${2}

            //     ';


            //     return $string ? strip_tags(str_replace('&nbsp;', '', preg_replace($pattern, $replacement, $string))) : null;

            // }

            // function clb($string) {

            //     return strip_tags(addslashes(str_replace(array("\r\n", "\n"),"", $string)));
            // }

            // function clb2($string) {

            //     return addslashes(str_replace(array("\r\n", "\n"),"", $string));
            // }

            foreach($details as $key => $credet) {



                $unww = (isset($credet['unww']) && $credet['unww']['klauzula']!== null) ? clb($credet['unww']['klauzula']) : null;
                $pouczenie = (isset($credet['pouczenie']) && $credet['pouczenie']['klauzula']!== null) ? clb($credet['pouczenie']['klauzula']) : null;
                $inne = (isset($credet['inne-postanowienia']) && $credet['inne-postanowienia']['klauzula']!== null) ? clb($credet['inne-postanowienia']['klauzula']) : null;
                $klauzula_ryzyka = (isset($credet['klauzula-ryzyka']) && $credet['klauzula-ryzyka']['klauzula']!== null) ? clb($credet['klauzula-ryzyka']['klauzula']) : null;
                $klauzule_zbiorczo = (isset($credet['klauzule-zbiorczo']) && $credet['klauzule-zbiorczo']['klauzula']!== null) ? clb($credet['klauzule-zbiorczo']['klauzula']) : null;
                $klauzula_spreadowa = (isset($credet['klauzula-spreadowa']) && $credet['klauzula-spreadowa']['klauzula']!== null) ? clb($credet['klauzula-spreadowa']['klauzula']) : null;
                $zmienne_oprocentowanie = (isset($credet['zmienne-oprocentowanie']) && $credet['zmienne-oprocentowanie']['klauzula']!== null) ? clb($credet['zmienne-oprocentowanie']['klauzula']) : null;
                $analiza = (isset($credet['analiza']) && $credet['analiza']['klauzula']!== null) ? clb2($credet['analiza']['klauzula']) : null;
                $analiza_uwagi = (isset($credet['analiza-uwagi']) && $credet['analiza-uwagi']['klauzula']!== null) ? clb($credet['analiza-uwagi']['klauzula']) : null;
                $analiza_uwagi_klient = (isset($credet['analiza-uwagi-klient']) && $credet['analiza-uwagi-klient']['klauzula']!== null) ? clb($credet['analiza-uwagi-klient']['klauzula']) : null;

                // $klauzula_ryzyka = null;
                // $klauzule_zbiorczo = null;
                // $klauzula_spreadowa = null;
                // $zmienne_oprocentowanie = null;
                // $analiza = null;
                // $analiza_uwagi = null;
                // $analiza_uwagi_klient = null;

                $new_details[$key] = [
                    [
                        "data" => [
                            "najem" => "",
                            "zawod" => "",
                            "zamieszkanie" => "",
                            "wyksztalcenie" => "",
                            "najem_szczegoly" => "",
                            "zamieszkanie_do" => "",
                            "dzialalnosc_okres" => "",
                            "dzialalnosc_koszty" => "",
                            "dzialalnosc_zwiazek" => "",
                            "wczesniejszy_kredyt" => "",
                            "dzialalnosc_charakter" => "",
                            "wczesniejszy_kredyt_desc" => "",
                            "dzialalnosc_w_chwili_umowy" => "",
                            "czy_kiedykolwiek_dzialalnosc" => ""
                        ],
                        "type" => "Status konsumenta"
                    ],
                    [
                        "data" => [
                            "symulacje" => "",
                            "porownanie" => "",
                            "co_o_ryzyku" => "",
                            "info_ryzyko" => "",
                            "inny_kredyt" => "",
                            "zdolnosc_pln" => "",
                            "splata_innego" => "",
                            "info_stabilnosc" => "",
                            "dlaczego_walutowy" => "",
                            "gdzie_formalnosci" => "",
                            "historyczne_kursy" => ""
                        ],
                        "type" => "Okoliczności zawarcia umowy"
                    ],
                    [
                        "data" => [
                            "spory" => "",
                            "istotne" => "",
                            "spory_desc" => "",
                            "czy_splacony" => "",
                            "istotne_desc" => "",
                            "sposob_splaty" => "",
                            "waluta_splaty" => "",
                            "kwestionowanie" => "",
                            "sposob_splaty_desc" => "",
                            "kwestionowanie_desc" => "",
                            "wielu_kredytobiorcow" => "",
                            "przedterminowa_splata" => "",
                            "sposob_splaty_przez_wielu" => ""
                        ],
                        "type" => "Wykonywanie umowy"
                    ]
                ];
            }

            // print_r($new_details);

            $credits = $mysqli->query("select * from credits");

            while($row = $credits->fetch_assoc()) {

                if(isset($new_details[$row['old_id']])) {

                    // print_r($new_details[$row['old_id']]);

                    $sql = "update credits set details = '".json_encode($new_details[$row['old_id']], JSON_UNESCAPED_UNICODE)."' where old_id='".$row['old_id']."'";
                    // echo $sql . "<br><br>";
                    $mysqli->query($sql);
                }

            }





            // foreach($credit_details as $key => $credit) {

            //     foreach($credit as $k => $v) {

            //             // $pattern = '/<p(.*?)>((.*?)+)\<\/p>/';
            //             // $replacement = '${2}

            //             // ';
            //             // $out = str_replace('&nbsp;', '', preg_replace($pattern, $replacement, $v));

            //         if($v['jednostka']) {
            //             $value = $v['klauzula'] ." (".$v['jednostka'].")";
            //         } else {
            //             $value = $v['klauzula'];
            //         }

            //         $sql = "update credit_details set `".$k."` = '".$value."' where credit_id='".$credit_uuid[$key]."'";
            //         $mysqli->query($sql);;
            //     }

            // }


        }

    }
}
