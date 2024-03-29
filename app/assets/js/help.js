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
