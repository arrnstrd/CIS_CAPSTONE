/**
 * CIS Interactive Product Tour Engine
 * Decoupled, zero-dependency tour engine featuring SVG cutout masking,
 * collision-aware tooltip positioning, keyboard accessibility, and state persistence.
 */
export class TourEngine {
    constructor() {
        this.isActive = false;
        this.currentTour = [];
        this.currentStepIndex = 0;
        this.pageId = null;
        this.overlayEl = null;
        this.highlightEl = null;
        this.popoverEl = null;
        this.resizeObserver = null;
        this._handleKeyDown = this._handleKeyDown.bind(this);
        this._handleReposition = this._handleReposition.bind(this);
    }

    /**
     * Check if a tour has already been completed for the specified page.
     * @param {string} pageId
     * @returns {boolean}
     */
    hasCompletedTour(pageId) {
        if (!pageId) return false;
        return localStorage.getItem(`cis_tour_completed_${pageId}`) === 'true';
    }

    /**
     * Mark a tour as completed in LocalStorage.
     * @param {string} pageId
     */
    markTourCompleted(pageId) {
        if (!pageId) return;
        localStorage.setItem(`cis_tour_completed_${pageId}`, 'true');
    }

    /**
     * Start a tour for a specific page.
     * @param {string} pageId
     * @param {Array<{target: string, title?: string, content: string, placement?: string}>} steps
     * @param {boolean} force - If true, bypasses the hasCompletedTour check.
     */
    start(pageId, steps, force = false) {
        if (!steps || !steps.length) return;
        if (!force && this.hasCompletedTour(pageId)) return;

        this.pageId = pageId;
        this.currentTour = steps;
        this.currentStepIndex = 0;
        this.isActive = true;

        this._createDOMElements();
        this._bindGlobalEvents();
        this._renderCurrentStep();
    }

    /**
     * Advance to the next step.
     */
    next() {
        if (!this.isActive) return;
        if (this.currentStepIndex < this.currentTour.length - 1) {
            this.currentStepIndex++;
            this._renderCurrentStep();
        } else {
            this.finish();
        }
    }

    /**
     * Go back to the previous step.
     */
    prev() {
        if (!this.isActive || this.currentStepIndex <= 0) return;
        this.currentStepIndex--;
        this._renderCurrentStep();
    }

    /**
     * Skip the tour and permanently mark it as completed.
     */
    skip() {
        if (this.pageId) {
            this.markTourCompleted(this.pageId);
        }
        this.destroy();
    }

    /**
     * Finish the tour upon completing the last step.
     */
    finish() {
        if (this.pageId) {
            this.markTourCompleted(this.pageId);
        }
        this.destroy();
    }

    /**
     * Tear down all DOM overlays and detach listeners.
     */
    destroy() {
        this.isActive = false;
        this.currentTour = [];
        this.currentStepIndex = 0;

        this._unbindGlobalEvents();

        if (this.overlayEl && this.overlayEl.parentNode) {
            this.overlayEl.parentNode.removeChild(this.overlayEl);
        }
        if (this.highlightEl && this.highlightEl.parentNode) {
            this.highlightEl.parentNode.removeChild(this.highlightEl);
        }
        if (this.popoverEl && this.popoverEl.parentNode) {
            this.popoverEl.parentNode.removeChild(this.popoverEl);
        }

        this.overlayEl = null;
        this.highlightEl = null;
        this.popoverEl = null;
    }

