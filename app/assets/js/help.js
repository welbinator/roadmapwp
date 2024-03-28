document.querySelectorAll('.copy-tooltip').forEach(item => {
    item.addEventListener('click', event => {
        event.preventDefault();
        const text = item.getAttribute('data-text');
        navigator.clipboard.writeText(text).then(() => {
            const message = document.createElement('span');
            message.textContent = 'Shortcode copied!';
            message.style.fontSize = '12px';
            message.style.marginLeft = '5px';
            message.style.opacity = '1';
            message.style.transition = 'opacity 2s ease-out';
            item.parentNode.insertBefore(message, item.nextSibling);
            
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 2000);
            }, 2000);
        }).catch(err => {
            console.error('Error copying text: ', err);
        });
    });
});
