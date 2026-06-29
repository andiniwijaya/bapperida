<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DesignSystemComponentsTest extends TestCase
{
    public function test_core_design_components_render_with_expected_accessibility_markup(): void
    {
        $button = Blade::render('<x-button type="submit" loading>Save</x-button>');
        $this->assertStringContainsString('type="submit"', $button);
        $this->assertStringContainsString('disabled', $button);
        $this->assertStringContainsString('ds-btn', $button);
        $this->assertStringContainsString('ds-btn--primary', $button);
        $this->assertStringContainsString('ds-btn__loading-dots', $button);
        $this->assertStringContainsString('Memproses...', $button);

        $input = Blade::render('<x-input name="email" label="Email" error="Email is required" />');
        $this->assertStringContainsString('aria-invalid', $input);
        $this->assertStringContainsString('Email is required', $input);
        $this->assertStringContainsString('ds-input', $input);

        $textarea = Blade::render('<x-textarea name="message" label="Message" />');
        $this->assertStringContainsString('textarea', $textarea);
        $this->assertStringContainsString('ds-textarea', $textarea);

        $select = Blade::render('<x-select name="department" label="Department" :options="[\'sales\' => \'Sales\']" />');
        $this->assertStringContainsString('select', $select);
        $this->assertStringContainsString('ds-select', $select);

        $checkbox = Blade::render('<x-checkbox name="agree" label="Agree" />');
        $this->assertStringContainsString('checkbox', $checkbox);

        $radio = Blade::render('<x-radio name="role" value="admin" label="Admin" />');
        $this->assertStringContainsString('radio', $radio);

        $switch = Blade::render('<x-switch name="active" label="Active" />');
        $this->assertStringContainsString('role="switch"', $switch);

        $card = Blade::render('<x-card><div>Content</div></x-card>');
        $this->assertStringContainsString('Content', $card);

        $statCard = Blade::render('<x-stat-card title="Open" value="12" />');
        $this->assertStringContainsString('Open', $statCard);

        $table = Blade::render('<x-table><x-slot:head><th>Head</th></x-slot:head><tbody><tr><td>Row</td></tr></tbody></x-table>');
        $this->assertStringContainsString('table', $table);

        $pagination = Blade::render('<x-pagination />');
        $this->assertStringContainsString('Navigasi halaman', $pagination);
        $this->assertStringContainsString('ds-caption', $pagination);

        $modal = Blade::render('<x-modal title="Dialog">Body</x-modal>');
        $this->assertStringContainsString('Dialog', $modal);

        $alert = Blade::render('<x-alert type="success" title="Saved">Saved</x-alert>');
        $this->assertStringContainsString('Saved', $alert);
        $this->assertStringContainsString('ds-alert', $alert);

        $toast = Blade::render('<x-toast message="Saved" />');
        $this->assertStringContainsString('Saved', $toast);
        $this->assertStringContainsString('ds-alert', $toast);

        $badge = Blade::render('<x-badge color="gold">New</x-badge>');
        $this->assertStringContainsString('New', $badge);
        $this->assertStringContainsString('ds-badge', $badge);
        $this->assertStringContainsString('ds-badge--gold', $badge);

        $avatar = Blade::render('<x-avatar name="Ada Lovelace" />');
        $this->assertStringContainsString('Ada', $avatar);

        $dropdown = Blade::render('<x-dropdown><x-slot:trigger><button>Open</button></x-slot:trigger><x-dropdown.item>Item</x-dropdown.item></x-dropdown>');
        $this->assertStringContainsString('Open', $dropdown);

        $pageHeader = Blade::render('<x-page-header title="Inbox" description="Letters" />');
        $this->assertStringContainsString('Inbox', $pageHeader);

        $breadcrumb = Blade::render('<x-breadcrumb :items="[[\'label\' => \'Home\', \'href\' => \'#\']]" />');
        $this->assertStringContainsString('Home', $breadcrumb);

        $emptyState = Blade::render('<x-empty-state title="No results" description="Try again" icon="inbox" />');
        $this->assertStringContainsString('No results', $emptyState);
        $this->assertStringContainsString('ds-empty-state', $emptyState);
        $this->assertStringContainsString('data-lucide="inbox"', $emptyState);

        $loading = Blade::render('<x-loading text="Loading" />');
        $this->assertStringContainsString('Loading', $loading);
        $this->assertStringContainsString('ds-skeleton', $loading);
        $this->assertStringContainsString('aria-busy="true"', $loading);

        $tableSkeleton = Blade::render('<x-skeleton.table :rows="4" :columns="5" />');
        $this->assertStringContainsString('ds-skeleton-table', $tableSkeleton);
        $this->assertStringContainsString('data-skeleton-root', $tableSkeleton);

        $cardSkeleton = Blade::render('<x-skeleton.card :count="2" />');
        $this->assertStringContainsString('ds-skeleton-cards', $cardSkeleton);

        $chartSkeleton = Blade::render('<x-skeleton.chart :count="1" />');
        $this->assertStringContainsString('ds-skeleton-chart', $chartSkeleton);

        $listSkeleton = Blade::render('<x-skeleton.list :count="3" />');
        $this->assertStringContainsString('ds-skeleton-list', $listSkeleton);

        $search = Blade::render('<x-search placeholder="Search" />');
        $this->assertStringContainsString('Search', $search);

        $filterCard = Blade::render('<x-filter-card><div>Filter</div></x-filter-card>');
        $this->assertStringContainsString('Filter', $filterCard);

        $formCard = Blade::render('<x-form-card><div>Form</div></x-form-card>');
        $this->assertStringContainsString('Form', $formCard);

        $sectionHeader = Blade::render('<x-section-header title="Section" description="Detail" />');
        $this->assertStringContainsString('Section', $sectionHeader);
        $this->assertStringContainsString('app-section-header', $sectionHeader);

        $panel = Blade::render('<x-panel title="Panel Title" description="Panel detail"><div>Body</div></x-panel>');
        $this->assertStringContainsString('Panel Title', $panel);
        $this->assertStringContainsString('app-panel__header', $panel);
        $this->assertStringContainsString('Body', $panel);

        $filterPanel = Blade::render('<x-crud.filter-panel title="Filter Pengguna"><div>Fields</div></x-crud.filter-panel>');
        $this->assertStringContainsString('Filter Pengguna', $filterPanel);
        $this->assertStringContainsString('id="reset-filter"', $filterPanel);
        $this->assertStringContainsString('data-app-tooltip="Atur Ulang"', $filterPanel);
        $this->assertStringContainsString('data-lucide="rotate-ccw"', $filterPanel);

        $actionBar = Blade::render('<x-crud.action-bar><x-button type="button">Cetak</x-button></x-crud.action-bar>');
        $this->assertStringContainsString('crud-action-bar', $actionBar);
        $this->assertStringContainsString('Cetak', $actionBar);

        $tablePanel = Blade::render('<x-crud.table-panel title="Daftar Pengguna"><div>Table</div></x-crud.table-panel>');
        $this->assertStringContainsString('Daftar Pengguna', $tablePanel);
        $this->assertStringContainsString('crud-table-panel', $tablePanel);
        $this->assertStringContainsString('crud-table-panel__table-area', $tablePanel);
        $this->assertStringContainsString('Table', $tablePanel);

        $feedback = Blade::render('<x-feedback.root />');
        $this->assertStringContainsString('app-feedback-root', $feedback);

        $fileUpload = Blade::render('<x-file-upload name="attachment" label="Attachment" />');
        $this->assertStringContainsString('Attachment', $fileUpload);

        $confirmDialog = Blade::render('<x-confirm-dialog title="Delete" message="Proceed?" />');
        $this->assertStringContainsString('Delete', $confirmDialog);

        $dashboard = view('dashboard.index', [
            'role' => 'staff',
            'departments' => collect([]),
            'title' => 'Beranda Staff',
            'description' => 'Ringkasan surat dan aktivitas yang Anda kelola.',
            'showDepartmentFilter' => false,
        ])->render();
        $this->assertStringContainsString('Beranda Staff', $dashboard);

        $loginView = view('livewire.auth.login')->render();
        $this->assertStringContainsString('Masuk ke akun Anda', $loginView);
        $this->assertStringContainsString('data-lucide="user"', $loginView);
        $this->assertStringContainsString('data-lucide="lock"', $loginView);
    }
}
