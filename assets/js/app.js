// Confirmation avant suppression : <form data-confirm="...">
document.addEventListener('submit', e => {
  const m = e.target.dataset.confirm;
  if (m && !confirm(m)) e.preventDefault();
});
// Filtre instantané des tableaux : <input data-filter="#idTable">
document.querySelectorAll('[data-filter]').forEach(inp => {
  inp.addEventListener('input', () => {
    const t = inp.value.toLowerCase();
    document.querySelectorAll(inp.dataset.filter + ' tbody tr').forEach(tr => {
      tr.style.display = tr.textContent.toLowerCase().includes(t) ? '' : 'none';
    });
  });
});
// Paiements : montant du transport rempli automatiquement
const typeSel = document.getElementById('ptype');
if (typeSel) {
  const etu = document.getElementById('petu'), mt = document.getElementById('pmontant');
  const fill = () => {
    const o = etu.selectedOptions[0];
    if (o && typeSel.value === 'transport' && o.dataset.tarif) mt.value = o.dataset.tarif;
  };
  typeSel.addEventListener('change', fill); etu.addEventListener('change', fill);
}
