const hamburger1 = document.getElementById('hamburger');
const nav1 = document.getElementById('nav');

hamburger1.addEventListener('click', () => {
    nav1.classList.toggle('active');
});


document.querySelectorAll('.nav a').forEach(link => {
    link.addEventListener('click', () => nav1.classList.remove('active'));
});



document.getElementById('career-link').addEventListener('click', (e) => {
    e.preventDefault(); alert('We\'re crafting opportunities — check back soon!');
});


const video = document.getElementById('preview');
const startBtn = document.getElementById('startCam');
const switchBtn = document.getElementById('switchCam');
const stopBtn = document.getElementById('stopScan');
const fileInput = document.getElementById('fileinput');
const uploadBtn = document.getElementById('uploadBtn');
const imgEl = document.getElementById('uploaded');
const resultCard = document.getElementById('resultCard');
const qrResult = document.getElementById('qrResult');


let scanner = null;
let cameras = [];
let activeIndex = 0;

function show(el) { el.classList.remove('hidden'); }
function hide(el) { el.classList.add('hidden'); }
function setResult(text) {
    resultCard.classList.remove('hidden');
    const isUrl = /^(https?:\/\/)/i.test(text);
    qrResult.innerHTML = isUrl
        ? `<a href="${text}" target="_blank" rel="noopener">${text}</a>`
        : `<code style="background:#f3f4f6; color:#111827; padding:2px 6px; border-radius:6px;">${text}</code>`;
}

async function initScanner() {
    if (scanner) return scanner;

    scanner = new Instascan.Scanner({ video, scanPeriod: 5, mirror: false, captureImage: true });
    scanner.addListener('scan', function (content, image) {
        if (image) {
            show(imgEl); imgEl.src = image; imgEl.alt = 'Captured frame';
        }
        setResult(content);
        stopCamera();
    });

    try {
        cameras = await Instascan.Camera.getCameras();
        if (!cameras.length) throw new Error('No cameras found');
        switchBtn.disabled = cameras.length <= 1;
    } catch (e) {
        alert('No camera found or permission denied. You can still upload an image.');
    }
    return scanner;
}

async function startCamera(index = 0) {
    await initScanner();
    if (!cameras.length) {
        alert('No camera available.');
        return;
    }

    activeIndex = index % cameras.length;

    try {
        await scanner.start(cameras[activeIndex]);
        show(video);   
        hide(imgEl);   
    } catch (e) {
        console.error(e);
        alert('Unable to start camera. Check permissions.');
    }
}

function stopCamera() {
    if (scanner) {
        scanner.stop();
    }
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    hide(video);  
}



uploadBtn.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const url = URL.createObjectURL(file);
    imgEl.src = url; imgEl.alt = 'Uploaded image';
    show(imgEl); hide(video);

    const formData = new FormData();
    formData.append('file', file);
    try {
        const res = await fetch('https://api.qrserver.com/v1/read-qr-code/', { method: 'POST', body: formData });
        const data = await res.json();
        const content = data?.[0]?.symbol?.[0]?.data;
        if (!content) throw new Error('No QR content found');
        setResult(content);
    } catch (err) {
        console.error(err);
        alert('Could not decode the image. Try a clearer picture.');
    }
});


startBtn.addEventListener('click', () => startCamera(activeIndex));
switchBtn.addEventListener('click', () => {
    if (!cameras.length) return;
    activeIndex = (activeIndex + 1) % cameras.length;
    startCamera(activeIndex);
});
stopBtn.addEventListener('click', stopCamera);