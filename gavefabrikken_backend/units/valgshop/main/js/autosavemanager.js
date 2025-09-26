// units/valgshop/main/js/autosavemanager.js

export default class AutoSaveManager {
    constructor() {
        this.idleTimer = null;
        this.countdownTimer = null;
        this.isIdle = false;
        this.lastActivity = Date.now();
        this.autoSaveActive = false;

        // Default konfiguration
        this.config = {
            idleThreshold: 30000,        // 30 sekunder før countdown
            autoSaveInterval: 300000,    // 5 minutter total
            activityEvents: ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click', 'input', 'change'],
            position: 'top-right',       // 'top-right', 'top-left', 'bottom-right', 'bottom-left'
            showDebugInfo: false         // Vis debug information
        };

        // Callbacks
        this.onSaveCallback = null;
        this.onSuccessCallback = null;
        this.onErrorCallback = null;
        this.onIdleStartCallback = null;
        this.onActivityCallback = null;
    }

    /**
     * Initialize AutoSaveManager
     * @param {object} options - Konfiguration og callbacks
     * @param {function} options.onSave - Callback der udfører gem operationen (skal returnere Promise)
     * @param {function} options.onSuccess - Callback ved succesfuld gem (optional)
     * @param {function} options.onError - Callback ved fejl (optional)
     * @param {function} options.onIdleStart - Callback når idle periode starter (optional)
     * @param {function} options.onActivity - Callback ved bruger aktivitet (optional)
     * @param {number} options.idleThreshold - Tid før countdown i ms (optional, default: 30000)
     * @param {number} options.autoSaveInterval - Total tid før autogem i ms (optional, default: 300000)
     * @param {string} options.position - Position af status element (optional, default: 'top-right')
     * @param {boolean} options.showDebugInfo - Vis debug info (optional, default: false)
     */
    init(options = {}) {
        // Merge konfiguration
        this.config = { ...this.config, ...options };

        // Set callbacks
        this.onSaveCallback = options.onSave || null;
        this.onSuccessCallback = options.onSuccess || null;
        this.onErrorCallback = options.onError || null;
        this.onIdleStartCallback = options.onIdleStart || null;
        this.onActivityCallback = options.onActivity || null;

        if (!this.onSaveCallback) {
            throw new Error('AutoSaveManager: onSave callback er påkrævet');
        }

        this.render();
        this.bindEvents();
        this.resetIdleTimer();

        if (this.config.showDebugInfo) {
            console.log('AutoSaveManager initialiseret med config:', this.config);
        }
    }

    render() {
        // Fjern eksisterende element
        const existing = document.getElementById('autosave-manager');
        if (existing) {
            existing.remove();
        }

        // Bestem position styling
        const positionStyles = this.getPositionStyles();

        const statusHTML = `
            <div id="autosave-manager" style="
                position: fixed; 
                ${positionStyles}
                background: #f8f9fa; 
                border: 1px solid #dee2e6; 
                border-radius: 6px; 
                padding: 10px 14px; 
                font-size: 13px; 
                z-index: 10000;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                display: none;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 250px;
                transition: all 0.3s ease;
            ">
                <div id="autosave-status-content">
                    <div id="autosave-message" style="display: flex; align-items: center;">
                        <span id="autosave-icon" style="margin-right: 6px;">💾</span>
                        <span id="autosave-text">Autogem aktiv</span>
                    </div>
                    
                    <div id="autosave-countdown" style="display: none;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span>Gemmer om:</span>
                            <span id="countdown-timer" style="font-weight: bold; font-size: 14px;">0</span>
                        </div>
                        <div id="countdown-progress" style="
                            width: 100%; 
                            height: 3px; 
                            background: #e9ecef; 
                            border-radius: 2px; 
                            margin-top: 6px;
                            overflow: hidden;
                        ">
                            <div id="countdown-bar" style="
                                height: 100%; 
                                background: linear-gradient(90deg, #28a745, #ffc107, #dc3545); 
                                border-radius: 2px;
                                transition: width 1s linear;
                                width: 100%;
                            "></div>
                        </div>
                    </div>
                    
                    ${this.config.showDebugInfo ? `
                    <div id="autosave-debug" style="
                        font-size: 10px; 
                        color: #6c757d; 
                        margin-top: 5px; 
                        border-top: 1px solid #dee2e6; 
                        padding-top: 5px;
                    ">
                        Debug info
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', statusHTML);
    }

    getPositionStyles() {
        const positions = {
            'top-right': 'top: 15px; right: 15px;',
            'top-left': 'top: 15px; left: 15px;',
            'bottom-right': 'bottom: 15px; right: 15px;',
            'bottom-left': 'bottom: 15px; left: 15px;'
        };

        return positions[this.config.position] || positions['top-right'];
    }

