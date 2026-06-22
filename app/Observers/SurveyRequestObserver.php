<?php

namespace App\Observers;

use App\Models\SurveyRequest;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class SurveyRequestObserver
{
    /**
     * Handle the SurveyRequest "updated" event.
     * Otomatis kirim notifikasi ke customer/tukang sesuai perubahan status.
     */
    public function updated(SurveyRequest $survey): void
    {
        // Hanya jalan jika kolom status berubah
        if (! $survey->wasChanged('status')) {
            return;
        }

        $oldStatus = $survey->getOriginal('status');
        $newStatus = $survey->status;

        Log::info("SurveyRequestObserver: {$oldStatus} → {$newStatus}", [
            'survey_id'   => $survey->id,
            'customer_id' => $survey->customer_id,
            'tukang_id'   => $survey->tukang_id,
        ]);

        // ── Tukang accept survey request ───────────────────────────
        // Status: requested → accepted
        // Notif ke: CUSTOMER
        if ($newStatus === 'accepted') {
            $this->sendToCustomer($survey, [
                'title' => 'Survey Disetujui ✅',
                'body'  => 'Tukang telah menyetujui permintaan survey Anda. Silakan lakukan pembayaran biaya survey.',
                'type'  => 'survey_approved',
                'data'  => [
                    'type'          => 'survey_approved',
                    'notifiable_id' => $survey->id,
                    'survey_id'     => $survey->id,
                    'survey_fee'    => (string) ($survey->survey_fee ?? 0),
                ],
            ]);
        }

        // ── Tukang reject survey request ───────────────────────────
        // Status: requested → rejected
        // Notif ke: CUSTOMER
        elseif ($newStatus === 'rejected') {
            $this->sendToCustomer($survey, [
                'title' => 'Survey Ditolak',
                'body'  => 'Maaf, tukang tidak dapat menerima permintaan survey Anda' .
                    ($survey->rejection_notes ? ': ' . $survey->rejection_notes : '.'),
                'type'  => 'survey',
                'data'  => [
                    'type'      => 'survey_rejected',
                    'survey_id' => $survey->id,
                ],
            ]);
        }

        // ── Tukang set harga/estimasi ───────────────────────────────
        // Status: on_survey/accepted → survey_priced
        // Notif ke: CUSTOMER
        elseif ($newStatus === 'survey_priced') {
            $this->sendToCustomer($survey, [
                'title' => 'Estimasi Harga Tersedia 📋',
                'body'  => 'Tukang telah mengirimkan estimasi harga survey. Silakan cek dan setujui.',
                'type'  => 'survey_priced',
                'data'  => [
                    'type'            => 'survey_priced',
                    'notifiable_id'   => $survey->id,
                    'survey_id'       => $survey->id,
                    'estimated_price' => (string) ($survey->estimated_price ?? 0),
                ],
            ]);
        }

        // ── Payment survey berhasil ─────────────────────────────────
        // Status: approved → confirmed
        // Notif ke: TUKANG
        elseif ($newStatus === 'confirmed') {
            $this->sendToTukang($survey, [
                'title' => 'Pembayaran Survey Diterima 💰',
                'body'  => 'Customer telah membayar biaya survey. Silakan lakukan survey sesuai jadwal.',
                'type'  => 'survey',
                'data'  => [
                    'type'      => 'survey_fee_paid',
                    'survey_id' => $survey->id,
                ],
            ]);
        }

        // ── Survey selesai ─────────────────────────────────────────
        // Status: → done
        // Notif ke: CUSTOMER
        elseif ($newStatus === 'done') {
            $this->sendToCustomer($survey, [
                'title' => 'Survey Selesai ✅',
                'body'  => 'Tukang telah menyelesaikan survey. Silakan cek laporan hasil survey.',
                'type'  => 'survey',
                'data'  => [
                    'type'      => 'survey_done',
                    'survey_id' => $survey->id,
                ],
            ]);
        }
    }

    // ── Private helpers ────────────────────────────────────────────

    private function sendToCustomer(SurveyRequest $survey, array $payload): void
    {
        try {
            UserNotification::send(
                userId: $survey->customer_id,
                title: $payload['title'],
                body: $payload['body'],
                type: $payload['type'],
                notifiable: $survey,
                data: $payload['data'] ?? [],
            );
        } catch (\Exception $e) {
            Log::error('SurveyRequestObserver sendToCustomer error: ' . $e->getMessage(), [
                'survey_id'   => $survey->id,
                'customer_id' => $survey->customer_id,
            ]);
        }
    }

    private function sendToTukang(SurveyRequest $survey, array $payload): void
    {
        try {
            UserNotification::send(
                userId: $survey->tukang_id,
                title: $payload['title'],
                body: $payload['body'],
                type: $payload['type'],
                notifiable: $survey,
                data: $payload['data'] ?? [],
            );
        } catch (\Exception $e) {
            Log::error('SurveyRequestObserver sendToTukang error: ' . $e->getMessage(), [
                'survey_id'  => $survey->id,
                'tukang_id'  => $survey->tukang_id,
            ]);
        }
    }

    public function created(SurveyRequest $surveyRequest): void {}
    public function deleted(SurveyRequest $surveyRequest): void {}
    public function restored(SurveyRequest $surveyRequest): void {}
    public function forceDeleted(SurveyRequest $surveyRequest): void {}
}
