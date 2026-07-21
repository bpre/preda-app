<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmClientMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'matter_id',
        'crm_mail_template_id',
        'action',
        'recipient_name',
        'recipient_email',
        'subject',
        'body',
        'target_stage_id',
        'default_offer_attached',
        'crm_workflow_offer_id',
        'crm_workflow_offer_label',
        'default_offer_disk',
        'default_offer_path',
        'default_offer_filename',
        'attachments',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'id' => 'string',
        'default_offer_attached' => 'boolean',
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class, 'matter_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrmMailTemplate::class, 'crm_mail_template_id');
    }

    public function targetStage(): BelongsTo
    {
        return $this->belongsTo(TemplateStage::class, 'target_stage_id');
    }

    public function workflowOffer(): BelongsTo
    {
        return $this->belongsTo(CrmWorkflowOffer::class, 'crm_workflow_offer_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function mailgunEvents(): HasMany
    {
        return $this->hasMany(MailgunEvent::class, 'crm_client_message_id')
            ->orderByDesc('occurred_at')
            ->orderByDesc('created_at');
    }
}
