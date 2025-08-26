const hamburger1 = document.getElementById('hamburger');
const nav1 = document.getElementById('nav');

hamburger1.addEventListener('click', () => {
    nav1.classList.toggle('active');
});


document.querySelectorAll('.nav a').forEach(link => {
    link.addEventListener('click', () => nav1.classList.remove('active'));
});





const hamburger = document.getElementById('hamburger');
const nav = document.getElementById('nav');
const mq = window.matchMedia('(max-width: 820px)');
function applyNav() {
    if (mq.matches) {
        hamburger.style.display = 'inline-flex';
        nav.style.display = 'none';
    } else {
        hamburger.style.display = 'none';
        nav.style.display = 'flex';
    }
}
applyNav();
window.addEventListener('resize', applyNav);
hamburger?.addEventListener('click', () => {
    nav.style.display = nav.style.display === 'none' ? 'flex' : 'none';
});


document.getElementById('career-link').addEventListener('click', (e) => {
    e.preventDefault(); alert('We\'re crafting opportunities — check back soon!');
});


const form = document.getElementById('reg-form');
const toast = document.getElementById('toast');
const toastClose = document.getElementById('toast-close');

function showToast() { toast.style.display = 'flex'; setTimeout(() => toast.style.display = 'none', 3000); }
toastClose.addEventListener('click', () => toast.style.display = 'none');

function setError(el, isError) { el.classList.toggle('error', !!isError); }

form.addEventListener('submit', (e) => {
    let valid = true;
    const requiredControls = form.querySelectorAll('input[required], select[required]');
    requiredControls.forEach(el => {
        const empty = !el.value || el.value === '' || el.value === undefined;
        const invalid = el.matches('input[pattern]') && el.value && !(new RegExp(el.getAttribute('pattern')).test(el.value));
        const isError = empty || invalid;
        setError(el, isError);
        if (isError) valid = false;
    });
    if (!valid) { e.preventDefault(); alert('Please complete all fields correctly.'); return; }

    showToast();
});

document.getElementById('cancel-btn').addEventListener('click', () => {
    if (confirm('Cancel registration?')) window.location.href = 'home.php';
});


const config = {
    cUrl: 'https://api.countrystatecity.in/v1/countries',
    ckey: 'VXhJMXhKQVRPZEVlV3VJWTQ0UVVOOUNIemlocmpPUDNhQklRS1UzQg=='
};
const countrySelect = document.querySelector('#country'),
    stateSelect = document.querySelector('#state'),
    citySelect = document.querySelector('#city');

function resetSelect(sel, placeholder) { sel.innerHTML = `<option value="" disabled selected>${placeholder}</option>`; }
function disable(sel, state) { sel.disabled = state; sel.style.pointerEvents = state ? 'none' : 'auto'; }

async function loadCountries() {
    try {
        const res = await fetch(config.cUrl, { headers: { 'X-CSCAPI-KEY': config.ckey } });
        const data = await res.json();
        resetSelect(countrySelect, 'Select Country');
        data.forEach(c => { const o = document.createElement('option'); o.value = c.iso2; o.textContent = c.name; countrySelect.appendChild(o); });
    } catch (err) { console.error('Countries load error', err); }
    disable(stateSelect, true); disable(citySelect, true);
}

async function loadStates(countryCode) {
    try {
        const res = await fetch(`${config.cUrl}/${countryCode}/states`, { headers: { 'X-CSCAPI-KEY': config.ckey } });
        const data = await res.json();
        resetSelect(stateSelect, 'Select State');
        data.forEach(s => { const o = document.createElement('option'); o.value = s.iso2; o.textContent = s.name; stateSelect.appendChild(o); });
        disable(stateSelect, false); disable(citySelect, true); resetSelect(citySelect, 'Select City');
    } catch (err) { console.error('States load error', err); }
}

async function loadCities(countryCode, stateCode) {
    try {
        const res = await fetch(`${config.cUrl}/${countryCode}/states/${stateCode}/cities`, { headers: { 'X-CSCAPI-KEY': config.ckey } });
        const data = await res.json();
        resetSelect(citySelect, 'Select City');
        data.forEach(ct => { const o = document.createElement('option'); o.value = ct.name; o.textContent = ct.name; citySelect.appendChild(o); });
        disable(citySelect, false);
    } catch (err) { console.error('Cities load error', err); }
}

countrySelect.addEventListener('change', (e) => loadStates(e.target.value));
stateSelect.addEventListener('change', () => loadCities(countrySelect.value, stateSelect.value));

window.addEventListener('load', loadCountries);