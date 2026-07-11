<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('website_sentence_content_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable()->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->string('instance')->nullable();
            $table->string('section');
            $table->json('all_of')->nullable();
            $table->json('any_of')->nullable();
            $table->json('none_of')->nullable();
            $table->integer('priority')->default(0);
            $table->string('selection_mode')->default('random');
            $table->text('content');
            $table->text('note')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('website_sentence_content_templates')->insert(array_map(
            fn (array $template): array => [
                ...$template,
                'is_active' => true,
                'selection_mode' => $template['selection_mode'] ?? 'random',
                'priority' => $template['priority'] ?? 0,
                'sort' => $template['sort'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $this->defaultTemplates(),
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_sentence_content_templates');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultTemplates(): array
    {
        return [
            [
                'key' => 'procedural-counterclaim-1',
                'name' => 'Powództwo wzajemne oddalone - wariant 1',
                'section' => 'procedural_events',
                'all_of' => json_encode(['counterclaim_dismissed']),
                'content' => 'Bank wniósł w sprawie powództwo wzajemne, jednak sąd go nie uwzględnił.',
            ],
            [
                'key' => 'procedural-counterclaim-2',
                'name' => 'Powództwo wzajemne oddalone - wariant 2',
                'section' => 'procedural_events',
                'all_of' => json_encode(['counterclaim_dismissed']),
                'content' => 'W toku sprawy {bank} zgłosił także powództwo wzajemne. Również ono nie zostało uwzględnione przez sąd.',
            ],
            [
                'key' => 'procedural-counterclaim-3',
                'name' => 'Powództwo wzajemne oddalone - wariant 3',
                'section' => 'procedural_events',
                'all_of' => json_encode(['counterclaim_dismissed']),
                'content' => 'Sąd nie podzielił stanowiska banku także w zakresie powództwa wzajemnego.',
            ],
            [
                'key' => 'procedural-setoff-1',
                'name' => 'Potrącenie nieuwzględnione - wariant 1',
                'section' => 'procedural_events',
                'all_of' => json_encode(['setoff_dismissed']),
                'content' => 'Sąd nie uwzględnił podniesionego przez bank zarzutu potrącenia.',
            ],
            [
                'key' => 'procedural-setoff-2',
                'name' => 'Potrącenie nieuwzględnione - wariant 2',
                'section' => 'procedural_events',
                'all_of' => json_encode(['setoff_dismissed']),
                'content' => 'Zarzut potrącenia zgłoszony przez {bank} nie zmienił korzystnego dla kredytobiorców rozstrzygnięcia.',
            ],
            [
                'key' => 'procedural-retention-1',
                'name' => 'Zatrzymanie nieuwzględnione - wariant 1',
                'section' => 'procedural_events',
                'all_of' => json_encode(['retention_dismissed']),
                'content' => 'Sąd nie uwzględnił podniesionego przez bank zarzutu zatrzymania.',
            ],
            [
                'key' => 'procedural-retention-2',
                'name' => 'Zatrzymanie nieuwzględnione - wariant 2',
                'section' => 'procedural_events',
                'all_of' => json_encode(['retention_dismissed']),
                'content' => 'Zarzut zatrzymania podniesiony przez {bank} okazał się nieskuteczny.',
            ],
            [
                'key' => 'evidence-borrower-documents-1',
                'name' => 'Dowody: kredytobiorcy + dokumenty - wariant 1',
                'section' => 'evidence',
                'all_of' => json_encode(['borrower_hearing', 'documents']),
                'priority' => 10,
                'content' => 'Postępowanie dowodowe koncentrowało się na dokumentach oraz przesłuchaniu kredytobiorców.',
            ],
            [
                'key' => 'evidence-borrower-documents-2',
                'name' => 'Dowody: kredytobiorcy + dokumenty - wariant 2',
                'section' => 'evidence',
                'all_of' => json_encode(['borrower_hearing', 'documents']),
                'priority' => 10,
                'content' => 'Dla rozstrzygnięcia kluczowe znaczenie miały dokumenty złożone w sprawie oraz zeznania kredytobiorców.',
            ],
            [
                'key' => 'evidence-documents-1',
                'name' => 'Dowody: dokumenty - wariant 1',
                'section' => 'evidence',
                'all_of' => json_encode(['documents']),
                'content' => 'Postępowanie dowodowe opierało się przede wszystkim na dokumentach złożonych do akt sprawy.',
            ],
            [
                'key' => 'evidence-expert-omitted-1',
                'name' => 'Pominięcie biegłego - wariant 1',
                'section' => 'evidence',
                'all_of' => json_encode(['expert_omitted']),
                'content' => 'Sąd pominął dowód z opinii biegłego, uznając go za zbędny dla rozstrzygnięcia sprawy.',
            ],
            [
                'key' => 'evidence-bank-witness-omitted-1',
                'name' => 'Pominięcie świadków banku - wariant 1',
                'section' => 'evidence',
                'all_of' => json_encode(['bank_witness_omitted']),
                'content' => 'Sąd pominął dowód z zeznań świadków banku, uznając go za nieprzydatny dla rozstrzygnięcia.',
            ],
            [
                'key' => 'security-granted-1',
                'name' => 'Zabezpieczenie - wariant 1',
                'section' => 'security',
                'all_of' => json_encode(['security_granted']),
                'content' => 'W toku sprawy kredytobiorcy uzyskali zabezpieczenie roszczenia, co pozwoliło im czasowo wstrzymać wykonywanie spornych obowiązków wynikających z umowy.',
            ],
            [
                'key' => 'security-granted-2',
                'name' => 'Zabezpieczenie - wariant 2',
                'section' => 'security',
                'all_of' => json_encode(['security_granted']),
                'content' => 'Na czas procesu sąd udzielił kredytobiorcom zabezpieczenia, wzmacniając ich sytuację jeszcze przed wydaniem końcowego wyroku.',
            ],
            [
                'key' => 'benefit-profit-payoff-1',
                'name' => 'Korzyść + wypłata - wariant 1',
                'section' => 'benefit',
                'all_of' => json_encode(['credit_profit_present', 'credit_payoff_present']),
                'priority' => 10,
                'content' => 'Szacowana korzyść kredytobiorców wynosi około {credit_profit}. Dla porównania, bank wypłacił kredyt w kwocie {credit_payoff}.',
            ],
            [
                'key' => 'benefit-profit-payoff-2',
                'name' => 'Korzyść + wypłata - wariant 2',
                'section' => 'benefit',
                'all_of' => json_encode(['credit_profit_present', 'credit_payoff_present']),
                'priority' => 10,
                'content' => 'Według danych zapisanych przy wyroku korzyść z wygrania sprawy to około {credit_profit}, przy kwocie wypłaconego kredytu wynoszącej {credit_payoff}.',
            ],
            [
                'key' => 'benefit-profit-1',
                'name' => 'Korzyść - wariant 1',
                'section' => 'benefit',
                'all_of' => json_encode(['credit_profit_present']),
                'content' => 'Szacowana korzyść kredytobiorców z wygrania sprawy wynosi około {credit_profit}.',
            ],
        ];
    }
};
