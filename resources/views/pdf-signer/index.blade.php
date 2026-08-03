<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Tanda Tangan PDF - iLovePDF Style</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.20/dist/interact.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f5f6f7;
            margin: 0; padding: 0;
            display: flex; flex-direction: column;
            height: 100vh; overflow: hidden;
        }
        .navbar {
            background-color: #fff;
            border-bottom: 1px solid #e1e3e6;
            padding: 10px 20px;
            display: flex; justify-content: space-between; align-items: center;
            height: 60px;
        }
        .navbar-brand {
            font-size: 20px; font-weight: bold; color: #333;
            display: flex; align-items: center; gap: 10px;
        }
        .navbar-brand span { color: #e5322d; }

        .workspace {
            display: flex; flex: 1;
            height: calc(100vh - 60px); overflow: hidden;
        }

        .left-pane {
            flex: 1; background-color: #e9ecef;
            display: flex; flex-direction: column;
            position: relative; overflow-y: auto;
        }
        .pdf-toolbar {
            background-color: #fff; border-bottom: 1px solid #ddd;
            padding: 10px 20px; display: flex; align-items: center; gap: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); z-index: 5;
        }
        .pdf-toolbar button {
            background: #fff; border: 1px solid #ccc; border-radius: 4px;
            padding: 5px 10px; cursor: pointer; font-weight: bold;
        }
        .pdf-toolbar button:hover:not(:disabled) { background-color: #f0f0f0; }
        .pdf-toolbar button:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .pdf-viewer-area {
            flex: 1; display: flex; justify-content: center; align-items: flex-start;
            padding: 30px; position: relative; overflow: auto;
        }
        #pdfContainer {
            position: relative; display: inline-block;
            background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        #pdfPreviewCanvas { display: block; }
        #draggableSignature {
            position: absolute; top: 0; left: 0;
            border: 2px dashed #e5322d; background-color: rgba(255, 255, 255, 0.4);
            cursor: move; display: none; z-index: 10;
            width: 160px; height: auto; user-select: none; touch-action: none;
        }

        .right-pane {
            width: 400px; background-color: #fff; border-left: 1px solid #e1e3e6;
            display: flex; flex-direction: column; padding: 20px; overflow-y: auto;
        }
        .right-pane h3 {
            font-size: 18px; margin-top: 0; color: #333;
            border-bottom: 1px solid #eee; padding-bottom: 10px;
        }
        .section-label {
            font-size: 14px; font-weight: bold; color: #555; margin: 15px 0 8px 0;
        }
        .upload-box {
            border: 2px dashed #cbd5e1; padding: 15px; border-radius: 6px;
            text-align: center; background: #f8fafc; margin-bottom: 15px;
        }
        .upload-box input { width: 100%; }

        /* Widget Tanda Tangan ala iLovePDF */
        .sig-card {
            border: 1px solid #cbd5e1; border-radius: 8px;
            background: #f8fafc; padding: 12px; display: flex;
            align-items: center; justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            margin-bottom: 15px;
        }
        .sig-preview-box {
            border: 1px dashed #94a3b8; border-radius: 4px; background: white;
            padding: 4px; flex: 1; text-align: center; height: 50px;
            display: flex; align-items: center; justify-content: center;
        }
        .sig-preview-box img { max-height: 40px; max-width: 100%; object-fit: contain; }
        .sig-edit-btn {
            background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px;
            width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #334155; margin-left: 10px; flex-shrink: 0;
        }
        .sig-edit-btn:hover { background: #e2e8f0; }

        .action-footer { margin-top: auto; padding-top: 20px; }
        .btn-process {
            width: 100%; background-color: #e5322d; color: white; border: none;
            padding: 14px; border-radius: 6px; font-size: 16px; font-weight: bold;
            cursor: pointer; box-shadow: 0 4px 6px rgba(229, 50, 45, 0.2);
        }
        .btn-process:hover { background-color: #c8232c; }

        /* Modal Pop-up Tanda Tangan */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); display: none;
            justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content {
            background: white; width: 500px; border-radius: 8px;
            padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex; flex-direction: column;
        }
        .modal-header {
            font-size: 18px; font-weight: bold; margin-bottom: 15px;
            border-bottom: 1px solid #eee; padding-bottom: 10px;
            display: flex; justify-content: space-between; align-items: center;
        }
        #signatureCanvas {
            border: 2px dashed #ccc; border-radius: 4px;
            background-color: #fff; cursor: crosshair;
            width: 100%; height: 180px;
        }
        .modal-footer {
            margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px;
        }
        .btn-modal { padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; }
        .btn-cancel { background: #e2e8f0; color: #334155; }
        .btn-apply { background: #e5322d; color: white; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="navbar-brand">
            <span>iLove</span>PDF <span>|</span> Web Signer
        </div>
        <div style="font-size: 14px; color: #666;">Mode Edit & Tanda Tangan Dokumen</div>
    </div>

    <div class="workspace">
        <div class="left-pane">
            <div class="pdf-toolbar" id="pdfToolbar" style="display: none;">
                <button id="prevPage" onclick="changePage(-1)">&#9650;</button>
                <button id="nextPage" onclick="changePage(1)">&#9660;</button>
                <span id="pageInfo" style="font-size: 14px; font-weight: bold;">Halaman 1 dari 1</span>
                <span id="fileNameDisplay" style="margin-left: auto; font-size: 14px; color: #555;"></span>
            </div>

            <div class="pdf-viewer-area">
                <div id="placeholderText" style="color: #666; margin-top: 50px; font-size: 16px;">
                    📂 Silakan pilih file PDF terlebih dahulu di panel sebelah kanan.
                </div>
                <div id="pdfContainer" style="display: none;">
                    <canvas id="pdfPreviewCanvas"></canvas>
                    <img id="draggableSignature" alt="Tanda Tangan Draggable">
                </div>
            </div>
        </div>

        <div class="right-pane">
            <h3>Pilihan Tanda Tangan</h3>

            <div class="upload-box">
                <div class="section-label" style="margin-top:0;">Pilih Dokumen PDF</div>
                <input type="file" id="pdfFile" accept="application/pdf">
            </div>

            <div class="section-label">Kotak isian wajib diisi</div>
            
            <!-- Widget Tanda Tangan ala iLovePDF -->
            <div class="sig-card">
                    <div class="sig-preview-box" id="sigPreviewContainer">
                        <span id="sigPlaceholderText" style="font-size: 12px; color: #64748b;">Belum ada TTD</span>
                        <img id="previewPreset" alt="Preset TTD" style="display: none;">
                    </div>
                <button class="sig-edit-btn" title="Edit Tanda Tangan" onclick="openSignatureModal()">
                    ✎
                </button>
            </div>

            <div class="action-footer">
                <button class="btn-process" onclick="applySignatureToPDF()">Tanda tangani &rarr;</button>
            </div>
        </div>
    </div>

    <!-- Modal Pop-up Pembuatan Tanda Tangan -->
    <div class="modal-overlay" id="sigModal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Buat / Perbarui Tanda Tangan</span>
                <button onclick="closeSignatureModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">&times;</button>
            </div>
            <div style="font-size: 13px; color: #64748b; margin-bottom: 8px;">Silakan buat tanda tangan Anda di bawah ini:</div>
            <canvas id="signatureCanvas" width="460" height="180"></canvas>
            <div style="margin-top: 8px; text-align: right;">
                <button class="btn-modal btn-cancel" onclick="clearCanvasDraw()" style="background: #fee2e2; color: #991b1b;">Hapus Kanvas</button>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-cancel" onclick="closeSignatureModal()">Batalkan</button>
                <button class="btn-modal btn-apply" onclick="savePresetFromModal()">Terapkan</button>
            </div>
        </div>
    </div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        let pdfDocGlobal = null;
        let pageNumGlobal = 1;
        let totalPagesGlobal = 0;
        let originalPdfBytes = null;
        let signatureDataUrlGlobal = null;
        let renderViewport = null;

        window.addEventListener('DOMContentLoaded', () => {
            const savedSignature = localStorage.getItem('saved_signature');
            if (savedSignature) {
                signatureDataUrlGlobal = savedSignature;
                updateSignaturePreviewUI(savedSignature);
            }
        });

        function openSignatureModal() {
            document.getElementById('sigModal').style.display = 'flex';
            // Reset atau muat ulang canvas modal jika diperlukan
            if (signatureDataUrlGlobal) {
                const img = new Image();
                img.src = signatureDataUrlGlobal;
                img.onload = () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                }
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }

        function closeSignatureModal() {
            document.getElementById('sigModal').style.display = 'none';
        }

        function updateSignaturePreviewUI(dataUrl) {
            const previewImg = document.getElementById('previewPreset');
            const placeholder = document.getElementById('sigPlaceholderText');
            previewImg.src = dataUrl;
            previewImg.style.display = 'block';
            placeholder.style.display = 'none';
        }

        // Event Drawing di Modal Canvas
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);
        canvas.addEventListener('touchstart', handleTouch);
        canvas.addEventListener('touchmove', handleTouch);
        canvas.addEventListener('touchend', stopDrawing);

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 'mousemove', {
                clientX: touch.clientX, clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        }

        function getMousePos(canvas, evt) {
            const rect = canvas.getBoundingClientRect();
            return { x: evt.clientX - rect.left, y: evt.clientY - rect.top };
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getMousePos(canvas, e);
            ctx.beginPath(); ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getMousePos(canvas, e);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#000000'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
        }

        function stopDrawing() { isDrawing = false; }
        function clearCanvasDraw() { ctx.clearRect(0, 0, canvas.width, canvas.height); }

        function savePresetFromModal() {
            const blank = document.createElement('canvas');
            blank.width = canvas.width; blank.height = canvas.height;
            if (canvas.toDataURL() === blank.toDataURL()) {
                alert('Tanda tangan masih kosong!');
                return;
            }

            signatureDataUrlGlobal = canvas.toDataURL('image/png');
            localStorage.setItem('saved_signature', signatureDataUrlGlobal);
            
            updateSignaturePreviewUI(signatureDataUrlGlobal);
            closeSignatureModal();

            if (pdfDocGlobal) {
                initializeDraggableSignature(true); // true = auto letakkan di tengah
            }
        }

        document.getElementById('pdfFile').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            document.getElementById('fileNameDisplay').innerText = file.name;

            const rawBuffer = await file.arrayBuffer();
            originalPdfBytes = rawBuffer;
            
            const pdfjsBuffer = rawBuffer.slice(0);
            const loadingTask = pdfjsLib.getDocument({ data: pdfjsBuffer });
            pdfDocGlobal = await loadingTask.promise;
            totalPagesGlobal = pdfDocGlobal.numPages;
            pageNumGlobal = 1;

            document.getElementById('placeholderText').style.display = 'none';
            document.getElementById('pdfToolbar').style.display = 'flex';
            document.getElementById('pdfContainer').style.display = 'inline-block';

            await renderPage(pageNumGlobal);
            if (signatureDataUrlGlobal) {
                initializeDraggableSignature(true);
            }
        });

        async function renderPage(num) {
            const page = await pdfDocGlobal.getPage(num);
            const scale = 1.2; 
            renderViewport = page.getViewport({ scale: scale });

            const previewCanvas = document.getElementById('pdfPreviewCanvas');
            const pCtx = previewCanvas.getContext('2d');
            previewCanvas.height = renderViewport.height;
            previewCanvas.width = renderViewport.width;

            await page.render({ canvasContext: pCtx, viewport: renderViewport }).promise;

            document.getElementById('pageInfo').innerText = `Halaman ${num} dari ${totalPagesGlobal}`;
            document.getElementById('prevPage').disabled = (num <= 1);
            document.getElementById('nextPage').disabled = (num >= totalPagesGlobal);

            if (signatureDataUrlGlobal) {
                initializeDraggableSignature(false); // Jangan reset ke tengah jika sekadar pindah halaman
            }
        }

        async function changePage(step) {
            pageNumGlobal += step;
            if (pageNumGlobal < 1) pageNumGlobal = 1;
            if (pageNumGlobal > totalPagesGlobal) pageNumGlobal = totalPagesGlobal;
            await renderPage(pageNumGlobal);
        }

        const draggableSig = document.getElementById('draggableSignature');
        let isInteractInitialized = false;

        function initializeDraggableSignature(centerIt = false) {
            if (!signatureDataUrlGlobal) return;
            
            draggableSig.src = signatureDataUrlGlobal;
            draggableSig.style.display = 'block';

            const previewCanvas = document.getElementById('pdfPreviewCanvas');

            if (centerIt) {
                // Hitung posisi tengah container preview PDF
                const cWidth = previewCanvas.clientWidth;
                const cHeight = previewCanvas.clientHeight;
                const sigWidth = 160;
                const sigHeight = 60; // Estimasi tinggi awal

                const centerX = (cWidth - sigWidth) / 2;
                const centerY = (cHeight - sigHeight) / 2;

                draggableSig.style.transform = `translate(${centerX}px, ${centerY}px)`;
                draggableSig.setAttribute('data-x', centerX);
                draggableSig.setAttribute('data-y', centerY);
            }

            if (!isInteractInitialized) {
                interact(draggableSig).draggable({
                    inertia: true,
                    modifiers: [
                        interact.modifiers.restrictRect({ restriction: 'parent', endOnly: true })
                    ],
                    listeners: {
                        move(event) {
                            const target = event.target;
                            const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                            const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                            target.style.transform = `translate(${x}px, ${y}px)`;
                            target.setAttribute('data-x', x);
                            target.setAttribute('data-y', y);
                        }
                    }
                }).resizable({
                    edges: { left: true, right: true, bottom: true, top: true },
                    modifiers: [
                        interact.modifiers.restrictSize({ min: { width: 50, height: 30 } }),
                        interact.modifiers.restrictRect({ restriction: 'parent' })
                    ],
                    listeners: {
                        move(event) {
                            let target = event.target;
                            let x = (parseFloat(target.getAttribute('data-x')) || 0);
                            let y = (parseFloat(target.getAttribute('data-y')) || 0);

                            target.style.width = event.rect.width + 'px';
                            target.style.height = event.rect.height + 'px';

                            x += event.deltaRect.left;
                            y += event.deltaRect.top;

                            target.style.transform = `translate(${x}px, ${y}px)`;
                            target.setAttribute('data-x', x);
                            target.setAttribute('data-y', y);
                        }
                    }
                });
                isInteractInitialized = true;
            }
        }

        async function applySignatureToPDF() {
            if (!originalPdfBytes) {
                alert('Silakan pilih file PDF terlebih dahulu!');
                return;
            }
            if (draggableSig.style.display === 'none' || !signatureDataUrlGlobal) {
                alert('Silakan buat tanda tangan terlebih dahulu melalui ikon pensil!');
                return;
            }

            try {
                const signatureImageBytes = await fetch(signatureDataUrlGlobal).then(res => res.arrayBuffer());

                const pdfDoc = await PDFLib.PDFDocument.load(originalPdfBytes);
                const pngImage = await pdfDoc.embedPng(signatureImageBytes);

                const pages = pdfDoc.getPages();
                const targetPage = pages[pageNumGlobal - 1];
                const { width: pdfPageWidth, height: pdfPageHeight } = targetPage.getSize();

                const previewCanvas = document.getElementById('pdfPreviewCanvas');
                const canvasDisplayWidth = previewCanvas.clientWidth;
                const canvasDisplayHeight = previewCanvas.clientHeight;

                const scaleX = pdfPageWidth / canvasDisplayWidth;
                const scaleY = pdfPageHeight / canvasDisplayHeight;

                const visualX = parseFloat(draggableSig.getAttribute('data-x')) || 0;
                const visualY = parseFloat(draggableSig.getAttribute('data-y')) || 0;
                const visualWidth = draggableSig.clientWidth;
                const visualHeight = draggableSig.clientHeight;

                const pdfX = visualX * scaleX;
                const pdfWidth = visualWidth * scaleX;
                const pdfHeight = visualHeight * scaleY;
                const pdfY = pdfPageHeight - (visualY * scaleY) - pdfHeight;

                targetPage.drawImage(pngImage, {
                    x: pdfX, y: pdfY, width: pdfWidth, height: pdfHeight,
                });

                const modifiedPdfBytes = await pdfDoc.save();

                const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `dokumen-tertanda-tangan-hal-${pageNumGlobal}.pdf`;
                link.click();

                alert(`Tanda tangan berhasil ditempel di Halaman ${pageNumGlobal}!`);
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat memproses PDF: ' + error.message);
            }
        }
    </script>

</body>
</html>