    bindEvents() {
        // Lyt efter bruger aktivitet
        this.config.activityEvents.forEach(event => {
            document.addEventListener(event, () => {
                this.handleActivity();
            }, true);
        });

        // Pause ved focus lost (optional)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseTimers();
            } else {
                this.resumeTimers();
            }
        });
    }

    handleActivity() {
        this.lastActivity = Date.now();

        if (this.onActivityCallback) {
            this.onActivityCallback();
        }

        if (this.config.showDebugInfo) {
            this.updateDebugInfo();
        }

        this.resetIdleTimer();
    }

    resetIdleTimer() {
        this.isIdle = false;

        // Ryd eksisterende timers
        if (this.idleTimer) {
            clearTimeout(this.idleTimer);
        }
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }

        // Skjul countdown
        this.hideStatus();

        // Start ny idle timer
        this.idleTimer = setTimeout(() => {
            this.startIdleCountdown();
        }, this.config.idleThreshold);
    }

    startIdleCountdown() {
        this.isIdle = true;

        if (this.onIdleStartCallback) {
            this.onIdleStartCallback();
        }

        // Vis countdown
        this.showCountdown();

        let remainingTime = (this.config.autoSaveInterval - this.config.idleThreshold) / 1000;
        const totalCountdownTime = remainingTime;

        this.updateCountdownDisplay(remainingTime, totalCountdownTime);

        // Start countdown timer
        this.countdownTimer = setInterval(() => {
            remainingTime--;

            if (remainingTime <= 0) {
                clearInterval(this.countdownTimer);
                this.performAutoSave();
            } else {
                this.updateCountdownDisplay(remainingTime, totalCountdownTime);
            }
        }, 1000);
    }

    showCountdown() {
        const statusElement = document.getElementById('autosave-manager');
        const countdownElement = document.getElementById('autosave-countdown');
        const messageElement = document.getElementById('autosave-message');

        if (statusElement && countdownElement && messageElement) {
            statusElement.style.display = 'block';
            statusElement.style.background = '#fff3cd';
            statusElement.style.borderColor = '#ffeaa7';
            countdownElement.style.display = 'block';
            messageElement.style.display = 'none';
        }
    }

    hideStatus() {
        const statusElement = document.getElementById('autosave-manager');
        if (statusElement) {
            statusElement.style.display = 'none';
        }
    }

    updateCountdownDisplay(seconds, totalSeconds) {
        const timerElement = document.getElementById('countdown-timer');
        const progressBar = document.getElementById('countdown-bar');

        if (timerElement) {
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            timerElement.textContent = minutes > 0 ? `${minutes}m ${secs}s` : `${secs}s`;
        }

        if (progressBar) {
            const percentage = (seconds / totalSeconds) * 100;
            progressBar.style.width = `${percentage}%`;
        }

        if (this.config.showDebugInfo) {
            this.updateDebugInfo();
        }
    }

    async performAutoSave() {
        try {
            this.showSaveInProgress();

            // Kald gem callback
            const result = await this.onSaveCallback();

            this.showSaveSuccess();

            if (this.onSuccessCallback) {
                this.onSuccessCallback(result);
            }

        } catch (error) {
            console.error('AutoSave fejlede:', error);
            this.showSaveError(error);

            if (this.onErrorCallback) {
                this.onErrorCallback(error);
            }
        }

        // Reset idle timer
        this.resetIdleTimer();
    }

    showSaveInProgress() {
        const statusElement = document.getElementById('autosave-manager');
        const messageElement = document.getElementById('autosave-message');
        const iconElement = document.getElementById('autosave-icon');
        const textElement = document.getElementById('autosave-text');
        const countdownElement = document.getElementById('autosave-countdown');

        if (statusElement && messageElement) {
            statusElement.style.display = 'block';
            statusElement.style.background = '#cce5ff';
            statusElement.style.borderColor = '#99ccff';
            messageElement.style.display = 'block';
            countdownElement.style.display = 'none';

            if (iconElement) iconElement.textContent = '⏳';
            if (textElement) textElement.textContent = 'Gemmer...';
        }
    }

    showSaveSuccess() {
        const statusElement = document.getElementById('autosave-manager');
        const iconElement = document.getElementById('autosave-icon');
        const textElement = document.getElementById('autosave-text');

        if (statusElement) {
            statusElement.style.background = '#d4edda';
            statusElement.style.borderColor = '#c3e6cb';

            if (iconElement) iconElement.textContent = '✅';
            if (textElement) textElement.textContent = `Autogemt ${new Date().toLocaleTimeString('da-DK')}`;

            // Skjul efter 3 sekunder
            setTimeout(() => {
                this.hideStatus();
            }, 3000);
        }
    }

    showSaveError(error) {
        const statusElement = document.getElementById('autosave-manager');
        const iconElement = document.getElementById('autosave-icon');
        const textElement = document.getElementById('autosave-text');

        if (statusElement) {
            statusElement.style.background = '#f8d7da';
            statusElement.style.borderColor = '#f5c6cb';

            if (iconElement) iconElement.textContent = '❌';
            if (textElement) textElement.textContent = 'Autogem fejlede';

            // Skjul efter 5 sekunder
            setTimeout(() => {
                this.hideStatus();
            }, 5000);
        }
    }

    updateDebugInfo() {
        const debugElement = document.getElementById('autosave-debug');
        if (debugElement) {
            const now = Date.now();
            const timeSinceActivity = now - this.lastActivity;
            debugElement.innerHTML = `
                Idle: ${this.isIdle}<br>
                Sidste aktivitet: ${Math.round(timeSinceActivity / 1000)}s siden
            `;
        }
    }

    pauseTimers() {
        if (this.idleTimer) {
            clearTimeout(this.idleTimer);
        }
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
        }
    }

    resumeTimers() {
        if (this.isIdle) {
            // Hvis vi var i idle mode, genstart countdown
            this.startIdleCountdown();
        } else {
            // Ellers genstart idle timer
            this.resetIdleTimer();
        }
    }

    // Public metoder
    trigger() {
        // Manuel trigger af autogem
        this.performAutoSave();
    }

    reset() {
        // Reset alle timers
        this.resetIdleTimer();
    }

    destroy() {
        // Ryd op
        this.pauseTimers();

        const statusElement = document.getElementById('autosave-manager');
        if (statusElement) {
            statusElement.remove();
        }

        // Fjern event listeners
        this.config.activityEvents.forEach(event => {
            document.removeEventListener(event, this.handleActivity, true);
        });
    }

    // Getter for status
    get status() {
        return {
            isIdle: this.isIdle,
            lastActivity: this.lastActivity,
            timeSinceActivity: Date.now() - this.lastActivity
        };
    }
}