//allows price to change dynamically when new option fo single flowers chosen

document.addEventListener('DOMContentLoaded', function () {
    const priceEl = document.getElementById('displayPrice');
    if (!priceEl) return;

    // parse the base price from the data attribute
    const basePrice = parseFloat(priceEl.dataset.basePrice);

    // find all the option selects in the form
    const selects = document.querySelectorAll('form select');

    function updatePrice() {
        let total = basePrice;
        selects.forEach(select => {
            const opt = select.options[select.selectedIndex];
            // each <option> has data-price-mod
            const mod = parseFloat(opt.dataset.priceMod) || 0;
            total += mod;
        });
        priceEl.textContent = '$' + total.toFixed(2);
    }

    // recalculate on every change
    selects.forEach(select => select.addEventListener('change', updatePrice));

    // initialize on page load
    updatePrice();
});