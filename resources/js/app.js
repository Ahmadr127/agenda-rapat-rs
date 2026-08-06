import './bootstrap';

import Alpine from 'alpinejs';
import SignaturePad from 'signature_pad';

window.Alpine = Alpine;
window.SignaturePad = SignaturePad;

// Lazy-load the heavy xlsx library only when it's actually needed
// (file import pages). Keeps it out of the main bundle. Cached after
// first load.
let xlsxPromise = null;
window.loadXLSX = () => (xlsxPromise ??= import('xlsx'));

Alpine.start();
