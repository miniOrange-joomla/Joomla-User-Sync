
function back_btn(){
    
    jQuery("#f").submit();
}


function mo_show_tab(tab_id)
{
    jQuery(".mo_boot_sync-tab").css("background",'none');
    jQuery(".mo_boot_sync-tab").css("color",'white');
    jQuery(".mo_sync_tab").css('display','none');
    jQuery("#"+tab_id).css('display','block');
    jQuery("#mo_"+tab_id).css("background",'white');
    jQuery("#mo_"+tab_id).css("color",'black');
    
}

function mo_test_configuration(){
		
    var username = jQuery("#mo_usersync_upn").val();
    var appname = jQuery("#moAppName").val();

    if(username){
        testconfigurl ='index.php?option=com_miniorange_usersync&view=accountsetup&task=accountsetup.moGetClient&username='+btoa(username)+'&appName='+btoa(appname);
        var myWindow = window.open(testconfigurl, 'TEST ATTRIBUTE MAPPING', 'scrollbars=1 width=800, height=800');
    }else{
        alert("Please enter username to see what attributes are retrieved by entered username");
    }
    var timer = setInterval(function() {   
        if(myWindow.closed) {  
            clearInterval(timer);  
            location.reload();
        }  
    }, 1); 
}

document.addEventListener('DOMContentLoaded', function () {

    // Support form: capture browser timezone into hidden fields if present
    (function setClientTimezoneFields() {
        const tzEl = document.getElementById('moClientTimezone');
        const offsetEl = document.getElementById('moClientTimezoneOffset');
        if (!tzEl && !offsetEl) {
            return;
        }

        let tzName = '';
        try {
            tzName = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            tzName = '';
        }

        const offsetMinutes = new Date().getTimezoneOffset(); // minutes behind UTC
        if (tzEl) tzEl.value = tzName;
        if (offsetEl) offsetEl.value = String(offsetMinutes);
    })();

    const list = document.getElementById('countryList');
    const select = document.getElementById('countrySelect');
    const hiddenInput = document.getElementById('countryCode');

    // If the current page doesn't have the phone dropdown, do nothing.
    if (!list || !select || !hiddenInput) {
        return;
    }

    // countries is defined in assets/js/countries.js
    if (typeof countries === 'undefined' || !Array.isArray(countries)) {
        // Avoid breaking the page if countries.js wasn't loaded for some reason.
        return;
    }

    function getFlagEmoji(countryCode) {
        if (!countryCode || typeof countryCode !== 'string' || countryCode.length !== 2) {
            return '';
        }
        const code = countryCode.toUpperCase();
        const A = 65;
        const REGIONAL_INDICATOR_A = 0x1F1E6; // 🇦
        const first = code.charCodeAt(0) - A + REGIONAL_INDICATOR_A;
        const second = code.charCodeAt(1) - A + REGIONAL_INDICATOR_A;
        try {
            return String.fromCodePoint(first, second);
        } catch (e) {
            return '';
        }
    }

    function setSelectedCountry(country) {
        const flagEl = select.querySelector('.flag');
        const dialEl = select.querySelector('.dial-code');
        if (!flagEl || !dialEl) {
            return;
        }

        // Selected view: ONLY flag + dial code (no country name)
        flagEl.className = 'flag';
        flagEl.textContent = getFlagEmoji(country.code);
        dialEl.textContent = `+${country.dial}`;
        hiddenInput.value = String(country.dial);
    }

    function normalizeForSearch(value) {
        return String(value || '').trim().toLowerCase();
    }

    // Search box (sticky at top of dropdown)
    const searchLi = document.createElement('li');
    searchLi.className = 'mo-country-search';
    searchLi.innerHTML = `
        <input
            type="text"
            id="moCountrySearch"
            class="mo-country-search-input"
            placeholder="Search country or code…"
            autocomplete="off"
            spellcheck="false"
        />
    `;
    list.appendChild(searchLi);

    const searchInput = searchLi.querySelector('input');

    // Build dropdown: show country name + dial code
    countries.forEach(country => {
        const li = document.createElement('li');
        li.dataset.name = normalizeForSearch(country.name);
        li.dataset.code = normalizeForSearch(country.code);
        li.dataset.dial = normalizeForSearch(country.dial);

        li.innerHTML = `
            <span class="flag" aria-hidden="true">${getFlagEmoji(country.code)}</span>
            <span class="name">${country.name}</span>
            <span class="dial">+${country.dial}</span>
        `;

        li.onclick = function () {
            setSelectedCountry(country);
            list.classList.remove('open');
        };

        list.appendChild(li);
    });

    // Initialize selected from hidden value (dial code) if present, else first country.
    const currentDial = String(hiddenInput.value || '').replace(/\D/g, '');
    const initial = countries.find(c => String(c.dial) === currentDial) || countries[0];
    if (initial) {
        setSelectedCountry(initial);
    }

    function applyFilter() {
        if (!searchInput) {
            return;
        }
        const q = normalizeForSearch(searchInput.value);
        const items = list.querySelectorAll('li');
        items.forEach(function (li) {
            if (li === searchLi) {
                return;
            }
            // divider/empty li safety
            if (!li.dataset) {
                return;
            }
            if (q === '') {
                li.style.display = '';
                return;
            }
            const haystack = `${li.dataset.name || ''} ${li.dataset.code || ''} ${li.dataset.dial || ''}`;
            li.style.display = haystack.includes(q) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                applyFilter();
                list.classList.remove('open');
                select.focus && select.focus();
            }
        });
    }

    select.onclick = () => {
        const isOpening = !list.classList.contains('open');
        list.classList.toggle('open');
        if (isOpening && searchInput) {
            // reset filter on open and focus search
            searchInput.value = '';
            applyFilter();
            setTimeout(() => searchInput.focus(), 0);
        }
    };

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!select.contains(e.target) && !list.contains(e.target)) {
            list.classList.remove('open');
        }
    });
});
