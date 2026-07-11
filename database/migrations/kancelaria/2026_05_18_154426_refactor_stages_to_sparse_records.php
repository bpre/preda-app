<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matters', function (Blueprint $table): void {
            if (! Schema::hasColumn('matters', 'current_template_stage_id')) {
                $table->foreignUuid('current_template_stage_id')
                    ->nullable()
                    ->after('end')
                    ->constrained('template_stages')
                    ->nullOnDelete();
            }
        });

        if (! Schema::hasTable('stage_placeholders_archive')) {
            Schema::create('stage_placeholders_archive', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('original_stage_id')->unique();
                $table->string('label');
                $table->text('description')->nullable();
                $table->text('files')->nullable();
                $table->text('files_names')->nullable();
                $table->integer('sort')->default(999);
                $table->string('parent');
                $table->date('date')->nullable();
                $table->uuid('matter_id');
                $table->boolean('is_current')->default(false);
                $table->uuid('stage_id')->nullable();
                $table->timestamp('original_created_at')->nullable();
                $table->timestamp('original_updated_at')->nullable();
                $table->timestamp('archived_at')->useCurrent();
            });
        }

        Schema::table('stages', function (Blueprint $table): void {
            $table->index(['matter_id', 'stage_id'], 'stages_matter_stage_index');
            $table->index(['matter_id', 'is_current'], 'stages_matter_current_index');
        });

        DB::statement(<<<'SQL'
            UPDATE matters
            SET current_template_stage_id = (
                SELECT stages.stage_id
                FROM stages
                WHERE stages.matter_id = matters.id
                    AND stages.is_current = 1
                    AND stages.stage_id IS NOT NULL
                LIMIT 1
            )
            WHERE matters.current_template_stage_id IS NULL
                AND EXISTS (
                    SELECT 1
                    FROM stages
                    WHERE stages.matter_id = matters.id
                        AND stages.is_current = 1
                        AND stages.stage_id IS NOT NULL
                )
        SQL);

        $blankPlaceholderStages = fn () => DB::table('stages')
            ->whereNull('stages.date')
            ->where('stages.is_current', false)
            ->where(function ($query): void {
                $query->whereNull('stages.description')
                    ->orWhereRaw("TRIM(stages.description) IN ('', '<p></p>', '<p><br></p>')");
            })
            ->where(function ($query): void {
                $query->whereNull('stages.files')
                    ->orWhereRaw("TRIM(stages.files) IN ('', '[]', 'null')");
            })
            ->where(function ($query): void {
                $query->whereNull('stages.files_names')
                    ->orWhereRaw("TRIM(stages.files_names) IN ('', '[]', 'null')");
            });

        DB::table('stage_placeholders_archive')->insertOrIgnoreUsing(
            [
                'original_stage_id',
                'label',
                'description',
                'files',
                'files_names',
                'sort',
                'parent',
                'date',
                'matter_id',
                'is_current',
                'stage_id',
                'original_created_at',
                'original_updated_at',
                'archived_at',
            ],
            $blankPlaceholderStages()
                ->select([
                    'stages.id',
                    'stages.label',
                    'stages.description',
                    'stages.files',
                    'stages.files_names',
                    'stages.sort',
                    'stages.parent',
                    'stages.date',
                    'stages.matter_id',
                    'stages.is_current',
                    'stages.stage_id',
                    'stages.created_at',
                    'stages.updated_at',
                    DB::raw('CURRENT_TIMESTAMP'),
                ]),
        );

        $blankPlaceholderStages()
            ->whereIn('stages.id', DB::table('stage_placeholders_archive')->select('original_stage_id'))
            ->delete();
    }

    public function down(): void
    {
        if (Schema::hasTable('stage_placeholders_archive')) {
            DB::table('stages')->insertOrIgnoreUsing(
                [
                    'id',
                    'label',
                    'description',
                    'files',
                    'files_names',
                    'sort',
                    'parent',
                    'date',
                    'matter_id',
                    'is_current',
                    'created_at',
                    'updated_at',
                    'stage_id',
                ],
                DB::table('stage_placeholders_archive')
                    ->select([
                        'original_stage_id',
                        'label',
                        'description',
                        'files',
                        'files_names',
                        'sort',
                        'parent',
                        'date',
                        'matter_id',
                        'is_current',
                        'original_created_at',
                        'original_updated_at',
                        'stage_id',
                    ]),
            );
        }

        Schema::table('stages', function (Blueprint $table): void {
            $table->dropIndex('stages_matter_stage_index');
            $table->dropIndex('stages_matter_current_index');
        });

        Schema::table('matters', function (Blueprint $table): void {
            if (Schema::hasColumn('matters', 'current_template_stage_id')) {
                $table->dropConstrainedForeignId('current_template_stage_id');
            }
        });
    }
};
