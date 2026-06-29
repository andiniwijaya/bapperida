<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class FormUxStandardizationTest extends TestCase
{
    public function test_crud_form_card_renders_standard_layout_and_form_ux_attributes(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-crud.form-card form-id="demoForm" title="Data Form" description="Deskripsi form.">
                <x-input id="name" name="name" label="Nama Lengkap" required placeholder="Masukkan nama..." />
                <x-slot:footer>
                    <button type="submit">Simpan</button>
                </x-slot:footer>
            </x-crud.form-card>
        BLADE);

        $this->assertStringContainsString('id="demoForm"', $html);
        $this->assertStringContainsString('data-form-ux', $html);
        $this->assertStringContainsString('app-crud-form-card__header', $html);
        $this->assertStringContainsString('app-crud-form-card__body', $html);
        $this->assertStringContainsString('app-crud-form-card__footer', $html);
        $this->assertStringContainsString('Nama Lengkap', $html);
        $this->assertStringContainsString('ds-field-required', $html);
        $this->assertStringContainsString('Masukkan nama...', $html);
    }

    public function test_input_component_renders_tooltip_and_validation_attributes(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-input
                id="priority"
                name="priority"
                label="Prioritas"
                tooltip="Tingkat urgensi surat."
                data-validate="username"
            />
        BLADE);

        $this->assertStringContainsString('data-app-tooltip="Tingkat urgensi surat."', $html);
        $this->assertStringContainsString('data-validate="username"', $html);
        $this->assertStringContainsString('help-circle', $html);
    }

    public function test_file_upload_component_uses_indonesian_copy(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-form.file-upload id="file" name="file" label="Unggah File PDF" accept="application/pdf" hint="Format PDF." />
        BLADE);

        $this->assertStringContainsString('Seret file ke sini', $html);
        $this->assertStringContainsString('pilih file', $html);
        $this->assertStringContainsString('Ukuran maksimal', $html);
        $this->assertStringContainsString('Unggah File PDF', $html);
    }

    public function test_searchable_select_renders_dropdown_markup(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-select id="department_id" name="department_id" label="Bidang" searchable placeholder="Pilih bidang..." />
        BLADE);

        $this->assertStringContainsString('ds-dropdown', $html);
        $this->assertStringContainsString('ds-dropdown__search', $html);
        $this->assertStringContainsString('ds-dropdown__native', $html);
        $this->assertStringContainsString('x-data="dsDropdown', $html);
    }

    public function test_standard_select_renders_native_control(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-select id="status" name="status" label="Status" :options="['active' => 'Aktif']" />
        BLADE);

        $this->assertStringContainsString('ds-select', $html);
        $this->assertStringNotContainsString('ds-dropdown__search', $html);
    }

    public function test_incoming_letter_edit_uses_design_system_form(): void
    {
        $html = view('incoming-letters.edit', ['incomingLetterId' => 1])->render();

        $this->assertStringContainsString('incomingLetterForm', $html);
        $this->assertStringContainsString('app-crud-form-card__header', $html);
        $this->assertStringContainsString('ds-dropdown', $html);
        $this->assertStringContainsString('Bidang Disposisi', $html);
    }

    public function test_auth_login_form_uses_form_ux_and_indonesian_labels(): void
    {
        $html = view('livewire.auth.login')->render();

        $this->assertStringContainsString('data-form-ux', $html);
        $this->assertStringContainsString('Email atau Nama Pengguna', $html);
        $this->assertStringContainsString('Masukkan kata sandi...', $html);
    }

    public function test_empty_state_component_renders_design_system_markup(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-empty-state data-empty-page="incoming-letters" icon="inbox" title="Belum ada data." description="Deskripsi." />
        BLADE);

        $this->assertStringContainsString('ds-empty-state', $html);
        $this->assertStringContainsString('data-empty-page="incoming-letters"', $html);
        $this->assertStringContainsString('data-empty-state-title', $html);
        $this->assertStringContainsString('data-lucide="inbox"', $html);
    }
}
