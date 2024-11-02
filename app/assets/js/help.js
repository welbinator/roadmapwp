document.querySelectorAll('.copy-tooltip').forEach(item => {
    item.addEventListener('click', event => {
        event.preventDefault();
        const text = item.getAttribute('data-text');
        navigator.clipboard.writeText(text).then(() => {
            const message = document.createElement('span');
            message.textContent = 'Shortcode copied!';
            message.style.fontSize = '12px';
            message.style.marginLeft = '8px';
            message.style.opacity = '1';
            message.style.transition = 'opacity 2s';
            item.parentNode.appendChild(message);

            // Fade out the message
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.parentNode.removeChild(message), 2000); // Remove after fade
            }, 1000); // Start fade out after 1 second
        }).catch(err => {
            console.error('Error copying text: ', err);
        });
    });
});

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
