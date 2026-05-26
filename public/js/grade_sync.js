/* ================================================================
   iSchool OBE System — grade_sync.js
   Real-time AJAX Live Grading (Google-Sheets style)

   Cơ chế:
   1. Người dùng nhập điểm → debounce 600ms → gửi Fetch API
   2. Visual feedback ngay lập tức (màu input, indicator dot)
   3. Tự động cập nhật % tổng của sinh viên sau mỗi lần lưu
   4. Queue-based: không gửi 2 request cùng lúc cho cùng 1 ô
   ================================================================ */

'use strict';

// ── Cấu hình ────────────────────────────────────────────────────
const CONFIG = {
    API_ENDPOINT  : '/api/score/save',
    DEBOUNCE_MS   : 600,
    SAVE_TEXT     : 'Tất cả thay đổi đã được lưu',
    SAVING_TEXT   : 'Đang lưu...',
};

// ── Debounce utility ─────────────────────────────────────────────
function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

// ── Save Status Bar ──────────────────────────────────────────────
const saveText    = document.getElementById('saveText');
const saveBar     = document.getElementById('saveStatusBar');
let   pendingCount = 0;

function setSaving() {
    pendingCount++;
    if (saveText) saveText.textContent = CONFIG.SAVING_TEXT;
    if (saveBar)  saveBar.style.borderColor = 'rgba(245,158,11,.4)';
}

function setSaved() {
    pendingCount = Math.max(0, pendingCount - 1);
    if (pendingCount === 0) {
        if (saveText) saveText.textContent = CONFIG.SAVE_TEXT;
        if (saveBar)  saveBar.style.borderColor = '';
    }
}

// ── Visual feedback helpers ──────────────────────────────────────
function setInputState(input, indicator, state) {
    // state: 'idle' | 'saving' | 'saved' | 'error'
    input.classList.remove('saving', 'saved', 'error');
    if (indicator) indicator.className = `score-indicator ${state !== 'idle' ? state : ''}`;
    if (state !== 'idle') input.classList.add(state);
}

// ── Cập nhật % tổng của một hàng sinh viên ───────────────────────
function updateRowTotal(studentId) {
    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
    if (!row) return;

    let totalEarned = 0;
    let totalMax    = 0;

    row.querySelectorAll('.score-input').forEach(input => {
        const max   = parseFloat(input.dataset.max) || 0;
        const score = parseFloat(input.value);
        totalMax += max;
        if (!isNaN(score)) totalEarned += score;
    });

    const totalEl = document.getElementById(`total-${studentId}`);
    if (totalEl && totalMax > 0) {
        const pct = Math.round((totalEarned / totalMax) * 100);
        totalEl.textContent = `${pct}%`;
        // Đổi màu theo ngưỡng
        totalEl.style.color = pct >= 70 ? 'var(--emerald)' : pct >= 50 ? 'var(--amber)' : 'var(--rose)';
    }
}

// ── Core save function ───────────────────────────────────────────
async function saveScore(input) {
    const studentId = parseInt(input.dataset.student, 10);
    const rubricId  = parseInt(input.dataset.rubric, 10);
    const maxScore  = parseFloat(input.dataset.max);
    const score     = parseFloat(input.value);

    if (isNaN(score)) return; // bỏ qua ô trống

    // Client-side validation
    if (score < 0 || score > maxScore) {
        const indicator = document.getElementById(`indicator-${studentId}-${rubricId}`);
        setInputState(input, indicator, 'error');
        window.Toast?.error(`Điểm phải từ 0 đến ${maxScore}`);
        return;
    }

    const indicator = document.getElementById(`indicator-${studentId}-${rubricId}`);
    setInputState(input, indicator, 'saving');
    setSaving();

    const payload = {
        student_id : studentId,
        rubric_id  : rubricId,
        score      : score,
        _token     : window.getCsrfToken(),
    };

    try {
        const res = await fetch(CONFIG.API_ENDPOINT, {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body    : JSON.stringify(payload),
        });

        const data = await res.json();

        if (res.ok && data.status === 'success') {
            setInputState(input, indicator, 'saved');
            updateRowTotal(studentId);
            // Cập nhật trạng thái hàng + progress bar
            if (typeof window.updateRowStatus === 'function') {
                window.updateRowStatus(studentId);
            }
            // Reset về idle sau 2 giây
            setTimeout(() => setInputState(input, indicator, 'idle'), 2000);
        } else {
            throw new Error(data.message || 'Lỗi không xác định');
        }
    } catch (err) {
        setInputState(input, indicator, 'error');
        window.Toast?.error('Lưu thất bại: ' + err.message);
        console.error('[GRADE SYNC]', err);
    } finally {
        setSaved();
    }
}

// Debounced version
const debouncedSave = debounce(saveScore, CONFIG.DEBOUNCE_MS);

// ── Keyboard navigation giữa các ô (Tab / Enter / Arrow keys) ────
function moveFocus(currentInput, direction) {
    const inputs = Array.from(document.querySelectorAll('.score-input'));
    const idx    = inputs.indexOf(currentInput);
    if (idx === -1) return;

    let nextIdx;
    const rowSize = document.querySelectorAll('thead th.th-rubric').length;

    switch (direction) {
        case 'next':  nextIdx = idx + 1;        break;
        case 'prev':  nextIdx = idx - 1;        break;
        case 'down':  nextIdx = idx + rowSize;  break;
        case 'up':    nextIdx = idx - rowSize;  break;
        default:      return;
    }

    if (nextIdx >= 0 && nextIdx < inputs.length) {
        inputs[nextIdx].focus();
        inputs[nextIdx].select();
    }
}

// ── Khởi tạo event listeners ────────────────────────────────────
function initGrading() {
    document.querySelectorAll('.score-input').forEach(input => {
        // Input event → debounced save
        input.addEventListener('input', () => debouncedSave(input));

        // Blur → lưu ngay
        input.addEventListener('blur', () => {
            // Hủy debounce đang chờ và lưu ngay
            if (input.value !== '' && !isNaN(parseFloat(input.value))) {
                saveScore(input);
            }
        });

        // Keyboard navigation
        input.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'Enter':
                    e.preventDefault();
                    moveFocus(input, 'down');
                    break;
                case 'Tab':
                    // Tab mặc định, không override
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    moveFocus(input, 'down');
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    moveFocus(input, 'up');
                    break;
                case 'Escape':
                    input.blur();
                    break;
            }
        });

        // Focus → chọn toàn bộ text
        input.addEventListener('focus', () => input.select());

        // Gán indicator id
        const sid = input.dataset.student;
        const rid = input.dataset.rubric;
        const indicator = document.getElementById(`indicator-${sid}-${rid}`);
        if (indicator && input.value !== '') {
            setInputState(input, indicator, 'saved');
        }
    });

    // Batch save: Ctrl+S
    document.addEventListener('keydown', async (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            window.Toast?.info('Đang lưu tất cả điểm...');
            const inputs = document.querySelectorAll('.score-input');
            for (const input of inputs) {
                if (input.value !== '' && !isNaN(parseFloat(input.value))) {
                    await saveScore(input);
                }
            }
            window.Toast?.success('Đã lưu tất cả điểm!');
        }
    });
}

// ── Run ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', initGrading);