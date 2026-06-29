<?php

namespace App\Support;

/**
 * Column definitions for report PDF/Excel exports (full database fields).
 */
final class ReportExportSchema
{
    /**
     * @return array<int, array{key: string, label: string}>
     */
    public static function columns(string $reportType): array
    {
        return match ($reportType) {
            'registration' => self::registrationColumns(),
            'incoming' => self::incomingColumns(),
            'outgoing' => self::outgoingColumns(),
            default => self::allColumns(),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function headings(string $reportType): array
    {
        return array_column(self::columns($reportType), 'label');
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, mixed>
     */
    public static function valuesForRow(string $reportType, array $row): array
    {
        $values = [];

        foreach (self::columns($reportType) as $column) {
            $values[] = $row[$column['key']] ?? '-';
        }

        return $values;
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private static function registrationColumns(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'index_code', 'label' => 'Kode Indeks'],
            ['key' => 'letter_code', 'label' => 'Kode Surat'],
            ['key' => 'sequence_number', 'label' => 'Nomor Urut'],
            ['key' => 'year', 'label' => 'Tahun'],
            ['key' => 'letter_number', 'label' => 'Nomor Surat'],
            ['key' => 'subject', 'label' => 'Perihal'],
            ['key' => 'summary', 'label' => 'Isi Ringkas'],
            ['key' => 'recipient', 'label' => 'Tujuan'],
            ['key' => 'letter_date', 'label' => 'Tanggal Surat'],
            ['key' => 'letter_type_label', 'label' => 'Jenis Surat'],
            ['key' => 'attachment', 'label' => 'Lampiran'],
            ['key' => 'notes', 'label' => 'Catatan'],
            ['key' => 'status_label', 'label' => 'Status'],
            ['key' => 'department', 'label' => 'Bidang'],
            ['key' => 'department_id', 'label' => 'ID Bidang'],
            ['key' => 'created_by', 'label' => 'Dibuat Oleh'],
            ['key' => 'updated_by', 'label' => 'Diperbarui Oleh'],
            ['key' => 'deleted_by', 'label' => 'Dihapus Oleh'],
            ['key' => 'created_at', 'label' => 'Dibuat Pada'],
            ['key' => 'updated_at', 'label' => 'Diperbarui Pada'],
            ['key' => 'deleted_at', 'label' => 'Dihapus Pada'],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private static function incomingColumns(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'letter_number', 'label' => 'Nomor Surat'],
            ['key' => 'sent_date', 'label' => 'Tanggal Surat'],
            ['key' => 'received_date', 'label' => 'Tanggal Diterima'],
            ['key' => 'disposition_date', 'label' => 'Tanggal Disposisi'],
            ['key' => 'sender', 'label' => 'Pengirim'],
            ['key' => 'department', 'label' => 'Bidang'],
            ['key' => 'department_id', 'label' => 'ID Bidang'],
            ['key' => 'disposition_department', 'label' => 'Bidang Disposisi'],
            ['key' => 'disposition_department_id', 'label' => 'ID Bidang Disposisi'],
            ['key' => 'subject', 'label' => 'Perihal'],
            ['key' => 'agenda_name', 'label' => 'Nama Agenda'],
            ['key' => 'summary', 'label' => 'Isi Ringkas'],
            ['key' => 'letter_type_label', 'label' => 'Jenis Surat'],
            ['key' => 'attachment', 'label' => 'Lampiran'],
            ['key' => 'file_path', 'label' => 'File PDF'],
            ['key' => 'status_label', 'label' => 'Status'],
            ['key' => 'notes', 'label' => 'Catatan'],
            ['key' => 'created_by', 'label' => 'Dibuat Oleh'],
            ['key' => 'updated_by', 'label' => 'Diperbarui Oleh'],
            ['key' => 'deleted_by', 'label' => 'Dihapus Oleh'],
            ['key' => 'created_at', 'label' => 'Dibuat Pada'],
            ['key' => 'updated_at', 'label' => 'Diperbarui Pada'],
            ['key' => 'deleted_at', 'label' => 'Dihapus Pada'],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private static function outgoingColumns(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'letter_number_registration_id', 'label' => 'ID Registrasi'],
            ['key' => 'letter_number', 'label' => 'Nomor Surat'],
            ['key' => 'index_code', 'label' => 'Kode Indeks'],
            ['key' => 'letter_code', 'label' => 'Kode Surat'],
            ['key' => 'sequence_number', 'label' => 'Nomor Urut'],
            ['key' => 'year', 'label' => 'Tahun'],
            ['key' => 'department', 'label' => 'Bidang'],
            ['key' => 'letter_date', 'label' => 'Tanggal Surat'],
            ['key' => 'subject', 'label' => 'Perihal'],
            ['key' => 'summary', 'label' => 'Isi Ringkas'],
            ['key' => 'recipient', 'label' => 'Tujuan'],
            ['key' => 'letter_type_label', 'label' => 'Jenis Surat'],
            ['key' => 'attachment', 'label' => 'Lampiran'],
            ['key' => 'file_path', 'label' => 'File PDF'],
            ['key' => 'status_label', 'label' => 'Status'],
            ['key' => 'notes', 'label' => 'Catatan'],
            ['key' => 'created_by', 'label' => 'Dibuat Oleh'],
            ['key' => 'updated_by', 'label' => 'Diperbarui Oleh'],
            ['key' => 'deleted_by', 'label' => 'Dihapus Oleh'],
            ['key' => 'created_at', 'label' => 'Dibuat Pada'],
            ['key' => 'updated_at', 'label' => 'Diperbarui Pada'],
            ['key' => 'deleted_at', 'label' => 'Dihapus Pada'],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private static function allColumns(): array
    {
        return [
            ['key' => 'type_label', 'label' => 'Jenis Data'],
            ['key' => 'letter_number', 'label' => 'Nomor Surat'],
            ['key' => 'index_code', 'label' => 'Kode Indeks'],
            ['key' => 'letter_code', 'label' => 'Kode Surat'],
            ['key' => 'sequence_number', 'label' => 'Nomor Urut'],
            ['key' => 'year', 'label' => 'Tahun'],
            ['key' => 'date', 'label' => 'Tanggal'],
            ['key' => 'department', 'label' => 'Bidang'],
            ['key' => 'origin_destination', 'label' => 'Asal/Tujuan'],
            ['key' => 'subject', 'label' => 'Perihal'],
            ['key' => 'agenda_name', 'label' => 'Nama Agenda'],
            ['key' => 'summary', 'label' => 'Isi Ringkas'],
            ['key' => 'letter_type_label', 'label' => 'Jenis Surat'],
            ['key' => 'attachment', 'label' => 'Lampiran'],
            ['key' => 'file_name', 'label' => 'File PDF'],
            ['key' => 'status_label', 'label' => 'Status'],
            ['key' => 'notes', 'label' => 'Catatan'],
        ];
    }
}
