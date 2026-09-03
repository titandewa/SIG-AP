<div id="alertModal" class="hidden fixed inset-0 bg-slate-900/50 z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <p id="alertModalMessage" class="text-sm text-slate-700 mb-5"></p>
        <button type="button" onclick="closeAlertModal()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
            Mengerti
        </button>
    </div>
</div>

<script>
    let _alertModalResolve = null;

    function showAlertModal(message) {
        document.getElementById('alertModalMessage').textContent = message;
        document.getElementById('alertModal').classList.remove('hidden');
        return new Promise((resolve) => {
            _alertModalResolve = resolve;
        });
    }
    function closeAlertModal() {
        document.getElementById('alertModal').classList.add('hidden');
        if (_alertModalResolve) {
            _alertModalResolve();
            _alertModalResolve = null;
        }
    }
</script>