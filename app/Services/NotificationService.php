<?php

// <!-- // ============================================================
// FILE: app/Services/NotificationService.php
// ============================================================
// Service untuk membuat notifikasi dengan type yang benar
// Panggil ini dari JobSurveyController saat tukang acc survey -->

namespace App\Services;

use App\Models\Notification;
use App\Models\SurveyRequest;
use App\Models\Order;

class NotificationService
{
    /**
     * Notifikasi ke CUSTOMER: survey diterima tukang
     * Type: survey_approved
     * Trigger: Tukang accept survey → JobSurveyController@accept
     */
    public static function surveyApprovedToCustomer(SurveyRequest $survey): void
    {
        Notification::create([
            'user_id'      => $survey->customer_id,
            'title'        => "{$survey->tukang->name} menerima permintaan surveymu!",
            'body'         => "Tukang akan datang ke lokasi Anda. Biaya kedatangan: Rp "
                . number_format($survey->survey_fee ?? 0, 0, ',', '.'),
            'type'         => 'survey_approved', // ← PENTING: cocok dengan NotificationType di Flutter
            'reference_id' => $survey->id,
            'data'         => [
                'survey_id'      => $survey->id,
                'tukang_name'    => $survey->tukang->name,
                'tukang_photo'   => $survey->tukang->tukangProfile?->photo
                    ? asset($survey->tukang->tukangProfile->photo) : null,
                'rating'         => $survey->tukang->tukangProfile?->rating,
                'service_name'   => $survey->service->name,
                'address'        => $survey->address,
                'survey_date'    => $survey->survey_date?->toDateTimeString(),
                'survey_fee'     => $survey->survey_fee,
                'status'         => $survey->status,
            ],
        ]);
    }

    /**
     * Notifikasi ke CUSTOMER: tukang sudah mengisi estimasi harga
     * Type: survey_priced
     * Trigger: Tukang set-price → JobSurveyController@setPrice
     */
    public static function surveyPricedToCustomer(SurveyRequest $survey): void
    {
        $surveyServices = $survey->surveyServices()->with('service')->get();
        $subtotal = $surveyServices->sum(fn($ss) => ($ss->estimated_price ?? 0) * $ss->qty);
        $total    = $subtotal * 1.10; // + 10% service fee

        Notification::create([
            'user_id'      => $survey->customer_id,
            'title'        => "{$survey->tukang->name} memberikan estimasi harga",
            'body'         => "Total estimasi: Rp " . number_format($total, 0, ',', '.')
                . ". Setujui untuk membuat booking.",
            'type'         => 'survey_priced', // ← type ini buka halaman estimasi di Flutter
            'reference_id' => $survey->id,
            'data'         => [
                'survey_id'      => $survey->id,
                'tukang_name'    => $survey->tukang->name,
                'tukang_photo'   => $survey->tukang->tukangProfile?->photo
                    ? asset($survey->tukang->tukangProfile->photo) : null,
                'rating'         => $survey->tukang->tukangProfile?->rating,
                'service_name'   => $survey->service->name,
                'estimated_price' => $subtotal,
                'estimated_days' => $survey->estimated_days,
                'tukang_notes'   => $survey->tukang_notes,
                'status'         => $survey->status,
                'survey_services' => $surveyServices->map(fn($ss) => [
                    'id'               => $ss->id,
                    'service_name'     => $ss->service_name,
                    'unit'             => $ss->service?->unit,
                    'estimated_price'  => $ss->estimated_price,
                    'qty'              => $ss->qty,
                    'subtotal'         => ($ss->estimated_price ?? 0) * $ss->qty,
                    'notes'            => $ss->notes,
                ])->toArray(),
            ],
        ]);
    }

    /**
     * Notifikasi ke CUSTOMER: order berhasil dibuat dari estimasi
     * Type: order_created
     * Trigger: Customer approve estimasi → SurveyRequestController@approve
     */
    public static function orderCreatedToCustomer(Order $order): void
    {
        Notification::create([
            'user_id'      => $order->customer_id,
            'title'        => "Booking berhasil dibuat! 🎉",
            'body'         => "No. Order: {$order->order_number}. Segera lakukan pembayaran Rp "
                . number_format($order->total_price, 0, ',', '.'),
            'type'         => 'order_created', // ← buka halaman payment di Flutter
            'reference_id' => $order->id,
            'data'         => [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'total_price'  => $order->total_price,
                'status'       => $order->status,
            ],
        ]);
    }
}
