<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MySQL Stored Function: fn_hitung_biaya_parkir
     *
     * Menghitung biaya parkir berdasarkan skema tarif yang dikonfigurasi tenant.
     * Mendukung 3 tipe tarif: Per Jam, Flat Harian, Progresif Mal.
     *
     * @param p_id_tarif INT   — ID tarif yang berlaku
     * @param p_durasi_jam INT — Durasi parkir dalam jam (pembulatan ke atas)
     * @return DECIMAL(12,2)   — Total biaya dasar parkir
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared("DROP FUNCTION IF EXISTS fn_hitung_biaya_parkir");

        DB::unprepared("
            CREATE FUNCTION fn_hitung_biaya_parkir(
                p_id_tarif INT,
                p_durasi_jam INT,
                p_jumlah_malam INT
            ) RETURNS DECIMAL(12,2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_tipe_tarif VARCHAR(30);
                DECLARE v_tarif_per_jam DECIMAL(12,2) DEFAULT 0;
                DECLARE v_tarif_jam_pertama DECIMAL(12,2) DEFAULT 0;
                DECLARE v_tarif_berikutnya DECIMAL(12,2) DEFAULT 0;
                DECLARE v_tarif_inap_harian DECIMAL(12,2) DEFAULT 0;
                DECLARE v_total DECIMAL(12,2) DEFAULT 0;
                DECLARE v_durasi INT;
                DECLARE v_sisa_jam INT;
                DECLARE v_malam INT;

                -- Minimal 1 jam
                SET v_durasi = GREATEST(COALESCE(p_durasi_jam, 1), 1);
                SET v_malam = GREATEST(COALESCE(p_jumlah_malam, 0), 0);

                -- Fallback jika caller hanya punya durasi jam >= 24 tapi p_jumlah_malam = 0
                IF v_malam = 0 AND v_durasi >= 24 THEN
                    SET v_malam = FLOOR(v_durasi / 24);
                END IF;

                -- Ambil data tarif
                SELECT tipe_tarif, COALESCE(tarif_per_jam, 0), COALESCE(tarif_jam_pertama, 0),
                       COALESCE(tarif_berikutnya, 0), COALESCE(tarif_inap_harian, 0)
                INTO v_tipe_tarif, v_tarif_per_jam, v_tarif_jam_pertama,
                     v_tarif_berikutnya, v_tarif_inap_harian
                FROM tarif
                WHERE id_tarif = p_id_tarif
                LIMIT 1;

                -- Hitung berdasarkan tipe tarif
                CASE v_tipe_tarif
                    WHEN 'Per Jam' THEN
                        -- Jam pertama: tarif_jam_pertama (atau tarif_per_jam jika 0)
                        -- Jam berikutnya: tarif_berikutnya (atau tarif_per_jam jika 0)
                        IF v_tarif_jam_pertama > 0 THEN
                            SET v_total = v_tarif_jam_pertama;
                            IF v_durasi > 1 THEN
                                SET v_total = v_total + ((v_durasi - 1) * IF(v_tarif_berikutnya > 0, v_tarif_berikutnya, v_tarif_per_jam));
                            END IF;
                        ELSE
                            SET v_total = v_durasi * v_tarif_per_jam;
                        END IF;

                    WHEN 'Flat Harian' THEN
                        -- Jika belum melewati jam 00:00 (v_malam = 0):
                        -- Terapkan tarif flat harian reguler (tarif_per_jam, misal Rp 3.000)
                        -- Jika sudah melewati jam 00:00 (v_malam >= 1):
                        -- Terapkan tarif inap per malam yang dilewati (tarif_inap_harian, misal Rp 10.000/malam)
                        IF v_malam > 0 THEN
                            IF v_tarif_inap_harian > 0 THEN
                                SET v_total = v_malam * v_tarif_inap_harian;
                            ELSE
                                SET v_total = (v_malam + 1) * v_tarif_per_jam;
                            END IF;
                        ELSE
                            SET v_total = v_tarif_per_jam;
                        END IF;

                    WHEN 'Progresif Mal' THEN
                        -- Jam pertama: tarif_jam_pertama
                        -- Jam berikutnya: tarif progresif (tarif_berikutnya naik per kelipatan)
                        IF v_durasi <= 1 THEN
                            SET v_total = IF(v_tarif_jam_pertama > 0, v_tarif_jam_pertama, v_tarif_per_jam);
                        ELSE
                            SET v_total = IF(v_tarif_jam_pertama > 0, v_tarif_jam_pertama, v_tarif_per_jam);
                            SET v_sisa_jam = v_durasi - 1;
                            SET v_total = v_total + (v_sisa_jam * IF(v_tarif_berikutnya > 0, v_tarif_berikutnya, v_tarif_per_jam));
                        END IF;

                    ELSE
                        -- Default fallback: simple per-jam
                        SET v_total = v_durasi * v_tarif_per_jam;
                END CASE;

                RETURN v_total;
            END
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::unprepared("DROP FUNCTION IF EXISTS fn_hitung_biaya_parkir");
    }
};
