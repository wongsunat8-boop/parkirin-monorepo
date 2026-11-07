<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_hitung_biaya_parkir;
        ");

        DB::unprepared("
            CREATE FUNCTION fn_hitung_biaya_parkir(
                p_id_tarif INT,
                p_waktu_masuk DATETIME,
                p_waktu_keluar DATETIME
            )
            RETURNS DECIMAL(15,2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_tipe_tarif VARCHAR(20);
                DECLARE v_tarif_per_jam DECIMAL(15,2);
                DECLARE v_tarif_jam_pertama DECIMAL(15,2);
                DECLARE v_tarif_berikutnya DECIMAL(15,2);
                DECLARE v_tarif_inap DECIMAL(15,2);
                DECLARE v_durasi_jam INT;
                DECLARE v_jumlah_inap INT;
                DECLARE v_biaya_total DECIMAL(15,2);

                -- Fetch tariff data
                SELECT tipe_tarif, tarif_per_jam, tarif_jam_pertama, tarif_berikutnya, tarif_inap
                INTO v_tipe_tarif, v_tarif_per_jam, v_tarif_jam_pertama, v_tarif_berikutnya, v_tarif_inap
                FROM tarif
                WHERE id_tarif = p_id_tarif;

                -- Calculate duration in hours (rounded up, minimum 1 hour)
                SET v_durasi_jam = GREATEST(CEIL(TIMESTAMPDIFF(SECOND, p_waktu_masuk, p_waktu_keluar) / 3600), 1);

                -- Calculate overnight stays via calendar date difference
                SET v_jumlah_inap = DATEDIFF(DATE(p_waktu_keluar), DATE(p_waktu_masuk));

                -- Calculate total fee based on tariff type
                IF v_tipe_tarif = 'Per Jam' THEN
                    SET v_biaya_total = (v_durasi_jam * v_tarif_per_jam) + (v_jumlah_inap * v_tarif_inap);

                ELSEIF v_tipe_tarif = 'Flat Harian' THEN
                    SET v_biaya_total = v_tarif_per_jam + (v_jumlah_inap * v_tarif_inap);

                ELSEIF v_tipe_tarif = 'Progresif Mal' THEN
                    SET v_biaya_total = v_tarif_jam_pertama + ((v_durasi_jam - 1) * v_tarif_berikutnya) + (v_jumlah_inap * v_tarif_inap);

                ELSE
                    SET v_biaya_total = 0;
                END IF;

                RETURN v_biaya_total;
            END
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("DROP FUNCTION IF EXISTS fn_hitung_biaya_parkir;");
        }
    }
};

