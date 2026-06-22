<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    /**
     * Kirim notifikasi ke satu user
     */
    public static function send(
        int    $userId,
        string $title,
        string $body,
        string $type       = 'system',
        mixed  $notifiable = null
    ): Notification {
        return Notification::create([
            'user_id'         => $userId,
            'title'           => $title,
            'body'            => $body,
            'type'            => $type,
            'notifiable_id'   => $notifiable?->id,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'is_read'         => false,
        ]);
    }

    /**
     * Kirim notifikasi ke banyak user sekaligus
     */
    public static function sendMany(
        array  $userIds,
        string $title,
        string $body,
        string $type       = 'system',
        mixed  $notifiable = null
    ): void {
        $now  = now();
        $data = array_map(fn($id) => [
            'user_id'         => $id,
            'title'           => $title,
            'body'            => $body,
            'type'            => $type,
            'notifiable_id'   => $notifiable?->id,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'is_read'         => false,
            'created_at'      => $now,
            'updated_at'      => $now,
        ], $userIds);

        Notification::insert($data);
    }

    // =========================================================
    // Template notifikasi siap pakai
    // =========================================================

    // ── Order ─────────────────────────────────────────────────

    public static function orderCreated($order, string $tukangName): void
    {
        self::send(
            userId: $order->customer_id,
            title: 'Order Berhasil Dibuat',
            body: "Ordermu #{$order->order_number} telah dikirim ke {$tukangName}.",
            type: 'order',
            notifiable: $order
        );
    }

    public static function orderAccepted($order, string $tukangName): void
    {
        self::send(
            userId: $order->customer_id,
            title: 'Order Diterima',
            body: "{$tukangName} menerima ordermu #{$order->order_number}.",
            type: 'order',
            notifiable: $order
        );
    }

    public static function orderRejected($order, string $tukangName): void
    {
        self::send(
            userId: $order->customer_id,
            title: 'Order Ditolak',
            body: "{$tukangName} tidak bisa menerima ordermu #{$order->order_number}.",
            type: 'order',
            notifiable: $order
        );
    }

    public static function orderStarted($order, string $tukangName): void
    {
        self::send(
            userId: $order->customer_id,
            title: 'Pengerjaan Dimulai',
            body: "{$tukangName} sudah mulai mengerjakan ordermu #{$order->order_number}.",
            type: 'order',
            notifiable: $order
        );
    }

    public static function orderCompleted($order): void
    {
        self::send(
            userId: $order->customer_id,
            title: 'Order Selesai',
            body: "Order #{$order->order_number} selesai dikerjakan. Jangan lupa beri review!",
            type: 'order',
            notifiable: $order
        );
    }

    public static function orderCancelled($order, string $cancelledBy): void
    {
        self::send(
            userId: $order->customer_id,
            title: 'Order Dibatalkan',
            body: "Order #{$order->order_number} telah dibatalkan oleh {$cancelledBy}.",
            type: 'order',
            notifiable: $order
        );

        if ($order->tukang_id) {
            self::send(
                userId: $order->tukang_id,
                title: 'Order Dibatalkan',
                body: "Order #{$order->order_number} telah dibatalkan oleh {$cancelledBy}.",
                type: 'order',
                notifiable: $order
            );
        }
    }

    public static function newOrderForTukang($order, string $customerName): void
    {
        self::send(
            userId: $order->tukang_id,
            title: 'Order Baru Masuk',
            body: "{$customerName} memesan jasamu. Segera konfirmasi!",
            type: 'order',
            notifiable: $order
        );
    }

    // ── Payment ───────────────────────────────────────────────

    public static function paymentSuccess($order): void
    {
        self::send(
            userId: $order->customer_id,
            title: 'Pembayaran Berhasil',
            body: "Pembayaran order #{$order->order_number} telah dikonfirmasi.",
            type: 'payment',
            notifiable: $order
        );
    }

    // ── Survey ────────────────────────────────────────────────

    public static function surveyRequested($survey, string $customerName): void
    {
        $serviceName = $survey->service->name ?? 'layanan';
        self::send(
            userId: $survey->tukang_id,
            title: 'Permintaan Survey Baru',
            body: "{$customerName} meminta survey untuk {$serviceName}.",
            type: 'survey',
            notifiable: $survey
        );
    }

    public static function surveyAccepted($survey, string $tukangName): void
    {
        self::send(
            userId: $survey->customer_id,
            title: 'Survey Diterima',
            body: "{$tukangName} menerima permintaan surveymu.",
            type: 'survey',
            notifiable: $survey
        );
    }

    public static function surveyRejected($survey, string $tukangName): void
    {
        self::send(
            userId: $survey->customer_id,
            title: 'Survey Ditolak',
            body: "{$tukangName} tidak dapat melakukan survey saat ini.",
            type: 'survey',
            notifiable: $survey
        );
    }

    public static function surveyPriced($survey, string $tukangName): void
    {
        self::send(
            userId: $survey->customer_id,
            title: 'Estimasi Harga Masuk',
            body: "{$tukangName} sudah mengirim estimasi harga. Cek dan setujui sekarang!",
            type: 'survey',
            notifiable: $survey
        );
    }

    // ── Earning ───────────────────────────────────────────────

    public static function earningSettled($earning, float $amount): void
    {
        $amountFormatted = number_format($amount, 0, ',', '.');
        $orderNumber     = $earning->order->order_number ?? '';
        self::send(
            userId: $earning->tukang_id,
            title: 'Pendapatan Siap Dicairkan',
            body: "Rp {$amountFormatted} dari order #{$orderNumber} siap dicairkan.",
            type: 'earning',
            notifiable: $earning
        );
    }

    public static function withdrawalProcessed($withdrawal, string $status): void
    {
        $amountFormatted = number_format($withdrawal->amount, 0, ',', '.');
        $title = $status === 'success' ? 'Penarikan Berhasil' : 'Penarikan Gagal';
        $body  = $status === 'success'
            ? "Rp {$amountFormatted} berhasil ditransfer ke rekeningmu."
            : "Rp {$amountFormatted} gagal diproses. Silakan hubungi admin.";

        self::send(
            userId: $withdrawal->tukang_id,
            title: $title,
            body: $body,
            type: 'earning',
            notifiable: $withdrawal
        );
    }

    // ── Chat ──────────────────────────────────────────────────

    public static function newMessage($chat, string $senderName, int $receiverId): void
    {
        self::send(
            userId: $receiverId,
            title: "Pesan dari {$senderName}",
            body: 'Kamu mendapat pesan baru.',
            type: 'chat',
            notifiable: $chat
        );
    }

      // ── Booking ───────────────────────────────────────────────

    /**
     * Customer buat booking order → notif ke tukang
     * Dipanggil di: Customer/OrderController@createBooking
     */
    public static function bookingCreatedForTukang($booking, string $customerName): void
    {
        // $booking->survey->tukang_id — ambil tukang dari relasi survey
        $tukangId = $booking->survey->tukang_id ?? null;
        if (!$tukangId) return;

        $date = \Carbon\Carbon::parse($booking->scheduled_date)
            ->translatedFormat('d F Y');

        self::send(
            userId: $tukangId,
            title: 'Booking Baru!',
            body: "{$customerName} menjadwalkan pengerjaan pada {$date}. Siap-siap ya!",
            type: 'booking',
            notifiable: $booking
        );
    }

    /**
     * Konfirmasi booking ke customer setelah berhasil dibuat
     * Dipanggil di: Customer/OrderController@createBooking (setelah bookingCreatedForTukang)
     */
    public static function bookingConfirmedForCustomer($booking): void
    {
        $date = \Carbon\Carbon::parse($booking->scheduled_date)
            ->translatedFormat('d F Y');

        self::send(
            userId: $booking->customer_id,
            title: 'Booking Dikonfirmasi',
            body: "Booking kamu untuk {$date} sudah dibuat. Lanjutkan ke pembayaran.",
            type: 'booking',
            notifiable: $booking
        );
    }

    /**
     * Notif ke customer setelah payment booking sukses
     * Dipanggil di: Customer/PaymentController atau Midtrans webhook
     */
    public static function bookingPaid($booking): void
    {
        $date = \Carbon\Carbon::parse($booking->scheduled_date)
            ->translatedFormat('d F Y');

        // Notif customer
        self::send(
            userId: $booking->customer_id,
            title: 'Pembayaran Booking Berhasil',
            body: "Pembayaran untuk booking {$date} telah dikonfirmasi. Tukang akan segera menghubungimu.",
            type: 'payment',
            notifiable: $booking
        );

        // Notif tukang
        $tukangId = $booking->survey->tukang_id ?? null;
        if ($tukangId) {
            $customerName = $booking->customer->name ?? 'Customer';
            self::send(
                userId: $tukangId,
                title: 'Booking Lunas!',
                body: "{$customerName} sudah membayar booking untuk {$date}. Pastikan kamu siap!",
                type: 'payment',
                notifiable: $booking
            );
        }
    }
}
