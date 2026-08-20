const SignatureEditor = (() => {
    let originalImageData = null;
    let canvas = null;
    let ctx = null;

    function init(canvasId) {
        canvas = document.getElementById(canvasId);
        ctx = canvas.getContext('2d');
    }

    function loadFile(file, onLoaded) {
        const img = new Image();
        const reader = new FileReader();

        reader.onload = (ev) => { img.src = ev.target.result; };
        img.onload = () => {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            originalImageData = ctx.getImageData(0, 0, img.width, img.height);
            if (onLoaded) onLoaded();
        };
        reader.readAsDataURL(file);
    }

    function applyThreshold(threshold) {
        if (!originalImageData) return;

        const imageData = new ImageData(
            new Uint8ClampedArray(originalImageData.data),
            originalImageData.width,
            originalImageData.height
        );
        const data = imageData.data;

        for (let i = 0; i < data.length; i += 4) {
            const brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;
            if (brightness > threshold) {
                data[i + 3] = 0;
            } else {
                const alpha = 255 - brightness;
                data[i] = 0; data[i + 1] = 0; data[i + 2] = 0;
                data[i + 3] = Math.min(255, alpha * 1.5);
            }
        }

        ctx.putImageData(imageData, 0, 0);
    }

    function getBlob(callback) {
        canvas.toBlob((blob) => callback(blob), 'image/png');
    }

    function hasImage() {
        return originalImageData !== null;
    }

    function reset() {
        originalImageData = null;
        if (ctx && canvas) ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    return { init, loadFile, applyThreshold, getBlob, hasImage, reset };
})();