    /**
     * Create the SVG mask backdrop, highlight border, and popover shell.
     * @private
     */
    _createDOMElements() {
        // 1. Overlay with SVG mask
        this.overlayEl = document.createElement('div');
        this.overlayEl.className = 'cis-tour-overlay';
        this.overlayEl.setAttribute('role', 'dialog');
        this.overlayEl.setAttribute('aria-modal', 'true');
        this.overlayEl.setAttribute('aria-label', 'Product Guide');

        this.overlayEl.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <defs>
                    <mask id="cisTourSvgMask">
                        <rect x="0" y="0" width="100%" height="100%" fill="#ffffff" />
                        <rect id="cisTourCutout" x="0" y="0" width="0" height="0" rx="8" ry="8" fill="#000000" />
                    </mask>
                </defs>
                <rect x="0" y="0" width="100%" height="100%" fill="rgba(15, 23, 42, 0.65)" mask="url(#cisTourSvgMask)" />
            </svg>
        `;

        // 2. Highlight accent frame
        this.highlightEl = document.createElement('div');
        this.highlightEl.className = 'cis-tour-highlight-box';

        // 3. Popover card
        this.popoverEl = document.createElement('div');
        this.popoverEl.className = 'cis-tour-popover';

        document.body.appendChild(this.overlayEl);
        document.body.appendChild(this.highlightEl);
        document.body.appendChild(this.popoverEl);
    }

    /**
     * Render the active step. Handles missing elements gracefully.
     * @private
     */
    _renderCurrentStep() {
        const step = this.currentTour[this.currentStepIndex];
        if (!step) {
            this.finish();
            return;
        }

        const targetEl = document.querySelector(step.target);

        // Element Absence Handling: if element not found or invisible, skip or abort gracefully
        if (!targetEl || !this._isElementVisible(targetEl)) {
            console.warn(`[TourEngine] Target element not found or not visible: ${step.target}`);
            if (this.currentStepIndex < this.currentTour.length - 1) {
                this.currentStepIndex++;
                this._renderCurrentStep();
            } else {
                this.finish();
            }
            return;
        }

        // Auto-Scroll element into viewport before calculating bounding rect
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });

        // Allow scroll and layout to settle
        setTimeout(() => {
            if (!this.isActive) return;
            this._updateStepUI(targetEl, step);
        }, 180);
    }

    /**
     * Check if a DOM element is visible in the viewport layout.
     * @private
     */
    _isElementVisible(el) {
        if (!el) return false;
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
            return false;
        }
        const rect = el.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    /**
     * Position the mask, highlight box, and render popover contents.
     * @private
     */
    _updateStepUI(targetEl, step) {
        const rect = targetEl.getBoundingClientRect();
        const padding = 6;

        const x = Math.max(0, rect.left - padding);
        const y = Math.max(0, rect.top - padding);
        const w = rect.width + padding * 2;
        const h = rect.height + padding * 2;

        // 1. Update SVG Cutout Hole
        const cutout = document.getElementById('cisTourCutout');
        if (cutout) {
            cutout.setAttribute('x', x);
            cutout.setAttribute('y', y);
            cutout.setAttribute('width', w);
            cutout.setAttribute('height', h);
        }

        // 2. Update Highlight Box
        if (this.highlightEl) {
            this.highlightEl.style.top = `${y}px`;
            this.highlightEl.style.left = `${x}px`;
            this.highlightEl.style.width = `${w}px`;
            this.highlightEl.style.height = `${h}px`;
        }

        // 3. Render Popover Content
        const isFirst = this.currentStepIndex === 0;
        const isLast = this.currentStepIndex === this.currentTour.length - 1;
        const totalSteps = this.currentTour.length;
        const stepNum = this.currentStepIndex + 1;

        this.popoverEl.innerHTML = `
            <div class="cis-tour-popover__arrow"></div>
            <div class="cis-tour-popover__header">
                <span class="cis-tour-popover__badge">Step ${stepNum} of ${totalSteps}</span>
                <button type="button" class="cis-tour-popover__close-btn" aria-label="Close Tour" id="cisTourCloseBtn">
                    <i class="fa-solid fa-times" aria-hidden="true"></i>
                </button>
            </div>
            ${step.title ? `<h4 class="cis-tour-popover__title">${this._escapeHTML(step.title)}</h4>` : ''}
            <p class="cis-tour-popover__body">${this._escapeHTML(step.content)}</p>
            <div class="cis-tour-popover__footer">
                <button type="button" class="cis-tour-popover__skip-btn" id="cisTourSkipBtn">Skip Tour</button>
                <div class="cis-tour-popover__nav-actions">
                    <button type="button" class="cis-tour-popover__btn cis-tour-popover__btn--back" id="cisTourBackBtn" ${isFirst ? 'disabled' : ''}>
                        Back
                    </button>
                    <button type="button" class="cis-tour-popover__btn cis-tour-popover__btn--primary" id="cisTourNextBtn">
                        ${isLast ? 'Finish' : 'Next'}
                    </button>
                </div>
            </div>
        `;

        // Bind control buttons
        const closeBtn = this.popoverEl.querySelector('#cisTourCloseBtn');
        const skipBtn = this.popoverEl.querySelector('#cisTourSkipBtn');
        const backBtn = this.popoverEl.querySelector('#cisTourBackBtn');
        const nextBtn = this.popoverEl.querySelector('#cisTourNextBtn');

        if (closeBtn) closeBtn.onclick = () => this.skip();
        if (skipBtn) skipBtn.onclick = () => this.skip();
        if (backBtn) backBtn.onclick = () => this.prev();
        if (nextBtn) nextBtn.onclick = () => this.next();

        // 4. Calculate Responsive Positioning
        this._positionPopover(rect, step.placement || 'bottom');
        this.popoverEl.classList.add('cis-tour-popover--visible');
    }

    /**
     * Compute collision-aware popover position (flips top/bottom/left/right).
     * @private
     */
    _positionPopover(targetRect, preferredPlacement) {
        const popoverWidth = this.popoverEl.offsetWidth || 320;
        const popoverHeight = this.popoverEl.offsetHeight || 160;
        const spacing = 14;
        const screenMargin = 16;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        let placement = preferredPlacement;

        // Check vertical flip suitability
        if (placement === 'bottom') {
            const fitsBottom = targetRect.bottom + spacing + popoverHeight <= viewportHeight - screenMargin;
            const fitsTop = targetRect.top - spacing - popoverHeight >= screenMargin;
            if (!fitsBottom && fitsTop) {
                placement = 'top';
            }
        } else if (placement === 'top') {
            const fitsTop = targetRect.top - spacing - popoverHeight >= screenMargin;
            const fitsBottom = targetRect.bottom + spacing + popoverHeight <= viewportHeight - screenMargin;
            if (!fitsTop && fitsBottom) {
                placement = 'bottom';
            }
        }

        // Check horizontal flip suitability
        if (placement === 'right') {
            const fitsRight = targetRect.right + spacing + popoverWidth <= viewportWidth - screenMargin;
            const fitsLeft = targetRect.left - spacing - popoverWidth >= screenMargin;
            if (!fitsRight && fitsLeft) {
                placement = 'left';
            }
        } else if (placement === 'left') {
            const fitsLeft = targetRect.left - spacing - popoverWidth >= screenMargin;
            const fitsRight = targetRect.right + spacing + popoverWidth <= viewportWidth - screenMargin;
            if (!fitsLeft && fitsRight) {
                placement = 'right';
            }
        }

        let top = 0;
        let left = 0;

        switch (placement) {
            case 'top':
                top = targetRect.top - spacing - popoverHeight;
                left = targetRect.left + (targetRect.width / 2) - (popoverWidth / 2);
                break;
            case 'bottom':
                top = targetRect.bottom + spacing;
                left = targetRect.left + (targetRect.width / 2) - (popoverWidth / 2);
                break;
            case 'left':
                top = targetRect.top + (targetRect.height / 2) - (popoverHeight / 2);
                left = targetRect.left - spacing - popoverWidth;
                break;
            case 'right':
                top = targetRect.top + (targetRect.height / 2) - (popoverHeight / 2);
                left = targetRect.right + spacing;
                break;
        }

        // Viewport horizontal constraint (keep within screen margins)
        if (left < screenMargin) {
            left = screenMargin;
        } else if (left + popoverWidth > viewportWidth - screenMargin) {
            left = viewportWidth - popoverWidth - screenMargin;
        }

        // Viewport vertical constraint
        if (top < screenMargin) {
            top = screenMargin;
        } else if (top + popoverHeight > viewportHeight - screenMargin) {
            top = viewportHeight - popoverHeight - screenMargin;
        }

        this.popoverEl.setAttribute('data-placement', placement);
        this.popoverEl.style.top = `${Math.round(top)}px`;
        this.popoverEl.style.left = `${Math.round(left)}px`;

        // Position arrow directly pointing toward target center
        const arrow = this.popoverEl.querySelector('.cis-tour-popover__arrow');
        if (arrow) {
            if (placement === 'top' || placement === 'bottom') {
                const targetCenterX = targetRect.left + targetRect.width / 2;
                const arrowX = Math.max(16, Math.min(popoverWidth - 24, targetCenterX - left - 5));
                arrow.style.left = `${Math.round(arrowX)}px`;
                arrow.style.right = '';
            } else {
                const targetCenterY = targetRect.top + targetRect.height / 2;
                const arrowY = Math.max(16, Math.min(popoverHeight - 24, targetCenterY - top - 5));
                arrow.style.top = `${Math.round(arrowY)}px`;
                arrow.style.bottom = '';
            }
        }
    }

    /**
     * Escape strings to prevent XSS.
     * @private
     */
    _escapeHTML(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Reposition overlay cutout and popover on resize or scroll.
     * @private
     */
    _handleReposition() {
        if (!this.isActive) return;
        const step = this.currentTour[this.currentStepIndex];
        if (!step) return;

        const targetEl = document.querySelector(step.target);
        if (targetEl && this._isElementVisible(targetEl)) {
            const rect = targetEl.getBoundingClientRect();
            const padding = 6;
            const x = Math.max(0, rect.left - padding);
            const y = Math.max(0, rect.top - padding);
            const w = rect.width + padding * 2;
            const h = rect.height + padding * 2;

            const cutout = document.getElementById('cisTourCutout');
            if (cutout) {
                cutout.setAttribute('x', x);
                cutout.setAttribute('y', y);
                cutout.setAttribute('width', w);
                cutout.setAttribute('height', h);
            }

            if (this.highlightEl) {
                this.highlightEl.style.top = `${y}px`;
                this.highlightEl.style.left = `${x}px`;
                this.highlightEl.style.width = `${w}px`;
                this.highlightEl.style.height = `${h}px`;
            }

            this._positionPopover(rect, step.placement || 'bottom');
        }
    }

    /**
     * Handle key presses (Escape, Left, Right).
     * @private
     */
    _handleKeyDown(e) {
        if (!this.isActive) return;
        if (e.key === 'Escape') {
            e.preventDefault();
            this.skip();
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            this.next();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            this.prev();
        }
    }

    /**
     * Bind listeners.
     * @private
     */
    _bindGlobalEvents() {
        window.addEventListener('keydown', this._handleKeyDown);
        window.addEventListener('resize', this._handleReposition);
        window.addEventListener('scroll', this._handleReposition, true);
    }

    /**
     * Remove listeners.
     * @private
     */
    _unbindGlobalEvents() {
        window.removeEventListener('keydown', this._handleKeyDown);
        window.removeEventListener('resize', this._handleReposition);
        window.removeEventListener('scroll', this._handleReposition, true);
    }
}

// Global instance export
export const globalTourEngine = new TourEngine();
