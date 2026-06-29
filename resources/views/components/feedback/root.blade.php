<div
    id="app-feedback-root"
    class="app-feedback"
    hidden
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="app-feedback-title"
>
    <div class="app-feedback__overlay" aria-hidden="true"></div>

    <div class="app-feedback__dialog">
        <div class="app-feedback__card" data-feedback-card>
            {{-- Loading state --}}
            <div class="app-feedback__loading" data-feedback-loading>
                <img
                    src="{{ asset('assets/images/logo-bapperida.png') }}"
                    alt="Logo BAPPERIDA"
                    class="app-feedback__logo"
                    width="56"
                    height="56"
                />
                <p class="app-feedback__loading-message" data-feedback-loading-message>
                    Mohon tunggu...
                </p>
                <div class="app-feedback__progress" aria-hidden="true">
                    <div class="app-feedback__progress-bar" data-feedback-progress-bar></div>
                </div>
            </div>

            {{-- Result state (success / error / warning / info) --}}
            <div class="app-feedback__result" data-feedback-result hidden>
                <div class="app-feedback__result-icon" data-feedback-result-icon aria-hidden="true"></div>
                <h2 id="app-feedback-title" class="app-feedback__result-title" data-feedback-result-title></h2>
                <p class="app-feedback__result-message" data-feedback-result-message></p>
                <button
                    type="button"
                    class="app-feedback__button app-feedback__button--primary"
                    data-feedback-result-close
                    hidden
                >
                    Tutup
                </button>
            </div>

            {{-- Confirmation state --}}
            <div class="app-feedback__confirm" data-feedback-confirm hidden>
                <div class="app-feedback__confirm-icon" aria-hidden="true">
                    <svg class="app-feedback__result-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-10a8 8 0 100 16 8 8 0 000-16z" />
                    </svg>
                </div>
                <h2 class="app-feedback__result-title" data-feedback-confirm-title>Apakah Anda yakin?</h2>
                <p class="app-feedback__confirm-message" data-feedback-confirm-message hidden></p>

                <div class="app-feedback__confirm-input-wrap" data-feedback-confirm-input-wrap hidden>
                    <label class="app-feedback__input-label" data-feedback-confirm-input-label>Alasan</label>
                    <textarea
                        class="app-feedback__input"
                        data-feedback-confirm-input
                        rows="3"
                        placeholder=""
                    ></textarea>
                </div>

                <div class="app-feedback__actions">
                    <button
                        type="button"
                        class="app-feedback__button app-feedback__button--ghost"
                        data-feedback-confirm-cancel
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="app-feedback__button app-feedback__button--danger"
                        data-feedback-confirm-submit
                    >
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
