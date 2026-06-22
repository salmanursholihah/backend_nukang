<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Service;
use App\Models\SurveyRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * SurveyPaymentSeeder
 *
 * Mensimulasikan flow lengkap:
 *   1. Buat user customer & tukang (jika belum ada)
 *   2. Customer buat survey request  → status: requested
 *   3. Tukang approve survey         → status: accepted
 *      → Notif ke customer: "Tukang menerima survey"
 *   4. Tukang isi estimasi harga     → status: survey_priced
 *      → Notif ke customer: "Tukang mengisi estimasi"
 *   5. Customer approve estimasi     → status: approved
 *      → Notif ke tukang: "Customer menyetujui estimasi"
 *   6. Payment record dibuat (pending, transfer BCA)
 *      → Notif ke customer: "Silakan lakukan pembayaran"
 *   7. Simulasi VA Midtrans sudah dibuat
 *      (midtrans_order_id, va_number, expiry_time tersimpan)
 *
 * Setelah seeder ini dijalankan:
 *   - Login sebagai customer → buka survey → bisa simulasi bayar
 *   - Login sebagai tukang   → lihat notifikasi approval
 */
class SurveyPaymentSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // ── 1. Siapkan users ───────────────────────────────────
            $customer = $this->findOrCreateUser([
                'name'     => 'Budi Santoso (Customer)',
                'email'    => 'customer@test.com',
                'password' => Hash::make('password'),
                'role'     => 'customer',
                'phone'    => '081234567890',
                'is_active' => true,
            ]);

            $tukang = $this->findOrCreateUser([
                'name'     => 'Ubaldo Schaden Sr. (Tukang)',
                'email'    => 'tukang@test.com',
                'password' => Hash::make('password'),
                'role'     => 'tukang',
                'phone'    => '089876543210',
                'is_active' => true,
            ]);

            $this->command->info("✅ Customer ID: {$customer->id} ({$customer->email})");
            $this->command->info("✅ Tukang   ID: {$tukang->id} ({$tukang->email})");

            // ── 2. Siapkan service ─────────────────────────────────
            $service = Service::first();
            if (!$service) {
                $service = Service::create([
                    'name'        => 'Isi Freon AC',
                    'description' => 'Pengisian freon AC rumahan',
                    'base_price'  => 150000,
                    'category_id' => 1,
                ]);
            }

            $this->command->info("✅ Service: {$service->name}");

            // ── 3. Buat Survey Request (customer minta survey) ─────
            $survey = SurveyRequest::create([
                'customer_id'  => $customer->id,
                'tukang_id'    => $tukang->id,
                'service_id'   => $service->id,
                'address'      => 'Jl. Malioboro No. 99, Yogyakarta',
                'latitude'     => -7.7956,
                'longitude'    => 110.3695,
                'survey_date'  => Carbon::now()->addDays(2),
                'notes'        => 'AC kamar tidur, merk Sharp 1 PK. Sudah lama tidak dingin.',
                'status'       => 'requested',
            ]);

            $this->command->info("✅ Survey Request ID: {$survey->id} → status: requested");

            // Notif ke tukang: ada survey request masuk
            $this->createNotification(
                userId: $tukang->id,
                title: 'Survey Request Baru',
                body: "Pelanggan {$customer->name} meminta survey untuk layanan {$service->name}.",
                type: 'survey',
                notifiableType: SurveyRequest::class,
                notifiableId: $survey->id,
                referenceId: $survey->id,
                data: [
                    'survey_id'   => $survey->id,
                    'customer'    => $customer->name,
                    'service'     => $service->name,
                    'address'     => $survey->address,
                    'survey_date' => $survey->survey_date->toDateTimeString(),
                    'action'      => 'survey_requested',
                ],
            );

            // ── 4. Tukang approve survey → status: accepted ────────
            $survey->update(['status' => 'accepted']);
            $this->command->info("✅ Survey → status: accepted (tukang approve)");

            // Notif ke customer: tukang menerima survey
            $this->createNotification(
                userId: $customer->id,
                title: 'Survey Diterima Tukang',
                body: "Tukang {$tukang->name} menerima permintaan survey kamu. Survey dijadwalkan {$survey->survey_date->format('d M Y')}.",
                type: 'survey',
                notifiableType: SurveyRequest::class,
                notifiableId: $survey->id,
                referenceId: $survey->id,
                data: [
                    'survey_id'   => $survey->id,
                    'tukang'      => $tukang->name,
                    'survey_date' => $survey->survey_date->toDateTimeString(),
                    'action'      => 'survey_accepted',
                ],
            );

            // ── 5. Tukang isi estimasi → status: survey_priced ─────
            $surveyFee      = 50000;
            $estimatedPrice = 350000;

            $survey->update([
                'status'          => 'survey_priced',
                'survey_fee'      => $surveyFee,
                'estimated_price' => $estimatedPrice,
                'estimated_days'  => 2,
                'tukang_notes'    => 'Freon sudah habis total, perlu pengisian 1 kg. '
                    . 'Estimasi 2 hari termasuk cek kompressor.',
            ]);

            $this->command->info("✅ Survey → status: survey_priced (biaya: Rp {$surveyFee})");

            // Notif ke customer: tukang sudah isi estimasi
            $this->createNotification(
                userId: $customer->id,
                title: 'Estimasi Harga Tersedia',
                body: "Tukang {$tukang->name} mengisi estimasi harga. "
                    . "Biaya survey: Rp " . number_format($surveyFee, 0, ',', '.')
                    . ", Estimasi pekerjaan: Rp " . number_format($estimatedPrice, 0, ',', '.') . ".",
                type: 'survey',
                notifiableType: SurveyRequest::class,
                notifiableId: $survey->id,
                referenceId: $survey->id,
                data: [
                    'survey_id'       => $survey->id,
                    'survey_fee'      => $surveyFee,
                    'estimated_price' => $estimatedPrice,
                    'estimated_days'  => 2,
                    'tukang_notes'    => $survey->tukang_notes,
                    'action'          => 'survey_priced',
                ],
            );

            // ── 6. Customer approve estimasi → status: approved ────
            $survey->update(['status' => 'approved']);
            $this->command->info("✅ Survey → status: approved (customer setuju)");

            // Notif ke tukang: customer approve estimasi
            $this->createNotification(
                userId: $tukang->id,
                title: 'Estimasi Disetujui Customer',
                body: "Customer {$customer->name} menyetujui estimasi kamu. "
                    . "Menunggu pembayaran survey fee.",
                type: 'survey',
                notifiableType: SurveyRequest::class,
                notifiableId: $survey->id,
                referenceId: $survey->id,
                data: [
                    'survey_id' => $survey->id,
                    'customer'  => $customer->name,
                    'action'    => 'survey_approved',
                ],
            );

            // ── 7. Buat Payment record (pending, siap dibayar) ─────
            $midtransOrderId = 'SURVEY-' . $survey->id . '-' . time();

            $payment = Payment::create([
                'survey_id'         => $survey->id,
                'payable_type'      => SurveyRequest::class,
                'payable_id'        => $survey->id,
                'customer_id'       => $customer->id,
                'user_id'           => $customer->id,
                'type'              => 'survey',
                'method'            => 'transfer',
                'payment_channel'   => 'BCA',
                'bank'              => 'bca',
                'amount'            => $surveyFee,
                'status'            => 'pending',
                'midtrans_order_id' => $midtransOrderId,
                // Simulasi VA sudah dibuat di Midtrans
                'va_number'         => '14403495367878' . rand(100000, 999999),
                'expiry_time'       => Carbon::now()->addDay(),
            ]);

            $this->command->info("✅ Payment ID: {$payment->id}");
            $this->command->info("   midtrans_order_id : {$midtransOrderId}");
            $this->command->info("   va_number         : {$payment->va_number}");
            $this->command->info("   amount            : Rp " . number_format($surveyFee, 0, ',', '.'));

            // Notif ke customer: silakan bayar
            $this->createNotification(
                userId: $customer->id,
                title: 'Selesaikan Pembayaran Survey',
                body: "Silakan bayar biaya survey sebesar Rp "
                    . number_format($surveyFee, 0, ',', '.')
                    . " via Virtual Account BCA: {$payment->va_number}.",
                type: 'payment',
                notifiableType: Payment::class,
                notifiableId: $payment->id,
                referenceId: $survey->id,
                data: [
                    'survey_id'         => $survey->id,
                    'payment_id'        => $payment->id,
                    'midtrans_order_id' => $midtransOrderId,
                    'va_number'         => $payment->va_number,
                    'bank'              => 'BCA',
                    'amount'            => $surveyFee,
                    'expired_at'        => $payment->expiry_time->toDateTimeString(),
                    'action'            => 'payment_pending',
                ],
            );

            DB::commit();

            // ── Ringkasan ──────────────────────────────────────────
            $this->command->newLine();
            $this->command->info('══════════════════════════════════════════');
            $this->command->info('  SEEDER SELESAI — Ringkasan');
            $this->command->info('══════════════════════════════════════════');
            $this->command->info("  Survey ID      : {$survey->id}");
            $this->command->info("  Payment ID     : {$payment->id}");
            $this->command->info("  Order Midtrans : {$midtransOrderId}");
            $this->command->info("  VA Number      : {$payment->va_number}");
            $this->command->info("  Amount         : Rp " . number_format($surveyFee, 0, ',', '.'));
            $this->command->newLine();
            $this->command->info('  Login sebagai customer:');
            $this->command->info("    Email    : {$customer->email}");
            $this->command->info('    Password : password');
            $this->command->newLine();
            $this->command->info('  Login sebagai tukang:');
            $this->command->info("    Email    : {$tukang->email}");
            $this->command->info('    Password : password');
            $this->command->info('══════════════════════════════════════════');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Seeder gagal: ' . $e->getMessage());
            Log::error('SurveyPaymentSeeder error: ' . $e->getMessage());
            throw $e;
        }
    }

    // ── Helper: buat notifikasi di KEDUA tabel ─────────────────
    // (notifications & user_notifications agar semua listener dapat)
    private function createNotification(
        int $userId,
        string $title,
        string $body,
        string $type,
        string $notifiableType,
        int $notifiableId,
        int $referenceId,
        array $data = [],
    ): void {
        // Tabel `notifications`
        DB::table('notifications')->insert([
            'user_id'         => $userId,
            'title'           => $title,
            'body'            => $body,
            'type'            => $type,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
            'reference_id'    => $referenceId,
            'data'            => json_encode($data),
            'is_read'         => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Tabel `user_notifications`
        DB::table('user_notifications')->insert([
            'user_id'         => $userId,
            'title'           => $title,
            'body'            => $body,
            'type'            => $type,
            'notifiable_type' => $notifiableType,
            'notifiable_id'   => $notifiableId,
            'data'            => json_encode($data),
            'is_read'         => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    // ── Helper: cari atau buat user ────────────────────────────
    private function findOrCreateUser(array $attributes): User
    {
        return User::firstOrCreate(
            ['email' => $attributes['email']],
            $attributes,
        );
    }
}
