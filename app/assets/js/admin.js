document.addEventListener('DOMContentLoaded', function() {
    const shortcodesToggle = document.getElementById('shortcodes-toggle');
    const shortcodesContent = document.getElementById('shortcodes-content');
    const blocksToggle = document.getElementById('blocks-toggle');
    const blocksContent = document.getElementById('blocks-content');
    const taxonomiesToggle = document.getElementById('taxonomies-toggle');
    const taxonomiesContent = document.getElementById('taxonomies-content');
    const stylesToggle = document.getElementById('styles-toggle');
    const stylesContent = document.getElementById('styles-content');
    

    shortcodesToggle.addEventListener('click', function() {
        shortcodesContent.classList.toggle('hidden');
        shortcodesToggle.textContent = shortcodesContent.classList.contains('hidden') ? 'expand' : 'collapse';
    });

    blocksToggle.addEventListener('click', function() {
        blocksContent.classList.toggle('hidden');
        blocksToggle.textContent = blocksContent.classList.contains('hidden') ? 'expand' : 'collapse';
    });

    taxonomiesToggle.addEventListener('click', function() {
        taxonomiesContent.classList.toggle('hidden');
        taxonomiesToggle.textContent = taxonomiesContent.classList.contains('hidden') ? 'expand' : 'collapse';
    });

    stylesToggle.addEventListener('click', function() {
        stylesContent.classList.toggle('hidden');
        stylesToggle.textContent = stylesContent.classList.contains('hidden') ? 'expand' : 'collapse';
    });
});