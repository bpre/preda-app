<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use App\Filament\Resources\DealResource;
use Illuminate\Support\Collection;

class DealsRelationManager extends RelationManager
{
    protected static string $relationship = 'deals';

    protected static ?string $title = 'Zlecenia';

    protected static ?string $modelLabel = 'Zlecenie';
    protected static ?string $pluralModelLabel = 'Zlecenia';

    public function form(Schema $schema): Schema
    {
        return DealResource::form($schema);
    }

    public function table(Table $table): Table
    {
        $table = DealResource::table(
            table: $table,
            hideDraftsByDefault: $this->shouldHideDraftsByDefault(),
        );

        return $table->header(fn () => $this->sentOfferMessages()->isNotEmpty()
            ? view('filament.resources.chf-matter-resource.relation-managers.deals-sent-offers-header', [
                'messages' => $this->sentOfferMessages(),
            ])
            : null);
    }

    protected function shouldHideDraftsByDefault(): bool
    {
        return $this->getOwnerRecord()?->is_matter !== false;
    }

    public function sentOfferMessages(): Collection
    {
        $owner = $this->getOwnerRecord();

        if (! $owner || ! method_exists($owner, 'crmClientMessages')) {
            return collect();
        }

        return $owner->crmClientMessages()
            ->where('default_offer_attached', true)
            ->with(['workflowOffer', 'sender'])
            ->orderByDesc('sent_at')
            ->orderByDesc('created_at')
            ->get();
    }
}
